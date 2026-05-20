<?php
session_start();
require_once 'db_config.php';

// 1. DATA RECOVERY: ToyyibPay sends status_id and order_id (our penalty_id)
$status_id = $_GET['status_id'] ?? 3; // 1=Success, 3=Fail
$penalty_id = $_GET['order_id'] ?? '';

if ($status_id == 1) {
    // 2. FETCH DETAILS FOR RECEIPT
    $stmt = $pdo->prepare("SELECT sp.*, pt.description 
                           FROM student_penalties sp 
                           JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                           WHERE sp.penalty_id = ?");
    $stmt->execute([$penalty_id]);
    $p = $stmt->fetch();
?>
    <!-- Sleek Futuristic Receipt Design -->
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>LesBot | Neural Receipt</title>
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #080a0f; color: #fff; font-family: 'Rajdhani'; text-align: center; padding-top: 50px; }
            .receipt-card { background: rgba(255,255,255,0.02); border: 2px dashed #00d4ff; border-radius: 20px; padding: 40px; max-width: 500px; margin: auto; }
            .text-cyan { color: #00d4ff; }
        </style>
    </head>
    <body>
        <div class="receipt-card shadow-lg">
            <h2 class="text-cyan" style="font-family:'Orbitron';">TRANSACTION SUCCESS</h2>
            <hr style="border-color: #333;">
            <div class="text-start">
                <p><strong>REFERENCE:</strong> <?= $penalty_id ?></p>
                <p><strong>VIOLATION:</strong> <?= $p['description'] ?></p>
                <p><strong>STATUS:</strong> <span class="badge bg-success">SETTLED</span></p>
                <h3 class="text-cyan mt-4">TOTAL: RM <?= number_format($p['amount'], 2) ?></h3>
            </div>
            <button onclick="window.print()" class="btn btn-outline-info btn-sm mt-4">PRINT TO PDF</button>
            <a href="student_dashboard.php" class="btn btn-link text-white-50 d-block mt-3">Return to Hub</a>
        </div>
    </body>
    </html>
<?php 
} else {
    // If status_id is not 1, show failure
    echo "<h1>TRANSACTION ABORTED</h1><p>Bank authorization failed. <a href='student_penalties.php'>Try Again</a></p>";
}
?>