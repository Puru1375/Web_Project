<?php
// api/logout_handler.php

// **IMPORTANT: Start session at the very beginning**
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Send a success response back to JavaScript
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
exit();
?>