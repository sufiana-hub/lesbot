<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric   = trim($_POST['matric']);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $room     = strtoupper(trim($_POST['room'])); // 
    $year_sem = trim($_POST['year_sem']);
    $plain_pw = $_POST['pass'];

    // --- INSERT WING VALIDATION HERE ---
    $firstChar = substr($room, 0, 1);
    
    if ($firstChar !== 'A' && $firstChar !== 'B') {
        $error = "INVALID WING: Room must start with 'A' (Blok A) or 'B' (Blok B).";
    } else {
        // MISSION ALIGNMENT: SHA-256 Encryption matching C++ Logic
        $hashed_pw = hash('sha256', $plain_pw);

        try {
            // Start Transaction for Supertype/Subtype integrity
            $pdo->beginTransaction();

            // 1. CREATE: Insert into 'users' Supertype
            $stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Student')");
            $stmtUser->execute([$matric, $name, $email, $hashed_pw]);

            // 2. CREATE: Insert into 'student' Subtype
            $stmtStd = $pdo->prepare("INSERT INTO student (matric_number, room_number, year_sem) VALUES (?, ?, ?)");
            $stmtStd->execute([$matric, $room, $year_sem]);

            $pdo->commit();

            $_SESSION['success'] = "NEURAL LINK ESTABLISHED: You may now login.";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = "IDENTITY CONFLICT: Matric number or Email already exists.";
            } else {
                $error = "CORE ERROR: Initialization Failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LesBot | Neural Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --pastel-blue: #A7C7E7; --neon-cyan: #00f2ff; --bg-obsidian: #0b0e14; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 50% 50%, #1a1e26 0%, #05070a 100%); font-family: 'Rajdhani', sans-serif; margin: 0; padding: 20px; color: #fff; }
        .signup-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px); border: 1px solid rgba(167, 199, 231, 0.2); border-radius: 30px; padding: 40px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px rgba(0,0,0,0.7); text-align: center; }
        .brand-name { font-family: 'Orbitron'; font-weight: 900; font-size: 2rem; letter-spacing: 5px; margin-bottom: 5px; }
        .neural-tag { font-family: 'Orbitron'; color: var(--pastel-blue); font-size: 0.7rem; letter-spacing: 3px; margin-bottom: 30px; text-transform: uppercase; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left; }
        .input-box { position: relative; margin-bottom: 20px; }
        .input-box.full { grid-column: span 2; }
        .input-box label { display: block; font-family: 'Orbitron'; font-size: 0.6rem; color: var(--pastel-blue); margin-bottom: 8px; letter-spacing: 1px; }
        .input-box input { width: 100%; background: #e0e6ed; border: none; padding: 12px 15px; border-radius: 10px; color: #0b0e14; font-weight: 700; font-size: 1rem; box-sizing: border-box; outline: none; }
        .btn-register { width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--neon-cyan); background: transparent; color: #ffffff; font-family: 'Orbitron'; font-weight: 900; font-size: 1rem; letter-spacing: 3px; cursor: pointer; transition: 0.5s; margin-top: 10px; }
        .btn-register:hover { background: var(--neon-cyan); color: #000; box-shadow: 0 0 20px var(--neon-cyan); }
    </style>
</head>
<body>
    <div class="signup-card">
        <div class="brand-name">LESBOT</div>
        <div class="neural-tag">New Entity Registration</div>
        <form method="POST">
            <div class="grid-inputs">
                <div class="input-box full"><label>FULL NAME</label><input type="text" name="name" required></div>
                <div class="input-box"><label>MATRIC NUMBER</label><input type="text" name="matric" placeholder="B03XXXXXXXX" required></div>
                <div class="input-box"><label>EMAIL ADDRESS</label><input type="email" name="email" required></div>
                <div class="input-box"><label>ROOM NUMBER</label><input type="text" name="room" required></div>
                <div class="input-box"><label>YEAR / SEMESTER</label><input type="text" name="year_sem" required></div>
                <div class="input-box full"><label>CREATE ACCESS KEY (PASSWORD)</label><input type="password" name="pass" required></div>
            </div>
            <button type="submit" class="btn-register">INITIATE CREATION</button>
            <a href="login.php" style="display:block; margin-top:20px; color:var(--pastel-blue); text-decoration:none; font-size:0.8rem;">Already linked? Login here.</a>
            <?php if(isset($error)) echo "<div style='color:#ff4b2b; margin-top:20px; font-size:0.8rem;'>⚠️ $error</div>"; ?>
        </form>
    </div>
</body>
</html>