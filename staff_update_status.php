<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { exit(); }

$request_id = $_GET['id'];
$new_status = $_GET['status']; // 'Completed', 'On-Hold', or 'Rejected'

try {
    // DBA Security Check: Has this already been resolved?
    $check = $pdo->prepare("SELECT status FROM maintenance_request WHERE request_id = ?");
    $check->execute([$request_id]);
    $current_status = $check->fetchColumn();

    if ($current_status === 'Completed' || $current_status === 'Rejected') {
        die("DBA RESTRICTION: Immutable Record. Status cannot be changed once Resolved or Rejected.");
    }

    if ($new_status == 'Rejected') {
        $stmt = $pdo->prepare("SELECT rejected_count FROM maintenance_request WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $count = $stmt->fetchColumn();

        if ($count < 2) { 
            $upd = $pdo->prepare("UPDATE maintenance_request SET status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
        } else {
            // Unassign on 3rd reject
            $upd = $pdo->prepare("UPDATE maintenance_request SET assigned_staff_id = NULL, status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
        }
        $upd->execute([$request_id]);
    } else {
        // Update to 'Completed' or 'On-Hold' (Hyphenated to fix SQL error)
        $upd = $pdo->prepare("UPDATE maintenance_request SET status = ? WHERE request_id = ?");
        $upd->execute([$new_status, $request_id]);
    }

    header("Location: staff_tasks.php?msg=success");
    exit();

} catch (Exception $e) { die("Transmission Failure: " . $e->getMessage()); }