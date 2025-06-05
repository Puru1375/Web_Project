<?php
date_default_timezone_set('Asia/Kolkata');  // Or your preferred timezone, e.g., 'Asia/Kolkata', 'America/New_York'
// // api/db_connect.php
// $db_host = 'localhost'; // Or your database host
// $db_name = 'voting_db';
// $db_user = 'root';      // Your database username
// $db_pass = 'Purvanshu13';          // Your database password

$db_file_path = __DIR__ . '/../database/'; // Path to the database directory
$db_file_name = 'voting_app.sqlite';      // Name of the SQLite database file
$full_db_path = $db_file_path . $db_file_name;

// --- Create the database directory if it doesn't exist ---
if (!is_dir($db_file_path)) {
    if (!mkdir($db_file_path, 0777, true) && !is_dir($db_file_path)) {
        // Let the script die here if directory creation fails, as DB connection will fail.
         die(sprintf('Failed to create database directory: %s. Please check permissions.', $db_file_path));
    }
}




// --- SMTP Configuration (!! IMPORTANT SECURITY WARNING !!) ---
// Storing credentials directly in code is insecure for production.
// Use environment variables or a secure config file instead.
define('SMTP_HOST', 'smtp.gmail.com');         // e.g., smtp.gmail.com or smtp.office365.com
define('SMTP_PORT', 587);                       // 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'purvanshu1375@gmail.com'); // Your SMTP login username
define('SMTP_PASSWORD', 'hfpwudzybvhblncw'); // Your SMTP password or App Password
define('SMTP_ENCRYPTION', 'tls');               // 'tls' or 'ssl' (PHPMailer::ENCRYPTION_STARTTLS or PHPMailer::ENCRYPTION_SMTPS)
define('SMTP_FROM_EMAIL', 'purvanshu1375@gmail.com'); // The "From" email address
define('SMTP_FROM_NAME', 'Your Voting Website');    // The "From" name



// Data Source Name
$dsn = "sqlite:" . $full_db_path; // Data Source Name for SQLite

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    // PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

try {
     $pdo = new PDO($dsn, null, null, $options);
     $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (\PDOException $e) {
      // In a real application, log this error and show a generic message to the user.
     error_log("Database Connection Error: " . $e->getMessage());
     die("Database connection failed. Please check the server logs or contact support. Error: " . $e->getMessage()); // Show error during development
}

// Start session on all pages that include this file or need session data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
