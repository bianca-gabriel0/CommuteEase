<?php
// ALWAYS start the session to be able to access it
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy the session cookie by setting its expiration time in the past
// This ensures the browser immediately deletes the session ID cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session on the server
session_destroy();
// 

// 4. Redirect the user to the login page (corrected filename) with a confirmation message
header("Location: login.php?message=logged_out");
exit;
?>
