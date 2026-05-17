<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST['token']);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];
    $email = $_SESSION['reset_email'];

    if ($new_pass !== $confirm_pass) {
        $error = "NEURAL MISMATCH: Passwords do not match.";
    } else {
        try {
            $now = date("Y-m-d H:i:s");
            // Check if code matches and is not expired
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expiry > ? LIMIT 1");
            $stmt->execute([$email, $token, $now]);
            $user = $stmt->fetch();

            if ($user) {
                $hashed_pass = hash('sha256', $new_pass);
                $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
                $update->execute([$hashed_pass, $email]);

                unset($_SESSION['reset_email']);
                echo "<script>alert('ACCESS RESTORED: Your password has been updated.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "INVALID CODE: The code is incorrect or expired.";
            }
        } catch (PDOException $e) { $error = "CORE ERROR: " . $e->getMessage(); }
    }
}
?>
<!-- Rest of your HTML stays the same -->
 
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
        .form-control:focus { background: rgba(0,0,0,0.5); border-color: var(--baby-blue); color: white; box-shadow: 0 0 10px rgba(167,199,231,0.2); }
        .btn-reset { background: var(--baby-blue); color: var(--deep-obsidian); font-family: 'Orbitron'; font-weight: 700; border-radius: 12px; border: none; padding: 15px; transition: 0.3s; }
        .btn-reset:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(167,199,231,0.3); }
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

        <?php if(isset($error)) echo "<p class='text-danger mt-3 small fw-bold'>⚠️ $error</p>"; ?>
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