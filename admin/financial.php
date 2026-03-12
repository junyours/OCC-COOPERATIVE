<?php
require('includes/header.php');


function money($value)
{
    if ($value == 0 || $value === null) {
        return '~';
    }
    return rtrim(rtrim(number_format($value, 2), '0'), '.');
}



// Get current year
$currentYear = date('Y');

// -------------------------
// Capital Share Breakdown per Member (Current Year Only) - NEW STRUCTURE
// -------------------------
$shares = $db->query("
    SELECT 
        m.member_id,
        CONCAT(m.first_name,' ',m.last_name) AS name,

        SUM(CASE WHEN MONTH(t.transaction_date) = 1 THEN t.amount ELSE 0 END) AS Jan,
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 THEN t.amount ELSE 0 END) AS Feb,
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 THEN t.amount ELSE 0 END) AS Mar,
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 THEN t.amount ELSE 0 END) AS Apr,
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 THEN t.amount ELSE 0 END) AS May,
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 THEN t.amount ELSE 0 END) AS Jun,
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 THEN t.amount ELSE 0 END) AS Jul,
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 THEN t.amount ELSE 0 END) AS Aug,
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 THEN t.amount ELSE 0 END) AS Sep,
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 THEN t.amount ELSE 0 END) AS Oct,
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 THEN t.amount ELSE 0 END) AS Nov,
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 THEN t.amount ELSE 0 END) AS `Dec`,

        SUM(t.amount) AS Total

    FROM tbl_members m

    LEFT JOIN accounts a 
        ON a.member_id = m.member_id

    LEFT JOIN account_types at 
        ON at.account_type_id = a.account_type_id

    LEFT JOIN transactions t 
        ON t.account_id = a.account_id
        AND YEAR(t.transaction_date) = '$currentYear'

    WHERE at.type_name = 'capital_share'

    GROUP BY m.member_id

    ORDER BY name ASC
");


$overall = $db->query("
    SELECT 

        SUM(CASE WHEN MONTH(t.transaction_date) = 1 THEN t.amount ELSE 0 END) AS Jan,
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 THEN t.amount ELSE 0 END) AS Feb,
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 THEN t.amount ELSE 0 END) AS Mar,
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 THEN t.amount ELSE 0 END) AS Apr,
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 THEN t.amount ELSE 0 END) AS May,
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 THEN t.amount ELSE 0 END) AS Jun,
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 THEN t.amount ELSE 0 END) AS Jul,
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 THEN t.amount ELSE 0 END) AS Aug,
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 THEN t.amount ELSE 0 END) AS Sep,
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 THEN t.amount ELSE 0 END) AS Oct,
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 THEN t.amount ELSE 0 END) AS Nov,
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 THEN t.amount ELSE 0 END) AS `Dec`,

        SUM(t.amount) AS Total

    FROM transactions t

    INNER JOIN accounts a 
        ON a.account_id = t.account_id

    INNER JOIN account_types at 
        ON at.account_type_id = a.account_type_id

    WHERE at.type_name = 'capital_share'
    AND YEAR(t.transaction_date) = '$currentYear'
")->fetch_assoc();


// -------------------------
// Savings Breakdown per Member (Current Year Only)
// -------------------------
$savings = $db->query("
    SELECT 
        m.member_id,
        CONCAT(m.first_name,' ',m.last_name) AS name,

        SUM(CASE WHEN MONTH(t.transaction_date) = 1 THEN t.amount ELSE 0 END) AS Jan,
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 THEN t.amount ELSE 0 END) AS Feb,
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 THEN t.amount ELSE 0 END) AS Mar,
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 THEN t.amount ELSE 0 END) AS Apr,
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 THEN t.amount ELSE 0 END) AS May,
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 THEN t.amount ELSE 0 END) AS Jun,
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 THEN t.amount ELSE 0 END) AS Jul,
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 THEN t.amount ELSE 0 END) AS Aug,
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 THEN t.amount ELSE 0 END) AS Sep,
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 THEN t.amount ELSE 0 END) AS Oct,
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 THEN t.amount ELSE 0 END) AS Nov,
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 THEN t.amount ELSE 0 END) AS `Dec`,

        SUM(t.amount) AS Total

    FROM tbl_members m

    LEFT JOIN accounts a 
        ON a.member_id = m.member_id

    LEFT JOIN account_types at 
        ON at.account_type_id = a.account_type_id

    LEFT JOIN transactions t 
        ON t.account_id = a.account_id
        AND YEAR(t.transaction_date) = '$currentYear'

    WHERE at.type_name = 'savings'

    GROUP BY m.member_id
    ORDER BY name ASC
