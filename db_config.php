<?php
// --- DBA DYNAMIC ENVIRONMENT DETECTION ---
// Identify if running locally or on the cloud host
$is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

if ($is_localhost) {
    // 1. LOCALROOT SETTINGS (XAMPP)
    $host = '127.0.0.1';
    $port = '3306'; // Standard XAMPP port
    $db   = 'lms_system';
    $user = 'root';
    $pass = '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
} else {
    // 2. PANTHEON CLOUD SETTINGS (New Production Home)
    $host = 'dbserver.dev.572c262a-f6d3-4e1b-9b3b-0dac7b22bd24.drush.in';
    $port = '11004'; // Pantheon specialized port
    $db   = 'pantheon';
    $user = 'pantheon';
    $pass = 'HZp1rEdNRSAq4R26lPFkFZ0zMJIJUtuM'; // Paste your FULL password here
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
}

try {
    // Establishing the Connection with the Port included
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);

    // Synchronize Database Timezone with Malaysia
    $pdo->exec("SET time_zone = '+08:00'");

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>