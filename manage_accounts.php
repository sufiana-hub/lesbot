<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

$admin_id = $_SESSION['std_id'] ?? 'AD001';

// --- LOG EVERY SECOND: ACCESS PULSE ---
try {
    $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, action_details) VALUES (?, 'ACCESS_HUB', 'Admin monitored entity archive hub')")->execute([$admin_id]);
} catch (Exception $e) {}

// --- INTELLIGENT PURGE LOGIC ---
if (isset($_GET['delete_id'])) {
    $target_id = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();

        // 1. Fetch Name AND Role before they are removed from existence
        $stmtCheck = $pdo->prepare("SELECT name, role FROM users WHERE user_id = ?");
        $stmtCheck->execute([$target_id]);
        $userData = $stmtCheck->fetch();
        
        if ($userData) {
            $t_name = $userData['name'];
            $t_role = $userData['role'];

            // 2. Perform the deletion
            $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'Admin'")->execute([$target_id]);

            // 3. LOG THE TRACE: We embed the ROLE so the Audit page can color it
            $details = "CRITICAL PURGE | ROLE: $t_role | NAME: $t_name";
            $stmtAudit = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'ENTITY_PURGE', ?, ?)");
            $stmtAudit->execute([$admin_id, $target_id, $details]);

            $pdo->commit();
            header("Location: manage_accounts.php?msg=purged"); exit();
        }
    } catch (PDOException $e) { $pdo->rollBack(); die($e->getMessage()); }
}

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'user_id';
$order = $_GET['order'] ?? 'ASC';
$next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

$all_users = $pdo->prepare("SELECT user_id, name, email, role FROM users WHERE (user_id LIKE :s OR name LIKE :s) ORDER BY $sort $order");
$all_users->execute(['s' => "%$search%"]);
$users = $all_users->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Entity Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 100px; min-height: 100vh; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; border-radius: 20px; transition: 0.3s; }
        .nav-links-container a.active { color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1); }
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 40px; backdrop-filter: blur(10px); }
        .action-remove { padding: 6px 15px; border-radius: 8px; background: rgba(255, 75, 43, 0.1); border: 1px solid rgba(255, 75, 43, 0.3); color: #ff4b2b; font-family: 'Orbitron'; font-size: 0.55rem; text-decoration: none; transition: 0.3s; font-weight: 900; }
        .action-remove:hover { background: #ff4b2b; color: white; box-shadow: 0 0 15px #ff4b2b; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" class="text-white text-decoration-none" style="font-family: 'Orbitron'; font-weight: 900;">LESBOT <span style="color: var(--lesbot-cyan);">•</span></a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php">OVERVIEW</a></li>
        <li><a href="manage_accounts.php" class="active">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php">MAINTENANCE</a></li>
        <li><a href="admin_audit_trail.php">AUDIT</a></li>
        <li><a href="export_data.php">EXPORTS</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4">
    <div class="system-container">
        <h2 style="font-family: 'Orbitron'; font-weight: 900; margin-bottom: 30px;">SYSTEM ENTITY <span style="color: var(--lesbot-cyan);">ARCHIVE</span></h2>
        
        <table class="table table-dark table-hover">
            <thead>
                <tr class="text-info" style="font-family: 'Orbitron'; font-size: 0.7rem;">
                    <th>ID</th>
                    <th>FULL NAME</th>
                    <th>ROLE</th>
                    <th class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td class="text-info fw-bold"><?= $u['user_id'] ?></td>
                    <td class="text-uppercase"><?= htmlspecialchars($u['name']) ?></td>
                    <td><span class="badge bg-secondary font-orbitron" style="font-size: 0.55rem;"><?= strtoupper($u['role']) ?></span></td>
                    <td class="text-end">
                        <?php if($u['role'] !== 'Admin'): ?>
                            <a href="?delete_id=<?= $u['user_id'] ?>" class="action-remove" onclick="return confirm('Purge entity from neural memory?')">
                                <i class="bi bi-trash-fill"></i> REMOVE
                            </a>
                        <?php else: ?>
                            <span class="text-cyan-bright small font-orbitron">PROTECTED</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-5">
            <a href="admin_audit_trail.php" class="btn btn-outline-info rounded-pill px-4 font-orbitron" style="font-size: 0.65rem;">
                <i class="bi bi-shield-lock-fill me-2"></i> OPEN NEURAL AUDIT TRAIL
            </a>
        </div>
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