// TEMPORARY DEBUG CODE
if (!getenv('MAIL_PASS')) {
    die("SYSTEM ERROR: Azure cannot see your MAIL_PASS variable. Check Azure settings.");
}

<?php
session_start();
require_once 'db_config.php';
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $mail = new PHPMailer(true);

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            // Save the 6-digit code to the database
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $update->execute([$token, $expiry, $email]);

            // --- GMAIL PRODUCTION SETTINGS ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USER'); // Pulled from Azure Vault
            $mail->Password   = getenv('MAIL_PASS'); // Pulled from Azure Vault
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom(getenv('MAIL_USER'), 'LesBot Neural System');
            $mail->addAddress($email); // Sends to the student's real Gmail
            $mail->isHTML(true);
            $mail->Subject = 'LesBot | Identity Verification Code';
            $mail->Body    = "Your identity verification code is: <b style='font-size: 24px; color: #00d4ff;'>$token</b><br><br>This code expires in 15 minutes.";

            $mail->send();

            // --- GMAIL PRODUCTION SETTINGS ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USER');
            $mail->Password   = getenv('MAIL_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // ADD THIS BLOCK TO FIX CERTIFICATE ERRORS
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
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
<!-- Rest of your HTML stays the same -->

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

    <div id="lesbot-chat-container" class="glass-card shadow-lg" style="position: fixed; bottom: 30px; right: 30px; width: 350px; display: none; z-index: 9999; border: 1px solid var(--lesbot-cyan);">
    <div class="card-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <span style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px;">LESBOT 24/7 HELPFLOW</span>
        <button onclick="toggleLesBot()" class="btn-close btn-close-white" style="font-size: 0.6rem;"></button>
    </div>
    <div id="chat-body" class="p-3" style="height: 350px; overflow-y: auto; font-family: 'Rajdhani';">
        <div class="mb-3"><small class="text-info">LesBot:</small><br>Identity verified. How can I assist you tonight?</div>
    </div>
    <div class="p-3 border-top border-secondary">
        <div class="input-group">
            <input type="text" id="user-msg" class="form-control bg-dark text-white border-secondary small" placeholder="Ask anything...">
            <button class="btn btn-outline-info" onclick="sendNeuralMessage()"><i class="bi bi-send"></i></button>
        </div>
    </div>
</div>

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>

</body>
</html>