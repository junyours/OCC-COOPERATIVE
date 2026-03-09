<?php
require('includes/header.php');


if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}

// Fetch Loans
$pending_loans = $db->query("
    SELECT l.loan_id, l.requested_amount, l.status, l.application_date,
           a.account_id,  m.member_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name,
           lt.loan_type_name, lt.term_value, lt.term_unit, lt.interest_rate
    FROM loans l
    JOIN accounts a ON l.account_id = a.account_id
    JOIN tbl_members m ON a.member_id = m.member_id
    JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
    WHERE l.status='pending'
    ORDER BY l.loan_id DESC
");

$approved_loans = $db->query("
    SELECT l.loan_id, l.requested_amount, l.status, l.application_date,
           a.account_id, m.member_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name,
           lt.loan_type_name, lt.term_value, lt.term_unit, lt.interest_rate
    FROM loans l
    JOIN accounts a ON l.account_id = a.account_id
    JOIN tbl_members m ON a.member_id = m.member_id
    JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
    WHERE l.status='approved'
    ORDER BY l.loan_id DESC 
");

$declined_loans = $db->query("
    SELECT l.loan_id, l.requested_amount, l.status, l.application_date,
           a.account_id, m.member_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name,
           lt.loan_type_name, lt.term_value, lt.term_unit, lt.interest_rate
    FROM loans l
    JOIN accounts a ON l.account_id = a.account_id
    JOIN tbl_members m ON a.member_id = m.member_id
    JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
    WHERE l.status='rejected'
    ORDER BY l.loan_id DESC
");

// Loan funds
$funds = $db->query("SELECT * FROM tbl_loan_fund ORDER BY fund_id DESC");
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

                <!-- Page header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i>
                                <span class="text-semibold">Dashboard</span> - Loans
                            </h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li class="active"><i class="icon-cash3 position-left"></i> Loans</li>
                        </ul>
                        <ul class="breadcrumb-elements">
                            <li><a href="javascript:;" data-toggle="modal" data-target="#modal-funds">
                                    <i class="icon-coins position-left text-teal-400"></i> Set Loan Funds</a></li>
                        </ul>
                    </div>
                </div>

                <div class="content">

                    <!-- Pending Loans -->
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> Pending Loans</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table table-hover table-bordered datatable-button-html5-basic">
                                <thead>
                                    <tr>
                                        <th hidden>ID</th>
                                        <th>Member</th>
                                        <th>Amount</th>
                                        <th>Term</th>
                                        <th>Interest Rate</th>
                                        <th>Date Applied</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pending_loans->fetch_assoc()) { ?>
                                        <tr>
                                            <td hidden><?= $row['loan_id'] ?></td>
                                            <td><?= htmlspecialchars($row['member_name']) ?></td>
                                            <td align="right"><?= number_format($row['requested_amount'], 2) ?></td>
                                            <td align="center"><?= $row['term_value'] ?> <?= $row['term_unit'] ?></td>
                                            <td align="center"><?= number_format($row['interest_rate'], 2) ?></td>
                                            <td><?= $row['application_date'] ?></td>
                                            <td align="center">
                                                <button class="btn btn-success btn-approve"
                                                    data-id="<?= $row['loan_id'] ?>"
                                                    data-amount="<?= $row['requested_amount'] ?>"
                                                    data-term="<?= $row['term_value'] ?>"
                                                    data-unit="<?= $row['term_unit'] ?>"
                                                    data-interest="<?= $row['interest_rate'] ?>">Approve</button>
                                                <button class="btn btn-danger btn-decline" data-id="<?= $row['loan_id'] ?>">Decline</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Approved Loans -->
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-check text-teal-400 position-left"></i> Approved Loans</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table table-hover table-bordered datatable-button-html5-basic">
                                <thead>
                                    <tr>
                                        <th hidden>ID</th>
                                        <th>Member</th>
                                        <th>Approved Amount</th>
                                        <th>Term</th>
                                        <th>Interest Rate</th>
                                        <th>Date Approved</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $approved_loans->fetch_assoc()) { ?>
                                        <tr>
                                            <td hidden><?= $row['loan_id'] ?></td>
                                            <td><?= htmlspecialchars($row['member_name']) ?></td>
                                            <td><?= number_format($row['requested_amount'], 2) ?></td>
                                            <td><?= $row['term_value'] ?> <?= $row['term_unit'] ?></td>
                                            <td><?= $row['interest_rate'] ?>%</td>
                                            <td><?= $row['application_date'] ?></td>
                                            <td align="center">
                                                <button class="btn btn-info view-receipt" data-id="<?= $row['loan_id'] ?>">
                                                    <i class="icon-file-eye"></i> Print
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Declined Loans -->
                    <!-- <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-cross text-teal-400 position-left"></i> Declined Loans</h6>
                        </div>
                        <div class="panel-body panel-theme">
                            <table class="table table-hover table-bordered datatable-button-html5-basic">
                                <thead>
                                    <tr>
                                        <th hidden>ID</th>
                                        <th>Member</th>
                                        <th>Amount</th>
                                        <th>Date Applied</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $declined_loans->fetch_assoc()) { ?>
                                        <tr>
                                            <td hidden><?= $row['loan_id'] ?></td>
                                            <td><?= htmlspecialchars($row['member_name']) ?></td>
                                            <td><?= number_format($row['requested_amount'], 2) ?></td>
                                            <td><?= $row['application_date'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div> -->

                </div>
                <?php require('includes/footer-text.php'); ?>
            </div>
        </div>
    </div>
</body>

<!-- Fund Modal -->
<div id="modal-funds" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-funds">
                <input type="hidden" name="save-loan-fund" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Loan Funds</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-bodys">
                    <div id="display-fund-msg"></div>
                    <div class="form-group">
                        <label>Fund Name</label>
                        <input type="text" name="fund_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Starting Balance</label>
                        <input type="number" step="0.01" name="starting_balance" class="form-control" required>
                    </div>
                    <hr>
                    <h6>Existing Funds</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fund Name</th>
                                <th>Starting Balance</th>
                                <th>Current Balance</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $funds_list = $db->query("SELECT * FROM tbl_loan_fund ORDER BY fund_id DESC");
                            while ($f = $funds_list->fetch_assoc()) {
                                echo "<tr>
                    <td>{$f['fund_id']}</td>
                    <td class='fund-name'>" . htmlspecialchars($f['fund_name']) . "</td>
                    <td class='fund-starting' style='text-align:right'>" . number_format($f['starting_balance'], 2) . "</td>
                    <td class='fund-current' style='text-align:right'>" . number_format($f['current_balance'], 2) . "</td>
                    <td>{$f['created_at']}</td>
                    <td align='center'>
                        <button class='btn btn-sm btn-warning btn-edit-fund' data-id='{$f['fund_id']}'>
                            <i class='icon-pencil'></i> Update
                        </button>
                    </td>
                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn-save-fund" class="btn bg-teal-400">Save Fund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="modal-approve" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-approve">
                <input type="hidden" name="approve_loan" value="1">
                <input type="hidden" name="loan_app_id" id="loan-app-id">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Loan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-bodys ">
                    <div class="form-group">
                        <label>Choose Fund</label>
                        <select name="fund_id" id="fund-select" class="form-control" required>
                            <option value="">-- Select Fund --</option>
                            <?php
                            $funds = $db->query("SELECT * FROM tbl_loan_fund ORDER BY fund_id DESC");
                            while ($f = $funds->fetch_assoc()) {
                                echo "<option value='{$f['fund_id']}' data-balance='{$f['current_balance']}'>
                        {$f['fund_name']} - Balance: " . number_format($f['current_balance'], 2) . "
                    </option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Approved Amount</label>
                        <input type="number" step="0.01" name="approved_amount" id="approved-amount" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Term</label>
                        <input type="text" name="approved_term" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" id="interest-rate" class="form-control" readonly>
                    </div>
                    <div id="approval-error" class="text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm Approval</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div id="modal-decline" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:8px;">
            <div class="modal-header bg-danger-400 text-white py-2">
                <h6 class="modal-title mb-0">
                    <i class="icon-cross2"></i> Confirm Decline
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <input type="hidden" id="decline-loan-id">
            <p class="mb-0" style="font-size:14px;">Are you sure you want to decline this loan?</p>
            <div class="modal-footer justify-content-center py-2">
                <button id="confirm-decline" class="btn btn-danger btn-sm btn-labeled">
                    <b><i class="icon-cross"></i></b> Decline
                </button>
                <button class="btn btn-default btn-sm btn-labeled" data-dismiss="modal">
                    <b><i class="icon-cancel-circle2"></i></b> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loan Receipt Modal -->
<div id="loanReceiptModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Loan Approval</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="loan-receipt-body"></div>
            <div class="modal-footer">
                <button type="button" onclick="printReceipt()" class="btn btn-primary">
                    <i class="icon-printer"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require('includes/footer.php'); ?>

<script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script src="../js/validator.min.js"></script>
<script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script>
    $(function() {
        $('.datatable-button-html5-basic').each(function() {
            $(this).DataTable();
        });

        // Approve Loan
        $(document).on('click', '.btn-approve', function() {
            let loanId = $(this).data('id');
            let amount = $(this).data('amount');
            let term = $(this).data('term');
            let unit = $(this).data('unit');
            let interest = $(this).data('interest'); // fetch interest

            $('#loan-app-id').val(loanId);
            $('#approved-amount').val(amount).prop('readonly', true);
            $('input[name="approved_term"]').val(term + (unit ? ` ${unit}` : '')).prop('readonly', true);
            $('#interest-rate').val(interest); // set interest rate
            $('#approval-error').text('');
            $('#modal-approve').modal('show');
        });
        // Decline Loan
        $(document).on('click', '.btn-decline', function() {
            let loanId = $(this).data('id');
            $('#decline-loan-id').val(loanId);
            $('#modal-decline').modal('show');
        });

        // Confirm Decline
        $('#confirm-decline').click(function() {
            let id = $('#decline-loan-id').val();
            $.post('../transaction.php', {
                decline_loan: 1,
                loan_app_id: id
            }, function(resp) {
                resp = resp.trim();
                if (resp === '1') {
                    $.jGrowl('Loan declined successfully.', {
                        header: 'Success',
                        theme: 'bg-success'
                    });
                    $('#modal-decline').modal('hide');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    $.jGrowl('Error: ' + resp, {
                        header: 'Error',
                        theme: 'bg-danger'
                    });
                }
            });
        });

        // Approve submit
        $('#form-approve').submit(function(e) {
            e.preventDefault();
            let approved = parseFloat($('#approved-amount').val());
            let fundBalance = parseFloat($('#fund-select option:selected').data('balance'));
            if (approved > fundBalance) {
                $('#approval-error').text('Error: Fund balance is insufficient!');
                return false;
            }
            $.post('../transaction.php', $(this).serialize(), function(resp) {
                resp = resp.trim();
                if (resp === '1') {
                    $.jGrowl('Loan approved successfully.', {
                        header: 'Success',
                        theme: 'bg-success'
                    });
                    $('#modal-approve').modal('hide');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    $('#approval-error').text(resp);
                }
            });
        });

        // View Receipt
        $(document).on('click', '.view-receipt', function() {
            let loanId = $(this).data('id');
            $.get('../transaction.php', {
                loan_receipt: 1,
                loan_id: loanId
            }, function(resp) {

                $('#loan-receipt-body').html(resp);

                $('#loanReceiptModal').modal('show');

            });
        });

        // Edit Fund
        $(document).on('click', '.btn-edit-fund', function(e) {
            e.preventDefault();
            let row = $(this).closest('tr');
            let fundId = $(this).data('id');
            let fundName = row.find('.fund-name').text().trim();
            let startingBalance = row.find('.fund-starting').text().replace(/,/g, '').trim();

            $('#form-funds input[name="fund_name"]').val(fundName);
            $('#form-funds input[name="starting_balance"]').val(startingBalance);
            $('#btn-save-fund').text('Update Fund').data('update-id', fundId);
            $('#modal-funds').modal('show');
        });


        // Save Fund
        $('#btn-save-fund').click(function() {
            let form = $('#form-funds');
            let updateId = $(this).data('update-id') || '';
            let postData = form.serialize();
            if (updateId) postData += '&update_id=' + updateId;

            $.post('../transaction.php', postData, function(resp) {
                resp = resp.trim();
                if (resp === '1') {
                    $.jGrowl(updateId ? 'Loan fund updated.' : 'Loan fund saved.', {
                        header: 'Success',
                        theme: 'bg-success'
                    });
                    $('#modal-funds').modal('hide');
                    $('#btn-save-fund').text('Save Fund').removeData('update-id');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    $('#display-fund-msg').html('<div class="alert alert-danger">' + resp + '</div>');
                }
            });
        });

    });




    function printReceipt() {
        let content = document.getElementById('loan-receipt-body').innerHTML;
        let w = window.open('', '', 'height=600,width=800');
        w.document.write('<html><head><title>Loan Receipt</title></head><body>');
        w.document.write(content);
        w.document.write('</body></html>');
        w.document.close();
        w.print();
    }
</script>