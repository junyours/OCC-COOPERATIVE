<?php
date_default_timezone_set('Asia/Manila');
date_default_timezone_get(); 
// MySQL database credentials
$host = "localhost"; 
$username = "root"; 
$password = ""; 
$database_name = "occ_coop"; // MySQL database name

// Database Connection
$db = new mysqli($host, $username, $password, $database_name);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Optional: set charset to utf8
$db->set_charset("utf8");


?> 
