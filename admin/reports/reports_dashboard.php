<?php
require('../db_connect.php');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// 2. FILTER LOGIC
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Default to start of month
$date_to   = $_GET['date_to'] ?? date('Y-m-d');
$acc_type  = $_GET['acc_type'] ?? 'all';
$status    = $_GET['status'] ?? 'active';

// 3. FETCH SUMMARY TOTALS (Based on filters)
$summary_query = "SELECT 
    SUM(CASE WHEN transaction_type_id = 1 AND status = 'active' THEN amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN transaction_type_id = 3 AND status = 'active' THEN amount ELSE 0 END) as total_capital,
    SUM(CASE WHEN transaction_type_id = 2 AND status = 'active' THEN amount ELSE 0 END) as total_withdrawals
    FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to'";
$summary = $db->query($summary_query)->fetch_assoc();

// 4. MAIN REPORT QUERY
$where_clauses = ["DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to'"];

if ($acc_type !== 'all') {
    $where_clauses[] = "a.account_type_id = " . intval($acc_type);
}
if ($status !== 'all') {
    $where_clauses[] = "t.status = '" . $db->real_escape_string($status) . "'";
}

$where_sql = implode(" AND ", $where_clauses);

$main_sql = "SELECT 
     t.*, 
    a.member_id, 
    at.type_name as account_category,
    tt.type_name as transaction_action,
    m.first_name,
    m.last_name
    FROM transactions t
    JOIN accounts a ON t.account_id = a.account_id
    JOIN account_types at ON a.account_type_id = at.account_type_id
    JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
    JOIN tbl_members m ON a.member_id = m.member_id
    WHERE $where_sql
    ORDER BY t.transaction_date DESC";

$results = $db->query($main_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coop Management - Professional Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50 p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Financial Reports</h1>
                <p class="text-gray-500">System Transactions & Audit Logs</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="flex items-center gap-2 bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:bg-gray-50">
                    <i class="fas fa-print text-gray-600"></i> Print Report
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500 uppercase">Savings Deposits</p>
                <h3 class="text-2xl font-bold text-emerald-600">₱ <?php echo number_format($summary['total_deposits'], 2); ?></h3>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500 uppercase">Capital Shares</p>
                <h3 class="text-2xl font-bold text-blue-600">₱ <?php echo number_format($summary['total_capital'], 2); ?></h3>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500 uppercase">Withdrawals</p>
                <h3 class="text-2xl font-bold text-red-500">₱ <?php echo number_format(abs($summary['total_withdrawals']), 2); ?></h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">From</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">To</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">Account Category</label>
                    <select name="acc_type" class="w-full border-gray-200 rounded-lg text-sm">
                        <option value="all">All Accounts</option>
                        <option value="1" <?php if ($acc_type == '1') echo 'selected'; ?>>Savings</option>
                        <option value="2" <?php if ($acc_type == '2') echo 'selected'; ?>>Capital Share</option>
                        <option value="3" <?php if ($acc_type == '3') echo 'selected'; ?>>Loan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">Status</label>
                    <select name="status" class="w-full border-gray-200 rounded-lg text-sm">
                        <option value="all">All Status</option>
                        <option value="active" <?php if ($status == 'active') echo 'selected'; ?>>Active</option>
                        <option value="voided" <?php if ($status == 'voided') echo 'selected'; ?>>Voided</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition duration-200">
                    Generate Report
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Timestamp</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Reference</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Member / Account</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-right">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if ($results->num_rows > 0): ?>
                        <?php while ($row = $results->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition duration-150 <?php echo ($row['status'] == 'voided') ? 'opacity-50 grayscale bg-gray-50' : ''; ?>">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo date('M d, Y', strtotime($row['transaction_date'])); ?><br>
                                    <span class="text-[10px] text-gray-400 uppercase"><?php echo date('h:i A', strtotime($row['transaction_date'])); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                                        <?php echo $row['reference_no']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-gray-800">Member #<?php echo $row['member_id']; ?></p>
                                    <p class="text-[11px] text-gray-500 uppercase"><?php echo $row['account_category']; ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo $row['transaction_action']; ?></td>
                                <td class="px-6 py-4 text-sm font-bold text-right <?php echo ($row['amount'] < 0) ? 'text-red-600' : 'text-emerald-600'; ?>">
                                    <?php echo ($row['amount'] < 0) ? '-' : ''; ?> ₱<?php echo number_format(abs($row['amount']), 2); ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($row['status'] == 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Voided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">No transactions found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>