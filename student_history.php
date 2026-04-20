<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    // 2. READ: Fetch Personal Details for the Header
    $u_stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
    $u_stmt->execute([$id]);
    $user = $u_stmt->fetch();

    // 3. READ: Fetch FULL Semester History
    $h_stmt = $pdo->prepare("SELECT * FROM student_room_history 
                             WHERE matric_number = ? 
                             ORDER BY move_in_date DESC");
    $h_stmt->execute([$id]);
    $full_history = $h_stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Student Audit Archive</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #0B0E14; 
            --lesbot-black: #1a1a1a;
            --lesbot-border: #e0e0e0;
        }
        body { 
            background-color: var(--obsidian); 
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
        }

        /* --- Global Header Styles --- */
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
        .nav-links-modern {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            color: #444;
            text-decoration: none;
            margin-right: 25px;
            transition: 0.2s;
        }
        .nav-links-modern:hover { color: var(--lesbot-cyan); }
        .btn-logout-pill {
            border: 2px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan);
            border-radius: 50px;
            padding: 8px 25px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }

        /* --- Audit Content Styles --- */
        .header-title { 
            font-family: 'Orbitron'; 
            font-weight: 900; 
            letter-spacing: 3px; 
            color: var(--lesbot-cyan);
        }
        .archive-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(0, 212, 255, 0.2); 
            border-radius: 20px; 
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }
        .timeline-item { 
            border-left: 2px solid var(--lesbot-cyan); 
            padding-left: 25px; 
            margin-bottom: 40px; 
            position: relative; 
        }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -10px; top: 0; 
            width: 18px; height: 18px; 
            background: var(--lesbot-cyan); 
            border-radius: 50%; 
            box-shadow: 0 0 15px var(--lesbot-cyan);
        }
        .sem-label { 
            font-family: 'Orbitron'; 
            font-weight: 900; 
            font-size: 0.8rem; 
            color: var(--obsidian); 
            background: var(--lesbot-cyan); 
            padding: 4px 12px; 
            border-radius: 4px;
        }

        /* 🖨️ PRINT OPTIMIZATION */
        @media print {
            body { background: white !important; color: black !important; }
            #header, .no-print, .btn, .text-white-50 { display: none !important; }
            .archive-card { background: none !important; border: 1px solid #000 !important; color: black !important; }
            .header-title { color: black !important; border-bottom: 2px solid black; }
            .timeline-item { border-left: 2px solid black !important; color: black !important; }
            .timeline-item::before { background: black !important; box-shadow: none !important; }
            .sem-label { background: #eee !important; border: 1px solid black !important; color: black !important; }
            .text-info { color: black !important; }
        }
    </style>
</head>
<body>

<header id="header" class="fixed-top shadow-sm no-print">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="logo-text">LESBOT <span style="color: var(--lesbot-cyan);">•</span></h1>
    <div class="d-flex align-items-center"> 
      <a href="index.php" class="nav-links-modern">UTAMA</a>
      <a href="javascript:void(0)" onclick="toggleLesBot()" class="nav-links-modern">CHATBOT</a>
      <a href="student_penalties.php" class="nav-links-modern">PENALTIES</a>
      <a href="student_history.php" class="nav-links-modern text-info">HISTORY</a>
      <a href="https://portal.utem.edu.my/" target="_blank" class="nav-links-modern">UTeM <i class="bi bi-box-arrow-up-right small"></i></a>
      <div class="ms-3">
          <a href="logout.php" class="btn-logout-pill">LOGOUT</a>
      </div>
    </div>
  </div>
</header>

<div style="margin-top: 100px;"></div>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="header-title">STUDENT AUDIT TRAIL</h2>
        <p class="text-white-50 small no-print">OFFICIAL RESIDENCY LOGS • IDENTITY: <?php echo strtoupper($user['name']); ?></p>
        
        <div class="mt-3 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-outline-light me-2">
                <i class="bi bi-printer"></i> PRINT PDF
            </button>
            <a href="export_history.php" class="btn btn-sm btn-outline-info">
                <i class="bi bi-download"></i> DOWNLOAD CSV
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="archive-card shadow-lg">
                <?php if (empty($full_history)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-shield-exclamation fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No historical semester logs found for this identity.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($full_history as $index => $log): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="sem-label"><?php echo htmlspecialchars($log['semester_session']); ?></span>
                                    <?php if ($index === 0): ?>
                                        <span class="badge bg-success ms-2 no-print" style="font-size: 0.6rem; vertical-align: middle;">ACTIVE</span>
                                    <?php endif; ?>
                                </div>
                                <span class="small text-white-50">
                                    <i class="bi bi-calendar-check me-1"></i> 
                                    <?php echo date('d M Y', strtotime($log['move_in_date'])); ?>
                                </span>
                            </div>
                            
                            <h4 class="fw-bold mb-1">Room: <?php echo htmlspecialchars($log['room_number']); ?></h4>
                            <p class="text-info small mb-0">
                                <i class="bi bi-building"></i> 
                                <?php 
                                    echo (strpos(strtoupper($log['room_number']), 'A') === 0) ? "Residential Block A (Lelaki)" : "Residential Block B (Perempuan)"; 
                                ?>
                            </p>
                            <p class="small text-muted mt-2">Status: Recorded in Neural Archive</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 no-print">
        <a href="student_dashboard.php" class="btn btn-outline-info rounded-pill px-5 fw-bold" style="font-family: 'Orbitron'; font-size: 0.8rem;">
            <i class="bi bi-arrow-left me-2"></i> BACK TO COMMAND CENTER
        </a>
    </div>
</div>

</body>
</html>