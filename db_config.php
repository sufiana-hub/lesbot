<?php
// --- DBA DYNAMIC ENVIRONMENT DETECTION ---
// Identify if running locally or on the Azure web host
$is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

if ($is_localhost) {
    // 1. LOCALROOT SETTINGS
    $host = '127.0.0.1';
    $db   = 'lms_system';
    $user = 'root';
    $pass = ''; 
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
} else {
    // 2. AZURE CLOUD SETTINGS (Production Alignment)
    $host = 'lesbot-db-server.mysql.database.azure.com';
    $db   = 'lms_system';
    $user = 'sufiana_admin';
    $pass = 'Sufi123?!'; 
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => true, // Mandated for Azure connectivity
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
}

try {
    // Establishing the Neural Link
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    
    // CRITICAL ALIGNMENT: Synchronize Database Timezone with Malaysia
    // This ensures 'NOW()' in SQL matches PHP's date_default_timezone_set
    $pdo->exec("SET time_zone = '+08:00';");

} catch (PDOException $e) {
    die("Neural Link Critical Failure: " . $e->getMessage());
}

// Ensure PHP processing also stays on Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');
?>