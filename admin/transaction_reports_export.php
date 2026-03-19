<?php
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get filters from GET parameters
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-d');
$transaction_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : '';
$member_id = isset($_GET['member_id']) ? $_GET['member_id'] : '';
$account_type = isset($_GET['account_type']) ? $_GET['account_type'] : '';

// Build WHERE clause
$where_conditions = ["DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'"];
$params = [];
$types = "";

if (!empty($transaction_type)) {
    $where_conditions[] = "t.transaction_type_id = ?";
    $params[] = $transaction_type;
    $types .= "i";
}

if (!empty($member_id)) {
    $where_conditions[] = "m.member_id = ?";
    $params[] = $member_id;
    $types .= "i";
}

if (!empty($account_type)) {
    $where_conditions[] = "at.account_type_id = ?";
    $params[] = $account_type;
    $types .= "i";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Main query for transaction data
$query = "SELECT 
    t.transaction_id,
    t.amount,
    t.reference_no,
    t.remarks,
    t.transaction_date,
    t.status,
    tt.type_name as transaction_type,
    at.type_name as account_type,
    CONCAT(m.first_name, ' ', m.last_name) as member_name,
    m.member_id,
    a.account_number
    FROM transactions t
    LEFT JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
    LEFT JOIN accounts a ON t.account_id = a.account_id
    LEFT JOIN account_types at ON a.account_type_id = at.account_type_id
    LEFT JOIN tbl_members m ON a.member_id = m.member_id
    $where_clause
    GROUP BY t.transaction_id
    ORDER BY t.transaction_date DESC";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Create CSV content
$filename = "transaction_report_" . date('Y-m-d_H-i-s') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
header('Pragma: public');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, [
    'Transaction ID',
    'Date',
    'Member Name',
    'Account Number',
    'Account Type',
    'Transaction Type',
    'Amount',
    'Reference No.',
    'Remarks',
    'Status'
]);

// CSV Data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['transaction_id'],
        date('Y-m-d H:i:s', strtotime($row['transaction_date'])),
        $row['member_name'] ?? 'N/A',
        $row['account_number'] ?? 'N/A',
        $row['account_type'] ?? 'N/A',
        $row['transaction_type'] ?? 'N/A',
        $row['amount'],
        $row['reference_no'] ?? 'N/A',
        $row['remarks'] ?? 'N/A',
        $row['status']
    ]);
}

fclose($output);
exit();
?>
