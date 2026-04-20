<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['std_id'])) { exit(); }
$id = $_SESSION['std_id'];

// 1. Fetch Audit Data
$stmt = $pdo->prepare("SELECT semester_session, room_number, move_in_date FROM student_room_history WHERE matric_number = ? ORDER BY move_in_date DESC");
$stmt->execute([$id]);
$records = $stmt->fetchAll();

// 2. Configure CSV Stream
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Audit_Trail_Lestari_'.$id.'.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['SEMESTER SESSION', 'ROOM NUMBER', 'MOVE IN DATE']);

// 3. Populate Rows
foreach ($records as $row) {
    fputcsv($output, [
        $row['semester_session'], 
        $row['room_number'], 
        date('d/m/Y', strtotime($row['move_in_date']))
    ]);
}
fclose($output);
exit();
?>