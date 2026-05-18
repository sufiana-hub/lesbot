<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Students allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$student_id = $_SESSION['std_id']; // Current logged-in student
$success_msg = "";
$error_msg = "";

// 2. FETCH CATEGORIES FOR DROPDOWN
try {
    $categories = $pdo->query("SELECT * FROM category")->fetchAll();
} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}

// 3. PROCESS FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'];
    $priority = $_POST['priority'];
    $description = trim($_POST['description']);

    if (!empty($description)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO maintenance_request (student_id, category_id, description, priority, status, created_at) 
                                   VALUES (?, ?, ?, ?, 'Pending', NOW())");
            $stmt->execute([$student_id, $category_id, $description, $priority]);
            $success_msg = "REQUEST UPLOADED TO NEURAL NETWORK SUCCESSFULLY.";
        } catch (PDOException $e) {
            $error_msg = "TRANSMISSION ERROR: " . $e->getMessage();
        }
    } else {
        $error_msg = "ERROR: DESCRIPTION FIELD CANNOT BE EMPTY.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Log Maintenance</title>

        <!-- Paste the Google tag here -->
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />

    <!-- site identity tags -->
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System">

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #0B0E14; }
        body { background-color: var(--obsidian); color: white; font-family: 'Rajdhani', sans-serif; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(0, 212, 255, 0.2); 
            border-radius: 15px; 
            padding: 30px;
            backdrop-filter: blur(10px);
        }
        .form-control, .form-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            color: white;
        }
        .form-control:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: var(--lesbot-cyan);
            color: white;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }
        .btn-neural {
            background: transparent;
            border: 2px solid var(--lesbot-cyan);
            color: var(--lesbot-cyan);
            font-family: 'Orbitron';
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-neural:hover {
            background: var(--lesbot-cyan);
            color: black;
            box-shadow: 0 0 20px var(--lesbot-cyan);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h2 class="text-center mb-4" style="font-family: 'Orbitron'; color: var(--lesbot-cyan);">INITIALIZE MAINTENANCE REQUEST</h2>
            
            <?php if($success_msg): ?>
                <div class="alert alert-success bg-dark text-info border-info"><?= $success_msg ?></div>
            <?php endif; ?>
            
            <?php if($error_msg): ?>
                <div class="alert alert-danger bg-dark text-danger border-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="glass-card shadow-lg">
                <form method="POST">
                    <div class="mb-4">
                        <label class="small fw-bold text-info mb-2">SELECT ISSUE CATEGORY</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"><?= strtoupper($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-info mb-2">SET PRIORITY LEVEL</label>
                        <select name="priority" class="form-select">
                            <option value="Low">LOW - Routine Maintenance</option>
                            <option value="Medium" selected>MEDIUM - Standard Issue</option>
                            <option value="High">HIGH - Urgent/Critical</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-info mb-2">DETAILED DESCRIPTION</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the neural/physical glitch..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-neural w-100 py-3">TRANSMIT REQUEST</button>
                </form>
            </div>

            <div class="text-center mt-4">
                <a href="student_dashboard.php" class="text-white-50 text-decoration-none small">← BACK TO SYSTEM HUB</a>
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