<?php require('includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../db_connect.php');

// Get all loans with member information
$loansQuery = "
    SELECT l.*, lt.loan_type_name, m.member_id, m.first_name, m.last_name, m.middle_name, m.email,
           CONCAT(m.first_name, ' ', IF(m.middle_name != '', CONCAT(m.middle_name, ' '), ''), m.last_name) as full_name,
           a.account_id
    FROM loans l
    LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
    LEFT JOIN accounts a ON l.account_id = a.account_id
    LEFT JOIN tbl_members m ON a.member_id = m.member_id
    ORDER BY l.application_date DESC
";
$loansResult = $db->query($loansQuery);
$loans = $loansResult->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .panel {
        margin-bottom: 20px;
        background-color: #fff;
        border: 1px solid transparent;
        border-radius: 4px;
        -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
        box-shadow: 0 1px 1px rgba(0,0,0,.05);
    }
    
    .panel-heading {
        padding: 10px 15px;
        border-bottom: 1px solid transparent;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
    }
    
    .panel-title {
        margin-top: 0;
        margin-bottom: 0;
        font-size: 16px;
        color: inherit;
    }
    
    .panel-body {
        padding: 15px;
    }
    
    .panel-white {
        border-color: #dddddd;
    }
    
    .panel-border-top-xlg {
        border-top-width: 3px;
    }
    
    .panel-border-top-teal-400 {
        border-top-color: #26a69a;
    }
    
    .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: normal;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        text-decoration: none;
    }
    
    .btn-default {
        color: #333;
        background-color: #fff;
        border-color: #ccc;
    }
    
    .btn-primary {
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
    }
    
    .btn-success {
        color: #fff;
        background-color: #5cb85c;
        border-color: #4cae4c;
    }
    
    .btn-teal-400 {
        color: #fff;
        background-color: #26a69a;
        border-color: #26a69a;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }
    
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
        border-spacing: 0;
    }
    
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }
    
    .table > thead > tr > th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
        border-right: 1px solid #ddd;
        font-weight: bold;
        background-color: #f5f5f5;
    }
    
    .table > tbody > tr > td {
        border-right: 1px solid #ddd;
    }
    
    .table-bordered {
        border: 1px solid #ddd;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-uppercase {
        text-transform: uppercase;
    }
    
    .input-group {
        position: relative;
        display: table;
        border-collapse: separate;
    }
    
    .input-group-addon {
        padding: 6px 12px;
        font-size: 14px;
        font-weight: normal;
        line-height: 1;
        color: #555;
        text-align: center;
        background-color: #eee;
        border: 1px solid #ccc;
        border-radius: 0 4px 4px 0;
        width: 1%;
        white-space: nowrap;
        vertical-align: middle;
        display: table-cell;
    }
    
    .form-control {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }
    
    .breadcrumb-elements {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
    }
    
    .breadcrumb-elements li {
        display: inline-block;
        margin-right: 10px;
    }
    
    .badge {
        display: inline-block;
        min-width: 10px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 10px;
    }
    
    .bg-warning {
        background-color: #f0ad4e;
    }
    
    .bg-info {
        background-color: #5bc0de;
    }
    
    .bg-success {
        background-color: #5cb85c;
    }
    
    .bg-primary {
        background-color: #337ab7;
    }
    
    .bg-danger {
        background-color: #d9534f;
    }
    
    .text-muted {
        color: #777;
    }
    
    .table-responsive {
        overflow-x: auto;
    }

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
                        <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - PDF Management</h4>
                    </div>
                </div>
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                        <li><a href="javascript:;"><i class="icon-file-pdf position-left"></i> PDF Management</a></li>
                        <li class="active"><i class="icon-dots position-left"></i>All Documents</li>
                    </ul>
                </div>
            </div>
            
            <div class="content">
                <!-- Search and Filter Panel -->
                <div class="panel panel-white border-top-xlg border-top-teal-400">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-search4 text-teal-400"></i> Search and Filter Loans</h6>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="icon-search4"></i>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search by name or loan type..." onkeyup="filterLoans()">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select id="statusFilter" class="form-control" onchange="filterLoans()">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="paid">Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="typeFilter" class="form-control" onchange="filterLoans()">
                                        <option value="">All Types</option>
                                        <?php
                                        $types = array_unique(array_column($loans, 'loan_type_name'));
                                        foreach ($types as $type) {
                                            echo "<option value='$type'>$type</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-default" onclick="clearFilters()">
                                        <i class="icon-filter4"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Overdue Loans Alert Panel -->
                <div class="panel panel-white border-top-xlg border-top-danger">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-warning22 text-danger"></i> Overdue Loans Alert</h6>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="overdueTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">Loan #</th>
                                        <th>Member Name</th>
                                        <th>Loan Type</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Days Overdue</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $overdueLoans = array_filter($loans, function($loan) {
                                        return strtolower($loan['status']) == 'overdue';
                                    });
                                    foreach ($overdueLoans as $loan): ?>
                                        <tr class="loan-item" 
                                            data-status="overdue"
                                            data-type="<?php echo strtolower($loan['loan_type_name']); ?>"
                                            data-search="<?php echo strtolower($loan['full_name'] . ' ' . $loan['loan_type_name']); ?>">
                                            
                                            <td class="text-center"><?php echo str_pad($loan['loan_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($loan['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['loan_type_name']); ?></td>
                                            <td class="text-right"><?php echo number_format($loan['requested_amount'], 2); ?></td>
                                            <td class="text-center">
                                                <?php 
                                                $daysOverdue = 0;
                                                if ($loan['status'] == 'overdue') {
                                                    $dueDate = new DateTime($loan['due_date'] ?? date('Y-m-d'));
                                                    $today = new DateTime();
                                                    $daysOverdue = $today->diff($dueDate)->days;
                                                }
                                                echo $daysOverdue > 0 ? $daysOverdue . ' days' : 'N/A';
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($loan['email'])): ?>
                                                <button class="btn btn-danger btn-sm overdue-btn" 
                                                        onclick="sendOverdueNotice(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars($loan['full_name']); ?>')"
                                                        title="Send URGENT Overdue Notice">
                                                    <i class="icon-warning22"></i>
                                                </button>
                                                <?php endif; ?>
                                                <a href="pdf/generate_loan_pdf.php?loan_id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-primary btn-sm" target="_blank" title="Download Application">
                                                    <i class="icon-file-pdf"></i>
                                                </a>
                                                <a href="pdf/generate_loan_statement.php?loan_id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-success btn-sm" target="_blank" title="Download Statement">
                                                    <i class="icon-file-text"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($overdueLoans)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="icon-checkmark-circle"></i> No overdue loans found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Email Actions Panel -->
                <div class="panel panel-white border-top-xlg border-top-warning">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-envelope text-warning"></i> Email Actions</h6>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-warning" onclick="sendBulkEmails()">
                                    <i class="icon-envelope"></i> Send SOA to All Overdue & Ongoing Loans
                                </button>
                                <span class="text-muted ml-3">
                                    <small><i class="icon-info22"></i> This will send Statement of Account emails to all members with overdue or ongoing loans.</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loans Table Panel -->
                <div class="panel panel-white border-top-xlg border-top-teal-400">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-list text-teal-400"></i> Loan Documents</h6>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="loansTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">Loan #</th>
                                        <th>Member Name</th>
                                        <th>Loan Type</th>
                                        <th>Application Date</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $activeLoans = array_filter($loans, function($loan) {
                                        return strtolower($loan['status']) != 'overdue';
                                    });
                                    foreach ($activeLoans as $loan): ?>
                                        <tr class="loan-item" 
                                            data-status="<?php echo strtolower($loan['status']); ?>"
                                            data-type="<?php echo strtolower($loan['loan_type_name']); ?>"
                                            data-search="<?php echo strtolower($loan['full_name'] . ' ' . $loan['loan_type_name']); ?>">
                                            
                                            <td class="text-center"><?php echo str_pad($loan['loan_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($loan['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['loan_type_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($loan['application_date'])); ?></td>
                                            <td class="text-right"><?php echo number_format($loan['requested_amount'], 2); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?php echo getStatusColor($loan['status']); ?>">
                                                    <?php echo strtoupper($loan['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="pdf/generate_loan_pdf.php?loan_id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-primary btn-sm" target="_blank" title="Download Application">
                                                    <i class="icon-file-pdf"></i>
                                                </a>
                                                <a href="pdf/generate_loan_statement.php?loan_id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-success btn-sm" target="_blank" title="Download Statement">
                                                    <i class="icon-file-text"></i>
                                                </a>
                                                <?php if ((strtolower($loan['status']) == 'overdue' || strtolower($loan['status']) == 'ongoing') && !empty($loan['email'])): ?>
                                                <button class="btn btn-warning btn-sm email-btn" 
                                                        onclick="sendSOAEmail(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars($loan['full_name']); ?>')"
                                                        title="Send SOA via Email">
                                                    <i class="icon-envelope"></i>
                                                </button>
                                                <?php endif; ?>
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
<?php require('includes/footer-text.php'); ?>
<?php require('includes/footer.php'); ?>

<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable for main loans table
    var table = $('#loansTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "order": [[0, "desc"]],
        "columnDefs": [
            { "targets": [0], "orderable": true, "className": "text-center" },
            { "targets": [1], "orderable": true },
            { "targets": [2], "orderable": true },
            { "targets": [3], "orderable": true },
            { "targets": [4], "orderable": true, "className": "text-right" },
            { "targets": [5], "orderable": false, "className": "text-center" }
        ],
        "language": {
            "search": "_INPUT_",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "zeroRecords": "No matching records found"
        },
        "initComplete": function() {
            // Apply custom styling to DataTable elements
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search loans...');
            $('.dataTables_length select').addClass('form-control');
            $('.dataTables_filter').css('float', 'left');
            $('.dataTables_length').css('float', 'right');
            $('.dataTables_filter').css('margin-bottom', '10px');
        }
    });

    // Initialize DataTable for overdue loans table
    var overdueTable = $('#overdueTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "order": [[3, "desc"]], // Order by amount (largest overdue first)
        "columnDefs": [
            { "targets": [0], "orderable": true, "className": "text-center" },
            { "targets": [1], "orderable": true },
            { "targets": [2], "orderable": true },
            { "targets": [3], "orderable": true, "className": "text-right" },
            { "targets": [4], "orderable": false, "className": "text-center" }
        ],
        "language": {
            "search": "_INPUT_",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "zeroRecords": "No overdue loans found"
        },
        "initComplete": function() {
            // Apply custom styling to DataTable elements
            $('#overdueTable_wrapper .dataTables_filter input').addClass('form-control').attr('placeholder', 'Search overdue loans...');
            $('#overdueTable_wrapper .dataTables_length select').addClass('form-control');
        }
    });

    // Custom search function for our filters
    function filterLoans() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const statusFilter = $('#statusFilter').val().toLowerCase();
        const typeFilter = $('#typeFilter').val().toLowerCase();
        
        // Build custom search string
        let searchTerms = [];
        if (searchTerm) searchTerms.push(searchTerm);
        if (statusFilter) searchTerms.push(statusFilter);
        if (typeFilter) searchTerms.push(typeFilter);
        
        // Apply search to DataTable
        table.search(searchTerms.join(' ')).draw();
    }

    // Clear filters function
    window.clearFilters = function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        table.search('').draw();
    }

    // Bind filter events
    $('#searchInput').on('keyup', filterLoans);
    $('#statusFilter').on('change', filterLoans);
    $('#typeFilter').on('change', filterLoans);
});

