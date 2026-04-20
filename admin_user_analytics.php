<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Only Admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); 
    exit(); 
}

try {
    // 2. ANALYTICS LOGIC: Aggregate User Roles
    // Copilot Suggestion: Fetch counts and calculate total in one go
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles = $stmt->fetchAll();
    
    $total_users = array_sum(array_column($roles, 'count'));

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | User Analytics</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #0B0E14; }
        body { background-color: var(--obsidian); color: white; font-family: 'Rajdhani', sans-serif; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(0, 212, 255, 0.2); 
            border-radius: 20px; 
            padding: 40px; 
        }
        .progress { height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; overflow: visible; }
        .progress-bar { background: var(--lesbot-cyan); box-shadow: 0 0 10px var(--lesbot-cyan); }
        .role-label { font-family: 'Orbitron'; letter-spacing: 2px; color: var(--lesbot-cyan); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 style="font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan);">4. NEURAL POPULATION ANALYTICS</h2>
        <p class="text-white-50 small">TOTAL MANAGED ENTITIES: <?php echo $total_users; ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 glass-card shadow-lg">
            <?php foreach($roles as $role): 
                $percentage = ($role['count'] / $total_users) * 100;
            ?>
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="role-label"><?php echo strtoupper($role['role']); ?></span>
                        <span class="fw-bold"><?php echo $role['count']; ?> UNITS (<?php echo round($percentage, 1); ?>%)</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="mt-4 pt-4 border-top border-secondary text-center">
                <p class="small text-muted">Analytics generated via Neural Link at <?php echo date('H:i:s'); ?></p>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="admin_dashboard.php" class="btn btn-outline-info rounded-pill px-5 fw-bold">
            RETURN TO COMMAND CORE
        </a>
    </div>
</div>

</body>
</html>