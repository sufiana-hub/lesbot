<?php
$host = 'lesbot-db-server.mysql.database.azure.com';
$db   = 'lms_system';
$user = 'sufiana_admin';
$pass = 'Sufi123?!'; // Replace with your actual password
$charset = 'utf8mb4';

date_default_timezone_set('Asia/Kuala_Lumpur');

try {
     // The secret ingredient: PDO::MYSQL_ATTR_SSL_CA => true
     // This tells PHP to use a secure transport layer for the "Neural Link"
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::MYSQL_ATTR_SSL_CA => true, 
          PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
     ]);
} catch (PDOException $e) {
     die("Neural Link Failed: " . $e->getMessage());
}
?>