<?php
// create_poll.php
require 'api/db_connect.php';
require 'api/helpers.php';

checkLogin(); // Make sure the user is logged in
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
    <title>Create Poll - Voting Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="spinner-overlay">
        <div class="spinner"></div>
    </div>
     <header>
        <h1>Create New Poll</h1>
        <nav>
             <a href="dashboard.php">Back to Dashboard</a>
             <a href="api/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <form id="createPollForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="question">Poll Question:</label>
                <textarea id="question" name="question" rows="3" required></textarea>
            </div>

            <div id="optionsContainer">
                <div class="form-group option-group">
                    <label>Option 1:</label>
                    <input type="text" name="options[]" required>
                </div>
                <div class="form-group option-group">
                    <label>Option 2:</label>
                    <input type="text" name="options[]" required>
                </div>
            </div>

            <div class="form-group">
                <label for="closes_at">Closes At (Optional):</label>
                <input type="datetime-local" id="closes_at" name="closes_at">
                <small>Leave blank if the poll should never close automatically.</small>
            </div>

            <button type="button" id="addOptionBtn">Add Another Option</button>
            <button type="submit">Create Poll</button>
            <p id="message" class="message"></p>
        </form>
    </div>
    <footer>
        Voting Website © <?php echo date("Y"); ?>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>