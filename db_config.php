<?php
$host = 'lesbot-db-server.mysql.database.azure.com';
$db   = 'lms_system'; 
$user = 'sufiana_admin';
$pass = 'Sufi123?!'; // Replace this with the password you typed in Cloud Shell
$charset = 'utf8mb4';

date_default_timezone_set('Asia/Kuala_Lumpur');

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::MYSQL_ATTR_SSL_CA => true, // Azure requires SSL for secure connection
     ]);
} catch (PDOException $e) {
     die("Neural Link Failed: " . $e->getMessage());
}
?>