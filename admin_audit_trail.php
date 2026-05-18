<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    $query = "SELECT at.*, u.name as admin_name 
              FROM system_audit_trail at
              LEFT JOIN users u ON at.admin_id COLLATE utf8mb4_general_ci = u.user_id COLLATE utf8mb4_general_ci
              ORDER BY at.created_at DESC";
    $stmt = $pdo->query($query);
    $audit_logs = $stmt->fetchAll();
} catch (PDOException $e) { die("Neural Link Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Neural Audit Trail</title>

        <!-- Paste the Google tag here -->
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />

    <!-- site identity tags -->
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 120px; min-height: 100vh; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; border-radius: 20px; transition: 0.3s; }
        .nav-links-container a.active { color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1); }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 35px; padding: 50px; backdrop-filter: blur(15px); }
        .audit-row { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--glass-border); border-radius: 15px; padding: 20px; margin-bottom: 12px; transition: 0.3s; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        
        /* INTELLIGENT ROLE COLORS */
        .id-student { color: var(--lesbot-cyan); font-weight: 900; font-family: 'Orbitron'; text-shadow: 0 0 10px rgba(0, 212, 255, 0.3); }
        .id-staff { color: #ff9d00; font-weight: 900; font-family: 'Orbitron'; text-shadow: 0 0 10px rgba(255, 157, 0, 0.3); }

        .btn-export { border: 1px solid #28a745; color: #28a745; background: transparent; font-family: 'Orbitron'; font-size: 0.6rem; border-radius: 50px; padding: 8px 25px; transition: 0.3s; text-decoration: none; }
        .btn-export:hover { background: #28a745; color: white; box-shadow: 0 0 15px #28a745; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" class="text-white text-decoration-none" style="font-family: 'Orbitron'; font-weight: 900;">LESBOT <span style="color: var(--lesbot-cyan);">•</span></a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php">OVERVIEW</a></li>
        <li><a href="manage_accounts.php">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php">MAINTENANCE</a></li>
        <li><a href="admin_audit_trail.php" class="active">AUDIT</a></li>
        <li><a href="export_data.php">EXPORTS</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 6px; margin: 0;">NEURAL <span style="color: var(--lesbot-cyan);">AUDIT TRAIL</span></h1>
                <p class="small text-white-50 mt-2">REAL-TIME ACTIVITY MONITORING • SYSTEM ACCOUNTABILITY VERIFIED</p>
            </div>
            <!-- EXPORT BUTTON -->
            <a href="export_purged_logs.php" class="btn-export">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> EXPORT PURGE HISTORY
            </a>
        </div>

        <div class="row align-items-center mb-3 px-4">
            <div class="col-md-2 label-neon">TIMESTAMP</div>
            <div class="col-md-3 label-neon">ADMIN ENTITY</div>
            <div class="col-md-2 label-neon">PURGED ID</div>
            <div class="col-md-5 label-neon">DETAILED TRACE</div>
        </div>

        <?php foreach($audit_logs as $log): ?>
            <div class="audit-row">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <div class="text-info font-orbitron" style="font-size: 0.75rem;"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                        <div class="text-white-50 small" style="font-family: 'Orbitron'; font-size: 0.6rem;"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                    </div>
                    <div class="col-md-3">
                        <p class="m-0 font-orbitron" style="font-size: 0.7rem;"><?= htmlspecialchars($log['admin_name'] ?? 'SYSTEM_CORE') ?></p>
                        <small class="text-white-50">ID: <?= $log['admin_id'] ?></small>
                    </div>
                    <div class="col-md-2">
                        <?php if($log['action_type'] == 'ENTITY_PURGE'): ?>
                            <?php 
                                // Detect role from the log text we cleverly stored
                                $isStaff = (strpos($log['action_details'], 'ROLE: Staff') !== false);
                                $colorClass = $isStaff ? 'id-staff' : 'id-student';
                            ?>
                            <span class="<?= $colorClass ?>"><?= $log['target_entity'] ?></span>
                        <?php else: ?>
                            <span class="text-white-50 small">--</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <span class="badge border border-info text-info mb-1" style="font-size: 0.5rem; font-family: 'Orbitron';"><?= $log['action_type'] ?></span>
                        <p class="m-0 text-white-50 small" style="font-style: italic; font-size: 0.75rem;">"<?= htmlspecialchars($log['action_details']) ?>"</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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