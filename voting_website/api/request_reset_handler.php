<?php
// api/request_reset_handler.php
require 'db_connect.php'; // Includes session start & SMTP constants
require 'helpers.php';    // Includes CSRF, PHPMailer setup

// Import PHPMailer classes needed (if not already done globally in helpers.php)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\SMTP; // If needed for debug

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An error occurred.'];
$httpStatusCode = 500;

// --- CSRF Token Verification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedToken)) {
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


$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    $response['message'] = 'Invalid email address format provided.';
    $httpStatusCode = 400;
} else {
    try {
        // Check if email exists
        $sqlFind = "SELECT id, username FROM users WHERE email = ?";
        $stmtFind = $pdo->prepare($sqlFind);
        $stmtFind->execute([$email]);
        $user = $stmtFind->fetch();

        // IMPORTANT: Always return a generic success message even if email not found
        // This prevents attackers from discovering registered emails (user enumeration).
        $response['success'] = true; // Assume success presentationally
        $response['message'] = 'If an account with that email exists, a password reset link has been sent.';
        $httpStatusCode = 200;

        if ($user) {
            // User found, proceed with token generation and email
            $userId = $user['id'];
            $username = $user['username'];

            // Generate secure token
            $token = bin2hex(random_bytes(32)); // 64 character hex token
            $expiryMinutes = 60; // Token valid for 1 hour
            $expiryTimestamp = date('Y-m-d H:i:s', strtotime("+$expiryMinutes minutes"));

            // Store token hash and expiry in DB (Store HASH, not raw token!)
            // $tokenHash = password_hash($token, PASSWORD_DEFAULT); // Option 1: Store hash
            // Using raw token here for simplicity, but hashing is more secure if DB is compromised
            $sqlSaveToken = "UPDATE users SET password_reset_token = ?, password_reset_expiry = ? WHERE id = ?";
            $stmtSaveToken = $pdo->prepare($sqlSaveToken);

            // Store raw token for this example
            if ($stmtSaveToken->execute([$token, $expiryTimestamp, $userId])) {
                // Send email with PHPMailer
                $mail = new PHPMailer(true);
                try {
                     // --- SMTP Configuration from db_connect.php ---
                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for testing only
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USERNAME;
                    $mail->Password   = SMTP_PASSWORD;
                    $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = SMTP_PORT;

                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($email, $username);

                    // --- Determine Base URL (Adapt this!) ---
                    // This needs to point correctly to your site structure
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    // Adjust the path if your voting site is in a subfolder
                    $baseUrl = $protocol . "://" . $host . "/web_project/voting_website"; // MODIFY AS NEEDED!

                    $resetLink = $baseUrl . "/reset_password.php?token=" . urlencode($token);

                    // --- Email Content ---
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request - Voting Website';
                    $mail->Body    = "Hello " . htmlspecialchars($username) . ",<br><br>" .
                                     "You requested a password reset for your account.<br>" .
                                     "Please click the link below to set a new password. This link is valid for $expiryMinutes minutes:<br><br>" .
                                     "<a href='" . $resetLink . "'>" . $resetLink . "</a><br><br>" .
                                     "If you did not request this, please ignore this email.<br><br>" .
                                     "Regards,<br>" . SMTP_FROM_NAME;
                    $mail->AltBody = "Hello " . htmlspecialchars($username) . ",\n\n" .
                                     "To reset your password, please visit the following link (valid for $expiryMinutes minutes):\n" . $resetLink . "\n\n" .
                                     "If you did not request this, please ignore this email.\n\n" .
                                     "Regards,\n" . SMTP_FROM_NAME;

                    $mail->send();
                    error_log("Password reset email sent successfully to: $email");

                } catch (Exception $e) {
                    error_log("Password reset email Error: Could not send to $email. Mailer Error: {$mail->ErrorInfo}");
                    // Don't change the user-facing message here for security.
                    // Internal error is logged.
                }

            } else {
                error_log("Failed to store password reset token for user ID: $userId");
                // Don't change the user-facing message here for security.
            }
        }
        // If user was not found, we still return the generic success message above.

    } catch (PDOException $e) {
        error_log("Password Reset Request DB Error: " . $e->getMessage());
        // Keep the generic user message for security.
    } catch (Exception $e) { // Catch other errors like random_bytes failure
        error_log("Password Reset Request General Error: " . $e->getMessage());
         // Keep the generic user message for security.
    }
}

// --- Final JSON Output ---
header('Content-Type: application/json');
http_response_code($httpStatusCode);
echo json_encode($response);
exit;
?>