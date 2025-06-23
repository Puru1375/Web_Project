<?php
error_log("--- RAW POST DATA in register_handler.php ---");
error_log(print_r($_POST, true));
error_log("--- END RAW POST DATA ---");
// api/register_handler.php
require 'db_connect.php';
require 'helpers.php';

// Initialize response array and HTTP status code at the very top
//header('Content-Type: application/json'); // Set this early
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];
$httpStatusCode = 500; // Default

// Define $ip_address early as it's needed for logging in most cases
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'; // Default if not available

error_log("REGISTER HANDLER: Script started for IP: " . $ip_address); 

// --- CSRF Token Verification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submittedToken)) {
        error_log("REGISTER HANDLER: CSRF token verification FAILED for IP: " . $ip_address);
        $response['message'] = 'Invalid security token.';
        $httpStatusCode = 403;
        http_response_code($httpStatusCode);
        echo json_encode($response);
        // Log attempt *before* exit if CSRF fails for this IP
        logRegistrationAttempt($pdo, $ip_address, 0); // 0 for unsuccessful
        exit;
    }
    error_log("REGISTER HANDLER: CSRF token verification PASSED for IP: " . $ip_address); // <<< DEBUG
} else {
    $response['message'] = 'Invalid request method.';
    $httpStatusCode = 405;
    http_response_code($httpStatusCode);
    echo json_encode($response);
    // Log attempt *before* exit if method is wrong for this IP
    logRegistrationAttempt($pdo, $ip_address, 0);
    exit;
}
// --- End CSRF / Method Checks ---


// --- reCAPTCHA v3 Verification ---
$recaptcha_secret = RECAPTCHA_V3_SECRET_KEY;
$recaptcha_response_token = $_POST['recaptcha_response'] ?? '';
error_log("REGISTER HANDLER: Token PHP is about to send to Google: " . $recaptcha_response_token); // <<< ADD THIS LINE
$recaptchaPassed = false; // Flag for reCAPTCHA status

error_log("REGISTER HANDLER: Starting reCAPTCHA verification for IP: " . $ip_address); // <<< DEBUG
error_log("REGISTER HANDLER: reCAPTCHA token received: " . $recaptcha_response_token); // <<< DEBUG

if (empty($recaptcha_response_token)) {
    error_log("REGISTER HANDLER: reCAPTCHA token was EMPTY for IP: " . $ip_address); // <<< DEBUG
    $response['message'] = 'reCAPTCHA verification failed. (Token missing)';
    $httpStatusCode = 400;
} else {
    error_log("REGISTER HANDLER: Preparing to call Google siteverify for IP: " . $ip_address); // <<< DEBUG
    // ... (your file_get_contents and json_decode logic for reCAPTCHA) ...
    $verification_url = 'https://www.google.com/recaptcha/api/siteverify';
    $post_data_array = [
        'secret'   => $recaptcha_secret,             // Your secret key
        'response' => $recaptcha_response_token,     // The token from the user's browser
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null // Optional: User's IP address
    ];
    $post_data = http_build_query($post_data_array);
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post_data
        ]
    ];
    $context  = stream_context_create($options);
    $verify_result_json = @file_get_contents($verification_url, false, $context); // Use @ to suppress warnings on failure
    error_log("REGISTER HANDLER: Google siteverify raw response: " . $verify_result_json . " for IP: " . $ip_address); // <<< DEBUG
    $verify_result = $verify_result_json ? json_decode($verify_result_json, true) : null;


    if (!$verify_result || !isset($verify_result['success'])) {
    // Log the raw JSON string from Google if decoding failed or 'success' is missing
    error_log("reCAPTCHA: Invalid or missing 'success' in Google's response. Raw response: " . $verify_result_json . " for IP: " . $ip_address);
    $response['message'] = 'reCAPTCHA verification system error.';
    $httpStatusCode = 503;
} elseif (!$verify_result['success']) {
    // Google said success is false. Log the error codes.
    $error_codes_str = isset($verify_result['error-codes']) ? implode(", ", $verify_result['error-codes']) : 'No error codes provided';
    error_log("reCAPTCHA: Google verification failed. Error codes: [" . $error_codes_str . "] for IP: " . $ip_address . ". Full Google Response: " . $verify_result_json);
    $response['message'] = 'reCAPTCHA verification failed. Are you a robot?'; // This is the message you are seeing
    $httpStatusCode = 400;
} elseif ($verify_result['score'] < 0.5) { // Your score threshold
    // ... (log low score) ...
} else {
    $recaptchaPassed = true;
}
}

if (!$recaptchaPassed) { // If reCAPTCHA did not pass, send response and exit
    error_log("REGISTER HANDLER: reCAPTCHA did not pass, exiting for IP: " . $ip_address); // <<< DEBUG
    http_response_code($httpStatusCode);
    echo json_encode($response);
    logRegistrationAttempt($pdo, $ip_address, 0); // Log failed attempt
    exit;
}
// --- End reCAPTCHA Verification ---


