<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

// 2. PROCESS PENALTY ISSUANCE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric = $_POST['matric'];
    $type_id = $_POST['type_id'];
    $amount = $_POST['amount'];
    
    try {
        // Insert into your 'student_penalties' table
        $stmt = $pdo->prepare("INSERT INTO student_penalties (matric_number, penalty_type_id, amount, date_issued, is_paid) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$matric, $type_id, $amount]);
        $success = "SYSTEM UPDATE: Penalty successfully synchronized with student ledger.";
    } catch (PDOException $e) {
        $error = "Transmission Error: " . $e->getMessage();
    }
}

// 3. READ: Fetch Violation Types and Students
$types = $pdo->query("SELECT * FROM penalty_types")->fetchAll();
$students = $pdo->query("SELECT matric_number, room_number FROM student")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Penalty Authorization</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --lesbot-red: #ff4d4d;
            --obsidian: #080a0f; 
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 77, 77, 0.2);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(255, 77, 77, 0.05) 0%, transparent 80%);
            color: #FFFFFF; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0;
            padding-top: 100px;
            min-height: 100vh;
        }

        /* --- Floating Navigation --- */
        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.8);
            backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px; padding: 10px 30px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .nav-brand { font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan); text-decoration: none; }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { 
            color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; 
            font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; border-radius: 20px; transition: 0.3s;
        }
        .nav-links-container a:hover, .nav-links-container a.active { color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1); }

        /* --- Penalty Content Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 50px; backdrop-filter: blur(10px);
            max-width: 800px; margin: 0 auto;
        }

        .form-control, .form-select {
            background: rgba(0,0,0,0.4); border: 1px solid rgba(255, 77, 77, 0.3);
            color: white; border-radius: 12px; padding: 12px 20px;
            font-family: 'Rajdhani'; transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(0,0,0,0.6); border-color: var(--lesbot-red);
            color: white; box-shadow: 0 0 15px rgba(255, 77, 77, 0.2);
        }

        .input-label { 
            font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); 
            letter-spacing: 1.5px; margin-bottom: 8px; font-weight: 700;
        }

        .btn-neural-authorize {
            background: var(--lesbot-red); color: white;
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.85rem;
            letter-spacing: 2px; padding: 18px; border: none; border-radius: 15px;
            transition: 0.3s; width: 100%; box-shadow: 0 5px 15px rgba(255, 77, 77, 0.3);
        }
        .btn-neural-authorize:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 77, 77, 0.5);
        }

        .alert-neural {
            background: rgba(0, 212, 255, 0.1); border: 1px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan); font-family: 'Rajdhani'; font-weight: 700;
            border-radius: 12px; margin-bottom: 30px;
        }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php">OVERVIEW</a></li>
        <li><a href="manage_accounts.php">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php">MAINTENANCE</a></li>
        <li><a href="admin_penalties.php" class="active">PENALTIES</a></li>
        <li><a href="export_data.php">EXPORTS</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Orbitron'; font-weight: 900; margin: 0; color: var(--lesbot-red);">ISSUE <span style="color: #fff;">PENALTY</span></h2>
            <p class="text-white-50 small mt-2" style="letter-spacing: 2px;">AUTHORIZATION REQUIRED • FINANCIAL PROTOCOL ACTIVE</p>
        </div>

        <?php if(isset($success)): ?>
            <div class="alert alert-neural text-center py-3"><i class="bi bi-shield-check me-2"></i> <?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="input-label">TARGET STUDENT IDENTITY</label>
                <select name="matric" class="form-select" required>
                    <option value="" disabled selected>Select Matric Entity...</option>
                    <?php foreach($students as $s): ?>
                        <option value="<?= $s['matric_number'] ?>"><?= $s['matric_number'] ?> (Room <?= $s['room_number'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

<div class="mb-4">
    <label class="input-label">VIOLATION CLASSIFICATION (DORM RULES)</label>
    <select name="type_id" class="form-select" required>
        <option value="" disabled selected>Identify Violation Type...</option>
        <?php foreach($types as $t): ?>
            <option value="<?= $t['penalty_type_id'] ?>">
                [CODE: <?= str_pad($t['penalty_type_id'], 3, '0', STR_PAD_LEFT) ?>] <?= $t['description'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

            <div class="mb-5">
                <label class="input-label">FINES AMOUNT (RM)</label>
                <input type="number" name="amount" class="form-control" step="0.01" placeholder="50.00" required>
                <div class="form-text text-white-50 small mt-2">Specify the numeric value for the penalty settlement.</div>
            </div>

            <button type="submit" class="btn-neural-authorize">
                <i class="bi bi-shield-exclamation me-2"></i> INITIALIZE PENALTY
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="text-decoration-none text-white-50 small hover-cyan">
                <i class="bi bi-arrow-left me-1"></i> Abort Authorization
            </a>
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

<?php include 'chatbot_component.php'; ?>

</body>
</html>