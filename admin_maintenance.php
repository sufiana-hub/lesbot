<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Head of Fellow
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

try {
    // 2. READ: Global Maintenance Log
    $query = "SELECT mr.request_id, u.name as student_name, c.category_name, 
                     mr.description, mr.status, mr.created_at, mr.priority
              FROM maintenance_request mr
              JOIN users u ON mr.student_id = u.user_id
              JOIN category c ON mr.category_id = c.category_id
              ORDER BY mr.created_at DESC";
              
    $stmt = $pdo->query($query);
    $all_requests = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Maintenance Log</title>

        <!-- Paste the Google tag here -->
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />

    <!-- site identity tags -->
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System">

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
            color: #FFFFFF; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0;
            padding-top: 100px;
            min-height: 100vh;
        }

        /* --- Floating Navigation (Aligned with Accounts) --- */
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

        /* --- Maintenance Content Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);
        }

        .table-neural { color: #fff; vertical-align: middle; }
        .table-neural thead th { 
            font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); 
            border-bottom: 1px solid var(--glass-border); padding: 15px;
        }

        .status-badge { 
            font-family: 'Orbitron'; font-size: 0.6rem; padding: 5px 12px; border-radius: 4px; 
            text-shadow: 0 0 5px rgba(0,0,0,0.5);
        }
        
        .priority-high { color: #ff4b2b; font-weight: bold; text-shadow: 0 0 8px rgba(255, 75, 43, 0.5); }

        /* Assignment Trigger Box */
        .assign-box {
            display: inline-block; padding: 6px 15px; border-radius: 8px;
            background: rgba(0, 212, 255, 0.1); border: 1px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan); font-family: 'Orbitron'; font-size: 0.65rem;
            text-decoration: none; transition: 0.3s; font-weight: 700;
        }
        .assign-box:hover { 
            background: var(--lesbot-cyan); color: var(--obsidian); 
            box-shadow: 0 0 15px var(--lesbot-cyan); 
        }

        .btn-neural-back {
            border: 1px solid var(--lesbot-cyan); color: var(--lesbot-cyan);
            font-family: 'Orbitron'; font-size: 0.75rem; border-radius: 12px;
            padding: 10px 25px; transition: 0.3s; text-decoration: none;
        }
        .btn-neural-back:hover { background: var(--lesbot-cyan); color: #000; box-shadow: 0 0 20px var(--lesbot-cyan); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php">OVERVIEW</a></li>
        <li><a href="manage_accounts.php">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php" class="active">MAINTENANCE</a></li>
        <li><a href="admin_penalties.php">PENALTIES</a></li>
        <li><a href="export_data.php">EXPORTS</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-family: 'Orbitron'; font-weight: 900; margin: 0;">MAINTENANCE <span style="color: var(--lesbot-cyan);">LOGS</span></h2>
            <div class="text-end">
                <span class="badge rounded-pill bg-dark border border-info text-info small px-3">NEURAL MONITORING ACTIVE</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover table-neural">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>STUDENT ENTITY</th>
                        <th>CATEGORY</th>
                        <th>PRIORITY</th>
                        <th>SYSTEM STATUS</th>
                        <th>SUBMITTED</th>
                        <th class="text-end">DELEGATION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_requests)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-cyan-bright">NO ACTIVE MAINTENANCE REQUESTS DETECTED</td></tr>
                    <?php else: ?>
                        <?php foreach($all_requests as $req): ?>
                        <tr>
                            <td class="text-info fw-bold small">#<?= $req['request_id'] ?></td>
                            <td class="text-uppercase small"><?= htmlspecialchars($req['student_name'] ?? 'Unknown') ?></td>
                            <td class="small"><?= htmlspecialchars($req['category_name']) ?></td>
                            <td class="<?= $req['priority'] == 'High' ? 'priority-high' : '' ?> small"><?= strtoupper($req['priority']) ?></td>
                            <td>
                                <?php 
                                    $statusClass = ($req['status'] == 'Pending') ? 'bg-warning text-dark' : 
                                                  (($req['status'] == 'In Progress') ? 'bg-info text-dark' : 'bg-success');
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= strtoupper($req['status']) ?>
                                </span>
                            </td>
                            <td class="text-white-50 small"><?= date('d/m/Y', strtotime($req['created_at'])) ?></td>
                            <td class="text-end">
                                <?php if($req['status'] == 'Pending'): ?>
                                    <a href="admin_assign_staff.php?id=<?= $req['request_id'] ?>" class="assign-box">
                                        <i class="bi bi-person-gear me-1"></i> DELEGATE
                                    </a>
                                <?php else: ?>
                                    <span class="text-success small fw-bold"><i class="bi bi-check2-circle"></i> ASSIGNED</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn-neural-back">
                <i class="bi bi-cpu me-2"></i> COMMAND CORE
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