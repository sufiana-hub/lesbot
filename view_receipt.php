<?php
session_start();
require_once 'db_config.php';

// ToyyibPay sends status_id and order_id in the URL after bank login
$status_id = $_GET['status_id'] ?? 3;
$penalty_id = $_GET['order_id'] ?? '';

if ($status_id == 1) {
    // FETCH DATA FOR THE RECEIPT
    $stmt = $pdo->prepare("SELECT sp.*, pt.description, u.name 
                           FROM student_penalties sp 
                           JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
                           JOIN users u ON sp.matric_number = u.user_id
                           WHERE sp.penalty_id = ?");
    $stmt->execute([$penalty_id]);
    $data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Receipt</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #080a0f; color: #fff; font-family: 'Rajdhani'; padding: 50px; text-align: center; }
        .receipt-card { background: rgba(255,255,255,0.02); border: 2px dashed #00d4ff; border-radius: 20px; padding: 40px; max-width: 500px; margin: auto; }
        .font-orbitron { font-family: 'Orbitron'; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="receipt-card shadow-lg">
        <h2 class="font-orbitron text-info">TRANSACTION SUCCESS</h2>
        <p class="small text-white-50 mt-2">NEURAL LEDGER UPDATED</p>
        <hr style="border-color: #333;">
        <div class="text-start">
            <p><strong>STUDENT:</strong> <?= $data['name'] ?></p>
            <p><strong>VIOLATION:</strong> <?= $data['description'] ?></p>
            <p><strong>REFERENCE:</strong> <?= $penalty_id ?></p>
            <h4 class="text-info mt-4">TOTAL PAID: RM <?= number_format($data['amount'], 2) ?></h4>
        </div>
        <button onclick="window.print()" class="btn btn-outline-info btn-sm mt-4">GENERATE PDF RECEIPT</button>
        <a href="student_dashboard.php" class="btn btn-link text-white-50 d-block mt-3">Return to Hub</a>
    </div>
</body>
</html>
<?php 
} else {
    echo "<h1>TRANSACTION ABORTED</h1><p>The bank authorization was unsuccessful. <a href='student_penalties.php'>Try again</a></p>";
}
?>