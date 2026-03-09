<?php require('../admin/includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Manila');
date_default_timezone_get();
$year = date('Y'); // current year
$today = date("Y-m-d");
$date_add = date('Y-m-d', strtotime('+1 day', strtotime($today)));

$deposit = 0;
$deposit_query = "SELECT SUM(amount) AS total FROM tbl_deposits WHERE date_added BETWEEN '$today' AND '$date_add'";
$result_deposit = $db->query($deposit_query);
$row = $result_deposit->fetch_assoc();
$deposit = $row['total'] ?? 0;

$all_subtotal = 0;
$all_discount = 0;
$all_total = 0;
$total_sales = 0;

$query = "
SELECT 
    sales_no,
    SUM(subtotal) AS subtotal,
    SUM(discount) AS discount,
    SUM(total_amount) AS total_amount
FROM tbl_sales
WHERE sales_date BETWEEN '$today' AND '$date_add'
GROUP BY sales_no
";
$result = $db->query($query);

if (!isset($_SESSION['is_login_yes'], $_SESSION['user_id']) || $_SESSION['is_login_yes'] != 'yes') {
    die("Unauthorized access. Please log in again.");
}

$user_id = (int) $_SESSION['user_id'];


$member_result = $db->query("
    SELECT member_id, cust_id, type 
    FROM tbl_members 
    WHERE user_id = $user_id
    LIMIT 1
");



$member_data = $member_result->fetch_assoc();

$member_id = (int)$member_data['member_id'];
$cust_id   = (int)$member_data['cust_id'];
$member_type = $member_data['type'];

$_SESSION['member_type'] = $member_type;

if ($cust_id <= 0) {
    die("Invalid customer account.");
}


if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['subtotal'];
        $discount = $row['discount'];
        $total_amount = $row['total_amount'];
        $all_subtotal += $subtotal;
        $all_discount += $discount;
        $all_total += $total_amount;
        $total_sales++;
    }
}

$vat_sales = $all_subtotal * 0.12;


$customer_select = "SELECT COUNT(*) AS total_customer FROM tbl_customer";
$customer_result = $db->query($customer_select);
$customer_row = $customer_result->fetch_assoc();
$customer_total = $customer_row['total_customer'];

$user_select = "SELECT COUNT(*) AS total_user FROM tbl_users WHERE usertype != 4";
$user_result = $db->query($user_select);
$user_row = $user_result->fetch_assoc();
$user_total = $user_row['total_user'];

$supplier_select = "SELECT COUNT(*) AS total_supplier FROM tbl_supplier";
$supplier_result = $db->query($supplier_select);
$supplier_row = $supplier_result->fetch_assoc();
$supplier_total = $supplier_row['total_supplier'];

