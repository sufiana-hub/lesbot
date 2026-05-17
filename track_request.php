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
    // 2. READ: Fetch Maintenance Requests with Joins
    $sql = "SELECT 
                mr.request_id, 
                c.category_name, 
                mr.description, 
                mr.status, 
                mr.priority, 
                mr.created_at,
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
    <title>LesBot | Neural Tracking</title>
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
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 80%);
            color: #ffffff; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0;
            padding-top: 130px;
            min-height: 100vh;
        }

        /* --- Floating Navigation --- */
        .neural-nav {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9);
            backdrop-filter: blur(15px); border: 1px solid var(--glass-border);
            border-radius: 50px; padding: 12px 35px; display: flex;
            justify-content: space-between; align-items: center; z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }
        .nav-brand { font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan); text-decoration: none; letter-spacing: 2px; }
        .nav-links-container { display: flex; gap: 25px; list-style: none; margin: 0; padding: 0; }
        .nav-links-container a { 
            color: rgba(255, 255, 255, 0.6); text-decoration: none; font-family: 'Orbitron'; 
            font-size: 0.7rem; letter-spacing: 1px; transition: 0.3s;
        }
        .nav-links-container a:hover, .nav-links-container a.active { color: var(--lesbot-cyan); text-shadow: 0 0 10px var(--lesbot-cyan); }

        /* --- Main Dashboard Container --- */
        .system-container {
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid var(--glass-border);
            border-radius: 40px; 
            padding: 50px; 
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .header-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: 5px; color: var(--lesbot-cyan); }

        /* --- Optimized Modern Card-Table --- */
        .custom-table { 
            border-collapse: separate; 
            border-spacing: 0 15px; 
            width: 100%;
        }

        .custom-table thead th { 
            border: none; font-family: 'Orbitron'; font-size: 0.8rem; 
            color: var(--lesbot-cyan); text-transform: uppercase; 
            letter-spacing: 2px; padding: 15px 20px;
        }

        /* Card Row Effect */
        .custom-table tbody tr { 
            background: rgba(255, 255, 255, 0.04); 
            border-radius: 15px;
            transition: 0.3s;
        }

        .custom-table tbody tr:hover { 
            background: rgba(0, 212, 255, 0.06); 
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .custom-table td { 
            padding: 25px 20px; 
            vertical-align: middle;
            color: #ffffff;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Font Styling for Readability */
        .id-text { font-family: 'Orbitron'; font-weight: 900; color: var(--lesbot-cyan); font-size: 0.9rem; }
        .category-text { font-family: 'Orbitron'; font-size: 0.8rem; font-weight: 700; color: #ffffff; }
        .desc-text { font-size: 1.1rem; color: rgba(255,255,255,0.9); line-height: 1.6; font-weight: 500; }
        
        .badge-status { 
            font-family: 'Orbitron'; font-size: 0.7rem; letter-spacing: 1px;
            padding: 8px 18px; border-radius: 10px; font-weight: 900;
        }

        .staff-info { font-family: 'Orbitron'; font-size: 0.65rem; color: var(--lesbot-cyan); }
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
    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold" style="font-family: 'Orbitron'; font-size: 0.6rem;">DISCONNECT</a>
</nav>

<div class="container mb-5">
    <div class="system-container shadow-lg">
        <div class="text-center mb-5">
            <h2 class="header-title">TRACK <span class="text-white">REQUESTS</span></h2>
            <p class="text-white-50 small" style="letter-spacing: 3px; font-family: 'Orbitron'; font-size: 0.6rem;">NEURAL LINK ACTIVE • MONITORING SYSTEM STATUS</p>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">IDENTIFIER</th>
                        <th style="width: 20%;">CLASSIFICATION</th>
                        <th style="width: 40%;">NEURAL DESCRIPTION</th>
                        <th style="width: 20%;" class="text-center">SYSTEM STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3 text-white-50"></i>
                                <span class="text-white-50 font-orbitron small">NO ACTIVE DATA LOGS FOUND</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>
                                <div class="id-text">#<?= htmlspecialchars($r['request_id']) ?></div>
                                <div class="small text-white-50 mt-1" style="font-size: 0.6rem;"><?= date('d M Y | H:i', strtotime($r['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="category-text"><?= htmlspecialchars($r['category_name']) ?></div>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger mt-2" style="font-size: 0.5rem; font-family: 'Orbitron';"><?= strtoupper($r['priority']) ?></span>
                            </td>
                            <!-- INCREASED FONT SIZE FOR DESCRIPTION -->
                            <td class="desc-text">
                                <?= nl2br(htmlspecialchars($r['description'])) ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $status = $r['status'];
                                    $bg = ($status == 'Pending') ? 'bg-warning text-dark' : (($status == 'In Progress') ? 'bg-info text-dark' : 'bg-success');
                                ?>
                                <div class="badge-status <?= $bg ?>"><?= strtoupper($status) ?></div>
                                
                                <div class="mt-3 staff-info">
                                    <?php if($r['staff_name']): ?>
                                        <i class="bi bi-person-check me-1"></i> <?= strtoupper($r['staff_name']) ?>
                                    <?php else: ?>
                                        <span class="opacity-50" style="font-size: 0.55rem;">AWAITING ASSIGNMENT</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 text-center">
            <a href="student_dashboard.php" class="btn btn-outline-info px-5 py-3 rounded-pill fw-bold" style="font-family: 'Orbitron'; font-size: 0.75rem; letter-spacing: 2px;">
                <i class="bi bi-arrow-left me-2"></i> RETURN TO HUB
            </a>
        </div>
    </div>
</div>

<!-- AI Component Include -->
<?php include 'chatbot_component.php'; ?>

</body>
</html>