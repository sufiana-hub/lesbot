<?php
/**
 * LESBOT NEURAL BI COMMAND CENTER v9.0
 * ARCHITECTURE: DATA WAREHOUSING + AI STRATEGIC FORENSICS (POWER BI LAYOUT)
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

    // 6. AI FORENSIC ANALYSIS (Highly Structured Prompt for UI cleanliness)
    $forensic_data = json_encode(['staff' => $staff_perf, 'risk_students' => $risky_students, 'finance' => $p_status]);
    $ai_prompt = "Act as Chief Data Analyst. Data: $forensic_data. 
                  Provide EXACTLY 3 very short, actionable sentences. 
                  Format strictly as HTML <li> elements (do not use markdown, no bolding, no prefixes). 
                  1: Identify the staff bottleneck. 
                  2: Identify the top-risk student. 
                  3: One operational fix.";
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
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 80px; overflow-x: hidden; }
        
        .neural-nav { position: fixed; top: 15px; left: 50%; transform: translateX(-50%); width: 95%; max-width: 1400px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 8px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        
        /* Power BI Dashboard Layout */
        .dashboard-wrapper { display: flex; gap: 20px; align-items: stretch; max-width: 1600px; margin: 0 auto; padding-bottom: 30px; }
        
        /* Left Panel: AI Sidebar */
        .ai-sidebar { width: 320px; background: linear-gradient(180deg, rgba(255, 77, 77, 0.08), rgba(0, 212, 255, 0.02)); border: 1px solid var(--neon-red); border-radius: 20px; padding: 25px; display: flex; flex-direction: column; flex-shrink: 0; }
        .ai-list { padding-left: 15px; color: #ffcccc; font-size: 0.95rem; line-height: 1.6; font-weight: 500; }
        .ai-list li { margin-bottom: 12px; }
        
        /* Right Panel: Main Grid */
        .main-board { flex-grow: 1; display: flex; flex-direction: column; gap: 20px; }
        .chart-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        
        .bi-card { background: rgba(255,255,255,0.02); border: 1px solid var(--neon-border); border-radius: 20px; padding: 20px; backdrop-filter: blur(15px); }
        .stat-label { font-family: 'Orbitron'; font-size: 0.65rem; letter-spacing: 2px; color: var(--lesbot-cyan); margin-bottom: 15px; text-transform: uppercase; }
        
        /* Table overrides for cleanliness */
        .table-dark { background-color: transparent !important; }
        .table-dark th { border-bottom: 1px solid var(--neon-border); color: var(--lesbot-cyan); font-family: 'Orbitron'; font-size: 0.6rem; letter-spacing: 1px; }
        .table-dark td { border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
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

<div class="container-fluid px-4">
    <div class="dashboard-wrapper">
        
        <div class="ai-sidebar">
            <h5 style="color:var(--neon-red); font-family:'Orbitron'; font-size:1.1rem; font-weight:900; margin-bottom: 0;">
                <i class="bi bi-cpu me-2"></i>Executive Summary
            </h5>
            <p style="font-size: 0.7rem; color: #888; margin-bottom: 15px;">AI GENERATED INSIGHTS</p>
            <hr style="border-color:var(--neon-red); opacity:0.3; margin-top:0;">
            
            <ul class="ai-list">
                <?= $ai_insights ?> 
            </ul>

            <div class="mt-auto pt-4">
                <button class="btn btn-outline-danger w-100" style="font-family: 'Orbitron'; font-size: 0.75rem; letter-spacing: 1px; border-radius: 12px;" onclick="alert('Compiling comprehensive forensic PDF module...')">
                    <i class="bi bi-file-earmark-pdf me-2"></i>GENERATE DEEP SCAN
                </button>
            </div>
        </div>

        <div class="main-board">
            
            <div class="chart-grid">
                
                <div class="bi-card">
                    <p class="stat-label"><i class="bi bi-graph-up text-white me-2"></i>System Load (7D Trend)</p>
                    <canvas id="lineChart" style="max-height: 200px;"></canvas>
                </div>

                <div class="bi-card">
                    <p class="stat-label"><i class="bi bi-bar-chart-fill text-white me-2"></i>Staff Rejection Volume</p>
                    <canvas id="barChart" style="max-height: 200px;"></canvas>
                </div>

                <div class="bi-card">
                    <p class="stat-label"><i class="bi bi-pie-chart-fill text-white me-2"></i>Category Hotspots</p>
                    <canvas id="maintChart" style="max-height: 200px;"></canvas>
                </div>

                <div class="bi-card">
                    <p class="stat-label"><i class="bi bi-cash-stack text-white me-2"></i>Financial Recovery Status</p>
                    <canvas id="penaltyChart" style="max-height: 200px;"></canvas>
                </div>

            </div>

            <div class="chart-grid">
                
                <div class="bi-card">
                    <p class="stat-label">Staff Accountability Ledger</p>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover small m-0">
                            <thead><tr><th>NAME</th><th>RESOLVED</th><th>REJECTIONS</th><th>SLA RATE</th></tr></thead>
                            <tbody>
                                <?php foreach($staff_perf as $s): 
                                    $rate = ($s['assigned'] > 0) ? ($s['resolved'] / $s['assigned']) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($s['name']) ?></td>
                                    <td class="text-success"><?= $s['resolved'] ?></td>
                                    <td class="<?= $s['total_rejections'] > 2 ? 'text-danger fw-bold' : 'text-white' ?>"><?= $s['total_rejections'] ?></td>
                                    <td style="font-family:'Orbitron';"><?= round($rate) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bi-card">
                    <p class="stat-label">High-Risk Residents Ledger</p>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover small m-0">
                            <thead><tr><th>MATRIC</th><th>NAME</th><th>INCIDENTS</th><th>DEBT</th></tr></thead>
                            <tbody>
                                <?php foreach($risky_students as $rs): ?>
                                <tr>
                                    <td class="text-info" style="font-family:'Orbitron'; font-size:0.75rem;"><?= htmlspecialchars($rs['matric_number']) ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($rs['name']) ?></td>
                                    <td class="text-center text-warning"><?= $rs['total_penalties'] ?></td>
                                    <td class="text-danger fw-bold">RM <?= number_format($rs['total_debt'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
// CHART GLOBAL CONFIG
Chart.defaults.color = '#888';
Chart.defaults.font.family = 'Rajdhani';

// 1. LINE CHART (System Load)
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach($daily_trends as $d) echo "'".date('D', strtotime($d['date']))."',"; ?>],
        datasets: [{ label: 'Tickets', data: [<?php foreach($daily_trends as $d) echo $d['count'].","; ?>], borderColor: '#00d4ff', tension: 0.4, fill: true, backgroundColor: 'rgba(0,212,255,0.1)', borderWidth: 2 }]
    },
    options: { plugins: { legend: false }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }, maintainAspectRatio: false }
});

