<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['std_id'])) { header("Location: login.php"); exit(); }
$penalty_id = $_GET['id'];
$id = $_SESSION['std_id'];
$stmt = $pdo->prepare("SELECT sp.*, pt.description FROM student_penalties sp 
                       JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                       WHERE sp.penalty_id = ? AND sp.matric_number = ?");
$stmt->execute([$penalty_id, $id]);
$p = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LesBot | Payment Hub</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #080a0f; color: #fff; font-family: 'Rajdhani'; padding-top: 50px; }
        .payment-card { background: rgba(255, 255, 255, 0.03); border: 1px solid #00d4ff; border-radius: 30px; padding: 40px; max-width: 500px; margin: auto; }
        .btn-gate { background: rgba(255,255,255,0.05); border: 1px solid #444; color: white; padding: 15px; border-radius: 12px; width: 100%; text-align: left; margin-bottom: 12px; transition: 0.3s; }
        .btn-gate:hover { border-color: #00d4ff; background: rgba(0, 212, 255, 0.1); color: #00d4ff; }
    </style>
</head>
<body>
<div class="container text-center">
    <div class="payment-card">
        <h2 class="mb-4" style="font-family:'Orbitron';">PAYMENT HUB</h2>
        <div class="alert bg-dark text-info border-info mb-4">
            <small class="d-block mb-1">SETTLEMENT FOR:</small>
            <strong><?= strtoupper($p['description']) ?></strong>
            <h3 class="mt-2 text-danger">RM <?= number_format($p['amount'], 2) ?></h3>
        </div>
        <form action="process_payment.php" method="POST">
            <input type="hidden" name="penalty_id" value="<?= $penalty_id ?>">
            <input type="hidden" name="amount" value="<?= $p['amount'] ?>">
            <button type="submit" name="method" value="FPX Online Banking" class="btn-gate">🏦 FPX Online Banking</button>
            <button type="submit" name="method" value="Touch n Go" class="btn-gate">📱 Touch 'n Go E-Wallet</button>
        </form>
    </div>
</div>

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>


</body>
</html>