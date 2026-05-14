<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Students allowed
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

    // 3. READ: Fetch Room History
    $h_stmt = $pdo->prepare("SELECT * FROM student_room_history WHERE matric_number = ? ORDER BY move_in_date DESC");
    $h_stmt->execute([$id]);
    $room_history = $h_stmt->fetchAll();

    $current_room = (!empty($room_history)) ? $room_history[0]['room_number'] : $student['room_number'];
    $wing = (strpos(strtoupper($current_room), 'A') === 0) ? "Block A (Male)" : "Block B (Female)";

    // 4. ANALYTICS: Fine and Maintenance Summary
    $p_stmt = $pdo->prepare("SELECT SUM(amount) FROM student_penalties WHERE matric_number = ? AND is_paid = 0");
    $p_stmt->execute([$id]);
    $total_p = $p_stmt->fetchColumn() ?: "0.00";

    $m_stmt = $pdo->prepare("SELECT COUNT(*) FROM maintenance_request WHERE student_id = ? AND status != 'Completed'");
    $m_stmt->execute([$id]);
    $active_tickets = $m_stmt->fetchColumn() ?: "0";

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Student Command</title>
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

        /* --- Dashboard Content Containers --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);
        }

        .profile-glass {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px; padding: 3rem; margin-bottom: 50px;
        }

        .info-label { font-family: 'Orbitron'; font-size: 0.75rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 700; }
        .data-value { font-family: 'Orbitron'; font-weight: 400; letter-spacing: 1px; color: #FFFFFF; text-transform: uppercase; }

        /* Hub Menu Items */
        .menu-box {
            background: rgba(0, 212, 255, 0.05); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px; transition: 0.3s ease;
            text-decoration: none; color: white; display: block; height: 100%;
        }
        .menu-box:hover {
            border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1);
            transform: translateY(-5px); box-shadow: 0 0 20px rgba(0, 212, 255, 0.2);
        }
        .menu-box i { font-size: 2.5rem; margin-bottom: 15px; display: block; }
        .menu-box h5 { font-family: 'Orbitron'; font-weight: 700; font-size: 0.9rem; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="student_dashboard.php" class="active">UTAMA</a></li>
        <li><a href="maintenance_report.php">REPORT</a></li>
        <li><a href="student_penalties.php">PENALTIES</a></li>
        <li><a href="student_history.php">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px;">STUDENT <span style="color: var(--lesbot-cyan);">HUB</span></h2>
            <p class="text-white-50 small">NEURAL IDENTITY VERIFIED • SYSTEM STATUS: ONLINE</p>
        </div>

        <div class="profile-glass shadow-lg">
            <div class="row mb-3 border-bottom border-secondary pb-2 align-items-center">
                <div class="col-sm-4 info-label">ENTITY NAME</div>
                <div class="col-sm-8 data-value"><?= htmlspecialchars($student['name']) ?></div>
            </div>
            <div class="row mb-3 border-bottom border-secondary pb-2 align-items-center">
                <div class="col-sm-4 info-label">MATRIC IDENTIFIER</div>
                <div class="col-sm-8 data-value"><?= htmlspecialchars($id) ?></div>
            </div>
            <div class="row mb-3 border-bottom border-secondary pb-2 align-items-center">
                <div class="col-sm-4 info-label">E-MAIL ADDRESS</div>
                <div class="col-sm-8 data-value text-lowercase" style="font-family: 'Rajdhani';"><?= htmlspecialchars($student['email']) ?></div>
            </div>
            <div class="row mb-3 border-bottom border-secondary pb-2 align-items-center">
                <div class="col-sm-4 info-label">CURRENT Bilik</div>
                <div class="col-sm-8 data-value">
                    <?= htmlspecialchars($current_room) ?> - 
                    <span class="text-info fw-bold" style="font-size: 0.8rem;"><?= strtoupper($wing) ?></span>
                </div>
            </div>
            <div class="row mb-4 border-bottom border-secondary pb-2 align-items-center">
                <div class="col-sm-4 info-label">STATUS</div>
                <div class="col-sm-8">
                    <span class="badge bg-success" style="font-family: 'Orbitron'; font-size: 0.6rem; letter-spacing: 1px;">ACTIVE MEMBER</span>
                </div>
            </div>

            <div class="mt-4 p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary">
                <h6 class="info-label text-info mb-3" style="font-size: 0.65rem;">
                    <i class="bi bi-clock-history me-2"></i> SEMESTER Bilik HISTORY
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-dark table-hover mb-0" style="font-size: 0.8rem; --bs-table-bg: transparent;">
                        <thead>
                            <tr class="text-muted small" style="font-family: 'Orbitron';">
                                <th>SESSION</th><th>Bilik</th><th>WING</th><th>DATE ASSIGNED</th>
                            </tr>
                        </thead>
                        <tbody style="font-family: 'Rajdhani';">
                            <?php foreach($room_history as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['semester_session']) ?></td>
                                    <td class="text-info fw-bold"><?= htmlspecialchars($log['room_number']) ?></td>
                                    <td><?= (strpos(strtoupper($log['room_number']), 'A') === 0) ? "A" : "B" ?></td>
                                    <td class="text-white-50"><?= date('d-m-Y', strtotime($log['move_in_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h6 class="text-uppercase mb-4 text-center" style="letter-spacing: 5px; font-family: 'Orbitron'; font-size: 0.7rem; opacity: 0.5;">Access Primary Protocols</h6>
        <div class="row g-4 justify-content-center text-center">
            <div class="col-md-3">
                <a href="maintenance_report.php" class="menu-box">
                    <i class="bi bi-tools text-primary"></i>
                    <h5>REPORT ISSUE</h5>
                    <p class="small text-white-50 mt-2">Log Maintenance</p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="track_request.php" class="menu-box">
                    <i class="bi bi-search text-info"></i>
                    <h5>TRACK REQUEST</h5>
                    <p class="small text-white-50 mt-2"><?= $active_tickets ?> Active Tickets</p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="student_penalties.php" class="menu-box">
                    <i class="bi bi-credit-card-2-front text-danger"></i>
                    <h5>PENALTIES</h5>
                    <p class="small text-white-50 mt-2">Debt: RM <?= $total_p ?></p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="student_history.php" class="menu-box">
                    <i class="bi bi-clock-history text-warning"></i>
                    <h5>AUDIT LOGS</h5>
                    <p class="small text-white-50 mt-2">Bilik History</p>
                </a>
            </div>
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