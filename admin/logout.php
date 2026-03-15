<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect to admin login page
header("Location: login.php");
exit;
?>