<?php



session_start();

require_once 'db_connect.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

  
    if (isset($input['email'], $input['password']) && !empty(trim($input['email'])) && !empty($input['password']))
    {
        $login_identifier = trim($input['email']); // Can be email or username
        $password = $input['password'];

        // Prepare select statement to find user by email OR username
        $sql = "SELECT id, username, password_hash FROM users WHERE email = ? OR username = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $login_identifier, $login_identifier);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Check if user exists
                if(mysqli_stmt_num_rows($stmt) == 1){
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $user_id, $username, $stored_hash);
                    if(mysqli_stmt_fetch($stmt)){
                        // Verify the password against the stored hash
                        if(password_verify($password, $stored_hash)){
                            // Password is correct!

                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            // Store user information in session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $username;

                            $response['success'] = true;
                            $response['message'] = 'Login successful!';
                            $response['username'] = $username; // Send username back to frontend if needed
                        } else {
                            // Incorrect password
                            $response['message'] = 'Invalid username/email or password.';
                        }
                    }
                } else {
                    // User not found
                    $response['message'] = 'Invalid username/email or password.';
                }
            } else { $response['message'] = 'Database error (execute select).'; }
            mysqli_stmt_close($stmt);
        } else { $response['message'] = 'Database error (prepare select).'; }
    } else { $response['message'] = 'Please enter both identifier (email/username) and password.'; }
} else { $response['message'] = 'Invalid request method.'; http_response_code(405); }

mysqli_close($conn);
echo json_encode($response);
exit();
?>