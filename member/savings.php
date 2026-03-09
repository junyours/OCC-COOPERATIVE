<?php
require('../admin/includes/header.php');

if (!isset($_SESSION['is_login_yes']) || $_SESSION['is_login_yes'] != 'yes') {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Get member_id
$stmt = $db->prepare("SELECT member_id FROM tbl_members WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($member_id);
$stmt->fetch();
$stmt->close();

// Get active savings account
$stmt = $db->prepare("
    SELECT a.account_id
    FROM accounts a
    JOIN account_types at ON a.account_type_id = at.account_type_id
    WHERE a.member_id = ?
      AND at.type_name = 'savings'
      AND a.status = 'active'
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$stmt->bind_result($account_id);
$stmt->fetch();
$stmt->close();

if (!$account_id) {
    die("No active savings account found.");
}

// Compute savings total
$savings_total = $db->query("
    SELECT IFNULL(SUM(t.amount),0) AS total
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'savings'
")->fetch_assoc()['total'] ?? 0;

// Get withdraw limit
$withdraw_limit = 0;
$stmt = $db->prepare("
    SELECT setting_value 
    FROM system_settings 
    WHERE setting_key='savings_withdrawal_limit' 
      AND status='active' 
    LIMIT 1
");
$stmt->execute();
$stmt->bind_result($withdraw_limit);
$stmt->fetch();
$stmt->close();

$withdraw_limit = (float)$withdraw_limit;

// Compute withdrawable amount
$max_withdrawable = $savings_total - $withdraw_limit;
$can_withdraw = $max_withdrawable > 0;



// Fetch withdrawal history
$history = [];
$stmt = $db->prepare("
    SELECT request_id, amount, reason, status, approved_by, 
           date_requested, date_approved, reference_no, remarks
    FROM savings_withdrawal_requests
    WHERE account_id = ?
    ORDER BY date_requested DESC
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
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
    }
</style>

<div class="layout-boxed navbar-top">
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/main_logo.jpg" alt="">
                <span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span>
            </a>
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

                <!-- Page Header -->
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-cash3 position-left"></i> Savings</h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li class="active">Savings</li>
                        </ul>

                        <ul class="breadcrumb-elements">
                            <li>
                                <button type="button" class="btn btn-link" data-toggle="modal" data-target="#modal_request_withdrawal">
                                    <i class="icon-coins text-blue-400 position-left"></i>
                                    Request Withdrawal
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="content">
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                <i class="icon-list text-teal-400 position-left"></i>
                                Withdrawal Requests History
                            </h6>
                        </div>
                        <div class="panel-body panel-theme table-responsive">
                            <table class="table table-hover table-bordered" id="withdrawal-history-table">
                                <thead>
                                    <tr style="border-bottom: 4px solid #ddd; background: #eee">
                                        <th>Date Requested</th>
                                        <th>Amount (₱)</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Date Approved</th>
                                        <th>Reference No</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($history)) : ?>
                                        <?php foreach ($history as $row) : ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                                                <td align="right">₱<?= number_format($row['amount'], 2) ?></td>
                                                <td><?= htmlspecialchars($row['reason']) ?></td>
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
                                                <td><?= $row['date_approved'] ? date('M d, Y', strtotime($row['date_approved'])) : '-' ?></td>
                                                <td><?= htmlspecialchars($row['reference_no'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No withdrawal requests yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <div id="modal_request_withdrawal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="withdrawalForm" method="POST" action="javascript:void(0);">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h5 class="modal-title">Request Savings Withdrawal</h5>
                    </div>
                    <div class="modal-bodys">
                        <p><strong>Current Balance:</strong> ₱<?= number_format($savings_total, 2) ?></p>
                        <p><strong>Minimum Required:</strong> ₱<?= number_format($withdraw_limit, 2) ?></p>

                        <input type="hidden" name="action_type" value="request_withdrawal">
                        <input type="hidden" name="account_id" value="<?= $account_id ?>">

                        <div class="form-group">
                            <label>Amount (₱)</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required
                                <?= $can_withdraw ? "max='$max_withdrawable'" : "disabled" ?>>
                            <?php if ($can_withdraw): ?>
                                <small class="text-muted">Maximum withdrawable: ₱<?= number_format($max_withdrawable, 2) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" required <?= $can_withdraw ? "" : "disabled" ?>></textarea>
                        </div>

                        <?php if (!$can_withdraw): ?>
                            <div class="alert alert-warning">
                                Withdrawal not allowed due to maintaining balance requirement.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" <?= $can_withdraw ? "" : "disabled" ?>>Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require('../admin/includes/footer-text.php'); ?>
    <?php require('../admin/includes/footer.php'); ?>

    <!-- JS Libraries -->
<!-- 
    <script src="../assets/js/plugins/tables/datatables/datatables.min.js"></script> -->
    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/validator.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log("Withdrawal JS Loaded");

            // Initialize DataTable
            // if ($.fn.DataTable) {
            //     $('#withdrawal-history-table').DataTable({
            //         paging: true,
            //         ordering: true,
            //         info: true,
            //         searching: true,
            //         order: [
            //             [0, "desc"]
            //         ]
            //     });
            // }

            // AJAX form submit
            $('#withdrawalForm').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $submitBtn = $form.find("button[type='submit']");
                if ($submitBtn.prop("disabled")) return;

                var formData = $form.serialize();
                $submitBtn.prop("disabled", true).html("Processing...");

                $.ajax({
                    url: "../transaction.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.status === "success") {
                            $.jGrowl("Withdrawal request submitted!", {
                                theme: "alert-styled-right bg-success"
                            });
                            $('#modal_request_withdrawal').modal('hide');
                            $form[0].reset();
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $.jGrowl(response.message, {
                                theme: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        $.jGrowl("Server error", {
                            theme: "error"
                        });
                    },
                    complete: function() {
                        $submitBtn.prop("disabled", false).html("Submit Request");
                    }
                });
            });
        });
    </script>