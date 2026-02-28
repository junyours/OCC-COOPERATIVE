<?php
require('includes/header.php');

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
        gap: 0px;
        font-weight: 800;
        color: white;
        text-decoration: none;
        font-size: 50px;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        object-fit: contain;
    }

    .navbar-brand span {
        white-space: nowrap;
    }

    /* Transaction Specific Styles */
    .type-card {
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        padding: 15px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .type-card:hover {
        border-color: #26A69A;
        background: #f9f9f9;
    }

    .type-card.active {
        border-color: #26A69A;
        background: #f1f8f7;
        box-shadow: 0 0 5px rgba(38, 166, 154, 0.2);
    }

    .type-card input {
        display: none;
    }
</style>

<body class="layout-boxed navbar-top">

    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img style="height: 45px!important" src="../images/main_logo.jpg" alt="">
                <span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span>
            </a>
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
                        <h4>
                            <i class="icon-transmission position-left"></i>
                            <span class="text-semibold">Accounting</span> - New Transaction
                        </h4>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li class="active">Post Transaction</li>
                        </ul>
                    </div>
                </div>

                <div class="content">
                    <div class="row">

                        <div class="col-lg-8">
                            <div class="panel panel-white border-top-xlg border-top-primary">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold"><i class="icon-plus-circle2 text-primary position-left"></i>Transaction Details</h6>
                                </div>

                                <div class="panel-body">
                                    <form id="transactionForm" action="../transaction.php" method="POST">
                                        <input type="hidden" name="post_new_transaction" value="1">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-semibold">Member:</label>
                                                    <select name="member_id" id="member_id" class="form-control" required>
                                                        <option value="">-- Select Member --</option>
                                                        <?php
                                                        $members = $db->query("SELECT member_id, first_name, last_name FROM tbl_members ORDER BY last_name ASC");
                                                        while ($m = $members->fetch_assoc()): ?>
                                                            <option value="<?= $m['member_id'] ?>"><?= strtoupper($m['last_name'] . ', ' . $m['first_name']) ?></option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-semibold">Target Account:</label>
                                                    <select name="account_id" id="account_id" class="form-control" required disabled>
                                                        <option value="">Select Member First...</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="text-semibold">Transaction Category:</label>
                                            <div class="row">
                                                <?php
                                                $types = $db->query("SELECT * FROM transaction_types ORDER BY transaction_type_id ASC");
                                                while ($t = $types->fetch_assoc()): ?>
                                                    <div class="col-xs-6 col-md-3">
                                                        <label class="type-card text-center display-block">
                                                            <input type="radio" name="transaction_type_id" value="<?= $t['transaction_type_id'] ?>" required>
                                                            <i class="icon-cash3 icon-2x text-slate-400"></i>
                                                            <div class="text-size-mini text-uppercase text-semibold mt-5"><?= $t['type_name'] ?></div>
                                                        </label>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>

                                        <div id="loan_selection_wrapper" style="display:none;" class="panel panel-body bg-slate-50 border-left-lg border-left-warning">
                                            <div class="form-group">
                                                <label class="text-semibold text-warning-600">Active Loan Amortization:</label>
                                                <select name="schedule_id" id="schedule_id" class="form-control">
                                                    <option value="">-- Select Amortization --</option>
                                                </select>
                                                <input type="hidden" name="loan_id" id="hidden_loan_id">
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-semibold text-primary">Amount:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">₱</span>
                                                        <input type="number" step="0.01" name="amount" class="form-control text-semibold" style="font-size: 1.3em;" placeholder="0.00" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-semibold">Reference/OR No:</label>
                                                    <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-semibold">Transaction Date:</label>
                                                    <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="text-semibold">Remarks:</label>
                                            <textarea name="remarks" rows="2" class="form-control" placeholder="Note down any details..."></textarea>
                                        </div>

                                        <div class="text-right pt-10">
                                            <button type="reset" class="btn btn-link text-slate">Clear All</button>
                                            <button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkmark4"></i></b> Post Transaction</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="panel panel-white border-top-xlg border-top-success">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">Member Overview</h6>
                                </div>
                                <div id="member_overview" class="panel-body">
                                    <div class="text-center py-20 text-muted">
                                        <i class="icon-user-check icon-3x"></i>
                                        <p class="mt-10">Select a member to view live account balances and loan status.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-flat">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">Quick Help</h6>
                                </div>
                                <div class="panel-body">
                                    <ul class="list-unstyled list-spaced">
                                        <li><i class="icon-info22 text-primary position-left"></i> Ensure the <b>Reference No.</b> matches the physical receipt.</li>
                                        <li><i class="icon-info22 text-primary position-left"></i> Transactions are instantly posted to the General Ledger.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('includes/footer-text.php'); ?>
    <?php require('includes/footer.php'); ?>

    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Handle Transaction Type Selection
            $('.type-card').click(function() {
                $('.type-card').removeClass('active').find('i').removeClass('text-teal-400').addClass('text-slate-400');
                $(this).addClass('active').find('i').removeClass('text-slate-400').addClass('text-teal-400');

                let type_id = $(this).find('input').val();
                let member_id = $('#member_id').val();

                // If Loan Payment (Assuming ID 5 is Loan Payment in your DB)
                if (type_id == "5") {
                    if (!member_id) {
                        $.jGrowl('Please select a member first.', {
                            theme: 'bg-danger'
                        });
                        return;
                    }
                    $('#loan_selection_wrapper').slideDown();
                    loadLoanSchedules(member_id);
                    // Loan payments usually don't need the 'Target Account' dropdown, 
                    // but we keep it optional or auto-select based on business logic.
                } else {
                    $('#loan_selection_wrapper').slideUp();
                }
            });

            // 2. Load Accounts when Member changes
            $('#member_id').change(function() {
                let member_id = $(this).val();
                if (member_id) {
                    // Clear and Load Accounts
                    $('#account_id').prop('disabled', false).html('<option>Loading accounts...</option>');
                    $.get('ajax_fetch_accounts.php', {
                        member_id: member_id
                    }, function(data) {
                        $('#account_id').html(data);
                    });

                    // Update Summary Panel
                    $('#member_overview').load('ajax_member_summary.php?member_id=' + member_id);

                    // Refresh loan schedules if Loan Payment is currently selected
                    let current_type = $('input[name="transaction_type_id"]:checked').val();
                    if (current_type == "5") loadLoanSchedules(member_id);

                } else {
                    $('#account_id').prop('disabled', true).html('<option value="">Select Member First...</option>');
                }
            });

            // 3. Load Loan Schedules Function
            function loadLoanSchedules(mid) {
                $('#schedule_id').html('<option>Loading loans...</option>');
                $.getJSON('ajax_get_schedules.php', {
                    member_id: mid
                }, function(data) { 
                    let html = '<option value="">-- Select Amortization --</option>';
                    if (data.length > 0) {
                        $.each(data, function(i, item) {
                            html += `<option value="${item.schedule_id}" data-loan="${item.loan_id}" data-amount="${item.total_due}">
                                Due: ${item.due_date} | Amount: ₱${item.total_due}
                             </option>`;
                        });
                    } else {
                        html = '<option value="">No active loans found.</option>';
                    }
                    $('#schedule_id').html(html);
                });
            }

            // 4. Auto-fill Amount from Schedule
            $('#schedule_id').change(function() {
                let selected = $(this).find(':selected');
                if (selected.val() != "") {
                    $('input[name="amount"]').val(selected.data('amount'));
                    $('#hidden_loan_id').val(selected.data('loan'));
                }
            });
        });
    </script>
</body>

</html>