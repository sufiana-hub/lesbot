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