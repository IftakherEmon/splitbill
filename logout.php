<?php
// =======================================================
// User Logout Handler: logout.php
// Clears all session variables and destroys the session.
// =======================================================

// 1. Initialize session so we have access to it
session_start();

// 2. Unset all session variables (e.g. user_id, user_name)
$_SESSION = array();

// 3. Destroy the session completely from the server
session_destroy();

// 4. Redirect user back to the login page with a clean state
header("Location: login.php");
exit();
?>
