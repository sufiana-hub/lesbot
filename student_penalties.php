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
    // 2. READ: Fetch Unpaid Penalties (Outstanding)
    $unpaid_stmt = $pdo->prepare("SELECT sp.*, pt.description as reason 
                                  FROM student_penalties sp 
                                  JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                                  WHERE sp.matric_number = ? AND sp.is_paid = 0");
    $unpaid_stmt->execute([$id]);
    $outstanding = $unpaid_stmt->fetchAll();

    // 3. READ: Fetch Paid Penalties (History)
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
    <title>LesBot | Neural Ledger</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --lesbot-red: #ff4d4d;
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
            margin-bottom: 30px;
        }

        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px; color: var(--lesbot-cyan); }
        
        .section-label { 
            font-family: 'Orbitron'; font-size: 0.8rem; color: var(--lesbot-cyan); 
            letter-spacing: 2px; margin-bottom: 20px; display: flex; align-items: center;
        }
        .section-label i { margin-right: 10px; font-size: 1.2rem; }

        /* --- Modern Data Table Styling --- */
        .table { color: #ffffff; --bs-table-bg: transparent; --bs-table-hover-bg: rgba(0, 212, 255, 0.05); }
        .table thead th { 
            font-family: 'Orbitron'; font-size: 0.7rem; color: rgba(255,255,255,0.5);
            border-bottom: 1px solid var(--glass-border); text-transform: uppercase;
        }
        .table tbody td { vertical-align: middle; padding: 1rem 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05); }

        .btn-pay { 
            background: var(--lesbot-cyan); color: var(--obsidian); 
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.65rem; 
            border-radius: 8px; padding: 8px 20px; transition: 0.3s; border: none;
        }
        .btn-pay:hover { box-shadow: 0 0 15px var(--lesbot-cyan); transform: scale(1.05); }
        
        .badge-status { font-family: 'Orbitron'; font-size: 0.6rem; padding: 5px 12px; border-radius: 4px; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="student_dashboard.php">UTAMA</a></li>
        <li><a href="maintenance_report.php">REPORT</a></li>
        <li><a href="student_penalties.php" class="active">PENALTIES</a></li>
        <li><a href="student_history.php">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="text-center mb-5">
        <h2 class="header-title">NEURAL <span class="text-white">LEDGER</span></h2>
        <p class="text-white-50 small" style="letter-spacing: 2px;">SECURE FINANCIAL LINK ACTIVE • SYSTEM AUDIT v3.0</p>
    </div>

    <div class="system-container shadow-lg" style="border-color: rgba(255, 77, 77, 0.3);">
        <h5 class="section-label" style="color: var(--lesbot-red);">
            <i class="bi bi-exclamation-triangle-fill"></i> OUTSTANDING PENALTIES
        </h5>
        <?php if (empty($outstanding)): ?>
            <div class="text-center py-4">
                <i class="bi bi-shield-check text-success fs-1 mb-2"></i>
                <p class="text-success fw-bold m-0" style="font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px;">LEDGER CLEAR: NO DEBT DETECTED</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>DATE ISSUED</th><th>DESCRIPTION</th><th>AMOUNT</th><th class="text-end">ACTION</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($outstanding as $o): ?>
                        <tr>
                            <td class="small text-white-50"><?= date('d/m/Y', strtotime($o['date_issued'])) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($o['reason']) ?></td>
                            <td class="text-danger fw-bold" style="font-family: 'Orbitron';">RM <?= number_format($o['amount'], 2) ?></td>
                            <td class="text-end">
                                <a href="pay_penalty.php?id=<?= $o['penalty_id'] ?>" class="btn btn-pay">INITIALIZE PAYMENT</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="system-container shadow-lg">
        <h5 class="section-label">
            <i class="bi bi-clock-history"></i> TRANSACTION ARCHIVE
        </h5>
        <?php if (empty($history)): ?>
            <p class="text-cyan-bright small text-center">No payment history found in the neural database.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>DATE</th><th>DESCRIPTION</th><th>SETTLEMENT</th><th>STATUS</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($history as $h): ?>
                        <tr>
                            <td class="small text-white-50"><?= date('d/m/Y', strtotime($h['date_issued'])) ?></td>
                            <td><?= htmlspecialchars($h['reason']) ?></td>
                            <td class="fw-bold" style="font-family: 'Orbitron';">RM <?= number_format($h['amount'], 2) ?></td>
                            <td><span class="badge-status bg-success">SETTLED</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5">
        <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-3 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.75rem; letter-spacing: 2px;">
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