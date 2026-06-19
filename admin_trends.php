<?php
/**
 * LESBOT NEURAL BI COMMAND CENTER v8.0
 * ARCHITECTURE: DATA WAREHOUSING + AI STRATEGIC FORENSICS
 */
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // 1. DATA WAREHOUSE: MAINTENANCE VOLUME (7D TREND)
    $daily_sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM maintenance_request WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY DATE(created_at) ORDER BY date ASC";
    $daily_trends = $pdo->query($daily_sql)->fetchAll(PDO::FETCH_ASSOC);

    // 2. DATA WAREHOUSE: STAFF ACCOUNTABILITY (Forensic Rejection Tracking)
    $staff_sql = "SELECT u.name, 
                  COUNT(mr.request_id) as assigned,
                  SUM(CASE WHEN mr.status = 'Completed' THEN 1 ELSE 0 END) as resolved,
                  SUM(mr.rejected_count) as total_rejections
                  FROM users u
                  JOIN staff st ON u.user_id = st.staff_id
                  LEFT JOIN maintenance_request mr ON st.staff_id = mr.assigned_staff_id
                  GROUP BY u.user_id, u.name";
    $staff_perf = $pdo->query($staff_sql)->fetchAll(PDO::FETCH_ASSOC);

    // 3. DATA WAREHOUSE: STUDENT INFRACTION INDEX (Identifying High-Risk Residents)
    $student_risk_sql = "SELECT u.name, s.matric_number, 
                         COUNT(sp.penalty_id) as total_penalties,
                         SUM(sp.amount) as total_debt
                         FROM student s
                         JOIN users u ON s.matric_number = u.user_id
                         JOIN student_penalties sp ON s.matric_number = sp.matric_number
                         GROUP BY s.matric_number, u.name
                         ORDER BY total_penalties DESC LIMIT 5";
    $risky_students = $pdo->query($student_risk_sql)->fetchAll(PDO::FETCH_ASSOC);

    // 4. DW: INFRASTRUCTURE HOTSPOTS
    $m_trends = $pdo->query("SELECT c.category_name, COUNT(*) as count FROM maintenance_request mr JOIN category c ON mr.category_id = c.category_id GROUP BY c.category_name")->fetchAll(PDO::FETCH_ASSOC);

    // 5. DW: FINANCIAL INTEGRITY
    $p_status = $pdo->query("SELECT SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid, SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as unpaid FROM student_penalties")->fetch(PDO::FETCH_ASSOC);

    // 6. AI FORENSIC ANALYSIS (Llama 3.3)
    $forensic_data = json_encode(['staff' => $staff_perf, 'risk_students' => $risky_students, 'finance' => $p_status]);
    $ai_prompt = "Act as 'Chief Forensic Auditor'. Data: $forensic_data. 
                  1. Name the staff member with the highest rejection/failure rate.
                  2. Name the top-risk student based on infraction frequency.
                  3. Suggest one 'Disciplinary Enforcement' and one 'Operational Solution'.
                  Limit: 5 precise bullet points.";
    $ai_insights = getLesBotResponse($ai_prompt, "Admin", $_SESSION['full_name']);

} catch (PDOException $e) { die("DW Engine Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | BI Forensic Command</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-red: #ff4d4d; --neon-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 80px; }
        .neural-nav { position: fixed; top: 15px; left: 50%; transform: translateX(-50%); width: 95%; max-width: 1400px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 8px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .bi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .bi-card { background: rgba(255,255,255,0.02); border: 1px solid var(--neon-border); border-radius: 20px; padding: 25px; backdrop-filter: blur(15px); transition: 0.3s; }
        .ai-forensic-card { grid-column: span 2; background: linear-gradient(135deg, rgba(255, 77, 77, 0.1), rgba(0, 212, 255, 0.05)); border-color: var(--neon-red); }
        .stat-label { font-family: 'Orbitron'; font-size: 0.6rem; letter-spacing: 2px; color: var(--lesbot-cyan); margin-bottom: 10px; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT BI •</a>
    <div style="display:flex; gap:20px;">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.6;">DASHBOARD</a>
        <a href="admin_trends.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">BI_FORENSICS</a>
    </div>
</nav>

<div class="container-fluid px-5">
    <div class="bi-grid">
        
        <!-- 1. AI STRATEGIC FORENSICS -->
        <div class="bi-card ai-forensic-card">
            <p class="stat-label text-danger"><i class="bi bi-shield-exclamation me-2"></i>Neural Forensic Report</p>
            <div class="small" style="line-height: 1.6; color: #ffcccc;">
                <?= nl2br($ai_insights) ?>
            </div>
        </div>

        <!-- 2. MAINTENANCE LINE TREND -->
        <div class="bi-card" style="grid-column: span 2;">
            <p class="stat-label">System Load (7D Maintenance Trend)</p>
            <canvas id="lineChart" style="max-height: 150px;"></canvas>
        </div>

        <!-- 3. STAFF PERFORMANCE FORENSICS -->
        <div class="bi-card" style="grid-column: span 2;">
            <p class="stat-label">Staff Accountability Index</p>
            <table class="table table-dark table-hover small m-0">
                <thead><tr class="opacity-50"><th>NAME</th><th>RESOLVED</th><th>REJECTIONS</th><th>SLA RATE</th></tr></thead>
                <tbody>
                    <?php foreach($staff_perf as $s): 
                        $rate = ($s['assigned'] > 0) ? ($s['resolved'] / $s['assigned']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $s['name'] ?></td>
                        <td class="text-success"><?= $s['resolved'] ?></td>
                        <td class="<?= $s['total_rejections'] > 2 ? 'text-danger' : 'text-white' ?>"><?= $s['total_rejections'] ?></td>
                        <td class="font-orbitron"><?= round($rate) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 4. STUDENT RISK LEADERBOARD -->
        <div class="bi-card" style="grid-column: span 2;">
            <p class="stat-label">High-Risk Residents (Infraction Leaders)</p>
            <table class="table table-dark table-hover small m-0">
                <thead><tr class="opacity-50"><th>MATRIC</th><th>NAME</th><th>INCIDENTS</th><th>DEBT</th></tr></thead>
                <tbody>
                    <?php foreach($risky_students as $rs): ?>
                    <tr>
                        <td class="text-info"><?= $rs['matric_number'] ?></td>
                        <td><?= $rs['name'] ?></td>
                        <td class="text-center"><?= $rs['total_penalties'] ?></td>
                        <td class="text-danger">RM <?= $rs['total_debt'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 5. CATEGORY DISTRIBUTION (PIE) -->
        <div class="bi-card">
            <p class="stat-label text-center">Category Hotspots</p>
            <canvas id="maintChart"></canvas>
        </div>

        <!-- 6. FINANCIAL SETTLEMENT (DOUGHNUT) -->
        <div class="bi-card">
            <p class="stat-label text-center">Financial Recovery</p>
            <canvas id="penaltyChart"></canvas>
        </div>

    </div>
</div>

<script>
// CHARTS LOGIC
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach($daily_trends as $d) echo "'".date('D', strtotime($d['date']))."',"; ?>],
        datasets: [{ label: 'Tickets', data: [<?php foreach($daily_trends as $d) echo $d['count'].","; ?>], borderColor: '#00d4ff', tension: 0.4, fill: true, backgroundColor: 'rgba(0,212,255,0.05)' }]
    },
    options: { plugins: { legend: false }, scales: { y: { display: false }, x: { grid: { display: false }, ticks: { color: '#666' } } } }
});

new Chart(document.getElementById('penaltyChart'), {
    type: 'doughnut',
    data: { labels: ['Paid', 'Unpaid'], datasets: [{ data: [<?= $p_status['paid'] ?>, <?= $p_status['unpaid'] ?>], backgroundColor: ['#2ecc71', '#ff4d4d'], borderWidth: 0 }] },
    options: { plugins: { legend: false }, cutout: '75%' }
});

new Chart(document.getElementById('maintChart'), {
    type: 'pie',
    data: {
        labels: [<?php foreach($m_trends as $mt) echo "'".$mt['category_name']."',"; ?>],
        datasets: [{ data: [<?php foreach($m_trends as $mt) echo $mt['count'].","; ?>], backgroundColor: ['#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#1abc9c'], borderWidth: 0 }]
    },
    options: { plugins: { legend: false } }
});
</script>

</body>
</html>