<?php
// api/otp_handler.php
require 'db_connect.php';
require 'helpers.php';



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
 // Ensure user is logged in (if required for this action)



header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        $response['message'] = 'Email and OTP are required.';
    } else {
        try {
            $sql = "SELECT id FROM users WHERE email = ? AND is_verified = 0 AND otp = ? AND otp_expiry > datetime('now')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $otp]); // Pass $otp here
    $user = $stmt->fetch();

    if (!$user) {
        // This could be due to: user not found, already verified, wrong OTP, or OTP expired
        $response['message'] = 'Invalid OTP or OTP has expired.';
        // To differentiate, you could run separate queries, but this is simpler.
    } else {
        // OTP is correct, not expired, and user not verified. Verify them.
        $sqlUpdate = "UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        if ($stmtUpdate->execute([$user['id']])) {
            $response['success'] = true;
            $response['message'] = 'Account verified successfully! You can now log in.';
            $httpStatusCode = 200;
        } else {
            $response['message'] = 'Failed to verify account. Please try again.';
            $httpStatusCode = 500;
        }
    }
        } catch (PDOException $e) {
            error_log("OTP Verification Error: " . $e->getMessage());
            $response['message'] = 'An error occurred during verification.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>