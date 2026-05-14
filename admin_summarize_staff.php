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