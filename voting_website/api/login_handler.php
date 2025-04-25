<?php
// api/login_handler.php
require 'db_connect.php'; // Includes session_start()

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Only verify for POST requests usually
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedToken)) {
        // Token is invalid or missing, reject the request
        header('Content-Type: application/json'); // Ensure JSON header
        http_response_code(403); // Forbidden status code
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit; // Stop script execution
    }
}
// --- End CSRF Verification ---


// If verification passed, continue with the rest of the script...
checkLogin(); // Ensure user is logged in (if required for this action)


header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['login_identifier'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $response['message'] = 'Username/Email and Password are required.';
    } else {
        try {
            // Check if the identifier is an email or username
            $sql = "SELECT id, username, password_hash, is_verified FROM users WHERE username = ? OR email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if (!$user) {
                $response['message'] = 'Invalid credentials or user not found.';
            } elseif ($user['is_verified'] == 0) {
                 $response['message'] = 'Account not verified. Please check your email for OTP or register again.';
                 // Optionally redirect to OTP verification page if you store the target email
                 // $response['redirect_otp'] = true;
                 // $response['user_email'] = $user['email']; // Assuming you fetch email
            } elseif (password_verify($password, $user['password_hash'])) {
                // Password is correct and user is verified, log them in
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $response['success'] = true;
                $response['message'] = 'Login successful!';
                // No need to send redirect URL in JSON, JS will handle it based on success
            } else {
                $response['message'] = 'Invalid credentials.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $response['message'] = 'An error occurred during login.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>