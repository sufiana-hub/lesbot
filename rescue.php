<?php
require_once 'db_config.php';

// 1. Define the plain password
$plain = 'Admin@123';
// 2. Create the high-level hash
$hash = password_hash($plain, PASSWORD_ARGON2ID);

try {
    // 3. Force the update into the database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = 'AD001'");
    $stmt->execute([$hash]);
    
    echo "<h1>MISSION ACCOMPLISHED</h1>";
    echo "Admin AD001 has been reset.<br>";
    echo "New Password: <b>Admin@123</b><br><br>";
    echo "<b>PLEASE DELETE THIS FILE (rescue.php) IMMEDIATELY FOR SECURITY!</b>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>