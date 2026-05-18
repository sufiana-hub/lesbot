<?php
session_start();
require_once 'db_config.php';

// Verify access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') { exit("Unauthorized"); }

$request_id = $_GET['id'];
$new_status = $_GET['status']; // 'Completed', 'On Hold', or 'Rejected'

try {
    if ($new_status == 'Rejected') {
        // 1. Get current count
        $stmt = $pdo->prepare("SELECT rejected_count FROM maintenance_request WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $count = $stmt->fetchColumn();

        if ($count < 2) { // 0, 1, 2 = 3 tries total
            $upd = $pdo->prepare("UPDATE maintenance_request SET status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
            $upd->execute([$request_id]);
        } else {
            // REACHED 3: Unassign the staff so someone else must handle the case
            $upd = $pdo->prepare("UPDATE maintenance_request SET assigned_staff_id = NULL, status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
            $upd->execute([$request_id]);
        }
    } else {
        // Standard Update (Resolved or On Hold)
        $upd = $pdo->prepare("UPDATE maintenance_request SET status = ? WHERE request_id = ?");
        $upd->execute([$new_status, $request_id]);
    }

    header("Location: staff_tasks.php?msg=updated");
    exit();

} catch (Exception $e) {
    die("Transmission Failure: " . $e->getMessage());
}