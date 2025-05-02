<?php
// api/register_handler.php
require 'db_connect.php';
require 'helpers.php'; // Include helpers for OTP generation


// --- CSRF Token Verification ---
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

 // Ensure user is logged in (if required for this action)



header('Content-Type: application/json'); // Set content type to JSON

$response = ['success' => false, 'message' => '', 'otp_sent' => false, 'user_email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic Validation
    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if username or email already exists
            $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $response['message'] = 'Username or Email already exists.';
            } else {
                // Hash the password (keep as before)
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert user (unverified initially)
                $sql = "INSERT INTO users (username, email, password_hash, is_verified) VALUES (?, ?, ?, 0)";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$username, $email, $password_hash])) {
                    $userId = $pdo->lastInsertId();

                    // --- Generate and attempt to SEND OTP Email ---
                    $emailSent = generateAndStoreOTP($pdo, $userId, $email, $username);
                    // --- --- --- --- --- --- --- --- --- --- --- ---

                    if ($emailSent) {
                        $response['success'] = true;
                        $response['message'] = 'Registration successful! Please check your email (' . $email . ') for the OTP to verify your account.';
                        $response['otp_sent'] = true; // Flag to redirect to OTP page
                        $response['user_email'] = $email; // Send email back to JS for OTP page
                    } else {
                         // Email failed to send. User is in DB but unverified.
                         // Don't set success = true. Message should indicate the issue.
                         $response['success'] = false; // Keep success false
                         $response['message'] = 'Registration completed, but we failed to send the verification OTP email. Please contact support or try registering again later.';
                         // Optionally: Could delete the user record here, or leave it for manual verification/retry.
                         // For simplicity, we leave the user record.
                         error_log("Registration OK, but OTP email failed for user: $username ($email)");
                    }

                } else {
                    $response['message'] = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            // Log the error in a real app
            error_log("Registration Error: " . $e->getMessage());
            $response['message'] = 'An error occurred during registration.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>