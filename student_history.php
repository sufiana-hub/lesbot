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
    <title>LesBot | Neural Audit</title>
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

        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px; color: var(--lesbot-cyan); }
        
        /* --- Timeline Audit Styling --- */
        .timeline-item { 
            border-left: 2px solid var(--glass-border); 
            padding-left: 30px; 
            margin-bottom: 30px; 
            position: relative; 
            transition: 0.3s;
        }
        .timeline-item::before { 
            content: ''; position: absolute; left: -9px; top: 0; 
            width: 16px; height: 16px; background: var(--obsidian); 
            border: 2px solid var(--lesbot-cyan); border-radius: 50%; 
            box-shadow: 0 0 10px var(--lesbot-cyan);
        }
        .timeline-item:hover { border-left-color: var(--lesbot-cyan); }

        .sem-tag { 
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.65rem; 
            color: var(--obsidian); background: var(--lesbot-cyan); 
            padding: 4px 12px; border-radius: 4px; text-transform: uppercase;
        }

        .btn-neural-tool {
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
            color: white; font-family: 'Orbitron'; font-size: 0.65rem;
            padding: 8px 18px; border-radius: 8px; transition: 0.3s; text-decoration: none;
        }
        .btn-neural-tool:hover { background: var(--lesbot-cyan); color: var(--obsidian); border-color: var(--lesbot-cyan); }

        /* 🖨️ PRINT OPTIMIZATION */
        @media print {
            body { background: white !important; color: black !important; padding: 0; }
            .neural-nav, .no-print { display: none !important; }
            .system-container { background: none !important; border: none !important; padding: 0; }
            .timeline-item { border-left: 2px solid black !important; }
            .timeline-item::before { border: 2px solid black !important; background: black !important; }
            .sem-tag { border: 1px solid black !important; background: #eee !important; color: black !important; }
            .header-title { color: black !important; text-align: center; }
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
        <li><a href="student_history.php" class="active">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="text-center mb-5 no-print">
        <h2 class="header-title">AUDIT <span class="text-white">TRAIL</span></h2>
        <p class="text-white-50 small" style="letter-spacing: 2px;">RESIDENCY LOG ARCHIVE • ENTITY: <?= strtoupper($user['name']); ?></p>
        
        <div class="mt-4">
            <button onclick="window.print()" class="btn-neural-tool me-2">
                <i class="bi bi-printer me-2"></i> GENERATE PDF
            </button>
            <a href="export.php" class="btn-neural-tool">
                <i class="bi bi-file-earmark-arrow-down me-2"></i> EXPORT CSV
            </a>
        </div>
    </div>

    <div class="system-container shadow-lg">
        <?php if (empty($full_history)): ?>
            <div class="text-center py-5">
                <i class="bi bi-archive fs-1 text-muted mb-3"></i>
                <p class="text-cyan-bright" style="font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px;">NEURAL ARCHIVE EMPTY: NO LOGS FOUND</p>
            </div>
        <?php else: ?>
            <div class="px-md-4">
                <?php foreach($full_history as $index => $log): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="sem-tag"><?= htmlspecialchars($log['semester_session']); ?></span>
                                <?php if ($index === 0): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success ms-2 no-print" style="font-size: 0.55rem; border: 1px solid rgba(25, 135, 84, 0.5);">CURRENT</span>
                                <?php endif; ?>
                            </div>
                            <span class="small text-white-50" style="font-family: 'Orbitron'; font-size: 0.65rem;">
                                <i class="bi bi-calendar3 me-2"></i><?= date('d M Y', strtotime($log['move_in_date'])); ?>
                            </span>
                        </div>
                        
                        <h4 class="fw-bold mb-1" style="font-family: 'Rajdhani'; color: #fff;">Bilik: <span class="text-info"><?= htmlspecialchars($log['room_number']); ?></span></h4>
                        <p class="small text-white-50 mb-0">
                            <i class="bi bi-geo-alt me-1"></i> 
                            <?php 
                                echo (strpos(strtoupper($log['room_number']), 'A') === 0) ? "Residential Block A (Male)" : "Residential Block B (Female)"; 
                            ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5 no-print">
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

<?php include 'chatbot_component.php'; ?>

</body>
</html>