");

// Overall Totals
$overall_savings = $db->query("
    SELECT 
        SUM(CASE WHEN MONTH(t.transaction_date) = 1 THEN t.amount ELSE 0 END) AS Jan,
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 THEN t.amount ELSE 0 END) AS Feb,
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 THEN t.amount ELSE 0 END) AS Mar,
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 THEN t.amount ELSE 0 END) AS Apr,
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 THEN t.amount ELSE 0 END) AS May,
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 THEN t.amount ELSE 0 END) AS Jun,
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 THEN t.amount ELSE 0 END) AS Jul,
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 THEN t.amount ELSE 0 END) AS Aug,
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 THEN t.amount ELSE 0 END) AS Sep,
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 THEN t.amount ELSE 0 END) AS Oct,
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 THEN t.amount ELSE 0 END) AS Nov,
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 THEN t.amount ELSE 0 END) AS `Dec`,
        SUM(t.amount) AS Total_Savings
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE at.type_name = 'savings'
    AND YEAR(t.transaction_date) = '$currentYear'
")->fetch_assoc();

// -------------------------
// Members Purchases Breakdown (Current Year Only)
// -------------------------
$purchases = $db->query("

SELECT 
    c.cust_id,
    c.name,

    COALESCE(cs.total_cash_sales, 0) AS total_cash_sales,

    COALESCE(cp.total_paid_charge, 0) AS total_paid_charge,

    COALESCE(cs.total_cash_sales, 0) +
    COALESCE(cp.total_paid_charge, 0) AS total_purchase


FROM tbl_customer c


LEFT JOIN (

    SELECT 
        cust_id,
        SUM(total_amount) AS total_cash_sales
    FROM (

        SELECT 
            sales_no,
            cust_id,
            MAX(total_amount) AS total_amount
        FROM tbl_sales
        WHERE sales_type = 1
        AND YEAR(sales_date) = '$currentYear'
        GROUP BY sales_no, cust_id

    ) cash_sales

    GROUP BY cust_id

) cs ON c.cust_id = cs.cust_id



LEFT JOIN (

    SELECT 
        cust_id,
        SUM(amount_paid) AS total_paid_charge
    FROM (

        SELECT 
            s.sales_no,
            s.cust_id,
            SUM(p.amount_paid) AS amount_paid
        FROM tbl_sales s
        INNER JOIN tbl_payments p 
            ON s.sales_no = p.sales_no
        WHERE s.sales_type = 0
        AND YEAR(s.sales_date) = '$currentYear'
        GROUP BY s.sales_no, s.cust_id

    ) charge_payment

    GROUP BY cust_id

) cp ON c.cust_id = cp.cust_id



WHERE c.cust_id != 1

ORDER BY c.name ASC

");

// -------------------------
// Overall Totals (All Members) - Purchases (Current Year)
// -------------------------
$overall_purchase = $db->query("

SELECT 

COALESCE(SUM(total_cash_sales),0) +
COALESCE(SUM(total_paid_charge),0)

AS total_purchase


FROM (

    SELECT 
        cust_id,
        SUM(total_amount) AS total_cash_sales,
        0 AS total_paid_charge
    FROM (

        SELECT 
            sales_no,
            cust_id,
            MAX(total_amount) AS total_amount
        FROM tbl_sales
        WHERE sales_type = 1
        AND YEAR(sales_date) = '$currentYear'
        GROUP BY sales_no, cust_id

    ) cash

    GROUP BY cust_id



    UNION ALL



    SELECT 
        cust_id,
        0,
        SUM(amount_paid)
    FROM (

        SELECT 
            s.sales_no,
            s.cust_id,
            SUM(p.amount_paid) AS amount_paid
        FROM tbl_sales s
        JOIN tbl_payments p 
            ON s.sales_no = p.sales_no
        WHERE s.sales_type = 0
        AND YEAR(s.sales_date) = '$currentYear'
        GROUP BY s.sales_no, s.cust_id

    ) charge

    GROUP BY cust_id


) totals

")->fetch_assoc();

// Load members for Add Contribution Modal
$members = $db->query("
    SELECT member_id, first_name, last_name
    FROM tbl_members
    WHERE type = 'regular'
    ORDER BY last_name ASC, first_name ASC
");
$membersArray = $members->fetch_all(MYSQLI_ASSOC);

$all_members = $db->query("
    SELECT member_id, first_name, last_name
    FROM tbl_members
    ORDER BY last_name ASC, first_name ASC
");
$allMembersArray = $all_members->fetch_all(MYSQLI_ASSOC);
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

    /* Make the whole panel clickable and hoverable */
    .panel-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .hover-panel {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-panel:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        cursor: pointer;
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
    <!-- /Navbar -->

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">

                <!-- Page header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4>Members' Financial (<?= $currentYear ?>)</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li class="active"><i class="icon-cash3 position-left"></i> Members' Financial</li>
                        </ul>
                        <ul class="breadcrumb-elements">
                            <li>
                                <a href="javascript:;" data-toggle="modal" data-target="#modal_savings">
                                    <i class="icon-coins text-blue-400"></i> Add Savings
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" data-toggle="modal" data-target="#modal_share">
                                    <i class="icon-coin-dollar text-teal-400"></i> Add Capital Share
                                </a>
                            </li>
                            <li>
                                <a href="alltransactions.php">
                                    <i class="icon-stats-bars2 text-orange-400"></i> Accounting Terminal
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /Page header -->

                <div class="content">

                    <!-- Summary Cards -->
                    <div class="row">
                        <!-- Capital Shares Panel -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="capital_share_report.php" class="panel-link">
                                <div class="panel bg-success-400 hover-panel" style="padding:10px; border-radius:5px;">
                                    <div class="panel-body text-center" style="padding:10px;">
                                        <h4 class="no-margin" style="font-size:18px;">₱ <?= number_format($overall['Total'] ?? 0, 2) ?></h4>
                                        <small>Total Capital Shares (<?= $currentYear ?>)</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Savings Panel -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="savings_report.php" class="panel-link">
                                <div class="panel bg-primary-400 hover-panel" style="padding:10px; border-radius:5px;">
                                    <div class="panel-body text-center" style="padding:10px;">
                                        <h4 class="no-margin" style="font-size:18px;">₱ <?= number_format($overall_savings['Total_Savings'] ?? 0, 2) ?></h4>
                                        <small>Total Savings (<?= $currentYear ?>)</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Member Purchases Panel -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="sales-report.php" class="panel-link">
                                <div class="panel bg-danger-400 hover-panel" style="padding:10px; border-radius:5px;">
                                    <div class="panel-body text-center" style="padding:10px;">
                                        <h4 class="no-margin" style="font-size:18px;">₱ <?= number_format($overall_purchase['total_purchase'] ?? 0, 2) ?></h4>
                                        <small>Total Member Patronage (<?= $currentYear ?>)</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <!-- /Summary Cards -->

                    <!-- Capital Share Breakdown Table -->
                    <div class="panel panel-white border-top-xlg border-top-success">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-cash3 text-success position-left"></i>Capital Share Contributions by Month (<?= $currentYear ?>)</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table datatable-button-html5-basic table-hover table-bordered">

                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Jan</th>
                                        <th>Feb</th>
                                        <th>Mar</th>
                                        <th>Apr</th>
                                        <th>May</th>
                                        <th>Jun</th>
                                        <th>Jul</th>
                                        <th>Aug</th>
                                        <th>Sep</th>
                                        <th>Oct</th>
                                        <th>Nov</th>
                                        <th>Dec</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $shares->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name']); ?></td>
                                            <?php for ($m = 1; $m <= 12; $m++) {
                                                $monthName = date('M', mktime(0, 0, 0, $m, 1));
                                                echo '<td style="text-align:right">' . money($row[$monthName]) . '</td>';
                                            } ?>

                                            <td style="text-align:right"><b><?= money($row['Total']); ?></b></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- Overall Totals -->
                            <div class="well" style="margin-top:20px;">
                                <h5><b>Overall Totals (All Members - <?= $currentYear ?>)</b></h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Jan</th>
                                        <th>Feb</th>
                                        <th>Mar</th>
                                        <th>Apr</th>
                                        <th>May</th>
                                        <th>Jun</th>
                                        <th>Jul</th>
                                        <th>Aug</th>
                                        <th>Sep</th>
                                        <th>Oct</th>
                                        <th>Nov</th>
                                        <th>Dec</th>
                                        <th>Total</th>
                                    </tr>
                                    <tr style="font-weight:bold;">
                                        <?php
                                        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                        foreach ($months as $m) {
                                            echo '<td style="text-align:right">' . money($overall[$m]) . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right"><?= money($overall['Total']); ?></td>

                                    </tr>
                                </table>
                            </div>

                        </div>
                    </div>

                    <!-- Savings Breakdown Table -->
                    <div class="panel panel-white border-top-xlg border-top-info">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-coins text-blue position-left"></i>Savings by Month (<?= $currentYear ?>)</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table datatable-button-html5-basic table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Jan</th>
                                        <th>Feb</th>
                                        <th>Mar</th>
                                        <th>Apr</th>
                                        <th>May</th>
                                        <th>Jun</th>
                                        <th>Jul</th>
                                        <th>Aug</th>
                                        <th>Sep</th>
                                        <th>Oct</th>
                                        <th>Nov</th>
                                        <th>Dec</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $savings->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name']); ?></td>
                                            <?php
                                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                            foreach ($months as $m) {
                                                echo '<td style="text-align:right">' . money($row[$m]) . '</td>';
                                            }
                                            ?>
                                            <td style="text-align:right; font-weight:bold;"><?= money($row['Total']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- Overall Totals -->
                            <div class="well" style="margin-top:20px;">
                                <h5><b>Overall Totals (All Members - <?= $currentYear ?>)</b></h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <?php foreach ($months as $m) {
                                            echo '<th>' . $m . '</th>';
                                        } ?>
                                        <th>Total</th>
                                    </tr>
                                    <tr style="font-weight:bold;">
                                        <?php foreach ($months as $m) {
                                            echo '<td style="text-align:right">' . money($overall_savings[$m]) . '</td>';
                                        } ?>
                                        <td style="text-align:right"><?= money($overall_savings['Total_Savings']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Members Purchases Table -->
                    <div class="panel panel-white border-top-xlg border-top-danger">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-cart text-danger position-left"></i> Members' Patronage (<?= $currentYear ?>)</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table datatable-button-html5-basic table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Cash Sales</th>
                                        <th>Paid Charge Sales</th>
                                        <th>Total Purchases</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $purchases->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name']); ?></td>
                                            <td style="text-align:right"><?= money($row['total_cash_sales']); ?></td>
                                            <td style="text-align:right"><?= money($row['total_paid_charge']); ?></td>
                                            <td style="text-align:right; font-weight:bold;"><?= money($row['total_purchase']); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>


                            <div class="well" style="margin-top:20px;">
                                <h5><b>Overall Total Patronage (All Members - <?= $currentYear ?>)</b></h5>
                                <h4 style="text-align:right; color:#ff0000;">₱ <?= money($overall_purchase['total_purchase']); ?></h4>

                            </div>
                        </div>
                    </div>

                </div>
                <?php require('includes/footer-text.php'); ?>
            </div>
        </div>
    </div>


    <?php require('includes/footer.php'); ?>


    <!-- Modal Add Savings -->
    <div id="modal_savings" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Add Savings</h5>
                </div>
                <div class="modal-bodys">
                    <form id="form-savings" class="form-horizontal" data-toggle="validator" role="form">
                        <input type="hidden" name="save-savings" value="1">

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Member</label>
                            <div class="col-sm-9">
                                <select class="form-control select-member" name="member_id" required>
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($allMembersArray as $m) { ?>
                                        <option value="<?= $m['member_id']; ?>">
                                            <?= htmlspecialchars($m['last_name'] . ', ' . $m['first_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Amount</label>
                            <div class="col-sm-9">
                                <input class="form-control" name="amount" type="number" step="0.01" min="0" placeholder="Enter amount (₱)" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn bg-blue-400 btn-labeled">
                                <b><i class="icon-add"></i></b> Save Savings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Add Capital Share -->
    <div id="modal_share" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Add Capital Share</h5>
                </div>

                <div class="modal-bodys">

                    <form action="#" id="form-share" class="form-horizontal" data-toggle="validator" role="form">
                        <input type="hidden" name="save-capital-share" value="1">


                        <div class="form-body" style="padding-top: 20px">
                            <div id="display-msg"></div>

                            <!-- Member -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Member</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="icon-user"></i>
                                        </span>
                                        <select class="form-control select-member" name="member_id" required>
                                            <option value="">-- Select Regular Member --</option>

                                            <?php foreach ($membersArray as $m) { ?>
                                                <option value="<?= $m['member_id']; ?>">
                                                    <?= htmlspecialchars($m['last_name'] . ', ' . $m['first_name']); ?>
                                                </option>
                                            <?php } ?>

                                        </select>


                                    </div>

                                </div>
                            </div>
                            <!-- Amount -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Amount</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-coin-dollar text-size-base"></i></span>
                                        <input class="form-control filterme" name="amount" type="number" step="0.01" min="0" placeholder="Enter amount (₱)" data-error="Please enter a valid amount." required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled">
                                    <b><i class="icon-add"></i></b> Save Contribution
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <script src="../js/select2.min.js"></script>

    <script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/validator.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#modal_share .select-member').select2({
                dropdownParent: $('#modal_share'),
                placeholder: "Search Member",
                allowClear: true,
                width: '100%'
            });

            $('#modal_savings .select-member').select2({
                dropdownParent: $('#modal_savings'),
                placeholder: "Search Member",
                allowClear: true,
                width: '100%'
            });
        });

        $(function() {
            $('.datatable-button-html5-basic').DataTable({
                "order": [
                    [0, "asc"]
                ],
                "aLengthMenu": [
                    [5, 15, 100],
                    [5, 15, 100]
                ]
            });

            // Handle Capital Share Form
            $('#form-share').validator().on('submit', function(e) {
                if (!e.isDefaultPrevented()) {
                    var data = $(this).serialize();
                    $.ajax({
                        type: "POST",
                        url: "../transaction.php",
                        data: data,
                        success: function(resp) {
                            resp = resp.trim(); // remove extra spaces/newlines

                            // Check for error from server
                            if (resp.startsWith("Error:") || resp.includes("Minimum monthly capital share")) {
                                $.jGrowl(resp, {
                                    header: 'Error',
                                    theme: 'bg-danger'
                                });
                            } else {
                                $.jGrowl("Contribution saved successfully!", {
                                    header: 'Success',
                                    theme: 'bg-success'
                                });
                                $('#modal_share').modal('hide');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        },
                        error: function() {
                            $.jGrowl("Error saving contribution.", {
                                header: 'Error',
                                theme: 'bg-danger'
                            });
                        }
                    });
                    return false;
                }
            });
        });

        $('#form-savings').validator().on('submit', function(e) {
            if (!e.isDefaultPrevented()) {
                var data = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "../transaction.php",
                    data: data,
                    success: function(resp) {
                        resp = resp.trim(); // remove extra whitespace/newlines

                        if (resp.startsWith("Error:")) {
                            // Show error returned from server
                            $.jGrowl(resp, {
                                header: 'Error',
                                theme: 'bg-danger'
                            });
                        } else if (resp == "1") {
                            // Only show success if server returned "1"
                            $.jGrowl("Savings saved successfully!", {
                                header: 'Success',
                                theme: 'bg-success'
                            });
                            $('#modal_savings').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            // Unexpected response
                            $.jGrowl("Unexpected response: " + resp, {
                                header: 'Warning',
                                theme: 'bg-warning'
                            });
                        }
                    },
                    error: function() {
                        $.jGrowl("Error saving savings.", {
                            header: 'Error',
                            theme: 'bg-danger'
                        });
                    }
                });
                return false;
            }
        });
    </script>