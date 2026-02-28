<?php require('includes/header.php'); ?>
<style type="text/css">
    #show-search-user {
        background-color: #26a69a;
        min-height: 300px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 100;
        position: absolute;
        width: 100%;
        display: none;
    }

    #show-search-user::-webkit-scrollbar-track {
        background-color: #F5F5F5;
    }

    #show-search-user::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    #show-search-user::-webkit-scrollbar-thumb {
        background-color: #3c8881;
    }

    #show-search-customer {
        position: absolute;
        min-height: 300px;
        max-height: 300px;
        overflow-y: scroll;
        background: #26a69a;
        width: 100%;
        z-index: 10;
        padding: 0px !important;
        display: none;
    }

    #show-search-customer::-webkit-scrollbar-track {
        background-color: #F5F5F5;
    }

    #show-search-customer::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    #show-search-customer::-webkit-scrollbar-thumb {
        background-color: #3c8881;
    }

    .ul-search {
        list-style-type: none;
        background: #26a69a;
        color: #fff;
        margin-left: -25px;
        font-size: 12px;
    }

    .ul-search li {
        padding-top: 10px;
        padding-left: 10px;
        padding-bottom: 10px;
        height: 40px;
        font-size: 12px;
        cursor: pointer;
    }

    .ul-search li {
        border-bottom: 1px solid #dddddd;
    }

    .name-span {
        font-size: 12px;
    }

    #customer-input {
        width: 200px;
    }

    #searchclear {
        position: absolute;
        right: 5px;
        top: 0;
        bottom: 0;
        height: 14px;
        margin: auto;
        font-size: 14px;
        cursor: pointer;
        color: #ccc;
    }

    #user-input {
        width: 200px;
    }

    #searchclearuser {
        position: absolute;
        right: 5px;
        top: 0;
        bottom: 0;
        height: 14px;
        margin: auto;
        font-size: 14px;
        cursor: pointer;
        color: #ccc;
    }

    .containers {
        display: block;
        position: relative;
        padding-left: 25px;
        margin-bottom: 22px;
        cursor: pointer;
        font-size: 14px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        color: #5a5959;
    }

    /* Hide the browser's default checkbox */
    .containers input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    /* Create a custom checkbox */
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: #bfbfbf;
    }

    /* On mouse-over, add a grey background color */
    .containers:hover input~.checkmark {
        background-color: #bfbfbf;
    }

    /* When the checkbox is checked, add a blue background */
    .containers input:checked~.checkmark {
        background-color: #26a69a;
    }

    /* Create the checkmark/indicator (hidden when not checked) */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the checkmark when checked */
    .containers input:checked~.checkmark:after {
        display: block;
    }

    /* Style the checkmark/indicator */
    .containers .checkmark:after {
        left: 9px;
        top: 5px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 3px 3px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    .paging_simple_numbers,
    .dataTables_info {
        margin-top: 10px;
    }

    .docoment-text {
        margin-top: 50px;
    }
</style>

<?php


if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3]) // allow usertype 1 OR 2
) {
    die("Unauthorized access.");
}


require('db_connect.php');
if (isset($_SESSION['sale-report-user']) != "") {
    $user_query_name = "SELECT * FROM tbl_users WHERE user_id='" . $_SESSION['sale-report-user'] . "' ";
    $user_queryname = $db->query($user_query_name);
    while ($row = $user_queryname->fetch_assoc()) {
        $selected_user = $row['fullname'];
    }
} else {
    $selected_user = "";
}

if (isset($_SESSION['sale-report-customer']) != "") {
    $customer_query_name = "SELECT * FROM tbl_customer WHERE cust_id='" . $_SESSION['sale-report-customer'] . "' ";
    $customer_queryname = $db->query($customer_query_name);
    while ($row = $customer_queryname->fetch_assoc()) {
        $selected_customer = $row['name'];
    }
} else {
    $selected_customer = "";
}

if (isset($_SESSION['sale-report-customer']) != "") {
    $customer_query = "AND tbl_sales.cust_id='" . $_SESSION['sale-report-customer'] . "' ";
} else {
    $customer_query = "";
}
$query = "SELECT * FROM tbl_customer";
$customer = $db->query($query);
$query1 = "SELECT * FROM tbl_users";
$employee = $db->query($query1);

