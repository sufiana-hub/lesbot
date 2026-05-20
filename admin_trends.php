<?php
/**
 * LESBOT NEURAL BI COMMAND CENTER
 * AI ANALYTICS + DATA WAREHOUSING + VISUAL BI
 */
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: login.php"); exit(); 
}

try {
    // 1. DATA WAREHOUSE: MAINTENANCE CLASSIFICATION TRENDS
    $m_trends = $pdo->query("SELECT c.category_name, COUNT(*) as count 
                             FROM maintenance_request mr 
                             JOIN category c ON mr.category_id = c.category_id 
                             GROUP BY c.category_name")->fetchAll(PDO::FETCH_ASSOC);

    // 2. DATA WAREHOUSE: PENALTY STATUS (Paid vs Unpaid)
    $p_status = $pdo->query("SELECT 
                             SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid,
                             SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as unpaid
                             FROM student_penalties")->fetch(PDO::FETCH_ASSOC);

    // 3. STAFF PERFORMANCE SCORECARD
    $staff_perf = $pdo->query("SELECT u.name, 
                               COUNT(mr.request_id) as total,
                               SUM(CASE WHEN mr.status = 'Completed' THEN 1 ELSE 0 END) as resolved,
                               SUM(CASE WHEN mr.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                               FROM staff st
                               JOIN users u ON st.staff_id = u.user_id
                               LEFT JOIN maintenance_request mr ON st.staff_id = mr.assigned_staff_id
                               GROUP BY st.staff_id, u.name")->fetchAll(PDO::FETCH_ASSOC);

    // 4. STUDENT DEMOGRAPHICS
    $demographics = $pdo->query("SELECT 
                                 SUM(CASE WHEN room_number LIKE 'A%' THEN 1 ELSE 0 END) as male,
                                 SUM(CASE WHEN room_number LIKE 'B%' THEN 1 ELSE 0 END) as female
                                 FROM student")->fetch(PDO::FETCH_ASSOC);

    // 5. AI STRATEGIC ENGINE: Llama 3.3 Analytics
    $summary_for_ai = json_encode(['maintenance' => $m_trends, 'penalties' => $p_status, 'staff' => $staff_perf]);
    $ai_prompt = "Analyze these dormitory operational metrics: $summary_for_ai. 
                  1. Identify high-risk trends in maintenance or discipline.
                  2. Suggest specific 'Consequences' for underperforming staff.
                  3. Generate a solution to reduce the top maintenance issue.
                  Tone: Authoritative, BI-Expert, Futuristic.";
    $ai_insights = getLesBotResponse($ai_prompt, "Admin", $_SESSION['full_name']);

} catch (PDOException $e) { die("BI Engine Failure: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LesBot | BI Command</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- CHART.JS FOR BI VISUALS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --lesbot-cyan: #00d4ff; --obsidian: #080a0f; --neon-border: rgba(0, 212, 255, 0.2); }
        body { background-color: var(--obsidian); color: #fff; font-family: 'Rajdhani', sans-serif; padding-top: 100px; }
        .neural-nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(8, 10, 15, 0.9); backdrop-filter: blur(15px); border: 1px solid var(--neon-border); border-radius: 50px; padding: 10px 35px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .bi-container { background: rgba(255, 255, 255, 0.01); border: 1px solid var(--neon-border); border-radius: 40px; padding: 40px; backdrop-filter: blur(20px); }
        .chart-card { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 25px; padding: 25px; height: 100%; }
        .ai-strategic-card { background: linear-gradient(135deg, rgba(0, 212, 255, 0.15), rgba(188, 19, 254, 0.05)); border: 1px solid var(--lesbot-cyan); border-radius: 25px; padding: 30px; margin-bottom: 30px; box-shadow: 0 0 40px rgba(0, 212, 255, 0.1); }
        .label-neon { font-family: 'Orbitron'; font-size: 0.65rem; color: var(--lesbot-cyan); letter-spacing: 2px; }
    </style>
</head>
<body>

<nav class="neural-nav">
    <a href="admin_dashboard.php" style="color:var(--lesbot-cyan); font-family:'Orbitron'; font-weight:900; text-decoration:none;">LESBOT •</a>
    <div class="d-flex gap-4">
        <a href="admin_dashboard.php" style="color:white; text-decoration:none; font-size:0.7rem; font-family:'Orbitron';">DASHBOARD</a>
        <a href="admin_trends.php" style="color:var(--lesbot-cyan); text-decoration:none; font-size:0.7rem; font-family:'Orbitron'; font-weight:900;">BI TRENDS</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="bi-container">
        
        <!-- 1. AI BI ANALYSIS -->
        <div class="ai-strategic-card">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-robot fs-3 text-info me-3"></i>
                <h5 class="m-0 font-orbitron" style="letter-spacing: 3px;">NEURAL STRATEGIC ANALYST (LLAMA 3.3)</h5>
            </div>
            <div class="small" style="line-height: 1.8; color: #e0f7ff;">
                <?= nl2br($ai_insights) ?>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- PIE CHART: GENDER SPLIT -->
            <div class="col-md-4">
                <div class="chart-card">
                    <p class="label-neon text-center mb-4">STUDENT POPULATION</p>
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
            <!-- PIE CHART: PENALTY SETTLEMENT -->
            <div class="col-md-4">
                <div class="chart-card">
                    <p class="label-neon text-center mb-4">LEDGER SETTLEMENT %</p>
                    <canvas id="penaltyChart"></canvas>
                </div>
            </div>
            <!-- BAR CHART: MAINTENANCE CATEGORIES -->
            <div class="col-md-4">
                <div class="chart-card">
                    <p class="label-neon text-center mb-4">MAINTENANCE HOTSPOTS</p>
                    <canvas id="maintChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. STAFF PERFORMANCE BI GRID -->
        <div class="chart-card mb-4">
            <p class="label-neon mb-4">STAFF EFFICIENCY PERFORMANCE INDEX</p>
            <table class="table table-dark table-hover align-middle small">
                <thead><tr class="font-orbitron opacity-50"><th>TECHNICIAN</th><th>TOTAL</th><th>RESOLVED</th><th>REJECTED</th><th>RATING</th></tr></thead>
                <tbody>
                    <?php foreach($staff_perf as $s): 
                        $rate = ($s['total'] > 0) ? ($s['resolved'] / $s['total']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $s['name'] ?></td>
                        <td><?= $s['total'] ?></td>
                        <td class="text-success"><?= $s['resolved'] ?></td>
                        <td class="text-danger"><?= $s['rejected'] ?></td>
                        <td class="font-orbitron <?= $rate < 50 ? 'text-danger' : 'text-info' ?>"><?= round($rate) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
// BI VISUALIZATION ENGINE
const ctxG = document.getElementById('genderChart');
new Chart(ctxG, {
    type: 'pie',
    data: {
        labels: ['MALE', 'FEMALE'],
        datasets: [{
            data: [<?= $demographics['male'] ?>, <?= $demographics['female'] ?>],
            backgroundColor: ['#00d4ff', '#ff69b4'],
            borderWidth: 0
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { color: '#fff', font: { family: 'Orbitron', size: 10 } } } } }
});

const ctxP = document.getElementById('penaltyChart');
new Chart(ctxP, {
    type: 'doughnut',
    data: {
        labels: ['PAID', 'UNPAID'],
        datasets: [{
            data: [<?= $p_status['paid'] ?>, <?= $p_status['unpaid'] ?>],
            backgroundColor: ['#2ecc71', '#ff4d4d'],
            borderWidth: 0
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { color: '#fff', font: { family: 'Orbitron', size: 10 } } } } }
});

const ctxM = document.getElementById('maintChart');
new Chart(ctxM, {
    type: 'polarArea',
    data: {
        labels: [<?php foreach($m_trends as $mt) echo "'".$mt['category_name']."',"; ?>],
        datasets: [{
            data: [<?php foreach($m_trends as $mt) echo $mt['count'].","; ?>],
            backgroundColor: ['#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#1abc9c'],
            borderWidth: 0
        }]
    },
    options: { 
        scales: { r: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { display: false } } },
        plugins: { legend: { display: false } } 
    }
});
</script>

<?php include 'chatbot_component.php'; ?>
</body>
</html>