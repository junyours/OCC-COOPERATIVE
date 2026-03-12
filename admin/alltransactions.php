<?php
require('includes/header.php');

/** 
 * Access Control: Only Admin (1) and Accounting (3)
 */
if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3])
) {
    die("Unauthorized access.");
}
?>

<style>
    /* Professional UI Enhancements */
    body {
        background-color: #f4f7f6;
        font-family: 'Inter', sans-serif;
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

    /* Transaction Terminal Styling */
    .panel-flat {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
    }

    .nav-tabs-highlight {
        border-bottom: none;
        margin-bottom: 0;
    }

    .nav-tabs-highlight>li>a {
        border-top: 3px solid transparent !important;
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 700;
        color: #777;
        padding: 15px;
    }

    .nav-tabs-highlight>li.active>a {
        border-top: 3px solid #26A69A !important;
        background-color: #fff !important;
        color: #26A69A !important;
    }

    .amount-input {
        font-size: 24px !important;
        font-weight: 800;
        color: #26A69A;
        height: 55px;
        border: 2px solid #e0e0e0;
        text-align: right;
        background: #f9fdfd !important;
    }

    .amount-input:focus {
        border-color: #26A69A;
        box-shadow: none;
    }

    /* Member Summary Stat Cards */
    .stat-card {
        padding: 15px;
        margin-bottom: 12px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #edf2f7;
    }

    .stat-label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #333;
    }

    .stat-value.text-success {
        color: #26A69A;
    }

    /* Today's Activity Table */
    .table-activity thead th {
        background-color: #f8fafc;
        text-transform: uppercase;
        font-size: 11px;
        color: #64748b;
    }

    .badge-type {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
    }
</style>

