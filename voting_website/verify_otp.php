<?php
require_once 'api/db_connect.php'; // Ensures session is started
require_once 'api/helpers.php';    // Include helpers
$csrf_token = generateCSRFToken(); // Generate/get token
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container auth-form">
        <h2>Verify Your Account</h2>
        <p>An OTP has been sent to <strong><?php echo htmlspecialchars($_GET['email'] ?? 'your email'); ?></strong>. Please enter it below.</p>
        <form id="otpForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
            <div class="form-group">
                <label for="otp">OTP:</label>
                <input type="text" id="otp" name="otp" required pattern="\d{6}" title="Enter the 6-digit OTP">
            </div>
            <button type="submit">Verify OTP</button>
            <p id="message" class="message"></p>
             <p><a href="register.php">Go back to Registration</a></p>
        </form>
    </div>

    <script src="js/script.js"></script>
</body>
</html>