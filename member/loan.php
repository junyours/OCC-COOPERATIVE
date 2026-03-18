<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../admin/includes/header.php');

if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id']) || $_SESSION['is_login_yes'] != 'yes') {
    die("Unauthorized access.");
}

$user_id = (int) $_SESSION['user_id'];

/* =========================
   GET MEMBER
========================= */
$member = $db->query("SELECT member_id FROM tbl_members WHERE user_id='$user_id'")->fetch_assoc();
$member_id = $member['member_id'] ?? 0;

/* =========================
   CHECK EXISTING LOAN
========================= */
$existingLoan = $db->query("
SELECT COUNT(*) AS total
FROM loans
WHERE status IN ('pending','approved','ongoing')
AND account_id IN (
    SELECT account_id 
    FROM accounts 
    WHERE member_id='$member_id'
)
")->fetch_assoc();

$has_existing_loan = $existingLoan['total'] > 0;

/* =========================
   CHECK CAPITAL BALANCE
========================= */
$capital = $db->query("
SELECT COALESCE(SUM(
CASE
WHEN tt.type_name IN ('deposit','capital_share') THEN t.amount
WHEN tt.type_name='withdrawal' THEN -t.amount
ELSE 0 END),0) AS capital_balance
FROM accounts a
JOIN account_types at ON a.account_type_id = at.account_type_id
LEFT JOIN transactions t ON t.account_id = a.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
WHERE a.member_id='$member_id'
AND at.type_name='capital_share'
")->fetch_assoc();

$capital_balance = $capital['capital_balance'] ?? 0;

/* =========================
   CHECK SAVINGS BALANCE
========================= */
$savings = $db->query("
SELECT COALESCE(SUM(
CASE
WHEN tt.type_name='deposit' THEN t.amount
WHEN tt.type_name='withdrawal' THEN -t.amount
ELSE 0 END),0) AS savings_balance
FROM accounts a
JOIN account_types at ON a.account_type_id = at.account_type_id
LEFT JOIN transactions t ON t.account_id = a.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id
WHERE a.member_id='$member_id'
AND at.type_name='savings'
")->fetch_assoc();

$savings_balance = $savings['savings_balance'] ?? 0;

/* =========================
   GET SYSTEM SETTINGS
========================= */
$settings = [];
$res = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('min_capital_required','min_savings_required')");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$min_capital = $settings['min_capital_required'] ?? 0;
$min_savings = $settings['min_savings_required'] ?? 0;

$eligible = ($capital_balance >= $min_capital) && ($savings_balance >= $min_savings);

/* =========================
   LOAN HISTORY
========================= */
$loan_history = [];
$stmt = $db->prepare("
SELECT l.loan_id, lt.loan_type_name, l.requested_amount, l.status, l.application_date, l.approved_date
FROM loans l
LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
WHERE l.account_id IN (SELECT account_id FROM accounts WHERE member_id=?)
ORDER BY l.application_date DESC
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $loan_history[] = $row;
$stmt->close();
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

    <!-- NAVBAR -->
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

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">

                <!-- PAGE HEADER -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-cash3 position-left"></i> Loans</h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li class="active">Loans</li>
                        </ul>

                        <ul class="breadcrumb-elements">
                            <li>
                                <?php if ($eligible && !$has_existing_loan): ?>
                                    <a href="#" data-toggle="modal" data-target="#addLoanModal">
                                        <i class="icon-coins text-blue-400 position-left"></i>
                                        Apply Loan
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" style="opacity:.5;cursor:not-allowed;"
                                        title="You are not eligible for a loan">
                                        <i class="icon-coins text-grey-400 position-left"></i>
                                        Apply Loan
                                    </a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="content">

                    <!-- ELIGIBILITY PANEL -->
                    <div class="panel panel-white">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="panel panel-body bg-success-400">
                                        <h3>₱ <?= number_format($capital_balance, 2) ?></h3>
                                        <span>Your Capital Share</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="panel panel-body bg-danger-400">
                                        <h3>₱ <?= number_format($min_capital, 2) ?></h3>
                                        <span>Required Capital</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="panel panel-body <?= $eligible ? 'bg-teal-400' : 'bg-warning-400' ?>">
                                        <h3><?= $eligible ? 'Eligible' : 'Not Eligible' ?></h3>
                                        <span>Loan Status</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LOAN HISTORY -->
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title">Loan Applications</h6>
                        </div>

                        <div class="panel-body panel-theme table-responsive">
                            <table class="table table-hover table-bordered" id="loan-history-table">
                                <thead>
                                    <tr style="border-bottom: 4px solid #ddd; background: #eee">
                                        <th>Date Applied</th>
                                        <th>Loan Type</th>
                                        <th>Amount (₱)</th>
                                        <th>Status</th>
                                        <th>Date Approved</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($loan_history)): ?>
                                        <?php foreach ($loan_history as $row): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($row['application_date'])) ?></td>
                                                <td><?= htmlspecialchars($row['loan_type_name']) ?></td>
                                                <td class="text-right">₱ <?= number_format($row['requested_amount'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    switch ($row['status']) {
                                                        case 'pending':
                                                            echo '<span class="label label-warning">Pending</span>';
                                                            break;
                                                        case 'approved':
                                                            echo '<span class="label label-success">Approved</span>';
                                                            break;
                                                        case 'declined':
                                                            echo '<span class="label label-danger">Declined</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="label label-default">' . htmlspecialchars($row['status']) . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= $row['approved_date'] ? date('M d, Y', strtotime($row['approved_date'])) : '-' ?></td>
                                                <td class="text-center">
                                                    <a href="../admin/pdf/generate_loan_pdf.php?loan_id=<?= $row['loan_id'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Download Loan Application PDF"
                                                       style="margin-right: 5px;">
                                                        <i class="icon-file-pdf"></i> App
                                                    </a>
                                                    <a href="../admin/pdf/generate_loan_statement.php?loan_id=<?= $row['loan_id'] ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Download Statement of Account">
                                                        <i class="icon-file-text"></i> Stmt
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No loan applications yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> <!-- content -->
            </div>
        </div>
    </div>

    <!-- LOAN MODAL -->
    <div id="addLoanModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-teal-400">
                    <h5 class="modal-title">Loan Application</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-bodys">

                    <form id="loanApplicationForm">
                        <input type="hidden" name="member_id" value="<?= (int)$member_id ?>">

                        <!-- Loan Type -->
                        <div class="form-group">
                            <label>Loan Type</label>
                            <select class="form-control" name="loan_type_id" id="loan_type_id" required>
                                <option value="">Select Loan Type</option>

                                <?php
                                $loanTypes = $db->query("SELECT loan_type_id, loan_type_name, require_comaker FROM loan_types WHERE status='active'");

                                if ($loanTypes && $loanTypes->num_rows) {
                                    while ($lt = $loanTypes->fetch_assoc()) {

                                        $loan_name = htmlspecialchars($lt['loan_type_name'], ENT_QUOTES);
                                        $loan_id_val = (int)$lt['loan_type_id'];
                                        $require_comaker = (int)$lt['require_comaker'];

                                        echo "<option value='{$loan_id_val}' data-comaker='{$require_comaker}'>{$loan_name}</option>";
                                    }
                                } else {
                                    echo "<option value=''>No loan types available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- CO-MAKER -->
                        <div class="form-group" id="comaker-field" style="display:none;">
                            <label>Select Co-Maker</label>

                            <select class="form-control" name="comaker_member_id" id="comaker_member_id" style="width:100%;">
                                <option value="">Search Member</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <label>Requested Amount</label>
                            <input type="number" step="0.01" class="form-control" name="requested_amount" required>
                        </div>

                        <!-- Remarks -->
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="remarks"></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>


    <?php require('../admin/includes/footer-text.php'); ?>
    <?php require('../admin/includes/footer.php'); ?>

    <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/validator.min.js"></script>
    <script src="../js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            console.log("Loan modal script loaded");

         
            $('#loan-history-table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']], // Sort by Date Applied descending
                columnDefs: [
                    { targets: 0, width: '120px' }, 
                    { targets: 1, width: '150px' }, 
                    { targets: 2, width: '120px', className: 'text-right' }, // Amount
                    { targets: 3, width: '100px', orderable: false }, 
                    { targets: 4, width: '120px' },
                    { targets: 5, width: '120px', orderable: false, className: 'text-center' }  // Actions
                ],
                language: {
                    search: "Search loans:",
                    lengthMenu: "Show _MENU_ ",
                    info: "Showing _START_ to _END_ of _TOTAL_ loan applications",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No loan applications found",
                    zeroRecords: "No matching loan applications found"
                }
            });

            /* -------------------------------
               SELECT2 INITIALIZATION
            --------------------------------*/
            $('#comaker_member_id').select2({
                dropdownParent: $('#addLoanModal'),
                width: '100%',
                placeholder: "Search Co-Maker",
                allowClear: true
            });


            /* -------------------------------
               SHOW / HIDE COMAKER
            --------------------------------*/
            $('#loan_type_id').on('change', function() {

                var requireComaker = $(this).find(':selected').data('comaker');

                if (requireComaker == 1) {

                    $('#comaker-field').slideDown();
                    $('#comaker_member_id').prop('required', true);

                } else {

                    $('#comaker-field').slideUp();
                    $('#comaker_member_id').prop('required', false).val(null).trigger('change');

                }

            });

            $('#addLoanModal').on('shown.bs.modal', function() {

                $('#comaker_member_id').select2({
                    dropdownParent: $('#addLoanModal'),
                    width: '100%',
                    placeholder: "Search Co-Maker",
                    minimumInputLength: 1,

                    ajax: {
                        url: '../transaction.php',
                        type: 'POST',
                        dataType: 'json',
                        delay: 250,

                        data: function(params) {
                            return {
                                search_member: 1,
                                search: params.term,
                                member_id: <?= (int)$member_id ?>
                            };
                        },

                        processResults: function(data) {
                            return {
                                results: data
                            };
                        }
                    }
                });

            });


            /* -------------------------------
               FORM SUBMIT WITH VALIDATOR
            --------------------------------*/
            $('#loanApplicationForm').validator().on('submit', function(e) {

                if (e.isDefaultPrevented()) {

                    $.jGrowl("Please fill all required fields correctly.", {
                        header: 'Validation Error',
                        theme: "alert-styled-right bg-danger"
                    });

                } else {

                    e.preventDefault();

                    var $form = $(this);
                    var $btn = $form.find('button[type="submit"]');

                    $btn.prop('disabled', true).html('Processing...');


                    $.post('../transaction.php',
                        $form.serialize() + "&save-loan-application=1",
                        function(response) {

                            console.log("Server response:", response);

                            if (response.trim() === "1") {

                                $.jGrowl("Loan application submitted successfully!", {
                                    header: 'Success Notification',
                                    theme: "alert-styled-right bg-success",
                                    life: 3000
                                });

                                $form[0].reset();
                                $('#comaker-field').hide();
                                $('#comaker_member_id').val(null).trigger('change');

                                $('#addLoanModal').modal('hide');

                                setTimeout(function() {
                                    location.reload();
                                }, 1000);

                            } else {

                                $.jGrowl("Something went wrong: " + response, {
                                    header: 'Error Notification',
                                    theme: "alert-styled-right bg-danger"
                                });

                            }

                            $btn.prop('disabled', false).html('Submit Application');

                        });

                }

            });

        });
    </script>