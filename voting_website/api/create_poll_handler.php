<?php
// api/create_poll_handler.php
require 'db_connect.php'; // Includes session_start()
require 'helpers.php';    // Includes CSRF and other functions

// Default response structure
$response = ['success' => false, 'message' => 'An error occurred.'];
$httpStatusCode = 500; // Default to server error

// --- CSRF Token Verification ---
// MUST happen before processing any POST data if method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedToken)) {
        $response['message'] = 'Invalid security token. Please refresh and try again.';
        $httpStatusCode = 403; // Forbidden
        header('Content-Type: application/json');
        http_response_code($httpStatusCode);
        echo json_encode($response);
        exit; // Stop script execution *only* on CSRF failure
    }
} else {
    $response['message'] = 'Invalid request method.';
    $httpStatusCode = 405; // Method Not Allowed
    header('Content-Type: application/json');
    http_response_code($httpStatusCode);
    echo json_encode($response);
    exit;
}
// --- End CSRF / Request Method Checks ---

// If we reach here, method is POST and CSRF is valid.

// --- Check Login Status ---
try {
    // Note: Ensure checkLogin() doesn't redirect in an API context.
    // It should ideally throw an exception or return a status if not logged in.
    // If it redirects, the JSON response will be lost.
    checkLogin();
    $userId = $_SESSION['user_id']; // Get user ID *after* confirming login
} catch (Exception $e) { // Catch potential exception from checkLogin or session access
    $response['message'] = 'Authentication required. Please log in.';
    $httpStatusCode = 401; // Unauthorized
    header('Content-Type: application/json');
    http_response_code($httpStatusCode);
    echo json_encode($response);
    exit;
}
// --- End Check Login Status ---


// --- Process POST Data ---
$question = trim($_POST['question'] ?? '');
$options = $_POST['options'] ?? [];
$closes_at_input = trim($_POST['closes_at'] ?? '');

// --- Validation ---
$isValid = true; // Use a single flag
$closes_at_db = null; // Initialize DB date variable

if (empty($question)) {
    $response['message'] = 'Poll question cannot be empty.';
    $isValid = false;
} elseif (!is_array($options) || count($options) < 2) { // Also check if options is an array
    $response['message'] = 'Please provide at least two valid options.';
    $isValid = false;
} else {
    // Check individual options
    foreach ($options as $option) {
        if (empty(trim($option))) {
            $response['message'] = 'Option text cannot be empty.';
            $isValid = false;
            break; // Stop checking options if one is empty
        }
    }
}

// Validate closes_at only if other fields are valid so far
if ($isValid && !empty($closes_at_input)) {
    $timestamp = strtotime($closes_at_input);
    if ($timestamp === false) {
        $response['message'] = 'Invalid closing date/time format.';
        $isValid = false;
    } elseif ($timestamp <= time()) {
        $response['message'] = 'Closing date/time must be in the future.';
        $isValid = false;
    } else {
        // Format for MySQL DATETIME column
        $closes_at_db = date('Y-m-d H:i:s', $timestamp);
    }
}
// --- End Validation ---

// --- Database Interaction (only if valid) ---
if ($isValid) {
    try {
        $pdo->beginTransaction();

        $sqlPoll = "INSERT INTO polls (question, created_by_user_id, closes_at) VALUES (?, ?, ?)";
        $stmtPoll = $pdo->prepare($sqlPoll);

        // *** CRITICAL FIX: Add $closes_at_db to the execute array ***
        $executeSuccess = $stmtPoll->execute([$question, $userId, $closes_at_db]);

        if ($executeSuccess) {
            $pollId = $pdo->lastInsertId();

            if($pollId) {
                // Insert options
                $sqlOption = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
                $stmtOption = $pdo->prepare($sqlOption);
                foreach ($options as $optionText) {
                    // You might want error checking per option insert in production
                    $stmtOption->execute([$pollId, trim($optionText)]);
                }
                 $pdo->commit();
                 $response['success'] = true;
                 $response['message'] = 'Poll created successfully!';
                 $httpStatusCode = 201; // 201 Created is more appropriate on success
            } else {
                 // This case indicates an issue after successful execute but before getting ID (rare)
                 throw new PDOException("Failed to retrieve lastInsertId after poll creation.");
            }
        } else {
             // execute() returned false, but didn't throw exception (also rare, depends on PDO settings)
             throw new PDOException("Poll insertion failed (execute returned false).");
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Create Poll Error: " . $e->getMessage());
        $response['message'] = 'An error occurred while saving the poll data.';
        $httpStatusCode = 500; // Internal Server Error for DB issues
    }
} else {
    // Validation failed, $response['message'] was set during validation.
    $httpStatusCode = 400; // Bad Request for validation errors
}
// --- End Database Interaction ---


// --- Final JSON Output ---
header('Content-Type: application/json'); // Set header before output
http_response_code($httpStatusCode); // Set appropriate status code
echo json_encode($response); // Output the final response
exit; // Exit after sending response

?> // Closing tag is optional