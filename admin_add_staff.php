<?php
/**
 * LESBOT STAFF INITIALIZATION
 * DBA CAP PROTOCOL: STRICT PK & UNIQUE IDENTIFIER ENFORCEMENT
 */
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

$admin_id = $_SESSION['std_id'] ?? 'AD001';
$success_msg = "";
$error_msg = "";


// 2. HANDLE SUCCESS REDIRECT LOGIC
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $new_id = $_GET['id'];
    $success_msg = "NEURAL LINK ESTABLISHED: Staff Entity [$new_id] successfully archived in the system.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DATA NORMALIZATION (Force Uppercase for IDs)
    $staff_id   = strtoupper(trim($_POST['staff_id']));
    $name       = strtoupper(trim($_POST['name']));
    $email      = strtolower(trim($_POST['email']));
    $department = $_POST['department'];
    $phone      = $_POST['phone'];
    $password   = password_hash($_POST['password'], PASSWORD_ARGON2ID);

    try {
        $pdo->beginTransaction();

        /** 
         * 3. DBA CONFLICT CHECK (The "Strict PK" Rule)
         * We use BINARY to ensure exact character matching and check BOTH tables.
         */
        $check = $pdo->prepare("SELECT user_id, email FROM users WHERE BINARY user_id = ? OR BINARY email = ? LIMIT 1");
        $check->execute([$staff_id, $email]);
        $existing = $check->fetch();

        if ($existing) {
            if (strtoupper($existing['user_id']) === $staff_id) {
                throw new Exception("INTEGRITY FAULT: Staff ID [$staff_id] is already assigned to another entity.");
            } else {
                throw new Exception("DATA CONFLICT: Email address [$email] is already linked to an existing profile.");
            }
        }

        // 4. INSERT PARENT ENTITY (users)
// 2. Insert Parent: users (Now includes the reset flag)
$stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role, requires_reset) 
                           VALUES (?, ?, ?, ?, 'Staff', 1)");
$stmtUser->execute([$staff_id, $name, $email, $password]);

        // 5. INSERT SUBTYPE ENTITY (staff)
        $stmtStaff = $pdo->prepare("INSERT INTO staff (staff_id, department, phone_num) VALUES (?, ?, ?)");
        $stmtStaff->execute([$staff_id, $department, $phone]);

        // 6. SYSTEM AUDIT TRAIL LOGGING
        $details = "REGISTRATION: New Staff Entity [$name] initialized in Unit: $department";
        $stmtAudit = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'STAFF_REGISTERED', ?, ?)");
        $stmtAudit->execute([$admin_id, $staff_id, $details]);

        $pdo->commit();
        
        // Refresh with success flag
        header("Location: admin_add_staff.php?status=success&id=$staff_id");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "SYSTEM ABORT: " . $e->getMessage();
    }
}
?>

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
            --glass-border: rgba(0, 212, 255, 0.2);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%);
            color: white; font-family: 'Rajdhani', sans-serif;
            min-height: 100vh; display: flex; align-items: center; padding: 60px 0;
        }

        .glass-card { 
            background: rgba(8, 10, 15, 0.85); backdrop-filter: blur(20px); 
            border: 1px solid var(--glass-border); border-radius: 40px; padding: 50px; 
        }

        .form-control {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            color: white; border-radius: 12px; padding: 14px 20px; transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.6); border-color: var(--lesbot-cyan);
            color: white; box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        }

        .btn-neural {
            background: transparent; border: 2px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan); font-family: 'Orbitron'; font-weight: 900;
            font-size: 0.9rem; letter-spacing: 3px; padding: 18px;
            transition: 0.4s; border-radius: 15px; width: 100%;
        }

        .btn-neural:hover {
            background: var(--lesbot-cyan); color: #000;
            box-shadow: 0 0 30px var(--lesbot-cyan); transform: translateY(-3px);
        }

        label { color: var(--lesbot-cyan); font-family: 'Orbitron'; font-size: 0.65rem; letter-spacing: 2px; font-weight: 700; margin-bottom: 10px; }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .alert-custom { border-radius: 12px; border: none; font-weight: bold; font-size: 0.85rem; }

            /* --- Chatbot Styling Refined --- */
    .glass-card {
        background: rgba(8, 10, 15, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.8);
    }
    </style>
</head>
<body>
    
<nav class="neural-nav">
    <a href="admin_dashboard.php" style="color:var(--lesbot-cyan); text-decoration:none; font-family:'Orbitron'; font-weight:900;">LESBOT •</a>
    <div class="d-flex gap-4">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.7;">DASHBOARD</a>
        <a href="admin_audit_trail.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.7;">AUDIT TRAIL</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card shadow-lg">
                <h2 class="text-center mb-5" style="font-family: 'Orbitron'; font-weight: 900; letter-spacing:5px;">REGISTER <span style="color: var(--lesbot-cyan);">STAFF</span></h2>
                
                <!-- Feedback Section -->
                <?php if($success_msg): ?>
                    <div class="alert alert-success bg-dark border-success text-success alert-custom mb-4">
                        <i class="bi bi-shield-check me-2"></i> <?= $success_msg ?>
                    </div>
                <?php endif; ?>

                <?php if($error_msg): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger alert-custom mb-4">
                        <i class="bi bi-shield-exclamation me-2"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label>STAFF IDENTIFIER (UNIQUE PK)</label>
                            <input type="text" name="staff_id" class="form-control" placeholder="E.G. STF002" required>
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
                            <label>ASSIGNED UNIT</label>
                            <select name="department" class="form-control" required>
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
                    
                    <div class="mt-5">
                        <button type="submit" class="btn btn-neural">INITIALIZE STAFF RECORD</button>
                    </div>

                    <div class="text-center mt-4">
                        <a href="admin_dashboard.php" style="color:rgba(255,255,255,0.4); text-decoration:none; font-size:0.75rem; letter-spacing:2px; font-family:'Orbitron';">
                            ← ABORT AND RETURN TO COMMAND
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>


</body>
</html>