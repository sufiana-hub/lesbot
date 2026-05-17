<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['std_id']) || $_SESSION['role'] !== 'Student') { 
    header("Location: login.php"); exit(); 
}

$id = $_SESSION['std_id'];

try {
    $sql = "SELECT mr.request_id, c.category_name, mr.description, mr.status, mr.priority, mr.created_at, u.name AS staff_name, st.phone_num AS staff_phone
            FROM maintenance_request mr
            JOIN category c ON mr.category_id = c.category_id
            LEFT JOIN staff st ON mr.assigned_staff_id = st.staff_id
            LEFT JOIN users u ON st.staff_id = u.user_id
            WHERE mr.student_id = :id
            ORDER BY mr.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) { die("Neural Link Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | Neural Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { 
            --lesbot-cyan: #00d4ff; 
            --obsidian: #080a0f; 
            --glass-card: rgba(255, 255, 255, 0.03);
            --neon-border: rgba(0, 212, 255, 0.3);
        }

        body { 
            background-color: var(--obsidian); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 80%);
            color: #ffffff; font-family: 'Rajdhani', sans-serif; margin: 0; padding-top: 130px; min-height: 100vh;
        }

        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; 
            background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border);
            border-radius: 50px; padding: 12px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000;
        }

        /* --- THE UI FIX: DARK CARD LAYOUT --- */
        .system-container {
            background: rgba(0, 0, 0, 0.4) !important; /* FORCED DARK */
            border: 1px solid var(--neon-border);
            border-radius: 40px; padding: 50px; backdrop-filter: blur(20px);
        }

        /* Header Row: High Visibility Cyan */
        .header-row {
            background: rgba(0, 212, 255, 0.1);
            border-radius: 15px; margin-bottom: 20px; padding: 15px 0;
            display: flex; align-items: center; text-align: center;
            font-family: 'Orbitron'; font-size: 0.75rem; color: var(--lesbot-cyan);
            letter-spacing: 2px; border: 1px solid var(--neon-border);
        }

        /* Data Card: Dark, Bold, Vibrant */
        .request-card {
            background: rgba(255, 255, 255, 0.04) !important; /* NO MORE WHITE BACKGROUND */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; margin-bottom: 15px; padding: 25px;
            display: flex; align-items: center; transition: 0.3s;
        }

        .request-card:hover {
            border-color: var(--lesbot-cyan);
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.15);
            transform: scale(1.01);
        }

        /* FONT UPGRADES */
        .id-val { font-family: 'Orbitron'; color: var(--lesbot-cyan); font-weight: 900; font-size: 0.9rem; text-shadow: 0 0 10px rgba(0,212,255,0.5); }
        .category-val { font-family: 'Orbitron'; font-weight: 700; color: #ffffff; font-size: 0.8rem; }
        .desc-val { font-size: 1.15rem; color: #ffffff; font-weight: 500; line-height: 1.5; padding: 0 15px; }
        
        .badge-status { 
            font-family: 'Orbitron'; font-size: 0.8rem; padding: 10px 20px; 
            border-radius: 12px; font-weight: 900; text-transform: uppercase;
        }

        .staff-label { font-family: 'Orbitron'; font-size: 0.6rem; color: rgba(255,255,255,0.4); margin-bottom: 5px; }

    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="index.php" style="font-family:'Orbitron'; color:var(--lesbot-cyan); text-decoration:none; font-weight:900;">LESBOT •</a>
    <div style="display:flex; gap:25px;">
        <a href="student_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">UTAMA</a>
        <a href="maintenance_report.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">REPORT</a>
        <a href="student_history.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">HISTORY</a>
    </div>
</nav>

<div class="container">
    <div class="system-container">
        <h2 class="text-center mb-5" style="font-family:'Orbitron'; font-weight:900; letter-spacing:5px;">TRACK <span style="color:var(--lesbot-cyan);">REQUESTS</span></h2>

        <!-- HEADER ROW -->
        <div class="header-row d-none d-lg-flex">
            <div class="col-2">IDENTIFIER</div>
            <div class="col-2">CLASSIFICATION</div>
            <div class="col-5">NEURAL DESCRIPTION</div>
            <div class="col-3">SYSTEM STATUS</div>
        </div>

        <?php if (empty($requests)): ?>
            <div class="text-center py-5 opacity-50">NO ACTIVE LOGS FOUND</div>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
                <div class="request-card row">
                    <div class="col-lg-2 text-center text-lg-start mb-3 mb-lg-0">
                        <div class="id-val">#<?= $r['request_id'] ?></div>
                        <div class="small opacity-50"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                    </div>
                    
                    <div class="col-lg-2 text-center text-lg-start mb-3 mb-lg-0">
                        <div class="category-val"><?= strtoupper($r['category_name']) ?></div>
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger mt-1" style="font-size:0.5rem;"><?= strtoupper($r['priority']) ?></span>
                    </div>

                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <!-- WHITE BOLD TEXT ON DARK BACKGROUND = POP-UP -->
                        <div class="desc-val"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
                    </div>

                    <div class="col-lg-3 text-center">
                        <?php 
                            $status = $r['status'];
                            $bg = ($status == 'Pending') ? 'bg-warning text-dark' : (($status == 'In Progress') ? 'bg-info text-dark' : 'bg-success');
                        ?>
                        <div class="badge-status <?= $bg ?>"><?= $status ?></div>
                        
                        <div class="mt-3">
                            <div class="staff-label">ASSIGNED STAFF</div>
                            <div class="small fw-bold text-info"><?= strtoupper($r['staff_name'] ?? 'AUTO-SEARCHING...') ?></div>
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