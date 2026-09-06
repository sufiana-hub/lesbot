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
        // HEROKU / JAWSDB CLOUD SETTINGS
        $host = 'kavfu5f7pido12mr.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
        $db   = 'j249j0ae1u6sisye';
        $user = 'mhc8p4pni9w39nj3';
        $pass = 'u42brr4u3s1bg1mm';
        $port = '3306'; // JawsDB uses the standard MySQL port
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