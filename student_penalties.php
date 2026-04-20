<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
// Ensures only the logged-in student can access their financial records
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    // 2. READ: Fetch Unpaid Penalties (Outstanding)
    // Joins with penalty_types to get the specific reason for the fine
    $unpaid_stmt = $pdo->prepare("SELECT sp.*, pt.description as reason 
                                  FROM student_penalties sp 
                                  JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                                  WHERE sp.matric_number = ? AND sp.is_paid = 0");
    $unpaid_stmt->execute([$id]);
    $outstanding = $unpaid_stmt->fetchAll();

    // 3. READ: Fetch Paid Penalties (History)
    // Ordered by date to show the most recent payments first
    $paid_stmt = $pdo->prepare("SELECT sp.*, pt.description as reason 
                                FROM student_penalties sp 
                                JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                                WHERE sp.matric_number = ? AND sp.is_paid = 1 
                                ORDER BY sp.date_issued DESC");
    $paid_stmt->execute([$id]);
    $history = $paid_stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Student Penalties</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-obsidian: #0B0E14; 
            --lesbot-cyan: #00d4ff; 
            --lesbot-red: #ff4d4d;
        }
        body { 
            background-color: var(--lesbot-obsidian); 
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
        }
        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px; color: var(--lesbot-cyan); }
        .penalty-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(0, 212, 255, 0.1); 
            border-radius: 20px; 
            padding: 2rem; 
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        .section-label { 
            font-family: 'Orbitron'; 
            font-size: 0.9rem; 
            color: var(--lesbot-cyan); 
            border-bottom: 1px solid rgba(0, 212, 255, 0.3); 
            padding-bottom: 10px; 
            margin-bottom: 20px;
        }
        .table { color: #ffffff; border-color: rgba(255,255,255,0.1); }
        .btn-pay { 
            background: var(--lesbot-cyan); 
            color: var(--lesbot-obsidian); 
            font-family: 'Orbitron'; 
            font-weight: 700; 
            font-size: 0.75rem; 
            border-radius: 50px;
        }
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
        <h2 class="header-title">PENALTY MANAGEMENT</h2>
        <p class="text-white-50 small">FINANCIAL NEURAL LINK • SYSTEM AUDIT ACTIVE</p>
    </div>

    <div class="penalty-card shadow-lg">
        <h5 class="section-label">PAYMENT HISTORY</h5>
        <?php if (empty($history)): ?>
            <p class="text-muted small">No payment history found in the database.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="text-muted small">
                        <tr><th>DATE</th><th>DESCRIPTION</th><th>AMOUNT (RM)</th><th>STATUS</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($history as $h): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($h['date_issued'])) ?></td>
                            <td><?= htmlspecialchars($h['reason']) ?></td>
                            <td class="fw-bold">RM <?= number_format($h['amount'], 2) ?></td>
                            <td><span class="badge bg-success small" style="font-size: 0.6rem;">PAID</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="penalty-card shadow-lg">
        <h5 class="section-label" style="color: var(--lesbot-red); border-color: rgba(255,77,77,0.3);">YOUR PENALTIES DETAILS</h5>
        <?php if (empty($outstanding)): ?>
            <p class="text-success fw-bold">No outstanding penalties! All neural connections are clear.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="text-muted small">
                        <tr><th>DATE ISSUED</th><th>DESCRIPTION</th><th>AMOUNT (RM)</th><th>ACTION</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($outstanding as $o): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($o['date_issued'])) ?></td>
                            <td><?= htmlspecialchars($o['reason']) ?></td>
                            <td class="text-danger fw-bold">RM <?= number_format($o['amount'], 2) ?></td>
                            <td>
                                <a href="pay_penalty.php?id=<?= $o['penalty_id'] ?>" class="btn btn-pay btn-sm px-4">PAY NOW</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

  <div class="text-center mt-5">
        <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-2 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.8rem;">
            <i class="bi bi-arrow-left me-2"></i> BACK TO COMMAND CENTER
        </a>
    </div>
</div>

</body>
</html>