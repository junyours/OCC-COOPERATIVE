<?php session_start(); ?>
<?php


// error_reporting(E_ALL);
// ini_set('display_errors', 1);



if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 2]) 
) {
    die("Unauthorized access.");
}


ini_set('max_execution_time', 0);
require('../db_connect.php');
$type = 1;
if (isset($_GET['type'])) {
    $type = 0;
}

if (!isset($_SESSION['payment_type'])) {
    $_SESSION['payment_type'] = true;
}

$other_amount = 0;
if (isset($_SESSION['other_amount'])) {
    $other_amount = $_SESSION['other_amount'];
}

$payment_type = false;
if (isset($_SESSION['payment_type']) && $_SESSION['payment_type']) {
    $payment_type = true;
}

$check_session  = $_SESSION['is_login_yes'];
$settings = "SELECT * FROM tbl_settings";
$result_settings = $db->query($settings);
while ($row = $result_settings->fetch_assoc()) {
    $tax = $row['tax'];
}

$today = date("Y-m-d");
$start = strtotime('today GMT');
$date_add = date('Y-m-d', strtotime('+1 day', $start));

$query = "
SELECT 
    MIN(tbl_sales.sales_id) AS sales_id,
    tbl_sales.sales_no,
    MIN(tbl_sales.sales_type) AS sales_type,
    MIN(tbl_sales.total_amount) AS total_amount,
    MIN(tbl_users.fullname) AS fullname,
    MIN(tbl_customer.name) AS customer_name,
    MIN(tbl_customer.address) AS customer_address,
    MIN(tbl_customer.contact) AS customer_contact
FROM tbl_sales
INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
WHERE sales_date BETWEEN '$today' AND '$date_add'
  AND tbl_sales.user_id = '{$_SESSION['user_id']}'
  AND sales_status != 3
GROUP BY tbl_sales.sales_no
ORDER BY tbl_sales.sales_no DESC
";
// $query = "
// SELECT 
//     ANY_VALUE(tbl_sales.sales_id) AS sales_id,
//     tbl_sales.sales_no,
//     ANY_VALUE(tbl_sales.sales_type) AS sales_type,
//     ANY_VALUE(tbl_sales.total_amount) AS total_amount,
//     ANY_VALUE(tbl_users.fullname) AS fullname,
//     ANY_VALUE(tbl_customer.name) AS customer_name,
//     ANY_VALUE(tbl_customer.address) AS customer_address,
//     ANY_VALUE(tbl_customer.contact) AS customer_contact
// FROM tbl_sales
// INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
// LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
// WHERE sales_date BETWEEN '$today' AND '$date_add'
//   AND tbl_sales.user_id = '{$_SESSION['user_id']}'
//   AND sales_status != 3
// GROUP BY tbl_sales.sales_no
// ORDER BY tbl_sales.sales_no DESC
// ";

$result = $db->query($query);

$total = 0;
$total_panda = 0;
$counter = 0;

while ($row = $result->fetch_assoc()) {
    $counter++;
    if ($row['sales_type']) {
        $total += $row['total_amount'];
    } else {
        $total_panda += $row['total_amount'];
    }
}


if (isset($_GET['update'])) {
    $sales_no = $_GET['sales_no'];
    echo "<script> var is_update = true; </script>";
    $salesSelect = "SELECT * FROM tbl_sales INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id WHERE sales_no='" . $_GET['sales_no'] . "'  ";
    $result_sales = $db->query($salesSelect);
    while ($rowSales = $result_sales->fetch_assoc()) {
        $sales_date = $rowSales['sales_date'];
        $user = $rowSales['fullname'];
    }
} else {
    echo "<script> var is_update = false; </script>";
}

$beginning = 0;
$beginning_query = "SELECT * FROM tbl_beginning_cash  ";
$result_beginning = $db->query($beginning_query);
while ($row = $result_beginning->fetch_assoc()) {
    $beginning = $row['amount'];
}

?>
<style>
    /* Make modal wider */
    #modal-payment .modal-dialog {
        max-width: 200px;
    }

    /* Keypad container */
    .pos-keypad {
        background: #0899af;
        padding: 15px;
        margin-top: 15px;
    }

    /* Make buttons fill cells */
    .pos-keypad table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
    }

    /* BIG POS BUTTONS */
    .pos-keypad .btn {
        width: 100%;
        height: 70px;
        font-size: 26px;
        font-weight: bold;
        border-radius: 10px;
    }

    /* ENTER button extra big */
    .pos-enter {
        height: 75px !important;
        font-size: 22px !important;
    }

    /* Clear button */
    .pos-clear {
        height: 75px !important;
        font-size: 20px !important;
    }

    #page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #ffffff;
        /* solid white */
        display: none;
        /* ONLY THIS */
        justify-content: center;
        align-items: center;
        z-index: 99999;
    }

    .page-loader-img {
        width: 90px;
        /* adjust size */
    }
