<?php
session_start();
require_once 'db_config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- 1. NEURAL MAIL ENGINE ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure this path is correct for your PHPMailer installation
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

/**
 * 2. NEURAL AUDIT BYPASS 
 */
$is_audit = (isset($_POST['audit_key']) && $_POST['audit_key'] === 'LESBOT_INTERNAL_AUDIT_2026');

if (!$is_audit) {
    if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') {
        header("Location: login.php");
        exit();
    }
    $student_id = $_SESSION['std_id'];
} else {
    $student_id = 'B032410816'; 
}

// 3. DATA ACQUISITION
$cat_stmt = $pdo->query("SELECT * FROM category ORDER BY category_name ASC");
$categories = $cat_stmt->fetchAll();

$success = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'] ?? 1; 
    $priority = $_POST['priority'] ?? 'Medium';
    $description = trim($_POST['description'] ?? 'Neural Audit Mode');
    $request_id = "REQ-" . date("YmdHis"); 

    try {
        // 4. AUTO-ASSIGN LOGIC (Now matches your 'staff' table structure)
        $staff_query = "SELECT s.staff_id, s.email, s.staff_name 
                        FROM staff s 
                        WHERE s.department = 'Maintenance' 
                        ORDER BY (SELECT COUNT(*) FROM maintenance_request WHERE assigned_staff_id = s.staff_id AND status != 'Completed') ASC 
                        LIMIT 1";
        $staff_row = $pdo->query($staff_query)->fetch(PDO::FETCH_ASSOC);
        
        $assigned_staff_id = $staff_row['staff_id'] ?? null;
        $staff_email = $staff_row['email'] ?? null;
        $staff_name = $staff_row['staff_name'] ?? 'Maintenance Specialist';

        // 5. DATABASE COMMIT
        $sql = "INSERT INTO maintenance_request (request_id, student_id, category_id, description, priority, status, assigned_staff_id, created_at) 
                VALUES (?, ?, ?, ?, ?, 'In Progress', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$request_id, $student_id, $category_id, $description, $priority, $assigned_staff_id])) {
            
            $email_status = ""; 

            // 6. GMAIL TRANSMISSION
            if ($staff_email) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'YOUR_GMAIL@gmail.com'; // CHANGE THIS
                    $mail->Password   = 'YOUR_APP_PASSWORD';      // CHANGE THIS (16 digits)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('lesbot-system@utem.edu.my', 'LESBOT PROTOCOL');
                    $mail->addAddress($staff_email, $staff_name);

                    $mail->isHTML(true);
                    $mail->Subject = "TASK ASSIGNED: $request_id";
                    $mail->Body    = "
                        <div style='background-color: #080a0f; color: #ffffff; padding: 20px; border: 1px solid #00d4ff;'>
                            <h2 style='color: #00d4ff;'>NEURAL ASSIGNMENT</h2>
                            <p>Greetings Specialist <strong>$staff_name</strong>,</p>
                            <p>A new maintenance request requires your attention.</p>
                            <hr>
                            <p><strong>ID:</strong> $request_id<br>
                            <strong>Priority:</strong> $priority<br>
                            <strong>Description:</strong> $description</p>
                        </div>";

                    $mail->send();
                    $email_status = "(Staff Notified)";
                } catch (Exception $e) {
                    $email_status = "(Mail Error: Check Config)";
                }
            }

            if ($is_audit && isset($_POST['audit_mode'])) {
                header('Content-Type: text/plain');
                echo "NEURAL LINK ESTABLISHED"; exit(); 
            }
            
            $success = "NEURAL LINK ESTABLISHED: Request #$request_id assigned. $email_status";
        }
    } catch (PDOException $e) { 
        if ($is_audit) { echo "DB ERROR: " . $e->getMessage(); exit(); }
        $error = "TRANSMISSION ERROR: " . $e->getMessage(); 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Log Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; padding-top: 100px; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; background: rgba(8, 10, 15, 0.8); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-brand { font-family: 'Orbitron'; color: var(--lesbot-cyan); text-decoration: none; font-weight: 900; }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; max-width: 900px; margin: 0 auto; backdrop-filter: blur(10px); }
        .form-control, .form-select { background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); color: white; border-radius: 12px; }
        .input-label { font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px; }
        .btn-neural-submit { background: var(--lesbot-cyan); color: var(--obsidian); font-family: 'Orbitron'; font-weight: 900; padding: 18px; border: none; border-radius: 15px; width: 100%; }
    </style>
</head>
<body>
<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <div style="display:flex; gap:20px;">
        <a href="student_dashboard.php" style="color:white; text-decoration:none; font-family:'Orbitron'; font-size:0.7rem;">UTAMA</a>
        <a href="maintenance_report.php" style="color:var(--lesbot-cyan); text-decoration:none; font-family:'Orbitron'; font-size:0.7rem;">REPORT</a>
    </div>
</nav>

<div class="container">
    <div class="system-container shadow-lg">
        <h2 class="text-center" style="font-family:'Orbitron'; color:var(--lesbot-cyan);">REPORT ISSUE</h2>
        
        <?php if($success): ?>
            <div class="alert alert-info bg-dark border-info text-info text-center"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="input-label">ISSUE CLASSIFICATION</label>
                    <select name="category_id" class="form-select" required>
                        <option value="" disabled selected>Select Category...</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= strtoupper($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="input-label">PRIORITY LEVEL</label>
                    <select name="priority" class="form-select" required>
                        <option value="Low">LOW</option>
                        <option value="Medium" selected>MEDIUM</option>
                        <option value="High">HIGH</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="input-label">NEURAL DESCRIPTION</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn-neural-submit">TRANSMIT REPORT</button>
        </form>
    </div>
</div>
</body>
</html>