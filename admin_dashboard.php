<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Admin 'Head of Fellow' allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id']; 

try {
    // 2. ANALYTICS: Count Total Users
    $student_count = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
    $staff_count   = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $admin_count   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'")->fetchColumn();

    // 3. FINANCIAL: Summarize Penalties
    $total_unpaid = $pdo->query("SELECT SUM(amount) FROM student_penalties WHERE is_paid = 0")->fetchColumn() ?: "0.00";

    // 4. MAINTENANCE: Status Breakdown
    $pending_tasks = $pdo->query("SELECT COUNT(*) FROM maintenance_request WHERE status = 'Pending'")->fetchColumn();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Admin Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #0B0E14; 
            --bright-white: #FFFFFF; 
            --lesbot-border: #e0e0e0;
        }

        body { 
            background-color: var(--obsidian); 
            color: var(--bright-white); 
            font-family: 'Rajdhani', sans-serif; 
            display: flex; 
            min-height: 100vh; 
            margin: 0;
        }

        /* Top White Header */
        #header {
            background: #ffffff;
            border-bottom: 1px solid var(--lesbot-border);
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1001;
        }
        .logo-text { font-family: 'Orbitron'; color: #1a1a1a; font-weight: 900; letter-spacing: 2px; margin: 0; }
        .nav-links-modern { font-family: 'Rajdhani'; font-weight: 700; color: #444; text-decoration: none; margin-right: 25px; font-size: 0.95rem; }
        .btn-logout-pill { border: 2px solid var(--lesbot-cyan); color: var(--lesbot-cyan); border-radius: 50px; padding: 5px 20px; font-weight: 700; text-decoration: none; }

        /* Sidebar Navigation */
        .side-nav { 
            width: 280px; 
            background: rgba(255, 255, 255, 0.03); 
            border-right: 1px solid rgba(0, 212, 255, 0.2); 
            padding: 30px; 
            padding-top: 120px; /* Space for top header */
        }
        .nav-logo { font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan); margin-bottom: 40px; text-align: center; }
        .nav-links { list-style: none; padding: 0; }
        .nav-links li { margin-bottom: 18px; }
        .nav-links a { color: #DDD; text-decoration: none; transition: 0.3s; display: block; font-size: 0.9rem; font-weight: 500; }
        .nav-links a:hover, .nav-links a.active { color: var(--bright-white); text-shadow: 0 0 8px var(--lesbot-cyan); transform: translateX(8px); }

        /* Dashboard Content */
        .main-content { flex: 1; padding: 50px; padding-top: 130px; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.25); 
            border-radius: 20px; 
            padding: 25px; 
        }
        .stat-value { 
            font-family: 'Orbitron'; 
            font-size: 2.8rem; 
            color: var(--bright-white); 
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.4); 
        }
        .text-cyan-bright { color: var(--lesbot-cyan) !important; text-shadow: 0 0 10px var(--lesbot-cyan); }
    </style>
</head>
<body>

<header id="header" class="no-print">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="logo-text">LESBOT <span style="color: var(--lesbot-cyan);">•</span></h1>
    <div class="d-flex align-items-center"> 
      <a href="index.php" class="nav-links-modern">UTAMA</a>
      <a href="javascript:void(0)" onclick="toggleLesBot()" class="nav-links-modern">CHATBOT</a>
      <a href="student_penalties.php" class="nav-links-modern">PENALTIES</a>
      <a href="student_history.php" class="nav-links-modern">HISTORY</a>
      <a href="https://portal.utem.edu.my/" target="_blank" class="nav-links-modern">UTeM <i class="bi bi-box-arrow-up-right small"></i></a>
      <div class="ms-3">
          <a href="logout.php" class="btn-logout-pill">LOGOUT</a>
      </div>
    </div>
  </div>
</header>

<nav class="side-nav">
    <div class="nav-logo">ADMIN CORE <span style="font-size: 0.6rem; display: block; color: var(--bright-white);">DORMITORY SYSTEM</span></div>
    <ul class="nav-links">
        <li><a href="admin_dashboard.php" class="active"><i class="bi bi-grid-fill"></i> OVERVIEW</a></li>
        <li><a href="manage_accounts.php"><i class="bi bi-people"></i> 1. Manage Accounts</a></li>
        <li><a href="admin_maintenance.php"><i class="bi bi-tools"></i> 2. View Reports</a></li>
        <li><a href="export_data.php"><i class="bi bi-file-earmark-arrow-down"></i> 3. Generate Report</a></li>
        <li><a href="admin_add_staff.php"><i class="bi bi-person-plus"></i> 11. Create Staff</a></li>
        <li><a href="admin_penalties.php"><i class="bi bi-card-checklist"></i> 7. Manage Penalties</a></li>
        <li><hr style="border-color: rgba(255,255,255,0.2);"></li>
        <li><a href="logout.php" style="color: #ff4d4d;"><i class="bi bi-power"></i> 10. Exit System</a></li>
    </ul>
</nav>

<main class="main-content">
    <header class="mb-5 d-flex justify-content-between align-items-center">
        <div>
            <h1 style="font-family: 'Orbitron'; font-weight: 900; color: var(--bright-white);">ADMIN <span class="text-cyan-bright">COMMAND</span></h1>
            <p class="small" style="color: #FFF; opacity: 0.9;">HEAD OF FELLOW: <b><?php echo $_SESSION['full_name']; ?> (<?php echo $id; ?>)</b></p>
        </div>
        <div class="glass-card py-2 px-4" style="border-color: var(--lesbot-cyan);">
            <span class="small text-cyan-bright fw-bold">SYSTEM STATUS: <span class="text-success">ONLINE</span></span>
        </div>
    </header>

    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="glass-card">
                <p class="small fw-bold mb-1" style="letter-spacing: 1px;">TOTAL SYSTEM USERS</p>
                <div class="stat-value text-cyan-bright"><?php echo ($student_count + $staff_count + $admin_count); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <p class="small fw-bold mb-1" style="letter-spacing: 1px;">PENDING TASKS</p>
                <div class="stat-value text-warning" style="text-shadow: 0 0 10px #ffc107;"><?php echo $pending_tasks; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <p class="small fw-bold mb-1" style="letter-spacing: 1px;">UNPAID FINES</p>
                <div class="stat-value text-danger" style="text-shadow: 0 0 10px #dc3545;">RM <?php echo number_format($total_unpaid, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 style="font-family: 'Orbitron'; font-size: 1rem; color: var(--lesbot-cyan); font-weight: 900;" class="mb-4">MASTER TRIGGERS</h4>
        <div class="row g-3">
            <div class="col-md-4"><a href="admin_add_staff.php" class="btn btn-outline-info w-100 py-3 fw-bold text-white">REGISTER STAFF</a></div>
            <div class="col-md-4"><a href="manage_accounts.php" class="btn btn-outline-info w-100 py-3 fw-bold text-white">ACCOUNT ARCHIVE</a></div>
            <div class="col-md-4"><a href="export_data.php" class="btn btn-outline-info w-100 py-3 fw-bold text-white">GLOBAL EXPORT</a></div>
        </div>
    </div>
</main>

</body>
</html>