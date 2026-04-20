<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

try {
    // 2. AGGREGATE LOGIC: Join Staff with Penalties
    // We use SUM() and COUNT() to create the summary
    $query = "SELECT u.name, s.department, 
                     COUNT(p.penalty_id) as total_issued, 
                     SUM(p.amount) as total_revenue
              FROM staff s
              JOIN users u ON s.staff_id = u.user_id
              LEFT JOIN student_penalties p ON s.staff_id = p.issued_by
              GROUP BY s.staff_id
              ORDER BY total_revenue DESC";
              
    $stmt = $pdo->query($query);
    $summaries = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Staff Performance</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0B0E14; color: white; font-family: 'Rajdhani'; }
        .glass-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 15px; padding: 25px; }
        .stat-highlight { color: #00d4ff; font-family: 'Orbitron'; font-weight: 900; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4" style="font-family: 'Orbitron'; color: #00d4ff;">8. STAFF PENALTY SUMMARY</h2>
    
    <div class="glass-card shadow-lg">
        <table class="table table-hover text-white">
            <thead>
                <tr class="text-info small">
                    <th>STAFF NAME</th>
                    <th>DEPARTMENT</th>
                    <th>TOTAL FINES ISSUED</th>
                    <th>TOTAL REVENUE (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($summaries as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><?= $row['total_issued'] ?> Cases</td>
                    <td class="stat-highlight"><?= number_format($row['total_revenue'] ?? 0, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-outline-info rounded-pill px-5">BACK TO CORE</a>
    </div>
</div>
</body>
</html>