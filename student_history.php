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

    $all_activities = [];

    // 3. AUDIT LOGIC A: Fetch Room History (Residencies)
    $h_stmt = $pdo->prepare("SELECT room_number, semester_session, move_in_date as activity_date FROM student_room_history WHERE matric_number = ?");
    $h_stmt->execute([$id]);
    while ($row = $h_stmt->fetch()) {
        $all_activities[] = [
            'date' => $row['activity_date'],
            'type' => 'RESIDENCY',
            'icon' => 'bi-house-door-fill',
            'color' => '#00d4ff', // Lesbot Cyan
            'title' => "Room Assigned: " . $row['room_number'],
            'desc' => "Commenced session " . $row['semester_session']
        ];
    }

    // 4. AUDIT LOGIC B: Fetch Maintenance Logs
    $m_stmt = $pdo->prepare("SELECT description, status, created_at FROM maintenance_request WHERE student_id = ?");
    $m_stmt->execute([$id]);
    while ($row = $m_stmt->fetch()) {
        $all_activities[] = [
            'date' => $row['created_at'],
            'type' => 'MAINTENANCE',
            'icon' => 'bi-tools',
            'color' => '#ffc107', // Warning Yellow
            'title' => "Maintenance Reported",
            'desc' => "Log: " . $row['description'] . " | Current Status: " . strtoupper($row['status'])
        ];
    }

    // 5. AUDIT LOGIC C: Fetch Penalty Logs
    $p_stmt = $pdo->prepare("SELECT pt.description as reason, sp.amount, sp.date_issued FROM student_penalties sp JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id WHERE sp.matric_number = ?");
    $p_stmt->execute([$id]);
    while ($row = $p_stmt->fetch()) {
        $all_activities[] = [
            'date' => $row['date_issued'],
            'type' => 'PENALTY',
            'icon' => 'bi-exclamation-octagon-fill',
            'color' => '#ff4d4d', // Danger Red
            'title' => "Penalty Issued: RM " . number_format($row['amount'], 2),
            'desc' => "Violation Record: " . $row['reason']
        ];
    }

    // 6. CHRONOLOGICAL SORT: Newest activities at the top
    usort($all_activities, function($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
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

        /* --- Floating Navigation --- */
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

        /* --- Timeline Dashboard Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);
        }

        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px; color: var(--lesbot-cyan); }
        
        /* --- Timeline Item Styling --- */
        .timeline-item { 
            border-left: 2px solid rgba(0, 212, 255, 0.2); 
            padding-left: 30px; 
            margin-bottom: 35px; 
            position: relative; 
            transition: 0.3s;
        }
        .timeline-item::before { 
            content: ''; position: absolute; left: -9px; top: 0; 
            width: 16px; height: 16px; background: var(--obsidian); 
            border: 2px solid var(--lesbot-cyan); border-radius: 50%; 
        }

        .sem-tag { 
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.6rem; 
            padding: 4px 12px; border-radius: 4px; text-transform: uppercase;
        }

        .btn-neural-tool {
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
            color: white; font-family: 'Orbitron'; font-size: 0.65rem;
            padding: 8px 18px; border-radius: 8px; transition: 0.3s; text-decoration: none;
        }
        .btn-neural-tool:hover { background: var(--lesbot-cyan); color: #000; border-color: var(--lesbot-cyan); }

        @media print {
            body { background: white !important; color: black !important; padding: 0; }
            .neural-nav, .no-print, .lesbot-chat-container { display: none !important; }
            .system-container { border: none !important; }
            .timeline-item { border-left-color: black !important; }
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
        <p class="text-white-50 small" style="letter-spacing: 2px;">NEURAL LOG ARCHIVE • ENTITY: <?= strtoupper($user['name']); ?></p>
        
        <div class="mt-4">
            <button onclick="window.print()" class="btn-neural-tool me-2">
                <i class="bi bi-printer me-2"></i> GENERATE PDF
            </button>
            <a href="export_data.php" class="btn-neural-tool">
                <i class="bi bi-file-earmark-arrow-down me-2"></i> EXPORT CSV
            </a>
        </div>
    </div>

    <div class="system-container shadow-lg">
        <?php if (empty($all_activities)): ?>
            <div class="text-center py-5">
                <i class="bi bi-archive fs-1 text-muted mb-3"></i>
                <p class="text-info" style="font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px;">NEURAL ARCHIVE EMPTY: NO LOGS DETECTED</p>
            </div>
        <?php else: ?>
            <div class="px-md-4">
                <?php foreach($all_activities as $activity): ?>
                    <div class="timeline-item" style="border-left-color: <?= $activity['color'] ?>;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="sem-tag" style="background: <?= $activity['color'] ?>; color: #000;">
                                    <?= $activity['type']; ?>
                                </span>
                            </div>
                            <span class="small text-white-50" style="font-family: 'Orbitron'; font-size: 0.65rem;">
                                <i class="bi bi-calendar3 me-2"></i><?= date('d M Y | H:i', strtotime($activity['date'])); ?>
                            </span>
                        </div>
                        
                        <h5 class="fw-bold mb-1" style="font-family: 'Rajdhani'; color: #fff;">
                            <i class="bi <?= $activity['icon'] ?> me-2" style="color: <?= $activity['color'] ?>;"></i>
                            <?= htmlspecialchars($activity['title']); ?>
                        </h5>
                        <p class="small text-white-50 mb-0">
                            <?= htmlspecialchars($activity['desc']); ?>
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

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>

</body>
</html>