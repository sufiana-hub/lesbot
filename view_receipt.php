<?php
session_start();
require_once 'db_config.php';

// Get status from the Bank Hub Redirect
$status = $_GET['status_id'] ?? 3; // 1=Success, 3=Fail
$penalty_id = $_GET['order_id'] ?? '';

if ($status == 1) {
    // Display your sleek Neural Receipt design here
    echo "<h1>TRANSACTION VERIFIED</h1>";
    echo "<p>Your penalty #$penalty_id has been cleared from the ledger.</p>";
} else {
    echo "<h1>TRANSACTION ABORTED</h1>";
    echo "<p>Please try again or contact the Fellow Office.</p>";
}
?>

<a href="student_penalties.php">Return to Hub</a>