<?php
// watchdog.php - Automated SLA Enforcement Protocol

/**
 * Scans the database for expired 'On-Hold' requests and reassigns them.
 * Type hint 'PDO' fixes the P1132 warning.
 */
function runNeuralWatchdog(PDO $pdo): void {
    // 1. Identify requests that have been 'On-Hold' for more than 72 hours (3 days)
    $stmt = $pdo->query("SELECT request_id, assigned_staff_id FROM maintenance_request 
                         WHERE status = 'On-Hold' 
                         AND hold_timestamp <= NOW() - INTERVAL 3 DAY");
    $expired = $stmt->fetchAll();

    foreach ($expired as $task) {
        $req_id = $task['request_id'];
        $old_staff = $task['assigned_staff_id'];

        // 2. AUTO-REASSIGN LOGIC: Find a NEW staff member with the lightest workload
        $new_staff_stmt = $pdo->prepare("SELECT s.staff_id FROM staff s 
                                         WHERE s.department = 'Maintenance' 
                                         AND s.staff_id != ?
                                         ORDER BY (SELECT COUNT(*) FROM maintenance_request WHERE assigned_staff_id = s.staff_id AND status != 'Completed') ASC 
                                         LIMIT 1");
        $new_staff_stmt->execute([$old_staff]);
        $new_staff = $new_staff_stmt->fetchColumn();

        if ($new_staff) {
            // 3. UPDATE: Snatch the report and reset the timer
            $upd = $pdo->prepare("UPDATE maintenance_request 
                                  SET assigned_staff_id = ?, 
                                      status = 'In Progress', 
                                      hold_timestamp = NULL 
                                  WHERE request_id = ?");
            $upd->execute([$new_staff, $req_id]);
            
            // Optional: Log this reassignment in your audit trail
            $log_details = "SLA BREACH: Request #$req_id taken from Staff #$old_staff due to 72h timeout. Reassigned to Staff #$new_staff.";
            $audit = $pdo->prepare("INSERT INTO system_audit_trail (admin_id, action_type, target_entity, action_details) VALUES ('SYSTEM', 'AUTO_REASSIGN', ?, ?)");
            $audit->execute([$req_id, $log_details]);
        }
    }
}