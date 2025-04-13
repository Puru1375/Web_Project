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
                if(mysqli_stmt_execute($stmt_check)){
                    mysqli_stmt_store_result($stmt_check);
                    if(mysqli_stmt_num_rows($stmt_check) > 0){
                        $response['message'] = 'Username or email already taken.';
                    } else {
                        // Hash the password
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        // Prepare insert statement
                        $sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
                        if($stmt_insert = mysqli_prepare($conn, $sql_insert)){
                            mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $password_hash);
                            if(mysqli_stmt_execute($stmt_insert)){
                                $response['success'] = true;
                                $response['message'] = 'Registration successful! You can now login.';
                            } else { $response['message'] = 'Registration failed. Please try again later.'; }
                            mysqli_stmt_close($stmt_insert);
                        } else { $response['message'] = 'Database error (prepare insert).'; }
                    }
                } else { $response['message'] = 'Database error (execute check).'; }
                mysqli_stmt_close($stmt_check);
            } else { $response['message'] = 'Database error (prepare check).'; }
        }
    } else { $response['message'] = 'Please fill in all required fields.'; }
} else { $response['message'] = 'Invalid request method.'; http_response_code(405); }

mysqli_close($conn);
echo json_encode($response);
exit();
?>