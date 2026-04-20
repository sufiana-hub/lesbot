<?php
$host = 'lesbot-db-server.mysql.database.azure.com'; // Your Endpoint
$db   = 'lms_system'; 
$user = 'sufiana_admin'; // Your Admin Login
$pass = 'Sufi123?!'; // The one you typed in Cloud Shell
$charset = 'utf8mb4';

date_default_timezone_set('Asia/Kuala_Lumpur');

try {
     // For Azure, we MUST add the host and use the proper credentials
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ]);
} catch (PDOException $e) {
     die("Neural Link Failed: " . $e->getMessage());
}
?>