<?php
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL: Ensures only logged-in students can view this data
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    // 2. READ: Fetch Maintenance Requests with Joins to get Category and Staff names
    // Included 'mr.description' and 'mr.rejected_count' for transparency
    $sql = "SELECT 
                mr.request_id, 
                c.category_name, 
                mr.description, 
                mr.status, 
                mr.priority, 
                mr.created_at,
                mr.rejected_count,
                u.name AS staff_name,
                st.phone_num AS staff_phone
            FROM maintenance_request mr
            JOIN category c ON mr.category_id = c.category_id
            LEFT JOIN staff st ON mr.assigned_staff_id = st.staff_id
            LEFT JOIN users u ON st.staff_id = u.user_id
            WHERE mr.student_id = :id
            ORDER BY mr.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $requests = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Track Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #080a0f; 
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(0, 212, 255, 0.2);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.07) 0%, transparent 80%);
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0;
            padding-top: 120px;
            min-height: 100vh;
        }

        /* --- Floating Navigation --- */
        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.8);
            backdrop-filter: blur(15px); border: 1px solid var(--glass-border);
            border-radius: 50px; padding: 10px 30px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .nav-brand { font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan); text-decoration: none; }
        .nav-links-container { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { 
            color: rgba(255, 255, 255, 0.7); text-decoration: none; font-family: 'Orbitron'; 
            font-size: 0.7rem; letter-spacing: 1px; padding: 8px 15px; border-radius: 20px; transition: 0.3s;
        }
        .nav-links-container a:hover, .nav-links-container a.active { color: var(--lesbot-cyan); background: rgba(0, 212, 255, 0.1); }

        .system-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);
        }

        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 3px; color: var(--lesbot-cyan); }

        /* --- Table Styling Fixed for Descriptions --- */
        .custom-table { color: #e0e0e0; border-collapse: separate; border-spacing: 0 10px; table-layout: fixed; width: 100%; }
        .custom-table thead th { 
            border: none; font-family: 'Orbitron'; font-size: 0.7rem; 
            color: var(--lesbot-cyan); text-transform: uppercase; padding-bottom: 15px;
        }
        .custom-table tbody tr { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); }
        
        /* THE DESCRIPTION FIX: Ensures text wraps and shows up */
        .desc-col { 
            font-size: 0.85rem; 
            color: rgba(255,255,255,0.7); 
            word-wrap: break-word; 
            overflow-wrap: break-word; 
            white-space: normal !important; 
            line-height: 1.4;
        }

        .badge-status { 
            font-family: 'Orbitron'; font-size: 0.6rem; letter-spacing: 1px;
            padding: 0.5rem 1rem; border-radius: 50px; font-weight: 700;
        }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" class="nav-brand">LESBOT<span style="color:#fff">•</span></a>
    <ul class="nav-links-container">
        <li><a href="student_dashboard.php">UTAMA</a></li>
        <li><a href="maintenance_report.php">REPORT</a></li>
        <li><a href="student_penalties.php">PENALTIES</a></li>
        <li><a href="student_history.php">HISTORY</a></li>
    </ul>
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mt-4 mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 class="header-title">TRACK <span class="text-white">REQUESTS</span></h2>
            <p class="text-white-50 small" style="letter-spacing: 2px;">NEURAL LINK ACTIVE • MONITORING SYSTEM STATUS</p>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">ID</th>
                        <th style="width: 15%;">CATEGORY</th>
                        <th style="width: 35%;">DESCRIPTION</th>
                        <th style="width: 15%;">STATUS</th>
                        <th style="width: 20%;">ASSIGNED TO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-info">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No active maintenance requests found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td class="small fw-bold text-info">#<?= htmlspecialchars($r['request_id']) ?></td>
                            <td>
                                <div class="fw-bold text-white small"><?= htmlspecialchars($r['category_name']) ?></div>
                                <div class="text-warning" style="font-size: 0.55rem; font-family: 'Orbitron';"><?= strtoupper($r['priority']) ?></div>
                            </td>
                            <!-- DESCRIPTION FIX APPLIED HERE -->
                            <td class="desc-col">
                                <?= nl2br(htmlspecialchars($r['description'])) ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $r['status'];
                                    $bg = 'bg-secondary';
                                    if($status == 'Pending') $bg = 'bg-warning text-dark';
                                    if($status == 'In Progress') $bg = 'bg-info text-dark';
                                    if($status == 'Completed') $bg = 'bg-success';
                                    if($status == 'Rejected') $bg = 'bg-danger';
                                ?>
                                <span class="badge-status <?= $bg ?>"><?= strtoupper($status) ?></span>
                            </td>
                            <td>
                                <div class="small fw-bold text-white"><?= $r['staff_name'] ?? '<span class="text-muted opacity-50" style="font-size:0.6rem">AUTO-SEARCHING...</span>' ?></div>
                                <?php if($r['staff_phone']): ?>
                                    <div class="small text-info"><i class="bi bi-telephone"></i> <?= $r['staff_phone'] ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-3 border-top border-secondary text-center small text-white-50">
            <span class="text-warning">●</span> PENDING: Staff reviewing. 
            <span class="ms-3 text-info">●</span> IN PROGRESS: Staff assigned.
            <span class="ms-3 text-success">●</span> COMPLETED: Issue resolved.
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-3 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.75rem; letter-spacing: 2px;">
            <i class="bi bi-arrow-left me-2"></i> RETURN TO HUB
        </a>
    </div>
</div>

<?php include 'chatbot_component.php'; ?>

</body>
</html>