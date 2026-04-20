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
    // 2. READ: Fetch Profile Details
    $sql = "SELECT u.name, u.email, s.room_number FROM users u 
            JOIN student s ON u.user_id = s.matric_number 
            WHERE u.user_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    // 3. READ: Fetch Room History (Latest to Oldest)
    $h_stmt = $pdo->prepare("SELECT * FROM student_room_history WHERE matric_number = ? ORDER BY move_in_date DESC");
    $h_stmt->execute([$id]);
    $room_history = $h_stmt->fetchAll();

    // Determine current room from history or fallback to profile
    $current_room = (!empty($room_history)) ? $room_history[0]['room_number'] : $student['room_number'];
    $wing = (strpos(strtoupper($current_room), 'A') === 0) ? "Blok A (Lelaki)" : "Blok B (Perempuan)";

    // 4. ANALYTICS: Fine Summary
    $p_stmt = $pdo->prepare("SELECT SUM(amount) FROM student_penalties WHERE matric_number = ? AND is_paid = 0");
    $p_stmt->execute([$id]);
    $total_p = $p_stmt->fetchColumn() ?: "0.00";

// 4. ANALYTICS: Fine Summary
    $p_stmt = $pdo->prepare("SELECT SUM(amount) FROM student_penalties WHERE matric_number = ? AND is_paid = 0");
    $p_stmt->execute([$id]);
    $total_p = $p_stmt->fetchColumn() ?: "0.00";

    // --- INSERT START HERE ---
    // 5. ANALYTICS: Maintenance Summary 
    // This counts how many requests are NOT 'Completed'
    $m_stmt = $pdo->prepare("SELECT COUNT(*) FROM maintenance_request WHERE student_id = ? AND status != 'Completed'");
    $m_stmt->execute([$id]);
    $active_tickets = $m_stmt->fetchColumn() ?: "0";
    // --- INSERT END HERE ---

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Student Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --baby-blue: #A7C7E7; }
        body { background-color: #f8f9fa; font-family: 'Rajdhani', sans-serif; }
        .profile-card { background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .info-label { font-weight: 700; color: #555; font-family: 'Orbitron'; font-size: 0.75rem; }
        .glass-baby-blue {
            background: rgba(167, 199, 231, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(167, 199, 231, 0.3);
            border-radius: 20px;
            transition: 0.4s;
            text-decoration: none; color: inherit;
        }
        .glass-baby-blue:hover { background: rgba(167, 199, 231, 0.25); transform: translateY(-5px); }
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
    <h2 class="text-center mb-5" style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px;">MAKLUMAT PELAJAR</h2>

    <div class="row justify-content-center mb-5">
        <div class="col-md-9 profile-card p-5">
            <div class="row mb-3 border-bottom pb-2">
                <div class="col-sm-4 info-label">NAMA </div>
                <div class="col-sm-8 text-uppercase"><?php echo htmlspecialchars($student['name']); ?></div>
            </div>
            <div class="row mb-3 border-bottom pb-2">
                <div class="col-sm-4 info-label">KAD MATRIK </div>
                <div class="col-sm-8"><?php echo htmlspecialchars($id); ?></div>
            </div>
            <div class="row mb-3 border-bottom pb-2">
                <div class="col-sm-4 info-label">E-MEL </div>
                <div class="col-sm-8"><?php echo htmlspecialchars($student['email']); ?></div>
            </div>
            <div class="row mb-3 border-bottom pb-2">
                <div class="col-sm-4 info-label">LOKASI BILIK </div>
                <div class="col-sm-8"><?php echo htmlspecialchars($current_room); ?> - <span class="text-primary fw-bold"><?php echo $wing; ?></span></div>
            </div>
            <div class="row mb-4 border-bottom pb-2">
                <div class="col-sm-4 info-label">STATUS </div>
                <div class="col-sm-8"><span class="badge bg-success">ACTIVE</span></div>
            </div>

            <div class="mt-4 p-3 bg-light rounded-3">
                <h6 class="info-label text-info mb-3"><i class="bi bi-clock-history"></i> SEMESTER ROOM HISTORY</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr class="text-muted"><th>SESSION</th><th>ROOM</th><th>WING</th><th>DATE ASSIGNED</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($room_history as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['semester_session']); ?></td>
                                    <td><?php echo htmlspecialchars($log['room_number']); ?></td>
                                    <td><?php echo (strpos(strtoupper($log['room_number']), 'A') === 0) ? "A" : "B"; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($log['move_in_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-4" style="font-family: 'Orbitron'; letter-spacing: 2px;">MENU UTAMA</h2>
    <div class="row g-4 justify-content-center text-center">
        <div class="col-md-4">
            <a href="maintenance_report.php" class="glass-baby-blue d-block p-4 h-100">
                <i class="bi bi-tools fs-1 text-primary"></i>
                <h5 class="mt-3 fw-bold">1. REPORT ISSUE</h5>
                <p class="small text-muted mb-0">" Lapor Kerosakan Bilik "</p>
            </a>
        </div>
<div class="col-md-4">
    <a href="track_request.php" class="glass-baby-blue d-block p-4 h-100">
        <i class="bi bi-search fs-1 text-info"></i>
        <h5 class="mt-3 fw-bold">2. TRACK REQUEST</h5>
        <p class="small text-muted mb-0">" Status: <?php echo $active_tickets; ?> Tiket Aktif "</p>
    </a>
</div>
        <div class="col-md-4">
            <a href="student_penalties.php" class="glass-baby-blue d-block p-4 h-100">
                <i class="bi bi-credit-card-2-front fs-1 text-danger"></i>
                <h5 class="mt-3 fw-bold">3. PENALTIES</h5>
                <p class="small text-muted mb-0">" Saman: RM <?php echo $total_p; ?> "</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="student_history.php" class="glass-baby-blue d-block p-4 h-100">
                <i class="bi bi-clock-history fs-1 text-warning"></i>
                <h5 class="mt-3 fw-bold">4. STUDENT HISTORY</h5>
                <p class="small text-muted mb-0">" Rekod Perpindahan Bilik "</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="logout.php" class="glass-baby-blue d-block p-4 h-100">
                <i class="bi bi-power fs-1 text-secondary"></i>
                <h5 class="mt-3 fw-bold">5. EXIT SYSTEM</h5>
                <p class="small text-muted mb-0">" Log Keluar Selamat "</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>