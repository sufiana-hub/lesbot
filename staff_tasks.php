<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
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
    <title>LesBot | Field Ops</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.3); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 120px; overflow-x: hidden; }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.95); backdrop-filter: blur(20px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 12px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 2000; }
        .system-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 35px; padding: 50px; }

        .task-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 25px; margin-bottom: 15px; transition: 0.3s; }
        .task-card:hover { border-color: var(--lesbot-cyan); box-shadow: 0 0 20px rgba(0, 212, 255, 0.1); }

        /* --- MODAL FIXES --- */
        .modal-backdrop { background-color: rgba(0, 0, 0, 0.8) !important; }
        .modal-content { background: #0B0E14 !important; border: 1px solid var(--lesbot-cyan) !important; border-radius: 25px !important; color: white !important; box-shadow: 0 0 50px rgba(0, 212, 255, 0.2); }
        .detail-box { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); }
        .label-neon { font-family: 'Orbitron'; font-size: 0.65rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; margin-bottom: 5px; }
        
        /* High-Level Action Buttons */
        .btn-neural { font-family: 'Orbitron'; font-weight: 900; font-size: 0.75rem; letter-spacing: 1px; padding: 12px 25px; border-radius: 10px; transition: 0.3s; border: none; }
        .btn-res { background: #198754; color: white; } .btn-res:hover { box-shadow: 0 0 20px #198754; }
        .btn-hld { background: #0dcaf0; color: #000; } .btn-hld:hover { box-shadow: 0 0 20px #0dcaf0; }
        .btn-rej { background: #dc3545; color: white; } .btn-rej:hover { box-shadow: 0 0 20px #dc3545; }

        .id-trigger { color: var(--lesbot-cyan); font-family: 'Orbitron'; font-weight: 900; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="#" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT STAFF</a>
    <div class="d-flex gap-4">
        <a href="staff_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.6;">DASHBOARD</a>
        <a href="staff_tasks.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">FIELD OPS</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="system-container">
        <h1 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900; letter-spacing:5px;">FIELD <span style="color:var(--lesbot-cyan);">OPERATIONS</span></h1>

        <?php if(empty($tasks)): ?>
            <div class="text-center py-5">NO ASSIGNED TASKS</div>
        <?php else: ?>
            <?php foreach($tasks as $t): ?>
                <div class="task-card row align-items-center">
                    <div class="col-md-2"><p class="label-neon">ID</p><a class="id-trigger" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">#<?= $t['request_id'] ?></a></div>
                    <div class="col-md-3"><p class="label-neon">STUDENT</p><div class="fw-bold"><?= $t['student_name'] ?></div><span class="small opacity-50"><?= $t['room_number'] ?></span></div>
                    <div class="col-md-3"><p class="label-neon">CATEGORY</p><div class="small"><?= $t['category_name'] ?></div></div>
                    <div class="col-md-2"><p class="label-neon">STATUS</p><span class="badge bg-info"><?= strtoupper($t['status']) ?></span></div>
                    <div class="col-md-2 text-end"><button class="btn btn-outline-info btn-sm font-orbitron" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">ANALYZE</button></div>
                </div>

                <!-- THE POPUP (MODAL) -->
                <div class="modal fade" id="modal-<?= $t['request_id'] ?>" tabindex="-1" style="z-index: 3000;">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-secondary">
                                <h5 class="modal-title font-orbitron text-info">CASE ANALYSIS #<?= $t['request_id'] ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6 border-end border-secondary">
                                        <label class="label-neon">STUDENT IDENTITY</label>
                                        <div class="detail-box mb-3">
                                            <p class="mb-1 text-white-50 small">NAME:</p>
                                            <p class="fw-bold fs-5"><?= $t['student_name'] ?></p>
                                            <p class="mb-1 text-white-50 small">ROOM ADDRESS:</p>
                                            <p class="fw-bold text-info"><?= $t['room_number'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-neon">NEURAL DESCRIPTION</label>
                                        <div class="detail-box mb-3" style="min-height: 120px;">
                                            <p class="small italic">"<?= nl2br(htmlspecialchars($t['description'])) ?>"</p>
                                        </div>
                                        <div class="d-flex justify-content-between px-2">
                                            <div><p class="label-neon mb-0">PRIORITY</p><span class="text-danger fw-bold"><?= strtoupper($t['priority']) ?></span></div>
                                            <div><p class="label-neon mb-0">REJECTS</p><span><?= $t['rejected_count'] ?> / 3</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 justify-content-center gap-3 pb-5">
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Completed" class="btn-neural btn-res">RESOLVE CASE</a>
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=On Hold" class="btn-neural btn-hld">PLACE ON-HOLD</a>
                                <?php if($t['rejected_count'] < 3): ?>
                                    <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Rejected" class="btn-neural btn-rej">REJECT (<?= $t['rejected_count'] ?>)</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- CRITICAL: BOOTSTRAP JS MUST BE LOADED -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>