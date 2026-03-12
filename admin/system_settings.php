<?php require('includes/header.php'); ?>
<?php require('db_connect.php'); ?>

<?php

if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || $_SESSION['usertype'] != 1
) {
    die("Unauthorized access.");
}

// Function to get setting
function get_setting($key)
{
    global $db;

    $stmt = $db->prepare("
        SELECT setting_value
        FROM system_settings
        WHERE setting_key = ?
        AND status = 'active'
        LIMIT 1
    ");

    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['setting_value'] ?? null;
}


function is_setting_active($key)
{
    global $db;

    $stmt = $db->prepare("
        SELECT status
        FROM system_settings
        WHERE setting_key = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $key);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return ($result && $result['status'] == 'active');
}

// Handle tab-specific saving
$success_tab = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_general'])) {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $success_tab = 'general';
    } elseif (isset($_POST['save_loan'])) {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $success_tab = 'loan';
    } elseif (isset($_POST['save_savings'])) {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $success_tab = 'savings';
    } elseif (isset($_POST['save_capital'])) {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $success_tab = 'capital';
    }
}


// Get loan types
$loanTypes = $db->query("
    SELECT *
    FROM loan_types
    ORDER BY created_at DESC
");





if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_type_id'])) {
    $id = (int)$_POST['loan_type_id'];
    $name = trim($_POST['loan_type_name']);
    $interest = floatval($_POST['interest_rate']);
    $term_value = (int)$_POST['term_value'];
    $term_unit = trim($_POST['term_unit']); // must be 'days', 'weeks', 'months'
    $frequency = strtolower(trim($_POST['payment_frequency'])); // 'daily','weekly','monthly'
    $comaker = (int)$_POST['require_comaker']; // 0 or 1
    $status = trim($_POST['status']); // 'active' or 'inactive'



    $stmt = $db->prepare("
    UPDATE loan_types
    SET loan_type_name=?, interest_rate=?, term_value=?, term_unit=?, payment_frequency=?, require_comaker=?, status=?
    WHERE loan_type_id=?
");
    $stmt->bind_param("sdissisi", $name, $interest, $term_value, $term_unit, $frequency, $comaker, $status, $id);
    $stmt->execute();

    echo "<script>
        jQuery(function(){
            jQuery.jGrowl('Loan type updated successfully!', { header: 'Success', life: 3000, theme: 'bg-success-400' });
        });
    </script>";
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
   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
        </div>
        <div class="navbar-collapse collapse">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - System Settings</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i>Dashboard</a></li>
                            <li class="active"><i class="icon-cog"></i> System Settings</li>
                        </ul>
                    </div>
                </div>

                <div class="content">

                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title">Cooperative System Configuration</h6>
                        </div>
                        <div class="panel-body">

                            <ul class="nav nav-tabs nav-tabs-solid bg-teal-400">
                                <li class="active"><a href="#general" data-toggle="tab"><i class="icon-cog"></i> General Overview</a></li>
                                <li><a href="#loan" data-toggle="tab"><i class="icon-cash"></i> Loan Settings</a></li>
                                <li><a href="#savings" data-toggle="tab"><i class="icon-piggy-bank"></i> Savings Settings</a></li>
                                <li><a href="#capital" data-toggle="tab"><i class="icon-stack"></i> Capital Share</a></li>
                            </ul>

                            <div class="tab-content">

                                <!-- GENERAL TAB -->
                                <div class="tab-pane active" id="general">
                                    <form method="POST">
                                        <br>
                                        <div class="row">

                                            <!-- LOAN CARD -->
                                            <div class="col-md-4">
                                                <div class="panel panel-flat border-top-success">
                                                    <div class="panel-heading">
                                                        <h6 class="panel-title">
                                                            <i class="icon-cash text-success"></i>
                                                            Loan Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="well well-sm">
                                                            <small>Minimum Membership</small>
                                                            <input type="number" class="form-control" name="settings[min_membership_months]" value="<?= get_setting('min_membership_months') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Minimum Savings Required</small>
                                                            <input type="number" class="form-control" name="settings[min_savings_required]" value="<?= get_setting('min_savings_required') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Minimum Capital Required</small>
                                                            <input type="number" class="form-control" name="settings[min_capital_required]" value="<?= get_setting('min_capital_required') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Require Comaker</small>
                                                            <select class="form-control" name="settings[require_comaker]">
                                                                <option value="1" <?= get_setting('require_comaker') == '1' ? 'selected' : '' ?>>Yes</option>
                                                                <option value="0" <?= get_setting('require_comaker') == '0' ? 'selected' : '' ?>>No</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SAVINGS CARD -->
                                            <div class="col-md-4">
                                                <div class="panel panel-flat border-top-info">
                                                    <div class="panel-heading">
                                                        <h6 class="panel-title">
                                                            <i class="icon-piggy-bank text-info"></i>
                                                            Savings Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="well well-sm">
                                                            <small>Monthly Memebers Savings</small>
                                                            <input type="number" class="form-control" name="settings[savings_min_balance]" value="<?= get_setting('monthly_savings') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Minimum Balance</small>
                                                            <input type="number" class="form-control" name="settings[savings_min_balance]" value="<?= get_setting('savings_min_balance') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Interest Rate (%)</small>
                                                            <input type="number" step="0.01" class="form-control" name="settings[savings_interest_rate]" value="<?= get_setting('savings_interest_rate') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Savings Interest Frequency</small>
                                                            <input type="number" step="0.01" class="form-control" name="settings[savings_interest_frequency]" value="<?= get_setting('savings_interest_frequency') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Withdrawal Limit</small>
                                                            <input type="number" class="form-control" name="settings[savings_withdrawal_limit]" value="<?= get_setting('savings_withdrawal_limit') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="panel panel-flat border-top-warning">
                                                    <div class="panel-heading">
                                                        <h6 class="panel-title">
                                                            <i class="icon-stack text-warning"></i>
                                                            Capital Share Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="well well-sm">
                                                            <small>Monthly Members Shares</small>
                                                            <input type="number" class="form-control" name="settings[capital_min_required]" value="<?= get_setting('monthly_share_capital') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Minimum Capital</small>
                                                            <input type="number" class="form-control" name="settings[capital_min_required]" value="<?= get_setting('capital_min_required') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Maximum Capital</small>
                                                            <input type="number" class="form-control" name="settings[capital_max_limit]" value="<?= get_setting('capital_max_limit') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Capital Share Interest (%)</small>
                                                            <input type="number" class="form-control" name="settings[capital_share_interest]" value="<?= get_setting('capital_share_interest') ?>">
                                                        </div>
                                                        <div class="well well-sm">
                                                            <small>Interest Frequency </small>
                                                            <input type="number" class="form-control" name="settings[capital_max_limit]" value="<?= get_setting('capital_share_interest_frequency') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="text-right">
                                            <button type="submit" name="save_general" class="btn bg-teal-400 btn-lg"><i class="icon-checkmark"></i> Save General</button>
                                        </div>

                                        <
                                            </form>
                                </div>

                                <!-- LOAN TAB -->
                                <div class="tab-pane" id="loan">

                                    <form method="POST">
                                        <div class="row">

                                            <!-- MINIMUM REQUIREMENTS CARD -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card shadow-sm">
                                                    <div class="card-header bg-info text-white">
                                                        Minimum Requirements
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label>Minimum Membership Months</label>
                                                            <input type="number" name="settings[min_membership_months]" class="form-control" value="<?= get_setting('min_membership_months') ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Minimum Savings Required</label>
                                                            <input type="number" name="settings[min_savings_required]" class="form-control" value="<?= get_setting('min_savings_required') ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Minimum Capital Share Balance Required</label>
                                                            <input type="number" name="settings[min_capital_required]" class="form-control" value="<?= get_setting('min_capital_required') ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Require Comaker</label>
                                                            <select name="settings[require_comaker]" class="form-control">
                                                                <option value="1" <?= get_setting('require_comaker') == '1' ? 'selected' : '' ?>>Yes</option>
                                                                <option value="0" <?= get_setting('require_comaker') == '0' ? 'selected' : '' ?>>No</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- LOAN CHARGES CARD -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card shadow-sm">
                                                    <div class="card-header bg-success text-white">
                                                        Loan Charges Configuration
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label>Processing Fee Type</label>
                                                                <select name="settings[loan_processing_fee_type]" class="form-control">
                                                                    <option value="percent" <?= get_setting('loan_processing_fee_type') == 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                                                                    <option value="fixed" <?= get_setting('loan_processing_fee_type') == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₱)</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label>Processing Fee Value</label>
                                                                <input type="number" step="0.01" name="settings[loan_processing_fee_value]" class="form-control" value="<?= get_setting('loan_processing_fee_value') ?>">
                                                            </div>
                                                        </div>

                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label>Penalty Type</label>
                                                                <select name="settings[loan_penalty_type]" class="form-control">
                                                                    <option value="percent" <?= get_setting('loan_penalty_type') == 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                                                                    <option value="fixed" <?= get_setting('loan_penalty_type') == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₱)</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label>Penalty Value</label>
                                                                <input type="number" step="0.01" name="settings[loan_penalty_value]" class="form-control" value="<?= get_setting('loan_penalty_value') ?>">
                                                            </div>
                                                        </div>

                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label>Document Stamp (%)</label>
                                                                <input type="number" step="0.01" name="settings[loan_doc_stamp_fee]" class="form-control" value="<?= get_setting('loan_doc_stamp_fee') ?>">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label>Insurance (%)</label>
                                                                <input type="number" step="0.01" name="settings[loan_insurance_fee]" class="form-control" value="<?= get_setting('loan_insurance_fee') ?>">
                                                            </div>
                                                        </div>

                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label>Penalty Frequency</label>
                                                                <select name="settings[loan_penalty_frequency]" class="form-control">
                                                                    <option value="daily" <?= get_setting('loan_penalty_frequency') == 'daily' ? 'selected' : '' ?>>Daily</option>
                                                                    <option value="weekly" <?= get_setting('loan_penalty_frequency') == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                                                    <option value="monthly" <?= get_setting('loan_penalty_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label>Grace Period (Days)</label>
                                                                <input type="number" name="settings[loan_grace_period_days]" class="form-control" value="<?= get_setting('loan_grace_period_days') ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- SAVE BUTTON -->
                                        <div class="text-right mb-4">
                                            <button type="submit" name="save_loan" class="btn btn-success btn-lg">
                                                <i class="icon-checkmark"></i> Save Loan Settings
                                            </button>
                                        </div>
                                    </form>

                                    <hr>

                                    <!-- LOAN TYPES TABLE -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5>Loan Types</h5>
                                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addLoanModal">
                                            <i class="icon-plus-circle2"></i> Add Loan Type
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Interest</th>
                                                    <th>Term</th>
                                                    <th>Payment</th>
                                                    <th>Comaker</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($loanTypes->num_rows > 0): ?>
                                                    <?php while ($row = $loanTypes->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($row['loan_type_name']) ?></td>
                                                            <td class="text-center"><?= (intval($row['interest_rate']) == $row['interest_rate']) ? intval($row['interest_rate']) : $row['interest_rate'] ?>%</td>
                                                            <td class="text-center"><?= $row['term_value'] ?> <?= ucfirst($row['term_unit']) ?></td>
                                                            <td class="text-center"><?= ucfirst($row['payment_frequency']) ?></td>
                                                            <td class="text-center"><?= $row['require_comaker'] ? 'YES' : 'NO' ?></td>
                                                            <td class="text-center"><?= ucfirst($row['status']) ?></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-primary btn-xs editLoan"
                                                                    data-id="<?= $row['loan_type_id'] ?>"
                                                                    data-name="<?= htmlspecialchars($row['loan_type_name']) ?>"
                                                                    data-interest="<?= $row['interest_rate'] ?>"
                                                                    data-termvalue="<?= $row['term_value'] ?>"
                                                                    data-termunit="<?= $row['term_unit'] ?>"
                                                                    data-frequency="<?= $row['payment_frequency'] ?>"
                                                                    data-comaker="<?= $row['require_comaker'] ?>"
                                                                    data-status="<?= $row['status'] ?>">Update</button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No loan types found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                                <div class="tab-pane fade" id="savings">

                                    <form method="POST">

                                        <div class="row justify-content-center">

                                            <div class="col-md-7">

                                                <div class="card shadow-lg border-0">

                                                    <div class="card-header bg-info text-white">
                                                        <h5 class="mb-0">
                                                            <i class="icon-piggy-bank mr-2"></i>
                                                            Savings Settings
                                                        </h5>
                                                    </div>

                                                    <div class="card-body">

                                                        <!-- Monthly Savings -->
                                                        <div class="form-group">

                                                            <label class="font-weight-bold text-info">
                                                                Monthly Memebers Savings
                                                            </label>

                                                            <div class="input-group input-group-lg">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text bg-info text-white">₱</span>
                                                                </div>
                                                                <input type="number"
                                                                    name="settings[monthly_savings]"
                                                                    class="form-control font-weight-bold text-info border-info"
                                                                    value="<?= get_setting('monthly_savings') ?>">
                                                            </div>

                                                            <small class="text-muted">
                                                                Required monthly savings deducted from members
                                                            </small>

                                                        </div>

                                                        <hr>

                                                        <div class="form-row">

                                                            <!-- Minimum Balance -->
                                                            <div class="form-group col-md-6">

                                                                <label class="font-weight-semibold">
                                                                    Minimum Balance
                                                                </label>

                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">₱</span>
                                                                    </div>
                                                                    <input type="number"
                                                                        name="settings[savings_min_balance]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('savings_min_balance') ?>">
                                                                </div>

                                                            </div>


                                                            <!-- Interest Rate -->
                                                            <div class="form-group col-md-6">

                                                                <label class="font-weight-semibold">
                                                                    Interest Rate
                                                                </label>

                                                                <div class="input-group">
                                                                    <input type="number"
                                                                        step="0.01"
                                                                        name="settings[savings_interest_rate]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('savings_interest_rate') ?>">
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>


                                                        <!-- Daily Withdrawal Limit -->
                                                        <div class="form-group">

                                                            <label class="font-weight-semibold">
                                                                Daily Withdrawal Limit
                                                            </label>

                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">₱</span>
                                                                </div>
                                                                <input type="number"
                                                                    name="settings[savings_withdrawal_limit]"
                                                                    class="form-control"
                                                                    value="<?= get_setting('savings_withdrawal_limit') ?>">
                                                            </div>

                                                        </div>


                                                        <div class="alert alert-info mt-4">
                                                            <i class="icon-info22 mr-2"></i>
                                                            This savings contribution builds member financial security.
                                                        </div>

                                                    </div>


                                                    <div class="card-footer text-right bg-light">

                                                        <button type="submit"
                                                            name="save_savings"
                                                            class="btn btn-success btn-lg shadow">

                                                            <i class="icon-checkmark mr-2"></i>
                                                            Save Savings Settings

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </form>

                                </div>

                                <!-- CAPITAL SHARE TAB -->
                                <div class="tab-pane fade" id="capital">
                                    <form method="POST">
                                        <div class="row justify-content-center">
                                            <div class="col-md-7">
                                                <div class="card shadow-lg border-0">
                                                    <div class="card-header bg-warning text-white">
                                                        <h5 class="mb-0">
                                                            <i class="icon-coins mr-2"></i>
                                                            Capital Share Settings
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <!-- Monthly Share Capital -->
                                                        <div class="form-group">
                                                            <label class="font-weight-bold text-warning">
                                                                Monthly Share Capital
                                                            </label>
                                                            <div class="input-group input-group-lg">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text bg-warning text-white">₱</span>
                                                                </div>
                                                                <input type="number"
                                                                    name="settings[monthly_share_capital]"
                                                                    class="form-control font-weight-bold text-warning border-warning"
                                                                    value="<?= get_setting('monthly_share_capital') ?>">
                                                            </div>
                                                            <small class="text-muted">
                                                                Ownership contribution of cooperative member
                                                            </small>
                                                        </div>


                                                        <hr>
                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label style="color:#333 !important; font-weight:600;">
                                                                    Minimum Capital Required
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">₱</span>
                                                                    </div>
                                                                    <input type="number"
                                                                        name="settings[capital_min_required]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('capital_min_required') ?>">
                                                                </div>
                                                            </div>


                                                            <div class="form-group col-md-6">
                                                                <label class="font-weight-bold mb-2" style="color:#333 !important; font-weight:600;">
                                                                    Maximum Capital Limit
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">₱</span>
                                                                    </div>
                                                                    <input type="number"
                                                                        name="settings[capital_max_limit]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('capital_max_limit') ?>">
                                                                </div>
                                                            </div>

                                                            <div class="form-group col-md-6">
                                                                <label class="font-weight-bold mb-2" style="color:#333 !important; font-weight:600;">
                                                                    Capitl Share Interest (%)
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                    </div>
                                                                    <input type="number"
                                                                        name="settings[capital_share_interest]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('capital_share_interest') ?>">
                                                                </div>
                                                            </div>

                                                            <div class="form-group col-md-6">
                                                                <label class="font-weight-bold mb-2" style="color:#333 !important; font-weight:600;">
                                                                    Capitl Share Interest Frequency
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                    </div>
                                                                    <input type="number"
                                                                        name="settings[capital_share_interest_frequency]"
                                                                        class="form-control"
                                                                        value="<?= get_setting('capital_share_interest_frequency') ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer text-right bg-light">
                                                        <button type="submit"
                                                            name="save_capital"
                                                            class="btn btn-success btn-lg shadow">
                                                            <i class="icon-checkmark mr-2"></i>
                                                            Save Capital Settings
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>




                            </div>
                        </div>
                    </div>

                </div> <!-- content -->
            </div> <!-- content-wrapper -->
        </div> <!-- page-content -->
    </div> <!-- page-container -->

    <!-- Edit Loan Modal -->
    <div id="editLoanModal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-teal-400">
                    <h5 class="modal-title">Edit Loan Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editLoanForm" class="form-horizontal" data-toggle="validator" role="form">
                        <input type="hidden" name="loan_type_id" id="edit_id">
                        <div class="form-body" style="padding-top:20px">
                            <div id="edit-msg"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Loan Name</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input type="text" class="form-control" name="loan_type_name" id="edit_name" placeholder="Enter Loan Name" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Interest (%)</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-percent"></i></span>
                                        <input type="number" step="0.01" class="form-control" name="interest_rate" id="edit_interest" placeholder="Enter Interest Rate" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Term Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Term Value</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-calendar"></i></span>
                                        <input type="number" class="form-control" name="term_value" id="edit_termvalue" placeholder="Enter Term Value" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Term Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Term Unit</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-clock"></i></span>
                                        <select class="form-control" name="term_unit" id="edit_termunit" required>
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Payment Frequency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Payment Frequency</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-repeat"></i></span>
                                        <select class="form-control" name="payment_frequency" id="edit_frequency" required>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Require Comaker -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Require Comaker</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-users"></i></span>
                                        <select class="form-control" name="require_comaker" id="edit_comaker" required>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Status</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-check"></i></span>
                                        <select class="form-control" name="status" id="edit_status" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-floppy-disk"></i></b> Save Changes</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Loan Modal -->
    <div id="addLoanModal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-teal-400">
                    <h5 class="modal-title">Add Loan Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addLoanForm" class="form-horizontal" data-toggle="validator" role="form">
                        <div class="form-body" style="padding-top:20px">
                            <div id="add-msg"></div>

                            <!-- Loan Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Loan Name</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input type="text" class="form-control" name="loan_type_name" placeholder="Enter Loan Name" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Interest Rate -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Interest (%)</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-percent"></i></span>
                                        <input type="number" step="0.01" class="form-control" name="interest_rate" placeholder="Enter Interest Rate" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Term Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Term Value</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-calendar"></i></span>
                                        <input type="number" class="form-control" name="term_value" placeholder="Enter Term Value" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Term Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Term Unit</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-clock"></i></span>
                                        <select class="form-control" name="term_unit" required>
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Payment Frequency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Payment Frequency</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-repeat"></i></span>
                                        <select class="form-control" name="payment_frequency" required>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Require Comaker -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Require Comaker</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-users"></i></span>
                                        <select class="form-control" name="require_comaker">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Status</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-check"></i></span>
                                        <select class="form-control" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Add Loan Type</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>

    <?php require('includes/footer-text.php'); ?>
    <script src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="../js/validator.min.js"></script>

    <script>
        $(document).ready(function() {


            $('.editLoan').click(function() {
                $('#edit_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_interest').val($(this).data('interest'));
                $('#edit_termvalue').val($(this).data('termvalue'));
                $('#edit_termunit').val($(this).data('termunit'));
                $('#edit_frequency').val($(this).data('frequency'));
                $('#edit_comaker').val($(this).data('comaker'));
                $('#edit_status').val($(this).data('status'));
                $('#editLoanModal').modal('show');
            });


            $('#editLoanForm').validator().on('submit', function(e) {
                if (!e.isDefaultPrevented()) {
                    e.preventDefault();

                    $.post('<?= $_SERVER['PHP_SELF'] ?>', $(this).serialize(), function(response) {
                        jQuery.jGrowl('Loan type updated successfully!', {
                            header: 'Success',
                            life: 3000,
                            theme: 'bg-success-400'
                        });
                        $('#editLoanModal').modal('hide');

                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    });
                } else {
                    jQuery.jGrowl('Please fill in all required fields correctly.', {
                        header: 'Error',
                        life: 3000,
                        theme: 'bg-danger-400'
                    });
                    return false;
                }
            });


            <?php if ($success_tab): ?>
                jQuery.jGrowl('<?= ucfirst($success_tab) ?> settings saved successfully!', {
                    header: 'Success',
                    life: 3000,
                    theme: 'bg-success-400'
                });
            <?php endif; ?>
        });

        $('#addLoanForm').validator().on('submit', function(e) {

            if (!e.isDefaultPrevented()) {

                e.preventDefault();

                $.post('../transaction.php', $(this).serialize() + '&add_loan_type=1', function(response) {

                    response = response.trim();

                    if (response === "success") {

                        jQuery.jGrowl('Loan type added successfully!', {
                            header: 'Success',
                            life: 3000,
                            theme: 'bg-success-400'
                        });

                        $('#addLoanModal').modal('hide');
                        $('#addLoanForm')[0].reset();

                        setTimeout(function() {
                            location.reload(); // reload table after 1s
                        }, 1000);

                    } else {
                        jQuery.jGrowl('Failed to add loan type! ' + response, {
                            header: 'Error',
                            life: 3000,
                            theme: 'bg-danger-400'
                        });
                    }

                });

            } else {
                jQuery.jGrowl('Please fill in all required fields!', {
                    header: 'Validation Error',
                    life: 3000,
                    theme: 'bg-danger-400'
                });
            }

        });

        $(document).ready(function() {


            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {

                var activeTab = $(e.target).attr('href');

                localStorage.setItem('activeSettingsTab', activeTab);

            });
            var activeTab = localStorage.getItem('activeSettingsTab');
            if (activeTab) {
                $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
            }
        });
    </script>