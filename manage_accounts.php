<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

$admin_id = $_SESSION['std_id'] ?? 'AD001';

// --- LOG ACCESS PULSE ---
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
            $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'ENTITY_PURGE', ?, ?)")
                ->execute([$admin_id, $target_id, $details]);

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
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Entity Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --lesbot-red: #ff4d4d;
            --lesbot-amber: #ff9d00;
            --obsidian: #080a0f; 
            --neon-border: rgba(0, 212, 255, 0.2); 
        }
        
        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%); 
            color: #FFFFFF; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 130px; min-height: 100vh; 
        }

        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 12px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .system-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 35px; padding: 50px; backdrop-filter: blur(15px); }

        /* --- THE REFINED ROLE BADGES --- */
        .role-badge {
            font-family: 'Orbitron';
            font-size: 0.70rem; /* Scaled down for elegance */
            font-weight: 900;
            padding: 6px 16px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: inline-block;
            border: 1px solid transparent;
        }

        .badge-admin { background: rgba(255, 77, 77, 0.1); color: var(--lesbot-red); border-color: var(--lesbot-red); box-shadow: 0 0 10px rgba(255, 77, 77, 0.2); }
        .badge-staff { background: rgba(255, 157, 0, 0.1); color: var(--lesbot-amber); border-color: var(--lesbot-amber); }
        .badge-student { background: rgba(0, 212, 255, 0.1); color: var(--lesbot-cyan); border-color: var(--lesbot-cyan); }

        .id-text { font-family: 'Orbitron'; color: var(--lesbot-cyan); font-weight: 700; font-size: 0.8rem; }
        .name-text { font-weight: 700; font-size: 1rem; letter-spacing: 0.5px; }

        .action-remove { padding: 6px 15px; border-radius: 8px; background: rgba(255, 75, 43, 0.05); border: 1px solid #ff4b2b; color: #ff4b2b; font-family: 'Orbitron'; font-size: 0.55rem; text-decoration: none; transition: 0.3s; font-weight: 900; }
        .action-remove:hover { background: #ff4b2b; color: white; box-shadow: 0 0 15px #ff4b2b; }
        
        .protected-tag { font-family: 'Orbitron'; font-size: 0.6rem; color: rgba(255,255,255,0.2); letter-spacing: 2px; font-weight: 700; }

        /* CLEAN TABLE STRIPING */
        .table tbody tr:hover { background: rgba(255, 255, 255, 0.02); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="font-family:'Orbitron'; color:var(--lesbot-cyan); text-decoration:none; font-weight:900; letter-spacing:2px;">LESBOT •</a>
    <div style="display:flex; gap:30px;">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.7;">DASHBOARD</a>
        <a href="manage_accounts.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">ACCOUNTS</a>
    </div>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold font-orbitron" style="font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mb-5">
    <div class="system-container shadow-lg">
        <h2 class="mb-5" style="font-family: 'Orbitron'; font-weight: 900; letter-spacing: 5px;">SYSTEM ENTITY <span style="color: var(--lesbot-cyan);">ARCHIVE</span></h2>
        
        <div class="table-responsive">
            <table class="table table-dark align-middle" style="--bs-table-bg: transparent;">
                <thead>
                    <tr class="text-info" style="font-family: 'Orbitron'; font-size: 0.75rem; border-bottom: 1px solid var(--neon-border);">
                        <th class="pb-3">IDENTIFIER</th>
                        <th class="pb-3">LEGAL NAME</th>
                        <th class="pb-3">NEURAL ROLE</th>
                        <th class="pb-3 text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3"><span class="id-text"><?= $u['user_id'] ?></span></td>
                        <td class="py-3"><span class="name-text"><?= htmlspecialchars($u['name']) ?></span></td>
                        <td class="py-3">
                            <?php 
                                $r = $u['role'];
                                $badgeClass = 'badge-student';
                                if($r == 'Admin') $badgeClass = 'badge-admin';
                                if($r == 'Staff') $badgeClass = 'badge-staff';
                            ?>
                            <span class="role-badge <?= $badgeClass ?>"><?= $r ?></span>
                        </td>
                        <td class="text-end py-3">
                            <?php if($u['role'] !== 'Admin'): ?>
                                <a href="?delete_id=<?= $u['user_id'] ?>" class="action-remove" onclick="return confirm('Purge entity from neural memory?')">
                                    <i class="bi bi-trash3-fill me-1"></i> REMOVE
                                </a>
                            <?php else: ?>
                                <span class="protected-tag">CORE_PROTECTED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 text-center">
            <a href="admin_audit_trail.php" class="btn btn-outline-info rounded-pill px-5 py-3 font-orbitron" style="font-size: 0.7rem; letter-spacing: 2px;">
                <i class="bi bi-shield-lock-fill me-2"></i> OPEN NEURAL AUDIT TRAIL
            </a>
        </div>
    </div>
</div>

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>


</body>
</html>