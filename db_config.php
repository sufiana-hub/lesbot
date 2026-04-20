<?php
$host = 'lesbot-db-server.mysql.database.azure.com';
$db   = 'lms_system';
$user = 'sufiana_admin';
$pass = 'Sufi123?!'; // Use your real password here
$charset = 'utf8mb4';

date_default_timezone_set('Asia/Kuala_Lumpur');

try {
    // Azure requires specific flags for SSL to work over the internet
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);
} catch (PDOException $e) {
    // If this fails, it will tell us exactly why
    die("Neural Link Failed: " . $e->getMessage());
}
?>