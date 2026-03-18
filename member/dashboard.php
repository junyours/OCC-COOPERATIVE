<?php require('../admin/includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Manila');
date_default_timezone_get();
$year = date('Y'); // current year
$today = date("Y-m-d");
$date_add = date('Y-m-d', strtotime('+1 day', strtotime($today)));

$deposit = 0;
$deposit_query = "SELECT SUM(amount) AS total FROM tbl_deposits WHERE date_added BETWEEN '$today' AND '$date_add'";
$result_deposit = $db->query($deposit_query);
$row = $result_deposit->fetch_assoc();
$deposit = $row['total'] ?? 0;

$all_subtotal = 0;
$all_discount = 0;
$all_total = 0;
$total_sales = 0;

$query = "
SELECT 
    sales_no,
    SUM(subtotal) AS subtotal,
    SUM(discount) AS discount,
    SUM(total_amount) AS total_amount
FROM tbl_sales
WHERE sales_date BETWEEN '$today' AND '$date_add'
GROUP BY sales_no
";
$result = $db->query($query);

if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id']) || $_SESSION['is_login_yes'] != 'yes') {
    die("Unauthorized access. Please log in again.");
}

$user_id = (int) $_SESSION['user_id'];


$member_result = $db->query("
    SELECT member_id, cust_id, type 
    FROM tbl_members 
    WHERE user_id = $user_id
    LIMIT 1
");



$member_data = $member_result->fetch_assoc();

$member_id = (int)$member_data['member_id'];
$cust_id   = (int)$member_data['cust_id'];
$member_type = $member_data['type'];

$_SESSION['member_type'] = $member_type;

if ($cust_id <= 0) {
    die("Invalid customer account.");
}


if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['subtotal'];
        $discount = $row['discount'];
        $total_amount = $row['total_amount'];
        $all_subtotal += $subtotal;
        $all_discount += $discount;
        $all_total += $total_amount;
        $total_sales++;
    }
}

$vat_sales = $all_subtotal * 0.12;


$customer_select = "SELECT COUNT(*) AS total_customer FROM tbl_customer";
$customer_result = $db->query($customer_select);
$customer_row = $customer_result->fetch_assoc();
$customer_total = $customer_row['total_customer'];

$user_select = "SELECT COUNT(*) AS total_user FROM tbl_users WHERE usertype != 4";
$user_result = $db->query($user_select);
$user_row = $user_result->fetch_assoc();
$user_total = $user_row['total_user'];

$supplier_select = "SELECT COUNT(*) AS total_supplier FROM tbl_supplier";
$supplier_result = $db->query($supplier_select);
$supplier_row = $supplier_result->fetch_assoc();
$supplier_total = $supplier_row['total_supplier'];

