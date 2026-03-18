<?php
require('includes/header.php');
require('../db_connect.php');
?>

<style type="text/css">
    /* Same CSS as before */
</style>

<?php
// ===================== FILTER VARIABLES =====================
$member_filter = '';
$status_filter = '';
$date_filter = '';
$selected_member = '';
$status_text = 'All';
$date_from = '';
$date_to = '';
$status = '';

if (!empty($_POST['membername'])) {
    $membername = $db->real_escape_string($_POST['membername']);
    $member_filter = " AND (CONCAT(m.first_name, ' ', m.last_name) LIKE '%$membername%' OR m.first_name LIKE '%$membername%' OR m.last_name LIKE '%$membername%') ";
    $selected_member = $membername;
}

if (!empty($_POST['status'])) {
    $status = $db->real_escape_string($_POST['status']);
    $status_filter = " AND l.status = '$status' ";
    $status_text = ucfirst($status);
}

if (!empty($_POST['date_from'])) {
    $date_from = $db->real_escape_string($_POST['date_from']);
    $date_filter .= " AND DATE(l.application_date) >= '$date_from' ";
}

if (!empty($_POST['date_to'])) {
    $date_to = $db->real_escape_string($_POST['date_to']);
    $date_filter .= " AND DATE(l.application_date) <= '$date_to' ";
}

// ===================== SUMMARY QUERY =====================
$summary_sql = "
    SELECT
        COUNT(l.loan_id) AS total_loans,
        COALESCE(SUM(l.requested_amount),0) AS total_requested,
        COALESCE(SUM(l.approved_amount),0) AS total_approved,
        COALESCE(SUM(l.total_due),0) AS total_disbursed
    FROM loans l
    LEFT JOIN accounts a ON a.account_id = l.account_id
    LEFT JOIN tbl_members m ON m.member_id = a.member_id
    WHERE 1=1
    $member_filter
    $status_filter
    $date_filter
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
    CONCAT(m.last_name, ', ', m.first_name) AS member_name,
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

    l.approved_amount AS total_disbursed,
    
    -- Calculate expected total (principal + interest)
    (l.approved_amount + (l.approved_amount * (l.interest_rate / 100) * l.term_value / 12)) AS expected_total

FROM loans l
LEFT JOIN accounts a ON a.account_id = l.account_id
LEFT JOIN tbl_members m ON m.member_id = a.member_id
LEFT JOIN transactions t ON t.account_id = l.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
WHERE 1=1
$member_filter
$status_filter
$date_filter
GROUP BY l.loan_id, l.requested_amount, l.approved_amount, l.interest_rate, l.term_value, l.term_unit, l.status, l.total_due, l.released_date, m.first_name, m.last_name
ORDER BY l.application_date DESC
";

