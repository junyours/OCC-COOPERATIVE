<?php
require('includes/header.php');
require('db_connect.php');
?>

<style type="text/css">
    /* Same CSS as before */
</style>

<?php
// ===================== FILTER VARIABLES =====================
$member_filter = '';
$status_filter = '';
$selected_member = '';
$status_text = 'All';

if (!empty($_POST['membername'])) {
    $membername = $db->real_escape_string($_POST['membername']);
    $member_filter = " AND m.name LIKE '%$membername%' ";
    $selected_member = $membername;
}

if (!empty($_POST['status'])) {
    $status = $db->real_escape_string($_POST['status']);
    $status_filter = " AND l.status = '$status' ";
    $status_text = ucfirst($status);
}

// ===================== SUMMARY QUERY =====================
$summary_sql = "
    SELECT
        COUNT(l.loan_id) AS total_loans,
        COALESCE(SUM(l.requested_amount),0) AS total_requested,
        COALESCE(SUM(l.approved_amount),0) AS total_approved,
        COALESCE(SUM(
            CASE 
                WHEN tt.type_name = 'loan_release'
                THEN t.amount
                ELSE 0
            END
        ),0) AS total_disbursed
    FROM loans l
    LEFT JOIN accounts a ON a.account_id = l.account_id
    LEFT JOIN tbl_members m ON m.cust_id = a.member_id
    LEFT JOIN transactions t ON t.account_id = l.account_id
    LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
    WHERE 1=1
    $member_filter
    $status_filter
";
$summary_query = $db->query($summary_sql);
$summary = $summary_query ? $summary_query->fetch_assoc() : [
    'total_loans' => 0,
    'total_requested' => 0,
    'total_approved' => 0,
    'total_disbursed' => 0
];

// ===================== LOAN TABLE QUERY =====================
$loan_sql = "
SELECT 
    l.loan_id,
    MAX(CONCAT(m.last_name, ', ', m.first_name)) AS member_name,
    l.requested_amount,
    l.approved_amount,
    l.interest_rate,
    l.term_value,
    l.term_unit,
    l.status,
    l.total_due,
    l.released_date,

    COALESCE(SUM(
        CASE 
            WHEN tt.type_name = 'loan_payment' 
            THEN t.amount 
            ELSE 0 
        END
    ), 0) AS total_paid,

    COALESCE(SUM(
        CASE 
            WHEN tt.type_name = 'loan_release' 
            THEN t.amount 
            ELSE 0 
        END
    ), 0) AS total_released

FROM loans l
LEFT JOIN accounts a ON a.account_id = l.account_id
LEFT JOIN tbl_members m ON m.cust_id = a.member_id
LEFT JOIN transactions t ON t.account_id = l.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
WHERE 1=1
$member_filter
$status_filter
GROUP BY l.loan_id, l.requested_amount, l.approved_amount, l.interest_rate, l.term_value, l.term_unit, l.status, l.total_due, l.released_date
ORDER BY l.application_date DESC
";

$loan_query = $db->query($loan_sql);
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
</style>