if (isset($_SESSION['sale-report-status'])) {
    if ($_SESSION['sale-report-status'] == 1) {
        $status_text = "Active";
        $input_status = 1;
    }
    if ($_SESSION['sale-report-status'] == 2) {
        $status_text = "Updated";
        $input_status = 2;
    }
    if ($_SESSION['sale-report-status'] == 3) {
        $status_text = "Cancelled";
        $input_status = 3;
    }
    $query_status = "AND tbl_sales.sales_status='" . $_SESSION['sale-report-status'] . "' ";
} else {
    $input_status = "";
    $status_text = "All ";
    $query_status = "";
}
if (isset($_SESSION['sale-report-register'])) {
    if ($_SESSION['sale-report-register'] == 1) {
        $register_text = "Closed";
        $input_register = 1;
    }
    if ($_SESSION['sale-report-register'] == 0) {
        $register_text = "Open";
        $input_register = 0;
    }

    $query_register = "AND tbl_sales.register='" . $_SESSION['sale-report-register'] . "' ";
} else {
    $register_text = "All ";
    $query_register = "";
    $input_register = "";
}


if (isset($_SESSION['sale-report-type'])) {
    if ($_SESSION['sale-report-type'] == 1) {
        $type_text = "Food Panda";
        $input_type = 1;
    }
    if ($_SESSION['sale-report-type'] == 0) {
        $type_text = "Default";
        $input_type = 0;
    }

    $query_type = "AND tbl_sales.sales_type='" . $_SESSION['sale-report-type'] . "' ";
} else {
    $type_text = "All ";
    $type_register = "";
    $type_register = "";
}

if (isset($_SESSION['sale-report'])) {
    $btn_color = 'bg-danger-400';
} else {
    $btn_color = 'bg-slate-400';
}

if (isset($_SESSION['sales-date-required'])) {
    $checkbox = 'checked';
} else {
    $checkbox = '';
}

