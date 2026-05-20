<?php
session_start();
require_once 'db_config.php';
$payment_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT py.*, pt.description FROM student_payments py 
                       JOIN student_penalties sp ON py.penalty_id = sp.penalty_id
                       JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
                       WHERE py.payment_id = ?");
$stmt->execute([$payment_id]);
$r = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Receipt</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0B0E14; color: white; padding: 50px; font-family: 'Rajdhani'; }
        .receipt-box { border: 2px dashed #00d4ff; padding: 40px; border-radius: 20px; max-width: 450px; margin: auto; background: rgba(255,255,255,0.02); }
    </style>
</head>
<body>
    <div class="receipt-box text-center shadow-lg">
        <h3 style="font-family:'Orbitron'; color:#00d4ff;">TRANSACTION SUCCESS</h3>
        <div class="text-start mt-4">
            <p><strong>Payment ID:</strong> <?= $r['payment_id'] ?></p>
            <p><strong>Item:</strong> <?= $r['description'] ?></p>
            <p><strong>Ref:</strong> <?= $r['transaction_ref'] ?></p>
            <hr>
            <h4 class="text-info">TOTAL: RM <?= number_format($r['amount_paid'], 2) ?></h4>
        </div>
        <button onclick="window.print()" class="btn btn-outline-info btn-sm mt-3">SAVE RECEIPT</button>
        <a href="student_dashboard.php" class="btn btn-link text-white-50 d-block mt-3">Back to Hub</a>
    </div>

    <button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>


</body>
</html>