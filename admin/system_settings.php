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
    
    .panel {
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .panel-body {
        padding: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .panel-white {
        background: white;
        border: 1px solid #ddd;
    }
    
    .border-top-xlg {
        border-top-width: 4px !important;
    }
    
    .border-top-teal-400 {
        border-top-color: #26a69a !important;
    }
    
    .text-teal-400 {
        color: #26a69a !important;
    }
    
    .calculation-box {
        background: #f8f9fa;
        border-left: 4px solid #26a69a;
        padding: 15px;
        margin: 10px 0;
        border-radius: 0 4px 4px 0;
    }
    
    .calculation-title {
        font-weight: bold;
        color: #26a69a;
        margin-bottom: 8px;
    }
    
    .calculation-formula {
        font-family: 'Courier New', monospace;
        background: #fff;
        padding: 8px;
        border-radius: 3px;
        margin: 5px 0;
        font-size: 0.9em;
    }
    
    .calculation-steps {
        margin: 8px 0;
        padding-left: 20px;
    }
    
    .calculation-steps li {
        margin: 3px 0;
        font-size: 0.9em;
    }
    
    .insight-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #2196f3;
        padding: 15px;
        margin: 15px 0;
        border-radius: 0 4px 4px 0;
    }
    
    .insight-title {
        font-weight: bold;
        color: #1976d2;
        margin-bottom: 8px;
    }
    
    .settings-card {
        background: white;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }
    
    .settings-card .card-header {
        border-radius: 4px 4px 0 0;
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .settings-card .card-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        border-radius: 3px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: #26a69a;
        box-shadow: 0 0 0 0.2rem rgba(38, 166, 154, 0.25);
    }
    
    .btn {
        border-radius: 3px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }
    
    .btn-teal-400 {
        background: #26a69a;
        border-color: #26a69a;
        color: white;
    }
    
    .btn-teal-400:hover {
        background: #26a69a;
        border-color: #26a69a;
        color: white;
        opacity: 0.9;
    }
    
    .input-group-addon {
        background: #f8f9fa;
        border: 1px solid #ddd;
        color: #495057;
    }
    
    .nav-tabs {
        border-bottom: 2px solid #26a69a;
    }
    
    .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
        background: #26a69a;
        border-color: #26a69a;
        color: white;
    }
    
    .nav-tabs > li > a:hover {
        border-color: #26a69a #26a69a #ddd;
        background: rgba(38, 166, 154, 0.1);
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
                                        <div class="row">
                                            <!-- LOAN CONFIGURATION CARD -->
                                            <div class="col-lg-4">
                                                <div class="settings-card border-top-xlg border-top-success">
                                                    <div class="card-header bg-success text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-cash mr-2"></i>
                                                            Loan Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Minimum Membership (Months)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icon-calendar2"></i></span>
                                                                <input type="number" class="form-control" name="settings[min_membership_months]" value="<?= get_setting('min_membership_months') ?>" placeholder="Enter months">
                                                            </div>
                                                            <small class="text-muted">Required membership duration for loan eligibility</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Minimum Savings Required</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[min_savings_required]" value="<?= get_setting('min_savings_required') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Minimum savings balance to qualify</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Minimum Capital Required</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[min_capital_required]" value="<?= get_setting('min_capital_required') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Capital share requirement for loans</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Require Comaker</label>
                                                            <select class="form-control" name="settings[require_comaker]">
                                                                <option value="1" <?= get_setting('require_comaker') == '1' ? 'selected' : '' ?>>Yes - Required</option>
                                                                <option value="0" <?= get_setting('require_comaker') == '0' ? 'selected' : '' ?>>No - Optional</option>
                                                            </select>
                                                            <small class="text-muted">Whether loan applications require a comaker</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SAVINGS CONFIGURATION CARD -->
                                            <div class="col-lg-4">
                                                <div class="settings-card border-top-xlg border-top-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-piggy-bank mr-2"></i>
                                                            Savings Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Monthly Members Savings</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[monthly_savings]" value="<?= get_setting('monthly_savings') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Required monthly savings contribution</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Minimum Balance</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[savings_min_balance]" value="<?= get_setting('savings_min_balance') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Minimum account balance to maintain</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Interest Rate (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" class="form-control" name="settings[savings_interest_rate]" value="<?= get_setting('savings_interest_rate') ?>" placeholder="Enter rate">
                                                                <span class="input-group-addon">%</span>
                                                            </div>
                                                            <small class="text-muted">Annual interest rate on savings</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Interest Frequency</label>
                                                            <select class="form-control" name="settings[savings_interest_frequency]">
                                                                <option value="monthly" <?= get_setting('savings_interest_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                                <option value="quarterly" <?= get_setting('savings_interest_frequency') == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                                                <option value="annually" <?= get_setting('savings_interest_frequency') == 'annually' ? 'selected' : '' ?>>Annually</option>
                                                            </select>
                                                            <small class="text-muted">How often interest is calculated</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Withdrawal Limit</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[savings_withdrawal_limit]" value="<?= get_setting('savings_withdrawal_limit') ?>" placeholder="Enter limit">
                                                            </div>
                                                            <small class="text-muted">Maximum daily withdrawal amount</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- CAPITAL SHARE CONFIGURATION CARD -->
                                            <div class="col-lg-4">
                                                <div class="settings-card border-top-xlg border-top-warning">
                                                    <div class="card-header bg-warning text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-stack mr-2"></i>
                                                            Capital Share Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Monthly Members Shares</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[monthly_share_capital]" value="<?= get_setting('monthly_share_capital') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Required monthly capital contribution</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Minimum Capital Required</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[capital_min_required]" value="<?= get_setting('capital_min_required') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Minimum capital share balance</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Maximum Capital Limit</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" class="form-control" name="settings[capital_max_limit]" value="<?= get_setting('capital_max_limit') ?>" placeholder="Enter limit">
                                                            </div>
                                                            <small class="text-muted">Maximum allowed capital contribution</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Capital Share Interest (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" class="form-control" name="settings[capital_share_interest]" value="<?= get_setting('capital_share_interest') ?>" placeholder="Enter rate">
                                                                <span class="input-group-addon">%</span>
                                                            </div>
                                                            <small class="text-muted">Annual return on capital shares</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Interest Frequency</label>
                                                            <select class="form-control" name="settings[capital_share_interest_frequency]">
                                                                <option value="monthly" <?= get_setting('capital_share_interest_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                                <option value="quarterly" <?= get_setting('capital_share_interest_frequency') == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                                                <option value="annually" <?= get_setting('capital_share_interest_frequency') == 'annually' ? 'selected' : '' ?>>Annually</option>
                                                            </select>
                                                            <small class="text-muted">Distribution frequency for returns</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="calculation-box">
                                                    <div class="calculation-title">General Settings Overview</div>
                                                    <div class="calculation-formula">System Configuration Impact Analysis</div>
                                                    <ul class="calculation-steps">
                                                        <li><strong>Loan Requirements:</strong> Members need <?= get_setting('min_membership_months') ?> months membership, ₱<?= number_format(get_setting('min_savings_required') ?: 0, 2) ?> savings, and ₱<?= number_format(get_setting('min_capital_required') ?: 0, 2) ?> capital</li>
                                                        <li><strong>Savings Program:</strong> Monthly contribution of ₱<?= number_format(get_setting('monthly_savings') ?: 0, 2) ?> with <?= get_setting('savings_interest_rate') ?: 0 ?>% interest</li>
                                                        <li><strong>Capital Structure:</strong> Monthly share of ₱<?= number_format(get_setting('monthly_share_capital') ?: 0, 2) ?> with <?= get_setting('capital_share_interest') ?: 0 ?>% returns</li>
                                                        <li><strong>Risk Management:</strong> Comaker requirement is <strong><?= get_setting('require_comaker') == '1' ? 'ENABLED' : 'DISABLED' ?></strong></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right mt-4">
                                            <button type="submit" name="save_general" class="btn btn-teal-400 btn-lg">
                                                <i class="icon-checkmark mr-2"></i> Save General Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- LOAN TAB -->
                                <div class="tab-pane" id="loan">
                                    <form method="POST">
                                        <div class="row">
                                            <!-- MINIMUM REQUIREMENTS CARD -->
                                            <div class="col-lg-6">
                                                <div class="settings-card border-top-xlg border-top-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-shield-check mr-2"></i>
                                                            Loan Eligibility Requirements
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Minimum Membership (Months)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icon-calendar2"></i></span>
                                                                <input type="number" name="settings[min_membership_months]" class="form-control" value="<?= get_setting('min_membership_months') ?>" placeholder="Enter months">
                                                            </div>
                                                            <small class="text-muted">Required membership duration for loan eligibility</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Minimum Savings Required</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" name="settings[min_savings_required]" class="form-control" value="<?= get_setting('min_savings_required') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Minimum savings balance to qualify for loans</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Minimum Capital Share Required</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon">₱</span>
                                                                <input type="number" name="settings[min_capital_required]" class="form-control" value="<?= get_setting('min_capital_required') ?>" placeholder="Enter amount">
                                                            </div>
                                                            <small class="text-muted">Capital share requirement for loan approval</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-info">Require Comaker</label>
                                                            <select name="settings[require_comaker]" class="form-control">
                                                                <option value="1" <?= get_setting('require_comaker') == '1' ? 'selected' : '' ?>>Yes - Required</option>
                                                                <option value="0" <?= get_setting('require_comaker') == '0' ? 'selected' : '' ?>>No - Optional</option>
                                                            </select>
                                                            <small class="text-muted">Whether loan applications require a guarantor</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- LOAN CHARGES CARD -->
                                            <div class="col-lg-6">
                                                <div class="settings-card border-top-xlg border-top-success">
                                                    <div class="card-header bg-success text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-calculator mr-2"></i>
                                                            Loan Charges & Fees
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Processing Fee Type</label>
                                                            <select name="settings[loan_processing_fee_type]" class="form-control">
                                                                <option value="percent" <?= get_setting('loan_processing_fee_type') == 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                                                                <option value="fixed" <?= get_setting('loan_processing_fee_type') == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₱)</option>
                                                            </select>
                                                            <small class="text-muted">How processing fee is calculated</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Processing Fee Value</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><?= get_setting('loan_processing_fee_type') == 'percent' ? '%' : '₱' ?></span>
                                                                <input type="number" step="0.01" name="settings[loan_processing_fee_value]" class="form-control" value="<?= get_setting('loan_processing_fee_value') ?>" placeholder="Enter value">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Penalty Type</label>
                                                            <select name="settings[loan_penalty_type]" class="form-control">
                                                                <option value="percent" <?= get_setting('loan_penalty_type') == 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                                                                <option value="fixed" <?= get_setting('loan_penalty_type') == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₱)</option>
                                                            </select>
                                                            <small class="text-muted">How late payment penalties are calculated</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-success">Penalty Value</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><?= get_setting('loan_penalty_type') == 'percent' ? '%' : '₱' ?></span>
                                                                <input type="number" step="0.01" name="settings[loan_penalty_value]" class="form-control" value="<?= get_setting('loan_penalty_value') ?>" placeholder="Enter value">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-4">
                                            <!-- ADDITIONAL FEES CARD -->
                                            <div class="col-lg-6">
                                                <div class="settings-card border-top-xlg border-top-warning">
                                                    <div class="card-header bg-warning text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-file-text mr-2"></i>
                                                            Additional Fees & Terms
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Document Stamp Duty (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" name="settings[loan_doc_stamp_fee]" class="form-control" value="<?= get_setting('loan_doc_stamp_fee') ?>" placeholder="Enter percentage">
                                                                <span class="input-group-addon">%</span>
                                                            </div>
                                                            <small class="text-muted">Government documentary stamp tax</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Insurance Premium (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" name="settings[loan_insurance_fee]" class="form-control" value="<?= get_setting('loan_insurance_fee') ?>" placeholder="Enter percentage">
                                                                <span class="input-group-addon">%</span>
                                                            </div>
                                                            <small class="text-muted">Loan insurance coverage premium</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Penalty Frequency</label>
                                                            <select name="settings[loan_penalty_frequency]" class="form-control">
                                                                <option value="daily" <?= get_setting('loan_penalty_frequency') == 'daily' ? 'selected' : '' ?>>Daily</option>
                                                                <option value="weekly" <?= get_setting('loan_penalty_frequency') == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                                                <option value="monthly" <?= get_setting('loan_penalty_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                            </select>
                                                            <small class="text-muted">How often penalties are applied</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold text-warning">Grace Period (Days)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icon-clock"></i></span>
                                                                <input type="number" name="settings[loan_grace_period_days]" class="form-control" value="<?= get_setting('loan_grace_period_days') ?>" placeholder="Enter days">
                                                            </div>
                                                            <small class="text-muted">Days before penalties start accruing</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- LOAN SUMMARY CALCULATION -->
                                            <div class="col-lg-6">
                                                <div class="calculation-box">
                                                    <div class="calculation-title">Loan Configuration Summary</div>
                                                    <div class="calculation-formula">Total Loan Cost = Principal + Processing Fee + Doc Stamp + Insurance</div>
                                                    <ul class="calculation-steps">
                                                        <li><strong>Eligibility Requirements:</strong> <?= get_setting('min_membership_months') ?: 0 ?> months membership, ₱<?= number_format(get_setting('min_savings_required') ?: 0, 2) ?> savings, ₱<?= number_format(get_setting('min_capital_required') ?: 0, 2) ?> capital</li>
                                                        <li><strong>Processing Fee:</strong> <?= get_setting('loan_processing_fee_type') == 'percent' ? get_setting('loan_processing_fee_value') . '%' : '₱' . number_format(get_setting('loan_processing_fee_value') ?: 0, 2) ?></li>
                                                        <li><strong>Penalty:</strong> <?= get_setting('loan_penalty_type') == 'percent' ? get_setting('loan_penalty_value') . '%' : '₱' . number_format(get_setting('loan_penalty_value') ?: 0, 2) ?> applied <?= get_setting('loan_penalty_frequency') ?: 'monthly' ?></li>
                                                        <li><strong>Government Fees:</strong> <?= get_setting('loan_doc_stamp_fee') ?: 0 ?>% doc stamp + <?= get_setting('loan_insurance_fee') ?: 0 ?>% insurance</li>
                                                        <li><strong>Grace Period:</strong> <?= get_setting('loan_grace_period_days') ?: 0 ?> days before penalties apply</li>
                                                        <li><strong>Risk Management:</strong> Comaker requirement is <strong><?= get_setting('require_comaker') == '1' ? 'ENABLED' : 'DISABLED' ?></strong></li>
                                                    </ul>
                                                </div>
                                                
                                                <div class="insight-box">
                                                    <div class="insight-title">Loan Policy Impact</div>
                                                    <ul>
                                                        <li><strong>Member Access:</strong> Current requirements ensure members have established financial commitment</li>
                                                        <li><strong>Risk Mitigation:</strong> <?= get_setting('require_comaker') == '1' ? 'Comaker requirement reduces default risk' : 'No comaker requirement increases accessibility' ?></li>
                                                        <li><strong>Cost Structure:</strong> Processing and government fees cover administrative costs</li>
                                                        <li><strong>Penalty System:</strong> <?= get_setting('loan_grace_period_days') ?: 0 ?>-day grace period provides flexibility</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- SAVE BUTTON -->
                                        <div class="text-right mt-4">
                                            <button type="submit" name="save_loan" class="btn btn-teal-400 btn-lg">
                                                <i class="icon-checkmark mr-2"></i> Save Loan Settings
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
                                        <div class="row">
                                            <!-- SAVINGS CONFIGURATION CARD -->
                                            <div class="col-lg-8">
                                                <div class="settings-card border-top-xlg border-top-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-piggy-bank mr-2"></i>
                                                            Savings Account Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <!-- Monthly Savings -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-info">Monthly Members Savings</label>
                                                                    <div class="input-group input-group-lg">
                                                                        <span class="input-group-addon bg-info text-white">₱</span>
                                                                        <input type="number" name="settings[monthly_savings]" class="form-control font-weight-bold text-info border-info" value="<?= get_setting('monthly_savings') ?>" placeholder="Enter amount">
                                                                    </div>
                                                                    <small class="text-muted">Required monthly savings contribution from members</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Minimum Balance -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-info">Minimum Balance</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon">₱</span>
                                                                        <input type="number" name="settings[savings_min_balance]" class="form-control" value="<?= get_setting('savings_min_balance') ?>" placeholder="Enter amount">
                                                                    </div>
                                                                    <small class="text-muted">Minimum account balance to maintain</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <!-- Interest Rate -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-info">Interest Rate (%)</label>
                                                                    <div class="input-group">
                                                                        <input type="number" step="0.01" name="settings[savings_interest_rate]" class="form-control" value="<?= get_setting('savings_interest_rate') ?>" placeholder="Enter rate">
                                                                        <span class="input-group-addon">%</span>
                                                                    </div>
                                                                    <small class="text-muted">Annual interest rate on savings accounts</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Interest Frequency -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-info">Interest Frequency</label>
                                                                    <select name="settings[savings_interest_frequency]" class="form-control">
                                                                        <option value="monthly" <?= get_setting('savings_interest_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                                        <option value="quarterly" <?= get_setting('savings_interest_frequency') == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                                                        <option value="annually" <?= get_setting('savings_interest_frequency') == 'annually' ? 'selected' : '' ?>>Annually</option>
                                                                    </select>
                                                                    <small class="text-muted">How often interest is calculated and credited</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Withdrawal Limit -->
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-info">Daily Withdrawal Limit</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon">₱</span>
                                                                        <input type="number" name="settings[savings_withdrawal_limit]" class="form-control" value="<?= get_setting('savings_withdrawal_limit') ?>" placeholder="Enter limit">
                                                                    </div>
                                                                    <small class="text-muted">Maximum daily withdrawal amount per member</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- SAVINGS SUMMARY & INSIGHTS -->
                                            <div class="col-lg-4">
                                                <div class="calculation-box">
                                                    <div class="calculation-title">Savings Program Summary</div>
                                                    <div class="calculation-formula">Member Wealth Building = Monthly Contributions + Interest Earnings</div>
                                                    <ul class="calculation-steps">
                                                        <li><strong>Monthly Requirement:</strong> ₱<?= number_format(get_setting('monthly_savings') ?: 0, 2) ?> per member</li>
                                                        <li><strong>Interest Rate:</strong> <?= get_setting('savings_interest_rate') ?: 0 ?>% applied <?= get_setting('savings_interest_frequency') ?: 'monthly' ?></li>
                                                        <li><strong>Minimum Balance:</strong> ₱<?= number_format(get_setting('savings_min_balance') ?: 0, 2) ?> must be maintained</li>
                                                        <li><strong>Withdrawal Limit:</strong> ₱<?= number_format(get_setting('savings_withdrawal_limit') ?: 0, 2) ?> daily maximum</li>
                                                        <li><strong>Annual Return:</strong> <?= get_setting('savings_interest_rate') ?: 0 ?>% on maintained balances</li>
                                                    </ul>
                                                </div>
                                                
                                                <div class="insight-box">
                                                    <div class="insight-title">Savings Program Benefits</div>
                                                    <ul>
                                                        <li><strong>Financial Security:</strong> Regular savings build emergency funds</li>
                                                        <li><strong>Interest Earnings:</strong> Members earn <?= get_setting('savings_interest_rate') ?: 0 ?>% returns</li>
                                                        <li><strong>Liquidity Management:</strong> Daily withdrawal limits protect fund stability</li>
                                                        <li><strong>Wealth Building:</strong> Consistent contributions create long-term assets</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- SAVE BUTTON -->
                                        <div class="text-right mt-4">
                                            <button type="submit" name="save_savings" class="btn btn-teal-400 btn-lg">
                                                <i class="icon-checkmark mr-2"></i> Save Savings Settings
                                            </button>
                                                </div>
                                    </form>
                                </div>

                                <!-- CAPITAL SHARE TAB -->
                                <div class="tab-pane fade" id="capital">
                                    <form method="POST">
                                        <div class="row">
                                            <!-- CAPITAL CONFIGURATION CARD -->
                                            <div class="col-lg-8">
                                                <div class="settings-card border-top-xlg border-top-warning">
                                                    <div class="card-header bg-warning text-white">
                                                        <h6 class="mb-0">
                                                            <i class="icon-coins mr-2"></i>
                                                            Capital Share Configuration
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <!-- Monthly Share Capital -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-warning">Monthly Members Shares</label>
                                                                    <div class="input-group input-group-lg">
                                                                        <span class="input-group-addon bg-warning text-white">₱</span>
                                                                        <input type="number" name="settings[monthly_share_capital]" class="form-control font-weight-bold text-warning border-warning" value="<?= get_setting('monthly_share_capital') ?>" placeholder="Enter amount">
                                                                    </div>
                                                                    <small class="text-muted">Required monthly capital contribution from members</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Minimum Capital Required -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-warning">Minimum Capital Required</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon">₱</span>
                                                                        <input type="number" name="settings[capital_min_required]" class="form-control" value="<?= get_setting('capital_min_required') ?>" placeholder="Enter amount">
                                                                    </div>
                                                                    <small class="text-muted">Minimum capital share balance required</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <!-- Maximum Capital Limit -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-warning">Maximum Capital Limit</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon">₱</span>
                                                                        <input type="number" name="settings[capital_max_limit]" class="form-control" value="<?= get_setting('capital_max_limit') ?>" placeholder="Enter limit">
                                                                    </div>
                                                                    <small class="text-muted">Maximum allowed capital contribution per member</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Capital Share Interest -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-warning">Capital Share Interest (%)</label>
                                                                    <div class="input-group">
                                                                        <input type="number" step="0.01" name="settings[capital_share_interest]" class="form-control" value="<?= get_setting('capital_share_interest') ?>" placeholder="Enter rate">
                                                                        <span class="input-group-addon">%</span>
                                                                    </div>
                                                                    <small class="text-muted">Annual return on capital shares</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Interest Frequency -->
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="font-weight-semibold text-warning">Interest Frequency</label>
                                                                    <select name="settings[capital_share_interest_frequency]" class="form-control">
                                                                        <option value="monthly" <?= get_setting('capital_share_interest_frequency') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                                        <option value="quarterly" <?= get_setting('capital_share_interest_frequency') == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                                                        <option value="annually" <?= get_setting('capital_share_interest_frequency') == 'annually' ? 'selected' : '' ?>>Annually</option>
                                                                    </select>
                                                                    <small class="text-muted">Distribution frequency for capital returns</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- CAPITAL SUMMARY & INSIGHTS -->
                                            <div class="col-lg-4">
                                                <div class="calculation-box">
                                                    <div class="calculation-title">Capital Share Program Summary</div>
                                                    <div class="calculation-formula">Member Ownership = Monthly Contributions + Interest Returns</div>
                                                    <ul class="calculation-steps">
                                                        <li><strong>Monthly Requirement:</strong> ₱<?= number_format(get_setting('monthly_share_capital') ?: 0, 2) ?> per member</li>
                                                        <li><strong>Interest Rate:</strong> <?= get_setting('capital_share_interest') ?: 0 ?>% applied <?= get_setting('capital_share_interest_frequency') ?: 'annually' ?></li>
                                                        <li><strong>Minimum Balance:</strong> ₱<?= number_format(get_setting('capital_min_required') ?: 0, 2) ?> required</li>
                                                        <li><strong>Maximum Limit:</strong> ₱<?= number_format(get_setting('capital_max_limit') ?: 0, 2) ?> per member</li>
                                                        <li><strong>Annual Return:</strong> <?= get_setting('capital_share_interest') ?: 0 ?>% on capital balances</li>
                                                    </ul>
                                                </div>
                                                
                                                <div class="insight-box">
                                                    <div class="insight-title">Capital Program Benefits</div>
                                                    <ul>
                                                        <li><strong>Ownership Stake:</strong> Members own part of the cooperative</li>
                                                        <li><strong>Investment Returns:</strong> Members earn <?= get_setting('capital_share_interest') ?: 0 ?>% on shares</li>
                                                        <li><strong>Long-term Growth:</strong> Capital builds sustainable cooperative foundation</li>
                                                        <li><strong>Member Benefits:</strong> Shared profits and voting rights</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- SAVE BUTTON -->
                                        <div class="text-right mt-4">
                                            <button type="submit" name="save_capital" class="btn btn-teal-400 btn-lg">
                                                <i class="icon-checkmark mr-2"></i> Save Capital Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
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