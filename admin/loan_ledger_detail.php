<?php require('includes/header.php'); ?>

<?php
if (!isset($_GET['loan_id'])) {
    die("Invalid request: Missing loan ID.");
}
$loan_id = (int)$_GET['loan_id'];

// Fetch loan + member info
$stmt = $db->prepare("
    SELECT l.*, a.account_id, m.first_name, m.last_name
    FROM loans l
    JOIN accounts a ON l.account_id = a.account_id
    JOIN tbl_members m ON a.member_id = m.member_id
    WHERE l.loan_id = ?
");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$loan) {
    die("Loan not found.");
}
$member_name = $loan['first_name'] . ' ' . $loan['last_name'];
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



    /* Smooth transition for tabs */
    .nav-tabs>li>a {
        transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
    }

    /* Hover effect */
    .nav-tabs>li>a:hover {
        transform: scale(1.05);
        /* slightly bigger */
        background-color: #b0c4de;
        /* subtle highlight, you can change color */
        color: #000 !important;
        /* ensure text is readable */
    }

    /* Active tab pop effect */
    .nav-tabs>li.active>a {
        transform: scale(1.05);
        font-weight: bold;
        color: #fff !important;
        background-color: #26a69a !important;
        /* your main tab color */   
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
</style>

<body class="layout-boxed navbar-top">
    <!-- Navbar -->
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
                            <h4><i class="icon-books position-left"></i> Loan Info - <?= htmlspecialchars($member_name) ?></h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="loan-transaction.php"><i class="icon-coins"></i> Active Loans</a></li>
                            <li class="active">Info</li>
                        </ul>
                    </div>
                </div>

                <div class="content">
                    <div class="panel panel-flat">
                        <div class="panel-body">
                            <div class="tabbable">
                                <ul class="nav nav-tabs bg-slate nav-justified">
                                    <li class="active"><a href="#information" data-toggle="tab">Information</a></li>
                                    <li><a href="#ledger" data-toggle="tab">Payment History</a></li>
                                    <li><a href="#penalties" data-toggle="tab">Penalties</a></li>
                                </ul>

                                <div class="tab-content">

                                    <!-- INFORMATION TAB -->
                                    <div class="tab-pane active" id="information">
                                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                                            <div class="panel-heading">
                                                <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Information</h6>
                                            </div>
                                            <div class="panel-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <td>Member</td>
                                                        <td><?= htmlspecialchars($member_name) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Approved Amount</td>
                                                        <td>₱<?= number_format($loan['approved_amount'], 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Term</td>
                                                        <td><?= $loan['term_value'] . ' ' . $loan['term_unit'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Interest Rate</td>
                                                        <td><?= number_format($loan['interest_rate'], 2) ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total Due</td>
                                                        <td>₱<?= number_format($loan['total_due'], 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td>
                                                        <td><?= ucfirst($loan['status']) ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PAYMENT HISTORY TAB -->
                                    <div class="tab-pane" id="ledger">
                                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                                            <div class="panel-heading">
                                                <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Payment History</h6>
                                            </div>
                                            <div class="panel-body">
                                                <table class="table datatable-button-html5-basic table-hover table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Description</th>
                                                            <th>Debit (Due)</th>
                                                            <th>Credit (Paid)</th>
                                                            <th>Balance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Initialize balance
                                                        $balance = round($loan['total_due'], 2);

                                                        // Fetch loan schedule + payments
                                                        $stmt_sched = $db->prepare("
                                                       SELECT s.schedule_id, s.due_date, s.total_due, s.principal_due, s.interest_due, s.penalty_due,
                                                        IFNULL(SUM(p.amount_paid),0) AS paid
                                                       FROM loan_schedule s
    LEFT JOIN loan_payments p ON s.schedule_id = p.schedule_id
    WHERE s.loan_id = ?
    GROUP BY s.schedule_id
    ORDER BY s.due_date ASC
");
                                                        $stmt_sched->bind_param("i", $loan_id);
                                                        $stmt_sched->execute();
                                                        $res_sched = $stmt_sched->get_result();

                                                        while ($row = $res_sched->fetch_assoc()) {
                                                            $row_balance = round($row['total_due'] - $row['paid'], 2);
                                                            $balance -= $row['paid'];
                                                            echo "<tr>
        <td>" . date("M d, Y", strtotime($row['due_date'])) . "</td>
        <td>Principal: ₱" . number_format($row['principal_due'], 2) . " | Interest: ₱" . number_format($row['interest_due'], 2) . "</td>
        <td style='text-align:right'>₱" . number_format($row['total_due'], 2) . "</td>
        <td style='text-align:right'>₱" . number_format($row['paid'], 2) . "</td>
        <td style='text-align:right'><b>₱" . number_format($balance, 2) . "</b></td>
    </tr>";
                                                        }
                                                        $stmt_sched->close();
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PENALTIES TAB -->
                                    <div class="tab-pane" id="penalties">
                                        <div class="panel panel-white border-top-xlg border-top-teal-400">
                                            <div class="panel-heading">
                                                <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Penalties</h6>
                                            </div>
                                            <div class="panel-body">
                                                <?php
                                                $stmt_pen = $db->prepare("
    SELECT due_date, penalty_due, status
    FROM loan_schedule
    WHERE loan_id = ? AND penalty_due > 0
    ORDER BY due_date ASC
");
                                                $stmt_pen->bind_param("i", $loan_id);
                                                $stmt_pen->execute();
                                                $res_pen = $stmt_pen->get_result();

                                                if ($res_pen->num_rows > 0) {
                                                    echo '<table class="table table-bordered"><thead><tr><th>Due Date</th><th>Penalty</th><th>Status</th></tr></thead><tbody>';
                                                    while ($p = $res_pen->fetch_assoc()) {
                                                        echo "<tr>
            <td>" . date("M d, Y", strtotime($p['due_date'])) . "</td>
            <td>₱" . number_format($p['penalty_due'], 2) . "</td>
            <td>" . htmlspecialchars($p['status']) . "</td>
        </tr>";
                                                    }
                                                    echo '</tbody></table>';
                                                } else {
                                                    echo "<p>No penalties recorded yet.</p>";
                                                }
                                                $stmt_pen->close();
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- tab-content -->
                            </div> <!-- tabbable -->
                        </div>
                    </div> <!-- panel -->

                </div> <!-- content -->

                <?php require('includes/footer-text.php'); ?>
                <?php require('includes/footer.php'); ?>
                <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
                <script src="../js/validator.min.js"></script>
                <script>
                    $(document).ready(function() {
                        // Open receipt modal when clicking a receipt number
                        $('.view-receipt').on('click', function() {
                            var receiptNumber = $(this).data('receipt');

                            // Load receipt content via AJAX
                            $('#receipt-content').load('loan_payment_receipt.php?receipt=' + receiptNumber, function() {
                                $('#modal-receipt').modal('show');
                            });
                        });

                        // Print receipt
                        $('#btn-print-receipt').click(function() {
                            var printContents = document.getElementById('receipt-content').innerHTML;
                            var originalContents = document.body.innerHTML;
                            document.body.innerHTML = printContents;
                            window.print();
                            document.body.innerHTML = originalContents;
                            location.reload(); // reload to restore JS events
                        });
                    });
                </script>
</body>

</html>