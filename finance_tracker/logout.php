<?php
// logout.php
session_start(); // Start the session
$_SESSION = array(); // Unset all of the session variables (optional but good practice)
session_destroy(); // Destroy the session.

// Redirect to the login page (which serves as your sign in/up page)
header("Location: login.php");
exit; // It's crucial to call exit() after a header redirect
?>