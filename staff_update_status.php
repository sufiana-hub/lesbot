<?php
session_start();
require_once 'db_config.php';

$request_id = $_GET['id'];
$new_status = $_GET['status']; // 'Completed', 'On Hold', or 'Rejected'

try {
    if ($new_status == 'Rejected') {
        // 1. Get current reject count
        $stmt = $pdo->prepare("SELECT rejected_count FROM maintenance_request WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $count = $stmt->fetchColumn();

        if ($count < 3) {
            // Re-assign back to Pending but keep the count
            $upd = $pdo->prepare("UPDATE maintenance_request SET status = 'Pending', rejected_count = rejected_count + 1 WHERE request_id = ?");
            $upd->execute([$request_id]);
            header("Location: staff_dashboard.php?msg=rejected");
        } else {
            // AT 3 REJECTIONS: Unassign the staff so someone else must take it
            $upd = $pdo->prepare("UPDATE maintenance_request SET assigned_staff_id = NULL, status = 'Pending' WHERE request_id = ?");
            $upd->execute([$request_id]);
            header("Location: staff_dashboard.php?msg=limit_reached");
        }
    } else {
        // Standard Update (Resolved or On Hold)
        $upd = $pdo->prepare("UPDATE maintenance_request SET status = ? WHERE request_id = ?");
        $upd->execute([$new_status, $request_id]);
        header("Location: staff_dashboard.php?msg=updated");
    }
} catch (Exception $e) { die($e->getMessage()); }