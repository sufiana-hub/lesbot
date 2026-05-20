<?php
/**
 * LESBOT NEURAL CALLBACK
 * SECURE SERVER-TO-SERVER SETTLEMENT VERIFICATION
 */
require_once 'db_config.php';

// ToyyibPay sends data via POST in the background
if (isset($_POST['status_id'])) {
    $penalty_id = $_POST['order_id']; // This is our database ID
    $status_id  = $_POST['status_id']; // 1 = Success, 3 = Failed
    $ref_no     = $_POST['transaction_id']; // The real Bank reference

    if ($status_id == 1) {
        try {
            $pdo->beginTransaction();

            // 1. UPDATE LEDGER: Finalize the payment status
            $upd = $pdo->prepare("UPDATE student_penalties SET is_paid = 1 WHERE penalty_id = ?");
            $upd->execute([$penalty_id]);

            // 2. AUDIT TRAIL: Record the real-world transaction details
            $details = "FPX SETTLEMENT SUCCESS: Real-world transfer verified. Ref: $ref_no";
            $stmt = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES ('SYSTEM_HUB', 'PAYMENT_VERIFIED', ?, ?)");
            $stmt->execute([$penalty_id, $details]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}