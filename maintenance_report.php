<?php
session_start();
require_once 'db_config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// 1. NEURAL ACCESS CONTROL: Only Students allowed
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

// Fetch categories from your 'category' table for the dropdown
// 1. READ: Fetch all categories from the registry
$cat_stmt = $pdo->query("SELECT * FROM category ORDER BY category_name ASC");
$categories = $cat_stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['std_id'];
    $category_id = $_POST['category_id']; 
    $priority = $_POST['priority'];
    $description = trim($_POST['description']);
    $request_id = "REQ-" . date("YmdHis"); 

    try {
        // 2. INTELLIGENT AUTO-ASSIGN: Find Staff in 'Maintenance' with the LOWEST workload
        $staff_query = "SELECT s.staff_id FROM staff s 
                        WHERE s.department = 'Maintenance' 
                        ORDER BY (SELECT COUNT(*) FROM maintenance_request WHERE assigned_staff_id = s.staff_id AND status != 'Completed') ASC 
                        LIMIT 1";
        $staff_stmt = $pdo->query($staff_query);
        $assigned_staff = $staff_stmt->fetchColumn();

        // 3. CREATE: Insert the request. Use 'In Progress' because a staff member is assigned immediately.
        $sql = "INSERT INTO maintenance_request (request_id, student_id, category_id, description, priority, status, assigned_staff_id, created_at) 
                VALUES (?, ?, ?, ?, ?, 'In Progress', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$request_id, $student_id, $category_id, $description, $priority, $assigned_staff])) {
            $success = "NEURAL LINK ESTABLISHED: Request #$request_id assigned to Technician ID: $assigned_staff";
        }
    } catch (PDOException $e) { 
        $error = "TRANSMISSION ERROR: " . $e->getMessage(); 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Log Report</title>
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

        /* --- Floating Navigation (Aligned with Admin/Hub) --- */
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

        /* --- Report Content Container --- */
        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 50px; backdrop-filter: blur(10px);
            max-width: 900px; margin: 0 auto;
        }

        .form-control, .form-select {
            background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border);
            color: white; border-radius: 12px; padding: 12px 20px;
            font-family: 'Rajdhani'; transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(0,0,0,0.6); border-color: var(--lesbot-cyan);
            color: white; box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .input-label { 
            font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); 
            letter-spacing: 2px; margin-bottom: 8px; font-weight: 700;
        }

        .btn-neural-submit {
            background: var(--lesbot-cyan); color: var(--obsidian);
            font-family: 'Orbitron'; font-weight: 900; font-size: 0.85rem;
            letter-spacing: 2px; padding: 18px; border: none; border-radius: 15px;
            transition: 0.3s; width: 100%; box-shadow: 0 5px 15px rgba(0, 212, 255, 0.3);
        }
        .btn-neural-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.5);
        }

        .btn-neural-outline {
            background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2);
            font-family: 'Orbitron'; font-size: 0.75rem; padding: 15px; border-radius: 15px;
            text-decoration: none; display: block; text-align: center; transition: 0.3s;
        }
        .btn-neural-outline:hover { background: rgba(255,255,255,0.05); border-color: white; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="student_dashboard.php">UTAMA</a></li>
        <li><a href="maintenance_report.php" class="active">REPORT</a></li>
        <li><a href="student_penalties.php">PENALTIES</a></li>
        <li><a href="student_history.php">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Orbitron'; font-weight: 900; margin: 0; color: var(--lesbot-cyan);">REPORT <span style="color: #fff;">ISSUE</span></h2>
            <p class="text-white-50 small mt-2" style="letter-spacing: 2px;">NEURAL LOGGING PROTOCOL • MAINTENANCE v3.0</p>
        </div>

        <?php if(isset($success)): ?>
            <div class="alert alert-info bg-dark border-info text-info text-center py-3 mb-4 rounded-3">
                <i class="bi bi-cpu-fill me-2"></i> <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
<label class="input-label">ISSUE CLASSIFICATION</label>
<select name="category_id" class="form-select" required>
    <option value="" disabled selected>Select Category...</option>
    <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['category_id'] ?>">
            <?= strtoupper($cat['category_name']) ?> (Class: <?= $cat['severity_level'] ?>)
        </option>
    <?php endforeach; ?>
</select>
                </div>
                <div class="col-md-6">
                    <label class="input-label">PRIORITY LEVEL</label>
                    <select name="priority" class="form-select" required>
                        <option value="Low">LOW</option>
                        <option value="Medium" selected>MEDIUM</option>
                        <option value="High">HIGH</option>
                        <option value="Urgent">URGENT</option>
                        <option value="Critical">CRITICAL</option>
                    </select>
                </div>
            </div>

            <div class="mb-5">
                <label class="input-label">NEURAL DESCRIPTION</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Describe the hardware glitch or physical malfunction in detail..." required></textarea>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn-neural-submit">
                    <i class="bi bi-broadcast me-2"></i> TRANSMIT REPORT
                </button>
                <a href="student_dashboard.php" class="btn-neural-outline">
                    <i class="bi bi-arrow-left me-2"></i> ABORT AND HUB
                </a>
            </div>
        </form>
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