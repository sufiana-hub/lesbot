<?php
/**
 * LESBOT NEURAL TRACKING
 * FIXED: SQL STRICT MODE & DUPLICATION PROTOCOL
 */
session_start();
require_once 'db_config.php';

// 1. NEURAL ACCESS CONTROL
if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); 
    exit(); 
}

$id = $_SESSION['std_id'];

try {
    /** 
     * 2. DATA ACQUISITION (FIXED FOR STRICT MODE)
     * We use 'SELECT DISTINCT' instead of 'GROUP BY'.
     * This solves the duplication while being 100% compatible with Azure Strict Mode.
     */
    $sql = "SELECT DISTINCT
                mr.request_id, 
                c.category_name, 
                mr.description, 
                mr.status, 
                mr.priority, 
                mr.created_at,
                u.name AS staff_name
            FROM maintenance_request mr
            JOIN category c ON mr.category_id = c.category_id
            LEFT JOIN users u ON mr.assigned_staff_id = u.user_id
            WHERE mr.student_id = :id
            ORDER BY mr.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $requests = $stmt->fetchAll();

} catch (PDOException $e) {
    // This will now handle any further SQL logic faults
    die("Neural Link Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
    <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
    <meta name="robots" content="index, follow">
    <meta charset="utf-8">
    <title>LesBot | Neural Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #080a0f; 
            --neon-red: #ff4d4d;
            --neon-border: rgba(0, 212, 255, 0.3);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 80%);
            color: #ffffff; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 130px; min-height: 100vh;
        }

        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9);
            backdrop-filter: blur(15px); border: 1px solid var(--neon-border);
            border-radius: 50px; padding: 12px 35px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
        }

        .system-container {
            background: rgba(0, 0, 0, 0.3); border: 1px solid var(--neon-border);
            border-radius: 40px; padding: 50px; backdrop-filter: blur(20px);
        }

        .header-row {
            background: rgba(0, 212, 255, 0.1); border-radius: 15px; margin-bottom: 25px; 
            padding: 18px 0; display: flex; align-items: center; text-align: center;
            font-family: 'Orbitron'; font-size: 0.75rem; color: var(--lesbot-cyan);
            letter-spacing: 2px; border: 1px solid var(--neon-border);
        }

        .request-card {
            background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; margin-bottom: 18px; padding: 35px;
            display: flex; align-items: center; transition: 0.4s;
        }

        .priority-tag { background: rgba(255, 77, 77, 0.1); color: var(--neon-red); border: 1px solid var(--neon-red); padding: 5px 12px; border-radius: 5px; font-family: 'Orbitron'; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; }
        
        .id-val { font-family: 'Orbitron'; color: var(--lesbot-cyan); font-weight: 900; font-size: 0.95rem; }
        .desc-val { font-size: 1.1rem; color: #ffffff; line-height: 1.6; padding: 0 20px; }
        
        .badge-status { font-family: 'Orbitron'; font-size: 0.85rem; padding: 12px 30px; border-radius: 12px; font-weight: 900; text-transform: uppercase; }
        .staff-label { font-family: 'Orbitron'; font-size: 0.65rem; color: rgba(255,255,255,0.3); margin-bottom: 5px; }

    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" style="color:var(--lesbot-cyan); text-decoration:none; font-family:'Orbitron'; font-weight:900;">LESBOT •</a>
    <div style="display:flex; gap:30px;">
        <a href="student_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">UTAMA</a>
        <a href="maintenance_report.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">REPORT</a>
        <a href="student_history.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">HISTORY</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="system-container">
        <h2 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900; letter-spacing:8px;">TRACK <span style="color:var(--lesbot-cyan);">REQUESTS</span></h2>

        <!-- HEADER ROW -->
        <div class="header-row d-none d-lg-flex row mx-0">
            <div class="col-2 px-4">IDENTIFIER</div>
            <div class="col-2">CLASSIFICATION</div>
            <div class="col-5">NEURAL DESCRIPTION</div>
            <div class="col-3">SYSTEM STATUS</div>
        </div>

        <?php if (empty($requests)): ?>
            <div class="text-center py-5 opacity-50 font-orbitron">NO ACTIVE LOGS DETECTED</div>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
                <div class="request-card row mx-0 align-items-center">
                    <div class="col-lg-2 text-center text-lg-start px-4">
                        <div class="id-val">#<?= htmlspecialchars($r['request_id']) ?></div>
                        <div class="small opacity-50 mt-1"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                    </div>
                    
                    <div class="col-lg-2 text-center text-lg-start">
                        <div style="font-family: 'Orbitron'; font-weight: 700; font-size: 0.8rem;"><?= strtoupper($r['category_name']) ?></div>
                        <div class="priority-tag mt-2">
                             <i class="bi bi-exclamation-triangle-fill"></i> <?= strtoupper($r['priority']) ?>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="desc-val"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
                    </div>

                    <div class="col-lg-3 text-center">
                        <?php 
                            $status = $r['status'];
                            $bg = 'bg-secondary';
                            if($status == 'Pending') $bg = 'bg-warning text-dark';
                            if($status == 'In Progress') $bg = 'bg-info text-dark';
                            if($status == 'On Hold') $bg = 'bg-primary';
                            if($status == 'Completed') $bg = 'bg-success';
                            if($status == 'Rejected') $bg = 'bg-danger';
                        ?>
                        <div class="badge-status <?= $bg ?>"><?= strtoupper($status) ?></div>
                        <div class="mt-4">
                            <div class="staff-label">AUTHENTICATED PERSONNEL</div>
                            <div class="small fw-bold text-info" style="font-family:'Orbitron'; letter-spacing:1px;">
                                <?= strtoupper($r['staff_name'] ?? 'AUTO-SEARCHING...') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-3 rounded-pill fw-bold" style="font-family:'Orbitron'; font-size:0.7rem; letter-spacing:2px;">
                RETURN TO HUB
            </a>
        </div>
    </div>
</div>

<?php include 'chatbot_component.php'; ?>

</body>
</html>