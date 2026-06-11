<?php
/**
 * LESBOT STAFF FINANCIAL AUDIT
 * VISION: RECEIPT CONFIRMATION PROTOCOL
 */
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { 
    header("Location: login.php"); 
    exit(); 
}

$staff_id = $_SESSION['std_id']; 

try {
    // 2. FETCH STAFF PROFILE
    $s_stmt = $pdo->prepare("SELECT u.name, st.department 
                             FROM users u 
                             JOIN staff st ON u.user_id = st.staff_id 
                             WHERE u.user_id = ?");
    $s_stmt->execute([$staff_id]);
    $staff = $s_stmt->fetch();

    // 3. READ: Fetch penalties issued by this specific staff member
    // We join with 'users' to get the student's name and 'penalty_types' for the description
    $query = "SELECT sp.*, u.name as student_name, pt.description as violation_type
              FROM student_penalties sp
              JOIN users u ON sp.matric_number = u.user_id
              JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
              WHERE sp.issued_by = ? 
              ORDER BY sp.is_paid ASC, sp.date_issued DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$staff_id]);
    $assigned_penalties = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Tactical Financials</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #080a0f; 
            --neon-border: rgba(0, 212, 255, 0.3);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%);
            color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 120px; min-height: 100vh;
        }

        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(25px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-variant { color: rgba(255,255,255,0.5); text-decoration: none; font-family: 'Orbitron'; font-size: 0.65rem; letter-spacing: 2px; padding: 10px 15px; border-radius: 12px; transition: 0.3s; }
        .nav-variant.active { background: var(--lesbot-cyan); color: var(--obsidian); font-weight: 900; }

        .system-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 35px; padding: 50px; backdrop-filter: blur(15px); }

        /* Profile Layer */
        .profile-layer-outer { background: rgba(0, 0, 0, 0.4); border: 1px solid var(--neon-border); border-radius: 20px; padding: 25px; margin-bottom: 40px; }
        .label-neon { font-family: 'Orbitron'; font-size: 0.6rem; color: var(--lesbot-cyan); letter-spacing: 2px; font-weight: 900; }
        .value-white { font-family: 'Orbitron'; font-size: 0.85rem; color: #FFF; text-transform: uppercase; }

        /* Ledger Cards */
        .ledger-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 20px; margin-bottom: 15px; transition: 0.3s; }
        .ledger-card:hover { border-color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.05); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="#" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none; letter-spacing:2px;">LESBOT STAFF</a>
    <div class="d-flex gap-2">
        <a href="staff_dashboard.php" class="nav-variant">DASHBOARD</a>
        <a href="staff_tasks.php" class="nav-variant">FIELD OPS</a>
        <a href="staff_penalties.php" class="nav-variant active">FINANCIALS</a>
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container shadow-lg">
        
        <div class="profile-layer-outer">
            <h6 class="mb-3 opacity-50 font-orbitron" style="font-size: 0.5rem;">AUTHENTICATED TECHNICIAN</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="label-neon mb-0">NAME</p>
                    <p class="value-white"><?= htmlspecialchars($staff['name']) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="label-neon mb-0">UNIT</p>
                    <p class="value-white text-info"><?= htmlspecialchars($staff['department']) ?></p>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <h1 style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 5px; margin: 0;">FINANCIAL <span style="color: #ff4d4d;">AUDIT</span></h1>
            <p class="small text-white-50 mt-2">MANAGE ISSUED PENALTIES & SETTLEMENTS</p>
        </div>

        <?php if (empty($assigned_penalties)): ?>
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-shield-slash fs-1 text-danger"></i>
                <p class="mt-3 font-orbitron">NO PENALTIES ISSUED BY YOUR ENTITY</p>
            </div>
        <?php else: ?>
            <?php foreach($assigned_penalties as $p): ?>
                <div class="ledger-card shadow">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <p class="label-neon mb-1">RECORD ID</p>
                            <p class="value-white text-info">#<?= $p['penalty_id'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="label-neon mb-1">STUDENT ENTITY</p>
                            <p class="value-white" style="font-size: 0.75rem;"><?= htmlspecialchars($p['student_name']) ?></p>
                            <small class="text-white-50"><?= $p['matric_number'] ?></small>
                        </div>
                        <div class="col-md-3">
                            <p class="label-neon mb-1">AMOUNT</p>
                            <p class="value-white fs-5">RM <?= number_format($p['amount'], 2) ?></p>
                        </div>
                        <div class="col-md-3 text-end">
                            <?php if($p['is_paid'] == 0): ?>
                                <span class="badge bg-warning text-dark font-orbitron">OUTSTANDING</span>
                            <?php else: ?>
                                <span class="badge bg-success font-orbitron"><i class="bi bi-check-circle me-1"></i> SETTLED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'chatbot_component.php'; ?>

</body>
</html>