<?php
/**
 * LESBOT STAFF FIELD OPS
 * VISION: HIGH-DETAIL CASE ANALYSIS & SLA MONITORING
 */
session_start();
require_once 'db_config.php';
require_once 'watchdog.php'; 

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); exit(); 
}

// 2. TRIGGER SLA WATCHDOG: Snatch expired holds before displaying page
runNeuralWatchdog($pdo);

$staff_id = $_SESSION['std_id']; 

try {
    // 3. DATA ACQUISITION: Fetching full student profile and request metadata
    $query = "SELECT mr.*, u.name as student_name, u.email as student_email, u.user_id as student_matric, 
                     s.room_number, s.year_sem, c.category_name 
              FROM maintenance_request mr
              JOIN users u ON mr.student_id = u.user_id
              JOIN student s ON u.user_id = s.matric_number
              JOIN category c ON mr.category_id = c.category_id
              WHERE mr.assigned_staff_id = ? AND mr.status NOT IN ('Completed', 'Rejected')
              ORDER BY mr.priority ASC, mr.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staff_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) { die("Neural Link Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Case Analysis Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.3); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 120px; }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.95); backdrop-filter: blur(20px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 12px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 2000; }
        .system-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 35px; padding: 50px; }

        .task-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 25px; margin-bottom: 15px; transition: 0.3s; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.65rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; margin-bottom: 5px; }
        
        /* --- ENHANCED MODAL STYLING --- */
        .modal-content { background: #0B0E14 !important; border: 1px solid var(--lesbot-cyan) !important; border-radius: 30px !important; color: white !important; box-shadow: 0 0 60px rgba(0, 212, 255, 0.2); }
        .info-box { background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.08); height: 100%; }
        .data-point { margin-bottom: 15px; }
        .data-label { font-family: 'Orbitron'; font-size: 0.55rem; color: var(--lesbot-cyan); opacity: 0.8; letter-spacing: 1px; }
        .data-value { font-size: 1rem; font-weight: 700; color: #fff; }

        .btn-neural { font-family: 'Orbitron'; font-weight: 900; font-size: 0.75rem; padding: 12px 25px; border-radius: 12px; transition: 0.3s; border: none; }
        .btn-res { background: #198754; color: white; } 
        .btn-hld { background: #0dcaf0; color: #000; } 
        .btn-rej { background: #dc3545; color: white; }

        .countdown-timer { font-family: 'Orbitron'; font-size: 0.8rem; color: #ff4d4d; text-shadow: 0 0 10px rgba(255, 77, 77, 0.5); }
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

<div class="container mb-5">
    <div class="system-container shadow-lg">
        <h1 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900; letter-spacing:5px;">FIELD <span style="color:var(--lesbot-cyan);">OPERATIONS</span></h1>

        <?php if(empty($tasks)): ?>
            <div class="text-center py-5">
                <i class="bi bi-shield-check text-success fs-1"></i>
                <p class="mt-3 font-orbitron">ALL TASKS ARCHIVED / COMPLETED</p>
            </div>
        <?php else: ?>
            <?php foreach($tasks as $t): ?>
                <div class="task-card row align-items-center">
                    <div class="col-md-2"><p class="label-neon">ID</p><a href="#" class="fw-bold text-info" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">#<?= $t['request_id'] ?></a></div>
                    <div class="col-md-3"><p class="label-neon">STUDENT</p><div class="fw-bold text-white"><?= $t['student_name'] ?></div><span class="small opacity-50"><?= $t['room_number'] ?></span></div>
                    <div class="col-md-3"><p class="label-neon">CATEGORY</p><div class="small"><?= $t['category_name'] ?></div></div>
                    <div class="col-md-2"><span class="badge bg-primary px-3 py-2"><?= strtoupper($t['status']) ?></span></div>
                    <div class="col-md-2 text-end"><button class="btn btn-outline-info btn-sm font-orbitron" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">ANALYZE</button></div>
                </div>

                <!-- DETAILED ANALYSIS MODAL -->
                <div class="modal fade" id="modal-<?= $t['request_id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-secondary p-4">
                                <h5 class="modal-title font-orbitron text-info"><i class="bi bi-search me-2"></i>DEEP ANALYSIS: #<?= $t['request_id'] ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-4">
                                    <!-- COLUMN 1: STUDENT PROFILE -->
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <p class="label-neon mb-3">STUDENT PROFILE</p>
                                            <div class="data-point"><div class="data-label">FULL NAME</div><div class="data-value"><?= $t['student_name'] ?></div></div>
                                            <div class="data-point"><div class="data-label">MATRIC NUMBER</div><div class="data-value"><?= $t['student_matric'] ?></div></div>
                                            <div class="data-point"><div class="data-label">ACADEMIC LEVEL</div><div class="data-value"><?= $t['year_sem'] ?></div></div>
                                            <div class="data-point"><div class="data-label">LOCATION</div><div class="data-value text-info"><?= $t['room_number'] ?></div></div>
                                            <div class="data-point"><div class="data-label">EMAIL</div><div class="data-value small"><?= $t['student_email'] ?></div></div>
                                        </div>
                                    </div>
                                    <!-- COLUMN 2: ISSUE DETAILS -->
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <p class="label-neon mb-3">CASE SPECIFICS</p>
                                            <div class="data-point"><div class="data-label">CLASSIFICATION</div><div class="data-value"><?= $t['category_name'] ?></div></div>
                                            <div class="data-point"><div class="data-label">PRIORITY LEVEL</div><div class="data-value text-danger"><?= strtoupper($t['priority']) ?></div></div>
                                            <div class="data-point"><div class="data-label">SUBMITTED ON</div><div class="data-value"><?= date('d M Y | H:i', strtotime($t['created_at'])) ?></div></div>
                                            <div class="data-point">
                                                <div class="data-label">NEURAL DESCRIPTION</div>
                                                <div class="p-2 bg-dark rounded border border-secondary mt-1 small" style="min-height: 80px;">
                                                    "<?= nl2br(htmlspecialchars($t['description'])) ?>"
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- COLUMN 3: SLA & AUDIT -->
                                    <div class="col-md-4">
                                        <div class="info-box border-info">
                                            <p class="label-neon mb-3">SYSTEM AUDIT & SLA</p>
                                            <div class="data-point"><div class="data-label">CURRENT STATUS</div><div class="data-value text-warning"><?= strtoupper($t['status']) ?></div></div>
                                            <div class="data-point"><div class="data-label">REJECT ATTEMPTS</div><div class="data-value"><?= $t['rejected_count'] ?> / 3</div></div>
                                            
                                            <?php if ($t['status'] == 'On-Hold'): ?>
                                                <div class="mt-4 p-3 bg-danger bg-opacity-10 border border-danger rounded">
                                                    <div class="data-label text-danger">72H REASSIGNMENT TIMER</div>
                                                    <div class="countdown-timer mt-2">
                                                        <?php 
                                                            $expiry = strtotime($t['hold_timestamp'] . ' + 3 days');
                                                            $diff = $expiry - time();
                                                            echo ($diff > 0) ? round($diff / 3600) . " HOURS REMAINING" : "EXPIRED - REASSIGNING...";
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-4 p-3 bg-success bg-opacity-10 border border-success rounded text-center small text-success">
                                                    <i class="bi bi-activity"></i> CASE ACTIVE IN FIELD
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 justify-content-center gap-3 pb-5">
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Completed" class="btn-neural btn-res">RESOLVE CASE</a>
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=On-Hold" class="btn-neural btn-hld">ON HOLD</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'chatbot_component.php'; ?>

</body>
</html>