<?php
// api/submit_vote.php
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
    $pollId = $_POST['poll_id'] ?? null;
    $optionId = $_POST['option_id'] ?? null;

    if (empty($pollId) || empty($optionId)) {
        $response['message'] = 'Poll ID and Option ID are required.';
    } else {
        try {

             // --- Check if Poll is Open before proceeding ---
            $sqlCheckOpen = "SELECT (closes_at IS NULL OR closes_at > NOW()) AS is_open FROM polls WHERE id = ?";
            $stmtCheckOpen = $pdo->prepare($sqlCheckOpen);
            $stmtCheckOpen->execute([$pollId]);
            $pollStatus = $stmtCheckOpen->fetch();
    
            if (!$pollStatus) {
                 $response['message'] = 'Poll not found.';
            } elseif (!$pollStatus['is_open']) {
                 $response['message'] = 'Sorry, this poll is now closed for voting.';
            } else {
             // --- Poll is open, proceed with voting logic ---


             // 1. Verify the option belongs to the poll (optional but good practice)
             $sqlCheckOption = "SELECT COUNT(*) FROM poll_options WHERE id = ? AND poll_id = ?";
             $stmtCheckOption = $pdo->prepare($sqlCheckOption);
             $stmtCheckOption->execute([$optionId, $pollId]);
             if ($stmtCheckOption->fetchColumn() == 0) {
                  $response['message'] = 'Invalid option for this poll.';
                  echo json_encode($response);
                  exit;
             }


            // 2. Check if the user has already voted on this poll
            $sqlCheck = "SELECT COUNT(*) FROM votes WHERE user_id = ? AND poll_id = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$userId, $pollId]);

            if ($stmtCheck->fetchColumn() > 0) {
                $response['message'] = 'You have already voted on this poll.';
            } else {
                // Insert the vote
                $sqlInsert = "INSERT INTO votes (user_id, poll_id, option_id) VALUES (?, ?, ?)";
                $stmtInsert = $pdo->prepare($sqlInsert);

                if ($stmtInsert->execute([$userId, $pollId, $optionId])) {
                    $response['success'] = true;
                    $response['message'] = 'Vote submitted successfully!';
                } else {
                    $response['message'] = 'Failed to submit vote. Please try again.';
                }
            }
            }
        } catch (PDOException $e) {
             // Check for unique constraint violation (alternative way to check if already voted)
             if ($e->getCode() == 23000) { // Integrity constraint violation
                 $response['message'] = 'You have already voted on this poll (constraint).';
             } else {
                error_log("Submit Vote Error: " . $e->getMessage());
                $response['message'] = 'An error occurred while submitting the vote.';
             }
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>