<?php
session_start();

// Unset all session variables to effectively lose user's data
$_SESSION = array();

// Destroy the entire session on the server
session_destroy();

// Redirect back to login page
header("Location: ../public/login.php");
exit();
?>
