<?php
session_start();
require_once 'db_config.php';
require_once 'ai_logic.php';

// Detect the actual user from the session
$id   = $_SESSION['std_id'] ?? 'GUEST_ENTITY';
$role = $_SESSION['role'] ?? 'Visitor'; 
$name = $_SESSION['full_name'] ?? 'Unknown User';

if (isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    
    // Pass the real Role and Name to the AI logic
    $ai_reply = getLesBotResponse($msg, $role, $name);
    
    echo $ai_reply;
}