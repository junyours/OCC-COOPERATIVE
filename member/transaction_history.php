        <?php
        require('../admin/includes/header.php');


        if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id']) || $_SESSION['is_login_yes'] != 'yes') {
            die("Unauthorized access. Please log in again.");
        }

        $user_id = (int) $_SESSION['user_id'];


        $member_result = $db->query("
            SELECT member_id, cust_id 
            FROM tbl_members 
            WHERE user_id = $user_id
            LIMIT 1
        ");

        if (!$member_result || $member_result->num_rows == 0) {
            die("Member is not linked to a customer record.");
        }

        $member_data = $member_result->fetch_assoc();
        $member_id = (int) $member_data['member_id'];
        $cust_id   = (int) $member_data['cust_id'];

        if ($cust_id <= 0) {
            die("Invalid customer account.");
        }

        $member_type_result = $db->query("
            SELECT type 
            FROM tbl_members 
            WHERE member_id = $member_id
            LIMIT 1
        ");

        $member_type_data = $member_type_result->fetch_assoc();
        $member_type = $member_type_data['type'] ?? 'associate';

        /* ============================================
        DATE RANGE FILTER
        ============================================ */

        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-01-01');
        $date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : date('Y-12-31');

        /* For display */
        $year = date('Y', strtotime($date_from));


        $customer_result = $db->query("SELECT * FROM tbl_customer WHERE cust_id = $cust_id");
        $customer = $customer_result->fetch_assoc();

        $customer_name_safe = preg_replace('/[^A-Za-z0-9_\- ]/', '', $customer['name']);

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
    ORDER BY t.transaction_date DESC
");



        // ---------- CASH SALES SUMMARY ---------- 
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


        <link rel="stylesheet" href="../css/transaction.css">


        <body class="layout-boxed navbar-top">
            <!-- NAVBAR -->
            <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
                    <ul class="nav navbar-nav visible-xs-block">
                        <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
                    </ul>
                </div>
                <div class="navbar-collapse collapse" id="navbar-mobile">
                    <?php require('../admin/includes/sidebar.php'); ?>
                </div>
            </div>

            <div class="page-container">
                <!-- MOBILE APP HEADER -->
                <div class="mobile-app-header">
                    <div class="mobile-title">
                        <i class="icon-history"></i> Transaction History
                    </div>
                </div>
                <!-- MOBILE TRANSACTION FEED -->
                <div class="mobile-transaction-feed">

                    <?php
                    $history = [];

                    /* Capital Share */
                    $capital_feed = $db->query("
            SELECT 
                 t.created_at AS datetime,
                t.amount,
                'Capital Share' AS type,
                t.reference_no AS ref_no
            FROM transactions t
            INNER JOIN accounts a ON a.account_id = t.account_id
            INNER JOIN account_types at ON at.account_type_id = a.account_type_id
            WHERE a.member_id = $member_id
            AND at.type_name = 'capital_share'
        ");

                    /* Savings Deposit and Withdrawal */
                    $savings_feed = $db->query("
            SELECT 
                t.created_at AS datetime,
                t.amount,
                CASE
                    WHEN t.amount >= 0 THEN 'Savings Deposit'
                    ELSE 'Savings Withdrawal'
                END AS type,
                t.reference_no AS ref_no
            FROM transactions t
            INNER JOIN accounts a ON a.account_id = t.account_id
            INNER JOIN account_types at ON at.account_type_id = a.account_type_id
            WHERE a.member_id = $member_id
            AND at.type_name = 'savings'
        ");

                    /* Payments */
                    $payment_feed = $db->query("
            SELECT date_payment AS datetime,
                amount_paid AS amount,
                'Charge Payment' AS type,
                sales_no AS ref_no
            FROM tbl_payments
            WHERE sales_no IN (
                SELECT sales_no FROM tbl_sales WHERE cust_id = $cust_id
            )
        ");

                    /* Benefits */
                    $benefit_feed = $db->query("
            SELECT disbursed_at AS datetime,
                amount_disbursed AS amount,
                'Benefit' AS type,
                reference_no AS ref_no
            FROM distribution_disbursements
            WHERE cust_id = $cust_id
        ");

                    /* Sales */
                    $sales_feed = $db->query("
            SELECT s.sales_date AS datetime,
                MAX(s.total_amount) AS amount,
                CASE 
                    WHEN s.sales_type = 1 THEN 'Cash Sale'
                    ELSE 'Charge Sale'
                END AS type,
                s.sales_no AS ref_no
            FROM tbl_sales s
            WHERE s.cust_id = $cust_id
            AND YEAR(s.sales_date) = $year
            GROUP BY s.sales_no, s.sales_date, s.sales_type
        ");

                    /* Merge all feeds */
                    while ($r = $capital_feed->fetch_assoc()) $history[] = $r;
                    while ($r = $savings_feed->fetch_assoc()) $history[] = $r; // ← ADD THIS LINE
                    while ($r = $sales_feed->fetch_assoc()) $history[] = $r;
                    while ($r = $payment_feed->fetch_assoc()) $history[] = $r;
                    while ($r = $benefit_feed->fetch_assoc()) $history[] = $r;

                    /* Sort by latest */
                    usort($history, function ($a, $b) {
                        return strtotime($b['datetime']) <=> strtotime($a['datetime']);
                    });

                    foreach ($history as $h) {

                        $date_label = date('M d, Y', strtotime($h['datetime']));
                        $time = date('h:i A', strtotime($h['datetime']));
                        $amount = number_format(abs($h['amount']), 2);

                        $sign = '+';
                        $color = '#1565c0';

                        if (
                            in_array($h['type'], [
                                'Charge Payment',
                                'Cash Sale',
                                'Charge Sale',
                                'Savings Withdrawal'
                            ])
                        ) {
                            $sign = '-';
                            $color = '#0b0a0a';
                        }

                    ?>

                        <div class="mobile-transaction-item"
                            onclick="openTransaction('<?= $h['type'] ?>','<?= $h['ref_no'] ?? '' ?>')"
                            style="cursor:pointer;">

                            <div class="mobile-left">
                                <div class="mobile-time"><?= $time ?></div>
                                <div class="mobile-type"><?= $h['type'] ?></div>
                            </div>

                            <div class="mobile-amount" style="color:<?= $color ?>">
                                <?= $sign ?>₱<?= $amount ?>
                            </div>

                        </div>

                    <?php } ?>

                </div>


                <div class="page-content desktop-view">
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
                                        <a href="dashboard.php">
                                            <i class="icon-home"></i>Dashboard
                                        </a>
                                    </li>

                                    <li class="active">Transaction History</li>
                                </ul>


                                <!-- RIGHT SIDE ELEMENTS -->
                                <ul class="breadcrumb-elements">

                                    <!-- DATE FILTER -->
                                    <li>

                                        <form method="GET" class="form-inline" style="display:flex; align-items:center; gap:5px;">

                                            <label style="margin:0;">From:</label>

                                            <input type="date"
                                                name="date_from"
                                                value="<?= $date_from ?>"
                                                class="form-control">
                                            <label style="margin:0;">To:</label>
                                            <input type="date"
                                                name="date_to"
                                                value="<?= $date_to ?>"
                                                class="form-control">
                                            <button type="submit"
                                                class="btn btn-primary">
                                                Filter
                                            </button>
                                            <a href="transaction_history.php"
                                                class="btn btn-default">
                                                Clear Filter
                                            </a>

                                        </form>

                                    </li>


                                    <!-- DOWNLOAD PDF -->
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
                                                    <li><a href="#cash" data-toggle="tab">Product Purchases (Cash)</a></li>
                                                    <li><a href="#charge" data-toggle="tab">Product Purchases (Charge)</a></li>
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

                                                            $total_savings = 0;

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
                                                                        <td>Total Product Purchases (Cash) (<?= $year; ?>)</td>
                                                                        <td>₱<?= number_format($total_cash, 2); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Total Paid (Charge <?= $year; ?>)</td>
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

                                                                            $credit = $c['amount'];
                                                                            $running_balance += $credit;
                                                                        } elseif ($c['transaction_type'] === 'withdrawal') {

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
                                                                            $balance = floatval($loan['total_due']);
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
                                                                              </tr>";
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane" id="cash">
                                                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                        <div class="panel-heading">
                                                            <h6 class="panel-title"><i class="icon-cart position-left text-teal-400"></i> Cash Sales Summary (<?= $year; ?>)</h6>
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


                                                <div class="tab-pane" id="charge">
                                                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                                                        <div class="panel-heading">
                                                            <h6 class="panel-title"><i class="icon-credit-card position-left text-teal-400"></i> Charge (<?= $year; ?>)</h6>
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




                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php require('../admin/includes/footer-text.php'); ?>

                        </div>
                    </div>
                </div>

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

            <div class="mobile-bottom-nav">
                <a href="transaction_history.php" class="active">
                    <i class="icon-history"></i>
                    transaction
                </a>

                <a href="dashboard.php">
                    <i class="icon-home"></i>
                    Home
                </a>
                <a href="loan.php">
                    <i class="icon-coins"></i>
                    Loans
                </a>
                <a href="../admin/profile.php">
                    <i class="icon-user"></i>
                    Profile
                </a>
            </div>





            <?php require('../admin/includes/footer.php'); ?>
            <script src="../js/html2canvas.min.js"></script>
            <script src="../js/jspdf.umd.min.js"></script>

            <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>

            <script>
                document.getElementById('btn-download-pdf').addEventListener('click', async function() {

                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const element = document.getElementById('history-content');


                    const tabs = document.querySelectorAll('.tab-pane');


                    tabs.forEach(tab => {
                        tab.style.display = "block";
                        tab.style.opacity = "1";
                        tab.style.visibility = "visible";
                        tab.classList.add('active');
                    });

                    await new Promise(resolve => setTimeout(resolve, 800));

                    const canvas = await html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        scrollY: -window.scrollY
                    });

                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    const pageHeight = pdf.internal.pageSize.getHeight();

                    let heightLeft = pdfHeight;
                    let position = 0;

                    pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
                    heightLeft -= pageHeight;

                    while (heightLeft > 0) {
                        position = heightLeft - pdfHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
                        heightLeft -= pageHeight;
                    }

                    pdf.save("<?= $customer_name_safe; ?>_History_<?= $year; ?>.pdf");


                    tabs.forEach(tab => {
                        tab.style.display = "";
                        tab.style.opacity = "";
                        tab.style.visibility = "";
                        tab.classList.remove('active');
                    });

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


                function openTransaction(type, ref) {

                    if (!ref) return;

                    $("#show-data-all").html('<div style="text-align:center;padding:40px"><img src="../images/LoaderIcon.gif"></div>');
                    let postData = {};
                    if (type.includes('Sale')) {
                        postData = {
                            sales_report_details: "",
                            sales_no: ref
                        };
                        $("#title-all").html('Sale No: <b class="text-danger">' + ref + '</b>');
                    } else if (type === 'Charge Payment') {
                        postData = {
                            payment_details: "",
                            sales_no: ref
                        };
                        $("#title-all").html('Payment for Sale No: <b class="text-success">' + ref + '</b>');
                    } else if (type === 'Benefit') {
                        postData = {
                            benefit_details: "",
                            ref_no: ref
                        };
                        $("#title-all").html('Benefit Ref: <b class="text-primary">' + ref + '</b>');
                    } else {
                        alert("Details not available for this item.");
                        return;
                    }
                    $.ajax({
                        type: 'POST',
                        url: '../transaction.php',
                        data: postData,
                        success: function(msg) {
                            $("#modal-all").modal('show');
                            $("#show-data-all").html(msg);
                        },
                        error: function() {
                            alert('Something went wrong!');
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
            </script>

        </body>

        </html>