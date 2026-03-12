<?php require('includes/header.php'); ?>

<?php
if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}

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

    /* Pop effect for breadcrumb links */
    .breadcrumb-elements a {
        display: inline-block;
        /* needed for transform */
        transition: all 0.2s ease;
    }

    .breadcrumb-elements a:hover {
        transform: scale(1.05);
        /* slightly bigger */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        /* subtle shadow */
        border-radius: 5px;
        /* optional: rounded edges for nicer look */
        background-color: rgba(0, 128, 128, 0.1);
        /* subtle background change */
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
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
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span></h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="javascript:;"><i class="icon-chart"></i> Reports</a></li>
                            <li class="active"><i class="icon-dots"></i> Daily Collection</li>
                        </ul>
                        <ul class="breadcrumb-elements">
                            <li><a href="javascript:;" data-toggle="modal" data-target="#modal-update"><i class="icon-pencil position-left text-teal-400"></i>Update Cash Beginning</a></li>
                            <li><a href="javascript:;" data-toggle="modal" data-target="#modal-add"><i class="icon-add position-left text-teal-400"></i>Add Deposit</a></li>
                        </ul>
                    </div>
                </div>
                <div class="content">
                    <div class="panel panel-body ">
                        <form class="heading-form" id="form-reports" method="POST">
                            <input type="hidden" name="daile-sales-report"></input>
                            <ul class="breadcrumb-elements" style="float:left">
                                <li data-toggle="tooltip" title="Employee" style="padding-top: 2px;padding-right: 2px">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="icon-calendar"></i></span>
                                        <input name="date" type="text" class="form-control pickadate-selectors picker__input picker__input--active" value=" <?php if (isset($_SESSION['daily-report-input']) != "") { ?>   <?= $_SESSION['daily-report-input'] ?> <?php } else { ?> <?= date("m-d-Y") ?> <?php } ?>" readonly="" id="P1916777366" aria-haspopup="true" aria-expanded="true" aria-readonly="false" aria-owns="P1916777366_root">
                                    </div>
                                </li>
                                <li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
                                <li data-toggle="tooltip" title="Export Excel" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="print_report()" class="btn btn-default"><b><i class="icon-printer"></i></b></button></li>
                            </ul>
                        </form>
                    </div>
                    <?php

                    // ----------------------
                    // Initialize Variables
                    // ----------------------
                    $deposit = 0;

                    $beginning = 0;

                    $all_subtotal = 0;
                    $all_discount = 0;
                    $all_total = 0;
                    $accountsReceivable = 0;
                    $charge_collections = 0;
                    $expence_amount = 0;

                    // Today's date
                    $today = date("Y-m-d");
                    $start = strtotime('today GMT');
                    $date_add = date('Y-m-d', strtotime('+1 day', $start));

                    // Determine date filter based on session
                    if (isset($_SESSION['daily-report']) && $_SESSION['daily-report'] != $today) {
                        $filter_start = $_SESSION['daily-report'];
                        $filter_end = $_SESSION['daily-report'];
                    } else {
                        $filter_start = $today;
                        $filter_end = $date_add;
                    }

                    // ----------------------
                    // 1. Sales Totals (Cash & Credit)

                    $query_cash_sales = "
SELECT 
    SUM(total_amount) AS total_amount,
    SUM(subtotal) AS total_subtotal,
    SUM(discount) AS total_discount
FROM (
    SELECT 
        s.sales_no,
        MAX(s.total_amount) AS total_amount,
        MAX(s.subtotal) AS subtotal,
        MAX(s.discount) AS discount
    FROM tbl_sales s
    WHERE s.sales_status != 3
      AND s.sales_type = 1
      AND DATE(s.sales_date) BETWEEN '$filter_start' AND '$filter_end'
    GROUP BY s.sales_no
) AS grouped_sales
";

                    $query_credit_sales = "
SELECT SUM(total_amount) AS total_amount
FROM tbl_sales
WHERE sales_status != 3
AND sales_type = 0
AND sales_date >= '$filter_start'
AND sales_date <= '$filter_end'
";


                    // Execute query
                    $result_cash = $db->query($query_cash_sales);
                    if ($row = $result_cash->fetch_assoc()) {
                        $all_total = $row['total_amount'] ?? 0;
                        $all_subtotal = $row['total_subtotal'] ?? 0;
                        $all_discount = $row['total_discount'] ?? 0;
                    }

                    $result_credit = $db->query($query_credit_sales);
                    if ($row = $result_credit->fetch_assoc()) {
                        $accountsReceivable = $row['total_amount'] ?? 0;
                    }

                    // VAT
                    $vat_sales = $all_subtotal * 0.12;

                    // ----------------------
                    // 2. Deposits
                    // ----------------------
                    $deposit_query = "
    SELECT SUM(amount) AS total
    FROM tbl_deposits
    WHERE DATE(date_added) BETWEEN '$filter_start' AND '$filter_end'
";
                    $result_deposit = $db->query($deposit_query);
                    if ($row = $result_deposit->fetch_assoc()) {
                        $deposit = $row['total'] ?? 0;
                    }

                    // ----------------------
                    // 3. Beginning Cash
                    // ----------------------
                    $beginning_query = "
                    SELECT SUM(amount) AS total
                    FROM tbl_beginning_cash
                    WHERE DATE(cash_date) BETWEEN '$filter_start' AND '$filter_end'
                    ";
                    $result_beginning = $db->query($beginning_query);
                    if ($row = $result_beginning->fetch_assoc()) {
                        $beginning = $row['total'] ?? 0;
                    }

                    // ----------------------
                    // 4. Charge Payments
                    // ----------------------
                    $payment_query = "
SELECT 
    COALESCE(SUM(
        COALESCE(p.total_paid, 0) + COALESCE(s.amount_paid, 0)
    ), 0) AS total_paid

FROM tbl_sales s

LEFT JOIN (
    SELECT sales_no, SUM(amount_paid) AS total_paid
    FROM tbl_payments
    WHERE DATE(date_payment) BETWEEN '$filter_start' AND '$filter_end'
    GROUP BY sales_no
) p ON s.sales_no = p.sales_no

WHERE DATE(s.sales_date) BETWEEN '$filter_start' AND '$filter_end'
AND s.sales_status != 3
AND s.sales_type = 0

/* same condition used in table */
AND (
        p.total_paid IS NOT NULL
     OR s.amount_paid > 0
    )
";

                    $result_payment = $db->query($payment_query);
                    if ($row = $result_payment->fetch_assoc()) {
                        $charge_collections = $row['total_paid'] ?? 0;
                    }


                    // ----------------------
                    // 5. Expenses (with User info)
                    // ----------------------
                    $expenses_query = "
    SELECT SUM(tbl_expences.expence_amount) AS total_expenses
    FROM tbl_expences
    INNER JOIN tbl_users ON tbl_expences.user_id = tbl_users.user_id
    WHERE DATE(tbl_expences.date_expence) BETWEEN '$filter_start' AND '$filter_end'
";
                    $result_expenses = $db->query($expenses_query);
                    if ($row = $result_expenses->fetch_assoc()) {
                        $expence_amount = $row['total_expenses'] ?? 0;
                    }

                    // ----------------------
                    // All totals ready:
                    // ----------------------
                    // $all_subtotal, $all_discount, $all_total, $accountsReceivable, $vat_sales
                    // $deposit, $beginning, $charge_collections, $expence_amount
                    ?>

                    ...
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="panel panel-body bg-teal-300 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($all_subtotal, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Sub Total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-2">
                            <div class="panel panel-body bg-teal-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($all_discount, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Discount</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="panel panel-body bg-teal-600 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($deposit, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Deposit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-2">
                            <div class="panel panel-body bg-teal-700 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($expence_amount, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Expenses</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-4">
                            <div class="panel panel-body bg-teal-800 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($charge_collections, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Total Paid Charge</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-4">
                            <div class="panel panel-body bg-teal-800 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($all_total - $all_discount, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Total Amount</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="print-report">
                        <style>
                            table,
                            td,
                            th {
                                border: 1px solid black;
                            }

                            table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                        </style>
                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                            <div class="panel-heading">
                                <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Sales<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                            </div>
                            <div class="panel-body product-div2">
                                <table class="table datatable-button-html5-basic table-hover table-bordered  ">
                                    <thead>
                                        <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                            <th>Bill No.</th>
                                            <th>Employee</th>
                                            <th>Customer</th>
                                            <th>Sub Total</th>
                                            <th>Discount</th>
                                            <th>Cash</th>
                                            <th>Amount Due</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <?php
                                        $i = 0;
                                        $today = date("Y-m-d");
                                        $start = strtotime('today GMT');
                                        $date_add = date('Y-m-d', strtotime('+1 day', $start));
                                        $total_sales = 0;

                                        // Date filter
                                        if (isset($_SESSION['daily-report']) && $_SESSION['daily-report'] != $today) {
                                            $filter_start = $_SESSION['daily-report'];
                                            $filter_end = $_SESSION['daily-report'];
                                        } else {
                                            $filter_start = $today;
                                            $filter_end = $date_add;
                                        }

                                        // Query sales grouped by sales_no to get only one row per sale
                                        $query = "
SELECT 
    s.sales_no,
    MAX(s.total_amount) AS total_amount,
    MAX(s.subtotal) AS subtotal,
    MAX(s.discount) AS discount,
    MAX(s.amount_paid) AS amount_paid,
    MAX(u.fullname) AS fullname,
    MAX(c.name) AS customer_name
FROM tbl_sales s
LEFT JOIN tbl_users u ON s.user_id = u.user_id
LEFT JOIN tbl_customer c ON s.cust_id = c.cust_id
WHERE DATE(s.sales_date) BETWEEN '$filter_start' AND '$filter_end'
  AND s.sales_status != 3
  AND s.sales_type = 1
GROUP BY s.sales_no
ORDER BY s.sales_no ASC
";

                                        $result = $db->query($query);

                                        while ($row = $result->fetch_assoc()) {
                                            $i++; // <-- increment counter

                                            // Only one total_amount per sales_no
                                            $total_sales += $row['total_amount'];

                                            $sales_id = str_pad($row['sales_no'], 8, '0', STR_PAD_LEFT);
                                            $subtotal = $row['subtotal'] ?? 0;
                                            $discount = $row['discount'] ?? 0;
                                            $amount_paid = $row['amount_paid'] ?? 0;
                                        ?>
                                    <tr>
                                        <td>
                                            <a href="javascript:;" onclick="view_details(this)" sales-id='<?= $sales_id ?>' sales-no='<?= $row['sales_no'] ?>'>
                                                <?= $sales_id ?>
                                            </a>
                                        </td>
                                        <td><?= $row['fullname'] ?></td>
                                        <td><?= $row['customer_name'] ?></td>
                                        <td style="text-align: right;"><?= number_format($subtotal, 2) ?></td>
                                        <td style="text-align: right;"><?= number_format($discount, 2) ?></td>
                                        <td style="text-align: center;"><?= number_format($amount_paid, 2) ?></td>
                                        <td style="text-align: right;">₱<b><?= number_format($row['total_amount'], 2) ?></b></td>
                                    </tr>
                                <?php } ?>

                                <?php if ($i == 0) { ?>
                                    <tr>
                                        <td colspan="9" align="center">
                                            <h2>No data found!</h2>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <td colspan="6" align="right">
                                        <h5>Total</h5>
                                    </td>
                                    <td colspan="2" align="right">
                                        <h5>₱<?= number_format($total_sales, 2) ?></h5>
                                    </td>
                                </tr>
                                </table>
                            </div>
                        </div>
                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                            <div class="panel-heading">
                                <h6 class="panel-title"><i class="icon-coin-dollar text-teal-400"></i> Charge Sales
                                    <a class="heading-elements-toggle"><i class="icon-more"></i></a>
                                </h6>
                            </div>
                            <div class="panel-body product-div2">
                                <table class="table datatable-button-html5-basic table-hover table-bordered">
                                    <thead>
                                        <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                            <th>Bill No.</th>
                                            <th>Customer</th>
                                            <th>Sub Total</th>
                                            <th>Amount Due</th>
                                            <th>Remaining Balance</th>
                                            <th>Payments Made</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $j = 0;
                                        $today = date("Y-m-d");
                                        $start = strtotime('today GMT');
                                        $date_add = date('Y-m-d', strtotime('+1 day', $start));
                                        $total_payments = 0;

                                        // Date filter
                                        if (isset($_SESSION['daily-report']) && $_SESSION['daily-report'] != $today) {
                                            $filter_start = $_SESSION['daily-report'];
                                            $filter_end = $_SESSION['daily-report'];
                                        } else {
                                            $filter_start = $today;
                                            $filter_end = $date_add;
                                        }

                                        // Query only charge sales that have payments
                                        $charge_query = "
                                                      SELECT 
                                                       s.sales_no,
                                                       MAX(s.subtotal) AS subtotal,
                                                       MAX(s.total_amount) AS total_amount,
                                                       MAX(s.balance) AS balance,
                                                       MAX(c.name) AS customer_name,

                                                     /* TOTAL PAYMENTS FROM BOTH SOURCES */
                                                       MAX(
                                                       COALESCE(p.total_paid, 0) + COALESCE(s.amount_paid, 0)
                                                                    ) AS payments_made

                                                         FROM tbl_sales s
                                                         LEFT JOIN tbl_customer c ON s.cust_id = c.cust_id
                                                         LEFT JOIN (
                                                         SELECT sales_no, SUM(amount_paid) AS total_paid
                                                         FROM tbl_payments
                                                         GROUP BY sales_no
                                                         ) p ON s.sales_no = p.sales_no

                                                        WHERE DATE(s.sales_date) BETWEEN '$filter_start' AND '$filter_end'
                                                        AND s.sales_status != 3
                                                        AND s.sales_type = 0

                                                       /* SHOW IF EITHER PAYMENT EXISTS */
                                                       AND (
                                                         p.total_paid IS NOT NULL
                                                        OR s.amount_paid > 0
                                                                                )

                                                         GROUP BY s.sales_no
                                                          ORDER BY s.sales_no ASC
                                                             ";

                                        $charge_result = $db->query($charge_query);

                                        while ($row = $charge_result->fetch_assoc()) {
                                            $j++;

                                            $subtotal = $row['subtotal'] ?? 0;
                                            $total_amount = $row['total_amount'] ?? 0;
                                            $payments_made = $row['payments_made'] ?? 0;
                                            $remaining = $row['balance'] ?? 0;

                                            // Only sum payments
                                            $total_payments += $payments_made;
                                        ?>
                                            <tr>
                                                <td><?= $row['sales_no'] ?></td>
                                                <td><?= $row['customer_name'] ?></td>
                                                <td style="text-align:right;"><?= number_format($subtotal, 2) ?></td>
                                                <td style="text-align:right;"><?= number_format($total_amount, 2) ?></td>
                                                <td style="text-align:right; color:<?= ($remaining > 0) ? 'red' : 'green' ?>;">
                                                    <b><?= number_format($remaining, 2) ?></b>
                                                </td>
                                                <td style="text-align:right;">₱<?= number_format($payments_made, 2) ?></td>
                                            </tr>
                                        <?php } ?>

                                        <?php if ($j == 0) { ?>
                                            <tr>
                                                <td colspan="6" align="center">
                                                    <h2>No paid charge sales found!</h2>
                                                </td>
                                            </tr>
                                        <?php } else { ?>
                                            <tr style="background:#f1f1f1;font-weight:bold;">
                                                <td colspan="5" align="right">TOTAL PAYMENTS:</td>
                                                <td align="right">₱<?= number_format($total_payments, 2) ?></td>
                                            </tr>
                                        <?php } ?>



                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                            <div class="panel-heading">
                                <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Expenses<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                            </div>
                            <div class="panel-body product-div2">
                                <table class="table datatable-button-html5-basic table-hover table-bordered  ">
                                    <thead>
                                        <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                            <th>Expenses ID</th>
                                            <th>Description</th>
                                            <th>Notes</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <?php
                                        $ib = 0;
                                        $today = date("Y-m-d");
                                        $start = strtotime('today GMT');
                                        $date_add = date('Y-m-d', strtotime('+1 day', $start));
                                        $total_expences = 0;
                                        if (isset($_SESSION['daily-report'])) {
                                            if ($today == $_SESSION['daily-report']) {
                                                $query2 = "SELECT * FROM tbl_expences INNER JOIN tbl_users ON tbl_expences.user_id=tbl_users.user_id   WHERE date(date_expence) BETWEEN  '$today' AND '$date_add'  ";
                                            } else {
                                                $query2 = "SELECT * FROM tbl_expences INNER JOIN tbl_users ON tbl_expences.user_id=tbl_users.user_id   WHERE  date(date_expence)='" . $_SESSION['daily-report'] . "'  ";
                                            }
                                        } else {
                                            $query2 = "SELECT * FROM tbl_expences INNER JOIN tbl_users ON tbl_expences.user_id=tbl_users.user_id   WHERE  date_expence BETWEEN  '$today' AND '$date_add' ";
                                        }
                                        $result = $db->query($query2);
                                        while ($row = $result->fetch_assoc()) {
                                            $ib++;
                                            $total_expences += $row['expence_amount'];
                                        ?>
                                            <td width="160px">06343<?= $row['expences_id']; ?></td>
                                            <td><?= $row['description']; ?></td>
                                            <td width="40%"><?= $row['notes']; ?></td>
                                            <td align="right"><b><?= number_format($row['expence_amount'], 2); ?></b></td>
                                    </tr>
                                <?php } ?>
                                <?php if ($ib == 0) { ?>
                                    <tr>
                                        <td colspan="4" align="center">
                                            <h2>No data found!</h2>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="3" align="right">
                                        <h5>Total</h5>
                                    </td>
                                    <td align="right">
                                        <h5><?= number_format($total_expences, 2) ?></h5>
                                    </td>
                                </tr>
                                </table>
                            </div>
                        </div>
                        <?php

                        $daily_collection = ($all_total + $charge_collections) - $expence_amount;
                        $balance = $daily_collection - $deposit + $beginning;
                        ?>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Sales : <?= number_format($all_total, 2) ?></h4>
                            </div>
                        </div>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Charge Sales : <?= number_format($charge_collections, 2) ?></h4>
                            </div>
                        </div>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Expenses : <?= number_format($expence_amount, 2) ?></h4>
                            </div>
                        </div>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Cash Beginning : <?= number_format($beginning, 2) ?></h4>
                            </div>
                        </div>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Deposit : <?= number_format($deposit, 2) ?></h4>
                            </div>
                        </div>
                        <div class="panel panel-white">
                            <div align="right" style="padding-right: 60px">
                                <h4>Daily Collection : <?= number_format($balance, 2) ?></h4>
                            </div>
                        </div>

                    </div>
                </div>
                <?php require('includes/footer-text.php'); ?>
            </div>
        </div>
    </div>
</body>

<div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-all"></h5>
                <button type="button" class="close" title="Click to close (Esc)" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="show-data-all"></div>
            </div>
        </div>
    </div>
</div>
<div id="modal-add" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title">New Deposit Form</h5>
            </div>
            <div class="modal-bodys">
                <form action="#" id="form-new" class="form-horizontal" data-toggle="validator" role="form">
                    <input type="hidden" name="save-deposit"></input>
                    <input type="hidden" value="<?= $balance ?>" name="balance">
                    <div class="form-body" style="padding-top: 20px">
                        <div id="display-msg"></div>
                        <div class="form-group">
                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Amount</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                    <input class="form-control filterme" autocomplete="off" name="amount" id="amount" placeholder="Enter amount" type="text" data-error=" Please enter valid amount." required>
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Deposit</button>
            </div>
            </form>
        </div>
    </div>
</div>
<div id="modal-update" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title">Update Beginning Cash Form</h5>
            </div>
            <div class="modal-bodys">
                <form action="#" id="form-update" class="form-horizontal" data-toggle="validator" role="form">
                    <input type="hidden" name="update-cash"></input>
                    <input type="hidden" value="<?= $balance ?>" name="balance">
                    <div class="form-body" style="padding-top: 20px">
                        <div id="display-msg"></div>
                        <div class="form-group">
                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Amount</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                    <input class="form-control filterme" autocomplete="off" name="amount" id="amount" placeholder="Enter amount" type="text" data-error=" Please enter valid amount." required>
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Amount</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php require('includes/footer.php'); ?>
<script src="../js/validator.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript">
    $(function() {
        $('.pickadate-selectors').pickadate({
            format: 'mm/dd/yyyy',
            hiddenPrefix: 'prefix__',
            hiddenSuffix: '__suffix',
            clear: ''
        });
    });

    $('#form-reports').on('submit', function(e) {
        $(':input[type="submit"]').prop('disabled', true);
        var data = $("#form-reports").serialize();
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: data,
            success: function(msg) {
                location.reload();
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    });

    function closer() {
        window.location = 'products.php';
    }

    function view_user_sales(el) {
        var fullname = $(el).attr('fullname');
        var user_id = $(el).attr('user-id');
        $("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                user_sales_details: "",
                user_id: user_id
            },
            success: function(msg) {
                $("#modal-all").modal('show');
                $("#show-button").html('');
                $("#title-all").html('<b>' + fullname + '</b> Sales Details');
                $("#show-data-all").html(msg);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    }

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

    function print_report() {
        var contents = $("#print-report").html();
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

    $('#form-new').validator().on('submit', function(e) {
        if (e.isDefaultPrevented()) {} else {
            var amount = parseFloat($("#amount").val());
            var balance = parseFloat(<?= $balance ?>);
            if (amount > balance) {
                $.jGrowl('Amount not allowed.', {
                    header: 'Error Notification',
                    theme: 'alert-styled-right bg-danger'
                });
                return false;
            }
            $(':input[type="submit"]').prop('disabled', true);
            var data = $("#form-new").serialize();
            $.ajax({
                type: 'POST',
                url: '../transaction.php',
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(msg) {
                    console.log(msg);
                    $.jGrowl('Deposit successfully saved.', {
                        header: 'Success Notification',
                        theme: 'alert-styled-right bg-success'
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
            return false;
        }
    });

    $('#form-update').validator().on('submit', function(e) {
        if (e.isDefaultPrevented()) {} else {
            $(':input[type="submit"]').prop('disabled', true);
            var data = $("#form-update").serialize();
            $.ajax({
                type: 'POST',
                url: '../transaction.php',
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(msg) {
                    console.log(msg);
                    $.jGrowl('Cash Beginning successfully updated.', {
                        header: 'Success Notification',
                        theme: 'alert-styled-right bg-success'
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
            return false;
        }
    });
</script>

</html>