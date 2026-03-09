<?php require('includes/header.php'); ?>

<?php

if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}

$cust_id = (int)$_GET['cust_id'];

/* 
DATE RANGE FILTER
 */

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-01-01');
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : date('Y-12-31');


$year = date('Y', strtotime($date_from));



$customer_result = $db->query("
    SELECT * 
    FROM tbl_customer 
    WHERE cust_id = $cust_id
");
$customer = $customer_result->fetch_assoc();

$customer_name_safe = preg_replace('/[^A-Za-z0-9_\- ]/', '', $customer['name']);

/* 
GET MEMBER ID FROM CUSTOMER ID
*/

$member_result = $db->query("
    SELECT member_id
    FROM tbl_members
    WHERE cust_id = $cust_id
    LIMIT 1
");

if (!$member_result) {

    die("Member query error: " . $db->error);
}

if ($member_result->num_rows == 0) {

    die("No member found for this customer.");
}

$member_data = $member_result->fetch_assoc();

$member_id = (int)$member_data['member_id'];

$member_type_result = $db->query("
    SELECT type 
    FROM tbl_members 
    WHERE member_id = $member_id
    LIMIT 1
");

$member_type_data = $member_type_result->fetch_assoc();
$member_type = $member_type_data['type'] ?? 'associate';

$capital_result = $db->query("
    SELECT SUM(t.amount) AS total_capital
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
    AND at.type_name = 'capital_share'
    AND DATE(t.transaction_date)
    BETWEEN '$date_from' AND '$date_to'
");

$capital = $capital_result->fetch_assoc();


$contributions = $db->query("
    SELECT 
        t.transaction_date AS contribution_date,
        t.amount,
        t.reference_no,
        tt.type_name AS transaction_type
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    INNER JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'capital_share'
      AND tt.type_name IN  ('deposit', 'capital_share')
      AND DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to'
    ORDER BY t.created_at DESC
");



$cash_sales = $db->query("
    SELECT 
        s.sales_no,
        s.sales_date,
        SUM(s.quantity_order) AS total_quantity,
        MAX(s.total_amount) AS total_amount
    FROM tbl_sales s
    
    WHERE s.sales_type = 1
      AND s.cust_id = $cust_id
        AND DATE(s.sales_date)
BETWEEN '$date_from' AND '$date_to'
    GROUP BY s.sales_no, s.sales_date
    ORDER BY s.sales_date DESC
");

$charge_sales = $db->query("
    SELECT 
        s.sales_no,
        s.sales_date,

        SUM(s.quantity_order) AS total_quantity,
        MAX(s.total_amount) AS total_amount,

        COALESCE(pay.total_paid, 0) AS payments_made,

        MAX(s.total_amount) - COALESCE(pay.total_paid, 0) AS balance

    FROM tbl_sales s

    
    LEFT JOIN (
        SELECT sales_no, SUM(amount_paid) AS total_paid
        FROM tbl_payments
        GROUP BY sales_no
    ) pay ON pay.sales_no = s.sales_no

    WHERE s.sales_type = 0
      AND s.sales_status != 3
      AND s.cust_id = $cust_id
     AND DATE(s.sales_date)
BETWEEN '$date_from' AND '$date_to'

    GROUP BY s.sales_no, s.sales_date, pay.total_paid
    ORDER BY s.sales_date DESC
");


$payments = $db->query("
    SELECT *
    FROM tbl_payments
    WHERE sales_no IN (
        SELECT sales_no
        FROM tbl_sales
        WHERE cust_id = $cust_id
          AND YEAR(sales_date) = $year
    )
    AND YEAR(date_payment) = $year
    ORDER BY date_payment DESC
");
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


    /* Mobile App Style */
    @media (max-width:768px) {
        .content-wrapper {
            padding: 10px;
        }

        .panel {
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .06);
        }

        .col-sm-6.col-md-3 {
            margin-bottom: 10px;
        }

        .panel .icon-3x {
            font-size: 28px !important;
        }

        .table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        .navbar-nav {
            display: none;
        }

        body {
            padding-bottom: 75px;
        }
    }

    .mobile-bottom-nav {
        display: none;
    }

    @media (max-width:768px) {
        .mobile-bottom-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #ddd;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 9999;
        }

        .mobile-bottom-nav a {
            text-align: center;
            font-size: 11px;
            color: #444;
        }

        .mobile-bottom-nav i {
            display: block;
            font-size: 20px;
            margin-bottom: 2px;
        }

        .mobile-bottom-nav a.active {
            color: #26a69a;
        }
    }

    /* Smooth transition for tabs */
    .nav-tabs>li>a {
        transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
    }

    /* Hover effect */
    .nav-tabs>li>a:hover {
        transform: scale(1.05);
        /* slightly bigger */
        background-color: #b0c4de;
        /* subtle highlight, you can change color */
        color: #26a69a !important;
        /* ensure text is readable */
    }

    /* Active tab pop effect */
    .nav-tabs>li.active>a {
        transform: scale(1.05);
        font-weight: bold;
        color: #fff !important;
        background-color: #26a69a !important;
        /* your main tab color */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
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
                            <h4>
                                <i class="icon-user position-left"></i>
                                Transaction History - <?= htmlspecialchars($customer['name']); ?>
                                (<?= $year; ?>)
                            </h4>
                        </div>

                    </div>

                    <div class="breadcrumb-line">

                        <ul class="breadcrumb">
                            <li>
                                <a href="customer.php">
                                    <i class="icon-users"></i>Member
                                </a>
                            </li>

                            <li class="active">Transaction History</li>
                        </ul>



                        <ul class="breadcrumb-elements">


                            <li>

                                <form method="GET" class="form-inline" style="display:flex; align-items:center; gap:5px;">
                                    <input type="hidden" name="cust_id" value="<?= $cust_id ?>">

                                    <label style="margin:0;">From:</label>
                                    <input type="date" name="date_from" value="<?= $date_from ?>" class="form-control">

                                    <label style="margin:0;">To:</label>
                                    <input type="date" name="date_to" value="<?= $date_to ?>" class="form-control">

                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="customer_history.php?cust_id=<?= $cust_id ?>" class="btn btn-default">Reset</a>
                                </form>

                            </li>



                            <li>

                                <a href="#" id="btn-download-pdf">

                                    <i class="icon-file-pdf text-teal-400"></i>

                                    Download PDF

                                </a>

                            </li>


                        </ul>

                    </div>

                    <div class="content" id="history-content">
                        <div class="panel panel-flat">
                            <div class="panel-body">
                                <div class="tabbable">
                                    <ul class="nav nav-tabs bg-slate nav-justified">

                                        <?php if ($member_type !== 'associate'): ?>
                                            <li class="active"><a href="#info" data-toggle="tab">Information</a></li>
                                            <li><a href="#capital" data-toggle="tab">Capital Share</a></li>
                                            <li><a href="#savings" data-toggle="tab">Savings</a></li>
                                            <li><a href="#loan" data-toggle="tab">Loans</a></li>
                                            <li><a href="#cash" data-toggle="tab">Cash Purchases</a></li>
                                            <li><a href="#charge" data-toggle="tab">Charge Sales</a></li>
                                        <?php endif; ?>
                                        <?php if ($member_type == 'associate'): ?>
                                            <li class="active"><a href="#info" data-toggle="tab">Information</a></li>
                                            <li><a href="#savings" data-toggle="tab">Savings</a></li>
                                        <?php endif; ?>
                                    </ul>

                                    <div class="tab-content">


                                        <div class="tab-pane active" id="info">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Information</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <?php

                                                    $total_cash = 0;
                                                    $total_cash_result = $db->query("
                                                SELECT MAX(s.total_amount) AS total_amount
                                                FROM tbl_sales s
                                                WHERE s.sales_type = 1
                                                AND s.cust_id = $cust_id
                                                AND DATE(s.sales_date)
                                                BETWEEN '$date_from' AND '$date_to'
                                                GROUP BY s.sales_no
                                                ");
                                                    while ($row = $total_cash_result->fetch_assoc()) {
                                                        $total_cash += $row['total_amount'];
                                                    }

                                                    $total_charge_paid = 0;
                                                    $total_charge_paid_result = $db->query("
                                                SELECT MAX(s.total_amount) - COALESCE(pay.total_paid,0) AS balance,
                                                COALESCE(pay.total_paid,0) AS payments_made
                                                FROM tbl_sales s
                                                LEFT JOIN (
                                                SELECT sales_no, SUM(amount_paid) AS total_paid
                                                FROM tbl_payments
                                                GROUP BY sales_no
                                                ) pay ON pay.sales_no = s.sales_no
                                                WHERE s.sales_type = 0
                                                AND s.sales_status != 3
                                                AND s.cust_id = $cust_id
                                                AND DATE(s.sales_date)
                                                BETWEEN '$date_from' AND '$date_to'
                                                   GROUP BY s.sales_no, pay.total_paid
                                                 ");
                                                    while ($row = $total_charge_paid_result->fetch_assoc()) {
                                                        $total_charge_paid += $row['payments_made'];
                                                    }

                                                    $savings_result = $db->query("
                                                        SELECT SUM(t.amount) AS total
                                                        FROM transactions t
                                                        INNER JOIN accounts a ON a.account_id = t.account_id
                                                        INNER JOIN account_types at ON at.account_type_id = a.account_type_id
                                                        WHERE a.member_id = $member_id
                                                        AND at.type_name = 'savings'
                                                        AND DATE(t.transaction_date)
                                                        BETWEEN '$date_from' AND '$date_to'
                                                    ");

                                                    if ($savings_result && $savings_result->num_rows > 0) {
                                                        $row = $savings_result->fetch_assoc();
                                                        $total_savings = $row['total'] ?? 0;
                                                    }
                                                    ?>

                                                    <table class="table table-bordered">
                                                        <?php if ($member_type !== 'associate'): ?>
                                                            <tr>
                                                                <td>Name</td>
                                                                <td><?= htmlspecialchars($customer['name']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Address</td>
                                                                <td><?= htmlspecialchars($customer['address']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Contact</td>
                                                                <td><?= htmlspecialchars($customer['contact']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Capital Share (<?= $year; ?>)</td>
                                                                <td>₱<?= number_format($capital['total_capital'] ?? 0, 2); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Savings Balance (<?= $year; ?>)</td>
                                                                <td>
                                                                    ₱<?= number_format($total_savings ?? 0, 2); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Cash Purchases (<?= $year; ?>)</td>
                                                                <td>₱<?= number_format($total_cash, 2); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Paid (Charge Sales <?= $year; ?>)</td>
                                                                <td>₱<?= number_format($total_charge_paid, 2); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <?php if ($member_type == 'associate'): ?>
                                                            <tr>
                                                                <td>Name</td>
                                                                <td><?= htmlspecialchars($customer['name']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Address</td>
                                                                <td><?= htmlspecialchars($customer['address']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Contact</td>
                                                                <td><?= htmlspecialchars($customer['contact']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Savings Balance (<?= $year; ?>)</td>
                                                                <td>
                                                                    ₱<?= number_format($total_savings ?? 0, 2); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CAPITAL SHARE TAB -->
                                        <div class="tab-pane" id="capital">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">
                                                        <i class="icon-piggy-bank position-left text-teal-400"></i>
                                                        Capital Share
                                                        <small style="margin-left:10px; color:#777;">
                                                            (<?= date('M d, Y', strtotime($date_from)) ?> to <?= date('M d, Y', strtotime($date_to)) ?>)
                                                        </small>
                                                    </h6>
                                                </div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr style="background:#eee">
                                                                <th>Reference</th>
                                                                <th>Date</th>
                                                                <th class="text-right">Credit</th>
                                                                <th class="text-right">Debit</th>
                                                                <th class="text-right">Balance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $hasContrib = false;
                                                            $running_balance = 0;

                                                            while ($c = $contributions->fetch_assoc()) {
                                                                $hasContrib = true;

                                                                $reference_no = htmlspecialchars($c['reference_no']);
                                                                $date = date('M d, Y', strtotime($c['contribution_date']));

                                                                $credit = 0;
                                                                $debit = 0;

                                                                if (in_array($c['transaction_type'], ['deposit', 'capital_share'])) {
                                                                    // It's a deposit type → add to balance
                                                                    $credit = $c['amount'];
                                                                    $running_balance += $credit;
                                                                } elseif ($c['transaction_type'] === 'withdrawal') {
                                                                    // It's a withdrawal → subtract from balance
                                                                    $debit = $c['amount'];
                                                                    $running_balance -= $debit;
                                                                }

                                                                echo "
                        <tr>
                            <td>
                                <a href='javascript:void(0);'
                                   onclick='view_capital_receipt(this)'
                                   data-reference='{$reference_no}'
                                   style='font-weight:600; color:#26a69a;'>
                                   {$reference_no}
                                </a>
                            </td>
                            <td>{$date}</td>
                            <td class='text-right'>" . ($credit ? "₱" . number_format($credit, 2) : '') . "</td>
                            <td class='text-right'>" . ($debit ? "₱" . number_format($debit, 2) : '') . "</td>
                            <td class='text-right'>₱" . number_format($running_balance, 2) . "</td>
                        </tr>";
                                                            }

                                                            if (!$hasContrib) {
                                                                echo "
                        <tr>
                            <td colspan='5' class='text-center'>No contributions found for this period.</td>
                        </tr>";
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="tab-pane" id="savings">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">
                                                        <i class="icon-wallet position-left text-teal-400"></i> Savings (<?= $year; ?>)
                                                    </h6>
                                                </div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr style="background:#eee">
                                                                <th>Reference</th>
                                                                <th>Date</th>
                                                                <th class="text-right text-success">Credit</th>
                                                                <th class="text-right text-danger">Debit</th>
                                                                <th class="text-right">Balance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $balance = 0;
                                                            $transactions = [];

                                                            // Fetch all savings transactions (oldest first for correct balance)
                                                            $savings_result = $db->query("
                        SELECT 
                            t.transaction_date,
                            t.amount,
                            t.reference_no,
                            tt.type_name,
                            t.transaction_id,
                            t.created_at
                        FROM transactions t
                        INNER JOIN accounts a 
                            ON a.account_id = t.account_id
                        INNER JOIN account_types at 
                            ON at.account_type_id = a.account_type_id
                        INNER JOIN transaction_types tt
                            ON tt.transaction_type_id = t.transaction_type_id
                        WHERE a.member_id = $member_id
                          AND at.type_name = 'savings'
                          AND YEAR(t.transaction_date) = $year
                        ORDER BY t.created_at ASC, t.transaction_id ASC
                    ");

                                                            if ($savings_result->num_rows > 0) {
                                                                // Calculate running balance first
                                                                while ($s = $savings_result->fetch_assoc()) {
                                                                    $amount = floatval($s['amount']); // negative for withdrawal
                                                                    $balance += $amount;

                                                                    $transactions[] = [
                                                                        'reference' => htmlspecialchars($s['reference_no']),
                                                                        'date'      => date('M d, Y', strtotime($s['transaction_date'])),
                                                                        'credit'    => $amount > 0 ? number_format($amount, 2) : '',
                                                                        'debit'     => $amount < 0 ? number_format(abs($amount), 2) : '',
                                                                        'balance'   => number_format($balance, 2),
                                                                    ];
                                                                }

                                                                // Reverse to show latest first
                                                                $transactions = array_reverse($transactions);

                                                                // Display
                                                                foreach ($transactions as $t) {
                                                                    echo "
                            <tr>
                                <td>
                                    <a href='javascript:void(0);'
                                       onclick='view_savings_receipt(this)'
                                       data-reference='{$t['reference']}'
                                       style='font-weight:600; color:#26a69a;'>{$t['reference']}</a>
                                </td>
                                <td>{$t['date']}</td>
                                <td class='text-right text-success'>" . ($t['credit'] ? "₱{$t['credit']}" : "") . "</td>
                                <td class='text-right text-danger'>" . ($t['debit'] ? "₱{$t['debit']}" : "") . "</td>
                                <td class='text-right'>₱{$t['balance']}</td>
                            </tr>
                            ";
                                                                }
                                                            } else {
                                                                echo "
                        <tr>
                            <td colspan='5' class='text-center'>No savings found for {$year}.</td>
                        </tr>
                        ";
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="loan">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">
                                                        <i class="icon-coins position-left text-teal-400"></i> Loan History (<?= $year; ?>)
                                                    </h6>
                                                </div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr style="background:#eee">
                                                                <th>Reference</th>
                                                                <th>Loan Type</th>
                                                                <th>Date Paid</th>
                                                                <th class="text-right">Principal</th>
                                                                <th class="text-right">Interest</th>
                                                                <th class="text-right">Penalty</th>
                                                                <th class="text-right">Total Paid</th>
                                                                <th class="text-right">Remaining Balance</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php

                                                            // Fetch loans INCLUDING total_due
                                                            $loan_result = $db->query("
SELECT l.loan_id, l.total_due, l.status, lt.loan_type_name
FROM loans l
JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
JOIN accounts a ON l.account_id = a.account_id
WHERE a.member_id = $member_id
ORDER BY l.loan_id DESC
");

                                                            if ($loan_result->num_rows > 0) {

                                                                while ($loan = $loan_result->fetch_assoc()) {

                                                                    $loan_id = $loan['loan_id'];
                                                                    $loan_type_name = htmlspecialchars($loan['loan_type_name']);
                                                                    $loan_status = ucfirst($loan['status']);

                                                                    // START balance from TOTAL LOAN DUE (correct)
                                                                    $balance = floatval($loan['total_due']);


                                                                    // Fetch payments
                                                                    $payments = $db->query("
        SELECT *
        FROM loan_payments
        WHERE loan_id = $loan_id
        ORDER BY payment_date ASC, payment_id ASC
        ");


                                                                    if ($payments->num_rows > 0) {

                                                                        while ($p = $payments->fetch_assoc()) {

                                                                            $reference = htmlspecialchars($p['reference_no']);

                                                                            $date_paid = date('M d, Y', strtotime($p['payment_date']));

                                                                            $principal_paid = floatval($p['principal_paid']);
                                                                            $interest_paid = floatval($p['interest_paid']);
                                                                            $penalty_paid = floatval($p['penalty_paid']);

                                                                            $total_paid = floatval($p['amount_paid']);


                                                                            // SUBTRACT payment from TOTAL DUE
                                                                            $balance -= $total_paid;


                                                                            echo "<tr>

                <td>
                <a href='javascript:void(0);'
                onclick='view_loanpayments_receipt(this)'
                data-reference='{$reference}'
                style='font-weight:600; color:#26a69a;'>
                {$reference}
                </a>
                </td>

                <td>{$loan_type_name}</td>

                <td>{$date_paid}</td>

                <td class='text-right'>₱" . number_format($principal_paid, 2) . "</td>

                <td class='text-right'>₱" . number_format($interest_paid, 2) . "</td>

                <td class='text-right'>₱" . number_format($penalty_paid, 2) . "</td>

                <td class='text-right'>₱" . number_format($total_paid, 2) . "</td>

                <td class='text-right'><b>₱" . number_format($balance, 2) . "</b></td>

                <td>{$loan_status}</td>

                </tr>";
                                                                        }
                                                                    } else {

                                                                        echo "
            <tr>
            <td colspan='9' class='text-center'>
            No payments made yet
            </td>
            </tr>
            ";
                                                                    }
                                                                }
                                                            } else {

                                                                echo "
<tr>
<td colspan='9' class='text-center'>
No loans found
</td>
</tr>
";
                                                            }

                                                            ?>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CASH SALES TAB -->
                                        <div class="tab-pane" id="cash">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">

                                                        <i class="icon-piggy-bank position-left text-teal-400"></i>

                                                        Cash Sales

                                                        <small style="margin-left:10px; color:#777;">

                                                            (<?= date('M d, Y', strtotime($date_from)) ?>

                                                            to

                                                            <?= date('M d, Y', strtotime($date_to)) ?>)

                                                        </small>

                                                    </h6>

                                                </div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr style="background:#eee">
                                                                <th>Sales No</th>
                                                                <th class="text-center">Total Quantity</th>
                                                                <th class="text-right">Total Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $hasCash = false;
                                                            $total_cash_sum = 0;

                                                            while ($row = $cash_sales->fetch_assoc()) {
                                                                $hasCash = true;

                                                                $qty = isset($row['total_quantity']) ? (int)$row['total_quantity'] : 0;
                                                                $amount = isset($row['total_amount']) ? $row['total_amount'] : 0;

                                                                $total_cash_sum += $amount;
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="javascript:;"
                                                                            onclick="view_details(this)"
                                                                            sales-id="<?= $row['sales_no']; ?>"
                                                                            sales-no="<?= $row['sales_no']; ?>">
                                                                            <?= htmlspecialchars($row['sales_no']); ?>
                                                                        </a>
                                                                    </td>
                                                                    <td class="text-center"><?= $qty; ?></td>
                                                                    <td class="text-right">₱<?= number_format($amount, 2); ?></td>
                                                                </tr>
                                                            <?php } ?>

                                                            <?php if (!$hasCash) { ?>
                                                                <tr>
                                                                    <td colspan="3">No cash sales found for <?= $year; ?>.</td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>

                                                        <?php if ($hasCash) { ?>

                                                            <tfoot>
                                                                <tr>
                                                                    <th colspan="2" class="text-right">Total:</th>
                                                                    <th class="text-right">₱<?= number_format($total_cash_sum, 2); ?></th>
                                                                </tr>
                                                            </tfoot>
                                                        <?php } ?>
                                                    </table>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- CHARGE SALES TAB -->
                                        <div class="tab-pane" id="charge">
                                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                <div class="panel-heading">
                                                    <h6 class="panel-title">

                                                        <i class="icon-piggy-bank position-left text-teal-400"></i>

                                                        Charge Sales

                                                        <small style="margin-left:10px; color:#777;">

                                                            (<?= date('M d, Y', strtotime($date_from)) ?>

                                                            to

                                                            <?= date('M d, Y', strtotime($date_to)) ?>)

                                                        </small>

                                                    </h6>

                                                </div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr style="background:#eee">
                                                                <th>Sales No</th>
                                                                <th class="text-center">Total Quantity</th>
                                                                <th class="text-right">Total Amount</th>
                                                                <th class="text-right">Paid</th>
                                                                <th class="text-right">Balance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $hasCharge = false;
                                                            $total_amt = $paid_amt = $bal_amt = 0;

                                                            while ($row = $charge_sales->fetch_assoc()) {
                                                                $hasCharge = true;

                                                                $paid = $row['total_amount'] - $row['balance'];
                                                                $total_amt += $row['total_amount'];
                                                                $paid_amt += $paid;
                                                                $bal_amt += $row['balance'];

                                                                echo "<tr>
                                                               <td>
                                                               <a href='javascript:;'
                                                                onclick='view_details(this)'
                                                                sales-id='" . htmlspecialchars($row['sales_no']) . "'
                                                                 sales-no='" . htmlspecialchars($row['sales_no']) . "'>
                                                              " . htmlspecialchars($row['sales_no']) . "
                                                                 </a>
                                                                </td>

                                                                 <td class='text-center'>" . (int)$row['total_quantity'] . "</td>

                                                                 <td class='text-right'>₱" . number_format($row['total_amount'], 2) . "</td>
                                                                  <td class='text-right'>₱" . number_format($paid, 2) . "</td>
                                                                  <td class='text-right'>₱" . number_format($row['balance'], 2) . "</td>
                                                                  </tr>";
                                                            }

                                                            if (!$hasCharge) {
                                                                echo "<tr><td colspan='5'>No charge sales found for $year.</td></tr>";
                                                            }
                                                            ?>

                                                        </tbody>
                                                        <?php if ($hasCharge) { ?>
                                                            <tfoot>
                                                                <tr style="font-weight:bold;">
                                                                    <th colspan="2" class="text-right">Totals:</th>
                                                                    <th class="text-right">₱<?= number_format($total_amt, 2); ?></th>
                                                                    <th class="text-right">₱<?= number_format($paid_amt, 2); ?></th>
                                                                    <th class="text-right">₱<?= number_format($bal_amt, 2); ?></th>
                                                                </tr>
                                                            </tfoot>
                                                        <?php } ?>
                                                    </table>

                                                </div>
                                            </div>
                                        </div>



                                    </div> <!-- tab-content -->
                                </div> <!-- tabbable -->
                            </div>
                        </div>
                    </div>





                    <?php require('includes/footer-text.php'); ?>

                    <div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="title-all"></h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div id="show-data-all"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php require('includes/footer.php'); ?>
            <script src="../js/validator.min.js"></script>

            <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
            <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->
            <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> -->
            <script src="../js/html2canvas.min.js"></script>
            <script src="../js/jspdf.umd.min.js"></script>

            <script>
                document.getElementById('btn-download-pdf').addEventListener('click', async function() {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const element = document.getElementById('history-content');

                    const activeTab = document.querySelector('.nav-tabs li.active a');
                    const tabs = document.querySelectorAll('.tab-pane');
                    tabs.forEach(tab => tab.classList.add('active', 'show'));
                    await new Promise(resolve => setTimeout(resolve, 400));

                    const canvas = await html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        scrollY: -window.scrollY
                    });
                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    let position = 0;

                    if (pdfHeight < pdf.internal.pageSize.getHeight()) {
                        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    } else {
                        let heightLeft = pdfHeight;
                        while (heightLeft > 0) {
                            pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
                            heightLeft -= pdf.internal.pageSize.getHeight();
                            if (heightLeft > 0) {
                                pdf.addPage();
                                position = -heightLeft;
                            }
                        }
                    }

                    pdf.save("<?= $customer_name_safe; ?>_History_<?= $year; ?>.pdf");

                    tabs.forEach(tab => tab.classList.remove('active', 'show'));
                    if (activeTab) $(activeTab).tab('show');

                    location.reload();
                });

                function view_details(el) {
                    var sales_no = $(el).attr('sales-no');
                    var sales_id = $(el).attr('sales-id');
                    $("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
                    $.ajax({
                        type: 'POST',
                        url: '../transaction.php',
                        data: {
                            sales_report_details: "",
                            sales_no: sales_no
                        },
                        success: function(msg) {
                            $("#modal-all").modal('show');
                            $("#show-button").html('');
                            $("#title-all").html('Bill No. : <b class="text-danger">' + sales_id + '</b>');
                            $("#show-data-all").html(msg);
                        },
                        error: function(msg) {
                            alert('Something went wrong!');
                        }
                    });
                    return false;
                }

                function changePage(el) {
                    $(".icon-circles").removeClass('text-primary');
                    $("#length_change").val($(el).attr('val'));
                    $("#length_change").trigger('change');
                    $(el).find('.icon-circles').addClass('text-primary');
                }

                function print_receipt() {
                    var contents = $("#print-receipt").html();
                    var frame1 = $('<iframe />');
                    frame1[0].name = "frame1";
                    frame1.css({
                        "position": "absolute",
                        "top": "-1000000px"
                    });
                    $("body").append(frame1);
                    var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
                    frameDoc.document.open();
                    frameDoc.document.write('<html><head><title></title>');
                    frameDoc.document.write('</head><body>');
                    frameDoc.document.write(contents);
                    frameDoc.document.write('</body></html>');
                    frameDoc.document.close();
                    setTimeout(function() {
                        window.frames["frame1"].focus();
                        window.frames["frame1"].print();
                        frame1.remove();
                    }, 500);
                }


                function view_capital_receipt(el) {
                    var reference_no = $(el).attr('data-reference');

                    if (!reference_no) {
                        alert("Reference number not found.");
                        return;
                    }

                    // show loader
                    $("#show-data-all").html(
                        '<div style="text-align:center;padding:40px;">' +
                        '<img src="../images/LoaderIcon.gif">' +
                        '</div>'
                    );

                    $.ajax({
                        type: 'POST',
                        url: '../transaction.php',
                        data: {
                            view_capital_receipt: true,
                            reference_no: reference_no
                        },

                        success: function(msg) {
                            $("#modal-all").modal('show');

                            $("#title-all").html(
                                'Capital Share Receipt : <b class="text-success">' +
                                reference_no +
                                '</b>'
                            );

                            $("#show-data-all").html(msg);
                        },

                        error: function() {
                            alert("Something went wrong.");
                        }
                    });
                }

                function view_savings_receipt(el) {
                    var reference_no = $(el).attr('data-reference');
                    if (!reference_no) {
                        alert("Reference number not found.");
                        return;
                    }
                    // show loader
                    $("#show-data-all").html(
                        '<div style="text-align:center;padding:40px;">' +
                        '<img src="../images/LoaderIcon.gif">' +
                        '</div>'
                    );
                    $.ajax({
                        type: 'POST',
                        url: '../transaction.php',

                        data: {
                            view_savings_receipt: true,
                            reference_no: reference_no
                        },
                        success: function(msg) {

                            $("#modal-all").modal('show');

                            $("#title-all").html(
                                'Savings Receipt : <b class="text-success">' +
                                reference_no +
                                '</b>'
                            );

                            $("#show-data-all").html(msg);
                        },

                        error: function() {
                            alert("Something went wrong.");
                        }

                    });

                }



                function view_loanpayments_receipt(el) {
                    var reference_no = $(el).attr('data-reference');

                    if (!reference_no) {
                        alert("Reference number not found.");
                        return;
                    }

                    // show loader
                    $("#show-data-all").html(
                        '<div style="text-align:center;padding:40px;">' +
                        '<img src="../images/LoaderIcon.gif">' +
                        '</div>'
                    );

                    $.ajax({
                        type: 'POST',
                        url: '../transaction.php',
                        data: {
                            view_loanpayments_receipt: true,
                            reference_no: reference_no
                        },

                        success: function(msg) {
                            $("#modal-all").modal('show');

                            $("#title-all").html(
                                'loan payments receipt: <b class="text-success">' +
                                reference_no +
                                '</b>'
                            );

                            $("#show-data-all").html(msg);
                        },

                        error: function() {
                            alert("Something went wrong.");
                        }
                    });
                }

                $(document).ready(function() {


                    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {

                        var activeTab = $(e.target).attr('href');

                        localStorage.setItem('activeSettingsTab', activeTab);

                    });
                    var activeTab = localStorage.getItem('activeSettingsTab');
                    if (activeTab) {
                        $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
                    }
                });
            </script>



            </html>