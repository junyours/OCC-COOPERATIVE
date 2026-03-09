<?php
require('includes/header.php');
require('../db_connect.php');

if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype']) || !in_array((int)$_SESSION['usertype'], [1, 3])) {
    die("Unauthorized access.");
}

// 1. HANDLE FILTERS
$filter_date = $_POST['date'] ?? (date("m/d/Y") . " - " . date("m/d/Y"));
$member_id = $_POST['member_id'] ?? "";
$trans_type = $_POST['trans_type'] ?? "";

// Split date range for SQL
$dates = explode(" - ", $filter_date);
$start_date = date('Y-m-d', strtotime($dates[0]));
$end_date = date('Y-m-d', strtotime($dates[1]));

// 2. CONSTRUCT QUERY (Removed u.username and user_id join)
$where = "WHERE DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
if (!empty($member_id)) {
    $where .= " AND a.member_id = '$member_id'";
}
if (!empty($trans_type)) {
    $where .= " AND a.account_type_id = '$trans_type'";
}

$main_sql = "SELECT t.*, m.first_name, m.last_name, at.type_name as account_cat, tt.type_name as action_name
             FROM transactions t
             JOIN accounts a ON t.account_id = a.account_id
             JOIN account_types at ON a.account_type_id = at.account_type_id
             JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
             JOIN tbl_members m ON a.member_id = m.member_id
             $where 
             ORDER BY t.transaction_date DESC";

$results = $db->query($main_sql);

// 3. SUMMARY CALCULATIONS
$total_logs = 0;
$total_in = 0;
$total_out = 0;

$data_rows = [];
if ($results && $results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
        $data_rows[] = $row;
        $total_logs++;
        if ($row['status'] == 'active') {
            // transaction_type_id 2 = Withdrawal (Outflow)
            if ($row['transaction_type_id'] == 2) {
                $total_out += abs($row['amount']);
            } else {
                // 1 = Savings, 3 = Capital (Inflow)
                $total_in += $row['amount'];
            }
        }
    }
}
$net_change = $total_in - $total_out;

// Get member name for input field if filtered
$selected_member_name = "";
if ($member_id) {
    $res = $db->query("SELECT first_name, last_name FROM tbl_members WHERE member_id='$member_id'");
    if ($row = $res->fetch_assoc()) $selected_member_name = $row['first_name'] . " " . $row['last_name'];
}
?>

<style type="text/css">
    /* Your existing styles here... */
    .navbar-brand {
        display: flex;
        align-items: center;
        font-weight: 800;
        color: white !important;
        font-size: 14px;
        line-height: 1.2;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        margin-right: 12px;
        border-radius: 20px;
    }
</style>

<body class="layout-boxed navbar-top">

    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../../images/main_logo.jpg" alt="Logo">
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
                <div class="content">

                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-success-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle"><i class="icon-history icon-3x opacity-75"></i></div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= $total_logs ?></h3>
                                        <span class="text-uppercase text-size-mini">Total Logs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-blue-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle"><i class="icon-cash3 icon-3x opacity-75"></i></div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($total_in, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Total Inflow</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-danger-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle"><i class="icon-cash3 icon-3x opacity-75"></i></div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($total_out, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Total Outflow</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-indigo-400">
                                <div class="media no-margin">
                                    <div class="media-left media-middle"><i class="icon-stats-growth icon-3x opacity-75"></i></div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin"><?= number_format($net_change, 2) ?></h3>
                                        <span class="text-uppercase text-size-mini">Net Change</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-body">
                        <form id="form-audit" method="POST">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="icon-calendar22"></i></span>
                                        <input type="text" name="date" class="form-control daterange-buttons" value="<?= $filter_date ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="hidden" name="member_id" id="member_id" value="<?= $member_id ?>">
                                    <input type="text" class="form-control" id="member-input" placeholder="Search Member..." value="<?= $selected_member_name ?>">
                                </div>
                                <div class="col-md-2">
                                    <select name="trans_type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="1" <?= ($trans_type == '1') ? 'selected' : '' ?>>Savings (SAV)</option>
                                        <option value="2" <?= ($trans_type == '2') ? 'selected' : '' ?>>Capital Share (CS)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn bg-teal-400"><i class="icon-search4"></i> Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="table-responsive">
                            <table class="table datatable-basic table-hover table-bordered">
                                <thead>
                                    <tr class="bg-teal-400">
                                        <th>Timestamp</th>
                                        <th>Ref No.</th>
                                        <th>Member Name</th>
                                        <th>Trans. Type</th>
                                        <th>Debit (Out)</th>
                                        <th>Credit (In)</th>
                                        <th>Encoder</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_rows as $row): ?>
                                        <tr class="<?= ($row['status'] == 'voided') ? 'text-muted' : '' ?>">
                                            <td><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                                            <td><code><?= $row['reference_no'] ?></code></td>
                                            <td><b><?= strtoupper($row['last_name'] . ', ' . $row['first_name']) ?></b></td>
                                            <td><?= $row['account_cat'] ?></td>
                                            <td class="text-danger"><?= ($row['transaction_type_id'] == 2) ? number_format($row['amount'], 2) : '-' ?></td>
                                            <td class="text-success"><?= ($row['transaction_type_id'] != 2) ? number_format($row['amount'], 2) : '-' ?></td>
                                            <td><?= $row['encoder'] ?? 'System' ?></td>
                                            <td>
                                                <span class="label <?= ($row['status'] == 'active') ? 'label-success' : 'label-default' ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
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

    <?php require('includes/footer.php'); ?>
    <script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>
</body>