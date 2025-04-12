<?php
// api/register_handler.php
require_once 'db_connect.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['username'], $input['email'], $input['password']) &&
        !empty(trim($input['username'])) && !empty(trim($input['email'])) && !empty($input['password']))
    {
        $username = trim($input['username']);
        $email = trim($input['email']);
        $password = $input['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } elseif (strlen($password) < 6) {
            $response['message'] = 'Password must be at least 6 characters long.';
        } else {
            // Check if username or email already exists
            $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
            if($stmt_check = mysqli_prepare($conn, $sql_check)){
                mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
                if(mysqli_