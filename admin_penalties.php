<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

$success = "";
$error = "";

// PROCESS PENALTY
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric = $_POST['matric'];
    $type_id = $_POST['type_id'];
    $amount = $_POST['amount'];
    $remarks = trim($_POST['remarks'] ?? ''); // Get the custom reason
    
    try {
        // DBA VALIDATION: If "Others" is selected, remarks must not be empty
        if ($type_id == 11 && empty($remarks)) {
            throw new Exception("NEURAL ERROR: Please specify the nature of the 'Other' violation.");
        }

        $stmt = $pdo->prepare("INSERT INTO student_penalties (matric_number, penalty_type_id, amount, date_issued, is_paid, remarks) VALUES (?, ?, ?, NOW(), 0, ?)");
        $stmt->execute([$matric, $type_id, $amount, $remarks]);
        $success = "LEDGER UPDATED: Custom penalty archived for Student #$matric.";
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// DATA FETCH
$types = $pdo->query("SELECT * FROM penalty_types ORDER BY penalty_type_id ASC")->fetchAll();
$students = $pdo->query("SELECT s.matric_number, u.name FROM student s JOIN users u ON s.matric_number = u.user_id ORDER BY u.name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Penalty Authorization</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --lesbot-red: #ff4d4d; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 77, 77, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(255, 77, 77, 0.05) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 100px; min-height: 100vh; }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; max-width: 800px; margin: 0 auto; backdrop-filter: blur(10px); }
        .form-control, .form-select { background: rgba(0,0,0,0.4); border: 1px solid rgba(255, 77, 77, 0.3); color: white; border-radius: 12px; padding: 12px; margin-bottom: 20px; }
        .form-control:focus { background: rgba(0,0,0,0.6); border-color: var(--lesbot-red); color: white; box-shadow: 0 0 15px rgba(255, 77, 77, 0.2); }
        .input-label { font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px; margin-bottom: 8px; font-weight: 700; display: block; }
        .btn-neural { background: var(--lesbot-red); color: white; font-family: 'Orbitron'; font-weight: 900; padding: 18px; border: none; border-radius: 15px; width: 100%; transition: 0.3s; }
        .btn-neural:hover { transform: translateY(-3px); box-shadow: 0 0 25px var(--lesbot-red); }
        
        /* Hide remarks box by default */
        #remarks_container { display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="system-container shadow-lg">
        <h2 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900;">ISSUE <span style="color:var(--lesbot-red);">PENALTY</span></h2>

        <?php if($success): ?> <div class="alert alert-success bg-dark text-info border-info small"><?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger bg-dark text-danger border-danger small"><?= $error ?></div> <?php endif; ?>

        <form method="POST">
            <label class="input-label">TARGET STUDENT</label>
            <select name="matric" class="form-select" required>
                <option value="" disabled selected>Select Matric Entity...</option>
                <?php foreach($students as $s): ?>
                    <option value="<?= $s['matric_number'] ?>"><?= $s['matric_number'] ?> - <?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label class="input-label">VIOLATION CLASSIFICATION</label>
            <select name="type_id" id="type_id" class="form-select" required onchange="handlePenaltyChange()">
                <option value="" disabled selected>Select Violation Type...</option>
                <?php foreach($types as $t): ?>
                    <option value="<?= $t['penalty_type_id'] ?>" data-price="<?= $t['default_amount'] ?>">
                        <?= $t['description'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- THIS BOX APPEARS ONLY FOR 'OTHERS' -->
            <div id="remarks_container">
                <label class="input-label">SPECIFY VIOLATION NATURE</label>
                <textarea name="remarks" id="remarks_box" class="form-control" rows="3" placeholder="Explain the specific misconduct..."></textarea>
            </div>

            <label class="input-label">FINES AMOUNT (RM)</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="10" max="50" required>

            <button type="submit" class="btn-neural">INITIALIZE PENALTY RECORD</button>
        </form>
    </div>
</div>

<script>
function handlePenaltyChange() {
    var select = document.getElementById('type_id');
    var amountField = document.getElementById('amount');
    var remarksContainer = document.getElementById('remarks_container');
    var remarksBox = document.getElementById('remarks_box');
    
    // Auto-fill amount
    var price = select.options[select.selectedIndex].getAttribute('data-price');
    amountField.value = price;

    // Show/Hide specific reason box (ID 11 is 'OTHERS')
    if (select.value == "11") {
        remarksContainer.style.display = "block";
        remarksBox.required = true;
    } else {
        remarksContainer.style.display = "none";
        remarksBox.required = false;
    }
}
</script>

<?php include 'chatbot_component.php'; ?>

</body>
</html>