<?php
require('db_connect.php');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="loan_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, [
    'Loan No.',
    'Member Name',
    'Requested Amount',
    'Approved Amount',
    'Disbursed Amount',
    'Term',
    'Interest Rate',
    'Status',
    'Total Due',
    'Total Paid',
    'Repayment Progress',
    'Application Date',
    'Released Date'
]);

// Get filter parameters
$member_filter = '';
$status_filter = '';
$date_filter = '';

if (!empty($_GET['membername'])) {
    $membername = $db->real_escape_string($_GET['membername']);
    $member_filter = " AND (CONCAT(m.first_name, ' ', m.last_name) LIKE '%$membername%' OR m.first_name LIKE '%$membername%' OR m.last_name LIKE '%$membername%') ";
}

if (!empty($_GET['status'])) {
    $status = $db->real_escape_string($_GET['status']);
    $status_filter = " AND l.status = '$status' ";
}

if (!empty($_GET['date_from'])) {
    $date_from = $db->real_escape_string($_GET['date_from']);
    $date_filter .= " AND DATE(l.application_date) >= '$date_from' ";
}

if (!empty($_GET['date_to'])) {
    $date_to = $db->real_escape_string($_GET['date_to']);
    $date_filter .= " AND DATE(l.application_date) <= '$date_to' ";
}

// Query for export data
$export_sql = "
SELECT 
    l.loan_id,
    CONCAT(m.last_name, ', ', m.first_name) AS member_name,
    l.requested_amount,
    l.approved_amount,
    l.interest_rate,
    l.term_value,
    l.term_unit,
    l.status,
    l.total_due,
    l.application_date,
    l.released_date,
    
    COALESCE(SUM(
        CASE 
            WHEN tt.type_name = 'loan_payment' 
            THEN t.amount 
            ELSE 0 
        END
    ), 0) AS total_paid,

    l.total_due AS total_released

FROM loans l
LEFT JOIN accounts a ON a.account_id = l.account_id
LEFT JOIN tbl_members m ON m.member_id = a.member_id
LEFT JOIN transactions t ON t.account_id = l.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
WHERE 1=1
$member_filter
$status_filter
$date_filter
GROUP BY l.loan_id, l.requested_amount, l.approved_amount, l.interest_rate, l.term_value, l.term_unit, l.status, l.total_due, l.application_date, l.released_date, m.first_name, m.last_name
ORDER BY l.application_date DESC
";

$export_query = $db->query($export_sql);

// Export data
while ($row = $export_query->fetch_assoc()) {
    $progress = ($row['total_due'] > 0) 
        ? round(($row['total_paid'] / $row['total_due']) * 100, 2) 
        : 0;
    
    fputcsv($output, [
        $row['loan_id'],
        $row['member_name'],
        number_format($row['requested_amount'], 2),
        number_format($row['approved_amount'], 2),
        number_format($row['total_disbursed'], 2),
        $row['term_value'] . ' ' . $row['term_unit'],
        $row['interest_rate'] . '%',
        ucfirst($row['status']),
        number_format($row['total_due'], 2),
        number_format($row['total_paid'], 2),
        $progress . '%',
        $row['application_date'],
        $row['released_date']
    ]);
}

fclose($output);
exit;
?>
