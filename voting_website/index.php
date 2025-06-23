<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
require_once 'api/db_connect.php';
require_once 'api/helpers.php';
$csrf_token = generateCSRFToken();
// For reCAPTCHA on login form (optional, but good for security)
// define('RECAPTCHA_V3_SITE_KEY', 'YOUR_LOGIN_RECAPTCHA_SITE_KEY_HERE'); // If using separate key for login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VotingSystem - Cast Your Vote Securely</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php /* if (defined('RECAPTCHA_V3_SITE_KEY')): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_V3_SITE_KEY; ?>"></script>
    <?php endif; */ ?>
</head>
<body>
    <div class="page-wrapper">

        <header class="main-header">
            <div class="nav-container">
                <a href="index.php" class="logo">VoteSecure</a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php" class="nav-link active">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <section class="hero-section">
                <div class="hero-content">
                    <h1>Your Voice, Your Vote, <span class="highlight">Secured.</span></h1>
                    <p class="subtitle">Create polls, cast votes, and see results instantly with our easy-to-use and secure online voting platform.</p>

                    <div class="login-form-container auth-form-themed">
                        <h2>Member Login</h2>
                        <form id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <?php /* if (defined('RECAPTCHA_V3_SITE_KEY')): ?>
                                <input type="hidden" name="recaptcha_response" id="recaptchaResponseLogin">
                            <?php endif; */ ?>
                            <div class="form-group">
                                <label for="login_identifier">Username or Email</label>
                                <input type="text" id="login_identifier" name="login_identifier" placeholder="Enter username or email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login <span class="button-spinner"></span></button>
                            <p id="message" class="message"></p>
                            <div class="form-links">
                                <a href="forgot_password.php">Forgot Password?</a>
                                <span>|</span>
                                <a href="register.php">Create an Account</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="hero-background-gradient"></div>
            </section>

            <section class="features-section section-padding">
                <div class="container-narrow">
                    <h2 class="section-title">Platform Features</h2>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon-placeholder">🔑</div>
                            <h3>Secure OTP Login</h3>
                            <p>Enhanced account security with One-Time Password verification during registration.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-placeholder">➕</div>
                            <h3>Create Custom Polls</h3>
                            <p>Easily design and launch polls with multiple options and optional closing dates.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-placeholder">🗳️</div>
                            <h3>Fair & Simple Voting</h3>
                            <p>Cast your vote quickly on active polls. One vote per user ensures fairness.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-placeholder">📊</div>
                            <h3>Instant Results</h3>
                            <p>View poll results as they happen or after the poll closes, with clear counts and percentages.</p>
                        </div>
                         <div class="feature-card">
                            <div class="feature-icon-placeholder">🛡️</div>
                            <h3>CSRF Protected</h3>
                            <p>All critical actions are protected against Cross-Site Request Forgery attacks.</p>
                        </div>
                         <div class="feature-card">
                            <div class="feature-icon-placeholder">⏳</div>
                            <h3>Poll Expiry Control</h3>
                            <p>Set automatic closing times for your polls to manage voting periods effectively.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="how-it-works-section section-padding alt-background">
                <div class="container-narrow">
                    <h2 class="section-title">Get Started Quickly</h2>
                    <div class="steps-grid">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3>Register Account</h3>
                            <p>Sign up in minutes and verify your email with a secure OTP.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3>Create or Vote</h3>
                            <p>Launch your own polls or participate in existing ones on the dashboard.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3>See The Impact</h3>
                            <p>View results instantly and understand the collective voice.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cta-section section-padding">
                <div class="container-narrow text-center">
                    <h2>New to VotingSystem?</h2>
                    <p class="subtitle" style="margin-bottom: 2rem;">Join our platform today and make your voice heard or gather valuable opinions securely.</p>
                    <a href="register.php" class="btn btn-primary btn-lg">Create Your Free Account Now</a>
                </div>
            </section>

        </main>

        <footer class="main-footer">
             <div class="container-narrow footer-content">
                <div class="footer-brand">
                    <a href="index.php" class="logo"> VoteSecure</a>
                    <p>© <?php echo date("Y"); ?>  VoteSecure. All rights reserved. <br>Secure and simple online polling.</p>
                </div>
                <div class="footer-links">
                    <div>
                        <h4>Navigate</h4>
                        <ul>
                            <li><a href="index.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="create_poll.php">Create Poll</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div>
                        <h4>Legal</h4>
                        <ul>
                            <li><a href="#">Privacy Policy (Sample)</a></li>
                            <li><a href="#">Terms of Service (Sample)</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- .page-wrapper -->

    <script src="js/script.js"></script>
    <?php /* if (defined('RECAPTCHA_V3_SITE_KEY')): ?>
    <script>
        // Optional: reCAPTCHA for Login Form
        // Make sure loginForm and messageDiv are defined if this script runs
        const loginFormForRecaptcha = document.getElementById('loginForm');
        const messageDivForRecaptcha = document.getElementById('message'); // Assuming message div is already defined

        loginFormForRecaptcha.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default form submission

            grecaptcha.ready(() => {
                grecaptcha.execute('<?php echo RECAPTCHA_V3_SITE_KEY; ?>', {action: 'login'})
                    .then((token) => {
                        // Add the token to the form
                        const recaptchaResponse = document.getElementById('recaptchaResponseLogin');
                        recaptchaResponse.value = token;

                        // Now submit the form
                        loginFormForRecaptcha.submit();
                    })
                    .catch((error) => {
                        console.error('reCAPTCHA error:', error);
                        messageDivForRecaptcha.textContent = 'reCAPTCHA verification failed. Please try again.';
                    });
            });
        })           
    </script>
    <?php endif; */ ?>
</body>
</html>