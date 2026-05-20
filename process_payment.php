<?php
session_start();
require_once 'db_config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $penalty_id = $_POST['penalty_id'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $matric = $_SESSION['std_id'];
    $payment_id = "PAY-" . strtoupper(substr(md5(time()), 0, 8));
    $ref_no = "UTEM-" . time();
    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE student_penalties SET is_paid = 1 WHERE penalty_id = ?")->execute([$penalty_id]);
        $pdo->prepare("INSERT INTO student_payments (payment_id, penalty_id, matric_number, payment_method, amount_paid, transaction_ref) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$payment_id, $penalty_id, $matric, $method, $amount, $ref_no]);
        $log_details = "PAYMENT SUCCESS: RM $amount via $method. ID: $payment_id";
        $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES (?, 'PAYMENT_COMPLETED', ?, ?)")
            ->execute([$matric, $payment_id, $log_details]);
        $pdo->commit();
        header("Location: view_receipt.php?id=" . $payment_id);
    } catch (Exception $e) { $pdo->rollBack(); die($e->getMessage()); }
}