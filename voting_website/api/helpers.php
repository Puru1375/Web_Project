<?php
// api/helpers.php

require __DIR__ . '/../vendor/autoload.php'; // <--- THIS LINE IS CRITICAL

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        try {
            // Generate a secure random token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Handle error if random_bytes fails (highly unlikely)
            error_log("CSRF token generation failed: " . $e->getMessage());
            // Fallback or error handling needed here in a real production app
            $_SESSION['csrf_token'] = 'fallback_token_' . time(); // Insecure fallback
        }
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($submittedToken) {
    if (empty($_SESSION['csrf_token']) || empty($submittedToken)) {
        error_log("CSRF Verification Failed: Session or submitted token missing.");
        return false;
    }
    // Use hash_equals for timing-attack safe comparison
    if (hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        // Optional: Regenerate token after successful use for single-use tokens
        // unset($_SESSION['csrf_token']); // Uncomment for single-use tokens (more complex with AJAX)
        return true;
    } else {
        error_log("CSRF Verification Failed: Tokens do not match.");
        return false;
    }
}
// --- End CSRF Token Handling ---


// --- IMPORTANT ---
// This is a SIMULATED OTP function for demonstration.
// Real OTP requires SMS/Email gateway integration (e.g., Twilio, SendGrid).
// For this example, we'll just generate it and store it.
// In a real app, you would send it via SMS/Email here.
function generateAndStoreOTP($pdo, $userId, $userEmail, $username) {
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

    // Store OTP in the database BEFORE trying to send
    $sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute([$otp, $expiryTime, $userId])) {
         error_log("Failed to store OTP for user ID: $userId");
         return false; // Failed to store OTP
    }

    // --- Send OTP Email using PHPMailer ---
    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output - Use for testing ONLY
        $mail->isSMTP();                         // Send using SMTP
        $mail->Host       = SMTP_HOST;           // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                // Enable SMTP authentication
        $mail->Username   = SMTP_USERNAME;       // SMTP username
        $mail->Password   = SMTP_PASSWORD;       // SMTP password
        $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS; // Enable implicit or explicit TLS encryption
        $mail->Port       = SMTP_PORT;           // TCP port to connect to

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($userEmail, $username); // Add a recipient

        // Content
        $mail->isHTML(true);                      // Set email format to HTML
        $mail->Subject = 'Your Verification Code for Voting Website';
        $mail->Body    = "Hello " . htmlspecialchars($username) . ",<br><br>" .
                         "Thank you for registering. Your One-Time Password (OTP) is: <br><br>" .
                         "<strong style='font-size: 1.5em;'>" . $otp . "</strong><br><br>" .
                         "This code is valid for 10 minutes.<br><br>" .
                         "If you did not request this, please ignore this email.<br><br>" .
                         "Regards,<br>" . SMTP_FROM_NAME;
        $mail->AltBody = "Hello " . htmlspecialchars($username) . ",\n\n" .
                         "Your One-Time Password (OTP) is: " . $otp . "\n\n" .
                         "This code is valid for 10 minutes.\n\n" .
                         "Regards,\n" . SMTP_FROM_NAME; // Plain text version

        $mail->send();
        error_log("OTP Email sent successfully to: $userEmail"); // Log success
        return true; // Email sent successfully

    } catch (Exception $e) {
        // Log the detailed error message from PHPMailer
        error_log("OTP Email Error: Could not send email to $userEmail. Mailer Error: {$mail->ErrorInfo}");
        return false; // Email sending failed
    }
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php'); // Redirect to login page
        exit();
    }
}
?>