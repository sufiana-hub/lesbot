<?php
$host = 'localhost';
$db   = 'lms_system'; // Point to original database
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';
// Change the space to an underscore
date_default_timezone_set('Asia/Kuala_Lumpur');

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ]);
} catch (PDOException $e) {
     die("Neural Link Failed: " . $e->getMessage());
}
?>