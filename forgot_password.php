<?php
session_start();
require_once 'db_config.php';
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Rest of your logic...

// Check Azure connection
if (!getenv('MAIL_PASS')) {
    die("SYSTEM ERROR: Azure settings not found. Ensure MAIL_USER and MAIL_PASS are saved in the portal.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // FIX: Initialize $mail here so it is never undefined
    $mail = new PHPMailer(true);

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $update->execute([$token, $expiry, $email]);

            // --- PHPMailer Settings ---
// --- GMAIL PRODUCTION SETTINGS (SSL MODE) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USER'); 
            $mail->Password   = getenv('MAIL_PASS'); 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Change to SMTPS
            $mail->Port       = 465; // Change to 465

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom(getenv('MAIL_USER'), 'LesBot Neural System');
            $mail->addAddress($email); 
            $mail->isHTML(true);
            $mail->Subject = 'LesBot | Identity Verification Code';
            $mail->Body    = "Your security code: <b style='font-size:24px;'>$token</b><br>Valid for 15 mins.";

            if($mail->send()) {
                $_SESSION['reset_email'] = $email;
                header("Location: verify_token.php");
                exit();
            }
        } else {
            $error = "IDENTITY NOT FOUND: This email is not registered.";
        }
    } catch (Exception $e) {
        // Safe check: Only use $mail->ErrorInfo if PHPMailer triggered the error
        $error = "Neural Link Error: " . $mail->ErrorInfo;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LesBot | Recover Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0B0E14; color: #A7C7E7; font-family: 'Rajdhani'; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(167, 199, 231, 0.3); border-radius: 30px; padding: 40px; width: 100%; max-width: 450px; text-align: center; }
        .form-control { background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; border-radius: 12px; margin-bottom: 15px; }
        .btn-neural { background: #00d4ff; color: black; font-family: 'Orbitron'; font-weight: 700; border-radius: 12px; border: none; padding: 15px; width: 100%; transition: 0.3s; }
    </style>
</head>
<body>
    <div class="glass-card shadow-lg">
        <h2 style="font-family: 'Orbitron'; letter-spacing: 3px;">RECOVERY</h2>
        <p class="small text-white-50 mb-4">Initialize Identity Verification</p>
        <form method="POST">
            <input type="email" name="email" class="form-control" placeholder="Registered Email" required>
            <button type="submit" class="btn btn-neural">SEND VERIFICATION CODE</button>
        </form>
        <?php if($error) echo "<p class='text-danger mt-3 small'>⚠️ $error</p>"; ?>
        <div class="mt-4"><a href="login.php" class="text-white-50 text-decoration-none small">← Back to Login</a></div>
    </div>
    <?php include 'chatbot_component.php'; ?>
</body>
</html>