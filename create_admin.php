<?php
require_once 'db_config.php';

// New Admin Details
$user_id  = 'ADMIN_PRESENT';
$name     = 'Presenter Admin';
$email    = 'present@lestari.com';
$password = 'admin123'; // THIS WILL BE YOUR PASSWORD
$role     = 'Admin';

// Hash the password exactly how your system expects
$hashed_password = password_hash($password, PASSWORD_ARGON2ID);

try {
    $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $email, $hashed_password, $role]);
    echo "SUCCESS: Admin 'ADMIN_PRESENT' created with password 'admin123'";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>