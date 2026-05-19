<?php
session_start();
require_once 'db_config.php';

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
    } else {
        try {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $now = date("Y-m-d H:i:s");

            // Check if code matches and is not expired
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expiry > ? LIMIT 1");
            $stmt->execute([$email, $token, $now]);
            $user = $stmt->fetch();

            if ($user) {
                // SUCCESS: Create high-level hash for the new password
                $hashed_pass = password_hash($new_pass, PASSWORD_ARGON2ID);

                // Update database and clear security flags
                $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL, requires_reset = 0 WHERE email = ?");
                $update->execute([$hashed_pass, $email]);

                unset($_SESSION['reset_email']);
                echo "<script>alert('ACCESS RESTORED: Your identity has been updated with high-level encryption.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "INVALID CODE: The identity fragment is incorrect or has expired.";
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
    <!-- Link to the Robot Icon library -->
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <meta name="robots" content="index, follow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LesBot | Verify Identity</title>
    
        <!-- Paste the Google tag here -->
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />

    <!-- site identity tags -->
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #0B0E14; }
        body { 
            background-color: var(--obsidian); 
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
            display: flex; align-items: center; justify-content: center; 
            height: 100vh; margin: 0; 
        }
        .glass-card { 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(15px); 
            border: 1px solid rgba(0, 212, 255, 0.2); 
            border-radius: 30px; padding: 40px; width: 100%; max-width: 420px; 
            text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .form-control { 
            background: rgba(0,0,0,0.4); 
            border: 1px solid rgba(0, 212, 255, 0.2); 
            color: white; border-radius: 12px; padding: 12px; margin-bottom: 15px;
            font-family: 'Rajdhani';
        }
        .form-control:focus {
            background: rgba(0,0,0,0.6); border-color: var(--lesbot-cyan);
            color: white; box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }
        .btn-neural { 
            background: var(--lesbot-cyan); color: #000; 
            font-family: 'Orbitron'; font-weight: 700; border-radius: 12px; 
            border: none; padding: 15px; width: 100%; transition: 0.3s;
        }
        .btn-neural:hover { box-shadow: 0 0 20px var(--lesbot-cyan); transform: translateY(-2px); }
        .text-cyan { color: var(--lesbot-cyan); }

            /* --- Chatbot Styling Refined --- */
    .glass-card {
        background: rgba(8, 10, 15, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.8);
    }

    </style>
</head>
<body>

    <div class="glass-card">
        <h2 style="font-family: 'Orbitron'; letter-spacing: 3px; margin-bottom: 5px;">VERIFY</h2>
        <p class="small text-white-50 text-uppercase mb-4" style="letter-spacing: 2px;">Input Security Fragment</p>
        
        <p class="small mb-4" style="opacity: 0.7;">Sent to: <span class="text-cyan"><?= htmlspecialchars($email) ?></span></p>

        <?php if($error): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger small py-2 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="d-block text-start small mb-1 text-cyan" style="font-family: 'Orbitron'; font-size: 0.6rem;">6-DIGIT CODE</label>
                <input type="text" name="token" class="form-control text-center" placeholder="000000" maxlength="6" required style="letter-spacing: 10px; font-size: 1.5rem; font-weight: 900;">
            </div>
            
            <div class="mb-3">
                <label class="d-block text-start small mb-1 text-cyan" style="font-family: 'Orbitron'; font-size: 0.6rem;">NEW ACCESS KEY</label>
                <input type="password" name="new_pass" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="d-block text-start small mb-1 text-cyan" style="font-family: 'Orbitron'; font-size: 0.6rem;">CONFIRM KEY</label>
                <input type="password" name="confirm_pass" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-neural">RESTORE ACCESS</button>
        </form>

        <div class="mt-4">
            <a href="forgot_password.php" class="text-white-50 text-decoration-none small">← Request New Code</a>
        </div>
    </div>

<!-- Neural AI Support Button -->
<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>
</body>
</html>