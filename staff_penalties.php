<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Staff authorization only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); 
    exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // 2. FETCH STAFF PROFILE: Layered Box Data
    $s_stmt = $pdo->prepare("SELECT u.name, u.email, st.department 
                             FROM users u 
                             JOIN staff st ON u.user_id = st.staff_id 
                             WHERE u.user_id = ?");
    $s_stmt->execute([$staff_id]);
    $staff = $s_stmt->fetch();

    // 3. READ: Fetch penalties assigned to this staff member
    // Note: Column name is 'issued_by' based on your SQL schema
    $query = "SELECT sp.*, u.name as student_name, pt.description as violation_type
              FROM student_penalties sp
              JOIN users u ON sp.matric_number = u.user_id
              JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
              WHERE sp.issued_by = ? 
              ORDER BY sp.is_paid ASC, sp.date_issued DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staff_id]);
    $assigned_penalties = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Tactical Financials</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
            color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 120px; min-height: 100vh;
        }

        /* --- Non-Plain Tactical Navigation cluster --- */
        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85);
            backdrop-filter: blur(25px); border: 1px solid var(--glass-border);
            border-radius: 50px; padding: 10px 30px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .nav-variant {
            color: rgba(255,255,255,0.5); text-decoration: none; font-family: 'Orbitron';
            font-size: 0.65rem; letter-spacing: 2px; padding: 10px 15px; border-radius: 12px; transition: 0.3s;
        }
        .nav-variant.active { background: var(--lesbot-cyan); color: var(--obsidian); font-weight: 900; }

        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 35px; padding: 50px; backdrop-filter: blur(15px);
        }

        /* --- Aligned Profile Section (Box-inside-Box) --- */
        .profile-layer-outer {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px; margin-bottom: 40px;
        }
        .profile-row { border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding: 10px 0; display: flex; align-items: center; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; width: 30%; }
        .value-white { font-family: 'Orbitron'; font-size: 0.8rem; color: #FFF; text-transform: uppercase; }

        /* --- Financial Ledger Cards --- */
        .ledger-card {
            background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 15px; padding: 20px; margin-bottom: 15px; transition: 0.3s;
        }
        .ledger-card:hover { border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.05); }

        /* --- SLEEK EXTERNAL RETURN ACTION --- */
        .external-return {
            margin-top: 40px; text-align: center; padding-bottom: 40px;
        }
        .btn-neural-back {
            color: rgba(255, 255, 255, 0.5); text-decoration: none; font-family: 'Orbitron';
            font-size: 0.75rem; letter-spacing: 4px; transition: 0.3s; display: inline-flex; align-items: center;
        }
        .btn-neural-back:hover { color: var(--lesbot-cyan); text-shadow: 0 0 10px var(--lesbot-cyan); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="#" class="nav-variant active" style="font-weight: 900; letter-spacing: 3px;">LESBOT STAFF</a>
    <div class="d-flex gap-2">
        <a href="staff_dashboard.php" class="nav-variant">DASHBOARD</a>
        <a href="staff_tasks.php" class="nav-variant">FIELD OPS</a>
        <a href="staff_penalties.php" class="nav-variant active">FINANCIALS</a>
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        
        <div class="profile-layer-outer">
            <h6 class="mb-4" style="font-family: 'Orbitron'; font-size: 0.55rem; color: rgba(255,255,255,0.3); letter-spacing: 3px;">AUTHENTICATED TECHNICIAN</h6>
            <div class="profile-row">
                <div class="label-neon">NAME</div>
                <div class="value-white"><?= htmlspecialchars($staff['name']) ?></div>
            </div>
            <div class="profile-row" style="border:none;">
                <div class="label-neon">ASSIGNED UNIT</div>
                <div class="value-white text-info"><?= htmlspecialchars($staff['department']) ?></div>
            </div>
        </div>

        <div class="text-center mb-5">
            <h1 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 5px; margin: 0;">FINANCIAL <span style="color: #ff4d4d;">AUDIT</span></h1>
            [cite_start]<p class="small text-white-50 mt-2">RECEIPT CONFIRMATION PROTOCOL [cite: 2984]</p>
        </div>

        <?php if (empty($assigned_penalties)): ?>
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-shield-slash fs-1 text-danger"></i>
                [cite_start]<p class="mt-3 font-orbitron">NO ASSIGNED STUDENT PENALTIES [cite: 2981]</p>
            </div>
        <?php else: ?>
            <?php foreach($assigned_penalties as $p): ?>
                <div class="ledger-card shadow">
                    <div class="row align-items-center text-center text-md-start">
                        <div class="col-md-2">
                            <p class="label-neon mb-1">RECORD ID</p>
                            <p class="value-white text-info" style="font-size: 0.7rem;">#<?= $p['penalty_id'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="label-neon mb-1">STUDENT ENTITY</p>
                            <p class="value-white" style="font-size: 0.75rem;"><?= htmlspecialchars($p['student_name']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p class="label-neon mb-1">SETTLEMENT</p>
                            <span class="badge <?= ($p['is_paid'] == 1) ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-family: 'Orbitron'; font-size: 0.5rem; padding: 5px 12px;">
                                <?= ($p['is_paid'] == 1) ? 'PAID' : 'OUTSTANDING' ?>
                            </span>
                        </div>
                        <div class="col-md-3 text-md-end mt-3 mt-md-0">
                            <?php if($p['is_paid'] == 0): ?>
                                <a href="staff_confirm_payment.php?id=<?= $p['penalty_id'] ?>" class="btn btn-sm btn-outline-info rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.55rem;">CONFIRM RECEIPT</a>
                            <?php else: ?>
                                <span class="text-success small fw-bold font-orbitron"><i class="bi bi-shield-check"></i> AUDITED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="external-return">
        <a href="staff_dashboard.php" class="btn-neural-back">
            [cite_start]<i class="bi bi-cpu-fill me-3"></i> RETURN TO HUB COMMAND [cite: 2985]
        </a>
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