// 2. BAR CHART (Staff Rejections)
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach($staff_perf as $s) echo "'".htmlspecialchars($s['name'])."',"; ?>],
        datasets: [{
            label: 'Rejections',
            data: [<?php foreach($staff_perf as $s) echo $s['total_rejections'].","; ?>],
            backgroundColor: 'rgba(255, 77, 77, 0.7)',
            borderColor: '#ff4d4d',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: { plugins: { legend: false }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }, maintainAspectRatio: false }
});

// 3. PIE CHART (Hotspots)
new Chart(document.getElementById('maintChart'), {
    type: 'pie',
    data: {
        labels: [<?php foreach($m_trends as $mt) echo "'".htmlspecialchars($mt['category_name'])."',"; ?>],
        datasets: [{ data: [<?php foreach($m_trends as $mt) echo $mt['count'].","; ?>], backgroundColor: ['#00d4ff', '#ff4d4d', '#f1c40f', '#9b59b6', '#2ecc71'], borderWidth: 0 }]
    },
    options: { plugins: { legend: { position: 'right', labels: { color: '#ccc', font: {size: 10} } } }, maintainAspectRatio: false }
});

// 4. DOUGHNUT CHART (Finances)
new Chart(document.getElementById('penaltyChart'), {
    type: 'doughnut',
    data: { 
        labels: ['Settled', 'Outstanding'], 
        datasets: [{ data: [<?= (int)$p_status['paid'] ?>, <?= (int)$p_status['unpaid'] ?>], backgroundColor: ['#2ecc71', '#ff4d4d'], borderWidth: 0 }] 
    },
    options: { plugins: { legend: { position: 'right', labels: { color: '#ccc', font: {size: 10} } } }, cutout: '70%', maintainAspectRatio: false }
});
</script>
</body>
</html>