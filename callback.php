<?php
// callback.php - Secret Background Listener
require_once 'db_config.php';

// Data sent from the Real Hub via POST
if (isset($_POST['status_id']) && $_POST['status_id'] == 1) {
    $penalty_id = $_POST['order_id'];
    $transaction_id = $_POST['transaction_id']; // Real FPX Ref No

    try {
        $pdo->beginTransaction();
        
        // 1. UPDATE LEDGER: Finalize payment status
        $pdo->prepare("UPDATE student_penalties SET is_paid = 1 WHERE penalty_id = ?")->execute([$penalty_id]);

        // 2. AUDIT LOG: Record the real-world transaction
        $details = "FPX SETTLEMENT SUCCESS: Real-world transfer verified. Ref: $transaction_id";
        $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES ('SYSTEM_HUB', 'PAYMENT_VERIFIED', ?, ?)")
            ->execute([$penalty_id, $details]);

        $pdo->commit();
    } catch (Exception $e) { $pdo->rollBack(); }
}