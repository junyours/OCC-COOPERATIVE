<?php
require_once('db_connect.php');
$result = $db->query('SHOW TABLES');
echo 'Tables in occ_coop database:' . PHP_EOL;
while($row = $result->fetch_row()) {
    echo '- ' . $row[0] . PHP_EOL;
}
?>
