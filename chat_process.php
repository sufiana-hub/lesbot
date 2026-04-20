<?php
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php';

if(isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    $std_id = $_SESSION['std_id'] ?? 'GUEST';
    
    // 1. Get Gemini Response
    $ai_reply = getGeminiResponse($msg);

    // 2. CREATE: Record in upgraded neural tables (tbl_chat_log)
    try {
        if(!isset($_SESSION['chat_sid'])) {
            $stmtS = $pdo->prepare("INSERT INTO tbl_chat_session (std_id) VALUES (?)");
            $stmtS->execute([$std_id]);
            $_SESSION['chat_sid'] = $pdo->lastInsertId();
        }

        $stmtL = $pdo->prepare("INSERT INTO tbl_chat_log (session_id, user_message, bot_response) VALUES (?, ?, ?)");
        $stmtL->execute([$_SESSION['chat_sid'], $msg, $ai_reply]);
    } catch (Exception $e) {}

    echo $ai_reply;
}
?>