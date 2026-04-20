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
    <title>LesBot | Global Maintenance</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0B0E14; color: #FFFFFF; font-family: 'Rajdhani', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px; padding: 25px; }
        .priority-high { color: #ff4b2b; font-weight: bold; text-shadow: 0 0 5px #ff4b2b; }
        .status-badge { font-family: 'Orbitron'; font-size: 0.65rem; padding: 5px 10px; border-radius: 5px; }
    </style>
</head>
<body>

    <style>
    :root {
        --lesbot-black: #1a1a1a;
        --lesbot-cyan: #00d4ff; /* The vibrant blue from your reference */
        --lesbot-border: #e0e0e0;
    }

    #header {
        background: #ffffff;
        border-bottom: 1px solid var(--lesbot-border);
        padding: 15px 0;
    }

    .logo-text {
        font-family: 'Orbitron', sans-serif;
        color: var(--lesbot-black);
        font-weight: 900;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0;
    }

    /* Modern Navigation Links */
    .nav-links-modern {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        color: #444;
        text-decoration: none;
        margin-right: 25px;
        transition: 0.2s;
    }

    .nav-links-modern:hover {
        color: var(--lesbot-cyan);
    }

    /* Button Styles from image_0b7f42.png */
    .btn-signup-pill {
        border: 2px solid var(--lesbot-cyan);
        color: var(--lesbot-cyan);
        border-radius: 50px;
        padding: 8px 25px;
        font-weight: 700;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-login-pill {
        background: var(--lesbot-cyan);
        color: #ffffff;
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        transition: 0.3s;
    }

    .btn-login-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
    }
</style>

<header id="header" class="fixed-top shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    
    <h1 class="logo-text">LESBOT <span style="color: var(--lesbot-cyan);">•</span></h1>

    <div class="d-flex align-items-center"> 
      <a href="index.php" class="nav-links-modern">UTAMA</a>
      <a href="javascript:void(0)" onclick="toggleLesBot()" class="nav-links-modern">CHATBOT</a>
      <a href="student_penalties.php" class="nav-links-modern">PENALTIES</a>
      <a href="student_history.php" class="nav-links-modern">HISTORY</a>
      <a href="https://portal.utem.edu.my/" target="_blank" class="nav-links-modern">UTeM <i class="bi bi-box-arrow-up-right small"></i></a>
      
      <div class="ms-3 d-flex gap-3 align-items-center">
          <a href="logout.php" class="btn-signup-pill">LOGOUT</a>
      </div>
    </div>
  </div>
</header>

<div style="margin-top: 80px;"></div>

<div class="container py-5">
    <h2 class="mb-4" style="font-family: 'Orbitron'; color: #00d4ff;">2. VIEW MAINTENANCE REPORTS</h2>

    <div class="glass-card shadow-lg">
        <?php if (empty($all_requests)): ?>
            <p class="text-center py-4">No active maintenance logs in the neural network.</p>
        <?php else: ?>
            <table class="table table-hover text-white">
                <thead>
                    <tr class="text-info small">
                        <th>ID</th>
                        <th>STUDENT</th>
                        <th>CATEGORY</th>
                        <th>PRIORITY</th>
                        <th>STATUS</th>
                        <th>SUBMITTED</th>
                        <th>DELEGATION</th> </tr>
                </thead>
                <tbody>
                    <?php foreach($all_requests as $req): ?>
                    <tr>
                        <td class="small fw-bold text-white-50"><?= $req['request_id'] ?></td>
                        <td><?= htmlspecialchars($req['student_name'] ?? 'Unknown Entity') ?></td>
                        <td><?= htmlspecialchars($req['category_name']) ?></td>
                        <td class="<?= $req['priority'] == 'High' ? 'priority-high' : '' ?>"><?= $req['priority'] ?></td>
                        <td>
                            <span class="status-badge bg-<?= $req['status'] == 'Pending' ? 'secondary' : ($req['status'] == 'In Progress' ? 'info' : 'success') ?>">
                                <?= strtoupper($req['status']) ?>
                            </span>
                        </td>
                        <td class="small"><?= date('d/m/Y', strtotime($req['created_at'])) ?></td>
                        
                        <td>
                            <?php if($req['status'] == 'Pending'): ?>
                                <a href="admin_assign_staff.php?id=<?= $req['request_id'] ?>" class="btn btn-sm btn-outline-info" style="font-size: 0.7rem; font-family: 'Orbitron';">ASSIGN STAFF</a>
                            <?php else: ?>
                                <span class="text-muted small" style="letter-spacing: 1px;">✓ ASSIGNED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5">
        <a href="admin_dashboard.php" class="btn btn-outline-info rounded-pill px-5">
            <i class="bi bi-arrow-left"></i> BACK TO COMMAND CENTER
        </a>
    </div>
</div>
</body>
</html>