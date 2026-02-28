<?php
// Adjust this path if your database connection file is elsewhere
require('../db_connect.php');

header('Content-Type: application/json');

if (isset($_GET['member_id'])) {
    $mid = (int)$_GET['member_id'];

    // Corrected table name to 'accounts' (two c's)
    $query = "SELECT 
                s.schedule_id, 
                s.loan_id, 
                s.due_date, 
                s.total_due 
              FROM loan_schedule s 
              INNER JOIN loans l ON s.loan_id = l.loan_id 
              INNER JOIN accounts a ON l.account_id = a.account_id 
              WHERE a.member_id = $mid 
              AND s.status = 'ongoing' 
              ORDER BY s.due_date ASC";

    $res = $db->query($query);

    $data = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
} else {
    echo json_encode([]);
}
