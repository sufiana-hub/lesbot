<?php
/**
 * LESBOT NEURAL TRENDS & BI DASHBOARD
 * FIXED: SQL STRICT MODE COMPLIANCE v5.1
 */
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // 1. DATA WAREHOUSE: Aggregating Staff Performance (Fixed for GROUP BY)
    $staff_sql = "SELECT u.name, st.staff_id,
                  COUNT(mr.request_id) as total,
                  SUM(CASE WHEN mr.status = 'Completed' THEN 1 ELSE 0 END) as resolved,
                  SUM(CASE WHEN mr.status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                  SUM(CASE WHEN mr.status = 'On-Hold' THEN 1 ELSE 0 END) as on_hold
                  FROM staff st
                  JOIN users u ON st.staff_id = u.user_id
                  LEFT JOIN maintenance_request mr ON st.staff_id = mr.assigned_staff_id
                  GROUP BY st.staff_id, u.name"; // FIXED: Added u.name to satisfy strict mode
    $staff_data = $pdo->query($staff_sql)->fetchAll();

    // 2. DATA WAREHOUSE: Student Demographic & Penalty Trends
    $student_stats = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM student WHERE room_number LIKE 'A%') as males,
        (SELECT COUNT(*) FROM student WHERE room_number LIKE 'B%') as females,
        (SELECT COUNT(*) FROM student_penalties WHERE is_paid = 0) as unpaid_fines,
        (SELECT COUNT(*) FROM student_penalties) as total_incidents
    ")->fetch();

    $top_penalties = $pdo->query("SELECT pt.description, COUNT(*) as count 
                                  FROM student_penalties sp 
                                  JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id 
                                  GROUP BY pt.description 
                                  ORDER BY count DESC LIMIT 3")->fetchAll();

    // 3. AI STRATEGIC ANALYST: Feed data to Llama 3.3
    $data_summary = "Staff metrics: " . json_encode($staff_data) . ". Dorm metrics: " . json_encode($student_stats) . ". Main violations: " . json_encode($top_penalties);
    
    $ai_prompt = "Act as 'LesBot Strategic AI'. Analyze this dormitory data: $data_summary. 
                  1. Summarize overall system health in 1 sentence.
                  2. Identify if any staff members are underperforming.
                  3. Suggest 3 specific data-driven dormitory improvements (Neural Enforcements). 
                  Tone: Futuristic and professional.";
    
    $ai_insights = getLesBotResponse($ai_prompt, "Admin", $_SESSION['full_name']);

    // 4. ACTION ZONE: Rejected/Unassigned reports
    $rejected_tasks = $pdo->query("SELECT mr.request_id, u.name, c.category_name, mr.rejected_count 
                                   FROM maintenance_request mr 
                                   JOIN users u ON mr.student_id = u.user_id 
                                   JOIN category c ON mr.category_id = c.category_id 
                                   WHERE mr.status = 'Rejected' OR mr.assigned_staff_id IS NULL")->fetchAll();

} catch (PDOException $e) { 
    die("Neural Link BI Error: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | BI Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 100px; min-height: 100vh; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 10px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .bi-container { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 40px; padding: 40px; backdrop-filter: blur(20px); }
        .ai-insight-card { background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(188, 19, 254, 0.1)); border: 1px solid var(--lesbot-cyan); border-radius: 25px; padding: 30px; margin-bottom: 40px; box-shadow: 0 0 30px rgba(0, 212, 255, 0.1); }
        .stat-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 20px; text-align: center; }
        .stat-val { font-family: 'Orbitron'; font-size: 1.5rem; font-weight: 900; color: var(--lesbot-cyan); }
        .progress { height: 6px; background: rgba(255,255,255,0.1); }
        .progress-bar { background: var(--lesbot-cyan); box-shadow: 0 0 10px var(--lesbot-cyan); }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT •</a>
    <div class="d-flex gap-4">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.7;">DASHBOARD</a>
        <a href="admin_trends.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">TRENDS</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="bi-container">
        <div class="text-center mb-5">
            <h2 style="font-family:'Orbitron'; font-weight:900; letter-spacing:5px;">STRATEGIC <span style="color:var(--lesbot-cyan);">ANALYSIS</span></h2>
            <p class="small text-white-50">AI BUSINESS INTELLIGENCE • NEURAL DATA WAREHOUSE v3.0</p>
        </div>

        <!-- 1. AI STRATEGIC REPORT -->
        <div class="ai-insight-card">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-cpu-fill fs-3 text-info me-3"></i>
                <h5 class="m-0 font-orbitron" style="letter-spacing: 2px;">NEURAL CORE STRATEGIC INSIGHTS</h5>
            </div>
            <div class="small" style="line-height: 1.8; color: rgba(255,255,255,0.9);">
                <?= nl2br($ai_insights) ?>
            </div>
        </div>

        <!-- 2. AGGREGATED METRICS -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="small text-white-50 font-orbitron">GENDER DISTRIBUTION</p>
                    <div class="stat-val"><?= $student_stats['males'] ?> MALE | <?= $student_stats['females'] ?> FEMALE</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="small text-white-50 font-orbitron">PENALTY INDEX</p>
                    <div class="stat-val text-danger"><?= $student_stats['unpaid_fines'] ?> UNPAID FINES</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="small text-white-50 font-orbitron">TOP VIOLATION</p>
                    <div class="stat-val text-warning"><?= $top_penalties[0]['description'] ?? 'NONE' ?></div>
                </div>
            </div>
        </div>

        <!-- 3. STAFF ANALYTICS GRID -->
        <div class="p-4 bg-dark bg-opacity-25 rounded-4 border border-secondary mb-5">
            <p class="font-orbitron small text-info mb-4">STAFF EFFICIENCY LEADERBOARD</p>
            <table class="table table-dark table-hover small align-middle">
                <thead><tr><th>TECHNICIAN</th><th>TOTAL</th><th>RESOLVED</th><th>REJECTS</th><th>EFFICIENCY</th></tr></thead>
                <tbody>
                    <?php foreach($staff_data as $row): 
                        $perc = ($row['total'] > 0) ? ($row['resolved'] / $row['total']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['total'] ?></td>
                        <td class="text-success"><?= $row['resolved'] ?></td>
                        <td class="text-danger"><?= $row['rejected'] ?></td>
                        <td style="width: 200px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1"><div class="progress-bar" style="width:<?= $perc ?>%"></div></div>
                                <span><?= round($perc) ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 4. REJECT RESCUE ACTION ZONE -->
        <div class="p-4 rounded-4 border border-danger bg-danger bg-opacity-10">
            <p class="font-orbitron small text-danger mb-4"><i class="bi bi-shield-slash"></i> CRITICAL REASSIGNMENT ZONE (REJECTED REPORTS)</p>
            <table class="table table-dark small">
                <thead><tr><th>ID</th><th>STUDENT</th><th>CATEGORY</th><th>ACTION</th></tr></thead>
                <tbody>
                    <?php foreach($rejected_tasks as $rt): ?>
                    <tr>
                        <td>#<?= $rt['request_id'] ?></td>
                        <td><?= $rt['name'] ?></td>
                        <td><?= $rt['category_name'] ?></td>
                        <td><a href="admin_assign_staff.php?id=<?= $rt['request_id'] ?>" class="btn btn-outline-danger btn-sm py-0 font-orbitron" style="font-size:0.5rem;">OVERRIDE</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'chatbot_component.php'; ?>
</body>
</html>