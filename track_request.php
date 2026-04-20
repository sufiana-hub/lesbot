<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
// Ensures only logged-in students can view this data
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    // 2. READ: Fetch Maintenance Requests with Joins
    // We join 'category' for names and 'staff/users' for technician details
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
            --baby-blue: #A7C7E7; 
            --obsidian: #0B0E14; 
            --lesbot-cyan: #00d4ff;
        }
        body { 
            background-color: var(--obsidian); 
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
        }
        .header-title { 
            font-family: 'Orbitron'; 
            font-weight: 900; 
            letter-spacing: 3px; 
            color: var(--baby-blue);
        }
        .status-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(167, 199, 231, 0.2); 
            border-radius: 20px; 
            padding: 2rem;
            backdrop-filter: blur(10px);
        }
        .custom-table { color: #e0e0e0; border-collapse: separate; border-spacing: 0 10px; }
        .custom-table thead th { 
            border: none; 
            font-family: 'Orbitron'; 
            font-size: 0.75rem; 
            color: var(--lesbot-cyan);
            text-transform: uppercase;
        }
        .custom-table tbody tr { background: rgba(255, 255, 255, 0.03); border-radius: 10px; transition: 0.3s; }
        .custom-table tbody tr:hover { background: rgba(167, 199, 231, 0.1); }
        .custom-table td { padding: 1.25rem; border: none; vertical-align: middle; }
        
        .badge-status { font-family: 'Orbitron'; font-size: 0.65rem; padding: 0.5rem 1rem; border-radius: 50px; }
        .legend-box { font-size: 0.8rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; }
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
    <div class="text-center mb-5">
        <h2 class="header-title">TRACK REQUESTS</h2>
<p class="text-white-50 small">NEURAL LINK ACTIVE • MONITORING SYSTEM STATUS</p>
    </div>

    <div class="status-card shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No maintenance requests found in your history.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td class="small fw-bold text-info">#<?= htmlspecialchars($r['request_id']) ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($r['category_name']) ?></div>
                                <div class="small text-warning" style="font-size: 0.7rem;"><?= strtoupper($r['priority']) ?> PRIORITY</div>
                            </td>
                            <td class="small text-muted" style="max-width: 200px;"><?= htmlspecialchars($r['description']) ?></td>
                            <td>
                                <?php 
                                    // Status Badge Logic based on your terminal prototype
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
                                <div class="small fw-bold"><?= $r['staff_name'] ?? '<span class="text-muted">UNASSIGNED</span>' ?></div>
                            </td>
                            <td class="small">
                                <?= $r['staff_phone'] ? '<a href="tel:'.$r['staff_phone'].'" class="text-decoration-none text-info">'.$r['staff_phone'].'</a>' : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="legend-box mt-4 row text-center g-2 text-muted">
            <div class="col-6 col-md-3 small"><span class="text-warning">●</span> Pending: Awaiting action</div>
            <div class="col-6 col-md-3 small"><span class="text-info">●</span> In Progress: Being addressed</div>
            <div class="col-6 col-md-3 small"><span class="text-success">●</span> Completed: Resolved</div>
            <div class="col-6 col-md-3 small"><span class="text-danger">●</span> Rejected: Reassigned/Cancelled</div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-2 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.8rem;">
            <i class="bi bi-arrow-left me-2"></i> BACK TO COMMAND CENTER
        </a>
    </div>
</div>

</body>
</html>