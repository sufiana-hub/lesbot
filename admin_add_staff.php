<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

$admin_id = $_SESSION['std_id'] ?? 'AD001';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id   = strtoupper(trim($_POST['staff_id']));
    $name       = strtoupper(trim($_POST['name']));
    $email      = strtolower(trim($_POST['email']));
    $department = $_POST['department'];
    $phone      = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

    try {
        $pdo->beginTransaction();

        // 1. Conflict Check (DBA Best Practice)
        $check = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? OR email = ?");
        $check->execute([$staff_id, $email]);
        if ($check->rowCount() > 0) {
            throw new Exception("NEURAL CONFLICT: Identifier $staff_id or Email $email already registered.");
        }

        // 2. Insert Parent: users
        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Staff')");
        $stmtUser->execute([$staff_id, $name, $email, $password]);

        // 3. Insert Subtype: staff
        $stmtStaff = $pdo->prepare("INSERT INTO staff (staff_id, department, phone_num) VALUES (?, ?, ?)");
        $stmtStaff->execute([$staff_id, $department, $phone]);

        // 4. Record to Audit Trail (This is what was missing in your screenshot)
        $details = "REGISTRATION: New Staff Entity [$name] initialized in Unit: $department";
        $stmtAudit = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'STAFF_REGISTERED', ?, ?)");
        $stmtAudit->execute([$admin_id, $staff_id, $details]);

        $pdo->commit();
        header("Location: admin_add_staff.php?status=success&id=$staff_id");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "SYSTEM ABORT: " . $e->getMessage();
    }
}
?>
<!-- [Include your HTML Form here - Use the Orbitron styling from File 1] -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Staff Initialization</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #080a0f; 
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(0, 212, 255, 0.2);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%);
            color: white; 
            font-family: 'Rajdhani', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .glass-card { 
            background: rgba(8, 10, 15, 0.8); 
            backdrop-filter: blur(15px); 
            border: 1px solid var(--glass-border); 
            border-radius: 30px; 
            padding: 50px;
        }

        .form-control {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 12px;
            padding: 12px 20px;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.6);
            border-color: var(--lesbot-cyan);
            color: white;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .btn-neural {
            background: transparent;
            border: 1px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan);
            font-family: 'Orbitron';
            font-weight: 900;
            font-size: 0.8rem;
            letter-spacing: 2px;
            transition: 0.3s;
            border-radius: 15px;
        }

        .btn-neural:hover {
            background: var(--lesbot-cyan);
            color: var(--obsidian);
            box-shadow: 0 0 25px var(--lesbot-cyan);
            transform: translateY(-2px);
        }

        label { 
            color: var(--lesbot-cyan); 
            font-family: 'Orbitron'; 
            font-size: 0.7rem; 
            letter-spacing: 1.5px; 
            font-weight: 700; 
            margin-bottom: 8px;
        }

        .alert-neural {
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .glass-card { background: rgba(8, 10, 15, 0.8); border: 1px solid var(--glass-border); border-radius: 30px; padding: 40px; }

    </style>
</head>
<body>
    
<nav class="neural-nav">
    <a href="admin_dashboard.php" class="text-white text-decoration-none" style="font-family: 'Orbitron'; font-weight: 900;">LESBOT <span style="color: var(--lesbot-cyan);">•</span></a>
    <div class="d-flex gap-3">
        <a href="admin_dashboard.php" class="text-white-50 text-decoration-none small font-orbitron">DASHBOARD</a>
        <a href="admin_audit_trail.php" class="text-white-50 text-decoration-none small font-orbitron">AUDIT TRAIL</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="glass-card shadow-lg">
                <h2 class="text-center mb-5" style="font-family: 'Orbitron'; font-weight: 900;">REGISTER <span style="color: var(--lesbot-cyan);">STAFF</span></h2>
                
<!-- HTML Alert Section Fix -->
<?php if(!empty($success_msg)): ?>
    <div class="alert bg-dark border-success text-success small mb-4"><?= $success_msg ?></div>
<?php endif; ?>

<?php if(!empty($error_msg)): ?>
    <div class="alert bg-dark border-danger text-danger small mb-4"><?= $error_msg ?></div>
<?php endif; ?>

                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label>STAFF IDENTIFIER (ID)</label>
                            <input type="text" name="staff_id" class="form-control text-uppercase" placeholder="E.G. STF002" required>
                        </div>
                        <div class="col-md-6">
                            <label>FULL LEGAL NAME</label>
                            <input type="text" name="name" class="form-control" placeholder="AS PER IC/PASSPORT" required>
                        </div>
                        <div class="col-md-12">
                            <label>OFFICIAL EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control" placeholder="staffname@utem.edu.my" required>
                        </div>
                        <div class="col-md-6">
                            <label>ASSIGNED DEPARTMENT</label>
                            <select name="department" class="form-control" required style="background-image: none;">
                                <option value="" disabled selected>-- Select Unit --</option>
                                <option value="Maintenance">Maintenance Unit</option>
                                <option value="Security">Security Unit</option>
                                <option value="Management">Management Unit</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>CONTACT NUMBER</label>
                            <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXX" required>
                        </div>
                        <div class="col-md-12">
                            <label>ACCESS KEY (SECURE PASSWORD)</label>
                            <input type="password" name="password" class="form-control" placeholder="MINIMUM 8 CHARACTERS" required>
                        </div>
                    </div>
                    
                    <div class="mt-5 d-grid gap-3">
                        <button type="submit" class="btn btn-neural py-3">INITIALIZE STAFF RECORD</button>
                        <a href="admin_dashboard.php" class="text-center text-white-50 text-decoration-none small hover-cyan">
                            <i class="bi bi-arrow-left me-1"></i> ABORT AND RETURN TO COMMAND
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

</body>
</html>