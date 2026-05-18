<?php
/**
 * LESBOT NEURAL LOGIN
 * HIGH-LEVEL ENCRYPTION & STRICT IDENTITY PROTOCOL v4.0
 */
session_start();
require_once 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); 
    $pass = $_POST['password'];

    try {
        /** 
         * DBA SECURITY UPGRADE: 
         * 1. Use 'BINARY' to force case-sensitivity on the Identifier (ID/Email).
         * 2. We ONLY fetch the user data here. We do not check the password in SQL
         *    because high-level hashes cannot be compared via simple strings.
         */
        $sql = "SELECT * FROM users 
                WHERE (BINARY user_id = :id OR BINARY email = :id) 
                LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $identifier]);
        $user = $stmt->fetch();

        /**
         * 3. HIGH-LEVEL VERIFICATION:
         * password_verify() is the industry standard. It handles the 
         * complex salt and algorithm logic (Argon2id/BCrypt) automatically.
         */
        if ($user && password_verify($pass, $user['password'])) {
            // Initialize Neural Session
            $_SESSION['std_id']    = $user['user_id'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            
            // REDIRECTION LOGIC
            switch ($user['role']) {
                case 'Admin': header("Location: admin_dashboard.php"); break;
                case 'Staff': header("Location: staff_dashboard.php"); break;
                case 'Student': header("Location: student_dashboard.php"); break;
            }
            exit();
        } else {
            // Triggered if ID doesn't exist OR password/case is wrong
            $error = "ACCESS DENIED: IDENTITY MISMATCH. Check your case-sensitivity or key credentials.";
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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FIX: ADD THIS LINE BELOW TO SHOW THE ROBOT ICON -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* Keep your existing styles here */
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
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 70%);
            font-family: 'Rajdhani', sans-serif;
            display: flex; justify-content: center; align-items: center;
            color: var(--soft-cyan);
        }

        .login-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 50px;
            border: 1px solid var(--neon-border);
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center; width: 100%; max-width: 420px;
        }

        .login-container h2 { font-family: 'Orbitron'; color: var(--pastel-blue); letter-spacing: 5px; font-size: 2.2rem; }
        .form-group { margin-bottom: 25px; text-align: left; }
        label { display: block; color: var(--lesbot-cyan); font-size: 0.65rem; margin-bottom: 8px; font-family: 'Orbitron'; letter-spacing: 2px; font-weight: 700; }
        input { width: 100%; background: rgba(0,0,0,0.4); border: 1px solid var(--neon-border); padding: 15px; border-radius: 12px; color: white; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--lesbot-cyan); box-shadow: 0 0 15px rgba(0, 212, 255, 0.2); }
        .login-button { width: 100%; padding: 15px; border-radius: 12px; border: none; background: linear-gradient(45deg, #00d4ff, #A7C7E7); color: #000; font-family: 'Orbitron'; font-weight: 900; letter-spacing: 2px; cursor: pointer; transition: 0.4s; margin-top: 10px; }
        .login-button:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 212, 255, 0.4); }
        .error-message { color: #FF4D4D; margin-top: 20px; font-size: 0.75rem; font-weight: bold; border: 1px solid #FF4D4D; background: rgba(255, 77, 77, 0.1); padding: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="margin-bottom: 5px;">LESBOT</h2>
        <p class="small opacity-50 text-uppercase mb-5" style="letter-spacing: 3px; font-size: 0.6rem;">High-Level Neural Auth Active</p>
        
        <form method="POST">
            <div class="form-group">
                <label>IDENTITY (CASE SENSITIVE)</label>
                <input type="text" name="identifier" required placeholder="ID / Email...">
            </div>
            <div class="form-group">
                <label>ACCESS KEY (HIGH ENCRYPTION)</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="login-button">ESTABLISH LINK</button>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="bi bi-shield-lock-fill me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>

        <div style="margin-top: 35px; font-size: 0.8rem;">
            <a href="forgot_password.php" style="color: var(--lesbot-cyan); text-decoration: none;">Recover Access</a> | 
            <a href="signup.php" style="color: var(--lesbot-cyan); text-decoration: none;">Create Identity</a>
        </div>
    </div>

    <!-- 24/7 Robot Support Icon -->
<!-- High-Visibility Robot Button -->
<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>

</body>
</html>