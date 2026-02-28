        <?php
        require('includes/header.php');
        require('../db_connect.php');



        if (
            !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
            || $_SESSION['is_login_yes'] != 'yes'
            || !in_array((int)$_SESSION['usertype'], [1, 3])
        ) {
            die("Unauthorized access.");
        }

        // Fetch pending loans
        $pending_loans = $db->query("
    SELECT 
        l.loan_id,
        l.requested_amount,
        l.term_value,
        l.term_unit,
        l.payment_frequency,
        l.status,
        l.application_date,
        CONCAT(m.first_name,' ',m.last_name) AS member_name,
        lt.loan_type_name
    FROM loans l
    JOIN accounts a ON a.account_id = l.account_id
    JOIN tbl_members m ON m.member_id = a.member_id
    JOIN loan_types lt ON lt.loan_type_id = l.loan_type_id
    WHERE l.status = 'pending'
    ORDER BY l.loan_id DESC
");

        // Approved loans
        $approved_loans = $db->query("
    SELECT 
        l.loan_id,
        l.approved_amount,
        l.term_value,
        l.term_unit,
        l.payment_frequency,
        l.status,
        l.approved_date,
        CONCAT(m.first_name,' ',m.last_name) AS member_name,
        lt.loan_type_name
    FROM loans l
    JOIN accounts a ON a.account_id = l.account_id
    JOIN tbl_members m ON m.member_id = a.member_id
    JOIN loan_types lt ON lt.loan_type_id = l.loan_type_id
    WHERE l.status = 'approved'
    ORDER BY l.loan_id DESC
");

        // Disbursed loans
        $disbursed_loans = $db->query("
    SELECT 
        l.loan_id,
        l.approved_amount,
        l.term_value,
        l.term_unit,
        l.payment_frequency,
        l.released_date,
        CONCAT(m.first_name,' ',m.last_name) AS member_name,
        lt.loan_type_name
    FROM loans l
    JOIN accounts a ON a.account_id = l.account_id
    JOIN tbl_members m ON m.member_id = a.member_id
    JOIN loan_types lt ON lt.loan_type_id = l.loan_type_id
    WHERE l.status = 'ongoing'
    ORDER BY l.released_date DESC
");

        $members = $db->query("
    SELECT a.account_id, m.member_id, CONCAT(m.first_name,' ',m.last_name) AS member_name
    FROM accounts a
    JOIN tbl_members m ON m.member_id = a.member_id
    WHERE a.account_type_id = 3 AND m.status='active'
");


        $loan_types = $db->query("
    SELECT * FROM loan_types WHERE status='active'
");

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
                                    <li><a href="javascript:;" data-toggle="modal" data-target="#modal-new"><i class="icon-add position-left text-teal-400"></i> New Loan</a></li>
                                    <li><a href="loan-transaction.php"><i class="icon-coins position-left text-primary"></i> View Active Loans</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="content">

                            <!-- Pending Loans -->
                            <div class="panel panel-white border-top-xlg border-top-warning">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-hour-glass2 text-warning position-left"></i> Pending Loan Applications</h6>
                                </div>
                                <div class="panel-body panel-theme">
                                    <table class="table datatable-button-html5-basic table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Loan Type</th>
                                                <th>Amount</th>
                                                <th>Term</th>
                                                <th>Frequency</th>
                                                <th>Status</th>
                                                <th>Date Applied</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $pending_loans->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['member_name']); ?></td>
                                                    <td><?= htmlspecialchars($row['loan_type_name']); ?></td>
                                                    <td style="text-align:right"><?= number_format($row['requested_amount'], 2); ?></td>
                                                    <td><?= (int)$row['term_value'] ?> <?= htmlspecialchars($row['term_unit']); ?></td>
                                                    <td><?= htmlspecialchars($row['payment_frequency']); ?></td>
                                                    <td align="center"><span class="label label-warning"><?= ucfirst($row['status']); ?></span></td>
                                                    <td><?= htmlspecialchars($row['application_date']); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Approved Loans -->
                            <div class="panel panel-white border-top-xlg border-top-success">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-checkmark4 text-success position-left"></i> Approved Loans</h6>
                                </div>
                                <div class="panel-body panel-theme">
                                    <table class="table datatable-button-html5-basic table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Loan Type</th>
                                                <th>Amount</th>
                                                <th>Term</th>
                                                <th>Frequency</th>
                                                <th>Status</th>
                                                <th>Date Approved</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $approved_loans->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['member_name']); ?></td>
                                                    <td><?= htmlspecialchars($row['loan_type_name']); ?></td>
                                                    <td style="text-align:right"><?= number_format($row['approved_amount'], 2); ?></td>
                                                    <td><?= (int)$row['term_value'] ?> <?= htmlspecialchars($row['term_unit']); ?></td>
                                                    <td><?= htmlspecialchars($row['payment_frequency']); ?></td>
                                                    <td align="center"><span class="label label-success"><?= ucfirst($row['status']); ?></span></td>
                                                    <td><?= htmlspecialchars($row['approved_date']); ?></td>
                                                    <td align="center">
                                                        <button class="btn btn-sm btn-success"
                                                            onclick='openDisburseModal(<?= json_encode([
                                                                                            "loan_id" => $row["loan_id"],
                                                                                            "member_name" => $row["member_name"],
                                                                                            "loan_type_name" => $row["loan_type_name"],
                                                                                            "approved_amount" => $row["approved_amount"],
                                                                                            "term_value" => $row["term_value"],
                                                                                            "term_unit" => $row["term_unit"],
                                                                                            "payment_frequency" => $row["payment_frequency"]
                                                                                        ]); ?>)'>
                                                            <i class="icon-wallet"></i> Disburse
                                                        </button>

                                                        <button class="btn btn-sm btn-danger"
                                                            onclick='openCancelModal(<?= json_encode([
                                                                                            "loan_id" => $row["loan_id"],
                                                                                            "member_name" => $row["member_name"],
                                                                                            "loan_type_name" => $row["loan_type_name"],
                                                                                            "approved_amount" => $row["approved_amount"]
                                                                                        ]); ?>)'>
                                                            <i class="icon-cross2"></i> Cancel
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Disbursed Loans -->
                            <div class="panel panel-white border-top-xlg border-top-primary">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-wallet text-primary position-left"></i> Disbursed Loans</h6>
                                </div>
                                <div class="panel-body panel-theme">
                                    <table class="table datatable-button-html5-basic table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Loan Type</th>
                                                <th>Amount Released</th>
                                                <th>Term</th>
                                                <th>Frequency</th>
                                                <th>Release Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $disbursed_loans->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['member_name']); ?></td>
                                                    <td><?= htmlspecialchars($row['loan_type_name']); ?></td>
                                                    <td style="text-align:right"><?= number_format($row['approved_amount'], 2); ?></td>
                                                    <td><?= (int)$row['term_value'] ?> <?= htmlspecialchars($row['term_unit']); ?></td>
                                                    <td><?= htmlspecialchars($row['payment_frequency']); ?></td>
                                                    <td><?= htmlspecialchars($row['released_date']); ?></td>
                                                    <td>
                                                        <button class="btn btn-info btn-print-disbursement view-receipt" data-id="<?= $row['loan_id']; ?>">
                                                            <i class="icon-printer"></i> Print
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div id="modal-new" class="modal fade" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="form-new" class="form-horizontal">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h5 class="modal-title">New Loan Application</h5>
                            </div>
                            <div class="modal-bodys">
                                <input type="hidden" name="save-loan-application" value="1">

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Member</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select-member-search" name="member_id" required>
                                            <option value="">-- Select Member --</option>
                                            <?php
                                            $members = $db->query("
                                    SELECT member_id, CONCAT(first_name,' ',last_name) AS member_name
                                    FROM tbl_members
                                    WHERE status='active'
                                ");
                                            while ($m = $members->fetch_assoc()) {
                                                echo '<option value="' . $m['member_id'] . '">' . htmlspecialchars($m['member_name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Loan Type</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="loan_type_id" required>
                                            <option value="">-- Select Loan Type --</option>
                                            <?php
                                            $loan_types = $db->query("SELECT * FROM loan_types WHERE status='active'");
                                            while ($lt = $loan_types->fetch_assoc()) {
                                                echo '<option value="' . $lt['loan_type_id'] . '">' . htmlspecialchars($lt['loan_type_name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Amount</label>
                                    <div class="col-sm-9">
                                        <input type="number" step="0.01" class="form-control" name="requested_amount" placeholder="Enter amount" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Purpose</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" name="purpose" placeholder="Loan purpose"></textarea>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled">
                                    <b><i class="icon-add"></i></b> Save Loan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="modal-disburse" class="modal fade" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="form-disburse" class="form-horizontal">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h5 class="modal-title">Confirm Loan Disbursement</h5>
                            </div>
                            <div class="modal-bodys">
                                <input type="hidden" name="disburse_loan" value="1">
                                <input type="hidden" name="loan_id" id="disburse-loan-id">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Member</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="disburse-member" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Loan Type</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="disburse-loan-type" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Approved Amount</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="disburse-amount" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Term</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="disburse-term" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Frequency</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="disburse-frequency" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success btn-labeled">
                                    <b><i class="icon-wallet"></i></b> Confirm Disbursement
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>

                            <div class="alert alert-info disburse-warning">
                                <i class="icon-info22"></i>
                                This will officially release the loan amount to the member.
                                Please confirm all details before proceeding.
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Loan Receipt Modal -->
            <div id="loanReceiptModal" class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Loan Disbursed</h5>
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


            <div id="modal-cancel" class="modal fade" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-md">
                    <div class="modal-content cancel-modal">

                        <form id="form-cancel" class="form-horizontal">

                            <!-- Header -->
                            <div class="modal-header bg-danger text-white">
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">
                                    <i class="icon-warning"></i> Confirm Loan Cancellation
                                </h4>
                            </div>

                            <!-- Body -->
                            <div class="modal-bodys">

                                <input type="hidden" name="cancelledloan" value="1">
                                <input type="hidden" name="loan_id" id="cancel-loan-id">
                                <!-- Loan Info Card -->
                                <div class="cancel-info-box">
                                    <p><strong>Member:</strong><br>
                                        <span id="cancel-member" class="info-value"></span>
                                    </p>
                                    <p><strong>Loan Type:</strong><br>
                                        <span id="cancel-type" class="info-value"></span>
                                    </p>
                                    <p><strong>Approved Amount:</strong><br>
                                        ₱ <span id="cancel-amount" class="info-value text-danger"></span>
                                    </p>
                                </div>
                                <!-- Warning Box -->
                                <div class="alert alert-warning cancel-warning">
                                    <i class="icon-info22"></i>
                                    The approved amount will be returned to the selected loan fund.
                                    <br>
                                    This action cannot be undone.
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-danger btn-confirm">
                                    <i class="icon-cross2"></i> Confirm Cancellation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php require('includes/footer-text.php'); ?>
            <?php require('includes/footer.php'); ?>
            <script src="../js/select2.min.js"></script>
            <script src="../js/validator.min.js"></script>
            <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
            <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function() {


                    $('#modal-new').on('shown.bs.modal', function() {
                        $('.select-member-search').select2({
                            dropdownParent: $('#modal-new'),
                            width: '100%'
                        });
                    });


                    $('.datatable-button-html5-basic').DataTable({
                        "pageLength": 5,
                        "lengthMenu": [5, 10, 25, 50, 100],
                        "order": [],
                        "autoWidth": false,
                        "responsive": true,
                        "language": {
                            "search": "Filter records:"
                        }
                    });


                    $('#member_id').change(function() {
                        var member_id = $(this).val();
                        if (member_id) {
                            $.ajax({
                                url: '../transaction.php',
                                type: 'POST',
                                data: {
                                    action: 'get_accounts',
                                    member_id: member_id
                                },
                                dataType: 'json',
                                success: function(resp) {
                                    $('#account_id').html('<option value="">-- Select Account --</option>');
                                    resp.forEach(function(a) {
                                        $('#account_id').append('<option value="' + a.account_id + '">' + a.account_number + '</option>');
                                    });
                                },
                                error: function(xhr, status, error) {
                                    $.jGrowl('Error loading accounts: ' + error, {
                                        header: 'Error',
                                        theme: 'bg-danger'
                                    });
                                }
                            });
                        } else {
                            $('#account_id').html('<option value="">-- Select Account --</option>');
                        }
                    });

                    // Loan application submission
                    $('#form-new').submit(function(e) {
                        e.preventDefault();
                        var formData = $(this).serialize();

                        $.ajax({
                            url: '../transaction.php',
                            type: 'POST',
                            data: formData,
                            success: function(resp) {
                                resp = resp.trim();
                                if (resp === "1") {
                                    $.jGrowl('Loan application saved successfully!', {
                                        header: 'Success',
                                        theme: 'bg-success'
                                    });
                                    $('#modal-new').modal('hide');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                } else {
                                    $.jGrowl('Error: ' + resp, {
                                        header: 'Error',
                                        theme: 'bg-danger'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                $.jGrowl('AJAX Error: ' + error, {
                                    header: 'Error',
                                    theme: 'bg-danger'
                                });
                            }
                        });
                    });

                    // Disburse modal
                    window.openDisburseModal = function(loan) {
                        $('#disburse-loan-id').val(loan.loan_id);
                        $('#disburse-member').val(loan.member_name);
                        $('#disburse-loan-type').val(loan.loan_type_name);
                        $('#disburse-amount').val(parseFloat(loan.approved_amount).toFixed(2));
                        $('#disburse-term').val(`${loan.term_value} ${loan.term_unit}`);
                        $('#disburse-frequency').val(loan.payment_frequency);
                        $('#modal-disburse').modal('show');
                    };

                    $('#form-disburse').submit(function(e) {
                        e.preventDefault();
                        var formData = $(this).serialize();

                        $.ajax({
                            url: '../transaction.php',
                            type: 'POST',
                            data: formData,
                            success: function(resp) {
                                resp = resp.trim();
                                if (resp === "1") {
                                    $.jGrowl('Loan disbursed successfully!', {
                                        header: 'Success',
                                        theme: 'bg-success'
                                    });
                                    $('#modal-disburse').modal('hide');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                } else {
                                    $.jGrowl('Error: ' + resp, {
                                        header: 'Error',
                                        theme: 'bg-danger'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                $.jGrowl('AJAX Error: ' + error, {
                                    header: 'Error',
                                    theme: 'bg-danger'
                                });
                            }
                        });
                    });

                    // Open Cancel Modal
                    window.openCancelModal = function(loan) {
                        $('#cancel-loan-id').val(loan.loan_id);
                        $('#cancel-member').text(loan.member_name);
                        $('#cancel-type').text(loan.loan_type_name);
                        $('#cancel-amount').text(parseFloat(loan.approved_amount).toFixed(2));
                        $('#modal-cancel').modal('show');
                    };

                    // Submit Cancel
                    $('#form-cancel').submit(function(e) {
                        e.preventDefault();
                        var formData = $(this).serialize();

                        $.ajax({
                            url: '../transaction.php',
                            type: 'POST',
                            data: formData,
                            success: function(resp) {
                                resp = resp.trim();
                                if (resp === "1") {
                                    $.jGrowl('Loan cancelled successfully!', {
                                        header: 'Success',
                                        theme: 'bg-success'
                                    });
                                    $('#modal-cancel').modal('hide');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                } else {
                                    $.jGrowl('Error: ' + resp, {
                                        header: 'Error',
                                        theme: 'bg-danger'
                                    });
                                }
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

                    // Print Receipt
                    window.printReceipt = function() {
                        let content = document.getElementById('loan-receipt-body').innerHTML;
                        let w = window.open('', '', 'height=600,width=800');
                        w.document.write('<html><head><title>Loan Receipt</title></head><body>');
                        w.document.write(content);
                        w.document.write('</body></html>');
                        w.document.close();
                        w.print();
                    };
                });
            </script>