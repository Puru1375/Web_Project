<?php
require_once 'api/db_connect.php'; // Session start & DB access
require_once 'api/helpers.php';    // CSRF functions

$token = $_GET['token'] ?? '';
$isTokenValid = false;
$errorMessage = '';
$userId = null;

if (empty($token)) {
    $errorMessage = 'Invalid or missing password reset token.';
} else {
    try {
        // Find user by VALID token (not expired)
        // Compare raw token here as we stored it raw.
        // If you stored hash, you'd select user by email, then verify hash.
        $sqlCheckToken = "SELECT id FROM users
                          WHERE password_reset_token = ? AND password_reset_expiry > NOW()";
        $stmtCheckToken = $pdo->prepare($sqlCheckToken);
        $stmtCheckToken->execute([$token]);
        $user = $stmtCheckToken->fetch();

        if ($user) {
            $isTokenValid = true;
            $userId = $user['id']; // Store user ID if needed, though not strictly required here
        } else {
            $errorMessage = 'Password reset token is invalid or has expired.';
            // Optionally: Clear expired tokens from DB here or via a cron job
             $sqlClear = "UPDATE users SET password_reset_token = NULL, password_reset_expiry = NULL WHERE password_reset_token = ?";
             $stmtClear = $pdo->prepare($sqlClear);
             $stmtClear->execute([$token]);
        }
    } catch (PDOException $e) {
        error_log("Token Check Error: " . $e->getMessage());
        $errorMessage = 'An error occurred while verifying your token. Please try again later.';
    }
}

$csrf_token = generateCSRFToken(); // Generate CSRF token for the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container auth-form">
        <h2>Set New Password</h2>

        <?php if ($isTokenValid): ?>
            <p>Please enter and confirm your new password below.</p>
            <form id="resetPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <!-- Include token in the form to send to handler -->
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit">Update Password</button>
                <p id="message" class="message"></p>
            </form>
        <?php else: ?>
            <!-- Show error if token was invalid/expired -->
            <div class="message error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <p><a href="forgot_password.php">Request a new reset link</a></p>
            <p><a href="index.php">Back to Login</a></p>
        <?php endif; ?>

    </div>
    <script src="js/script.js"></script>
</body>
</html>