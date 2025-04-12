<?php
// api/db_connect.php
define('DB_SERVER', 'localhost:3309');
define('DB_USERNAME', 'root');    // <-- Your MySQL username
define('DB_PASSWORD', '');        // <-- Your MySQL password
define('DB_NAME', 'discount_finder_db');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
    // In production, log error instead of die()
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>