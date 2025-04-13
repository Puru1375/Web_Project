<?php
// api/check_session.php

// **IMPORTANT: Start session at the very beginning**
session_start();

header('Content-Type: application/json');

$response = ['loggedIn' => false]; // Default response: not logged in

// Check if the session variables we set during login exist
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $response['loggedIn'] = true;
    // Optionally send back username if needed on the frontend
    if (isset($_SESSION['username'])) {
        $response['username'] = $_SESSION['username'];
    }
}

echo json_encode($response);
exit();
?>