// Get total capital share for this member
$capital_share = $db->query("
    SELECT IFNULL(SUM(t.amount),0) AS total
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'capital_share'
")->fetch_assoc()['total'] ?? 0;

// Get total savings for this member
$savings_total = $db->query("
    SELECT IFNULL(SUM(t.amount),0) AS total
    FROM transactions t
    INNER JOIN accounts a ON a.account_id = t.account_id
    INNER JOIN account_types at ON at.account_type_id = a.account_type_id
    WHERE a.member_id = $member_id
      AND at.type_name = 'savings'
")->fetch_assoc()['total'] ?? 0;

?>

<link rel="stylesheet" href="../css/mobile-dashboard.css">
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
</style>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="dashboard.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
            <ul class="nav navbar-nav visible-xs-block">
                <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            </ul>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('../admin/includes/sidebar.php'); ?>
        </div>
    </div>
    <!-- /main navbar -->
    <!-- Page container -->
    <div class="page-container">

        <div class="mobile-view">


            <div class="mobile-header">
                Hello, <?= $_SESSION['fullname'] ?>
                <button id="toggle-balance" title="Show/Hide Balance" style="background:none; border:none; color:#fff; font-size:18px;">
                    <i class="icon-eye"></i>
                </button>
            </div>


            <div class="mobile-balance-card">

                <div class="balance-tabs">
                    <?php if ($_SESSION['member_type'] !== 'associate'): ?>
                        <div class="tab active" data-target="#capital">Capital Share</div>
                    <?php endif; ?>
                    <div class="tab " data-target="#savings">Savings</div>
                </div>

                <div class="tab-content">

                    <div id="capital" class="tab-pane active">
                        <?php if ($_SESSION['member_type'] !== 'associate'): ?>
                            <h2 class="balance-amount">₱ <?= number_format($capital_share, 2) ?></h2>
                        <?php endif; ?>
                    </div>

                    <div id="savings" class="tab-pane">
                        <h2 class="balance-amount">₱ <?= number_format($savings_total, 2) ?></h2>
                    </div>

                </div>
            </div>




            <div class="mobile-actions">
                <a href="capital_share.php"><i class="icon-credit-card"></i>Deposit</a>
                <a href="capital_share.php"><i class="icon-"></i>withdraw</a>
                <a href="loan.php"> <i class="icon-coins"></i>Loan</a>
                <a href="transaction_history.php"><i class="icon-history"></i>History</a>
            </div>


            <div class="mobile-loan-summary">
                <b>Loan Overview</b>
                <div class="loan-card">
                    <div>
                        <small>Disbursed</small>
                        <h4>₱ 0.00</h4>
                    </div>
                    <div>
                        <small>Repaid</small>
                        <h4>₱ 0.00</h4>
                    </div>
                    <div>
                        <small>Outstanding</small>
                        <h4>₱ 0.00</h4>
                    </div>
                </div>
            </div>


            <!-- <div class="mobile-commercial">

                <div class="commercial-header">
                    Promotions
                </div>

                <div class="commercial-slider" id="commercialSlider">

                    <div class="commercial-item">
                        <img src="../images/no-image.png">
                        <div class="commercial-info">
                            <h4>Low Interest Loan</h4>
                            <p>now with only 1% interest.</p>
                        </div>
                    </div>

                    <div class="commercial-item">
                        <img src="../images/no-image.png">
                        <div class="commercial-info">
                            <h4>Savings Bonus</h4>
                            <p> save more.</p>
                        </div>
                    </div>

                    <div class="commercial-item">
                        <img src="../images/no-image.png">
                        <div class="commercial-info">
                            <h4>Member Benefits</h4>
                            <p>Exclusive offers for members.</p>
                        </div>
                    </div>

                </div> -->

        </div>


    </div>


    </div>

    <?php require('../admin/includes/footer-text.php'); ?>
    </div>

    <!-- Page content -->
    <div class="page-content desktop-view">
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header page-header-default"></div>
            <!-- /page header -->




            <!-- Content area -->
            <div class="content">
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <div class="panel panel-body">
                            <div class="media no-margin">
                                <div class="media-left media-middle">
                                    <i class="icon-credit-card  icon-3x text-danger-400"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 class="no-margin text-semibold"><?= $capital_share ?></h3>
                                    <span class="text-uppercase text-size-mini text-muted">Capital Share</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="panel panel-body panel-body-accent">
                            <div class="media no-margin">
                                <div class="media-left media-middle">
                                    <i class="icon-piggy-bank icon-3x text-success-400"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 class="no-margin text-semibold"><?= $savings_total ?></h3>
                                    <span class="text-uppercase text-size-mini text-muted">Savings</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="panel panel-body">
                            <div class="media no-margin">
                                <div class="media-left media-middle">
                                    <i class="icon-file-text icon-3x text-indigo-400"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 class="no-margin text-semibold"><?= $customer_total ?></h3>
                                    <span class="text-uppercase text-size-mini text-muted">Loans</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="panel panel-body">
                            <div class="media no-margin">
                                <div class="media-left media-middle">
                                    <i class="icon-history icon-3x text-blue-400"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 class="no-margin text-semibold"><?= $supplier_total ?></h3>
                                    <span class="text-uppercase text-size-mini text-muted">Transactions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <h6 class="panel-title">
                            <i class="icon-stats-bars text-teal-400"></i> Overview
                        </h6>
                    </div>

                    <div class="panel-body">
                        <div class="row">

                            <!-- Capital Share -->
                            <div class="col-sm-6 col-md-4">
                                <div class="panel panel-body bg-success-400 has-bg-image">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-coins icon-3x opacity-75"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin">
                                                <!-- ₱ <?= number_format($contributions, 2) ?> -->
                                            </h3>
                                            <span class="text-uppercase text-size-mini">
                                                Capital Share
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Savings -->
                            <div class="col-sm-6 col-md-4">
                                <div class="panel panel-body bg-blue-400 has-bg-image">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-wallet icon-3x opacity-75"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin">
                                                <!-- ₱ <?= number_format($savings, 2) ?> -->
                                            </h3>
                                            <span class="text-uppercase text-size-mini">
                                                Savings
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loans -->
                            <div class="col-sm-6 col-md-4">
                                <div class="panel panel-body bg-warning-400 has-bg-image">
                                    <div class="media no-margin">
                                        <div class="media-left media-middle">
                                            <i class="icon-credit-card icon-3x opacity-75"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h3 class="no-margin">
                                                <!-- ₱ <?= number_format($loans, 2) ?> -->
                                            </h3>
                                            <span class="text-uppercase text-size-mini">
                                                Loans Balance
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- /content area -->
    </div>
    <!-- /main content -->
    </div>
    <!-- /page content -->
    </div>
    <!-- /page container -->
</body>

<div class="mobile-bottom-nav">
    <a href="transaction_history.php">
        <i class="icon-history"></i>
        transaction
    </a>

    <a href="dashboard.php" class="active">
        <i class="icon-home"></i>
        Home
    </a>
    <a href="loan.php">
        <i class="icon-coins"></i>
        Loans
    </a>
    <a href="../admin/profile.php">
        <i class="icon-user"></i>
        Profile
    </a>
</div>
<?php require('../admin/includes/footer.php'); ?>


<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>


<script type="text/javascript">
    const tabs = document.querySelectorAll('.balance-tabs .tab');
    const panes = document.querySelectorAll('.tab-content .tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.querySelector(tab.dataset.target).classList.add('active');
        });
    });

    // Toggle show/hide balance
    document.getElementById('toggle-balance').addEventListener('click', function() {
        const amounts = document.querySelectorAll('.balance-amount');
        amounts.forEach(a => a.classList.toggle('hide'));
        this.querySelector('i').classList.toggle('icon-eye-blocked');
    });

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();

    });

    $('#form-seller').on('submit', function(e) {
        $(':input[type="submit"]').prop('disabled', true);
        var data = $("#form-seller").serialize();
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: data,
            success: function(msg) {
                location.reload();
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    });

    const slider = document.getElementById('commercialSlider');

    let scrollAmount = 0;

    function autoScrollSlider() {

        const itemWidth = slider.querySelector('.commercial-item').offsetWidth + 16;

        scrollAmount += itemWidth;

        if (scrollAmount >= slider.scrollWidth - slider.clientWidth) {
            scrollAmount = 0;
        }

        slider.scrollTo({
            left: scrollAmount,
            behavior: 'smooth'
        });

    }

    setInterval(autoScrollSlider, 5000);
</script>