</style>

<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS</title>
    <link href="../assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/colors.css" rel="stylesheet" type="text/css">
    <link href="../css/my_css.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../css/pos.css">
    <!-- <link href="../css/my_css.css" rel="stylesheet" type="text/css" /> -->
</head>

<div id="page-loader">
    <img src="../images/LoaderIcon.gif" alt="Loading..." class="page-loader-img">
</div>

<body>
    <div class="main-div">
        <div class="top-content">
            <div class="logo">
                <img src="../images/main_logo.jpg">
            </div>
            <div class="search-div">
                <div class="form-group has-feedback has-feedback-left input-text">
                    <?php if (isset($_GET['update'])) {  ?>
                        <input style="padding-right:32px" autocomplete="off" value="<?php if (!empty($_SESSION['pos-custid_update'])) {
                                                                                        echo $_SESSION['pos-customer_update'];
                                                                                    } else {
                                                                                        echo 'Walk-in Customer';
                                                                                    } ?>" class="form-control" placeholder="Customer" type="text" id="customer-input">
                    <?php } else { ?>
                        <input style="padding-right:32px" autocomplete="off" value="<?php if (!empty($_SESSION['pos-customer'])) {
                                                                                        echo $_SESSION['pos-name'];
                                                                                    } else {
                                                                                        echo 'Walk-in Customer';
                                                                                    } ?>" class="form-control" placeholder="Customer" type="text" id="customer-input">
                    <?php } ?>
                    <div class="form-control-feedback">
                        <i class="icon-search4 text-size-base"></i>
                    </div>
                    <span id="searchcustomer" class="glyphicon glyphicon-remove-circle"></span>
                    <div id="show-search-customer"></div>
                </div>
                <div class="form-group has-feedback has-feedback-left input-text product-input" style="width: 447px;">
                    <input autocomplete="off" class="form-control" placeholder="Product" type="text" id="product-input">
                    <div class="form-control-feedback">
                        <i class="icon-search4 text-size-base"></i>
                    </div>
                    <span id="searchproduct" class="glyphicon glyphicon-remove-circle"></span>
                    <div id="show-search"></div>
                </div>
                <div class="form-group has-feedback has-feedback-left input-text product-input" hidden>
                    <input class="form-control filterme" style="width: 100px" placeholder="QTY" value="1" type="text" id="quatity-input">
                    <div class="form-control-feedback">
                        <i class="icon-cart text-size-base"></i>
                    </div>
                </div>
                <div class="form-group has-feedback has-feedback-left input-text product-input" hidden>
                    <input autocomplete="off" class="form-control" placeholder="PO#" type="text" value="<?php if (isset($_SESSION['po_no'])) {
                                                                                                            echo $_SESSION['po_no'];
                                                                                                        } ?>" onchange="set_po(this)">
                    <div class="form-control-feedback">
                        <i class="icon-file-text ext-size-base"></i>
                    </div>
                </div>
                <div class="form-group has-feedback has-feedback-left input-text product-input" hidden>
                    <input autocomplete="off" class="form-control" placeholder="Cheque  #" type="text" value="<?php if (isset($_SESSION['check_no'])) {
                                                                                                                    echo $_SESSION['check_no'];
                                                                                                                } ?>" onchange="set_check(this)">
                    <div class="form-control-feedback">
                        <i class="icon-file-text ext-size-base"></i>
                    </div>
                </div>
                <div class="form-switch">
                    <label class="switch">
                        <input type="checkbox" id="togBtn" <?php if ($payment_type) {
                                                                echo  'checked';
                                                            } ?> onclick="change_payment_type('<?= $payment_type ?>')">
                        <div class="slider round">
                            <!--ADDED HTML -->
                            <span class="on">CASH</span>
                            <span class="off">CHARGE</span>
                            <!--END-->
                        </div>
                    </label>
                </div>
                <div class="loader-content product-input" id="show-loader">
                </div>
            </div>
            <div class="left-action">
                <?php if ($_SESSION['session_type'] == "admin") { ?>
                    <a title="Home" class="top-row3-link" href="index.php"> <i class="icon-home2" style="color: #fff"></i></a>
                <?php } else { ?>
                    <a title="Logout" class="top-row3-link" href="../transaction.php?admin-logout=yes"> <i class="icon-switch2"></i></a>
                <?php } ?>
            </div>
        </div>
        
        <div class="main-content">
            <div class="main-right">
                <div class="cart-content">
                    <div class="cart-row">
                        <table class="table-head-cart">
                            <thead>
                                <tr>
                                    <th style="width: 50px;text-align: center;padding: 0px;">#</th>
                                    <th style="width:63%">Name/Description</th>
                                    <th style="width:120px;    padding-right: 12px;text-align:right;">Price</th>
                                    <th style="width:140px;text-align:center;">Quantity</th>
                                    <th style="width: 50px"></th>
                                    <th style="text-align:right;padding-right:40px;width: 150px">Total</th>
                                </tr>
                            </thead>
                        </table>
                        <div id="cart-divs">
                            <table class="table-body">
                                <tbody id="show-cart"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="cart-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-action-group">
                                <div class="btn-action" onclick="receiving()">
                                    <span class="">F6</span>
                                    <div class="btn-action-text">Receiving</div>
                                </div>
                                <div class="btn-action" onclick="view_products()">
                                    <span class="">F12</span>
                                    <div class="btn-action-text">Products</div>
                                </div>
                                <div class="btn-action" onclick="new_product()">
                                    <span class="">F4</span>
                                    <div class="btn-action-text">Add New<br>Product</div>
                                </div>
                                <div class="btn-action" onclick="my_sale()">
                                    <span class="">F11</span>
                                    <div class="btn-action-text">My Sales</div>
                                </div>

                                <div class="btn-action" onclick="reloadLocation()">
                                    <span class="">F5</span>
                                    <div class="btn-action-text">Reload <br> Page</div>
                                </div>
                                <div class="btn-action" onclick="add_discount()">
                                    <span class="">F3</span>
                                    <div class="btn-action-text">Discount</div>
                                </div>
                                <div class="btn-action" onclick="cancel_sale_confirm()">
                                    <span class="">F2</span>
                                    <div class="btn-action-text">Cancel</div>
                                </div>
                                <?php
                                if (!$payment_type) {
                                ?>

                                <?php } ?>
                                <div class="btn-action" onclick="add_payment()">
                                    <span class="">F1</span>
                                    <div class="btn-action-text">Payment</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-left">
                <div class="summary-content">
                    <table class="table-summary">
                        <tr class="td-payable">
                            <td>&nbsp;</td>
                            <td></td>
                        </tr>
                        <tr class="td-payable">
                            <td>Sub Total</td>
                            <td id="show-subtotal" style="font-weight: bold;text-align: right;padding-right: 25px"></td>
                        </tr>
                        <tr class="td-payable">
                            <td>Other Amount</td>
                            <td style="font-weight: bold;text-align: right;padding-right: 25px"><?= number_format($other_amount, 2) ?></td>
                        </tr>
                        <tr class="td-payable">
                            <td>Discount(<span id="show-discount-percent"></span>%)</td>
                            <td style="font-weight: bold;text-align: right;padding-right: 25px" id="show-discount"></td>
                        </tr>
                        <tr class="td-payable">
                            <td>Vat Sales</td>
                            <td id="show-vat-sales" style="font-weight: bold;text-align: right;padding-right: 25px"></td>
                        </tr>
                        <tr class="td-payable">
                            <td>Vat Amount(<?= $tax ?>%)</td>
                            <td id="show-vat-amount" style="font-weight: bold;text-align: right;padding-right: 25px"></td>
                        </tr>
                        <tr class="td-payable">
                            <td>&nbsp;</td>
                            <td></td>
                        </tr>
                    </table>
                    <div class="amount-due-div">
                        <span style="font-size: 14px">Amount Due</span>
                        <div class="grand-total-div">
                            <p id="grand-total"></p>
                        </div>
                    </div>
                </div>
                <div class="summary-action">
                    <div class="stock-inventory">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Employee : <b><?= $_SESSION['fullname'] ?></b></td>
                                </tr>
                                <tr>
                                    <td>No. of sales : <b><?= $counter ?></b></td>
                                </tr>
                                <tr>
                                    <td>Sales Amount : <b><?= number_format($total, 2) ?></b></td>
                                </tr>
                                <tr>
                                    <td>Cash Beginning: <b><?= number_format($beginning, 2) ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                        <div>
                        </div>
                        <div>
                            <div>
                                <div id="modal-payment" class="modal fade" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-pay after-sales">
                                        <div class="modal-content">
                                            <div class="modal-body" id="show-payment">

                                                <form action="#" id="form-payment" class="form-horizontal">

                                                    <input name="sales_type" type="hidden" value="<?= $type ?>" />

                                                    <input class="order-number" style="width:250px;display:none" name="order-number" type="text" id="order-number">

                                                    <?php if (isset($_GET['update'])) { ?>
                                                        <input type="hidden" name="sales_no" value="<?= $sales_no ?>">
                                                        <input type="hidden" name="update-payment">
                                                        <input type="hidden" id="cust_id" value="<?= !empty($_SESSION['pos-custid_update']) ? $_SESSION['pos-custid_update'] : '1' ?>">
                                                    <?php } else { ?>
                                                        <input type="hidden" name="save-payment">
                                                        <input type="hidden" id="cust_id" value="<?= !empty($_SESSION['pos-customer']) ? $_SESSION['pos-customer'] : '1' ?>">
                                                    <?php } ?>

                                                    <div class="bottom-div">
                                                        Amount Due :
                                                        <div id="amount-due-div">
                                                            <span id="amount-due"></span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <input class="form-control"
                                                            type="text"
                                                            autocomplete="off"
                                                            name="payment"
                                                            id="payment"
                                                            placeholder="Payment"
                                                            style="height:70px;font-size:35px;">
                                                    </div>

                                                    <div class="pos-keypad">
                                                        <table>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(1)">1</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(2)">2</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(3)">3</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(4)">4</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(5)">5</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(6)">6</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(7)">7</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(8)">8</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(9)">9</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-warning" onclick="clear_last()">⌫</button></td>
                                                                <td><button type="button" class="btn btn-danger" onclick="select_key('.')">.</button></td>
                                                                <td><button type="button" class="btn btn-primary" onclick="select_key(0)">0</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2"><button type="button" class="btn btn-warning pos-clear" onclick="clear_all()">Clear</button></td>
                                                                <td><button type="submit" class="btn btn-success pos-enter">ENTER</button></td>
                                                            </tr>
                                                        </table>
                                                    </div>


                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="modal-discount" class="modal fade" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"></h5>
                                            </div>
                                            <div class="modal-bodys" id="show-payment">
                                                <form action="#" id="form-discount" class="form-horizontal" data-toggle="validator" role="form">
                                                    <input type="hidden" name="save-discount"></input>
                                                    <div class="row ">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <div class="col-sm-12">
                                                                    <div class="form-group has-feedback-left input-text">
                                                                        <input class="form-control filterme" type="text" autocomplete="off" name="discount" id="discount" placeholder="Discount" type="text">
                                                                        <div class="form-control-feedback">
                                                                            <i class="icon-pencil7 text-size-base"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div style="background: #0899afff;padding-left: 50px;padding-top: 30px;padding-bottom: 30px;margin-top: -20px">
                                                                <table style="width: 60%">
                                                                    <tr>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(1)">1</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(2)">2</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(3)">3</button></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(4)">4</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(5)">5</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(6)">6</button></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(7)">7</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(8)">8</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(9)">9</button></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><button type="button" class="btn btn-warning btn-keyboards" onclick="clear_last2()">x</button></td>
                                                                        <td><button type="button" class="btn btn-danger btn-keyboards" onclick="select_key2('.')">.</button></td>
                                                                        <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key2(0)">0</button></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3"><button type="button" class="btn btn-warning btn-clear" onclick="clear_all2()">Clear</button> <button type="submit" class="btn btn-success btn-clear">ENTER</button></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                                <div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-full">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="title-all"></h5>
                                                <button type="button" class="close " onclick="refresh()" title="Click to close">&times;</button>
                                            </div>
                                            <div id="modal-body" class="modal-body">
                                                <div id="show-data-all"></div>
                                            </div>
                                            <div class="modal-footer" id="footer-sales">
                                                <div class="row pull-right">
                                                    <div class="col-md-6  no-padding ">
                                                        <div id="show-button"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="modal-new" class="modal fade">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"> New Customer Form</h5>
                                            </div>
                                            <div class="modal-bodys" style="padding: 20px">
                                                <form action="#" id="form-customer" class="form-horizontal" data-toggle="validator" role="form">
                                                    <input type="hidden" name="save-customer-sales"></input>
                                                    <div class="form-body">
                                                        <div class="form-group">
                                                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Name</label>
                                                            <div class="col-sm-9">
                                                                <div class="input-group input-group-xlg">
                                                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                                                    <input class="form-control" name="name" placeholder="Name" type="text" data-error=" Name is required." required>
                                                                </div>
                                                                <div class="help-block with-errors"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Address</label>
                                                            <div class="col-sm-9">
                                                                <div class="input-group input-group-xlg">
                                                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                                                    <input class="form-control" name="address" placeholder="Address" type="text" data-error=" Address is required.">
                                                                </div>
                                                                <div class="help-block with-errors"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="exampleInputuname_4" class="col-sm-3 control-label">Contact</label>
                                                            <div class="col-sm-9">
                                                                <div class="input-group input-group-xlg">
                                                                    <span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
                                                                    <input class="form-control" name="contact" placeholder="Contact" type="text" data-error=" Contact is required.">
                                                                </div>
                                                                <div class="help-block with-errors"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <hr>
                                            <div class="modal-footer">
                                                <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b>ESC</b> Close</button>
                                                <button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-plus3"></i></b> Save Customer</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="modal-cancel" class="modal fade">
                                <input type="hidden" id="cancel-input">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"> Confirmation!!!</h5>
                                        </div>
                                        <div class="modal-bodys" align="center">
                                            <h3>Are you sure you wan't to cancel this sale?</h3>
                                        </div>
                                        <hr>
                                        <div class="modal-footer">
                                            <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b>N</i></b> NO</button>
                                            <button type="button" onclick="cancel_sale()" class="btn bg-teal-400 btn-labeled"><b>Y</b> YES</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="modal-other-amount" class="modal fade" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"></h5>
                                    </div>
                                    <div class="modal-bodys" id="show-payment">
                                        <form action="#" id="form-other-amount" class="form-horizontal" data-toggle="validator" role="form">
                                            <input type="hidden" name="save-other-amount"></input>
                                            <div class="row ">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="col-sm-12">
                                                            <div class="form-group has-feedback-left input-text">
                                                                <input class="form-control filterme" type="text" autocomplete="off" name="other_amount" id="other_amount" placeholder="Other Amount" type="text">
                                                                <div class="form-control-feedback">
                                                                    <i class="icon-pencil7 text-size-base"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="background: #0c797bff;padding-left: 50px;padding-top: 30px;padding-bottom: 30px;margin-top: -20px">
                                                        <table style="width: 60%">
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(1)">1</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(2)">2</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(3)">3</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(4)">4</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(5)">5</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(6)">6</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(7)">7</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(8)">8</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(9)">9</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td><button type="button" class="btn btn-warning btn-keyboards" onclick="clear_last3()">x</button></td>
                                                                <td><button type="button" class="btn btn-danger btn-keyboards" onclick="select_key3('.')">.</button></td>
                                                                <td><button type="button" class="btn btn-primary btn-keyboards" onclick="select_key3(0)">0</button></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3"><button type="button" class="btn btn-warning btn-clear" onclick="clear_all3()">Clear</button> <button type="submit" class="btn btn-success btn-clear">ENTER</button></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- New Product Modal -->
                        <div id="modal-add-new" class="modal fade" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
                                        <h5 class="modal-title">New Product Form</h5>
                                    </div>
                                    <div class="modal-body">
                                        <form action="#" id="form-new" class="form-horizontal" data-toggle="validator" role="form">
                                            <input type="hidden" name="save-product">
                                            <div class="form-body" style="padding-top:20px">
                                                <div id="display-msg"></div>

                                                <!-- Product Name -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Product Name</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control currency" autocomplete="off" name="product_name" id="product_name" placeholder="Enter Product Name" type="text" required data-error="Product Name is required.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Product Code -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Product Code</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control currency" autocomplete="off" name="product_code" id="product-code" placeholder="Enter product code" type="text" minlength="8" required data-error="Product Code is required & minimum 8 numbers.">
                                                            <span class="input-group-addon text-teal" style="cursor:pointer" title="Auto Generate"><i class="icon-database-refresh"></i></span>
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Selling Price -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Selling Price</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control filterme" autocomplete="off" name="selling_price" placeholder="Enter selling price" type="text" required data-error="Please enter valid amount.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Supplier Price -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Supplier Price</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control filterme" autocomplete="off" name="supplier_price" placeholder="Enter supplier price" type="text" required data-error="Please enter valid amount.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Beginning Quantity -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Beginning Quantity</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control currency" autocomplete="off" name="quantity" onkeypress="return numbersonly(event)" placeholder="Enter quantity" type="text" required data-error="Please enter valid quantity.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Reorder Level -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Reorder Level</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input class="form-control currency" autocomplete="off" name="critical_qty" onkeypress="return numbersonly(event)" placeholder="Enter quantity" type="text" required data-error="Please enter valid quantity.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                                <!-- Unit -->
                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label">Unit</label>
                                                    <div class="col-sm-9">
                                                        <div class="input-group input-group-xlg">
                                                            <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                                            <input type="text" class="form-control" placeholder="pcs,kg,ml,pack,box,etc." name="unit" required data-error="Please enter unit.">
                                                        </div>
                                                        <div class="help-block with-errors"></div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Products</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="new-sa les"></input>
                        <input type="hidden" id="discount-open">
                        <input type="hidden" id="payment-open">
                        <input type="hidden" id="sales_no">
                        <input type="hidden" id="current-form">
                        <input type="hidden" id="total_cart">
                        <script type="text/javascript" src="../assets/js/core/libraries/jquery.min.js"></script>
                        <script type="text/javascript" src="../assets/js/core/libraries/bootstrap.min.js"></script>
                        <script type="text/javascript" src="../js/pos.js"></script>
                        <script type="text/javascript" src="../js/jquery.scannerdetection.js"></script>
                        <script type="text/javascript" src="../js/jquery.key.js"></script>
                        <script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
                        <script type="text/javascript" src="../assets/js/plugins/forms/inputs/touchspin.min.js"></script>
                        <script src="https://unpkg.com/@zxing/library@latest"></script>



                        <script src="../js/validator.min.js"></script>
                        <script type="text/javascript">
                            let typePos = 1;
                            let payment_type = <?= isset($_SESSION['payment_type']) ? (int)$_SESSION['payment_type'] : 0 ?>;

                            var tax = "<?= $tax ?>";

                            let typingInInput = false;

                            $(document).on('focus', 'input, textarea, select', function() {
                                typingInInput = true;
                            });

                            $(document).on('blur', 'input, textarea, select', function() {
                                typingInInput = false;
                            });


                            function addMoney(el, m) {
                                var payment = 0;
                                var payValue = el;
                                if (m === 'm1') {
                                    payValue = parseFloat($(".m0").text().replace(/,/g, ""));
                                }

                                if ($("#payment").val() === '') {
                                    payment = payValue;
                                } else {
                                    payment = parseFloat($("#payment").val()) + payValue;
                                }
                                $("#payment").val(payment.toFixed(2));
                                var currentVal = parseFloat($('.' + m).text()) + 1;
                                $('.' + m).text(currentVal);
                            }

                            $(document).keypress(function(e) {
                                if (e.which == 13) {
                                    var form = $("#current-form").val();
                                    if (form == '1') {
                                        $("#form-payment").submit();
                                    }
                                    if (form == '2') {
                                        $("#form-discount").submit();
                                    }

                                }
                            });

                            function numbersonly(e) {
                                var unicode = e.charCode ? e.charCode : e.keyCode
                                if (unicode != 8) {
                                    if (unicode < 48 || unicode > 57)
                                        return false
                                }
                            }

                            $(window).load(function() {
                                var session = "<?= $check_session ?>";
                                if (session == "") {
                                    window.location = '../index.php';
                                }
                                $("#spinner_div").fadeOut(1000);

                            });



                            function addBarcodeToCart(barcode) {
                                barcode = barcode.trim().replace(/\s+/g, '').replace(/[^\x20-\x7E]/g, '');
                                console.log("Sending barcode:", barcode);

                                $("#show-loader").html('<i class="icon-spinner2 spinner" style="z-index: 30;position: absolute;font-size: 50px;color: #fff"></i>');

                                $.ajax({
                                    type: 'POST',
                                    url: '../transaction.php',
                                    dataType: 'json',
                                    data: {
                                        save_cartbarcode: 1,
                                        barcode: barcode
                                    },
                                    success: function(msg) {
                                        console.log('AJAX Response:', msg);

                                        if (msg.message === 'save' || msg.message === 'save2') {
                                            total();
                                            view_cart();
                                            const audio = new Audio('../audio/scanner.mp3');
                                            audio.play();
                                        } else if (msg['message'] == 'unsave') {
                                            beep_error();
                                            $.jGrowl('Desired quantity <b>(' + msg['quantity_order'] + ')</b> is greather than quantity left <b>(' + msg['quantity_left'] + ')</b>.Please check your inventory.', {
                                                header: 'Error Notification',
                                                theme: 'alert-styled-right bg-danger'
                                            });
                                            $("#show-loader").html('');
                                        } else if (msg.message === 'unsave2') {
                                            beep_error();
                                            $.jGrowl('Product code does not exist!', {
                                                header: 'Error',
                                                theme: 'bg-danger'
                                            });
                                        } else if (msg['message'] == 'unsave3') {
                                            beep_error();
                                            $.jGrowl('Desired quantity  <b>(' + msg['quantity_order'] + ')</b> is greather than quantity left <b>(' + msg['quantity_left'] + ')</b>.Please check your inventory.', {
                                                header: 'Error Notification',
                                                theme: 'alert-styled-right bg-danger'
                                            });
                                            $("#show-loader").html('');
                                        } else if (msg.message === 'error') {
                                            beep_error();
                                            $.jGrowl(msg.info || 'Something went wrong!', {
                                                header: 'Error',
                                                theme: 'bg-danger'
                                            });
                                        } else {

                                            beep_error();
                                            $.jGrowl('Product Does not exist!', {
                                                header: 'Error',
                                                theme: 'bg-danger'
                                            });
                                        }

                                        $("#show-loader").html('');
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("AJAX error:", status, error, xhr.responseText);


                                        let serverMsg = 'Something went wrong!';
                                        try {
                                            const res = JSON.parse(xhr.responseText);
                                            if (res.info) serverMsg = res.info;
                                        } catch (e) {}

                                        beep_error();
                                        $.jGrowl(serverMsg, {
                                            header: 'Error',
                                            theme: 'bg-danger'
                                        });
                                        $("#show-loader").html('');
                                    }
                                });
                            }


                            $(document).scannerDetection({

                                timeBeforeScanTest: 150,
                                avgTimeByChar: 30,
                                endChar: [9, 13],
                                preventDefault: false,

                                onComplete: function(barcode) {


                                    barcode = barcode.trim()
                                        .replace(/\s+/g, '')
                                        .replace(/[^\x20-\x7E]/g, '');

                                    console.log("SCANNED BARCODE:", barcode);


                                    if ($("input:focus, textarea:focus").length > 0) {
                                        console.log("Scan ignored — typing in input");
                                        return;
                                    }


                                    $("#show-loader").html(
                                        '<i class="icon-spinner2 spinner" style="z-index:30;position:absolute;font-size:50px;color:#fff"></i>'
                                    );


                                    addBarcodeToCart(barcode);
                                },

                                onError: function(string) {
                                    console.log("Scanner error:", string);
                                }
                            });


                            function showPageLoader() {
                                $("#page-loader").css("display", "flex").hide().fadeIn(100);
                            }

                            $(window).on('load', function() {
                                $("#page-loader").fadeOut(100);
                            });

                            function receiving() {
                                showPageLoader();
                                setTimeout(function() {
                                    window.location.href = 'receiving.php';
                                }, 1000);
                            }


                            function profile() {
                                window.location.href = 'profile.php';
                            }


                            $.key('esc', function() {
                                $("#discount-open").val('');
                                $("#payment-open").val('');
                                $("#current-form").val("");
                                if ($("#new-sales").val() == "yes") {
                                    if (is_update == true) {
                                        window.location = 'close-open-register-report.php';
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    $('.modal').modal('hide');
                                }
                            });

                            $.key('f12', function() {
                                view_products();
                            });

                            $.key('f11', function() {
                                $('.modal').modal('hide');
                                my_sale();
                            });

                            // $.key('f6', function() {
                            //     $('.modal').modal('hide');
                            //     $("#modal-new").modal('show');
                            // });

                            $.key('f3', function() {
                                var amount = parseFloat($("#show-discount").text());
                                $("#payment-open").val('');
                                if ($("#discount-open").val() != 'yes') {
                                    $('.modal').modal('hide');
                                    $("#current-form").val('2');
                                    $("#modal-discount").modal('show');
                                    $("#discount").val(amount);
                                    setTimeout(function() {
                                        $("#discount").focus();
                                    }, 500);
                                    $("#discount-open").val('yes');
                                }

                            });

                            $.key('f2', function() {
                                <?php
                                if (isset($_GET['update'])) {
                                ?>
                                    cancel_void()
                                <?php } else { ?>
                                    cancel_sale_confirm()
                                <?php } ?>

                            });

                            $.key('f1', function() {
                                add_payment();
                            });


                            $.key('P', function() {
                                add_payment();
                            });


                            $.key('f4', function() {
                                new_product();
                            });



                            function new_product() {

                                $('#form-new')[0].reset();
                                $('#display-msg').html('');


                                $('#modal-add-new').modal('show');
                                setTimeout(function() {
                                    $('#product_name').focus();
                                }, 500);
                            }


                            $('#form-new').validator().on('submit', function(e) {
                                if (e.isDefaultPrevented()) return false;

                                e.preventDefault();
                                $('#btn-submit').prop('disabled', true);

                                $.ajax({
                                    type: 'POST',
                                    url: '../transaction.php',
                                    data: $('#form-new').serialize(),
                                    dataType: 'json',
                                    success: function(response) {
                                        console.log('AJAX Response:', response);

                                        if (response.status === 'success') {


                                            $.jGrowl('Product successfully added!', {
                                                header: 'Success Notification',
                                                theme: 'alert-styled-right bg-success'
                                            });


                                            $('#form-new')[0].reset();
                                            $('#display-msg').html('');
                                            $('#product_name').focus();

                                            $('#btn-submit').prop('disabled', false);


                                            setTimeout(function() {
                                                $('#modal-add-new').modal('hide');
                                            }, 1500);

                                        } else {
                                            alert('Save failed: ' + response.message);
                                            $('#btn-submit').prop('disabled', false);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('AJAX error:', status, error, xhr.responseText);
                                        $.jGrowl('Something went wrong while saving.', {
                                            header: 'Error Notification',
                                            theme: 'alert-styled-right bg-danger'
                                        });
                                        $('#btn-submit').prop('disabled', false);
                                    }
                                });

                                return false;
                            });




                            function add_payment() {


                                var grand_total = parseFloat($("#grand-total").text());
                                var payment_open = $("#payment-open").val();
                                var total_cart = $("#total_cart").val();
                                // if(typePos === 1){
                                // 	$("#payment").prop('readonly', true);
                                // 	$("#payment").val(grand_total);
                                // }

                                if (total_cart < 1) {
                                    $.jGrowl('No Product added. Please select products before you can add payment.', {
                                        header: 'Error Notification',
                                        theme: 'alert-styled-right bg-danger'
                                    });
                                } else if (grand_total < 1) {
                                    $.jGrowl('Cannot proceed payment. Please check your discount.', {
                                        header: 'Error Notification',
                                        theme: 'alert-styled-right bg-danger'
                                    });
                                } else {
                                    $("#modal-discount").modal('hide');
                                    $("#current-form").val('1');
                                    $("#modal-payment").modal('show');
                                    setTimeout(function() {
                                        $("#payment").focus();
                                    }, 500);
                                    $("#payment-open").val('yes');
                                }
                                $("#amount-due").html($("#grand-total").text());
                                $(".m0").text($("#grand-total").text());

                                /*if ($("#payment-open").val()!='yes') {
                                    
                                    if ( grand_total < 1 ) {
                                        $.jGrowl('Unable to add payment', {
                                            header: 'Error Notification',
                                            theme: 'alert-styled-right bg-danger'
                                        });
                                    }else{
                                        $("#current-form").val('1');
                                        $("#modal-payment").modal('show');
                                        setTimeout(function(){ $("#payment").focus(); }, 500);
                                    }
                                    $("#payment-open").val('yes');
                                }*/
                            }

                            // function addCustomer() {
                            //     $('.modal').modal('hide');
                            //     $("#modal-new").modal('show');
                            // }

                            function reloadLocation() {
                                window.location.reload();
                            }


                            // $.key('f3', function() {
                            // 	$("#cancel-input").val('yes');
                            //     $('.modal').modal('hide');
                            //     $("#modal-cancel").modal('show');
                            // });

                            $.key('y', function() {
                                if (!typingInInput) {
                                    if ($("#cancel-input").val() == 'yes') {
                                        cancel_sale();
                                    }
                                }
                            });

                            $.key('n', function() {
                                if (!typingInInput) {
                                    if ($("#cancel-input").val() == 'yes') {
                                        $("#cancel-input").val("");
                                        $('.modal').modal('hide');
                                    }
                                }
                            });


                            $.key('ctrl+p', function() {
                                $("#product-input").focus();
                            });

                            $.key('ctrl+c', function() {
                                $("#customer-input").focus();
                            });


                            $.key('ctrl+q', function() {
                                alert();
                                $("#quatity-input").focus();
                            });

                            $('.filterme').keypress(function(eve) {
                                if ((eve.which != 46 || $(this).val().indexOf('.') != -1) && (eve.which < 48 || eve.which > 57) || (eve.which == 46 && $(this).caret().start == 0)) {
                                    eve.preventDefault();
                                }
                                $('.filterme').keyup(function(eve) {
                                    if ($(this).val().indexOf('.') == 0) {
                                        $(this).val($(this).val().substring(1));
                                    }
                                });
                            });

                            $('#payment').keyup(function(e) {
                                if (e.keyCode == 8)
                                    var str = $('#payment').val();
                                $('#payment').val(str.substring(0, str.length - 1));
                            });

                            $('#discount').keyup(function(e) {
                                if (e.keyCode == 8)
                                    var str = $('#discount').val();
                                $('#discount').val(str.substring(0, str.length - 1));
                            });

                            $('#form-customer').validator().on('submit', function(e) {
                                if (e.isDefaultPrevented()) {} else {
                                    $(':input[type="submit"]').prop('disabled', true);
                                    var data = $(this).serialize();
                                    $.ajax({
                                        type: 'POST',
                                        url: '../transaction.php',
                                        data: data,
                                        success: function(msg) {
                                            if (msg == '1') {

                                                $.jGrowl('New customer successfully added.', {
                                                    header: 'Success Notification',
                                                    theme: 'alert-styled-right bg-success'
                                                });
                                                setTimeout(function() {
                                                    location.reload();
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

                            $(".order-number").change(function() {
                                localStorage.setItem('orderNumber', $(this).val());
                                $(".order-number").val($(this).val());
                            });

                            if (localStorage.getItem("orderNumber") !== null) {
                                $(".order-number").val(localStorage.getItem("orderNumber"));
                            }

                            function other_amount() {
                                $("#modal-other-amount").modal('show');
                            }

                            function set_po(el) {
                                let po_no = $(el).val();
                                $.ajax({
                                    type: 'POST',
                                    url: '../transaction.php',
                                    data: {
                                        set_po_data: "",
                                        po_no: po_no
                                    },
                                    success: function(msg) {
                                        console.log(msg);
                                    },
                                    error: function(msg) {
                                        alert('Something went wrong!');
                                    }
                                });
                            }

                            function set_check(el) {
                                let check_no = $(el).val();
                                $.ajax({
                                    type: 'POST',
                                    url: '../transaction.php',
                                    data: {
                                        set_check_data: "",
                                        check_no: check_no
                                    },
                                    success: function(msg) {
                                        console.log(msg);
                                    },
                                    error: function(msg) {
                                        alert('Something went wrong!');
                                    }
                                });
                            }
                        </script>
</body>

</html>