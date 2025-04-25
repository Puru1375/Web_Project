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
    <title>Register - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="spinner-overlay">
        <div class="spinner"></div>
    </div>
    <div class="container auth-form" utocomplete="true">
        <h2>Register</h2>
        <form id="registerForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <small>We'll *simulate* sending OTP to this email.</small>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
            <p id="message" class="message"></p>
            <p>Already have an account? <a href="index.php">Login here</a></p>
        </form>
    </div>
    <footer>
        Voting Website © <?php echo date("Y"); ?>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>