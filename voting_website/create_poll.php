<?php
require 'api/db_connect.php';
require 'api/helpers.php';
checkLogin();
$username = $_SESSION['username']; // Though not directly used in form, good for header
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Poll - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <header class="main-header app-header"> <!-- Added .app-header class for specific styling -->
            <div class="nav-container">
                <a href="index.php" class="logo">VoteSecure</a>

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
            <div class="create-poll-container container-narrow"> <!-- Specific container class -->
                 <div class="create-poll-header">
                    <h1>Create New Poll</h1>
                </div>

                <form id="createPollForm" class="themed-form"> <!-- General themed form class -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="question">Poll Question:</label>
                        <textarea id="question" name="question" rows="3" placeholder="What do you want to ask?" required></textarea>
                    </div>

                    <fieldset id="optionsContainer">
                        <legend>Poll Options (Minimum 2)</legend>
                        <div class="form-group option-group">
                            <label for="option1">Option 1:</label>
                            <input type="text" id="option1" name="options[]" placeholder="Enter option text" required>
                        </div>
                        <div class="form-group option-group">
                            <label for="option2">Option 2:</label>
                            <input type="text" id="option2" name="options[]" placeholder="Enter option text" required>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <button type="button" id="addOptionBtn" class="btn btn-outline">Add Another Option</button>
                    </div>

                    <div class="form-group">
                        <label for="closes_at">Closes At (Optional):</label>
                        <input type="datetime-local" id="closes_at" name="closes_at">
                        <small>Leave blank if the poll should never close automatically.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Poll <span class="button-spinner"></span></button>
                    <p id="message" class="message"></p>
                </form>
            </div>
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