?>
<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        /* vertically center image + text */
        gap: 0px;
        /* space between logo and text */
        font-weight: 800;
        color: white;
        /* adjust to your navbar color */
        text-decoration: none;
        font-size: 50px;
    }

    .navbar-brand img {
        height: 40px;
        /* adjust logo height */
        width: auto;
        object-fit: contain;
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
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - Sales Report</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="javascript:;"><i class="icon-chart position-left"></i> Reports</a></li>
                            <li class="active"><i class="icon-dots position-left"></i>Sales</li>
                        </ul>
                    </div>
                </div>
                <div class="content">
                    <div class="row">

                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-success-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-cart  icon-3x opacity-75"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin" id="no-sales">0</h3>
                                        <span class="text-uppercase text-size-mini">No. of Sales</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-blue-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin" id="subtotal">0.00</h3>
                                        <span class="text-uppercase text-size-mini">Sub Total</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-danger-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin" id="discount">0.00</h3>
                                        <span class="text-uppercase text-size-mini">Discount</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body bg-indigo-400 has-bg-image">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-3x opacity-75">₱</i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin" id="totalAmount">0.00</h3>
                                        <span class="text-uppercase text-size-mini">Total Amount</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-body ">
                        <div>
                            <form class="heading-form" id="form-sales" method="POST">
                                <input type="hidden" name="submit-sales">
                                <ul class="breadcrumb-elements" style="float:left">
                                    <li style="padding-top: 2px;padding-right: 2px">
                                        <div class="input-group">
                                            <span data-toggle="tooltip" title="Enable/disable Sales Date" class="input-group-addon" style="padding: 5px 12px;">
                                                <label class="containers">
                                                    <input type="checkbox" name="date-required" <?= $checkbox ?>>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </span>
                                            <input style="width: 180px" type="text" autocomplete="off" name="date" class="form-control daterange-buttons " value=" <?php if (isset($_SESSION['sale-report']) != "") { ?>   <?= $_SESSION['sale-report'] ?> <?php } else { ?> <?= date("m-d-Y") ?> - <?= date("m-d-Y") ?>  <?php } ?>">
                                        </div>
                                    </li>
                                    <li data-toggle="tooltip" title="Customer" style="padding-top: 2px;padding-right: 2px">
                                        <div class="btn-group">
                                            <input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['sale-report-customer']) != "") {
                                                                                                echo  $_SESSION['sale-report-customer'];
                                                                                            } ?>" name="cust_id" id="cust_id">
                                            <input style="width: 230px" autocomplete="off" type="search" class="form-control" id="customer-input" value="<?php if (isset($_SESSION['sale-report-customer']) != "") {
                                                                                                                                                                echo  $selected_customer;
                                                                                                                                                            } ?>" name="custname">
                                            <span id="searchclear" class="glyphicon glyphicon-remove-circle"></span>
                                            <div id="show-search-customer"></div>
                                        </div>
                                    </li>
                                    <li data-toggle="tooltip" title="Employee" style="padding-top: 2px;padding-right: 2px">
                                        <div class="btn-group">
                                            <input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['sale-report-user']) != "") {
                                                                                                echo  $_SESSION['sale-report-user'];
                                                                                            } ?>" name="user_id" id="user_id">
                                            <input style="width: 230px" autocomplete="off" type="search" class="form-control" id="user-input" value="<?php if (isset($_SESSION['sale-report-user']) != "") {
                                                                                                                                                            echo  $selected_user;
                                                                                                                                                        } ?>" name="username">
                                            <span id="searchclearuser" class="glyphicon glyphicon-remove-circle"></span>
                                            <div id="show-search-user"></div>
                                        </div>
                                    </li>
                                    <input type="hidden" id="input-status" name="status" value="<?= $input_status ?>">
                                    <li data-toggle="tooltip" title="Status" class="text-center" style="padding-top: 2px;padding-right: 2px;width: auto;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-rounded"> <span id="span-status"><?= $status_text ?></span></button>
                                            <button type="button" class="btn btn-default btn-rounded dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li onclick="select_status(this)" status-val="1" status-name="Active"><a href="#"><i class="icon-circle text-green-400"></i> Active</a></li>
                                                <!-- <li onclick="select_status(this)" status-val="2"  status-name="Updated" ><a href="#"><i class="icon-circle text-blue-400"></i> Updated</a></li> -->
                                                <li onclick="select_status(this)" status-val="3" status-name="Cancelled"><a href="#"><i class="icon-circle text-danger-400"></i> Cancelled </a></li>
                                                <li onclick="select_status(this)" status-val="" status-name="All"><a href="#"><i class="icon-circle text-default-400"></i> All </a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <input type="hidden" id="input-type" name="sales-type" value="<?= $input_type ?>">

                                    <input type="hidden" id="input-register" name="register" value="<?= $input_register ?>">
                                    <li data-toggle="tooltip" title="Register" class="text-center" style="padding-top: 2px;padding-right: 2px;width: auto">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-rounded"> <span id="span-register"><?= $register_text ?></span> </button>
                                            <button type="button" class="btn btn-default btn-rounded dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li onclick="select_register(this)" status-val="0" status-name="Open"><a href="#"><i class="icon-circle text-primary-400"></i> Open</a></li>
                                                <li onclick="select_register(this)" status-val="1" status-name="Close"><a href="#"><i class="icon-circle text-green-400"></i> Close</a></li>
                                                <li onclick="select_register(this)" status-val="" status-name="All"><a href="#"><i class="icon-circle text-default-400"></i> ALL</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400"><b><i class="icon-search4"></i></b></button></li>
                                    <li data-toggle="tooltip" title="Clear" style="padding-top: 2px;padding-right: 2px"><button type="button" onclick="clear_filter()" class="btn <?= $btn_color ?>"><b><i class="icon-filter4"></i></b></button></li>
                                    <li data-toggle="tooltip" title="Export Excel" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="window.location.href='downloadPDF.php?type=sales&export=excel&backlink=sales-report.php'" class="btn bg-success-700"><b><i class="icon-file-excel"></i></b></button></li>
                                    <li data-toggle="tooltip" title="Export PDF" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="window.location.href='downloadPDF.php?type=sales&export=pdf&backlink=sales-report.php'" class="btn bg-danger-700"><b><i class="icon-file-pdf"></i></b></button></li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400"></i> List of Sales<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                            <div class="heading-elements">
                            </div>
                        </div>
                        <div class="entry-page">
                            <div class="btn-group">
                                <button type="button" class="btn bg-teal-400 btn-labeled"><b><i class="icon-book"></i></b> Entries</button>
                                <button type="button" class="btn bg-teal-400 dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li onclick="changePage(this)" val="20"><a href="#"><i class="icon-circles text-primary "></i> 20</a></li>
                                    <li onclick="changePage(this)" val="50"><a href="#"><i class="icon-circles "></i> 50</a></li>
                                    <li onclick="changePage(this)" val="100"><a href="#"><i class="icon-circles "></i> 100</a></li>
                                    <li onclick="changePage(this)" val="-1"><a href="#"><i class="icon-circles "></i> All</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body product-div2">
                            <input type="hidden" name='length_change' id='length_change' value="">
                            <table class="table datatable-button-html5-basic table-hover table-bordered ">
                                <thead>
                                    <tr class="tr-table">
                                        <th>Date</th>
                                        <th>Bill No.</th>
                                        <th>Employee</th>
                                        <th>Customer</th>
                                        <th>Amount Due</th>
                                        <th>Other Amount</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <!-- <th>Register</th> -->
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr class="tr-table">
                                        <th>Date</th>
                                        <th>Bill No.</th>
                                        <th>Employee</th>
                                        <th>Customer</th>
                                        <th>Amount Due</th>
                                        <th>Other Amount</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <!-- <th>Register</th> -->
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php require('includes/footer-text.php'); ?>
            </div>
        </div>
    </div>
