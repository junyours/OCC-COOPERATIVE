<?php require('../admin/includes/header.php'); ?>

<?php

if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id']) || $_SESSION['is_login_yes'] != 'yes') {
    die("Unauthorized access. Please log in again.");
}

$user_id = (int) $_SESSION['user_id'];



$member = $db->query("
SELECT member_id
FROM tbl_members
WHERE user_id='$user_id'
")->fetch_assoc();

$member_id = $member['member_id'];


$capital = $db->query("
SELECT
COALESCE(SUM(
CASE
WHEN tt.type_name IN ('deposit', 'capital_share')
THEN t.amount
WHEN tt.type_name='withdrawal'
THEN -t.amount
ELSE 0
END
),0) AS capital_balance
FROM accounts a
JOIN account_types at ON at.account_type_id=a.account_type_id
LEFT JOIN transactions t ON t.account_id=a.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id=t.transaction_type_id
WHERE a.member_id='$member_id'
AND at.type_name='capital_share'
")->fetch_assoc();

$capital_balance = $capital['capital_balance'];

$capital_balance = $capital['capital_balance'];

$savings = $db->query("
SELECT
COALESCE(SUM(
CASE
WHEN tt.type_name='deposit'
THEN t.amount
WHEN tt.type_name='withdrawal'
THEN -t.amount
ELSE 0
END
),0) AS savings_balance
FROM accounts a
JOIN account_types at ON at.account_type_id=a.account_type_id
LEFT JOIN transactions t ON t.account_id=a.account_id
LEFT JOIN transaction_types tt ON tt.transaction_type_id=t.transaction_type_id
WHERE a.member_id='$member_id'
AND at.type_name='savings'
")->fetch_assoc();

$savings_balance = $savings['savings_balance'];


$settings = [];
$res = $db->query("
SELECT setting_key, setting_value
FROM system_settings
WHERE setting_key IN ('min_capital_required','min_savings_required')
");

while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$min_capital = $settings['min_capital_required'];
$min_savings = $settings['min_savings_required'];


$eligible = ($capital_balance >= $min_capital) && ($savings_balance >= $min_savings);
?>
<link rel="stylesheet" href="../css/mobile-dashboard.css">

<body class="layout-boxed navbar-top">



    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img style="height: 45px!important" src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
        </div>
        <div class="navbar-collapse collapse">
            <?php require('../admin/includes/sidebar.php'); ?>
        </div>
    </div>


    <div class="page-container">

        <div class="mobile-view">
            <div class="mobile-header">
                Loan Page
            </div>
            <div class="mobile-loan-summary">
                <div class="loan-card" style="background:#26a69a;color:white;">
                    <div>
                        <small>Capital Share</small>
                        <h4>
                            ₱ <?= number_format($capital_balance, 2) ?>
                        </h4>
                    </div>
                    <div>
                        <small>Required</small>
                        <h4>
                            ₱ <?= number_format($min_capital, 2) ?>
                        </h4>
                    </div>
                </div>
            </div>
            <?php if ($eligible) { ?>
                <div class="alert alert-success">
                    Eligible for Loan
                </div>
                <a href="apply_loan.php" class="btn btn-primary btn-block">
                    Apply Loan
                </a>
            <?php } else { ?>
                <div class="alert alert-danger">
                    Not Eligible
                </div>
            <?php } ?>
        </div>




        <div class="page-content desktop-view">

            <div class="content-wrapper">
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4>
                                <i class="icon-user position-left"></i>
                                Loan Status
                            </h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li>
                                <a href="dashboard.php">
                                    <i class="icon-home"></i>Dashboard
                                </a>
                            </li>
                            <li class="active"> Loan Status</li>
                        </ul>
                    </div>
                </div>

                <div class="content">

                    <div class="row">

                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-success-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-coins icon-3x opacity-75"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin">
                                            ₱ <?= number_format($capital_balance, 2) ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini">
                                            Your Capital Share
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-danger-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-wallet icon-3x opacity-75"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin">
                                            ₱ <?= number_format($min_capital, 2) ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini">
                                            Required Capital
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body <?= $eligible ? 'bg-teal-400' : 'bg-warning-400' ?> has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-check icon-3x opacity-75"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin">
                                            <?= $eligible ? 'Eligible' : 'Not Eligible' ?>
                                        </h3>
                                        <span class="text-uppercase text-size-mini">
                                            Loan Status
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title">
                                Apply Loan
                            </h6>
                        </div>

                        <?php if ($eligible) { ?>
                            <div class="alert alert-success">Eligible for Loan</div>
                            <a href="javascript:void(0)" class="btn btn-primary btn-block" data-bs-toggle="modal" data-bs-target="#loanApplicationModal">
                                Apply Loan
                            </a>
                        <?php } else { ?>
                            <div class="alert alert-danger">Not Eligible</div>
                        <?php } ?>
                    </div>

                    <div class="mobile-bottom-nav">
                        <a href="transaction_history.php">
                            <i class="icon-history"></i>
                            Transaction
                        </a>
                        <a href="dashboard.php">
                            <i class="icon-home"></i>
                            Home
                        </a>
                        <a href="loan.php" class="active">
                            <i class="icon-coins"></i>
                            Loans
                        </a>
                        <a href="../admin/profile.php">
                            <i class="icon-user"></i>
                            Profile
                        </a>
                    </div>
                </div>


                <!-- Loan Application Modal -->
                <div class="modal fade" id="loanApplicationModal" tabindex="-1" role="dialog" aria-labelledby="loanApplicationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-teal-400 text-white">
                                <h5 class="modal-title" id="loanApplicationModalLabel">Apply for Loan</h5>
                                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="loanApplicationForm">
                                    <input type="hidden" name="member_id" value="<?= $member_id ?>">

                                    <div class="form-group">
                                        <label>Loan Type</label>
                                        <select name="loan_type_id" class="form-control" required>
                                            <option value="">Select Loan Type</option>
                                            <?php
                                            $loanTypes = $db->query("SELECT loan_type_id, loan_type_name FROM loan_types WHERE status=1");
                                            while ($lt = $loanTypes->fetch_assoc()) {
                                                echo "<option value='{$lt['loan_type_id']}'>{$lt['loan_type_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Loan Amount</label>
                                        <input type="number" name="requested_amount" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Purpose</label>
                                        <textarea name="purpose" class="form-control" required></textarea>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn bg-teal-400 btn-lg">Submit Application</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <?php require('../admin/includes/footer-text.php'); ?>
                <?php require('../admin/includes/footer.php'); ?>


</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script src="../js/validator.min.js"></script>

<script>
    $("#loanApplicationForm").submit(function(e) {
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize() + "&save-loan-application=1";
        $form.find(':input[type="submit"]').prop('disabled', true);

        $.ajax({
            url: "../transaction.php",
            type: "POST",
            data: data,
            success: function(response) {
                response = response.trim();

                if (response == "1") {
                    $.jGrowl('Loan Application Submitted Successfully.', {
                        header: 'Success',
                        theme: 'alert-styled-right bg-success'
                    });
                    $form[0].reset();

                    // Close modal
                    var modalEl = document.getElementById('loanApplicationModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    setTimeout(() => location.reload(), 1500);

                } else if (response == "no_changes") {
                    $.jGrowl('Nothing was changed.', {
                        header: 'Notice',
                        theme: 'alert-styled-right bg-info'
                    });

                } else if (response == "not_eligible") {
                    $.jGrowl('You are not eligible to apply for this loan.', {
                        header: 'Warning',
                        theme: 'alert-styled-right bg-warning'
                    });

                } else {
                    $.jGrowl('Something went wrong. Please try again.', {
                        header: 'Error',
                        theme: 'alert-styled-right bg-danger'
                    });
                }

                $form.find(':input[type="submit"]').prop('disabled', false);
            },
            error: function() {
                $.jGrowl('Server error. Please try again later.', {
                    header: 'Error',
                    theme: 'alert-styled-right bg-danger'
                });
                $form.find(':input[type="submit"]').prop('disabled', false);
            }
        });
    });
</script>