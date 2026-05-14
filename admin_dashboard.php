<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // --- LIVE DATABASE ALIGNMENT ---
    // 1. Total Active: The main source of truth (Users table)
    $total_active = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // 2. Total Purged: Unique IDs removed (Audit table)
    $purged_count = $pdo->query("SELECT COUNT(DISTINCT target_entity) FROM system_audit_trail WHERE action_type = 'ENTITY_PURGE'")->fetchColumn();

    // 3. DBA BREAKDOWN (Math must add up to $total_active)
    $adm_c = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'")->fetchColumn();
    $stf_c = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $std_c = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();

    // 4. SECONDARY METRICS
    $pending_tasks = $pdo->query("SELECT COUNT(*) FROM maintenance_request WHERE status = 'Pending'")->fetchColumn();
    $total_unpaid  = $pdo->query("SELECT IFNULL(SUM(amount), 0) FROM student_penalties WHERE is_paid = 0")->fetchColumn();

} catch (PDOException $e) { die("Neural Link Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Neural Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 100px; min-height: 100vh; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; border-radius: 20px; transition: 0.3s; }
        .nav-links-container a.active { color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1); }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; backdrop-filter: blur(10px); }
        .glass-card { background: rgba(0, 0, 0, 0.3); border: 1px solid var(--glass-border); border-radius: 25px; padding: 30px; text-align: center; height: 100%; transition: 0.3s; }
        .glass-card:hover { border-color: var(--lesbot-cyan); transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 212, 255, 0.1); }
        .stat-value { font-family: 'Orbitron'; font-size: 3.5rem; font-weight: 900; color: var(--lesbot-cyan); text-shadow: 0 0 20px var(--lesbot-cyan); line-height: 1; }
        .btn-trigger { background: transparent; border: 1px solid var(--lesbot-cyan); color: var(--lesbot-cyan); font-family: 'Orbitron'; font-size: 0.75rem; padding: 15px; border-radius: 12px; text-decoration: none; display: block; text-align: center; transition: 0.3s; font-weight: 700; letter-spacing: 1px; }
        .btn-trigger:hover { background: var(--lesbot-cyan); color: var(--obsidian); box-shadow: 0 0 25px var(--lesbot-cyan); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" class="text-white text-decoration-none" style="font-family: 'Orbitron'; font-weight: 900;">LESBOT <span style="color: var(--lesbot-cyan);">•</span></a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php" class="active">OVERVIEW</a></li>
        <li><a href="manage_accounts.php">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php">MAINTENANCE</a></li>
        <li><a href="admin_penalties.php">PENALTIES</a></li>
        <li><a href="admin_audit_trail.php">AUDIT</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-5">
    <div class="system-container">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-secondary pb-4">
            <div>
                <h1 style="font-family: 'Orbitron'; font-weight: 900; margin: 0;">COMMAND <span style="color: var(--lesbot-cyan);">CENTER</span></h1>
                <p class="text-info small" style="letter-spacing: 2px;">NEURAL INTERFACE ACTIVE</p>
            </div>
            <div class="text-end">
                <span class="badge rounded-pill bg-dark border border-info px-3 py-2 text-info mb-2" style="font-size: 0.6rem; font-family: 'Orbitron';">ADMIN ONLINE</span>
                <p class="m-0 fw-bold"><?= strtoupper($_SESSION['full_name'] ?? 'ADMIN CORE'); ?></p>
                <p class="small text-white-50 m-0">#<?= $_SESSION['std_id']; ?></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card">
                    <p class="text-uppercase small mb-3" style="letter-spacing: 2px; opacity: 0.6;">System Population</p>
                    <div class="stat-value"><?= $total_active ?></div>
                    <div class="mt-3 border-top border-secondary pt-3 d-flex flex-column gap-1">
                        <div class="d-flex justify-content-center gap-3">
                            <small class="text-info font-orbitron" style="font-size: 0.55rem;">ACTIVE: <?= $total_active ?></small>
                            <small class="text-danger font-orbitron" style="font-size: 0.55rem;">PURGED: <?= $purged_count ?></small>
                        </div>
                        <small class="text-white-50 font-orbitron" style="font-size: 0.5rem; letter-spacing: 1px;">
                            A:<?= $adm_c ?> | S:<?= $stf_c ?> | ST:<?= $std_c ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="glass-card">
                    <p class="text-uppercase small mb-3" style="letter-spacing: 2px; opacity: 0.6;">Pending Tickets</p>
                    <div class="stat-value text-warning" style="text-shadow: 0 0 15px rgba(255, 193, 7, 0.5);"><?= $pending_tasks; ?></div>
                    <div class="mt-3 border-top border-secondary pt-3">
                        <small class="text-white-50 font-orbitron" style="font-size: 0.55rem;">AWAITING SYSTEM RESPONSE</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="glass-card">
                    <p class="text-uppercase small mb-3" style="letter-spacing: 2px; opacity: 0.6;">Total Debt</p>
                    <div class="stat-value text-danger" style="text-shadow: 0 0 15px rgba(220, 53, 69, 0.5);">RM <?= number_format($total_unpaid, 2); ?></div>
                    <div class="mt-3 border-top border-secondary pt-3">
                        <small class="text-white-50 font-orbitron" style="font-size: 0.55rem;">UNPAID PENALTY ACCRUAL</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4">
            <h6 class="text-uppercase mb-4" style="letter-spacing: 3px; font-family: 'Orbitron'; font-size: 0.7rem; opacity: 0.5;">Master Triggers</h6>
            <div class="row g-3">
                <div class="col-md-3"><a href="admin_add_staff.php" class="btn btn-trigger">REGISTER STAFF</a></div>
                <div class="col-md-3"><a href="manage_accounts.php" class="btn btn-trigger">ARCHIVE DATA</a></div>
                <div class="col-md-3"><a href="export_data.php" class="btn btn-trigger">GENERATE REPORTS</a></div>
                <div class="col-md-3"><a href="admin_penalties.php" class="btn btn-trigger">ISSUE PENALTY</a></div>
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