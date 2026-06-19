<?php
/**
 * LESBOT NEURAL BI COMMAND CENTER
 * DATA WAREHOUSING + AI STRATEGIC ANALYTICS v7.0
 */
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // 1. DW: MAINTENANCE CATEGORY AGGREGATION
    $m_trends = $pdo->query("SELECT c.category_name, COUNT(*) as count FROM maintenance_request mr JOIN category c ON mr.category_id = c.category_id GROUP BY c.category_name")->fetchAll(PDO::FETCH_ASSOC);

    // 2. DW: DAILY TRENDS (Line Chart Logic - Last 7 Days)
    $daily_sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM maintenance_request WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY DATE(created_at) ORDER BY date ASC";
    $daily_trends = $pdo->query($daily_sql)->fetchAll(PDO::FETCH_ASSOC);

    // 3. DW: PENALTY INTEGRITY
    $p_status = $pdo->query("SELECT SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid, SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as unpaid FROM student_penalties")->fetch(PDO::FETCH_ASSOC);

    // 4. DW: STAFF EFFICIENCY INDEX
    $staff_perf = $pdo->query("SELECT u.name, COUNT(mr.request_id) as total, SUM(CASE WHEN mr.status = 'Completed' THEN 1 ELSE 0 END) as resolved FROM staff st JOIN users u ON st.staff_id = u.user_id LEFT JOIN maintenance_request mr ON st.staff_id = mr.assigned_staff_id GROUP BY st.staff_id, u.name")->fetchAll(PDO::FETCH_ASSOC);

    // 5. DW: GENDER DEMOGRAPHICS
    $demographics = $pdo->query("SELECT SUM(CASE WHEN room_number LIKE 'A%' THEN 1 ELSE 0 END) as male, SUM(CASE WHEN room_number LIKE 'B%' THEN 1 ELSE 0 END) as female FROM student")->fetch(PDO::FETCH_ASSOC);

    // 6. AI STRATEGIC SUMMARY (Prompted for STRUCTURE and BREVITY)
    $summary_data = json_encode(['daily' => $daily_trends, 'staff' => $staff_perf, 'penalties' => $p_status]);
    $ai_prompt = "Dormitory Data: $summary_data. Provide exactly 3 short bullet points (Max 15 words each) on system health, 1 underperforming staff ID, and 1 conclusion for future solution.";
    $ai_insights = getLesBotResponse($ai_prompt, "Admin", $_SESSION['full_name']);

} catch (PDOException $e) { die("BI Data Fault: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | BI Executive Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --glass: rgba(255, 255, 255, 0.02); --neon-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 90px; }
        
        .neural-nav { position: fixed; top: 15px; left: 50%; transform: translateX(-50%); width: 95%; max-width: 1400px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 8px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        
        /* GRID LAYOUT LIKE POWER BI */
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr 350px; grid-template-rows: auto auto; gap: 20px; }
        
        .bi-card { background: var(--glass); border: 1px solid var(--neon-border); border-radius: 20px; padding: 20px; backdrop-filter: blur(10px); }
        .ai-sidebar { grid-row: span 2; background: linear-gradient(180deg, rgba(0,212,255,0.05) 0%, rgba(188,19,254,0.05) 100%); border: 1px solid var(--lesbot-cyan); }
        
        .stat-label { font-family: 'Orbitron'; font-size: 0.6rem; letter-spacing: 2px; color: var(--lesbot-cyan); margin-bottom: 15px; text-transform: uppercase; }
        .ai-point { border-left: 3px solid var(--lesbot-cyan); padding-left: 15px; margin-bottom: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT BI •</a>
    <div style="display:flex; gap:20px;">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; opacity:0.6;">DASHBOARD</a>
        <a href="admin_trends.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">BI_ANALYTICS</a>
    </div>
</nav>

<div class="container-fluid px-5">
    <div class="dashboard-grid">
        
        <!-- TOP LEFT: MAINTENANCE OVER TIME (LINE CHART) -->
        <div class="bi-card">
            <p class="stat-label">Neural Activity: Maintenance Load (7D)</p>
            <canvas id="lineChart" style="max-height: 250px;"></canvas>
        </div>

        <!-- TOP CENTER: PENALTY DISTRIBUTION (DOUGHNUT) -->
        <div class="bi-card">
            <p class="stat-label">Financial Integrity: Settlement Ratio</p>
            <div class="row align-items-center">
                <div class="col-6"><canvas id="penaltyChart"></canvas></div>
                <div class="col-6 text-center">
                    <h2 class="font-orbitron m-0"><?= round(($p_status['paid']/($p_status['paid']+$p_status['unpaid']))*100) ?>%</h2>
                    <small class="opacity-50">COLLECTION RATE</small>
                </div>
            </div>
        </div>

        <!-- RIGHT: AI STRATEGIC INSIGHTS -->
        <div class="bi-card ai-sidebar">
            <p class="stat-label text-white"><i class="bi bi-robot me-2"></i>AI Strategic Analyst</p>
            <div class="mt-4">
                <?php 
                $points = explode("\n", $ai_insights);
                foreach($points as $p) {
                    if(trim($p)) echo "<div class='ai-point'>".htmlspecialchars($p)."</div>";
                }
                ?>
            </div>
            <div class="mt-5 pt-4 border-top border-secondary small opacity-50">
                CONCLUSION: Operational efficiency is currently stable. Monitor underperforming entities.
            </div>
        </div>

        <!-- BOTTOM LEFT: CATEGORY HOTSPOTS (BAR CHART) -->
        <div class="bi-card">
            <p class="stat-label">Infrastructure Hotspots (By Category)</p>
            <canvas id="barChart" style="max-height: 250px;"></canvas>
        </div>

        <!-- BOTTOM CENTER: STAFF LEADERBOARD -->
        <div class="bi-card">
            <p class="stat-label">Staff Performance Scorecard</p>
            <table class="table table-dark table-hover small m-0">
                <thead><tr class="opacity-50"><th>NAME</th><th>RESOLVED</th><th>INDEX</th></tr></thead>
                <tbody>
                    <?php foreach($staff_perf as $s): 
                        $rate = ($s['total'] > 0) ? ($s['resolved'] / $s['total']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $s['name'] ?></td>
                        <td class="text-info"><?= $s['resolved'] ?></td>
                        <td><div class="progress"><div class="progress-bar bg-info" style="width:<?= $rate ?>%"></div></div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
// 1. LINE CHART: MAINTENANCE TRENDS
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach($daily_trends as $d) echo "'".date('D', strtotime($d['date']))."',"; ?>],
        datasets: [{ label: 'Tickets', data: [<?php foreach($daily_trends as $d) echo $d['count'].","; ?>], borderColor: '#00d4ff', tension: 0.4, fill: true, backgroundColor: 'rgba(0,212,255,0.05)' }]
    },
    options: { plugins: { legend: false }, scales: { y: { grid: { color: '#222' } }, x: { grid: { display: false } } } }
});

// 2. DOUGHNUT: PENALTIES
new Chart(document.getElementById('penaltyChart'), {
    type: 'doughnut',
    data: { labels: ['Paid', 'Unpaid'], datasets: [{ data: [<?= $p_status['paid'] ?>, <?= $p_status['unpaid'] ?>], backgroundColor: ['#2ecc71', '#ff4d4d'], borderWidth: 0 }] },
    options: { plugins: { legend: false }, cutout: '80%' }
});

// 3. BAR: CATEGORIES
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach($m_trends as $mt) echo "'".substr($mt['category_name'],0,10)."',"; ?>],
        datasets: [{ data: [<?php foreach($m_trends as $mt) echo $mt['count'].","; ?>], backgroundColor: '#00d4ff' }]
    },
    options: { plugins: { legend: false }, scales: { y: { beginAtZero: true } } }
});
</script>

</body>
</html>