<?php
/**
 * LESBOT NEURAL STRATEGIC DASHBOARD
 * BI & DATA WAREHOUSING PROTOCOL v6.0
 */
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // 1. DATA WAREHOUSE: Exact Staff Performance Matrix
    // We calculate percentages directly in SQL for 100% accuracy
    $staff_sql = "SELECT u.name, st.staff_id,
                  COUNT(mr.request_id) as total,
                  SUM(CASE WHEN mr.status = 'Completed' THEN 1 ELSE 0 END) as resolved,
                  SUM(CASE WHEN mr.status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                  SUM(CASE WHEN mr.status = 'On-Hold' THEN 1 ELSE 0 END) as on_hold,
                  SUM(CASE WHEN mr.status = 'Pending' THEN 1 ELSE 0 END) as pending
                  FROM staff st
                  JOIN users u ON st.staff_id = u.user_id
                  LEFT JOIN maintenance_request mr ON st.staff_id = mr.assigned_staff_id
                  GROUP BY st.staff_id, u.name";
    $staff_data = $pdo->query($staff_sql)->fetchAll();

    // 2. DATA WAREHOUSE: Accurate Financial & Demographic Aggregates
    $kpi_sql = "SELECT 
        (SELECT COUNT(*) FROM student WHERE room_number LIKE 'A%') as male_total,
        (SELECT COUNT(*) FROM student WHERE room_number LIKE 'B%') as female_total,
        (SELECT IFNULL(SUM(amount), 0) FROM student_penalties WHERE is_paid = 0) as total_debt,
        (SELECT COUNT(*) FROM student_penalties WHERE is_paid = 0) as unpaid_count,
        (SELECT pt.description FROM student_penalties sp JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id GROUP BY pt.description ORDER BY COUNT(*) DESC LIMIT 1) as top_violation
    ";
    $kpis = $pdo->query($kpi_sql)->fetch();

    // 3. AI STRATEGIC ANALYST: Feed the math to the AI
    $stats_json = json_encode([
        'staff_metrics' => $staff_data,
        'debt' => $kpis['total_debt'],
        'top_violation' => $kpis['top_violation'],
        'gender_split' => ['Male' => $kpis['male_total'], 'Female' => $kpis['female_total']]
    ]);

    $ai_prompt = "Act as the 'Pejabat Fellow Strategic Analyst'. Based on this raw data: $stats_json. 
                  1. Provide a System Health Score (0-100%).
                  2. Identify precisely which staff member is underperforming.
                  3. Based on the top violation and debt, generate a 'New solution or enforcement' to improve student discipline.
                  Format: Use short, futuristic bullet points.";
    
    $ai_analysis = getLesBotResponse($ai_prompt, "Admin", $_SESSION['full_name']);

    // 4. ACTION ZONE: FETCH REJECTED REPORTS FOR REASSIGNMENT
    $reassign_sql = "SELECT mr.request_id, u.name as student_name, s.room_number, c.category_name, mr.rejected_count 
                     FROM maintenance_request mr
                     JOIN users u ON mr.student_id = u.user_id
                     JOIN student s ON u.user_id = s.matric_number
                     JOIN category c ON mr.category_id = c.category_id
                     WHERE mr.status = 'Rejected' OR mr.assigned_staff_id IS NULL";
    $reassign_list = $pdo->query($reassign_sql)->fetchAll();

} catch (PDOException $e) { die("BI Engine Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | BI Trends</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 100px; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 10px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .bi-card { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--neon-border); border-radius: 30px; padding: 35px; backdrop-filter: blur(20px); height: 100%; }
        
        /* AI INSIGHT BOX */
        .ai-strategic-box { background: linear-gradient(145deg, rgba(0, 212, 255, 0.1), rgba(188, 19, 254, 0.05)); border: 1px solid var(--lesbot-cyan); border-radius: 20px; padding: 25px; box-shadow: 0 0 30px rgba(0, 212, 255, 0.15); }
        
        .stat-label { font-family: 'Orbitron'; font-size: 0.65rem; letter-spacing: 2px; color: var(--lesbot-cyan); opacity: 0.7; }
        .stat-huge { font-family: 'Orbitron'; font-size: 1.8rem; font-weight: 900; }
        .progress { height: 10px; background: rgba(255,255,255,0.1); border-radius: 10px; }
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
    <div class="row g-4">
        
        <!-- ROW 1: AI STRATEGIC ANALYST (Visionary Component) -->
        <div class="col-12">
            <div class="ai-strategic-box">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-cpu-fill fs-3 text-info me-3 animate-pulse"></i>
                    <h5 class="m-0 font-orbitron text-uppercase" style="letter-spacing: 3px;">Neural Strategic Analysis</h5>
                </div>
                <div class="small" style="line-height: 1.8; color: #e0f7ff;">
                    <?= nl2br($ai_analysis) ?>
                </div>
            </div>
        </div>

        <!-- ROW 2: DATA KPI CARDS (Accurate Data) -->
        <div class="col-md-4">
            <div class="bi-card text-center">
                <p class="stat-label">DEMOGRAPHIC RATIO</p>
                <div class="stat-huge"><?= $kpis['male_total'] ?>M : <?= $kpis['female_total'] ?>F</div>
                <div class="small opacity-50 mt-2">STUDENT POPULATION SPLIT</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bi-card text-center border-danger">
                <p class="stat-label text-danger">LEDGER DEBT</p>
                <div class="stat-huge text-danger">RM <?= number_format($kpis['total_debt'], 2) ?></div>
                <div class="small opacity-50 mt-2"><?= $kpis['unpaid_count'] ?> UNPAID PENALTIES</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bi-card text-center border-warning">
                <p class="stat-label text-warning">TOP SYSTEM VIOLATION</p>
                <div class="stat-huge text-warning" style="font-size: 1.2rem;"><?= $kpis['top_violation'] ?? 'NONE DETECTED' ?></div>
                <div class="small opacity-50 mt-2">MOST FREQUENT OFFENSE</div>
            </div>
        </div>

        <!-- ROW 3: STAFF PERFORMANCE ENGINE (Business Intelligence) -->
        <div class="col-12">
            <div class="bi-card">
                <p class="stat-label mb-4">STAFF PERFORMANCE DATA WAREHOUSE</p>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead>
                            <tr class="font-orbitron small opacity-50">
                                <th>NAME</th><th>TOTAL</th><th>RESOLVED</th><th>HOLD</th><th>REJECTS</th><th>EFFICIENCY</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($staff_data as $s): 
                                $eff = ($s['total'] > 0) ? ($s['resolved'] / $s['total']) * 100 : 0;
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $s['name'] ?></td>
                                <td><?= $s['total'] ?></td>
                                <td class="text-success"><?= $s['resolved'] ?></td>
                                <td class="text-info"><?= $s['on_hold'] ?></td>
                                <td class="text-danger"><?= $s['rejected'] ?></td>
                                <td style="width: 250px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="progress flex-grow-1"><div class="progress-bar" style="width: <?= $eff ?>%"></div></div>
                                        <span class="small font-orbitron"><?= round($eff) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ROW 4: REASSIGNMENT ACTION ZONE (Admin Intervention) -->
        <div class="col-12">
            <div class="bi-card border-danger bg-danger bg-opacity-10">
                <p class="stat-label text-danger mb-4"><i class="bi bi-shield-slash"></i> CRITICAL: REJECTED REPORTS FOR OVERRIDE</p>
                <?php if (empty($reassign_list)): ?>
                    <p class="text-center opacity-50 py-3">NO REJECTED TASKS FOUND</p>
                <?php else: ?>
                    <table class="table table-dark small">
                        <thead><tr><th>ID</th><th>STUDENT</th><th>CATEGORY</th><th>REJECTS</th><th>ACTION</th></tr></thead>
                        <tbody>
                            <?php foreach($reassign_list as $r): ?>
                            <tr>
                                <td>#<?= $r['request_id'] ?></td>
                                <td><?= $r['student_name'] ?> (<?= $r['room_number'] ?>)</td>
                                <td><?= $r['category_name'] ?></td>
                                <td class="text-warning"><?= $r['rejected_count'] ?> / 3</td>
                                <td><a href="admin_assign_staff.php?id=<?= $r['request_id'] ?>" class="btn btn-outline-info btn-sm font-orbitron" style="font-size:0.5rem;">REASSIGN STAFF</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include 'chatbot_component.php'; ?>
</body>
</html>