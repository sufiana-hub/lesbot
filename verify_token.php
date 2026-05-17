<?php
session_start();
require_once 'db_config.php';

// 1. SECURITY CHECK: Did they come from the forgot password page?
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = ""; 
$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST['token']);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass !== $confirm_pass) {
        $error = "NEURAL MISMATCH: Passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error = "SECURITY VULNERABILITY: Key must be at least 6 characters.";
    } else {
        try {
            // Synchronize with Malaysia Time for the expiry check
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $now = date("Y-m-d H:i:s");

            // 2. VALIDATE TOKEN: Check if it exists and is not expired
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expiry > ? LIMIT 1");
            $stmt->execute([$email, $token, $now]);
            $user = $stmt->fetch();

            if ($user) {
                // 3. UPDATE PASSWORD: Using SHA256 to match your login logic
                $hashed_pass = hash('sha256', $new_pass);

                $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
                $update->execute([$hashed_pass, $email]);

                // Clear session and redirect
                unset($_SESSION['reset_email']);
                echo "<script>alert('ACCESS RESTORED: Neural Link Updated Successfully.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "INVALID OR EXPIRED CODE: The identity fragment does not match.";
            }
        } catch (PDOException $e) {
            $error = "CORE ERROR: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LesBot | Verify Identity</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --baby-blue: #A7C7E7; --deep-obsidian: #0B0E14; }
        body { background-color: var(--deep-obsidian); color: var(--baby-blue); font-family: 'Rajdhani'; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(167, 199, 231, 0.3); border-radius: 30px; padding: 40px; width: 100%; max-width: 450px; text-align: center; }
        .form-control { background: rgba(0,0,0,0.3); border: 1px solid rgba(167,199,231,0.2); color: white; border-radius: 12px; padding: 12px; }
        .form-control:focus { background: rgba(0,0,0,0.5); border-color: var(--baby-blue); color: white; box-shadow: 0 0 10px rgba(167, 199, 231, 0.2); }
        .btn-reset { background: var(--baby-blue); color: var(--deep-obsidian); font-family: 'Orbitron'; font-weight: 700; border-radius: 12px; border: none; padding: 15px; transition: 0.3s; }
        .btn-reset:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(167, 199, 231, 0.3); }
    </style>
</head>
<body>

    <div class="glass-card">
        <h2 style="font-family: 'Orbitron'; letter-spacing: 3px;">VERIFY</h2>
        <p class="small text-muted mb-4 text-uppercase">Input Identity Verification Code</p>

        <form method="POST">
            <div class="mb-3">
                <label class="d-block text-start small mb-2 opacity-75">6-DIGIT CODE</label>
                <input type="text" name="token" class="form-control text-center" placeholder="000000" maxlength="6" required style="letter-spacing: 10px; font-size: 1.5rem; font-weight: 900;">
            </div>
            
            <div class="mb-3">
                <label class="d-block text-start small mb-2 opacity-75">NEW ACCESS KEY</label>
                <input type="password" name="new_pass" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="d-block text-start small mb-2 opacity-75">CONFIRM KEY</label>
                <input type="password" name="confirm_pass" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-reset w-100">RESTORE ACCESS</button>
        </form>

        <?php if(!empty($error)) echo "<p class='text-danger mt-3 small fw-bold'>⚠️ $error</p>"; ?>
    </div>

    <!-- Floating Chatbot Included -->
    <?php include 'chatbot_component.php'; ?>

</body>
</html>