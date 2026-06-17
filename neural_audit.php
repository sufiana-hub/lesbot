<?php
require_once 'db_config.php';

// If Postman sends anything to this file, we record a test entry and say SUCCESS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $request_id = "AUDIT-" . time();
        $sql = "INSERT INTO maintenance_request (request_id, student_id, category_id, description, priority, status) 
                VALUES (?, 'B032410816', 1, 'POSTMAN EXTERNAL AUDIT', 'Low', 'Completed')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$request_id]);

        header('Content-Type: text/plain');
        echo "NEURAL_LINK_ESTABLISHED";
        exit();
    } catch (Exception $e) {
        echo "DB_ERROR";
        exit();
    }
} else {
    echo "READY_FOR_AUDIT";
}