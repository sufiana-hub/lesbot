<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { exit(); }

// 1. Fetch only Deletion (Purge) records for the analyst report
$query = "SELECT created_at, admin_id, target_entity, action_details 
          FROM system_audit_trail 
          WHERE action_type = 'ENTITY_PURGE' 
          ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set headers for browser download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Neural_Purge_Archive_'.date('Ymd').'.csv');

// 3. Create the CSV stream
$output = fopen('php://output', 'w');

// 4. Add Excel Headers
fputcsv($output, array('Timestamp', 'Admin ID', 'Purged Entity ID', 'Full Action Detail Trace'));

// 5. Fill with data rows
foreach ($logs as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>