<?php
// hash_gen.php
// Choose a password for your admin (e.g., 'Admin@123')
$password = 'Admin@123'; 

// Generate the high-level hash
$new_hash = password_hash($password, PASSWORD_ARGON2ID);

echo "Copy this hash into your database: <br><br><b>" . $new_hash . "</b>";
?>