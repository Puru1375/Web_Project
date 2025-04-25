<?php
// api/create_poll_handler.php
require 'db_connect.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Only verify for POST requests usually
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedToken)) {
        // Token is invalid or missing, reject the request
        header('Content-Type: application/json'); // Ensure JSON header
        http_response_code(403); // Forbidden status code
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit; // Stop script execution
    }
}
// --- End CSRF Verification ---


// If verification passed, continue with the rest of the script...
checkLogin(); // Ensure user is logged in (if required for this action)

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $options = $_POST['options'] ?? [];

    // Basic Validation
    if (empty($question)) {
        $response['message'] = 'Poll question cannot be empty.';
    } elseif (count($options) < 2) {
        $response['message'] = 'Please provide at least two options.';
    } else {
        $validOptions = true;
        foreach ($options as $option) {
            if (empty(trim($option))) {
                $validOptions = false;
                $response['message'] = 'Option text cannot be empty.';
                break;
            }
        }

        if ($validOptions) {
            try {
                $pdo->beginTransaction(); // Start transaction

                // Insert the poll question
                $sqlPoll = "INSERT INTO polls (question, created_by_user_id) VALUES (?, ?)";
                $stmtPoll = $pdo->prepare($sqlPoll);
                $stmtPoll->execute([$question, $userId]);
                $pollId = $pdo->lastInsertId();

                // Insert the options
                $sqlOption = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
                $stmtOption = $pdo->prepare($sqlOption);

                foreach ($options as $optionText) {
                    $stmtOption->execute([$pollId, trim($optionText)]);
                }

                $pdo->commit(); // Commit transaction
                $response['success'] = true;
                $response['message'] = 'Poll created successfully!';

            } catch (PDOException $e) {
                $pdo->rollBack(); // Rollback transaction on error
                error_log("Create Poll Error: " . $e->getMessage());
                $response['message'] = 'An error occurred while creating the poll.';
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>