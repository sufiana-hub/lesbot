<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); 
    $pass = $_POST['password'];
    $hashed_pass = hash('sha256', $pass); //

    try {
        // Authenticate via Supertype 'users'
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (user_id = :id OR email = :id) AND password = :pass LIMIT 1");
        $stmt->execute(['id' => $identifier, 'pass' => $hashed_pass]);
        $user = $stmt->fetch();

        if ($user) {
            // Store common session data
            $_SESSION['std_id']    = $user['user_id'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            
            // ROLE-BASED REDIRECTION
            switch ($user['role']) {
              case 'Admin':
              header("Location: admin_dashboard.php"); // Takes the 'Head of Fellow' to the core
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
            $error = "ACCESS DENIED: NEURAL MISMATCH";
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
    <style>
        :root {
            --pastel-blue: #A7C7E7;
            --soft-cyan: #E0F2F7;
            --deep-obsidian: #0B0E14;
            --glass: rgba(255, 255, 255, 0.05);
            --neon-border: rgba(167, 199, 231, 0.3);
        }

        body {
            margin: 0; padding: 0; height: 100vh;
            background-color: var(--deep-obsidian);
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(167, 199, 231, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(106, 90, 205, 0.1) 0%, transparent 40%);
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

        .login-container h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--pastel-blue);
            letter-spacing: 5px; font-size: 2rem;
        }

        .form-group { margin-bottom: 25px; text-align: left; }
        label { display: block; color: var(--pastel-blue); font-size: 0.8rem; margin-bottom: 8px; }

        input {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--neon-border);
            padding: 15px; border-radius: 12px; color: white; outline: none; box-sizing: border-box;
        }

        .login-button {
            width: 100%; padding: 15px; border-radius: 12px; border: none;
            background: linear-gradient(45deg, #7FA8D1, #A7C7E7);
            font-family: 'Orbitron', sans-serif; cursor: pointer; transition: 0.4s;
        }

        .login-button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(167, 199, 231, 0.3); }
        .error-message { color: #FF6B6B; margin-top: 15px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>LESBOT</h2>
        <p style="font-size: 0.7rem; letter-spacing: 3px; color: rgba(167,199,231,0.6);">NEURAL NETWORK LOGIN</p>
        
        <form method="POST">
            <div class="form-group">
                <label>IDENTITY (MATRIX / EMAIL/ ID)</label>
                <input type="text" name="identifier" required placeholder="Initialize ID...">
            </div>
            <div class="form-group">
                <label>ACCESS KEY</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="login-button">INITIALIZE</button>

            <?php if (isset($error)): ?>
                <p class="error-message">⚠️ <?php echo $error; ?></p>
            <?php endif; ?>
        </form>

        <div style="margin-top: 30px; font-size: 0.8rem;">
            <a href="forgot_password.php" style="color: var(--pastel-blue); text-decoration: none;">Recover Key</a> | 
            <a href="signup.php" style="color: var(--pastel-blue); text-decoration: none;">Create Identity</a>
        </div>
    </div>
</body>
</html>