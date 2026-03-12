<?php
date_default_timezone_set('Asia/Manila');
date_default_timezone_get(); 

$host = "localhost"; 
$username = "root"; 
$password = ""; 
$database_name = "occ_coop"; 


$db = new mysqli($host, $username, $password, $database_name);


if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}


$db->set_charset("utf8");


?> 
