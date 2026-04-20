<?php
$host = 'lesbot-db-server.mysql.database.azure.com';
$db   = 'lms_system';
$user = 'sufiana_admin';
$pass = 'Sufi123?!'; 
$charset = 'utf8mb4';

date_default_timezone_set('Asia/Kuala_Lumpur');

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          // This tells Azure: "I want a secure connection, but don't look for a local cert file"
          PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
          PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
     ]);
} catch (PDOException $e) {
     die("Neural Link Failed: " . $e->getMessage());
}
?>