$loan_query = $db->query($loan_sql);
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

                    <!-- Enhanced Filter Form -->
                    <div class="panel panel-body">
                        <form class="form-horizontal" id="form-loan" method="POST">
                            <input type="hidden" name="submit-loan">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Member:</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="member-input" 
                                                   value="<?php echo $selected_member; ?>" name="membername" 
                                                   placeholder="Search member...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Status:</label>
                                        <div class="col-md-9">
                                            <select class="form-control" name="status" id="status-select">
                                                <option value="">All Status</option>
                                                <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo ($status == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                                <option value="disbursed" <?php echo ($status == 'disbursed') ? 'selected' : ''; ?>>Disbursed</option>
                                                <option value="rejected" <?php echo ($status == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date From:</label>
                                        <div class="col-md-9">
                                            <input type="date" class="form-control" name="date_from" 
                                                   value="<?php echo $date_from; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date To:</label>
                                        <div class="col-md-9">
                                            <input type="date" class="form-control" name="date_to" 
                                                   value="<?php echo $date_to; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-12 text-right">
                                            <button type="submit" class="btn bg-teal-400">
                                                <i class="icon-search4"></i> Search
                                            </button>
                                            <button type="button" onclick="clear_filter()" class="btn bg-slate-400">
                                                <i class="icon-filter4"></i> Clear
                                            </button>
                                            <button type="button" onclick="export_csv()" class="btn bg-blue-400">
                                                <i class="icon-file-excel"></i> Export CSV
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                    // Helper functions for styling
                                    function getStatusColor($status) {
                                        switch($status) {
                                            case 'pending': return 'warning';
                                            case 'approved': return 'info';
                                            case 'disbursed': return 'success';
                                            case 'rejected': return 'danger';
                                            default: return 'default';
                                        }
                                    }
                                    
                                    function getProgressColor($progress) {
                                        if ($progress >= 80) return 'progress-bar-success';
                                        if ($progress >= 50) return 'progress-bar-warning';
                                        return 'progress-bar-danger';
                                    }
                                    
                                    while ($row = $loan_query->fetch_assoc()) {
                                        // Use expected_total for progress calculation
                                        $expected_total = $row['expected_total'] > 0 ? $row['expected_total'] : $row['approved_amount'];
                                        $progress = ($expected_total > 0)
                                            ? round(($row['total_paid'] / $expected_total) * 100, 2)
                                            : 0;
                                        
                                        // Only cap at 100% for paid/completed loans
                                        if ($row['status'] === 'paid' || $row['status'] === 'completed') {
                                            $progress = min(100, $progress);
                                        }

                                        echo "<tr>";
                                        echo "<td>
                                                <a href='javascript:void(0);' onclick='view_loan_details({$row['loan_id']})' 
                                                   class='btn-link text-teal-400' title='View Details'>
                                                   {$row['loan_id']}
                                                </a>
                                              </td>";
                                        echo "<td>" . htmlspecialchars($row['member_name'] ?? '') . "</td>";
                                        echo "<td align='right'>" . number_format($row['requested_amount'], 2) . "</td>";
                                        echo "<td align='right'>" . number_format($row['approved_amount'], 2) . "</td>";
                                        echo "<td align='right'>" . number_format($row['total_disbursed'], 2) . "</td>";
                                        echo "<td align='center'>{$row['term_value']} {$row['term_unit']}</td>";
                                        echo "<td align='center'>{$row['interest_rate']}%</td>";
                                        echo "<td><span class='label label-" . getStatusColor($row['status']) . "'>" . ucfirst($row['status']) . "</span></td>";
                                        echo "<td align='center'>
                                                <div class='progress' style='margin-bottom: 0; height: 20px;'>
                                                    <div class='progress-bar " . getProgressColor($progress) . "' 
                                                         style='width: {$progress}%' 
                                                         title='{$progress}%'>
                                                        {$progress}%
                                                    </div>
                                                </div>
                                              </td>";
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
        // Member autocomplete functionality
        $(document).ready(function() {
            $('#member-input').on('input', function() {
                var query = $(this).val();
                if (query.length >= 2) {
                    $.ajax({
                        url: 'ajax_get_members.php',
                        type: 'POST',
                        data: { query: query },
                        success: function(data) {
                            $('#show-search-member').html(data).show();
                        }
                    });
                } else {
                    $('#show-search-member').hide();
                }
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.member-search').length) {
                    $('#show-search-member').hide();
                }
            });
        });

        function select_member(name) {
            $('#member-input').val(name);
            $('#show-search-member').hide();
        }

        function clear_filter() {
            document.getElementById('member-input').value = '';
            document.getElementById('status-select').value = '';
            document.querySelector('input[name="date_from"]').value = '';
            document.querySelector('input[name="date_to"]').value = '';
            document.getElementById('form-loan').submit();
        }

        function export_csv() {
            var form = document.getElementById('form-loan');
            var formData = new FormData(form);
            
            // Create export URL with current filters
            var exportUrl = 'export_loan_report.php?' + new URLSearchParams(formData).toString();
            window.open(exportUrl, '_blank');
        }

        // Enhanced DataTable initialization
        $(document).ready(function() {
            $('.datatable-loan').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { targets: [0], width: '80px' },
                    { targets: [1], width: '200px' },
                    { targets: [2, 3, 4], width: '120px', className: 'text-right' },
                    { targets: [5], width: '100px', className: 'text-center' },
                    { targets: [6], width: '100px', className: 'text-center' },
                    { targets: [7], width: '100px' },
                    { targets: [8], width: '120px', className: 'text-center' }
                ],
                language: {
                    search: "Search loans:",
                    lengthMenu: "Show _MENU_ loans per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ loans",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });

        function view_loan_details(loanId) {
            // You can implement a modal or redirect to a details page
            alert('Loan details view for Loan ID: ' + loanId + '\n\nThis can be expanded to show a modal with full loan details including payment history, schedule, etc.');
        }
    </script>
</body>