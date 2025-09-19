<?php
// Include the bootstrap file
require_once 'includes/bootstrap.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Log the logout action
    logAction($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Clear the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Use JavaScript redirect instead of PHP header redirect
echo '<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <meta http-equiv="refresh" content="0;url=login.php">
</head>
<body>
    <p>Logging out... <a href="login.php">Click here if you are not redirected</a></p>
    <script>
        window.location.href = "login.php";
    </script>
</body>
</html>';
exit();
?>