// Send SOA email to individual loan
function sendSOAEmail(loanId, memberName) {
    if (!confirm('Run DEBUG test for ' + memberName + ' (Loan #' + loanId + ')?')) {
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="icon-spinner2 spinner"></i> Testing...';
    btn.disabled = true;
    
    $.ajax({
        url: 'pdf/send_overdue_soa_email.php',
        type: 'GET',
        data: { loan_id: loanId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('DEBUG SUCCESS! ' + response.message);
            } else {
                alert('DEBUG FAILED: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('DEBUG Error: ' + error + ' (Status: ' + xhr.status + ')');
            console.log('XHR Response:', xhr.responseText);
        },
        complete: function() {
            // Restore button
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

// Send URGENT overdue notice email
function sendOverdueNotice(loanId, memberName) {
    if (!confirm('Send URGENT overdue notice to ' + memberName + ' (Loan #' + loanId + ')?\n\nThis will send an immediate payment demand with the SOA attached.')) {
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="icon-spinner2 spinner"></i> Sending...';
    btn.disabled = true;
    
    $.ajax({
        url: 'pdf/send_overdue_soa_email.php',
        type: 'GET',
        data: { loan_id: loanId, urgent_overdue: 'true' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(' URGENT notice sent successfully to ' + memberName + '!\n\n' + response.message);
            } else {
                alert(' Failed to send urgent notice: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert(' Error sending urgent notice: ' + error + ' (Status: ' + xhr.status + ')');
            console.log('XHR Response:', xhr.responseText);
        },
        complete: function() {
            // Restore button
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

// Send bulk emails to all overdue and ongoing loans
function sendBulkEmails() {
    if (!confirm('Send SOA emails with PDFs to ALL members with overdue or ongoing loans?')) {
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="icon-spinner2 spinner"></i> Sending...';
    btn.disabled = true;
    
    $.ajax({
        url: 'pdf/send_overdue_soa_email.php',
        type: 'GET',
        data: { send_all_overdue: 'true' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let message = response.message + '\n\nEmail Results:\n';
                response.details.forEach(function(detail) {
                    const status = detail.result.success ? '✅' : '❌';
                    message += status + ' Loan #' + detail.loan_id + ' - ' + detail.member + '\n';
                    message += '   Email: ' + detail.email + '\n';
                    message += '   Status: ' + (detail.status ? detail.status.toUpperCase() : 'N/A') + '\n';
                    message += '   ' + detail.result.message + '\n';
                });
                alert(message);
            } else {
                alert('FAILED: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error: ' + error + ' (Status: ' + xhr.status + ')');
            console.log('XHR Response:', xhr.responseText);
        },
        complete: function() {
            // Restore button
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}
</script>

<?php
function getStatusColor($status) {
    switch(strtolower($status)) {
        case 'pending': return 'warning';
        case 'approved': return 'info';
        case 'ongoing': return 'success';
        case 'completed': return 'primary';
        case 'overdue': return 'danger';
        case 'paid': return 'success';
        case 'rejected': return 'danger';
        default: return 'default';
    }
}
?>
</script>
