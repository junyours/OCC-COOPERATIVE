<?php require('includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../db_connect.php');

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

// Main query for transaction reports
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

// Get summary statistics
$summary_query = "SELECT 
    COUNT(DISTINCT t.transaction_id) as total_transactions,
    SUM(CASE WHEN t.transaction_type_id IN (1, 3) THEN t.amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN t.transaction_type_id = 2 THEN t.amount ELSE 0 END) as total_withdrawals,
    SUM(CASE WHEN t.transaction_type_id = 5 THEN t.amount ELSE 0 END) as total_loan_payments,
    COUNT(DISTINCT a.member_id) as unique_members
    FROM transactions t
    LEFT JOIN accounts a ON t.account_id = a.account_id
    LEFT JOIN tbl_members m ON a.member_id = m.member_id
    $where_clause";

$summary_stmt = $db->prepare($summary_query);
if (!empty($params)) {
    $summary_stmt->bind_param($types, ...$params);
}
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary = $summary_result->fetch_assoc();

// Get transaction types for filter
$types_query = "SELECT transaction_type_id, type_name FROM transaction_types ORDER BY type_name";
$types_result = $db->query($types_query);

// Get account types for filter
$account_types_query = "SELECT account_type_id, type_name FROM account_types ORDER BY type_name";
$account_types_result = $db->query($account_types_query);

// Get members for filter
$members_query = "SELECT member_id, CONCAT(first_name, ' ', last_name) as member_name FROM tbl_members WHERE status = 'active' ORDER BY first_name, last_name";
$members_result = $db->query($members_query);
?>

