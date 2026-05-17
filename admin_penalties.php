<?php
/**
 * LESBOT NEURAL PENALTY INTERFACE
 * DBA PROTOCOL: AUTHORIZATION & FINANCIAL SYNCHRONIZATION
 */
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

$success = "";
$error = "";

// 2. PROCESS PENALTY ISSUANCE (Atomic Transaction)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric = $_POST['matric'];
    $type_id = $_POST['type_id'];
    $amount = $_POST['amount'];
    
    try {
        // DBA Check: Ensure amount is within 10-50 range on server-side too
        if ($amount < 10 || $amount > 50) {
            throw new Exception("FINANCIAL BREACH: Amount must be between RM 10 and RM 50.");
        }

        $stmt = $pdo->prepare("INSERT INTO student_penalties (matric_number, penalty_type_id, amount, date_issued, is_paid) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$matric, $type_id, $amount]);
        $success = "SYSTEM UPDATE: Penalty synchronized with Student Ledger #$matric.";
    } catch (Exception $e) {
        $error = "TRANSMISSION ERROR: " . $e->getMessage();
    }
}

// 3. DATA ACQUISITION: Pull students and rules from the archive
$types = $pdo->query("SELECT * FROM penalty_types ORDER BY description ASC")->fetchAll();
$students = $pdo->query("SELECT s.matric_number, u.name, s.room_number 
                         FROM student s 
                         JOIN users u ON s.matric_number = u.user_id 
                         ORDER BY s.matric_number ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Penalty Authorization</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --lesbot-red: #ff4d4d; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 77, 77, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(255, 77, 77, 0.05) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 100px; min-height: 100vh; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; backdrop-filter: blur(10px); max-width: 800px; margin: 0 auto; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .form-control, .form-select { background: rgba(0,0,0,0.4); border: 1px solid rgba(255, 77, 77, 0.3); color: white; border-radius: 12px; padding: 12px 20px; font-family: 'Rajdhani'; transition: 0.3s; }
        .form-control:focus, .form-select:focus { background: rgba(0,0,0,0.6); border-color: var(--lesbot-red); color: white; box-shadow: 0 0 15px rgba(255, 77, 77, 0.2); }
        .input-label { font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 1.5px; margin-bottom: 8px; font-weight: 700; }
        .btn-neural-authorize { background: var(--lesbot-red); color: white; font-family: 'Orbitron'; font-weight: 900; font-size: 0.85rem; letter-spacing: 2px; padding: 18px; border: none; border-radius: 15px; transition: 0.3s; width: 100%; box-shadow: 0 5px 15px rgba(255, 77, 77, 0.3); }
        .btn-neural-authorize:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255, 77, 77, 0.5); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="font-family:'Orbitron'; color:var(--lesbot-cyan); text-decoration:none; font-weight:900;">LESBOT •</a>
    <div style="display:flex; gap:20px;">
        <a href="admin_dashboard.php" style="color:rgba(255,255,255,0.7); text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">OVERVIEW</a>
        <a href="manage_accounts.php" style="color:rgba(255,255,255,0.7); text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">ACCOUNTS</a>
        <a href="admin_penalties.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">PENALTIES</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="system-container">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Orbitron'; font-weight: 900; margin: 0; color: var(--lesbot-red);">ISSUE <span style="color: #fff;">PENALTY</span></h2>
            <p class="text-white-50 small mt-2" style="letter-spacing: 2px;">DBA AUTHORIZATION REQUIRED • PRICE RANGE RM10-50</p>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success bg-dark border-info text-info text-center py-3 mb-4"><i class="bi bi-shield-check me-2"></i> <?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger text-center py-3 mb-4"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="input-label">TARGET STUDENT IDENTITY</label>
                <select name="matric" class="form-select" required>
                    <option value="" disabled selected>Select Matric Entity...</option>
                    <?php foreach($students as $s): ?>
                        <option value="<?= $s['matric_number'] ?>"><?= $s['matric_number'] ?> - <?= htmlspecialchars($s['name']) ?> (Room <?= $s['room_number'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="input-label">VIOLATION CLASSIFICATION</label>
                <!-- AUTO-SYNC SELECTOR -->
                <select name="type_id" id="penalty_selector" class="form-select" required onchange="syncAmount()">
                    <option value="" disabled selected>Select Violation Type...</option>
                    <?php foreach($types as $t): ?>
                        <option value="<?= $t['penalty_type_id'] ?>" data-price="<?= $t['default_amount'] ?>">
                            <?= strtoupper($t['description']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-5">
                <label class="input-label">FINES AMOUNT (RM)</label>
                <input type="number" name="amount" id="amount_field" class="form-control" step="0.01" min="10" max="50" placeholder="0.00" required>
                <div class="form-text text-white-50 small mt-2">Standardized amount automatically synchronized with dormitory rules.</div>
            </div>

            <button type="submit" class="btn-neural-authorize">
                <i class="bi bi-shield-exclamation me-2"></i> INITIALIZE PENALTY
            </button>
        </form>
    </div>
</div>

<script>
// DBA Auto-Sync Function
function syncAmount() {
    var selector = document.getElementById('penalty_selector');
    var amountField = document.getElementById('amount_field');
    var selectedPrice = selector.options[selector.selectedIndex].getAttribute('data-price');
    amountField.value = selectedPrice;
}
</script>

<?php include 'chatbot_component.php'; ?>

</body>
</html>