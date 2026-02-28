<?php require('includes/header.php'); ?>
<?php


if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Manila');
date_default_timezone_get();
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
    MAX(total_amount) AS total_amount
FROM tbl_sales
WHERE DATE(sales_date) BETWEEN '$today' AND '$date_add'
GROUP BY sales_no
";

$result = $db->query($query);

if ($result) {

    // count of unique sales
    $total_sales = $result->num_rows;

    while ($row = $result->fetch_assoc()) {

        $all_subtotal += $row['subtotal'];
        $all_discount += $row['discount'];

        // use MAX total_amount (correct total per sale)
        $all_total += $row['total_amount'];
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


$total_savings = 0;

$query = "
SELECT SUM(t.amount) AS total
FROM transactions t
JOIN accounts a ON a.account_id = t.account_id
JOIN account_types at ON at.account_type_id = a.account_type_id
WHERE at.type_name = 'savings'
AND t.transaction_type_id = 1
";

$result = $db->query($query);
$row = $result->fetch_assoc();
$total_savings = $row['total'] ?? 0;


$total_capital_share = 0;

$query = "
SELECT SUM(t.amount) AS total
FROM transactions t
JOIN accounts a ON a.account_id = t.account_id
JOIN account_types at ON at.account_type_id = a.account_type_id
WHERE at.type_name = 'capital_share'
AND t.transaction_type_id IN (1,3)
";

$result = $db->query($query);
$row = $result->fetch_assoc();
$total_capital_share = $row['total'] ?? 0;


$total_loans_ongoing = 0;

$query = "
SELECT COUNT(*) AS total
FROM loans
WHERE status = 'ongoing'
";

$result = $db->query($query);
$row = $result->fetch_assoc();

$total_loans_ongoing = $row['total'] ?? 0;



?>

<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        /* vertically center image + text */
        gap: 0px;
        /* space between logo and text */
        font-weight: 800;
        color: white;
        /* adjust to your navbar color */
        text-decoration: none;
        font-size: 50px;
    }

    .navbar-brand img {
        height: 40px;
        /* adjust logo height */
        width: auto;
        object-fit: contain;
    }

    .navbar-brand span {
        white-space: nowrap;
        /* prevent text from wrapping to next line */
    }

    .panel-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .panel-link .panel {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .panel-link .panel:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img style="height: 45px!important" src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>
    <!-- /main navbar -->
    <!-- Page container -->
    <div class="page-container">
        <!-- Page content -->
        <div class="page-content">
            <!-- Main content -->
            <div class="content-wrapper">
                <!-- Page header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span></h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li class="active"><i class="icon-home2 position-left"></i> Dashboard</li>
                        </ul>
                    </div>
                </div>
                <!-- /page header -->
                <?php require('includes/footer-text.php'); ?>
                <!-- Content area -->
                <div class="content">
                    <div class="row">

                        <!-- Total Savings -->
                        <div class="col-sm-6 col-md-4">
                            <a href="financial.php" class="panel-link">
                                <div class="panel panel-body bg-success-400">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-wallet icon-3x"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin">₱ <?= number_format($total_savings, 2) ?></h3>
                                            <span>Total Savings</span>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <!-- Capital Share -->
                        <div class="col-sm-6 col-md-4">
                            <a href="financial.php" class="panel-link">
                                <div class="panel panel-body bg-blue-400">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-coins icon-3x"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin">₱ <?= number_format($total_capital_share, 2) ?></h3>
                                            <span>Total Capital Share</span>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <!-- Ongoing Loans -->
                        <div class="col-sm-6 col-md-4">
                            <a href="loan-report.php" class="panel-link">
                                <div class="panel panel-body bg-danger-400">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-cash icon-3x"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin"><?= $total_loans_ongoing ?></h3>
                                            <span>Ongoing Loans</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="daily_sales.php" class="panel-link">
                                <div class="panel panel-body">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-cart icon-3x text-danger-400"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin text-semibold"><?= $total_sales ?></h3>
                                            <span class="text-uppercase text-size-mini text-muted">today's Sale</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="cashier.php" class="panel-link">
                                <div class="panel panel-body">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-users icon-3x text-success-400"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin text-semibold"><?= $user_total ?></h3>
                                            <span class="text-uppercase text-size-mini text-muted">Employee</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="customer.php" class="panel-link">
                                <div class="panel panel-body">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-users icon-3x text-indigo-400"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin text-semibold"><?= $customer_total ?></h3>
                                            <span class="text-uppercase text-size-mini text-muted">Member</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="supplier.php" class="panel-link">
                                <div class="panel panel-body">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-users icon-3x text-blue-400"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin text-semibold"><?= $supplier_total ?></h3>
                                            <span class="text-uppercase text-size-mini text-muted">Supplier</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="panel panel-white">
                        <a href="daily_sales.php" class="panel-link">
                            <div class="panel-heading">
                                <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Daily Sales</h6>
                            </div>
                            <!-- <input type="text" id="myInputTextField"> -->
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-6 col-md-3">
                                        <div class="panel panel-body bg-success-400 has-bg-image">
                                            <div class="media no-margin">
                                                <div class="media-left media-middle">
                                                    <i class="icon-cart icon-3x opacity-75"></i>
                                                </div>
                                                <div class="media-body text-right">
                                                    <h3 class="no-margin" id="no-sales"><?= $total_sales ?></h3>
                                                    <span class="text-uppercase text-size-mini">No. of Sales</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="panel panel-body bg-blue-400 has-bg-image">
                                            <div class="media no-margin">
                                                <div class="media-right media-middle">
                                                    <i class="icon-3x opacity-75">₱</i>
                                                </div>
                                                <div class="media-body text-right">
                                                    <h3 class="no-margin"><?= number_format($all_total, 2) ?></h3>
                                                    <span class="text-uppercase text-size-mini">Sub Total</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="panel panel-body bg-danger-400 has-bg-image">
                                            <div class="media no-margin">
                                                <div class="media-right media-middle">
                                                    <i class="icon-3x opacity-75">₱</i>
                                                </div>
                                                <div class="media-body text-right">
                                                    <h3 class="no-margin"><?= number_format($all_discount, 2) ?></h3>
                                                    <span class="text-uppercase text-size-mini">Discount</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="panel panel-body bg-indigo-400 has-bg-image">
                                            <div class="media no-margin">
                                                <div class="media-left media-middle">
                                                    <i class="icon-3x opacity-75">₱</i>
                                                </div>
                                                <div class="media-body text-right">
                                                    <h3 class="no-margin"><?= number_format($all_total, 2) ?></h3>
                                                    <span class="text-uppercase text-size-mini">Total Amount</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php
                            // Current year
                            $year = date("Y");

                            // Array to hold monthly totals
                            $monthly_totals = [];

                            // Loop through all 12 months
                            for ($m = 1; $m <= 12; $m++) {

                                $month = sprintf("%02d", $m);
                                $month_str = "$year-$month";

                                $query = "

    SELECT SUM(total_amount_once) AS month_total

    FROM (

        SELECT 
            sales_no,
            MAX(total_amount) AS total_amount_once

        FROM tbl_sales

        WHERE DATE_FORMAT(sales_date, '%Y-%m') = '$month_str'

        GROUP BY sales_no

    ) monthly_sales

    ";

                                $result = $db->query($query);

                                $row = $result->fetch_assoc();

                                $monthly_totals[$m] = $row['month_total'] ?? 0;
                            }


                            // Assign totals
                            $january_total   = $monthly_totals[1];
                            $february_total  = $monthly_totals[2];
                            $march_total     = $monthly_totals[3];
                            $april_total     = $monthly_totals[4];
                            $may_total       = $monthly_totals[5];
                            $june_total      = $monthly_totals[6];
                            $july_total      = $monthly_totals[7];
                            $august_total    = $monthly_totals[8];
                            $september_total = $monthly_totals[9];
                            $october_total   = $monthly_totals[10];
                            $november_total  = $monthly_totals[11];
                            $december_total  = $monthly_totals[12];
                            ?>

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-calendar position-left"></i><b><?= date('Y') ?></b> Monthly Sales</h6>
                                </div>
                                <div class="panel-body" style="background-color: #263238; color: #fff;">
                                    <style>
                                        #chartdiv {
                                            width: 100%;
                                            height: 500px;
                                        }
                                    </style>

                                    <!-- AmCharts resources -->
                                    <script src="../amchart/amcharts.js"></script>
                                    <script src="../amchart/serial.js"></script>
                                    <script src="../amchart/export.min.js"></script>
                                    <link rel="stylesheet" href="../amchart/export.css" type="text/css" media="all" />
                                    <script src="../amchart/black.js"></script>

                                    <script>
                                        var chart = AmCharts.makeChart("chartdiv", {
                                            "theme": "black",
                                            "type": "serial",
                                            "startDuration": 2,
                                            "dataProvider": [{
                                                    "country": "January",
                                                    "visits": <?= $january_total ?>,
                                                    "color": "#FF0F00"
                                                },
                                                {
                                                    "country": "February",
                                                    "visits": <?= $february_total ?>,
                                                    "color": "#FF6600"
                                                },
                                                {
                                                    "country": "March",
                                                    "visits": <?= $march_total ?>,
                                                    "color": "#FF9E01"
                                                },
                                                {
                                                    "country": "April",
                                                    "visits": <?= $april_total ?>,
                                                    "color": "#FCD202"
                                                },
                                                {
                                                    "country": "May",
                                                    "visits": <?= $may_total ?>,
                                                    "color": "#F8FF01"
                                                },
                                                {
                                                    "country": "June",
                                                    "visits": <?= $june_total ?>,
                                                    "color": "#B0DE09"
                                                },
                                                {
                                                    "country": "July",
                                                    "visits": <?= $july_total ?>,
                                                    "color": "#04D215"
                                                },
                                                {
                                                    "country": "August",
                                                    "visits": <?= $august_total ?>,
                                                    "color": "#0D8ECF"
                                                },
                                                {
                                                    "country": "September",
                                                    "visits": <?= $september_total ?>,
                                                    "color": "#0D52D1"
                                                },
                                                {
                                                    "country": "October",
                                                    "visits": <?= $october_total ?>,
                                                    "color": "#2A0CD0"
                                                },
                                                {
                                                    "country": "November",
                                                    "visits": <?= $november_total ?>,
                                                    "color": "#8A0CCF"
                                                },
                                                {
                                                    "country": "December",
                                                    "visits": <?= $december_total ?>,
                                                    "color": "#CD0D74"
                                                }
                                            ],
                                            "valueAxes": [{
                                                "position": "left",
                                                "title": "Amount"
                                            }],
                                            "graphs": [{
                                                "balloonText": "[[category]]: <b>[[value]]</b>",
                                                "fillColorsField": "color",
                                                "fillAlphas": 1,
                                                "lineAlpha": 0.1,
                                                "type": "column",
                                                "valueField": "visits"
                                            }],
                                            "depth3D": 20,
                                            "angle": 30,
                                            "chartCursor": {
                                                "categoryBalloonEnabled": false,
                                                "cursorAlpha": 0,
                                                "zoomable": false
                                            },
                                            "categoryField": "country",
                                            "categoryAxis": {
                                                "gridPosition": "start",
                                                "labelRotation": 90
                                            },
                                            "export": {
                                                "enabled": false
                                            }

                                        });
                                    </script>

                                    <div id="chartdiv"></div>
                                </div>
                            </div>


                            <!-- <div class="panel panel-white border-top-xlg border-top-teal-400">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Latest Sytem History</h6>
                                </div>
                                <div class="panel-body product-div2">
                                    <table class="table datatable-button-html5-basic table-hover table-bordered  ">
                                        <thead>
                                            <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                                <th>History ID</th>
                                                <th>Date</th>
                                                <th>History Type</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tr>
                                            <?php
                                            $query = "SELECT * FROM tbl_history ORDER BY history_id DESC LIMIT 10";
                                            $result = $db->query($query);

                                            while ($row = $result->fetch_assoc()) {
                                                $details = json_decode($row['details']);

                                                // Safe access: user_id may not exist
                                                $user_id = isset($details->user_id) ? $details->user_id : 0;

                                                // Fetch user info safely
                                                $query_user = "SELECT * FROM tbl_users WHERE user_id='$user_id' LIMIT 1";
                                                $result_user = $db->query($query_user);
                                                $data_user = $result_user ? $result_user->fetch_assoc() : null;
                                                $user_fullname = $data_user['fullname'] ?? "Unknown";

                                                // Default values
                                                $history_type = $row['history_type'];
                                                $details_data = "Not Set";

                                                // Map history types safely
                                                switch ($row['history_type']) {
                                                    case 1:
                                                        $history_type = "New Sales";
                                                        $sales_no = $details->sales_no ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Bill No. #:' . $sales_no .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 2:
                                                        $history_type = "Delete Sales";
                                                        $sales_no = $details->sales_no ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Bill No. #:' . $sales_no .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 3:
                                                        $history_type = "Set Active Sales";
                                                        $sales_no = $details->sales_no ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Bill No. #:' . $sales_no .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 11:
                                                        $history_type = "New Product";
                                                        $product_id = $details->product_id ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID:' . $product_id .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 12:
                                                        $history_type = "Update Product";
                                                        $product_id = $details->product_id ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID:' . $product_id .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 15:
                                                        $history_type = "New Member";
                                                        $cust_id = $details->cust_id ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Member ID:' . $cust_id .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 17:
                                                        $history_type = "New Supplier";
                                                        $supplier_id = $details->supplier_id ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Supplier ID:' . $supplier_id .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 19:
                                                        $history_type = "New Employee";
                                                        $employee_id = $details->user_id ?? "N/A";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Employee ID:' . $employee_id .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . $user_fullname;
                                                        break;
                                                    case 26:
                                                        $history_type = "Login";
                                                        $details_data = '<i class="icon-barcode2 text-teal-400"></i> Date: ' . $row['date_history'] .
                                                            ' <i class="icon-user text-teal-400"></i> User: ' . $user_fullname;
                                                        break;
                                                    case 40:
                                                        $history_type = "Loan Application";
                                                        $cust_id = $details->cust_id ?? "N/A";
                                                        $amount = $details->amount ?? "N/A";
                                                        $term = $details->term ?? "N/A";
                                                        $emp_id = $details->user_id ?? 0;
                                                        $details_data = '<i class="icon-users text-teal-400"></i> Member: ' . getCustomerName($db, $cust_id) .
                                                            ' <i class="icon-coin-dollar text-teal-400"></i> Amount: ' . $amount .
                                                            ' <i class="icon-hour-glass2 text-teal-400"></i> Term: ' . $term . ' months' .
                                                            ' <i class="icon-user text-teal-400"></i> Employee: ' . getUserFullname($db, $emp_id);
                                                        break;
                                                    default:
                                                        $details_data = "Not Set";
                                                        break;
                                                }

                                            ?>

                                        <tr>
                                            <td><?= $row['history_id'] ?></td>
                                            <td><?= $row['date_history'] ?></td>
                                            <td><?= $history_type ?></td>
                                            <td><?= $details_data ?></td>
                                        </tr>

                                    <?php
                                            }

                                            // Helper functions for MySQLi
                                            function getCustomerName($db, $cust_id)
                                            {
                                                $q = "SELECT name FROM tbl_customer WHERE cust_id='$cust_id' LIMIT 1";
                                                $res = $db->query($q);
                                                $data = $res->fetch_assoc();
                                                return $data['name'] ?? 'Unknown';
                                            }

                                            function getUserFullname($db, $user_id)
                                            {
                                                $q = "SELECT fullname FROM tbl_users WHERE user_id='$user_id' LIMIT 1";
                                                $res = $db->query($q);
                                                $data = $res->fetch_assoc();
                                                return $data['fullname'] ?? 'Unknown';
                                            }
                                    ?>

                                    <?php if (empty($i)) { ?>
                                        <tr>
                                            <td colspan="10" align="center">
                                                <h2>No data found!</h2>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </table>
                                    <br>
                                    <div align="right"><a href="system-history.php">View All History <i class="icon-circle-right2"></i></a></div>
                                </div>
                            </div>
                        </div> -->
                            <div class="col-lg-6">
                                <div class="panel panel-white border-top-xlg border-top-teal-400">
                                    <form class="heading-form" id="form-seller" method="POST">
                                        <input type="hidden" name="submit-seller">
                                        <div class="panel-heading">
                                            <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Top 5 Best Seller </h6>
                                            <div style="position: absolute;right: 0px;margin-top: -27px;margin-right: 20px;display: flex;">
                                                <input style="width: 180px" type="text" autocomplete="off" name="date" class="form-control daterange-buttons " value=" <?php if (isset($_SESSION['seller-report']) != "") { ?>   <?= $_SESSION['seller-report'] ?> <?php } else { ?> <?= date("m-d-Y") ?> - <?= date("m-d-Y") ?>  <?php } ?>">
                                                <button style="margin-left: 3px" type="submit" class="btn bg-teal-400" data-toggle="tooltip" title="Search"><b><i class="icon-search4"></i></b></button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="panel-body product-div2">
                                        <table class="table datatable-button-html5-basic table-hover table-bordered  ">
                                            <thead>
                                                <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                                    <th>Product</th>
                                                    <th>Sold</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $best = 0;
                                                $from = $_SESSION['seller-report-from'] ?? date("Y-m-d");
                                                $to = $_SESSION['seller-report-to'] ?? date("Y-m-d");
                                                $today = date("Y-m-d");
                                                $start = strtotime('today GMT');
                                                $date_add = date('Y-m-d', strtotime('+1 day', $start));

                                                if (isset($_SESSION['seller-report'])) {
                                                    if ($today == $from || $today == $to) {
                                                        $query = "SELECT tbl_sales.product_id, product_name, SUM(quantity_order) AS Totalqty
                  FROM tbl_sales
                  INNER JOIN tbl_products ON tbl_sales.product_id = tbl_products.product_id
                  WHERE sales_date BETWEEN '$today' AND '$date_add'
                  GROUP BY tbl_sales.product_id
                  ORDER BY Totalqty DESC
                  LIMIT 5";
                                                    } else {
                                                        $query = "SELECT tbl_sales.product_id, product_name, SUM(quantity_order) AS Totalqty
                  FROM tbl_sales
                  INNER JOIN tbl_products ON tbl_sales.product_id = tbl_products.product_id
                  WHERE sales_date BETWEEN '$from' AND '$to'
                  GROUP BY tbl_sales.product_id
                  ORDER BY Totalqty DESC
                  LIMIT 5";
                                                    }
                                                } else {
                                                    $query = "SELECT tbl_sales.product_id, product_name, SUM(quantity_order) AS Totalqty
              FROM tbl_sales
              INNER JOIN tbl_products ON tbl_sales.product_id = tbl_products.product_id
              WHERE sales_date BETWEEN '$today' AND '$date_add'
              GROUP BY tbl_sales.product_id
              ORDER BY Totalqty DESC
              LIMIT 5";
                                                }

                                                // Execute query with MySQLi
                                                $result_top = $db->query($query);

                                                if ($result_top && $result_top->num_rows > 0) {
                                                    while ($row_top = $result_top->fetch_assoc()) {
                                                        $best++;
                                                ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($row_top['product_name']) ?></td>
                                                            <td class="text-center"><?= htmlspecialchars($row_top['Totalqty']) ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                }

                                                if ($best == 0) {
                                                    ?>

                                                    <tr>
                                                        <td class="text-center" colspan="2">No products found!</td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>

                                            <tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="panel panel-white border-top-xlg border-top-teal-400">
                                    <div class="panel-heading">
                                        <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Low Inventory</h6>
                                    </div>
                                    <div class="panel-body product-div2">
                                        <table class="table datatable-button-html5-basic table-hover table-bordered  ">
                                            <thead>
                                                <tr style="border-bottom: 4px solid #ddd;background: #eee">
                                                    <th>Name</th>
                                                    <th>In Stock</th>
                                                </tr>
                                            </thead>
                                            <tr>
                                                <?php
                                                $query = "SELECT * FROM tbl_products WHERE quantity <= critical_qty";
                                                $result_top = $db->query($query);


                                                if ($result_top && $result_top->num_rows > 0) {
                                                    while ($row_top = $result_top->fetch_assoc()) {
                                                ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row_top['product_name']) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row_top['quantity']) ?> <?= htmlspecialchars($row_top['unit']) ?></td>
                                            </tr>
                                        <?php
                                                    }
                                                } else {
                                        ?>
                                        <tr>
                                            <td class="text-center" colspan="2">No products below critical quantity!</td>
                                        </tr>
                                    <?php
                                                }
                                    ?>

                                        </table>
                                    </div>
                                </div>
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
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>


<script type="text/javascript">
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();

    });

    $('#form-seller').on('submit', function(e) {
        $(':input[type="submit"]').prop('disabled', true);
        var data = $("#form-seller").serialize();
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
</script>

</html>