<body class="layout-boxed navbar-top">

   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../images/main_logo.jpg" alt="Logo">
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

                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <h4>
                            <i class="icon-transmission position-left"></i>
                            <span class="text-semibold">Accounting</span> - Transaction Terminal
                        </h4>
                    </div>
                </div>

                <div class="content">
                    <div class="row">

                        <!-- LEFT: Transaction Tabs -->
                        <div class="col-lg-8">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">Post New Entry</h6>
                                </div>
                                <div class="panel-body">

                                    <!-- Step 1: Member Selection -->
                                    <div class="row mb-20">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="text-semibold text-muted">Step 1: Select Member</label>
                                                <select id="global_member_id" class="form-control input-lg select-search" required>
                                                    <option value="">-- Search Member Name --</option>
                                                    <?php
                                                    $members = $db->query("
        SELECT member_id, first_name, last_name, type 
        FROM tbl_members 
        ORDER BY last_name ASC
    ");
                                                    while ($m = $members->fetch_assoc()): ?>
                                                        <option
                                                            value="<?= $m['member_id'] ?>"
                                                            data-type="<?= strtolower($m['type']) ?>">
                                                            <?= strtoupper($m['first_name'] . ' ' . $m['last_name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2: Transaction Tabs -->
                                    <label class="text-semibold text-muted">Step 2: Select Transaction Type</label>
                                    <hr class="mt-5 mb-15">

                                    <div class="tabbable">
                                        <ul class="nav nav-tabs nav-tabs-highlight nav-justified">
                                            <li class="active"><a href="#tab-deposit" data-toggle="tab"><i class="icon-wallet position-left"></i> DEPOSIT</a></li>
                                            <li><a href="#tab-withdraw" data-toggle="tab"><i class="icon-minus-circle2 position-left"></i> WITHDRAW</a></li>
                                            <li><a href="#tab-loan-pay" data-toggle="tab"><i class="icon-cash3 position-left"></i> LOAN PAY</a></li>
                                            <li>
                                                <a href="#tab-void-transaction" data-toggle="tab">
                                                    <i class="icon-cross2 position-left"></i> VOID/CANCEL
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">

                                            <!-- DEPOSIT TAB -->
                                            <div class="tab-pane active" id="tab-deposit">
                                                <form id="deposit-form">

                                                    <input type="hidden" name="member_id" id="deposit_member_id">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Deposit Type:</label>
                                                                <select name="deposit_type" id="deposit_type" class="form-control" required>
                                                                    <option value="">-- Select Deposit Type --</option>
                                                                    <option value="savings">Savings</option>
                                                                    <option value="capital_share">Capital Share</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Amount (₱):</label>
                                                                <input type="number" step="0.01" name="amount" class="form-control amount-input" placeholder="0.00" required>
                                                                <span class="help-text text-teal">Minimum ₱250 required.</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn bg-teal-600 btn-labeled">
                                                            <b><i class="icon-check"></i></b> Process Deposit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- WITHDRAW TAB -->
                                            <div class="tab-pane" id="tab-withdraw">
                                                <div class="alert alert-info border-left-info">
                                                    This tab processes approved withdrawal requests only.
                                                </div>

                                                <div class="form-group">
                                                    <label>Select Pending Withdrawal Request:</label>
                                                    <select id="withdraw_request_select" class="form-control" required>
                                                        <option value="">Select Member First...</option>
                                                    </select>
                                                </div>

                                                <form id="approve-withdraw-form">
                                                    <input type="hidden" name="action_type" value="">
                                                    <input type="hidden" name="request_id" id="withdraw_request_id">

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Requested Amount (₱):</label>
                                                                <input type="number" id="requested_amount" class="form-control amount-input" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" id="approve-btn" class="btn bg-teal-600 btn-labeled"><b><i class="icon-check"></i></b> Approve</button>
                                                        <!-- <button type="button" id="reject-btn" class="btn bg-danger-600 btn-labeled"><b><i class="icon-cross2"></i></b> Reject</button> -->
                                                    </div>
                                                </form>
                                            </div>


                                            <!-- LOAN PAYMENT TAB -->
                                            <div class="tab-pane" id="tab-loan-pay">
                                                <form id="loanPaymentForm" class="ajax-transaction-form">
                                                    <input type="hidden" name="save-loan-payments" value="1">
                                                    <input type="hidden" name="loan_id" id="loan_id_hidden">
                                                    <input type="hidden" name="account_id" id="account_id_hidden">
                                                    <input type="hidden" name="schedule_id" id="schedule_id_hidden">

                                                    <div class="row mb-15">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Payment Method:</label>
                                                                <select name="pay_mode" id="pay_mode" class="form-control" required>
                                                                    <option value="scheduled">Scheduled Amortization</option>
                                                                    <option value="custom">Custom Payment</option>
                                                                    <option value="full">Full Payment</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6" id="sched_wrapper">
                                                            <div class="form-group">
                                                                <label>Select Due Date / Schedule:</label>
                                                                <select name="schedule_id" id="schedule_select" class="form-control" required>
                                                                    <option value="">Select Member First...</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Amount (₱):</label>
                                                                <input type="number" step="0.01" name="amount_paid" id="loan_amount_input" class="form-control amount-input" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn bg-teal-600 btn-labeled">
                                                            <b><i class="icon-check"></i></b> Process Payment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <div class="tab-pane" id="tab-void-transaction">

                                                <div class="alert alert-danger border-left-danger">
                                                    <strong>Warning!</strong> Voiding a transaction will create a reversal entry.

                                                </div>

                                                <div class="form-group">
                                                    <label>Select Transaction to Void:</label>
                                                    <select id="void_transaction_select" class="form-control" required>
                                                        <option value="">Select Member First...</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Reason for Voiding:</label>
                                                    <textarea id="void_reason" class="form-control" required></textarea>
                                                </div>

                                                <div class="text-right">
                                                    <button type="button" id="btnConfirmVoid" class="btn btn-danger">
                                                        <i class="icon-cross2"></i> Void Transaction
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>



                                </div>
                            </div>

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">
                                        <i class="icon-history position-left"></i>
                                        Today's Transaction Log
                                    </h6>
                                </div>

                                <div class="table-responsive">
                                    <table id="today-transactions-table" class="table table-activity table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ref #</th>
                                                <th>Member</th>

                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Time</th>
                                                <!-- <th class="text-center">Status</th> -->
                                            </tr>
                                        </thead>
                                        <tbody id="recent_transactions_log">
                                            <?php

                                            $today = date('Y-m-d');

                                            $recent = $db->query("
                                                SELECT 
                                                    t.*,
                                                    a.account_number,
                                                    m.first_name,
                                                    m.last_name,
                                                    tt.type_name
                                                FROM transactions t
                                                LEFT JOIN accounts a ON t.account_id = a.account_id
                                                LEFT JOIN tbl_members m ON a.member_id = m.member_id
                                                LEFT JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
                                                WHERE DATE(t.created_at) = '$today'
                                                ORDER BY t.created_at DESC
                                              
                                            ");

                                            if ($recent->num_rows > 0):
                                                while ($row = $recent->fetch_assoc()):

                                                    $member_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                                                    $member_name = $member_name !== '' ? strtoupper($member_name) : 'N/A';


                                                    $transaction_type = strtoupper($row['type_name'] ?? 'N/A');
                                            ?>
                                                    <tr>
                                                        <td class="text-semibold"><?= $row['reference_no'] ?></td>
                                                        <td><?= $member_name ?></td>
                                                        <td>
                                                            <span class="badge-type bg-teal-400"><?= $transaction_type ?></span>
                                                        </td>
                                                        <td class="text-semibold text-teal">₱<?= number_format($row['amount'], 2) ?></td>
                                                        <td class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></td>
                                                        <!-- <td class="text-center"><span class="label label-flat border-success text-success">Success</span></td> -->
                                                    </tr>
                                                <?php
                                                endwhile;
                                            else:
                                                ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted p-20">No transactions recorded today yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                        <div class="col-lg-4">
                            <div class="panel panel-flat bg-success-400" style="margin-bottom: 10px;">
                                <div class="p-10 text-center">
                                    <span class="text-semibold" style="font-size: 12px; opacity: 0.9;">System Time:</span>
                                    <span id="live_date" class="ml-5" style="font-size: 12px;"></span>
                                    <span id="live_clock" class="text-bold ml-5" style="font-size: 14px;">00:00:00 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Member Summary -->
                        <div class="col-lg-4">
                            <div class="panel panel-white border-top-success">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">Account Summary</h6>
                                </div>
                                <div id="member_summary_panel" class="panel-body">
                                    <div class="text-center p-20 text-muted">
                                        <i class="icon-user-check icon-3x"></i>
                                        <p class="mt-10">Select a member to load financial data.</p>
                                    </div>
                                </div>
                            </div>




                            <div class="panel panel-flat border-top-primary">
                                <div class="panel-heading">
                                    <h6 class="panel-title text-semibold">
                                        <i class="icon-reading position-left text-primary"></i>
                                        Terminal Guidelines
                                    </h6>
                                </div>

                                <div class="panel-body">
                                    <div class="content-group">
                                        <p class="text-muted content-group-sm">Follow these standard operating procedures for error-free posting:</p>

                                        <div class="media">
                                            <div class="media-left"><i class="icon-point-right text-teal"></i></div>
                                            <div class="media-body">
                                                <h6 class="media-heading text-semibold">Verification</h6>
                                                <span class="text-muted">Always cross-reference the <strong>Member Name</strong> in the summary panel before clicking "Confirm".</span>
                                            </div>
                                        </div>

                                        <div class="media">
                                            <div class="media-left"><i class="icon-point-right text-teal"></i></div>
                                            <div class="media-body">
                                                <h6 class="media-heading text-semibold">Min. Deposit</h6>
                                                <span class="text-muted">Ensure "Savings, CBU" deposits meet the <strong>₱250.00</strong> floor requirement.</span>
                                            </div>
                                        </div>

                                        <div class="media">
                                            <div class="media-left"><i class="icon-point-right text-teal"></i></div>
                                            <div class="media-body">
                                                <h6 class="media-heading text-semibold">Withdrawals</h6>
                                                <span class="text-muted">Processing a withdrawal here marks the request as <strong>"Released"</strong>.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning alert-styled-left alert-bordered">
                                        <span class="text-semibold">Audit Warning:</span>
                                        All voided transactions require a reason and are logged with your account name: <strong><?= $_SESSION['fullname'] ?></strong>.
                                    </div>
                                </div>
                            </div>





                            <!--Content-->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Withdrawal Confirmation Modal -->
    <div id="withdrawConfirmModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-teal-600">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Withdrawal Confirmation</h5>
                </div>

                <div class="modal-bodys">
                    <p><strong>Member:</strong> <span id="modal_member_name"></span></p>
                    <p><strong>Request ID:</strong> <span id="modal_request_id"></span></p>
                    <p><strong>Amount:</strong> ₱<span id="modal_amount"></span></p>
                    <hr>
                    <p id="modal_action_text" class="text-semibold"></p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmWithdrawAction" class="btn btn-success">
                        Confirm
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Deposit Confirmation Modal -->
    <div id="depositConfirmModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-teal-600">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Deposit Confirmation</h5>
                </div>

                <div class="modal-bodys">
                    <p><strong>Member:</strong> <span id="deposit_modal_member"></span></p>
                    <p><strong>Deposit Type:</strong> <span id="deposit_modal_type"></span></p>
                    <p><strong>Amount:</strong> ₱<span id="deposit_modal_amount"></span></p>
                    <hr>
                    <p class="text-semibold">Are you sure you want to proceed with this deposit?</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDepositAction" class="btn btn-success">Confirm Deposit</button>
                </div>

            </div>
        </div>
    </div>


    <!-- LOAN PAYMENT CONFIRMATION MODAL -->
    <div id="loanConfirmModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-teal-600 text-white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">Loan Payment Confirmation</h5>
                </div>

                <div class="modal-bodys">
                    <p><strong>Member:</strong> <span id="loan_modal_member"></span></p>
                    <p><strong>Payment Mode:</strong> <span id="loan_modal_mode"></span></p>
                    <p><strong>Amount:</strong> ₱<span id="loan_modal_amount"></span></p>
                    <p><strong>Due Schedule:</strong> <span id="loan_modal_schedule"></span></p>
                    <hr>
                    <p class="text-semibold">Are you sure you want to proceed with this payment?</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmLoanPayment" class="btn btn-success">Confirm Payment</button>
                </div>

            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>

    <?php require('includes/footer-text.php'); ?>






    <script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/select2.min.js"></script>
    <script src="../js/validator.min.js"></script>
    <script src="../js/alltransactions.js"></script>
    <script>
        $(document).ready(function() {
            $('#global_member_id').select2({
                placeholder: '-- Search Member Name --',
                allowClear: true,
                width: '100%',
                minimumInputLength: 1 // start searching after typing 1 character
            });
        });



        $(document).ready(function() {
            $('#today-transactions-table').DataTable({
                pageLength: 5, // Show first 5 rows initially
                lengthMenu: [5, 10, 25, 50, 100], // Options for user to view more
                autoWidth: false,
                dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    lengthMenu: '<span>Show:</span> _MENU_',
                    paginate: {
                        'first': 'First',
                        'last': 'Last',
                        'next': '&rarr;',
                        'previous': '&larr;'
                    }
                },
                order: [
                    [4, "desc"]
                ],
                columnDefs: [{
                    orderable: false,
                    width: '100px',
                    targets: [4]
                }],
                drawCallback: function() {
                    $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
                },
                preDrawCallback: function() {
                    $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
                }
            });

            // Enhance the "Show entries" dropdown with Select2
            $('.dataTables_length select').select2({
                minimumResultsForSearch: Infinity,
                width: 'auto'
            });
        });


        function startLiveClock() {
            const timeElement = document.getElementById('live_clock');
            const dateElement = document.getElementById('live_date');

            function update() {
                const now = new Date();

                // Format Time: 12:00:00 PM
                const timeString = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                // Format Date: Wednesday, March 4, 2026
                const dateString = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                timeElement.textContent = timeString;
                dateElement.textContent = dateString;
            }

            // Run every second
            setInterval(update, 1000);
            update(); // Run immediately so there is no 1-second delay
        }

        // Initialize the clock
        startLiveClock();
    </script>

</body>

</html>