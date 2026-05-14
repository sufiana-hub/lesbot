<?php
session_start();

// 1. NEURAL DISCONNECTION
// Unset all session variables
$_SESSION = array();

// 2. DESTROY SESSION COOKIE
// This ensures the browser fully "forgets" the login
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. CORE DESTRUCTION
// Completely destroy the session on the server
session_destroy();

// 4. REDIRECTION
// Send the user back to the home page or login screen
header("Location: index.php");
exit();
?>