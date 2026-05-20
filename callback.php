<?php
// callback.php - Automated Settlement Verification
require_once 'db_config.php';

// ToyyibPay talks to this file in the background (POST)
if (isset($_POST['status_id']) && $_POST['status_id'] == 1) {
    $penalty_id = $_POST['order_id'];
    $transaction_id = $_POST['transaction_id'];

    try {
        $pdo->beginTransaction();
        
        // 1. SETTLE: Update is_paid
        $pdo->prepare("UPDATE student_penalties SET is_paid = 1 WHERE penalty_id = ?")
            ->execute([$penalty_id]);

        // 2. AUDIT: Log the transaction ID for the Admin
        $log = "FPX SUCCESS: Transaction $transaction_id completed. Ledger cleared.";
        $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES ('SYSTEM_FINANCE', 'FPX_SETTLEMENT', ?, ?)")
            ->execute([$penalty_id, $log]);

        $pdo->commit();
    } catch (Exception $e) { $pdo->rollBack(); }
}