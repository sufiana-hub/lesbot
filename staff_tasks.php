<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // Fetch tasks
    $query = "SELECT mr.*, u.name as student_name, u.email as student_email, s.room_number, c.category_name 
              FROM maintenance_request mr
              JOIN users u ON mr.student_id = u.user_id
              JOIN student s ON u.user_id = s.matric_number
              JOIN category c ON mr.category_id = c.category_id
              WHERE mr.assigned_staff_id = ?
              ORDER BY mr.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staff_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) { die("Neural Link Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Field Operations</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.3); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 120px; }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.95); backdrop-filter: blur(20px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 12px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .system-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 35px; padding: 50px; }

        .task-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 25px; margin-bottom: 15px; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.65rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        
        /* MODAL FIXES */
        .modal { z-index: 9999 !important; }
        .modal-backdrop { z-index: 9998 !important; }
        .modal-content { background: #0B0E14 !important; border: 1px solid var(--lesbot-cyan) !important; border-radius: 25px !important; color: white !important; }
        .detail-box { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); }
        
        .btn-neural { font-family: 'Orbitron'; font-weight: 900; font-size: 0.7rem; padding: 12px 20px; border-radius: 10px; text-decoration: none; display: inline-block; text-align: center; border: none; transition: 0.3s; }
        .btn-res { background: #198754; color: white; }
        .btn-hld { background: #0dcaf0; color: #000; }
        .btn-rej { background: #dc3545; color: white; }
        .btn-res:hover, .btn-hld:hover, .btn-rej:hover { filter: brightness(1.2); transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="staff_dashboard.php" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT STAFF</a>
    <div class="d-flex gap-4">
        <a href="staff_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.6;">DASHBOARD</a>
        <a href="staff_tasks.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">FIELD OPS</a>
    </div>
</nav>

<div class="container">
    <div class="system-container">
        <h1 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900; letter-spacing:5px;">FIELD <span style="color:var(--lesbot-cyan);">OPERATIONS</span></h1>

        <?php if(empty($tasks)): ?>
            <div class="text-center py-5">NO ASSIGNED TASKS DETECTED</div>
        <?php else: ?>
            <?php foreach($tasks as $t): ?>
                <div class="task-card row align-items-center">
                    <div class="col-md-2">
                        <p class="label-neon mb-1">ID</p>
                        <a href="#" class="fw-bold text-info" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">#<?= $t['request_id'] ?></a>
                    </div>
                    <div class="col-md-3">
                        <p class="label-neon mb-1">STUDENT</p>
                        <div class="fw-bold"><?= $t['student_name'] ?></div>
                    </div>
                    <div class="col-md-3">
                        <p class="label-neon mb-1">CATEGORY</p>
                        <div class="small"><?= $t['category_name'] ?></div>
                    </div>
                    <div class="col-md-2">
                        <span class="badge bg-primary"><?= strtoupper($t['status']) ?></span>
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-outline-info btn-sm font-orbitron" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">ANALYZE</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL REGISTRY - MOVED TO THE BOTTOM TO PREVENT CLICK BLOCKING -->
<?php foreach($tasks as $t): ?>
<div class="modal fade" id="modal-<?= $t['request_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-orbitron text-info">CASE ANALYSIS #<?= $t['request_id'] ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 text-start">
                    <div class="col-md-6 border-end border-secondary">
                        <label class="label-neon">STUDENT IDENTITY</label>
                        <div class="detail-box mb-3">
                            <p class="mb-1 text-white-50 small">NAME:</p>
                            <p class="fw-bold"><?= $t['student_name'] ?></p>
                            <p class="mb-1 text-white-50 small">ROOM:</p>
                            <p class="fw-bold text-info"><?= $t['room_number'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="label-neon">NEURAL DESCRIPTION</label>
                        <div class="detail-box mb-3" style="min-height: 100px;">
                            <p class="small italic">"<?= nl2br(htmlspecialchars($t['description'])) ?>"</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-3 pb-5">
                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Completed" class="btn-neural btn-res">RESOLVE</a>
                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=On Hold" class="btn-neural btn-hld">ON HOLD</a>
                <?php if($t['rejected_count'] < 3): ?>
                    <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Rejected" class="btn-neural btn-rej">REJECT (<?= $t['rejected_count'] ?>/3)</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'chatbot_component.php'; ?>

</body>
</html>