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

                <div class="page-header page-header-default">

                    <div class="page-header-content">

                        <h4>
                            <i class="icon-arrow-left52 position-left"></i>
                            <span class="text-semibold">Loan</span> - Active Loans
                        </h4>

                    </div>

                    <div class="breadcrumb-line">

                        <ul class="breadcrumb">
                            <li><a href="loan.php"><i class="icon-cash3"></i> Loan</a></li>
                            <li class="active">Active Loans</li>
                        </ul>

                    </div>
                </div>



                <div class="content">

                    <div class="panel panel-white border-top-xlg border-top-primary">

                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <i class="icon-coins text-primary position-left"></i>
                                Ongoing Loans
                            </h6>
                        </div>


                        <div class="panel-body panel-theme">

                            <table class="table table-bordered table-hover" id="loan-ledger">

                                <thead>
                                    <tr>

                                        <th>Member</th>
                                        <th>Approved Amount</th>
                                        <th>Term</th>
                                        <th>Interest</th>
                                        <th>Total Due</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Next Month Due</th>
                                        <th>Action</th>

                                    </tr>
                                </thead>

                                <tbody>

                                    <?php

                                    $loans = $db->query("
SELECT l.*,a.account_id,m.first_name,m.last_name
FROM loans l
JOIN accounts a ON l.account_id=a.account_id
JOIN tbl_members m ON a.member_id=m.member_id
WHERE l.status='ongoing'
ORDER BY l.loan_id DESC
");

                                    while ($loan = $loans->fetch_assoc()):

                                        $loan_id = $loan['loan_id'];

                                        $member_name = $loan['first_name'] . ' ' . $loan['last_name'];

                                        $stmt = $db->prepare("
SELECT SUM(amount_paid) total_paid
FROM loan_payments
WHERE loan_id=?
");

                                        $stmt->bind_param("i", $loan_id);
                                        $stmt->execute();

                                        $paid = $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0;

                                        $balance = $loan['total_due'] - $paid;

                                        if ($balance <= 0) continue;


                                        $stmt2 = $db->prepare("
  SELECT schedule_id, total_due, due_date
    FROM loan_schedule
    WHERE loan_id = ?
    AND status IN ('ongoing','overdue')
    ORDER BY due_date ASC
    LIMIT 1
");
                                        $stmt2->bind_param("i", $loan_id);
                                        $stmt2->execute();

                                        $schedule = $stmt2->get_result()->fetch_assoc();

                                        $monthly_due = $schedule['total_due'] ?? 0;

                                    ?>

                                        <tr>

                                            <td><b><?= htmlspecialchars($member_name) ?></b></td>

                                            <td class="text-right">
                                                <?= number_format($loan['approved_amount'], 2) ?>
                                            </td>

                                            <td>
                                                <?= $loan['term_value'] . ' ' . $loan['term_unit'] ?>
                                            </td>

                                            <td>
                                                <?= number_format($loan['interest_rate'], 2) ?>%
                                            </td>

                                            <td class="text-right">
                                                <?= number_format($loan['total_due'], 2) ?>
                                            </td>

                                            <td class="text-success text-right">
                                                <?= number_format($paid, 2) ?>
                                            </td>

                                            <td class="text-danger text-right">
                                                <?= number_format($balance, 2) ?>
                                            </td>

                                            <td class="text-danger text-right">
                                                <?= number_format($monthly_due, 2) ?>
                                            </td>

                                            <td align="center">
                                                <!-- View button -->
                                                <a href="loan_ledger_detail.php?loan_id=<?= $loan_id ?>"
                                                    class="btn btn-xs btn-primary">
                                                    View
                                                </a>

                                                <!-- Payment button -->
                                                <button type="button"
                                                    class="btn btn-xs btn-success btn-payment"

                                                    data-account-id="<?= $loan['account_id'] ?>"
                                                    data-loan-id="<?= $loan_id ?>"
                                                    data-schedule-id="<?= $schedule['schedule_id'] ?? '' ?>"
                                                    data-member-name="<?= htmlspecialchars($member_name) ?>"

                                                    data-next-due="<?= $schedule['total_due'] ?? 0 ?>"
                                                    data-balance="<?= $balance ?>"
                                                    data-total-due="<?= $loan['total_due'] ?>"
                                                    data-paid="<?= $paid ?>">

                                                    Pay

                                                </button>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>
                    </div>


                    <!-- FULLY PAID LOANS -->
                    <div class="panel panel-white border-top-xlg border-top-success">

                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <i class="icon-checkmark-circle text-success position-left"></i>
                                Fully Paid Loans
                            </h6>
                        </div>

                        <div class="panel-body panel-theme">

                            <table class="table table-bordered table-hover" id="loan-paid-table">

                                <thead>
                                    <tr>

                                        <th>Member</th>
                                        <th>Approved Amount</th>
                                        <th>Term</th>
                                        <th>Interest</th>
                                        <th>Total Due</th>
                                        <th>Total Paid</th>
                                        <th>Date Approved</th>
                                        <th>Date Released</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                    </tr>
                                </thead>

                                <tbody>

                                    <?php

                                    $paid_loans = $db->query("
                SELECT l.*,a.account_id,m.first_name,m.last_name
                FROM loans l
                JOIN accounts a ON l.account_id=a.account_id
                JOIN tbl_members m ON a.member_id=m.member_id
                WHERE l.status='paid'
                ORDER BY l.loan_id DESC
                ");

                                    while ($loan = $paid_loans->fetch_assoc()):

                                        $loan_id = $loan['loan_id'];

                                        $member_name = $loan['first_name'] . ' ' . $loan['last_name'];

                                        $stmt = $db->prepare("
                    SELECT SUM(amount_paid) total_paid
                    FROM loan_payments
                    WHERE loan_id=?
                    ");

                                        $stmt->bind_param("i", $loan_id);
                                        $stmt->execute();

                                        $paid = $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0;

                                    ?>

                                        <tr>

                                            <td>
                                                <b><?= htmlspecialchars($member_name) ?></b>
                                            </td>

                                            <td class="text-right">
                                                <?= number_format($loan['approved_amount'], 2) ?>
                                            </td>

                                            <td>
                                                <?= $loan['term_value'] . ' ' . $loan['term_unit'] ?>
                                            </td>

                                            <td>
                                                <?= number_format($loan['interest_rate'], 2) ?>%
                                            </td>

                                            <td class="text-right">
                                                <?= number_format($loan['total_due'], 2) ?>
                                            </td>

                                            <td class="text-success text-right">
                                                <?= number_format($paid, 2) ?>
                                            </td>

                                            <td>
                                                <?= date('M d, Y', strtotime($loan['approved_date'])) ?>
                                            </td>

                                            <td>
                                                <?= date('M d, Y', strtotime($loan['released_date'])) ?>
                                            </td>

                                            <td align="center">
                                                <span class="label bg-success">
                                                    Fully Paid
                                                </span>
                                            </td>

                                            <td align="center">

                                                <a href="loan_ledger_detail.php?loan_id=<?= $loan_id ?>"
                                                    class="btn btn-xs btn-primary">
                                                    View
                                                </a>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>



    <!-- MODAL -->
    <div class="modal fade" id="modal-new" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <form id="loanPaymentForm">
                    <input type="hidden" name="save-loan-payments" value="1">

                    <!-- Header -->
                    <div class="modal-header bg-teal-400 text-white">
                        <button type="button" class="close text-white" data-dismiss="modal">×</button>
                        <h4 class="modal-title">
                            <i class="icon-cash3"></i> Add Loan Payment
                        </h4>
                    </div>

                    <!-- Body -->
                    <div class="modal-bodys">

                        <!-- Payment Type -->
                        <div class="form-group">
                            <label><strong>Payment Type</strong></label>
                            <select id="payment_type" class="form-control">
                                <option value="" selected disabled>Select Payment Type</option>
                                <option value="full">Full Payment</option>
                                <option value="schedule">Scheduled Payment</option>
                                <option value="custom">Custom Payment</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Loan Details Section -->
                        <div id="loan_schedule_section" style="display:none;">

                            <input type="hidden" name="account_id" id="selected_account_id">
                            <input type="hidden" name="loan_id" id="selected_loan_id">
                            <input type="hidden" name="schedule_id" id="selected_schedule_id">
                            <input type="hidden" id="balance">
                            <input type="hidden" id="total_due">
                            <input type="hidden" id="total_paid">

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Loan Information</strong>
                                </div>
                                <div class="panel-body">

                                    <div class="form-group">
                                        <label>Member</label>
                                        <input type="text" id="member_name" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Next Due Date</label>
                                        <input type="text" id="next_due" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Payment Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">₱</span>
                                            <input type="number"
                                                step="0.01"
                                                name="amount_paid"
                                                id="amount_paid"
                                                class="form-control"
                                                required readonly>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Alert -->
                        <div id="payment_alert" class="alert" style="display:none;"></div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="icon-checkmark3"></i> Save Payment
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    <?php require('includes/footer-text.php'); ?>
    <?php require('includes/footer.php'); ?>



    <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/validator.min.js"></script>

    <script>
        $(document).on('click', '.btn-payment', function() {
            $('#selected_account_id').val($(this).data('account-id'));
            $('#selected_loan_id').val($(this).data('loan-id'));
            $('#selected_schedule_id').val($(this).data('schedule-id'));
            $('#member_name').val($(this).data('member-name'));
            $('#next_due').val($(this).data('next-due'));
            $('#balance').val($(this).data('balance'));
            $('#total_due').val($(this).data('total-due'));
            $('#total_paid').val($(this).data('paid'));
            $('#amount_paid').val('');
            $('#loan_schedule_section').show();
            $('#payment_type').val('');
            $('#modal-new').modal('show');

        });


        $(document).on('change', '#payment_type', function() {
            let type = $(this).val();
            let balance = parseFloat($('#balance').val()) || 0;
            let next_due = parseFloat($('#next_due').val()) || 0;
            if (type === 'full') {
                $('#amount_paid').val(balance.toFixed(2)).prop('readonly', true);
            } else if (type === 'schedule') {
                $('#amount_paid').val(next_due.toFixed(2)).prop('readonly', true);
            } else if (type === 'custom') {
                $('#amount_paid').val('').prop('readonly', false);
            }
        });



        $(document).ready(function() {

            $('#loan-ledger').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [0, 'desc']
                ]
            });

            $('#loan-paid-table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [0, 'desc']
                ]
            });

        });



        $(document).on(
            'click',
            '.select-account',
            function() {
                $('#selected_account_id').val($(this).data('account-id'));
                $('#selected_loan_id').val($(this).data('loan-id'));
                $('#selected_schedule_id').val($(this).data('schedule-id'));
                $('#member_name').val($(this).data('member-name'));
                $('#next_due').val($(this).data('next-due'));
                $('#amount_paid').val($(this).data('next-due'));
                $('#loan_schedule_section').show();
                $('#account_list').hide();
            });


        $('#loanPaymentForm').validator().on('submit', function(e) {
            if (!e.isDefaultPrevented()) {
                e.preventDefault();

                $(':input[type="submit"]').prop('disabled', true);
                var data = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: '../transaction.php',
                    data: data,
                    dataType: 'json', // IMPORTANT
                    success: function(res) {
                        if (res.success) {
                            $.jGrowl(
                                'Loan payment successfully saved.<br>Reference: ' + res.reference, {
                                    header: 'Success',
                                    theme: 'alert-styled-right bg-success'
                                }
                            );

                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $.jGrowl(
                                res.message || 'Payment failed', {
                                    header: 'Error',
                                    theme: 'alert-styled-right bg-danger'
                                }
                            );
                            $(':input[type="submit"]').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        $.jGrowl(
                            'Server Error', {
                                header: 'Error',
                                theme: 'alert-styled-right bg-danger'
                            }
                        );
                        $(':input[type="submit"]').prop('disabled', false);
                    }
                });
                return false;
            }
        });
    </script>