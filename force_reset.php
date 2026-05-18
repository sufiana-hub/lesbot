<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['std_id'])) { header("Location: login.php"); exit(); }

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass !== $confirm_pass) {
        $error = "Neural Mismatch: Keys do not match.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_ARGON2ID);
        $id = $_SESSION['std_id'];

        // Update password AND set reset flag to 0
        $stmt = $pdo->prepare("UPDATE users SET password = ?, requires_reset = 0 WHERE user_id = ?");
        $stmt->execute([$hashed, $id]);

        echo "<script>alert('NEURAL KEY UPDATED. Access authorized.'); window.location.href='staff_dashboard.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <title>LesBot | Secure Your Link</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0B0E14; color: #fff; font-family: 'Rajdhani'; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.05); border: 1px solid #00d4ff; border-radius: 30px; padding: 40px; width: 400px; text-align: center; }
        .form-control { background: rgba(0,0,0,0.3); color: white; border: 1px solid #444; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="glass-card shadow-lg">
        <h2 style="font-family: 'Orbitron'; color: #00d4ff;">SECURE ACCESS</h2>
        <p class="small opacity-50 mb-4">First login detected. You must set a private access key.</p>
        
        <?php if($error) echo "<p class='text-danger'>$error</p>"; ?>

        <form method="POST">
            <input type="password" name="new_pass" class="form-control" placeholder="New Private Key" required>
            <input type="password" name="confirm_pass" class="form-control" placeholder="Confirm Private Key" required>
            <button type="submit" class="btn btn-info w-100 fw-bold">INITIALIZE PRIVATE LINK</button>
        </form>
    </div>
</body>
</html>