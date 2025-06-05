<?php
echo "Default Timezone: " . date_default_timezone_get() . "<br>";
echo "Current PHP Time (using default timezone): " . date('Y-m-d H:i:s') . "<br>";

// If you set a specific timezone in db_connect.php
date_default_timezone_set('UTC'); // Or whatever you set
echo "Current PHP Time (UTC or explicitly set): " . date('Y-m-d H:i:s') . "<br>";
?>