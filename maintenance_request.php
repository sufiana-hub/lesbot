<?php
session_start();
require_once 'db_config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- 1. NEURAL MAIL ENGINE ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        /**
         * 4. AUTO-ASSIGN LOGIC (THE VISION)
         * We JOIN 'staff' and 'users' to get the email/name from the users table 
         * where the data actually exists in your database.
         */
        $staff_query = "SELECT s.staff_id, u.email, u.name as staff_name 
                        FROM staff s 
                        JOIN users u ON s.staff_id = u.user_id 
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
                    $mail->Username   = 'your-gmail@gmail.com'; // [ACTION]: Put your Gmail here
                    $mail->Password   = 'your-app-password';    // [ACTION]: Put your 16-digit App Password here
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('lesbot-system@utem.edu.my', 'LESBOT PROTOCOL');
                    $mail->addAddress($staff_email, $staff_name);

                    $mail->isHTML(true);
                    $mail->Subject = "NEW NEURAL ASSIGNMENT: $request_id";
                    $mail->Body    = "
                        <div style='background-color: #080a0f; color: #ffffff; padding: 25px; border: 2px solid #00d4ff; font-family: sans-serif;'>
                            <h2 style='color: #00d4ff; border-bottom: 1px solid #00d4ff;'>TASK ASSIGNED</h2>
                            <p>Greetings Specialist <strong>$staff_name</strong>,</p>
                            <p>A new maintenance request has been auto-assigned to your terminal.</p>
                            <div style='background: rgba(255,255,255,0.05); padding: 15px;'>
                                <strong>Request ID:</strong> $request_id<br>
                                <strong>Priority:</strong> <span style='color:#ff4d4d;'>$priority</span><br>
                                <strong>Log:</strong> $description
                            </div>
                            <p style='font-size: 10px; margin-top: 20px;'>TRANSMITTED VIA LESBOT CORE v3.0</p>
                        </div>";

                    $mail->send();
                    $email_status = "(Staff Notified via Gmail)";
                } catch (Exception $e) {
                    $email_status = "(Mail Engine Offline)";
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
        body { background-color: var(--obsidian); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; padding-top: 100px; background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; background: rgba(8, 10, 15, 0.8); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-brand { font-family: 'Orbitron'; color: var(--lesbot-cyan); text-decoration: none; font-weight: 900; }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; max-width: 900px; margin: 0 auto; backdrop-filter: blur(10px); }
        .form-control, .form-select { background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); color: white; border-radius: 12px; transition: 0.3s; }
        .form-control:focus { border-color: var(--lesbot-cyan); box-shadow: 0 0 15px rgba(0, 212, 255, 0.2); color: white; background: rgba(0,0,0,0.5); }
        .input-label { font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px; margin-bottom: 8px; font-weight: 700; }
        .btn-neural-submit { background: var(--lesbot-cyan); color: var(--obsidian); font-family: 'Orbitron'; font-weight: 900; padding: 18px; border: none; border-radius: 15px; width: 100%; transition: 0.3s; }
        .btn-neural-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 212, 255, 0.5); }
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

<div class="container mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 style="font-family:'Orbitron'; font-weight:900; color:var(--lesbot-cyan);">REPORT <span style="color:#fff;">ISSUE</span></h2>
            <p class="text-white-50 small mt-2">NEURAL LOGGING PROTOCOL • MAINTENANCE v3.0</p>
        </div>
        
        <?php if($success): ?>
            <div class="alert alert-info bg-dark border-info text-info text-center py-3 rounded-3 mb-4">
                <i class="bi bi-cpu-fill me-2"></i> <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger text-center py-3 rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?>
            </div>
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
                        <option value="Urgent">URGENT</option>
                    </select>
                </div>
            </div>
            <div class="mb-5">
                <label class="input-label">NEURAL DESCRIPTION</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Describe the physical malfunction..." required></textarea>
            </div>
            <button type="submit" class="btn-neural-submit">
                <i class="bi bi-broadcast me-2"></i> TRANSMIT REPORT
            </button>
        </form>
    </div>
</div>
</body>
</html>