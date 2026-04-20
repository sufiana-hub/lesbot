<?php
session_start();
require_once 'db_config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['std_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories from your 'category' table for the dropdown
$cat_stmt = $pdo->query("SELECT * FROM category");
$categories = $cat_stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['std_id'];
    $category_id = $_POST['category_id'];
    $priority = $_POST['priority'];
    $description = trim($_POST['description']);
    
    // Generate a unique Request ID (Vision from your terminal prototype)
    $request_id = "REQ-" . date("YmdHis"); 

    try {
        // Saving to your 'maintenance_request' table
        $sql = "INSERT INTO maintenance_request (request_id, student_id, category_id, description, priority, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$request_id, $student_id, $category_id, $description, $priority])) {
            $success = "Request $request_id submitted successfully!";
        }
    } catch (PDOException $e) {
        $error = "Neural Link Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Report Issue</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { --baby-blue: #A7C7E7; --deep-obsidian: #0B0E14; }
        body { background-color: var(--deep-obsidian); color: white; font-family: 'Rajdhani'; }
        .form-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(167, 199, 231, 0.2); border-radius: 20px; padding: 30px; }
        .form-control, .form-select { background: rgba(0,0,0,0.3); border: 1px solid rgba(167,199,231,0.2); color: white; }
        .form-control:focus { background: rgba(0,0,0,0.5); color: white; border-color: var(--baby-blue); }
        .btn-submit { background: var(--baby-blue); color: var(--deep-obsidian); font-family: 'Orbitron'; font-weight: 700; border: none; }
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

    <div class="container py-5"></div>


    <div class="container py-5">
        <h2 class="text-center mb-4" style="font-family: 'Orbitron'; letter-spacing: 2px;">REPORT AN ISSUE</h2>
        
        <div class="row justify-content-center">
            <div class="col-md-8 form-card shadow-lg">
                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                
                <n method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-info small">CATEGORY</label>
                            <select name="category" class="form-select" required>
                                <option value="Water Being Cut">Water Being Cut (Critical)</option>
                                <option value="Water Drip">Water Drip (Urgent)</option>
                                <option value="Electrical Malfunction">Electrical Malfunction (High)</option>
                                <option value="Broken Furniture">Broken Furniture (Medium)</option>
                                <option value="WiFi Issues">WiFi Issues (Low)</option>
                                <option value="Other">Other (Custom)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info small">PRIORITY LEVEL</label>
                            <select name="priority" class="form-select" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-info small">DESCRIPTION</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue in detail..." required></textarea>
                    </div>
                    
<div class="d-grid gap-3">
    <button type="submit" class="btn btn-submit py-3">SUBMIT NEURAL REPORT</button>
    <a href="student_dashboard.php" class="btn btn-submit py-3">Back to Command Center</a>
</div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>