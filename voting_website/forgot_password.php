<?php
require_once 'api/db_connect.php'; // Ensures session start
require_once 'api/helpers.php';    // For CSRF
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container auth-form">
        <h2>Reset Password</h2>
        <p>Enter your registered email address below. If an account exists, we will send instructions to reset your password.</p>
        <form id="forgotPasswordForm">
             <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Reset Link</button>
            <p id="message" class="message"></p>
            <p><a href="index.php">Back to Login</a></p>
        </form>
    </div>
    <script src="js/script.js"></script>
</body>
</html>