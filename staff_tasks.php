<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Staff permitted
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); 
    exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // 2. READ: Fetch Assigned Maintenance Reports with student and category details
    $query = "SELECT mr.*, u.name as student_name, s.room_number, c.category_name 
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

        /* --- Floating Tactical Navigation --- */
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

        /* --- System Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 35px; padding: 50px; backdrop-filter: blur(15px);
        }

        /* --- Aligned Task Cards --- */
        .task-card {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px; margin-bottom: 20px; transition: 0.3s;
        }
        .task-card:hover { border-color: var(--lesbot-cyan); box-shadow: 0 0 20px rgba(0, 212, 255, 0.1); }
        
        .label-neon { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        .value-white { font-family: 'Orbitron'; font-size: 0.85rem; color: #FFF; text-transform: uppercase; }

        /* --- Action Buttons --- */
        .btn-action {
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.55rem;
            padding: 8px 12px; border-radius: 6px; transition: 0.3s;
            text-decoration: none; display: block; text-align: center; border: 1px solid transparent;
        }
        .btn-resolve { background: rgba(25, 135, 84, 0.1); border-color: #198754; color: #198754; }
        .btn-resolve:hover { background: #198754; color: white; }
        .btn-reject { background: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #dc3545; }
        .btn-reject:hover { background: #dc3545; color: white; }

        /* --- Sleek External Return --- */
        .external-return { margin-top: 40px; text-align: center; padding-bottom: 40px; }
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
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h1 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 6px; margin: 0;">FIELD <span style="color: var(--lesbot-cyan);">OPERATIONS</span></h1>
            <p class="small text-white-50 mt-2">ENHANCED CASE MANAGEMENT • WORKSHOP 1 LOGIC v2.0</p>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="text-center py-5 opacity-40">
                <i class="bi bi-clipboard-x fs-1 text-info"></i>
                <p class="mt-3 font-orbitron" style="letter-spacing: 2px; font-size: 0.7rem;">NO ASSIGNED REPORTS IN ACTIVE ARCHIVE</p>
            </div>
        <?php else: ?>
            <?php foreach($tasks as $t): ?>
                <div class="task-card">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center text-md-start">
                            <p class="label-neon mb-1">ID</p>
                            <p class="value-white text-info">#<?= $t['request_id'] ?></p>
                        </div>
                        <div class="col-md-3">
                            <p class="label-neon mb-1">STUDENT ENTITY</p>
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
                        <div class="col-md-2">
                            <div class="d-flex flex-column gap-2">
                                <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Completed" class="btn-action btn-resolve">RESOLVE</a>
                                <?php if($t['rejected_count'] < 3): ?>
                                    <a href="staff_update_status.php?id=<?= $t['request_id'] ?>&status=Rejected" class="btn-action btn-reject">REJECT (<?= $t['rejected_count'] ?>/3)</a>
                                <?php else: ?>
                                    <div class="text-danger fw-bold text-center" style="font-size: 0.45rem;">REJECT LIMIT</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="external-return">
        <a href="staff_dashboard.php" class="btn-neural-back">
            <i class="bi bi-cpu-fill me-3"></i> RETURN TO HUB COMMAND
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