<?php
require 'api/db_connect.php';
require 'api/helpers.php';
checkLogin();
$username = $_SESSION['username'];
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <header class="main-header app-header"> <!-- Added .app-header class for specific styling -->
            <div class="nav-container">
                <a href="index.php" class="logo">VotingSystem</a>

                <!-- Hamburger Icon (visible on mobile) -->
                <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <!-- Or use Font Awesome: <i class="fas fa-bars"></i> -->
                </button>

                <nav class="main-nav" id="mainNavMenu"> <!-- Add ID for JS targeting -->
                    <ul>
                        <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                        <li><a href="create_poll.php" class="nav-link">Create Poll</a></li>
                        <li class="nav-user-info"><span>Welcome, <?php echo htmlspecialchars($username); ?>!</span></li>
                        <li><a href="api/logout.php" class="nav-link logout-link">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <div class="dashboard-container container-narrow"> <!-- Use a more specific container -->
                 <div class="dashboard-header">
                    <h1>Available Polls</h1>
                </div>
                <div id="pollsList" data-csrf="<?php echo htmlspecialchars($csrf_token); ?>">
                    <p>Loading polls...</p>
                </div>
                <div id="voteMessage" class="message" style="margin-top: 20px;"></div>
            </div>
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