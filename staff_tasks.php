<?php
/**
 * LESBOT STAFF FIELD OPS
 * VISION: DETAILED ANALYSIS & STATUS MANAGEMENT
 */
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); 
    exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // 2. DATA ACQUISITION: Fetching full details for the Modal view
    $query = "SELECT mr.*, u.name as student_name, u.email as student_email, s.room_number, c.category_name 
              FROM maintenance_request mr
              JOIN users u ON mr.student_id = u.user_id
              JOIN student s ON u.user_id = s.matric_number
              JOIN category c ON mr.category_id = c.category_id
              WHERE mr.assigned_staff_id = ?
              ORDER BY 
                CASE WHEN mr.status = 'In Progress' THEN 1 
                     WHEN mr.status = 'Pending' THEN 2 
                     WHEN mr.status = 'On Hold' THEN 3
                     ELSE 4 END, 
                mr.priority ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staff_id]);
    $tasks = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Field Operations Hub</title>
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

        .task-card {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px; margin-bottom: 20px; transition: 0.3s;
        }

        /* CLICKABLE ID */
        .id-trigger { color: var(--lesbot-cyan); cursor: pointer; text-decoration: none; font-family: 'Orbitron'; font-weight: 900; transition: 0.3s; }
        .id-trigger:hover { text-shadow: 0 0 15px var(--lesbot-cyan); transform: scale(1.05); }

        /* MODAL STYLING */
        .modal-content { background: #0B0E14; border: 1px solid var(--lesbot-cyan); border-radius: 25px; color: white; }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .detail-label { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; margin-bottom: 5px; }
        .detail-value { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; }
        
        .label-neon { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        .value-white { font-family: 'Orbitron'; font-size: 0.85rem; color: #FFF; text-transform: uppercase; }

        .btn-neural-back {
            color: rgba(255, 255, 255, 0.4); text-decoration: none; font-family: 'Orbitron';
            font-size: 0.7rem; letter-spacing: 4px; transition: 0.3s; display: inline-flex; align-items: center;
        }
        .btn-neural-back:hover { color: var(--lesbot-cyan); text-shadow: 0 0 10px var(--lesbot-cyan); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="#" class="nav-variant active" style="font-weight: 900; letter-spacing: 3px;">LESBOT STAFF</a>
    <div class="d-flex gap-2">
        <a href="staff_dashboard.php" class="nav-variant">DASHBOARD</a>
        <a href="staff_tasks.php" class="nav-variant active">FIELD OPS</a>
        <a href="staff_penalties.php" class="nav-variant">FINANCIALS</a>
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h1 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 6px; margin: 0;">FIELD <span style="color: var(--lesbot-cyan);">OPERATIONS</span></h1>
            <p class="small text-white-50 mt-2">CLICK REQUEST ID FOR DEEP ANALYSIS • VISION v3.0</p>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="text-center py-5 opacity-40">
                <i class="bi bi-clipboard-x fs-1 text-info"></i>
                <p class="mt-3 font-orbitron" style="letter-spacing: 2px;">NO ASSIGNED REPORTS</p>
            </div>
        <?php else: ?>
            <?php foreach($tasks as $t): ?>
                <div class="task-card">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <p class="label-neon mb-1">ID (CLICK)</p>
                            <h5 class="id-trigger" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">#<?= $t['request_id'] ?></h5>
                        </div>
                        <div class="col-md-3">
                            <p class="label-neon mb-1">STUDENT</p>
                            <p class="value-white" style="font-size: 0.75rem;"><?= htmlspecialchars($t['student_name']) ?></p>
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary" style="font-size: 0.5rem;">ROOM: <?= $t['room_number'] ?></span>
                        </div>
                        <div class="col-md-3">
                            <p class="label-neon mb-1">CLASSIFICATION</p>
                            <p class="value-white small"><?= htmlspecialchars($t['category_name']) ?></p>
                        </div>
                        <div class="col-md-2">
                            <p class="label-neon mb-1">STATUS</p>
                            <span class="value-white" style="color: <?= ($t['status'] == 'In Progress') ? 'var(--lesbot-cyan)' : '#ffc107' ?>;"><?= strtoupper($t['status']) ?></span>
                        </div>
                        <div class="col-md-2 text-center">
                            <button class="btn btn-outline-info btn-sm font-orbitron w-100" data-bs-toggle="modal" data-bs-target="#modal-<?= $t['request_id'] ?>">ANALYZE</button>
                        </div>
                    </div>
                </div>

                <!-- NEURAL DETAIL MODAL -->
                <div class="modal fade" id="modal-<?= $t['request_id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content shadow-lg">
                            <div class="modal-header">
                                <h5 class="modal-title font-orbitron text-info">CASE ANALYSIS #<?= $t['request_id'] ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6 border-end border-secondary">
                                        <p class="detail-label">STUDENT ENTITY</p>
                                        <div class="detail-value"><?= $t['student_name'] ?></div>
                                        
                                        <p class="detail-label">ROOM ADDRESS</p>
                                        <div class="detail-value text-info"><?= $t['room_number'] ?></div>
                                        
                                        <p class="detail-label">COMMUNICATION LINK</p>
                                        <div class="detail-value small opacity-50"><?= $t['student_email'] ?></div>
                                    </div>
                                    <div class="col-md-6 ps-md-4">
                                        <p class="detail-label">NEURAL DESCRIPTION</p>
                                        <div class="p-3 bg-dark rounded-3 mb-4" style="border: 1px solid rgba(255,255,255,0.1);">
                                            <p class="mb-0 italic text-white-50">"<?= nl2br(htmlspecialchars($t['description'])) ?>"</p>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <p class="detail-label">PRIORITY</p>
                                                <span class="text-danger fw-bold"><?= strtoupper($t['priority']) ?></span>
                                            </div>
                                            <div class="col-6">
                                                <p class="detail-label">REJECT LOG</p>
                                                <span><?= $t['rejected_count'] ?> / 3</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center gap-3 border-0 pb-4">
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Completed" class="btn btn-success px-4 font-orbitron">RESOLVE</a>
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=On Hold" class="btn btn-info px-4 font-orbitron">ON HOLD</a>
                                <?php if($t['rejected_count'] < 3): ?>
                                    <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Rejected" class="btn btn-danger px-4 font-orbitron">REJECT</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary disabled px-4 font-orbitron">LIMIT REACHED</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="external-return text-center mt-4">
        <a href="staff_dashboard.php" class="btn-neural-back">
            <i class="bi bi-cpu-fill me-3"></i> RETURN TO HUB COMMAND
        </a>
    </div>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include 'chatbot_component.php'; ?>

</body>
</html>