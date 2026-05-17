<?php
/**
 * LESBOT NEURAL LOGIN
 * STRICT AUTHENTICATION PROTOCOL v3.0
 */
session_start();
require_once 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); 
    $pass = $_POST['password'];
    
    // Passwords are case-sensitive by nature of the Hash (SHA-256)
    $hashed_pass = hash('sha256', $pass); 

    try {
        /** 
         * DBA SECURITY UPGRADE: 
         * We use the 'BINARY' keyword to force case-sensitivity in the database.
         * This ensures 'B0324' != 'b0324' and 'ADMIN1' != 'admin1'.
         */
        $sql = "SELECT * FROM users 
                WHERE (BINARY user_id = :id OR BINARY email = :id) 
                AND BINARY password = :pass 
                LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $identifier, 'pass' => $hashed_pass]);
        $user = $stmt->fetch();

        if ($user) {
            // Initialize Neural Session
            $_SESSION['std_id']    = $user['user_id'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            
            // REDIRECTION LOGIC
            switch ($user['role']) {
                case 'Admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Staff':
                    header("Location: staff_dashboard.php");
                    break;
                case 'Student':
                    header("Location: student_dashboard.php");
                    break;
            }
            exit();
        } else {
            // Triggered if characters match but CASE does not match
            $error = "ACCESS DENIED: IDENTITY MISMATCH. Check your case-sensitivity (UPPER/lower case).";
        }
    } catch (PDOException $e) { 
        $error = "CORE ERROR: " . $e->getMessage(); 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LesBot | Neural Login</title>
    <!-- Modern Cyberpunk Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --pastel-blue: #A7C7E7;
            --soft-cyan: #E0F2F7;
            --deep-obsidian: #0B0E14;
            --glass: rgba(255, 255, 255, 0.05);
            --neon-border: rgba(167, 199, 231, 0.3);
            --lesbot-cyan: #00d4ff;
        }

        body {
            margin: 0; padding: 0; min-height: 100vh;
            background-color: var(--deep-obsidian);
            /* Deep radial glow aesthetic */
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 70%);
            font-family: 'Rajdhani', sans-serif;
            display: flex; justify-content: center; align-items: center;
            color: var(--soft-cyan);
            overflow: hidden;
        }

        .login-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 50px;
            border: 1px solid var(--neon-border);
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center; width: 100%; max-width: 420px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 { 
            font-family: 'Orbitron'; 
            color: var(--pastel-blue); 
            letter-spacing: 5px; 
            font-size: 2.2rem; 
            margin-bottom: 5px;
        }

        .form-group { margin-bottom: 25px; text-align: left; }
        
        label { 
            display: block; 
            color: var(--lesbot-cyan); 
            font-size: 0.65rem; 
            margin-bottom: 8px; 
            font-family: 'Orbitron'; 
            letter-spacing: 2px;
            font-weight: 700;
        }

        input { 
            width: 100%; 
            background: rgba(0,0,0,0.4); 
            border: 1px solid var(--neon-border); 
            padding: 15px; 
            border-radius: 12px; 
            color: white; 
            outline: none; 
            font-family: 'Rajdhani';
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--lesbot-cyan);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .login-button { 
            width: 100%; 
            padding: 15px; 
            border-radius: 12px; 
            border: none; 
            background: linear-gradient(45deg, #00d4ff, #A7C7E7); 
            color: #000;
            font-family: 'Orbitron'; 
            font-weight: 900; 
            letter-spacing: 2px;
            cursor: pointer; 
            transition: 0.4s; 
            margin-top: 10px;
        }

        .login-button:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.4); 
            filter: brightness(1.1);
        }

        .error-message { 
            color: #FF4D4D; 
            margin-top: 20px; 
            font-size: 0.75rem; 
            font-weight: bold; 
            border: 1px solid #FF4D4D;
            background: rgba(255, 77, 77, 0.1);
            padding: 10px;
            border-radius: 8px;
        }

        .link-footer { margin-top: 35px; font-size: 0.8rem; }
        .link-footer a { color: var(--lesbot-cyan); text-decoration: none; margin: 0 10px; transition: 0.3s; }
        .link-footer a:hover { text-shadow: 0 0 10px var(--lesbot-cyan); }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>LESBOT</h2>
        <p class="small opacity-50 text-uppercase mb-5" style="letter-spacing: 3px; font-size: 0.6rem;">Initialize Strict Neural Auth</p>
        
        <form method="POST">
            <div class="form-group">
                <label>IDENTITY (CASE SENSITIVE)</label>
                <input type="text" name="identifier" required placeholder="User ID / Email..." autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>ACCESS KEY (CASE SENSITIVE)</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="login-button">STABLISH LINK</button>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="bi bi-shield-lock-fill me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>

        <div class="link-footer">
            <a href="forgot_password.php">Recover Access</a>
            <span class="opacity-25">|</span>
            <a href="signup.php">Create Fragment</a>
        </div>
    </div>

    <!-- Integrate the 24/7 Neural AI Component -->
    <?php include 'chatbot_component.php'; ?>

</body>
</html>