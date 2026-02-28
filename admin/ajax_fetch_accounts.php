<?php
require('../db_connect.php');

if (isset($_GET['member_id'])) {
    $mid = (int)$_GET['member_id'];

    // Corrected table name to 'accounts' (two c's)
    $query = "SELECT a.account_id, a.account_number, t.type_name 
              FROM accounts a 
              JOIN account_types t ON a.account_type_id = t.account_type_id 
              WHERE a.member_id = $mid 
              AND a.status = 'active'";

    $result = $db->query($query);

    if ($result && $result->num_rows > 0) {
        echo '<option value="">-- Select Target Account --</option>';
        while ($row = $result->fetch_assoc()) {
            $name = ucwords(str_replace('_', ' ', $row['type_name']));
            echo '<option value="' . $row['account_id'] . '">' . $name . ' (' . $row['account_number'] . ')</option>';
        }
    } else {
        echo '<option value="">No active accounts found</option>';
    }
}
