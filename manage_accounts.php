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

        $stmtCheck = $pdo->prepare("SELECT name, role FROM users WHERE user_id = ?");
        $stmtCheck->execute([$target_id]);
        $userData = $stmtCheck->fetch();
        
        if ($userData) {
            $t_name = $userData['name'];
            $t_role = $userData['role'];

            $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'Admin'")->execute([$target_id]);

            $details = "CRITICAL PURGE | ROLE: $t_role | NAME: $t_name";
            $stmtAudit = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'ENTITY_PURGE', ?, ?)");
            $stmtAudit->execute([$admin_id, $target_id, $details]);

            $pdo->commit();
            header("Location: manage_accounts.php?msg=purged"); exit();
        }
    } catch (PDOException $e) { $pdo->rollBack(); die($e->getMessage()); }
}

$search = $_GET['search'] ?? '';
$all_users = $pdo->prepare("SELECT user_id, name, email, role FROM users WHERE (user_id LIKE :s OR name LIKE :s) ORDER BY role ASC, user_id ASC");
$all_users->execute(['s' => "%$search%"]);
$users = $all_users->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Entity Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 120px; min-height: 100vh; }
        
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 50px; padding: 10px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; transition: 0.3s; }
        .nav-links-container a.active { color: var(--lesbot-cyan); text-shadow: 0 0 10px var(--lesbot-cyan); }
        
        .system-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 35px; padding: 50px; backdrop-filter: blur(15px); }

        /* --- ENHANCED ROLE BADGES --- */
        .role-badge {
            font-family: 'Orbitron';
            font-size: 0.75rem; /* Enormous compared to before */
            font-weight: 900;
            padding: 8px 18px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            border: 1px solid transparent;
        }
        
        /* High-Contrast Role colors */
        .badge-admin { background: rgba(255, 255, 255, 0.1); color: #fff; border-color: rgba(255,255,255,0.4); }
        .badge-staff { background: rgba(0, 212, 255, 0.1); color: var(--lesbot-cyan); border-color: var(--lesbot-cyan); box-shadow: 0 0 10px rgba(0, 212, 255, 0.2); }
        .badge-student { background: rgba(167, 199, 231, 0.1); color: #A7C7E7; border-color: #A7C7E7; }

        .id-text { font-family: 'Orbitron'; color: var(--lesbot-cyan); font-weight: 700; font-size: 0.85rem; }
        .name-text { font-weight: 700; font-size: 1.1rem; letter-spacing: 0.5px; }

        .action-remove { padding: 8px 20px; border-radius: 10px; background: rgba(255, 75, 43, 0.1); border: 1px solid #ff4b2b; color: #ff4b2b; font-family: 'Orbitron'; font-size: 0.65rem; text-decoration: none; transition: 0.3s; font-weight: 900; }
        .action-remove:hover { background: #ff4b2b; color: white; box-shadow: 0 0 20px #ff4b2b; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="font-family:'Orbitron'; color:var(--lesbot-cyan); text-decoration:none; font-weight:900; letter-spacing:2px;">LESBOT •</a>
    <ul class="nav-links-container">
        <li><a href="admin_dashboard.php">OVERVIEW</a></li>
        <li><a href="manage_accounts.php" class="active">ACCOUNTS</a></li>
        <li><a href="admin_maintenance.php">MAINTENANCE</a></li>
        <li><a href="admin_audit_trail.php">AUDIT</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mb-5">
    <div class="system-container shadow-lg">
        <h2 class="mb-5" style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 5px;">SYSTEM ENTITY <span style="color: var(--lesbot-cyan);">ARCHIVE</span></h2>
        
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr class="text-info" style="font-family: 'Orbitron'; font-size: 0.8rem; border-bottom: 2px solid var(--glass-border);">
                        <th class="pb-3">IDENTIFIER</th>
                        <th class="pb-3">LEGAL NAME</th>
                        <th class="pb-3">NEURAL ROLE</th>
                        <th class="pb-3 text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-4"><span class="id-text"><?= $u['user_id'] ?></span></td>
                        <td class="py-4"><span class="name-text"><?= htmlspecialchars($u['name']) ?></span></td>
                        <td class="py-4">
                            <?php 
                                $r = $u['role'];
                                $badgeClass = ($r == 'Admin') ? 'badge-admin' : (($r == 'Staff') ? 'badge-staff' : 'badge-student');
                            ?>
                            <span class="role-badge <?= $badgeClass ?>"><?= strtoupper($r) ?></span>
                        </td>
                        <td class="text-end py-4">
                            <?php if($u['role'] !== 'Admin'): ?>
                                <a href="?delete_id=<?= $u['user_id'] ?>" class="action-remove" onclick="return confirm('Purge entity from neural memory?')">
                                    <i class="bi bi-trash3-fill me-2"></i> REMOVE
                                </a>
                            <?php else: ?>
                                <span class="text-white-50 small font-orbitron opacity-50" style="letter-spacing: 2px;">CORE_PROTECTED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 text-center">
            <a href="admin_audit_trail.php" class="btn btn-outline-info rounded-pill px-5 py-3 font-orbitron" style="font-size: 0.75rem; letter-spacing: 2px;">
                <i class="bi bi-shield-lock-fill me-2"></i> OPEN NEURAL AUDIT TRAIL
            </a>
        </div>
    </div>
</div>

<?php include 'chatbot_component.php'; ?>

</body>
</html>