<body class="layout-boxed navbar-top">
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

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">

                <!-- Page Header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - Loan Report</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="javascript:;"><i class="icon-chart position-left"></i> Reports</a></li>
                            <li class="active"><i class="icon-dots position-left"></i> Loan Report</li>
                        </ul>
                    </div>
                </div>

                <div class="content">

                    <!-- Summary Panels -->
                    <div class="row">
                        <?php
                        $colors = ['bg-success-400', 'bg-blue-400', 'bg-purple-400', 'bg-orange-400'];
                        $titles = ['No. of Loans', 'Total Requested', 'Total Approved', 'Total Disbursed'];
                        $values = [
                            $summary['total_loans'],
                            number_format($summary['total_requested'], 2),
                            number_format($summary['total_approved'], 2),
                            number_format($summary['total_disbursed'], 2)
                        ];
                        foreach ($titles as $i => $title) {
                            echo '<div class="col-sm-6 col-md-3">
                                <div class="panel panel-body ' . $colors[$i] . ' has-bg-image">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class=" icon-3x opacity-75">₱</i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3>' . $values[$i] . '</h3>
                                            <span class="text-uppercase text-size-mini">' . $title . '</span>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                        ?>
                    </div>

                    <!-- Filter Form -->
                    <div class="panel panel-body">
                        <form class="heading-form" id="form-loan" method="POST">
                            <input type="hidden" name="submit-loan">
                            <input type="hidden" id="input-status" name="status" value="">
                            <ul class="breadcrumb-elements" style="float:left">
                                <li style="padding-top: 2px;padding-right: 2px">
                                    <div class="btn-group">
                                        <input style="width: 230px" autocomplete="off" type="search" class="form-control"
                                            id="member-input" value="<?php echo $selected_member; ?>" name="membername">
                                        <span id="searchclearmember" class="glyphicon glyphicon-remove-circle"></span>
                                        <div id="show-search-member"></div>
                                    </div>
                                </li>
                                <li class="text-center" style="padding-top: 2px;padding-right: 2px;width:auto;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-rounded dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li onclick="select_status(this)" status-val="pending" status-name="Pending"><a href="#">Pending</a></li>
                                            <li onclick="select_status(this)" status-val="approved" status-name="Approved"><a href="#">Approved</a></li>
                                            <li onclick="select_status(this)" status-val="disbursed" status-name="Disbursed"><a href="#">Disbursed</a></li>
                                            <li onclick="select_status(this)" status-val="rejected" status-name="Rejected"><a href="#">Rejected</a></li>
                                            <li onclick="select_status(this)" status-val="" status-name="All"><a href="#">All</a></li>
                                        </ul>
                                    </div>
                                </li>
                                <li style="padding-top: 2px;padding-right: 2px">
                                    <button type="submit" class="btn bg-teal-400"><b><i class="icon-search4"></i></b></button>
                                </li>
                                <li style="padding-top: 2px;padding-right: 2px">
                                    <button type="button" onclick="clear_filter()" class="btn bg-slate-400"><b><i class="icon-filter4"></i></b></button>
                                </li>
                            </ul>
                        </form>
                    </div>

                    <!-- Loan Table -->
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400"></i> List of Loans</h6>
                        </div>

                        <div class="panel-body">
                            <table class="table datatable-loan table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Loan No.</th>
                                        <th>Member Name</th>
                                        <th>Requested Amount</th>
                                        <th>Approved Amount</th>
                                        <th>Disbursed Amount</th>
                                        <th>Term</th>
                                        <th>Interest Rate</th>
                                        <th>Status</th>
                                        <th>Repayment Progress</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    while ($row = $loan_query->fetch_assoc()) {
                                        $progress = ($row['total_due'] > 0)
                                            ? round(($row['total_paid'] / $row['total_due']) * 100, 2)
                                            : 0;

                                        echo "<tr>";
                                        echo "<td>{$row['loan_id']}</td>";
                                        echo "<td>" . htmlspecialchars($row['member_name'] ?? '') . "</td>";
                                        echo "<td align='right'>" . number_format($row['requested_amount'], 2) . "</td>";
                                        echo "<td align='right'>" . number_format($row['approved_amount'], 2) . "</td>";
                                        echo "<td align='right'>" . number_format($row['total_released'], 2) . "</td>";
                                        echo "<td align='center'>{$row['term_value']} {$row['term_unit']}</td>";
                                        echo "<td align='center'>{$row['interest_rate']}%</td>";
                                        echo "<td>" . ucfirst($row['status']) . "</td>";
                                        echo "<td align='center'>{$progress}%</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div><!-- content -->
            </div>
        </div>
    </div>
    <?php require('includes/footer-text.php'); ?>
    <?php require('includes/footer.php'); ?>

    <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>

    <script>
        function select_status(el) {
            var val = el.getAttribute('status-val');
            var name = el.getAttribute('status-name');
            document.getElementById('input-status').value = val;
        }

        function clear_filter() {
            document.getElementById('member-input').value = '';
            document.getElementById('input-status').value = '';
            document.getElementById('form-loan').submit();
        }
    </script>
</body>