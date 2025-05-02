<?php
// api/update_password_handler.php
require 'db_connect.php'; // Includes session start
require 'helpers.php';    // Includes CSRF

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An error occurred.'];
$httpStatusCode = 500;

// --- CSRF Token Verification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedTokenCSRF = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedTokenCSRF)) {
        $response['message'] = 'Invalid security token.';
        $httpStatusCode = 403;
        http_response_code($httpStatusCode);
        echo json_encode($response);
        exit;
    }
} else {
    $response['message'] = 'Invalid request method.';
    $httpStatusCode = 405;
    http_response_code($httpStatusCode);
    echo json_encode($response);
    exit;
}
// --- End CSRF / Method Checks ---


$token = $_POST['token'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$userId = null;

// 1. Basic Password Validation
if (empty($newPassword) || empty($confirmPassword)) {
    $response['message'] = 'Please enter and confirm your new password.';
    $httpStatusCode = 400;
} elseif ($newPassword !== $confirmPassword) {
    $response['message'] = 'Passwords do not match.';
    $httpStatusCode = 400;
} elseif (strlen($newPassword) < 6) { // Enforce minimum length
    $response['message'] = 'Password must be at least 6 characters long.';
    $httpStatusCode = 400;
} else {
    // 2. Verify Token (again, for security) and get user ID
    try {
        $sqlCheckToken = "SELECT id FROM users
                          WHERE password_reset_token = ? AND password_reset_expiry > NOW()";
        $stmtCheckToken = $pdo->prepare($sqlCheckToken);
        $stmtCheckToken->execute([$token]);
        $user = $stmtCheckToken->fetch();

        if ($user) {
            $userId = $user['id'];

            // 3. Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // 4. Update password and clear token fields
            $sqlUpdate = "UPDATE users SET
                            password_hash = ?,
                            password_reset_token = NULL,
                            password_reset_expiry = NULL
                          WHERE id = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);

            if ($stmtUpdate->execute([$newPasswordHash, $userId])) {
                $response['success'] = true;
                $response['message'] = 'Password updated successfully! You can now log in with your new password.';
                $httpStatusCode = 200;
                // Optionally: Log the user out of other sessions if desired
                 // session_regenerate_id(true); // Helps prevent session fixation after password change
                 // unset($_SESSION['user_id']); // Force re-login everywhere
                 // unset($_SESSION['username']);
            } else {
                $response['message'] = 'Failed to update password. Please try again.';
                $httpStatusCode = 500;
            }

        } else {
            $response['message'] = 'Invalid or expired password reset token. Please request a new one.';
            $httpStatusCode = 400;
        }

    } catch (PDOException $e) {
        error_log("Password Update Error: " . $e->getMessage());
        $response['message'] = 'An error occurred while updating the password.';
        $httpStatusCode = 500;
    }
}

// --- Final JSON Output ---
header('Content-Type: application/json');
http_response_code($httpStatusCode);
echo json_encode($response);
exit;
?>