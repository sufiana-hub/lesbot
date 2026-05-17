<?php
/**
 * LESBOT NEURAL REGISTRATION
 * STRICT IDENTITY PROTOCOL v3.0
 * 
 * Logic Enforced:
 * 1. Unique Matric Number (Integrity Constraint)
 * 2. Room Hierarchy: [Block]-[Aras]-[Rumah]-[Bilik]
 *    - Girls: B1/B2 | Boys: A1/A2/A3/A4
 *    - Aras: G/1/2/3/4 | Rumah: A/B/C/D | Bilik: 01-07
 * 3. Academic Sync: Standardized "YEAR X SEM Y" format
 */
session_start();
require_once 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. DATA NORMALIZATION: Force structure for the Neural Archive
    $matric   = strtoupper(trim($_POST['matric'])); // B03...
    $name     = strtoupper(trim($_POST['name']));   // FULL NAME
    $email    = strtolower(trim($_POST['email']));
    $room     = strtoupper(trim($_POST['room']));   // B1-1-C-04
    
    // 2. ACADEMIC SYNCHRONIZATION: Combine dropdowns into a structured string
    $year     = $_POST['year'];
    $sem      = $_POST['semester'];
    $year_sem = "YEAR $year SEM $sem";

    $plain_pw = $_POST['pass'];
    $hashed_pw = hash('sha256', $plain_pw);

    // 3. HIERARCHICAL ROOM VALIDATION (DBA DOMAIN CAP)
    /**
     * Regex Pattern Explained:
     * ^(A[1-4]|B[1-2]) : Starts with A1-A4 or B1-B2
     * -[G1-4]          : Aras must be G, 1, 2, 3, or 4
     * -[A-D]           : Rumah must be A, B, C, or D
     * -0[1-7]$         : Bilik must be 01 to 07
     */
    $room_pattern = '/^(A[1-4]|B[1-2])-[G1-4]-[A-D]-0[1-7]$/';

    if (!preg_match($room_pattern, $room)) {
        $error = "STRUCTURE ERROR: Invalid Room Format. <br> 
                  Please follow: [Block]-[Aras]-[Rumah]-[Bilik] <br>
                  (e.g., Girls: B1-1-C-04 | Boys: A3-G-A-01). <br>
                  Note: Aras G-4, Rumah A-D, Bilik 01-07.";
    } 
    else {
        try {
            // Start Neural Transaction
            $pdo->beginTransaction();

            // 4. INSERT INTO SUPERTYPE (users)
            // If Matric exists, PDO throws Error 23000
            $stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Student')");
            $stmtUser->execute([$matric, $name, $email, $hashed_pw]);

            // 5. INSERT INTO SUBTYPE (student)
            $stmtStd = $pdo->prepare("INSERT INTO student (matric_number, room_number, year_sem) VALUES (?, ?, ?)");
            $stmtStd->execute([$matric, $room, $year_sem]);

            $pdo->commit();

            $_SESSION['success'] = "NEURAL IDENTITY CREATED: Identity for $matric is now live.";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            // 6. UNIQUENESS ENFORCEMENT (DBA Integrity)
            if ($e->getCode() == 23000) {
                $error = "DBA CONFLICT: Matric ID [$matric] or Email is already registered in the system.";
            } else {
                $error = "CORE ERROR: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LesBot | Neural Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { 
            --neon-cyan: #00f2ff; 
            --pastel-blue: #A7C7E7;
            --bg-obsidian: #0b0e14;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body { 
            min-height: 100vh; 
            display: flex; align-items: center; justify-content: center; 
            background: radial-gradient(circle at 50% 50%, #1a1e26 0%, #05070a 100%); 
            font-family: 'Rajdhani', sans-serif; margin: 0; padding: 30px; color: #fff;
            overflow-x: hidden;
        }

        .signup-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px); 
            border: 1px solid rgba(167, 199, 231, 0.2); 
            border-radius: 40px; padding: 50px; 
            width: 100%; max-width: 650px; 
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        .brand-name { font-family: 'Orbitron'; font-weight: 900; font-size: 2.2rem; letter-spacing: 5px; color: var(--neon-cyan); text-align: center; }
        .tagline { text-align: center; font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 3px; opacity: 0.6; margin-bottom: 30px; }

        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left; }
        .input-box.full { grid-column: span 2; }
        
        label { display: block; font-family: 'Orbitron'; font-size: 0.65rem; color: var(--pastel-blue); margin-bottom: 8px; letter-spacing: 2px; font-weight: 700; }
        
        input, select { 
            width: 100%; background: #e0e6ed; border: 2px solid transparent; 
            padding: 14px 18px; border-radius: 12px; color: #0b0e14; 
            font-weight: 700; font-size: 1rem; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--neon-cyan); background: #ffffff; }

        .btn-register { 
            width: 100%; padding: 18px; border-radius: 15px; 
            border: 1px solid var(--neon-cyan); background: transparent; 
            color: #ffffff; font-family: 'Orbitron'; font-weight: 900; 
            font-size: 1.1rem; letter-spacing: 5px; cursor: pointer; 
            transition: 0.5s; margin-top: 30px;
        }
        .btn-register:hover { background: var(--neon-cyan); color: #000; box-shadow: 0 0 40px var(--neon-cyan); }

        .error-msg { 
            color: #ff4d4d; background: rgba(255, 77, 77, 0.1); 
            border: 1px solid #ff4d4d; padding: 15px; 
            border-radius: 12px; margin-bottom: 25px; 
            font-weight: bold; font-size: 0.85rem; line-height: 1.5;
        }

        .footer-link { margin-top: 25px; text-align: center; }
        .footer-link a { color: var(--pastel-blue); text-decoration: none; font-size: 0.8rem; font-family: 'Orbitron'; transition: 0.3s; }
        .footer-link a:hover { color: var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); }
    </style>
</head>
<body>

    <div class="signup-card">
        <div class="brand-name">LESBOT</div>
        <div class="tagline">IDENTITY SYNCHRONIZATION PORTAL</div>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="grid-inputs">
                <div class="input-box full">
                    <label>FULL LEGAL NAME</label>
                    <input type="text" name="name" placeholder="ENTER FULL NAME AS PER ID" required>
                </div>
                
                <div class="input-box">
                    <label>MATRIC IDENTIFIER (UNIQUE)</label>
                    <input type="text" name="matric" placeholder="E.G. B032410816" required>
                </div>
                
                <div class="input-box">
                    <label>NEURAL LINK (EMAIL)</label>
                    <input type="email" name="email" placeholder="student@utem.edu.my" required>
                </div>
                
                <div class="input-box">
                    <label>ROOM ADDR (E.G. B1-1-C-04)</label>
                    <input type="text" name="room" placeholder="BLOCK-ARAS-RUMAH-BILIK" required>
                </div>

                <div class="input-box">
                    <label>ACADEMIC SYNCHRONIZATION</label>
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

                <div class="input-box full">
                    <label>SET ACCESS KEY (PASSWORD)</label>
                    <input type="password" name="pass" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" class="btn-register">INITIATE IDENTITY</button>
            
            <div class="footer-link">
                <a href="login.php">← ALREADY LINKED? LOGIN HERE</a>
            </div>
        </form>
    </div>

    <!-- Integrated AI Chatbot Interface -->
    <?php include 'chatbot_component.php'; ?>

</body>
</html>