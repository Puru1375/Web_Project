<?php
// index.php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
// For CSRF token on the login form
require_once 'api/db_connect.php'; // Ensures session is started via db_connect
require_once 'api/helpers.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Website - Secure & Simple</title>
    <link rel="stylesheet" href="css/style.css"> <!-- We'll heavily update this -->
    <!-- Link to a Google Font (e.g., Poppins or Lato as used before) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Optional: Add an icon library like Font Awesome for icons -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> -->
</head>
<body>
    <div class="page-wrapper">

        <header class="main-header">
            <div class="nav-container">
                <a href="index.php" class="logo">VoteSecure</a>
                <nav class="main-nav">
                    <ul>
                        <!-- For a landing page, you might have Features, How it Works, etc. -->
                        <!-- For now, just login/register if not logged in -->
                        <li><a href="index.php" class="nav-link active">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <section class="hero-section">
                <div class="hero-content">
                    <h1>Secure Voting <span class="highlight">Made Simple</span></h1>
                    <p class="subtitle">Empower your decisions with transparent, secure, and lightning-fast voting. Make every vote count.</p>

                    <!-- Login Form Integrated Here -->
                    <div class="login-form-container auth-form-themed">
                        <h2>Member Login</h2>
                        <form id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
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

            <!-- Example: "Why Choose Us" section (Simplified) -->
            <section class="features-overview-section section-padding">
                <div class="container-narrow">
                    <h2 class="section-title">Why Our Voting System?</h2>
                    <div class="features-grid">
                        <div class="feature-card">
                            <!-- <i class="fas fa-shield-alt feature-icon"></i> Icon example -->
                            <div class="feature-icon-placeholder">S</div>
                            <h3>Bank-Level Security</h3>
                            <p>Ensuring your votes are completely secure and tamper-proof with OTP and CSRF protection.</p>
                        </div>
                        <div class="feature-card">
                             <div class="feature-icon-placeholder">T</div>
                            <h3>Complete Transparency</h3>
                            <p>Real-time results and clear processes provide full visibility into the voting outcome.</p>
                        </div>
                        <div class="feature-card">
                             <div class="feature-icon-placeholder">F</div>
                            <h3>Lightning Fast</h3>
                            <p>Cast your vote in seconds with our streamlined, intuitive interface.</p>
                        </div>
                    </div>
                </div>
            </section>

             <!-- Example: "How It Works" Section -->
            <section class="how-it-works-section section-padding alt-background">
                <div class="container-narrow">
                    <h2 class="section-title">Get Started in 3 Simple Steps</h2>
                    <div class="steps-grid">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3>Register & Verify</h3>
                            <p>Quickly sign up and verify your account via email OTP.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3>Create or Join Polls</h3>
                            <p>Easily create new polls or participate in existing ones.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3>View Real-time Results</h3>
                            <p>Watch votes come in (or view final results) with clear analytics.</p>
                        </div>
                    </div>
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
    </div> <!-- .page-wrapper -->

    <script src="js/script.js"></script>
</body>
</html>