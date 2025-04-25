<?php
// index.php
session_start(); // Start session to check login status
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
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
    <title>Login - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="spinner-overlay">
        <div class="spinner"></div>
    </div>
    <div class="container auth-form">
        <h2>Login</h2>
        <form id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="login_identifier">Username or Email:</label>
                <input type="text" id="login_identifier" name="login_identifier" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <p id="message" class="message"></p>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
    <footer>
        Voting Website © <?php echo date("Y"); ?>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>