</body>

<div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-all"></h5>
                <button type="button" class="close" title="Click to close (Esc)" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="show-data-all"></div>
            </div>
        </div>
    </div>
</div>
<div id="modal-confirm" class="modal fade">
    <input type="hidden" id="cancel-input">
    <input type="hidden" id="sales-id-input">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Confirmation!!!</h5>
            </div>
            <div class="modal-bodys">
                <h3>Are you sure you want to cancel this sale?</h3>
                <!-- <h5>Sales Details and Product Inventory will updated. </h5> -->
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> NO</button>
                <a type="button" id="delete_sale" onclick="delete_sale()" href="javascript:;" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> YES</a>
            </div>
        </div>
    </div>
</div>
</div>
<div id="modal-confirm2" class="modal fade">
    <input type="hidden" id="sales-id-input2">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Confirmation!!!</h5>
            </div>
            <div class="modal-bodys">
                <h3>Are you sure you want to active this sale?</h3>
                <!-- <h5>Sales Details and Product Inventory will updated. </h5> -->
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> NO</button>
                <a type="button" id="active_sale" onclick="active_sale()" href="javascript:;" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> YES</a>
            </div>
        </div>
    </div>
</div>
</div>

<div id="modal-payment" class="modal fade">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Add Payment(<span id="add-payment-text"></span>)</h5>
            </div>
            <div class="modal-bodys">
                <form action="#" id="form-payment" class="form-horizontal" data-toggle="validator" role="form">
                    <input type="hidden" name="save-payment-charge"></input>
                    <input type="hidden" name="sales_no" id="sales-id-payment">
                    <div class="form-body">
                        <div class="form-group">
                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Balance</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                    <input id="balance" class="form-control" name="balance" placeholder="Balance" type="text" data-error=" Balance is required." required disabled>
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group" hidden>
                            <label for="exampleInputuname_4" class="col-sm-3 control-label">CR Number</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                    <input class="form-control currency" onkeypress='return numbersonly(event)' name="cr_no" placeholder="Collection Receipt Number" type="text">
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Amount</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                    <input id="amount_paid" class="form-control filterme" name="amount" placeholder="Amount" type="text" data-error=" Amount is required." required>
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> Close</button>
                        <button type="submit" id="active_sale" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> Submit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>

