<?php
require('../db_connect.php');
header('Content-Type: application/json');

if (isset($_GET['member_id'])) {
    $mid = (int)$_GET['member_id'];

    // Get each schedule
    $query = "
        SELECT 
            s.schedule_id, 
            s.loan_id, 
            s.due_date, 
            s.total_due,
            l.account_id,
            (s.total_due + IFNULL(s.penalty_due,0)) AS remaining_loan_balance
        FROM loan_schedule s
        INNER JOIN loans l ON s.loan_id = l.loan_id
        INNER JOIN accounts a ON l.account_id = a.account_id
        WHERE a.member_id = $mid
          AND s.status = 'ongoing'
        ORDER BY s.due_date ASC
    ";
    $res = $db->query($query);

    $data = [];
    $full_remaining = 0; // total for full payment
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
            $full_remaining += (float) $row['remaining_loan_balance'];
        }
    }

    
    foreach ($data as &$item) {
        $item['full_remaining'] = $full_remaining;
    }

    echo json_encode($data);
} else {
    echo json_encode([]);
}
