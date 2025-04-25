<?php
// api/db_connect.php
$db_host = 'localhost'; // Or your database host
$db_name = 'voting_db';
$db_user = 'root';      // Your database username
$db_pass = 'Purvanshu13';          // Your database password


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
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

try {
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
     // In production, log this error instead of showing it
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
     // Or die('Database connection failed: ' . $e->getMessage());
}

// Start session on all pages that include this file or need session data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>