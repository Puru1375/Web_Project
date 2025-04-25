<?php
// api/logout.php
session_start(); // Access the session
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

header('Location: ../index.php'); // Redirect back to the login page (index.php is one level up)
exit();
?>