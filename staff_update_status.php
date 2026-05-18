<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { exit(); }

$request_id = $_GET['id'];
$new_status = $_GET['status']; 

try {
    // 1. IMMUTABILITY CHECK: Once solved, it stays solved.
    $check = $pdo->prepare("SELECT status FROM maintenance_request WHERE request_id = ?");
    $check->execute([$request_id]);
    $current = $check->fetchColumn();

    if ($current === 'Completed') {
        die("DBA RESTRICTION: This case is already closed and cannot be modified.");
    }

    if ($new_status == 'Rejected') {
        // Handle the 3-rejection logic
        $stmt = $pdo->prepare("SELECT rejected_count FROM maintenance_request WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $count = $stmt->fetchColumn();

        if ($count < 2) { 
            $upd = $pdo->prepare("UPDATE maintenance_request SET status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
        } else {
            $upd = $pdo->prepare("UPDATE maintenance_request SET assigned_staff_id = NULL, status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
        }
        $upd->execute([$request_id]);
    } else {
        // 2. HOLD TIMESTAMP LOGIC
        // If status is On-Hold, set the timestamp to NOW(). If Resolve, set to NULL.
        $sql = "UPDATE maintenance_request SET status = ?, hold_timestamp = " . ($new_status == 'On-Hold' ? "NOW()" : "NULL") . " WHERE request_id = ?";
        $upd = $pdo->prepare($sql);
        $upd->execute([$new_status, $request_id]);
    }

    header("Location: staff_tasks.php?msg=status_updated");
    exit();

} catch (Exception $e) { die("Transmission Failure: " . $e->getMessage()); }