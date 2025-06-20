<?php
require_once 'api/db_connect.php'; // Ensures session start
require_once 'api/helpers.php';    // For CSRF
$csrf_token = generateCSRFToken();
$emailForDisplay = htmlspecialchars($_GET['email'] ?? 'your email address'); // Get email for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <header class="main-header"> <!-- Use the same header as other auth pages -->
            <div class="nav-container">
                <a href="index.php" class="logo">VotingSystem</a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                        <!-- No "active" class here unless you want "Verify OTP" in nav -->
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <section class="auth-page-section"> <!-- Provides padding for the section -->
                <div class="auth-form-container auth-form-themed"> <!-- Use the consistent themed class -->
                    <h2>Verify Your Account</h2>
                    <p class="auth-subtext">An OTP has been sent to <strong><?php echo $emailForDisplay; ?></strong>. Please enter it below.</p>
                    <form id="otpForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                        <div class="form-group">
                            <label for="otp">OTP Code:</label>
                            <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" required pattern="\d{6}" title="Enter the 6-digit OTP">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Verify OTP <span class="button-spinner"></span></button>
                        <p id="message" class="message"></p>
                        <div class="form-links">
                            <a href="register.php">Go back to Registration</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <footer class="main-footer">
             <div class="container-narrow footer-content">
                <div class="footer-brand">
                    <a href="index.php" class="logo">VotingSystem</a>
                    <p>© <?php echo date("Y"); ?> VotingSystem. All rights reserved.</p>
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