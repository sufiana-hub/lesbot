<?php
session_start();
require_once 'db_config.php';

// Access Control
if ($_SESSION['role'] !== 'Admin') { exit("Unauthorized"); }

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="LesBot_Global_Report_'.date('Ymd').'.csv"');

$output = fopen('php://output', 'w');

// 1. Export Student List
fputcsv($output, ['--- STUDENT REGISTRY ---']);
fputcsv($output, ['Matric', 'Name', 'Email', 'Room']);
$students = $pdo->query("SELECT u.user_id, u.name, u.email, s.room_number FROM users u JOIN student s ON u.user_id = s.matric_number")->fetchAll();
foreach ($students as $row) { fputcsv($output, $row); }

fputcsv($output, []); // Spacer

// 2. Export Maintenance Log
fputcsv($output, ['--- MAINTENANCE LOG ---']);
fputcsv($output, ['ID', 'Student', 'Category', 'Status', 'Priority']);
$tasks = $pdo->query("SELECT mr.request_id, u.name, c.category_name, mr.status, mr.priority FROM maintenance_request mr JOIN users u ON mr.student_id = u.user_id JOIN category c ON mr.category_id = c.category_id")->fetchAll();
foreach ($tasks as $row) { fputcsv($output, $row); }

fclose($output);
exit();