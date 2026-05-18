<?php
// 1. SYSTEM INITIALIZATION
session_start();
require_once 'db_config.php';

// Sync with Malaysia Time for the 15-minute expiry check
date_default_timezone_set('Asia/Kuala_Lumpur');

// 2. NEURAL ACCESS CONTROL: Ensure the user is in the middle of a reset
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = ""; 
$email = $_SESSION['reset_email'];

// 3. PROCESS FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST['token']);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Validation Logic
    if ($new_pass !== $confirm_pass) {
        $error = "NEURAL MISMATCH: Passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error = "VULNERABILITY: New key must be at least 6 characters.";
    } else {
        try {
            $now = date("Y-m-d H:i:s");

            // CHECK DATABASE: Does token match and is it still valid (within 15 mins)?
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expiry > ? LIMIT 1");
            $stmt->execute([$email, $token, $now]);
            $user = $stmt->fetch();

            if ($user) {
                // TOKEN VALID: Update the password using the system's SHA-256 standard
                $hashed_pass = hash('sha256', $new_pass);

                // Clear the token and update password
                $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
                $update->execute([$hashed_pass, $email]);

                // Destroy reset session for security
                unset($_SESSION['reset_email']);

                // Success Notification
                echo "<script>alert('ACCESS RESTORED: Identity updated. Redirecting to Login.'); window.location.href='login.php';</script>";
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
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LesBot | Verify Identity</title>
    
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

    <!-- 4. CHATBOT COMPONENT (Ensure this file exists or remove this line to test) -->
     <button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>

    <?php 
        if (file_exists('chatbot_component.php')) {
            include 'chatbot_component.php'; 
        }
    ?>

</body>
</html>