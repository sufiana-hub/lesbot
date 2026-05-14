<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Ensures only logged-in students can view this data
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    // 2. READ: Fetch Maintenance Requests with Joins
    $sql = "SELECT 
                mr.request_id, 
                c.category_name, 
                c.severity_level, 
                mr.description, 
                mr.status, 
                mr.priority, 
                mr.created_at,
                u.name AS staff_name,
                st.phone_num AS staff_phone
            FROM maintenance_request mr
            JOIN category c ON mr.category_id = c.category_id
            LEFT JOIN staff st ON mr.assigned_staff_id = st.staff_id
            LEFT JOIN users u ON st.staff_id = u.user_id
            WHERE mr.student_id = :id
            ORDER BY mr.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $requests = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Track Requests</title>
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
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0;
            padding-top: 120px;
            min-height: 100vh;
        }

        /* --- Floating Navigation (Glassmorphic) --- */
        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.8);
            backdrop-filter: blur(15px); border: 1px solid var(--glass-border);
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

        /* --- System Dashboard Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);
        }

        .header-title { 
            font-family: 'Orbitron'; 
            font-weight: 900; 
            letter-spacing: 3px; 
            color: var(--lesbot-cyan);
        }

        /* --- Modern Data Table Styling --- */
        .custom-table { color: #e0e0e0; border-collapse: separate; border-spacing: 0 10px; }
        .custom-table thead th { 
            border: none; 
            font-family: 'Orbitron'; 
            font-size: 0.75rem; 
            color: var(--lesbot-cyan);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 15px;
        }
        .custom-table tbody tr { 
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease; 
        }
        .custom-table tbody tr:hover { 
            background: rgba(0, 212, 255, 0.05); 
            border-color: var(--lesbot-cyan);
            transform: scale(1.005);
        }
        .custom-table td { padding: 1.25rem; border: none; vertical-align: middle; }
        
        /* Status Badges */
        .badge-status { 
            font-family: 'Orbitron'; 
            font-size: 0.65rem; 
            letter-spacing: 1px;
            padding: 0.6rem 1.2rem; 
            border-radius: 50px; 
            font-weight: 700;
        }
        
        .legend-box { 
            font-size: 0.8rem; 
            border-top: 1px solid rgba(255,255,255,0.1); 
            padding-top: 1.5rem; 
            font-family: 'Orbitron';
        }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="student_dashboard.php">UTAMA</a></li>
        <li><a href="maintenance_report.php">REPORT</a></li>
        <li><a href="student_penalties.php">PENALTIES</a></li>
        <li><a href="student_history.php">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 class="header-title">TRACK <span class="text-white">REQUESTS</span></h2>
            <p class="text-white-50 small" style="letter-spacing: 2px;">NEURAL LINK ACTIVE • MONITORING SYSTEM STATUS</p>
        </div>

        <div class="table-responsive">
            <table class="table custom-table mb-0" class="text-center py-5 text-cyan-bright">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Staff Assigned</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-cyan-bright">
                                <i class="bi bi-inbox fs-1 d-block mb-3 text-info"></i>
                                No active maintenance requests found in your history.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td class="small fw-bold text-info" style="font-family: 'Orbitron';">#<?= htmlspecialchars($r['request_id']) ?></td>
                            <td>
                                <div class="fw-bold text-white"><?= htmlspecialchars($r['category_name']) ?></div>
                                <div class="small text-muted" style="font-size: 0.7rem; font-family: 'Orbitron';"><?= strtoupper($r['priority']) ?> PRIORITY</div>
                            </td>
                            <td class="small text-white-50" style="max-width: 250px;"><?= htmlspecialchars($r['description']) ?></td>
                            <td>
                                <?php 
                                    $status = $r['status'];
                                    $bg = 'bg-secondary';
                                    if($status == 'Pending') $bg = 'bg-warning text-dark';
                                    if($status == 'In Progress') $bg = 'bg-info text-dark';
                                    if($status == 'Completed') $bg = 'bg-success';
                                    if($status == 'Rejected') $bg = 'bg-danger';
                                ?>
                                <span class="badge-status <?= $bg ?>"><?= strtoupper($status) ?></span>
                            </td>
                            <td>
                                <div class="small fw-bold text-white"><?= $r['staff_name'] ?? '<span class="text-muted" style="font-size: 0.7rem; font-family: \'Orbitron\';">UNASSIGNED</span>' ?></div>
                            </td>
                            <td class="small">
                                <?= $r['staff_phone'] ? '<a href="tel:'.$r['staff_phone'].'" class="text-decoration-none text-info fw-bold" style="font-family: \'Orbitron\';">'.$r['staff_phone'].'</a>' : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="legend-box mt-5 row text-center g-2 text-cyan-bright justify-content-center">
            <div class="col-6 col-md-3 small"><span class="text-warning">●</span> PENDING: AWAITING ACTION</div>
            <div class="col-6 col-md-3 small"><span class="text-info">●</span> IN PROGRESS: BEING ADDRESSED</div>
            <div class="col-6 col-md-3 small"><span class="text-success">●</span> COMPLETED: RESOLVED</div>
            <div class="col-6 col-md-3 small"><span class="text-danger">●</span> REJECTED: CANCELLED</div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-3 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.75rem; letter-spacing: 2px; transition: 0.3s;">
            <i class="bi bi-arrow-left me-2"></i> RETURN TO HUB
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

</body>
</html>