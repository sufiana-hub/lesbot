<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['std_id'])) { header("Location: login.php"); exit(); }

// CRUD: READ - Fetch all tickets for the logged-in student
$query = "SELECT * FROM tbl_maintenance WHERE std_id = ? ORDER BY date_created DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['std_id']]);
?>