<?php
require_once 'api/db_connect.php';
require_once 'api/helpers.php';
$csrf_token = generateCSRFToken();
// Define your reCAPTCHA v3 Site Key (can also be done in JS, but PHP makes it easy to change)
define('RECAPTCHA_V3_SITE_KEY', '6LekrmkrAAAAAPMmyB-TJaDMhfwcNfS1Rm6uaMRk');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js?render=6LekrmkrAAAAAPMmyB-TJaDMhfwcNfS1Rm6uaMRk"></script>
</head>
<body>
    <div class="page-wrapper">
        <header class="main-header">
            <div class="nav-container">
                <a href="index.php" class="logo">VoteSecure</a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link active">Register</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <section class="section-padding"> <!-- Use this for consistent padding -->
                <!-- Add auth-form-themed and ensure proper centering for the container -->
                <div class="auth-form-container auth-form-themed" style="max-width: 500px; margin: 60px auto;">
                    <h2>Create Your Account</h2>
                    <form id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Choose a username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            <small>We'll send an OTP to this email for verification.</small>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Register <span class="button-spinner"></span></button>
                        <p id="message" class="message"></p>
                        <div class="form-links">
                            <span>Already have an account?</span>
                            <a href="index.php">Login here</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <footer class="main-footer">
            <div class="container-narrow footer-content">
                <div class="footer-brand">
                    <a href="index.php" class="logo">VoteSecure</a>
                    <p>© <?php echo date("Y"); ?> VoteSecure. All rights reserved.</p>
                </div>
                <div class="footer-links">
                    <div>
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="index.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                            <li><a href="dashboard.php">Dashboard</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4>Legal</h4>
                        <ul>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div><!-- .page-wrapper -->
    <script src="js/script.js"></script>
</body>
</html>