<!-- Theme JS files -->
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/extensions/buttons.min.js"></script>
<script src="../js/validator.min.js"></script>
<script type="text/javascript">
    var image = '<img src="../images/LoaderIcon.gif" >';
    let sales_no = null;
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        var oTable = $('.datatable-button-html5-basic').DataTable({
            "bDestroy": true,
            "aaSorting": [],
            "ordering": false,
            "searching": false,
            "iDisplayLength": 20,
            "aLengthMenu": [
                [20, 50, 100, 200, 500],
                [20, 50, 100, 200, 500]
            ],
            /* "responsive": true,*/
            "processing": true,
            // "scrollX": true, // enables horizontal scrolling    
            /* "stateSave": true,*/ //restore table state on page reload, 
            "oLanguage": {
                "sSearch": '<div class="input-group">_INPUT_<span class="input-group-addon"><i class="icon-search"></i></span></div>',
                "sSearchPlaceholder": "Search...",
                "sProcessing": '' + image + '',
            },
            "serverSide": true,
            "columnDefs": [{
                    className: 'right',
                    targets: 4
                },
                {
                    className: 'right',
                    targets: 5
                },
                {
                    className: 'right',
                    targets: 5
                },
                {
                    className: 'right',
                    targets: 6
                },
                {
                    className: 'center',
                    targets: 7
                },
                {
                    className: 'center',
                    targets: 8
                },
                {
                    className: 'center',
                    targets: 9
                }
            ],
            "ajax": {
                url: "../transaction.php?search-report",
                type: 'POST',
                dataFilter: function(data) { // console.log(data);
                    var json = jQuery.parseJSON(data);
                    json.recordsTotal = json.recordsFiltered;
                    json.recordsFiltered = json.recordsFiltered;
                    json.data = json.data;
                    checkSummary(json.queryTotal);
                    return JSON.stringify(json);
                }


            }

        });
        // $('#length_change').val(oTable.page.len());
        // $('#length_change').change( function() { 
        //      oTable.page.len( $(this).val() ).draw();
        // });
        // $("#DataTables_Table_0_length").html('');
    });

    function checkSummary(el) {
        if (!el || !el.length) return; // prevent error if undefined

        var totalAmount = 0;
        var subtotal = 0;
        var discount = 0;
        for (var i = 0; i < el.length; i++) {
            totalAmount += el[i].total_amount;
            subtotal += el[i].subtotal;
            discount += el[i].discount;
        }
        $("#totalAmount").html(formatMoney(totalAmount));
        $("#subtotal").html(formatMoney(subtotal));
        $("#discount").html(formatMoney(discount));
        $("#no-sales").html(el.length);
    }

    function formatMoney(number, decPlaces, decSep, thouSep) {
        (decPlaces = isNaN((decPlaces = Math.abs(decPlaces))) ?
            2 :
            decPlaces),
        (decSep = typeof decSep === "undefined" ? "." : decSep);
        thouSep = typeof thouSep === "undefined" ? "," : thouSep;
        var sign = number < 0 ? "-" : "";
        var i = String(
            parseInt(
                (number = Math.abs(Number(number) || 0).toFixed(decPlaces))
            )
        );
        var j = (j = i.length) > 3 ? j % 3 : 0;

        return (
            sign +
            (j ? i.substr(0, j) + thouSep : "") +
            i
            .substr(j)
            .replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
            (decPlaces ?
                decSep +
                Math.abs(number - i)
                .toFixed(decPlaces)
                .slice(2) :
                "")
        );
    }

    $('#form-sales').on('submit', function(e) {
        $(':input[type="submit"]').prop('disabled', true);
        var data = $("#form-sales").serialize();
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

    function closer() {
        window.location = 'products.php';
    }

    function view_details(el) {
        sales_no = $(el).attr('sales-no');
        var sales_id = $(el).attr('sales-id');
        $("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                sales_report_details: "",
                sales_no: sales_no
            },
            success: function(msg) {
                $("#modal-all").modal('show');
                $("#show-button").html('');
                $("#title-all").html('Bill No. : <b class="text-danger">' + sales_id + '</b>');
                $("#show-data-all").html(msg);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    }

    $("#customer-input").keyup(function() {
        $("#show-search-customer").show();
        var keywords = $(this).val();
        if (keywords != "") {
            $.ajax({
                type: 'GET',
                url: '../transaction.php',
                data: {
                    search_customer: "",
                    keywords_search: keywords
                },
                success: function(msg) {
                    $("#show-search-customer").html(msg);
                    $("#show-loader").html('');
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
        } else {
            $("#customer-input").click();
        }

    });

    $("#customer-input").click(function() {
        $("#show-search-customer").show();
        $("#show-search-user").hide();
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                search_customer: "",
                keywords_search: ""
            },
            success: function(msg) {
                $("#show-search-customer").html(msg);
                $("#show-loader").html('');
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    });

    $("#user-input").keyup(function() {
        $("#show-search-user").show();
        var keywords = $(this).val();
        if (keywords != "") {
            $.ajax({
                type: 'GET',
                url: '../transaction.php',
                data: {
                    search_user: "",
                    keywords: keywords
                },
                success: function(msg) {
                    $("#show-search-user").html(msg);
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
        } else {
            $("#user-input").click();
        }

    });

    $("#user-input").click(function() {
        $("#show-search-user").show();
        $("#show-search-customer").hide();
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                search_user: "",
                keywords: ""
            },
            success: function(msg) {
                $("#show-search-user").html(msg);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    });


    function select_customer(el) {
        var cust_id = $(el).attr('cust_id');
        var name = $(el).attr('name');
        $("#cust_id").val(cust_id);
        $("#customer-input").val(name);
        $("#show-search-customer").hide();

    }

    function select_user(el) {
        var user_id = $(el).attr('user_id');
        var name = $(el).attr('name');
        $("#user_id").val(user_id);
        $("#user-input").val(name);
        $("#show-search-user").hide();
    }

    $("#searchclear").click(function() {
        $("#customer-input").val("");
        $("#show-search-customer").hide();
    });

    $("#searchclearuser").click(function() {
        $("#user-input").val("");
        $("#show-search-user").hide();
    });

    function select_status(el) {
        $("#span-status").html($(el).attr('status-name'));
        $("#input-status").val($(el).attr('status-val'));
    }

    function select_type(el) {
        $("#span-type").html($(el).attr('status-name'));
        $("#input-type").val($(el).attr('status-val'));
    }

    function select_register(el) {
        $("#span-register").html($(el).attr('status-name'));
        $("#input-register").val($(el).attr('status-val'));
    }

    function clear_filter() {
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                clear_filter_sales: ""
            },
            success: function(msg) {
                location.reload();
            }

        });
    }

    function changePage(el) {
        $(".icon-circles").removeClass('text-primary');
        $("#length_change").val($(el).attr('val'));
        $("#length_change").trigger('change');
        $(el).find('.icon-circles').addClass('text-primary');
    }

    function print_receipt() {
        var contents = $("#print-receipt").html();
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({
            "position": "absolute",
            "top": "-1000000px"
        });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write('<html><head><title></title>');
        frameDoc.document.write('</head><body>');
        frameDoc.document.write(contents);
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function() {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
    }

    function delete_sales(el) {
        var sales_no = $(el).attr('sales_no');
        $("#sales-id-input").val(sales_no);
        $("#cancel-input").val('yes');
        $("#modal-confirm").modal('show');
    }

    function delete_sale() {
        $("#delete_sale").attr('disabled', true);
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                delete_sales: "",
                sales_id: $("#sales-id-input").val()
            },
            success: function(msg) {
                console.log(msg);
                $.jGrowl('Sales successfully cancelled.', {
                    header: 'Success Notification',
                    theme: 'alert-styled-right bg-success'
                });

                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    }

    function set_active(el) {
        var sales_no = $(el).attr('sales_no');
        $("#sales-id-input2").val(sales_no);
        $("#modal-confirm2").modal('show');
    }

    function active_sale() {
        $("#active_sale").attr('disabled', true);
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                active_sales: "",
                sales_id: $("#sales-id-input2").val()
            },
            success: function(msg) {
                console.log(msg);
                $.jGrowl('Sales successfully active.', {
                    header: 'Success Notification',
                    theme: 'alert-styled-right bg-success'
                });

                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    }

    function add_payment(el) {
        if (parseFloat($(el).attr('balance')) <= 0) {
            $.jGrowl('Zero Balance.', {
                header: 'Error Notification',
                theme: 'alert-styled-right bg-danger'
            });
        } else {
            $("#add-payment-text").html($(el).attr('sales_id'));
            $("#balance").val(parseFloat($(el).attr('balance')).toFixed(2));
            $("#add-payment-text").html($(el).attr('sales_id'));
            $("#sales-id-payment").val($(el).attr('sales_no'));
            $("#modal-payment").modal("show");
        }
    }

    $("#amount_paid").change(function() {
        var amount = parseFloat($(this).val());
        var balance = parseFloat($("#balance").val());
        if (amount > balance) {
            $(this).val(balance)
        }
    });

    $('#form-payment').validator().on('submit', function(e) {
        if (e.isDefaultPrevented()) {} else {
            $(':input[type="submit"]').prop('disabled', true);
            var data = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: '../transaction.php',
                data: data,
                success: function(msg) {

                    if (msg == '1') {

                        $.jGrowl('Payment successfully saved.', {
                            header: 'Success Notification',
                            theme: 'alert-styled-right bg-success'
                        });
                        setTimeout(function() {
                            location.reload()
                        }, 1500);
                    } else {
                        alert('Something went wrong!');
                    }
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
            return false;
        }
    });

    function delivery_address(el) {

        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                update_delivery_address: '',
                sales_no: sales_no,
                delivery_address: $(el).val()
            },
            success: function(msg) {

            }

        });
    }

    function salesman(el) {

        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                update_salesman: '',
                sales_no: sales_no,
                salesman: $(el).val()
            },
            success: function(msg) {

            }

        });
    }
</script>


</html>