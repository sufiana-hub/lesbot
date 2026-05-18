<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Staff permitted [cite: 2740]
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); 
    exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // 2. ANALYTICS: System-wide Population Metrics
    $student_count = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
    $staff_count   = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $admin_count   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'")->fetchColumn();

    // 3. FETCH STAFF PROFILE: "Box-inside-Box" Data
    $s_stmt = $pdo->prepare("SELECT u.name, u.email, st.department, st.phone_num 
                             FROM users u 
                             JOIN staff st ON u.user_id = st.staff_id 
                             WHERE u.user_id = ?");
    $s_stmt->execute([$staff_id]);
    $staff = $s_stmt->fetch();

    // 4. ANALYTICS: Tactical Status Metrics
    $m_stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
        FROM maintenance_request WHERE assigned_staff_id = ?");
    $m_stmt->execute([$staff_id]);
    $stats = $m_stmt->fetch();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Staff Hub</title>
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
            color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 120px; min-height: 100vh;
        }

        /* --- NON-PLAIN FLOATING NAVIGATION --- */
        .tactical-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85);
            backdrop-filter: blur(25px); border: 1px solid var(--glass-border);
            border-radius: 50px; padding: 10px 30px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .nav-links-cluster { display: flex; gap: 10px; }
        
        .nav-variant {
            color: rgba(255,255,255,0.5); text-decoration: none; font-family: 'Orbitron';
            font-size: 0.65rem; letter-spacing: 2px; padding: 10px 15px; border-radius: 12px;
            transition: 0.3s; border: 1px solid transparent;
        }
        .nav-variant:hover { color: var(--lesbot-cyan); border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.05); }
        .nav-variant.active { background: var(--lesbot-cyan); color: var(--obsidian); font-weight: 900; }

        /* --- BOX INSIDE BOX PROFILE (Student Style) --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 35px; padding: 50px; backdrop-filter: blur(15px);
        }

        .profile-layer-outer {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            border-radius: 25px; padding: 30px; margin-bottom: 30px;
        }
        .profile-row { border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding: 12px 0; align-items: center; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        .value-white { font-family: 'Orbitron'; font-size: 0.85rem; color: #FFF; text-transform: uppercase; }

        /* --- METRICS BAR --- */
        .metric-pill {
            background: rgba(255, 255, 255, 0.02); border-radius: 15px; padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05); text-align: center; transition: 0.3s;
        }
        .metric-pill:hover { border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.03); }
        .metric-val { font-family: 'Orbitron'; font-weight: 900; font-size: 1.5rem; display: block; }

        /* --- MENU GRID --- */
        .hub-box {
            background: rgba(255, 255, 255, 0.02); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 30px; transition: 0.4s ease;
            text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center;
        }
        .hub-box:hover {
            border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1);
            transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 212, 255, 0.2);
        }
        .hub-box i { font-size: 2.2rem; margin-bottom: 15px; }
        .hub-box h5 { font-family: 'Orbitron'; font-weight: 700; font-size: 0.75rem; letter-spacing: 1px; margin: 0; }
    </style>
</head>
<body>

<nav class="tactical-nav">
    <a href="#" class="nav-variant active" style="font-weight: 900; letter-spacing: 3px;">LESBOT STAFF</a>
    
    <div class="nav-links-cluster">
        <a href="staff_dashboard.php" class="nav-variant active">DASHBOARD</a>
        <a href="staff_tasks.php" class="nav-variant">MY REPORTS</a>
        <a href="staff_penalties.php" class="nav-variant">PENALTIES</a>
        <a href="staff_archive.php" class="nav-variant">ARCHIVE</a>
    </div>

    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="system-container shadow-lg">
        
        <div class="profile-layer-outer shadow">
            <h6 class="mb-4" style="font-family: 'Orbitron'; font-size: 0.6rem; color: rgba(255,255,255,0.3); letter-spacing: 3px;">AUTHENTICATED PERSONNEL</h6>
            <div class="row profile-row">
                <div class="col-md-4 label-neon">NAME</div>
                <div class="col-md-8 value-white"><?= htmlspecialchars($staff['name']) ?></div>
            </div>
            <div class="row profile-row">
                <div class="col-md-4 label-neon">STAFF ID</div>
                <div class="col-md-8 value-white text-info"><?= $staff_id ?></div>
            </div>
            <div class="row profile-row" style="border:none;">
                <div class="col-md-4 label-neon">ASSIGNED UNIT</div>
                <div class="col-md-8 value-white"><?= htmlspecialchars($staff['department']) ?></div>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="metric-pill">
                    <span class="label-neon" style="font-size:0.6rem">RESOLVED</span>
                    <span class="metric-val text-success"><?= $stats['resolved'] ?? 0 ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-pill">
                    <span class="label-neon" style="font-size:0.6rem">PENDING</span>
                    <span class="metric-val text-warning"><?= $stats['pending'] ?? 0 ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-pill">
                    <span class="label-neon" style="font-size:0.6rem">ON HOLD</span>
                    <span class="metric-val text-info"><?= $stats['on_hold'] ?? 0 ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-pill">
                    <span class="label-neon" style="font-size:0.6rem">REJECTED</span>
                    <span class="metric-val text-danger"><?= $stats['rejected'] ?? 0 ?></span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="hub-box">
                    <i class="bi bi-people-fill text-info"></i>
                    <h5>1. USER POPULATION</h5>
                    <p class="small text-white-50 mt-2">A:<?= $admin_count ?> | S:<?= $staff_count ?> | ST:<?= $student_count ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <a href="staff_tasks.php" class="hub-box">
                    <i class="bi bi-cpu-fill text-warning"></i>
                    <h5>2. ASSIGNED REPORTS</h5>
                    <p class="small text-white-50 mt-2">ACCESS FIELD TICKETS</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="staff_penalties.php" class="hub-box">
                    <i class="bi bi-shield-lock-fill text-danger"></i>
                    <h5>3. MANAGE PENALTIES</h5>
                    <p class="small text-white-50 mt-2">RECEIPT CONFIRMATION</p>
                </a>
            </div>
        </div>

        <div class="mt-5 text-center p-4" style="border: 1px dashed var(--glass-border); border-radius: 15px;">
            <p class="small text-white-50 mb-0">FYP ENHANCEMENT: AI CHATBOT PROTOCOL INTEGRATED FOR STUDENT RELIABILITY</p>
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