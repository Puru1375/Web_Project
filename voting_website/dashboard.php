<?php
// dashboard.php
require 'api/db_connect.php'; // Establishes connection and starts session
require 'api/helpers.php';    // Contains checkLogin()

checkLogin(); // Make sure the user is logged in

$username = $_SESSION['username'];
$csrf_token = generateCSRFToken(); // Generate/get token
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Voting Dashboard</h1>
        <nav>
            <span>Welcome, <?php echo htmlspecialchars($username); ?>!</span>
            <a href="create_poll.php">Create New Poll</a>
            <a href="api/logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Available Polls</h2>
        <div id="pollsList" data-csrf="<?php echo htmlspecialchars($csrf_token); ?>" >
            <!-- Polls will be loaded here by JavaScript -->
            <p>Loading polls...</p>
        </div>
         <div id="voteMessage" class="message" style="margin-top: 20px;"></div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>