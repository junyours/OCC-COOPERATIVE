<?php
require_once 'db_connect.php';

echo "=== savings_withdrawal_requests table structure ===\n";
$result = $db->query('DESCRIBE savings_withdrawal_requests');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
    }
} else {
    echo 'Error: ' . $db->error . PHP_EOL;
}

echo "\n=== Sample data ===\n";
$result = $db->query('SELECT * FROM savings_withdrawal_requests LIMIT 1');
if ($result) {
    $row = $result->fetch_assoc();
    if ($row) {
        echo "Columns: " . implode(', ', array_keys($row)) . PHP_EOL;
    } else {
        echo "No data in table\n";
    }
} else {
    echo 'Error: ' . $db->error . PHP_EOL;
}
?>
