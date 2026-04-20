<?php
session_start();
require_once 'db_config.php';
if ($_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }

// UPDATE: Handle Soft Delete (Toggles status between active/inactive)
if (isset($_GET['toggle_id'])) {
    $current_status = $_GET['status'];
    $new_status = ($current_status == 'active') ? 'inactive' : 'active';
    
    $stmt = $pdo->prepare("UPDATE account SET status = ? WHERE account_id = ?");
    $stmt->execute([$new_status, $_GET['toggle_id']]);
    header("Location: manage_accounts.php");
}

$users = $pdo->query("SELECT a.account_id, a.status, s.name FROM account a LEFT JOIN student s ON a.account_id = s.matric_number WHERE a.account_type = 'student'")->fetchAll();
?>

<div class="main-content">
    <h2 style="font-family: Orbitron;">Account <span style="color: var(--primary-glow);">Registry</span></h2>
    <div class="glass-card mt-4">
        <table class="table table-dark">
            <thead>
                <tr><th>MATRIX</th><th>NAME</th><th>STATUS</th><th>ACTION</th></tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td><?php echo $s['matrix_no']; ?></td>
                    <td><?php echo $s['full_name']; ?></td>
                    <td><?php echo $s['status'] ?? 'Active'; ?></td>
                    <td>
                        <a href="manage_accounts.php?deactivate=<?php echo $s['std_id']; ?>" class="text-danger">DEACTIVATE</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>