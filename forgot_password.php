<?php
session_start();
require_once 'db_config.php';

// Phase 1: Reference the library files in your htdocs/lesbot folder
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    try {
        // Search for user in your 3NF database structure
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure 6-digit token and 15-minute expiry
// Set Malaysia Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

$token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// This now creates a timestamp exactly 15 minutes from "NOW" in Malaysia
$expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

$update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
$update->execute([$token, $expiry, $email]);

            // Store token in the Supertype table
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $update->execute([$token, $expiry, $email]);

            // Phase 2: Mailtrap Sandbox Configuration
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Port       = 2525;
            $mail->Username   = 'aaccc15ccf1332'; // From Mailtrap Gear Icon
            $mail->Password   = '0689c86460b24e'; // From Mailtrap Gear Icon

            // Recipients
            $mail->setFrom('recovery@lesbot.utem.edu.my', 'LesBot Neural System');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'LESBOT | Neural Access Recovery';
            $mail->Body    = "Your verification code is: <b style='font-size: 20px;'>$token</b>. Expires in 15 minutes.";

            $mail->send();
            
            $_SESSION['reset_email'] = $email;
            header("Location: verify_token.php");
            exit();

        } else {
            $error = "IDENTITY NOT FOUND: Email not registered.";
        }
    } catch (Exception $e) {
        $error = "Neural Link Error: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Recover Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0B0E14; color: #A7C7E7; font-family: 'Rajdhani'; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(167, 199, 231, 0.3); border-radius: 30px; padding: 40px; width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="glass-card text-center">
        <h2 style="font-family: 'Orbitron';">RECOVERY</h2>
        <p class="small mb-4">Initialize Identity Verification</p>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-3 bg-dark text-white border-info" placeholder="Enter Registered Email" required>
            <button type="submit" class="btn btn-info w-100 fw-bold">SEND VERIFICATION CODE</button>
        </form>
        <?php if(isset($error)) echo "<p class='text-danger mt-3'>$error</p>"; ?>
    </div>
</body>
</html>