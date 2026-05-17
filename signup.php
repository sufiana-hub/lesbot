<?php
session_start();
require_once 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. DATA ACQUISITION & NORMALIZATION
    $matric   = strtoupper(trim($_POST['matric'])); // Force Uppercase (B03...)
    $name     = strtoupper(trim($_POST['name']));   // Force Uppercase for structured records
    $email    = strtolower(trim($_POST['email']));
    $room     = strtoupper(trim($_POST['room']));   
    
    // Synchronize Year and Sem into a single structured string: "Y3 S2"
    $year     = $_POST['year'];
    $sem      = $_POST['semester'];
    $year_sem = "YEAR $year SEM $sem";

    $plain_pw = $_POST['pass'];
    $hashed_pw = hash('sha256', $plain_pw);

    // 2. DOMAIN VALIDATION (DBA CAP)
    $firstChar = substr($room, 0, 1);
    
    // Check for correct Block Prefix
    if ($firstChar !== 'A' && $firstChar !== 'B') {
        $error = "VALIDATION FAILURE: Room must begin with 'A' (Male) or 'B' (Female).";
    } 
    // Basic pattern check: ensure it looks like A1-01 or B2-10
    elseif (!preg_match('/^[AB][1-9]-[0-9]+/', $room)) {
        $error = "STRUCTURE ERROR: Use format [Block][Floor]-[Number] (e.g., B1-05).";
    }
    else {
        try {
            $pdo->beginTransaction();

            /**
             * 3. UNIQUENESS CHECK (The "Only 1 Matric" Rule)
             * We attempt the insert. If the Matric exists, PDO will throw 
             * exception 23000 (Integrity Constraint Violation).
             */
            
            // Insert into Supertype
            $stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Student')");
            $stmtUser->execute([$matric, $name, $email, $hashed_pw]);

            // Insert into Subtype
            $stmtStd = $pdo->prepare("INSERT INTO student (matric_number, room_number, year_sem) VALUES (?, ?, ?)");
            $stmtStd->execute([$matric, $room, $year_sem]);

            $pdo->commit();

            $_SESSION['success'] = "NEURAL IDENTITY CREATED: Access Key for $matric is now active.";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Error Code 23000 = Duplicate Entry
            if ($e->getCode() == 23000) {
                $error = "DBA CONFLICT: Matric Number [$matric] or Email is already registered in the Neural Archive.";
            } else {
                $error = "SYSTEM ERROR: " . $e->getMessage();
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
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --pastel-blue: #A7C7E7; --neon-cyan: #00f2ff; --bg-obsidian: #0b0e14; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 50% 50%, #1a1e26 0%, #05070a 100%); font-family: 'Rajdhani', sans-serif; margin: 0; padding: 20px; color: #fff; }
        .signup-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px); border: 1px solid rgba(167, 199, 231, 0.2); border-radius: 30px; padding: 50px; width: 100%; max-width: 650px; box-shadow: 0 25px 50px rgba(0,0,0,0.7); text-align: center; }
        .brand-name { font-family: 'Orbitron'; font-weight: 900; font-size: 2.2rem; letter-spacing: 5px; color: var(--neon-cyan); }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left; margin-top: 30px; }
        .input-box.full { grid-column: span 2; }
        label { display: block; font-family: 'Orbitron'; font-size: 0.65rem; color: var(--pastel-blue); margin-bottom: 10px; letter-spacing: 2px; }
        input, select { width: 100%; background: #e0e6ed; border: none; padding: 14px; border-radius: 12px; color: #0b0e14; font-weight: 700; font-size: 1rem; outline: none; }
        .btn-register { width: 100%; padding: 18px; border-radius: 15px; border: 1px solid var(--neon-cyan); background: transparent; color: #ffffff; font-family: 'Orbitron'; font-weight: 900; font-size: 1.1rem; letter-spacing: 4px; cursor: pointer; transition: 0.4s; margin-top: 20px; }
        .btn-register:hover { background: var(--neon-cyan); color: #000; box-shadow: 0 0 30px var(--neon-cyan); }
        .error-msg { color: #ff4b2b; background: rgba(255, 75, 43, 0.1); border: 1px solid #ff4b2b; padding: 15px; border-radius: 10px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="signup-card">
        <div class="brand-name">LESBOT</div>
        <div style="font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 3px; opacity: 0.6;">NEW ENTITY REGISTRATION</div>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="grid-inputs">
                <div class="input-box full"><label>FULL NAME (IDENTIFIER)</label><input type="text" name="name" placeholder="E.G. SUFIANA ADLIN" required></div>
                
                <div class="input-box"><label>MATRIC NUMBER (UNIQUE)</label><input type="text" name="matric" placeholder="B03XXXXXXXX" required></div>
                
                <div class="input-box"><label>OFFICIAL EMAIL</label><input type="email" name="email" placeholder="student@utem.edu.my" required></div>
                
                <div class="input-box">
                    <label>ROOM NUMBER (A=BOY, B=GIRL)</label>
                    <input type="text" name="room" placeholder="E.G. B1-01" required>
                </div>

                <div class="input-box">
                    <label>ACADEMIC LEVEL</label>
                    <div class="d-flex gap-2">
                        <select name="year" required>
                            <option value="" disabled selected>Year</option>
                            <option value="1">Y1</option><option value="2">Y2</option>
                            <option value="3">Y3</option><option value="4">Y4</option>
                        </select>
                        <select name="semester" required>
                            <option value="" disabled selected>Sem</option>
                            <option value="1">S1</option><option value="2">S2</option>
                        </select>
                    </div>
                </div>

                <div class="input-box full"><label>CREATE ACCESS KEY (SECURE PASSWORD)</label><input type="password" name="pass" required></div>
            </div>
            
            <button type="submit" class="btn-register">INITIATE CREATION</button>
            
            <div class="mt-4">
                <a href="login.php" style="color:var(--pastel-blue); text-decoration:none; font-size:0.8rem; font-family:'Orbitron';">← Back to Login</a>
            </div>
        </form>
    </div>

    <?php include 'chatbot_component.php'; ?>
</body>
</html>