// --- IP Address Rate Limiting ---
// This runs ONLY IF reCAPTCHA passed
$max_attempts_per_window = 5;
$time_window_seconds = 3600;
$ipRateLimitPassed = false; // Flag

try {
    $window_start_timestamp_obj = new DateTime("-$time_window_seconds seconds");
    $window_start_iso = $window_start_timestamp_obj->format('Y-m-d H:i:s');

    $sqlCheckIp = "SELECT COUNT(*) as attempt_count FROM registration_attempts
                   WHERE ip_address = ? AND attempt_timestamp > ? AND is_successful_registration = 1"; // Only count successful ones for strict limit
    $stmtCheckIp = $pdo->prepare($sqlCheckIp);
    $stmtCheckIp->execute([$ip_address, $window_start_iso]);
    $attempts = $stmtCheckIp->fetchColumn();

    if ($attempts !== false && $attempts >= $max_attempts_per_window) {
        $response['message'] = 'Too many registration attempts from this IP. Please try again later.';
        $httpStatusCode = 429;
    } else {
        $ipRateLimitPassed = true; // IP limit check passed
    }
} catch (PDOException $e) {
    error_log("IP Rate Limiting Check Error for IP $ip_address: " . $e->getMessage());
    $response['message'] = 'Server error during request verification.';
    // $httpStatusCode is already 500
}

if (!$ipRateLimitPassed) { // If IP rate limit check did not pass, send response and exit
    http_response_code($httpStatusCode);
    echo json_encode($response);
    logRegistrationAttempt($pdo, $ip_address, 0); // Log failed attempt
    exit;
}
// --- End IP Address Rate Limiting ---


// --- Proceed with Actual Registration Logic ---
// This runs ONLY IF CSRF, reCAPTCHA, AND IP Rate Limit passed
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$is_registration_successful_db_flag = 0; // Initialize: 0 for fail, 1 for success (user added to DB for OTP)

// Basic Validation
if (empty($username) || empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    $response['message'] = 'Invalid input. Check fields and ensure password is at least 6 characters.';
    $httpStatusCode = 400;
} else {
    try {
        // Check if username or email already exists
        $sqlCheckUser = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmtCheckUser = $pdo->prepare($sqlCheckUser);
        $stmtCheckUser->execute([$username, $email]);

        if ($stmtCheckUser->fetch()) {
            $response['message'] = 'Username or Email already exists.';
            $httpStatusCode = 409; // Conflict
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sqlInsertUser = "INSERT INTO users (username, email, password_hash, is_verified) VALUES (?, ?, ?, 0)";
            $stmtInsertUser = $pdo->prepare($sqlInsertUser);

            if ($stmtInsertUser->execute([$username, $email, $password_hash])) {
                $userId = $pdo->lastInsertId();
                $is_registration_successful_db_flag = 1; // User successfully inserted

                $emailSent = generateAndStoreOTP($pdo, $userId, $email, $username);

                if ($emailSent) {
                    $response['success'] = true;
                    $response['message'] = 'Registration successful! Please check your email (' . htmlspecialchars($email) . ') for OTP.';
                    $response['otp_sent'] = true;
                    $response['user_email'] = $email;
                    $httpStatusCode = 201;
                } else {
                    // User was created, but OTP email failed.
                    $response['message'] = 'Registration completed, but we failed to send the verification OTP email. Please contact support.';
                    $httpStatusCode = 500; // Or 207 Multi-Status if you want to be specific
                    // $is_registration_successful_db_flag is still 1 because user is in DB
                }
            } else {
                $response['message'] = 'Failed to create user account due to a database issue.';
                // $httpStatusCode remains 500
            }
        }
    } catch (PDOException $e) {
        error_log("Registration Main DB Error: " . $e->getMessage());
        $response['message'] = 'A database error occurred during registration.';
        // $httpStatusCode remains 500
    } catch (Exception $e) {
        error_log("Registration General Error (e.g., OTP): " . $e->getMessage());
        $response['message'] = 'An unexpected error occurred.';
        // $httpStatusCode remains 500
    }
}
// --- End Registration Logic ---


// --- Log IP Attempt (this will now always have $ip_address and $is_registration_successful_db_flag defined) ---
logRegistrationAttempt($pdo, $ip_address, $is_registration_successful_db_flag);
// --- End Log IP Attempt ---


// --- Final JSON Output ---
http_response_code($httpStatusCode);
echo json_encode($response);
exit;


// --- Helper function for logging (can also be in helpers.php) ---
function logRegistrationAttempt($pdo_conn, $ip, $success_flag) {
    // Ensure IP is not empty before trying to log, though it should be set by now
    if (empty($ip)) {
        error_log("Attempted to log registration attempt with empty IP.");
        return;
    }
    try {
        $sql = "INSERT INTO registration_attempts (ip_address, is_successful_registration) VALUES (?, ?)";
        $stmt = $pdo_conn->prepare($sql);
        $stmt->execute([$ip, (int)$success_flag]); // Cast flag to int
    } catch (PDOException $e) {
        // Log this error but don't let it break the main response to the user
        error_log("Failed to log registration attempt for IP $ip: " . $e->getMessage());
    }
}
?>