<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        font-weight: 800;
        color: white;
        text-decoration: none;
        font-size: 16px;
        line-height: 1.2;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        margin-right: 12px;
        border-radius: 20px;
    }

    .navbar-brand span {
        white-space: nowrap;
    }
    
    .panel {
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .panel-body {
        padding: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .date-filter {
        background: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .breadcrumb-elements {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .breadcrumb-elements li {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .btn-rounded {
        border-radius: 3px;
    }
    
    .panel-white {
        background: white;
        border: 1px solid #ddd;
    }
    
    .border-top-xlg {
        border-top-width: 4px !important;
    }
    
    .border-top-teal-400 {
        border-top-color: #26a69a !important;
    }
    
    .text-teal-400 {
        color: #26a69a !important;
    }
</style>

<body class="layout-boxed navbar-top">

   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../images/main_logo.jpg" alt="Logo">
                <span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span>
            </a>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="page-header page-header-default">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Transaction Reports</span></h4>
                    </div>
                </div>
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                        <li class="active"><i class="icon-exchange-alt position-left"></i> Transaction Reports</li>
                    </ul>
                </div>
            </div>
            
            <div class="content">

<!-- Summary Cards -->
<div class="row">
    <div class="col-sm-6 col-md-3">
        <div class="panel panel-body bg-info-400 has-bg-image">
            <div class="media no-margin">
                <div class="media-left media-middle">
                    <i class="icon-exchange-alt icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="no-margin"><?php echo number_format($summary['total_transactions']); ?></h3>
                    <span class="text-uppercase text-size-mini">Total Transactions</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
        <div class="panel panel-body bg-success-400 has-bg-image">
            <div class="media no-margin">
                <div class="media-left media-middle">
                    <i class="icon-arrow-down icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="no-margin">₱<?php echo number_format($summary['total_deposits'], 2); ?></h3>
                    <span class="text-uppercase text-size-mini">Total Deposits</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
        <div class="panel panel-body bg-warning-400 has-bg-image">
            <div class="media no-margin">
                <div class="media-left media-middle">
                    <i class="icon-arrow-up icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="no-margin">₱<?php echo number_format($summary['total_withdrawals'], 2); ?></h3>
                    <span class="text-uppercase text-size-mini">Total Withdrawals</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
        <div class="panel panel-body bg-primary-400 has-bg-image">
            <div class="media no-margin">
                <div class="media-left media-middle">
                    <i class="icon-money-check-alt icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="no-margin">₱<?php echo number_format($summary['total_loan_payments'], 2); ?></h3>
                    <span class="text-uppercase text-size-mini">Loan Payments</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="panel panel-body ">
    <div>
        <form class="heading-form" method="GET">
            <ul class="breadcrumb-elements" style="float:left">
                <li style="padding-top: 2px;padding-right: 2px">
                    <div class="input-group">
                        <span class="input-group-addon" style="padding: 5px 12px;">
                            <i class="icon-calendar"></i>
                        </span>
                        <input style="width: 180px" type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                </li>
                <li style="padding-top: 2px;padding-right: 2px">
                    <div class="input-group">
                        <span class="input-group-addon" style="padding: 5px 12px;">
                            <i class="icon-calendar"></i>
                        </span>
                        <input style="width: 180px" type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                </li>
                <li style="padding-top: 2px;padding-right: 2px">
                    <select name="transaction_type" class="form-control" style="width: 150px">
                        <option value="">All Types</option>
                        <?php while($type = $types_result->fetch_assoc()): ?>
                            <option value="<?php echo $type['transaction_type_id']; ?>" <?php echo ($transaction_type == $type['transaction_type_id']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($type['type_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </li>
                <li style="padding-top: 2px;padding-right: 2px">
                    <select name="account_type" class="form-control" style="width: 150px">
                        <option value="">All Accounts</option>
                        <?php while($acc_type = $account_types_result->fetch_assoc()): ?>
                            <option value="<?php echo $acc_type['account_type_id']; ?>" <?php echo ($account_type == $acc_type['account_type_id']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($acc_type['type_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </li>
                <li style="padding-top: 2px;padding-right: 2px">
                    <select name="member_id" class="form-control" style="width: 200px">
                        <option value="">All Members</option>
                        <?php while($member = $members_result->fetch_assoc()): ?>
                            <option value="<?php echo $member['member_id']; ?>" <?php echo ($member_id == $member['member_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['member_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </li>
                <li data-toggle="tooltip" title="Filter Report" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400"><b><i class="icon-search4"></i></b></button></li>
                <li data-toggle="tooltip" title="Clear Filter" style="padding-top: 2px;padding-right: 2px"><a href="transaction_reports.php" class="btn bg-slate-400"><b><i class="icon-filter4"></i></b></a></li>
                <li data-toggle="tooltip" title="Export Excel" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="window.location.href='transaction_reports_export.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&transaction_type=<?php echo $transaction_type; ?>&member_id=<?php echo $member_id; ?>&account_type=<?php echo $account_type; ?>'" class="btn bg-success-700"><b><i class="icon-file-excel"></i></b></button></li>
                <li data-toggle="tooltip" title="Analytics" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="window.location.href='transaction_analytics.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>'" class="btn bg-blue-700"><b><i class="icon-chart-pie"></i></b></button></li>
            </ul>
        </form>
    </div>
</div>

            <!-- Transactions Table -->
<div class="panel panel-white border-top-xlg border-top-teal-400">
    <div class="panel-heading">
        <h6 class="panel-title"><i class="icon-exchange-alt text-teal-400"></i> Transaction Details<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
        <div class="heading-elements">
            <span class="badge badge-default"><?php echo $result->num_rows; ?> Records</span>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="transactionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Member Name</th>
                        <th>Account Number</th>
                        <th>Account Type</th>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Reference No.</th>
                        <th>Remarks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['transaction_date'])); ?></td>
                                <td><?php echo $row['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['member_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['account_number'] ?? 'N/A'); ?></td>
                                <td><?php echo ucfirst($row['account_type'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo getTransactionTypeBadge($row['transaction_type']); ?>">
                                        <?php echo ucfirst($row['transaction_type']); ?>
                                    </span>
                                </td>
                                <td class="text-right">₱<?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['reference_no'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ($row['status'] == 'active') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
</div>

<?php
function getTransactionTypeBadge($type) {
    switch($type) {
        case 'deposit': return 'success';
        case 'withdrawal': return 'warning';
        case 'loan_payment': return 'info';
        case 'loan_release': return 'primary';
        case 'capital_share': return 'secondary';
        case 'cancelled_loan': return 'danger';
        default: return 'light';
    }
}
?>

<script>
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ordering": true,
        "info": true,
        "paging": true,
        "pageLength": 25
    });
});
</script>

<?php require('includes/footer.php'); ?>
