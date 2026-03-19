<?php
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get date range from GET parameters
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-d');

// Daily transaction totals for chart
$daily_query = "SELECT 
    DATE(t.transaction_date) as date,
    SUM(CASE WHEN t.transaction_type_id IN (1, 3) THEN t.amount ELSE 0 END) as deposits,
    SUM(CASE WHEN t.transaction_type_id = 2 THEN t.amount ELSE 0 END) as withdrawals,
    SUM(CASE WHEN t.transaction_type_id = 5 THEN t.amount ELSE 0 END) as loan_payments,
    COUNT(DISTINCT t.transaction_id) as total_transactions
    FROM transactions t
    WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(t.transaction_date)
    ORDER BY date ASC";

$daily_result = $db->query($daily_query);

$daily_data = [];
while ($row = $daily_result->fetch_assoc()) {
    $daily_data[] = [
        'date' => $row['date'],
        'deposits' => (float)$row['deposits'],
        'withdrawals' => (float)$row['withdrawals'],
        'loan_payments' => (float)$row['loan_payments'],
        'total_transactions' => (int)$row['total_transactions']
    ];
}

// Transaction type distribution
$type_query = "SELECT 
    tt.type_name,
    COUNT(DISTINCT t.transaction_id) as count,
    SUM(t.amount) as total_amount
    FROM transactions t
    LEFT JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
    WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
    GROUP BY t.transaction_type_id, tt.type_name
    ORDER BY count DESC";

$type_result = $db->query($type_query);

$type_data = [];
while ($row = $type_result->fetch_assoc()) {
    $type_data[] = [
        'type' => $row['type_name'],
        'count' => (int)$row['count'],
        'total_amount' => (float)$row['total_amount']
    ];
}

// Account type distribution
$account_query = "SELECT 
    at.type_name,
    COUNT(DISTINCT t.transaction_id) as count,
    SUM(t.amount) as total_amount
    FROM transactions t
    LEFT JOIN accounts a ON t.account_id = a.account_id
    LEFT JOIN account_types at ON a.account_type_id = at.account_type_id
    WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
    GROUP BY at.account_type_id, at.type_name
    ORDER BY count DESC";

$account_result = $db->query($account_query);

$account_data = [];
while ($row = $account_result->fetch_assoc()) {
    $account_data[] = [
        'account_type' => $row['type_name'],
        'count' => (int)$row['count'],
        'total_amount' => (float)$row['total_amount']
    ];
}

// Monthly trends
$monthly_query = "SELECT 
    DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
    SUM(t.amount) as total_amount,
    COUNT(DISTINCT t.transaction_id) as transaction_count
    FROM transactions t
    WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
    ORDER BY month ASC";

$monthly_result = $db->query($monthly_query);

$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = [
        'month' => $row['month'],
        'total_amount' => (float)$row['total_amount'],
        'transaction_count' => (int)$row['transaction_count']
    ];
}

// Top members by transaction volume
$members_query = "SELECT 
    CONCAT(m.first_name, ' ', m.last_name) as member_name,
    m.member_id,
    COUNT(DISTINCT t.transaction_id) as transaction_count,
    SUM(t.amount) as total_amount
    FROM transactions t
    LEFT JOIN accounts a ON t.account_id = a.account_id
    LEFT JOIN tbl_members m ON a.member_id = m.member_id
    WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
    GROUP BY m.member_id, m.first_name, m.last_name
    ORDER BY transaction_count DESC
    LIMIT 10";

$members_result = $db->query($members_query);

$members_data = [];
while ($row = $members_result->fetch_assoc()) {
    $members_data[] = [
        'member_name' => $row['member_name'],
        'member_id' => (int)$row['member_id'],
        'transaction_count' => (int)$row['transaction_count'],
        'total_amount' => (float)$row['total_amount']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'daily_data' => $daily_data,
    'type_distribution' => $type_data,
    'account_distribution' => $account_data,
    'monthly_trends' => $monthly_data,
    'top_members' => $members_data
]);
?>
