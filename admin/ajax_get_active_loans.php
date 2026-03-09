<?php
require('db_connect.php');
$mid = intval($_GET['member_id'] ?? 0);

$data = ['schedules' => [], 'loans' => []];

// Get active schedules
$scheds = $db->query("SELECT s.*, l.account_id FROM loan_schedule s 
                      JOIN loans l ON s.loan_id = l.loan_id 
                      WHERE l.member_id = $mid AND s.status IN ('pending', 'ongoing') 
                      ORDER BY s.due_date ASC");
while ($row = $scheds->fetch_assoc()) $data['schedules'][] = $row;

// Get approved loans (for cancellation)
$loans = $db->query("SELECT loan_id, approved_amount, status FROM loans WHERE member_id = $mid AND status = 'approved'");
while ($row = $loans->fetch_assoc()) $data['loans'][] = $row;

echo json_encode($data);
