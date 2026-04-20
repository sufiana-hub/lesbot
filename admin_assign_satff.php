<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

$request_id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = $_POST['staff_id'];
    $req_id = $_POST['request_id'];

    try {
        // UPDATE: Assign staff and move status to 'In Progress'
        $stmt = $pdo->prepare("UPDATE maintenance_request SET assigned_staff_id = ?, status = 'In Progress' WHERE request_id = ?");
        $stmt->execute([$staff_id, $req_id]);
        
        header("Location: admin_maintenance.php?success=assigned");
        exit();
    } catch (PDOException $e) {
        $error = "Assignment Error: " . $e->getMessage();
    }
}

// 2. READ: Fetch all available Staff
$staff_list = $pdo->query("SELECT s.staff_id, u.name, s.department FROM staff s JOIN users u ON s.staff_id = u.user_id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Assign Technician</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0B0E14; color: white; font-family: 'Rajdhani'; }
        .glass-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 20px; padding: 30px; }
        .form-control { background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 glass-card shadow-lg">
            <h2 class="text-center mb-4" style="font-family: 'Orbitron'; color: #00d4ff;">DELEGATE TASK</h2>
            <p class="text-center text-white-50 small">Assigning Staff to Request ID: <b><?= htmlspecialchars($request_id) ?></b></p>
            
            <form method="POST">
                <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">
                <div class="mb-4">
                    <label class="form-label">SELECT TECHNICIAN</label>
                    <select name="staff_id" class="form-control" required>
                        <option value="">-- Choose Assigned Personnel --</option>
                        <?php foreach($staff_list as $staff): ?>
                            <option value="<?= $staff['staff_id'] ?>">
                                <?= htmlspecialchars($staff['name']) ?> (<?= $staff['department'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-info w-100 fw-bold py-3">INITIALIZE ASSIGNMENT</button>
            </form>
            <div class="text-center mt-3">
                <a href="admin_maintenance.php" class="text-white-50 text-decoration-none small">Cancel and Return</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>