// Get total capital share for this member
$capital_share = $db->query("
    SELECT IFNULL(SUM(t.amount),0) AS total
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'capital_share'
")->fetch_assoc()['total'] ?? 0;

// Get total savings for this member
$savings_total = $db->query("
    SELECT IFNULL(SUM(t.amount),0) AS total
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'savings'
")->fetch_assoc()['total'] ?? 0;

// Get total loan balance for this member
$loan_balance = $db->query("
    SELECT IFNULL(SUM(l.total_due - COALESCE(p.paid_amount, 0)), 0) AS balance
    FROM loans l
    INNER JOIN accounts a ON a.account_id = l.account_id
    LEFT JOIN (
        SELECT loan_id, SUM(amount_paid) AS paid_amount
        FROM loan_payments
        GROUP BY loan_id
    ) p ON l.loan_id = p.loan_id
    WHERE a.member_id = $member_id
      AND l.status IN ('approved', 'ongoing', 'released')
")->fetch_assoc()['balance'] ?? 0;

// Get member statistics
$member_stats = [
    'total_loans' => $db->query("
        SELECT COUNT(*) AS count
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
    ")->fetch_assoc()['count'] ?? 0,
    
    'active_loans' => $db->query("
        SELECT COUNT(*) AS count
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
          AND l.status IN ('approved', 'ongoing', 'released')
    ")->fetch_assoc()['count'] ?? 0,
    
    'paid_loans' => $db->query("
        SELECT COUNT(*) AS count
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
          AND l.status = 'paid'
    ")->fetch_assoc()['count'] ?? 0,
    
    'total_loan_amount' => $db->query("
        SELECT IFNULL(SUM(l.approved_amount), 0) AS total
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
    ")->fetch_assoc()['total'] ?? 0,
    
    'total_paid_amount' => $db->query("
        SELECT IFNULL(SUM(p.amount_paid), 0) AS total
        FROM loan_payments p
        INNER JOIN loans l ON l.loan_id = p.loan_id
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
    ")->fetch_assoc()['total'] ?? 0,
    
    'withdrawal_requests' => $db->query("
        SELECT COUNT(*) AS count
        FROM savings_withdrawal_requests swr
        INNER JOIN accounts a ON a.account_id = swr.account_id
        WHERE a.member_id = $member_id
          AND swr.status = 'pending'
    ")->fetch_assoc()['count'] ?? 0
];

// Get associate member specific data
$associate_data = [
    'recent_savings' => $db->query("
        SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') as month, SUM(t.amount) as total, COUNT(*) as count
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $member_id
          AND at.type_name = 'savings'
          AND t.amount > 0
          AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
        ORDER BY month
    ")->fetch_all(MYSQLI_ASSOC),
    
    'deposit_pattern' => $db->query("
        SELECT 
            DAYOFWEEK(t.transaction_date) as day_of_week,
            CASE 
                WHEN DAYOFWEEK(t.transaction_date) = 1 THEN 'Sunday'
                WHEN DAYOFWEEK(t.transaction_date) = 2 THEN 'Monday'
                WHEN DAYOFWEEK(t.transaction_date) = 3 THEN 'Tuesday'
                WHEN DAYOFWEEK(t.transaction_date) = 4 THEN 'Wednesday'
                WHEN DAYOFWEEK(t.transaction_date) = 5 THEN 'Thursday'
                WHEN DAYOFWEEK(t.transaction_date) = 6 THEN 'Friday'
                WHEN DAYOFWEEK(t.transaction_date) = 7 THEN 'Saturday'
            END as day_name,
            COUNT(*) as count,
            SUM(t.amount) as total
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $member_id
          AND at.type_name = 'savings'
          AND t.amount > 0
          AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        GROUP BY DAYOFWEEK(t.transaction_date), 
                 CASE 
                     WHEN DAYOFWEEK(t.transaction_date) = 1 THEN 'Sunday'
                     WHEN DAYOFWEEK(t.transaction_date) = 2 THEN 'Monday'
                     WHEN DAYOFWEEK(t.transaction_date) = 3 THEN 'Tuesday'
                     WHEN DAYOFWEEK(t.transaction_date) = 4 THEN 'Wednesday'
                     WHEN DAYOFWEEK(t.transaction_date) = 5 THEN 'Thursday'
                     WHEN DAYOFWEEK(t.transaction_date) = 6 THEN 'Friday'
                     WHEN DAYOFWEEK(t.transaction_date) = 7 THEN 'Saturday'
                 END
        ORDER BY DAYOFWEEK(t.transaction_date)
    ")->fetch_all(MYSQLI_ASSOC),
    
    'withdrawal_history' => $db->query("
        SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') as month, SUM(ABS(t.amount)) as total, COUNT(*) as count
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $member_id
          AND at.type_name = 'savings'
          AND t.amount < 0
          AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
        ORDER BY month
    ")->fetch_all(MYSQLI_ASSOC),
    
    'withdrawal_requests' => $db->query("
        SELECT swr.request_id, swr.amount, swr.status, swr.date_requested,
               CASE swr.status
                   WHEN 'pending' THEN 'Pending'
                   WHEN 'approved' THEN 'Approved'
                   WHEN 'rejected' THEN 'Rejected'
                   WHEN 'processed' THEN 'Processed'
                   ELSE swr.status
               END as status_label
        FROM savings_withdrawal_requests swr
        INNER JOIN accounts a ON a.account_id = swr.account_id
        WHERE a.member_id = $member_id
          AND swr.date_requested >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ORDER BY swr.date_requested DESC
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC)
];

// Get chart data
$chart_data = [
    'monthly_loans' => $db->query("
        SELECT DATE_FORMAT(l.application_date, '%Y-%m') as month, COUNT(*) as count, SUM(l.approved_amount) as total
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
          AND l.application_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(l.application_date, '%Y-%m')
        ORDER BY month
    ")->fetch_all(MYSQLI_ASSOC),
    
    'monthly_payments' => $db->query("
        SELECT DATE_FORMAT(p.payment_date, '%Y-%m') as month, SUM(p.amount_paid) as total, COUNT(*) as count
        FROM loan_payments p
        INNER JOIN loans l ON l.loan_id = p.loan_id
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
          AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
        ORDER BY month
    ")->fetch_all(MYSQLI_ASSOC),
    
    'monthly_savings' => $db->query("
        SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') as month, SUM(t.amount) as total
        FROM transactions t
        INNER JOIN accounts a ON a.account_id = t.account_id
        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
        WHERE a.member_id = $member_id
          AND at.type_name = 'savings'
          AND t.amount > 0
          AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
        ORDER BY month
    ")->fetch_all(MYSQLI_ASSOC),
    
    'loan_types' => $db->query("
        SELECT lt.loan_type_name, COUNT(*) as count, SUM(l.approved_amount) as total
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        INNER JOIN loan_types lt ON lt.loan_type_id = l.loan_type_id
        WHERE a.member_id = $member_id
        GROUP BY lt.loan_type_name
    ")->fetch_all(MYSQLI_ASSOC),
    
    'loan_status' => $db->query("
        SELECT l.status, COUNT(*) as count
        FROM loans l
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
        GROUP BY l.status
    ")->fetch_all(MYSQLI_ASSOC),
    
    'payment_breakdown' => $db->query("
        SELECT SUM(p.principal_paid) as principal, SUM(p.interest_paid) as interest, SUM(p.penalty_paid) as penalty
        FROM loan_payments p
        INNER JOIN loans l ON l.loan_id = p.loan_id
        INNER JOIN accounts a ON a.account_id = l.account_id
        WHERE a.member_id = $member_id
    ")->fetch_assoc()
];

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
        /* prevent text from wrapping to next line */
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
<div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="dashboard.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('../admin/includes/sidebar.php'); ?>
        </div>
    </div>
    <!-- /main navbar -->
    <!-- Page container -->
    <div class="page-container">



    </div>


    </div>

    <?php require('../admin/includes/footer-text.php'); ?>
    </div>

    <!-- Page content -->
    <div class="page-content desktop-view">
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header page-header-default"></div>
            <!-- /page header -->


            <!-- Content area -->
            <div class="content">
                <?php if ($member_type === 'associate'): ?>
                    <!-- Associate Member Dashboard - Simple Analytics -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-teal-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-piggy-bank icon-2x text-white"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-white">₱<?= number_format($savings_total, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini text-white-70">Savings Balance</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-blue-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-calendar2 icon-2x text-white"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-white">
                                            <?php 
                                            $monthly_deposits = $db->query("
                                                SELECT COUNT(*) as count
                                                FROM transactions t
                                                INNER JOIN accounts a ON a.account_id = t.account_id
                                                INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                WHERE a.member_id = $member_id
                                                  AND at.type_name = 'savings'
                                                  AND t.amount > 0
                                                  AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                            ")->fetch_assoc()['count'] ?? 0;
                                            echo $monthly_deposits;
                                            ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini text-white-70">Deposits This Month</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-orange-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-arrow-up-right2 icon-2x text-white"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-white">
                                            <?php 
                                            $last_deposit = $db->query("
                                                SELECT t.amount, t.transaction_date
                                                FROM transactions t
                                                INNER JOIN accounts a ON a.account_id = t.account_id
                                                INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                WHERE a.member_id = $member_id
                                                  AND at.type_name = 'savings'
                                                  AND t.amount > 0
                                                ORDER BY t.transaction_date DESC
                                                LIMIT 1
                                            ")->fetch_assoc();
                                            echo $last_deposit ? '₱' . number_format($last_deposit['amount'], 2) : '₱0';
                                            ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini text-white-70">Last Deposit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-red-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-arrow-down-left2 icon-2x text-white"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-white">
                                            <?php 
                                            $total_withdrawn = $db->query("
                                                SELECT SUM(ABS(t.amount)) as total
                                                FROM transactions t
                                                INNER JOIN accounts a ON a.account_id = t.account_id
                                                INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                WHERE a.member_id = $member_id
                                                  AND at.type_name = 'savings'
                                                  AND t.amount < 0
                                            ")->fetch_assoc()['total'] ?? 0;
                                            echo '₱' . number_format($total_withdrawn, 2);
                                            ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini text-white-70">Total Withdrawn</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Simple Analytics Section -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <i class="icon-stats-bars text-teal-400"></i> Simple Savings Overview
                            </h6>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info alert-styled-left alert-arrow-right alpha-info">
                                <button type="button" class="close" data-dismiss="alert"><i class="icon-cross2"></i></button>
                                <h6 class="alert-heading">Savings Summary</h6>
                                Track your savings progress with simple, clear analytics.
                            </div>
                            
                            <!-- Main Savings Chart -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-flat">
                                        <div class="panel-heading">
                                            <h6 class="panel-title">Savings Growth (Last 6 Months)</h6>
                                        </div>
                                        <div class="panel-body">
                                            <canvas id="associateSimpleSavings" width="800" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="panel bg-teal-400">
                                        <div class="panel-body text-center">
                                            <h4 class="no-margin text-white mb-1">
                                                <?php 
                                                $total_deposits = $db->query("
                                                    SELECT COUNT(*) as count
                                                    FROM transactions t
                                                    INNER JOIN accounts a ON a.account_id = t.account_id
                                                    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                    WHERE a.member_id = $member_id
                                                      AND at.type_name = 'savings'
                                                      AND t.amount > 0
                                                ")->fetch_assoc()['count'] ?? 0;
                                                echo $total_deposits;
                                                ?>
                                            </h4>
                                            <span class="text-white-70">Total Deposits</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="panel bg-blue-400">
                                        <div class="panel-body text-center">
                                            <h4 class="no-margin text-white mb-1">
                                                ₱<?= number_format($db->query("
                                                    SELECT AVG(t.amount) as avg_deposit
                                                    FROM transactions t
                                                    INNER JOIN accounts a ON a.account_id = t.account_id
                                                    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                    WHERE a.member_id = $member_id
                                                      AND at.type_name = 'savings'
                                                      AND t.amount > 0
                                                ")->fetch_assoc()['avg_deposit'] ?? 0, 2) ?>
                                            </h4>
                                            <span class="text-white-70">Average Deposit</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="panel bg-purple-400">
                                        <div class="panel-body text-center">
                                            <h4 class="no-margin text-white mb-1">
                                                <?php 
                                                $months_member = $db->query("
                                                    SELECT COUNT(DISTINCT DATE_FORMAT(t.transaction_date, '%Y-%m')) as months
                                                    FROM transactions t
                                                    INNER JOIN accounts a ON a.account_id = t.account_id
                                                    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                    WHERE a.member_id = $member_id
                                                      AND at.type_name = 'savings'
                                                      AND t.amount > 0
                                                ")->fetch_assoc()['months'] ?? 0;
                                                echo $months_member;
                                                ?>
                                            </h4>
                                            <span class="text-white-70">Active Months</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="panel panel-flat">
                                        <div class="panel-heading">
                                            <h6 class="panel-title">Recent Activity</h6>
                                        </div>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $recent_transactions = $db->query("
                                                            SELECT t.transaction_date, t.amount,
                                                                   CASE WHEN t.amount > 0 THEN 'Deposit' ELSE 'Withdrawal' END as type
                                                            FROM transactions t
                                                            INNER JOIN accounts a ON a.account_id = t.account_id
                                                            INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                            WHERE a.member_id = $member_id
                                                              AND at.type_name = 'savings'
                                                            ORDER BY t.transaction_date DESC
                                                            LIMIT 5
                                                        ")->fetch_all(MYSQLI_ASSOC);
                                                        
                                                        foreach ($recent_transactions as $tx): ?>
                                                            <tr>
                                                                <td><?= date('M d, Y', strtotime($tx['transaction_date'])) ?></td>
                                                                <td>
                                                                    <span class="label label-<?= $tx['type'] == 'Deposit' ? 'success' : 'danger' ?>">
                                                                        <?= $tx['type'] ?>
                                                                    </span>
                                                                </td>
                                                                <td class="text-<?= $tx['type'] == 'Deposit' ? 'success' : 'danger' ?>">
                                                                    <?= $tx['type'] == 'Deposit' ? '+' : '-' ?>₱<?= number_format(abs($tx['amount']), 2) ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Regular Member Dashboard - Show all features -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-credit-card  icon-3x text-danger-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold">₱<?= number_format($capital_share, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Capital Share</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body panel-body-accent">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-piggy-bank icon-3x text-success-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold">₱<?= number_format($savings_total, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Savings</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body panel-body-accent">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-credit-card icon-3x text-warning-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold">₱<?= number_format($loan_balance, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Loan Balance</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body panel-body-accent">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-stats-bars icon-3x text-info-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold"><?= $member_stats['active_loans'] ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Active Loans</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <i class="icon-stats-bars text-teal-400"></i> Member Statistics & Analytics
                            </h6>
                        </div>

                        <div class="panel-body">
                            <!-- Chart Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bottom">
                                <li class="active"><a href="#overview-stats" data-toggle="tab"><i class="icon-stats-bars"></i> Overview</a></li>
                                <li><a href="#loan-chart" data-toggle="tab"><i class="icon-graph"></i> Loan Analysis</a></li>
                                <li><a href="#savings-chart" data-toggle="tab"><i class="icon-piggy-bank"></i> Savings Trend</a></li>
                                <li><a href="#payment-chart" data-toggle="tab"><i class="icon-cash"></i> Payment History</a></li>
                            </ul>

                            <div class="tab-content">
                                <!-- Overview Stats Tab -->
                                <div class="tab-pane active" id="overview-stats">
                                    <div class="row">
                                        <!-- Capital Share -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-success-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-coins icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            ₱<?= number_format($capital_share, 2) ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Capital Share
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Savings -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-blue-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-wallet icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            ₱<?= number_format($savings_total, 2) ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Savings
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Loans -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-warning-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-credit-card icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            ₱<?= number_format($loan_balance, 2) ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Loan Balance
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Loans -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-info-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-file-text2 icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            <?= $member_stats['total_loans'] ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Total Loans
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <!-- Paid Loans -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-teal-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-checkmark-circle icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            <?= $member_stats['paid_loans'] ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Paid Loans
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Loan Amount -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-purple-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-cash3 icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            ₱<?= number_format($member_stats['total_loan_amount'], 2) ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Total Borrowed
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Paid Amount -->
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-pink-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-cash icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            ₱<?= number_format($member_stats['total_paid_amount'], 2) ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Total Paid
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pending Withdrawals -->
                                        <?php if ($member_stats['withdrawal_requests'] > 0): ?>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="panel panel-body bg-orange-400 has-bg-image">
                                                <div class="media no-margin">
                                                    <div class="media-left media-middle">
                                                        <i class="icon-clock3 icon-3x opacity-75"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h3 class="no-margin">
                                                            <?= $member_stats['withdrawal_requests'] ?>
                                                        </h3>
                                                        <span class="text-uppercase text-size-mini">
                                                            Pending Withdrawals
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Quick Stats Chart -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Financial Overview</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="financialOverview" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Loan Status Distribution</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="loanStatusChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loan Chart Tab -->
                                <div class="tab-pane" id="loan-chart">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Loan History & Balance Trend</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="loanHistoryChart" width="800" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Loan Types Distribution</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="loanTypeChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Monthly Loan Payments</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="monthlyPaymentsChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Savings Chart Tab -->
                                <div class="tab-pane" id="savings-chart">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Savings Growth Trend</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="savingsGrowthChart" width="800" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Savings vs Capital Share</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="savingsVsCapitalChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Monthly Deposits</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="monthlyDepositsChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Chart Tab -->
                                <div class="tab-pane" id="payment-chart">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Payment History Trend</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="paymentHistoryChart" width="800" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Principal vs Interest Payments</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="principalInterestChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-flat">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">Payment Frequency</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <canvas id="paymentFrequencyChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>

        <!-- /content area -->      
    </div>
    <!-- /main content -->
    </div>
    <!-- /page content -->
    </div>
    <!-- /page container -->
</body>

<?php require('../admin/includes/footer.php'); ?>


<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script type="text/javascript">
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Chart data from PHP
        const chartData = <?= json_encode($chart_data) ?>;
        const memberStats = <?= json_encode($member_stats) ?>;
        
        // Simple Associate Member Chart
        if (document.getElementById('associateSimpleSavings')) {
            const simpleSavingsCtx = document.getElementById('associateSimpleSavings').getContext('2d');
            const associateData = <?= json_encode($associate_data) ?>;
            
            // Calculate cumulative savings
            let cumulativeSavings = 0;
            const cumulativeData = associateData.recent_savings.map(item => {
                cumulativeSavings += parseFloat(item.total);
                return cumulativeSavings;
            });
            
            new Chart(simpleSavingsCtx, {
                type: 'line',
                data: {
                    labels: associateData.recent_savings.map(item => {
                        const date = new Date(item.month + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Savings Balance',
                        data: cumulativeData,
                        borderColor: '#26a69a',
                        backgroundColor: 'rgba(38, 166, 154, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#26a69a',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }, {
                        label: 'Monthly Deposits',
                        data: associateData.recent_savings.map(item => parseFloat(item.total)),
                        backgroundColor: '#2196f3',
                        borderColor: '#2196f3',
                        borderWidth: 0,
                        borderRadius: 4,
                        type: 'bar'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#26a69a',
                            borderWidth: 1,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₱' + context.parsed.y.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Regular Member Charts
        const financialCtx = document.getElementById('financialOverview').getContext('2d');
        new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: ['Capital Share', 'Savings', 'Loan Balance', 'Total Borrowed'],
                datasets: [{
                    label: 'Amount (₱)',
                    data: [<?= $capital_share ?>, <?= $savings_total ?>, <?= $loan_balance ?>, <?= $member_stats['total_loan_amount'] ?>],
                    backgroundColor: ['#10a050', '#2196f3', '#ff9800', '#9c27b0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Loan Status Distribution Chart
        const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
        const loanStatusData = <?= json_encode($chart_data['loan_status']) ?>;
        new Chart(loanStatusCtx, {
            type: 'doughnut',
            data: {
                labels: loanStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                datasets: [{
                    data: loanStatusData.map(item => item.count),
                    backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#f44336', '#9c27b0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Loan History Chart
        const loanHistoryCtx = document.getElementById('loanHistoryChart').getContext('2d');
        const monthlyLoans = <?= json_encode($chart_data['monthly_loans']) ?>;
        const monthlyPayments = <?= json_encode($chart_data['monthly_payments']) ?>;
        
        // Get all months from both datasets
        const allMonths = [...new Set([...monthlyLoans.map(item => item.month), ...monthlyPayments.map(item => item.month)])].sort();
        
        new Chart(loanHistoryCtx, {
            type: 'line',
            data: {
                labels: allMonths.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Loan Amount',
                    data: allMonths.map(month => {
                        const found = monthlyLoans.find(item => item.month === month);
                        return found ? parseFloat(found.total) : 0;
                    }),
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Payments',
                    data: allMonths.map(month => {
                        const found = monthlyPayments.find(item => item.month === month);
                        return found ? parseFloat(found.total) : 0;
                    }),
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
        
        // Loan Types Chart
        const loanTypeCtx = document.getElementById('loanTypeChart').getContext('2d');
        const loanTypes = <?= json_encode($chart_data['loan_types']) ?>;
        new Chart(loanTypeCtx, {
            type: 'pie',
            data: {
                labels: loanTypes.map(item => item.loan_type_name),
                datasets: [{
                    data: loanTypes.map(item => item.count),
                    backgroundColor: ['#2196f3', '#4caf50', '#ff9800', '#f44336', '#9c27b0', '#ff5722'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Monthly Payments Chart
        const monthlyPaymentsCtx = document.getElementById('monthlyPaymentsChart').getContext('2d');
        new Chart(monthlyPaymentsCtx, {
            type: 'bar',
            data: {
                labels: monthlyPayments.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Payment Amount',
                    data: monthlyPayments.map(item => parseFloat(item.total)),
                    backgroundColor: '#4caf50',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Savings Growth Chart
        const savingsGrowthCtx = document.getElementById('savingsGrowthChart').getContext('2d');
        const monthlySavings = <?= json_encode($chart_data['monthly_savings']) ?>;
        
        // Calculate cumulative savings
        let cumulativeSavings = 0;
        const cumulativeData = monthlySavings.map(item => {
            cumulativeSavings += parseFloat(item.total);
            return cumulativeSavings;
        });
        
        new Chart(savingsGrowthCtx, {
            type: 'line',
            data: {
                labels: monthlySavings.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Cumulative Savings',
                    data: cumulativeData,
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Monthly Deposits',
                    data: monthlySavings.map(item => parseFloat(item.total)),
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
        
        // Savings vs Capital Chart
        const savingsVsCapitalCtx = document.getElementById('savingsVsCapitalChart').getContext('2d');
        new Chart(savingsVsCapitalCtx, {
            type: 'doughnut',
            data: {
                labels: ['Savings', 'Capital Share'],
                datasets: [{
                    data: [<?= $savings_total ?>, <?= $capital_share ?>],
                    backgroundColor: ['#2196f3', '#4caf50'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Monthly Deposits Chart
        const monthlyDepositsCtx = document.getElementById('monthlyDepositsChart').getContext('2d');
        new Chart(monthlyDepositsCtx, {
            type: 'bar',
            data: {
                labels: monthlySavings.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Deposit Amount',
                    data: monthlySavings.map(item => parseFloat(item.total)),
                    backgroundColor: '#2196f3',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Payment History Chart
        const paymentHistoryCtx = document.getElementById('paymentHistoryChart').getContext('2d');
        new Chart(paymentHistoryCtx, {
            type: 'line',
            data: {
                labels: monthlyPayments.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Payment Amount',
                    data: monthlyPayments.map(item => parseFloat(item.total)),
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
        
        // Principal vs Interest Chart
        const principalInterestCtx = document.getElementById('principalInterestChart').getContext('2d');
        const paymentBreakdown = <?= json_encode($chart_data['payment_breakdown']) ?>;
        new Chart(principalInterestCtx, {
            type: 'doughnut',
            data: {
                labels: ['Principal', 'Interest', 'Penalty'],
                datasets: [{
                    data: [paymentBreakdown.principal || 0, paymentBreakdown.interest || 0, paymentBreakdown.penalty || 0],
                    backgroundColor: ['#4caf50', '#ff9800', '#f44336'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Payment Frequency Chart
        const paymentFrequencyCtx = document.getElementById('paymentFrequencyChart').getContext('2d');
        new Chart(paymentFrequencyCtx, {
            type: 'bar',
            data: {
                labels: monthlyPayments.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Number of Payments',
                    data: monthlyPayments.map(item => parseInt(item.count)),
                    backgroundColor: '#9c27b0',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>