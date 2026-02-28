<?php
session_start();
ini_set('max_execution_time', 0);
require('db_connect.php');

$type = isset($_GET['type']) ? 1 : 0;
$check_session  = $_SESSION['is_login_yes'] ?? 0;

/* ================= SETTINGS ================= */
$tax = 0;
$settings = "SELECT tax FROM tbl_settings LIMIT 1";
$result_settings = $db->query($settings);
if ($row = $result_settings->fetch_assoc()) {
	$tax = $row['tax'];
}

/* ================= TODAY SALES ================= */
$today = date("Y-m-d");
$start = strtotime('today GMT');
$date_add = date('Y-m-d', strtotime('+1 day', $start));

$query = "
SELECT 
    tbl_sales.sales_no,
    tbl_sales.sales_date,
    tbl_sales.total_amount,
    tbl_sales.sales_type,
    tbl_sales.sales_status,
    tbl_users.fullname,
    tbl_customer.name
FROM tbl_sales
INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
WHERE tbl_sales.sales_date BETWEEN '$today' AND '$date_add'
AND tbl_sales.user_id = '" . $_SESSION['user_id'] . "'
AND tbl_sales.sales_status != 3
ORDER BY tbl_sales.sales_id DESC
";

$total = 0;
$total_panda = 0;
$counter = 0;

$result = $db->query($query);
while ($row = $result->fetch_assoc()) {
	$counter++;
	if ($row['sales_type'] == 0) {
		$total += $row['total_amount'];
	} else {
		$total_panda += $row['total_amount'];
	}
}

/* ================= UPDATE MODE ================= */
if (isset($_GET['update'])) {

	echo "<script> var is_update = true; </script>";
	$sales_no = intval($_GET['sales_no']);

	$salesSelect = "
    SELECT tbl_sales.sales_date, tbl_users.fullname
    FROM tbl_sales
    INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
    WHERE tbl_sales.sales_no = '$sales_no'
    LIMIT 1
    ";

	$result_sales = $db->query($salesSelect);
	if ($rowSales = $result_sales->fetch_assoc()) {
		$sales_date = $rowSales['sales_date'];
		$user = $rowSales['fullname'];
	}
} else {
	echo "<script> var is_update = false; </script>";
}

/* ================= BEGINNING CASH ================= */
$beginning = 0;
$beginning_query = "SELECT amount FROM tbl_beginning_cash LIMIT 1";
$result_beginning = $db->query($beginning_query);
if ($row = $result_beginning->fetch_assoc()) {
	$beginning = $row['amount'];
}
?>

<style>
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
						<input style="padding-right:32px" autocomplete="off" value="<?php if (!empty($_SESSION['session_supplier'])) {
																						echo $_SESSION['pos-customer_update'];
																					} else {
																						echo 'Walk-in Customer';
																					} ?>" class="form-control" placeholder="Customer" type="text" id="customer-input">

					<?php } else { ?>
						<input style="padding-right:32px" autocomplete="off" value="<?php if (!empty($_SESSION['session_supplier'])) {
																						echo $_SESSION['pos-name'];
																					} else {
																						echo '';
																					} ?>" class="form-control" placeholder="Supplier" type="text" id="customer-input">
					<?php } ?>
					<div class="form-control-feedback">
						<i class="icon-search4 text-size-base"></i>
					</div>
					<span id="searchcustomer" class="glyphicon glyphicon-remove-circle"></span>
					<div id="show-search-customer"></div>
				</div>
				<div class="form-group has-feedback has-feedback-left input-text product-input">
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
				<div class="loader-content product-input" id="show-loader">

				</div>
			</div>
			<div class="left-action">
				<?php if ($_SESSION['session_type'] == "admin") { ?>
					<a title="Home" class="top-row3-link" href="index.php"> <i class="icon-home2" style="color: #fff"></i></a>
				<?php } else { ?>
					<a title="log-out" class="top-row3-link" href="../transaction.php?admin-logout=yes"> <i class="icon-switch2"></i></a>
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
									<th width="text-align:center;">#</th>
									<th width="350px">Name</th>
									<th style="text-align: left;width: 20px;">Unit</th>
									<th style="text-align: right;width: 180px;">Price</th>
									<th style="max-width:70px;text-align: center">Quantity</th>
									<th style='text-align: left;width: 150px;padding-left: 65px'>Total</th>
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
								<div class="btn-action" onclick="pos()">
									<span class="">F7</span>
									<div class="btn-action-text">POS</div>
								</div>
								<div class="btn-action" onclick="view_products()">
									<span class="">F12</span>
									<div class="btn-action-text">Products</div>
								</div>
								<div class="btn-action" onclick="new_product()">
									<span class="">F4</span>
									<div class="btn-action-text">New <br> Product</div>
								</div>
								<div class="btn-action" onclick="new_supplier()">
									<span class="">F6</span>
									<div class="btn-action-text">New <br> Supplier</div>
								</div>
								<div class="btn-action" onclick="reloadLocation()">
									<span class="">F5</span>
									<div class="btn-action-text">Reload <br> Page</div>
								</div>
								<div class="btn-action" onclick="add_discount()">
									<span class="">F3</span>
									<div class="btn-action-text">Discount</div>
								</div>
								<div class="btn-action" onclick="cancel_receving_confirm()">
									<span class="">F2</span>
									<div class="btn-action-text">Cancel</div>
								</div>
								<div class="btn-action" onclick="receive_confirm()">
									<span class="">F1</span>
									<div class="btn-action-text">Submit</div>
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
							<td>Discount</td>
							<td style="font-weight: bold;text-align: right;padding-right: 25px" id="show-discount"></td>
						</tr>

						<tr class="td-payable">
							<td>&nbsp;</td>
							<td></td>
						</tr>
					</table>
					<div class="amount-due-div">
						<span style="font-size: 14px">Total</span>
						<div class="grand-total-div">
							<p id="grand-total"></p>
						</div>
					</div>
				</div>
			</div>
			<div>
				<div>



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
												<div style="background: #eee;padding-left: 50px;padding-top: 30px;padding-bottom: 30px;margin-top: -20px">
													<table style="width: 100%">
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

					<div id="modal_new" class="modal fade">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h5 class="modal-title"> New Supplier Form</h5>
								</div>

								<div class="modal-body">
									<form action="#" id="form-customer" class="form-horizontal" data-toggle="validator" role="form">
										<input type="hidden" name="save-supplier"></input>
										<input type="hidden" name="receiving-input">
										<div class="form-body">
											<div class="form-group">
												<label for="exampleInputuname_4" class="col-sm-3 control-label">Name</label>
												<div class="col-sm-9">
													<div class="input-group input-group-xlg">
														<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
														<input class="form-control" name="supplier_name" placeholder="Name" type="text" data-error=" Name is required." required>
													</div>

													<div class="help-block with-errors"></div>
												</div>
											</div>

											<div class="form-group">
												<label for="exampleInputuname_4" class="col-sm-3 control-label">Address</label>
												<div class="col-sm-9">
													<div class="input-group input-group-xlg">
														<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
														<input class="form-control" name="supplier_address" placeholder="Address" type="text" data-error=" Address is required.">
													</div>

													<div class="help-block with-errors"></div>
												</div>
											</div>

											<div class="form-group">
												<label for="exampleInputuname_4" class="col-sm-3 control-label">Contact</label>
												<div class="col-sm-9">
													<div class="input-group input-group-xlg">
														<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
														<input class="form-control" name="supplier_contact" placeholder="Contact" type="text" data-error=" Contact is required.">
													</div>

													<div class="help-block with-errors"></div>
												</div>
											</div>

										</div>
								</div>
								<hr>
								<div class="modal-footer">
									<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-reading"></i></b> Save Supplier</button>
								</div>
							</div>
							</form>
						</div>
					</div>
				</div>

				<div id="modal-confirm" class="modal fade">
					<input type="hidden" id="cancel-input">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title"> Confirmation!!!</h5>
							</div>
							<div class="modal-bodys" align="center">
								<h3>Are you sure you wan't to receive?</h3>
							</div>
							<hr>
							<div class="modal-footer">
								<button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b>N</i></b> NO</button>
								<button type="button" onclick="add_payment()" class="btn bg-teal-400 btn-labeled"><b>Y</b> YES</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="modal-confirm-receiving" class="modal fade">
				<input type="hidden" id="receiving-cancel-input">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title"> Confirmation!!!</h5>
						</div>
						<div class="modal-bodys" align="center">
							<h3>Are you sure you wan't to cancel this receiving?</h3>
						</div>
						<hr>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b>N</i></b> NO</button>
							<button type="button" onclick="cancel_receving()" class="btn bg-teal-400 btn-labeled"><b>Y</b> YES</button>
						</div>
					</div>
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
		<input type="hidden" id="new-sales"></input>
		<input type="hidden" id="discount-open">
		<input type="hidden" id="receiving-input">
		<script type="text/javascript" src="../assets/js/core/libraries/jquery.min.js"></script>
		<script type="text/javascript" src="../assets/js/core/libraries/bootstrap.min.js"></script>
		<script type="text/javascript" src="../js/receiving.js"></script>
		<script type="text/javascript" src="../js/jquery.scannerdetection.js"></script>
		<script type="text/javascript" src="../js/jquery.key.js"></script>
		<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
		<script src="../js/validator.min.js"></script>


		<!-- <script type="text/javascript" src="../assets/js/plugins/forms/inputs/touchspin.min.js"></script> -->



		<script src="../js/validator.min.js"></script>
		<script type="text/javascript">
			let typingInInput = false;

			$(document).on('focus', 'input, textarea, select', function() {
				typingInInput = true;
			});

			$(document).on('blur', 'input, textarea, select', function() {
				typingInInput = false;
			});



			function changeAmount(eve) {
				if ((eve.which != 46 || $(this).val().indexOf('.') != -1) && (eve.which < 48 || eve.which > 57) || (eve.which == 46 && $(this).caret().start == 0)) {
					eve.preventDefault();
				}
			}
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


			function amountsonly(e) {
				var unicode = e.charCode ? e.charCode : e.keyCode
				if (unicode != 8) {
					if (unicode < 48 || unicode > 57)
						return false
				}
			}

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


			$(document).scannerDetection({
				timeBeforeScanTest: 150,
				avgTimeByChar: 40,
				endChar: [9, 13],
				preventDefault: false,

				onComplete: function(barcode) {
					$("#show-loader").html('<i class="icon-spinner2 spinner" style="z-index:30;position:absolute;font-size:50px;color:#fff"></i>');

					$.ajax({
						type: 'POST',
						url: '../transaction.php',
						dataType: 'json',
						data: {
							save_cart2barcode: 1,
							barcode: barcode
						},
						success: function(msg) {
							$("#show-loader").html('');
							if (msg.message === 'save') {
								total();
								view_cart(msg.user_id); // <-- pass user_id here
								beep_success();
							}
						},

					});
				}
			});


			$.key('n', function() {
				if (!typingInInput) { // <-- only run if not typing
					$('.modal').modal('hide');
					$("#receiving-cancel-input").val("");
					$("#receiving-input").val("");
				}
			});

			$.key('y', function() {
				if (!typingInInput) {
					if ($("#receiving-cancel-input").val() == 'yes') {
						cancel_receving();
					}
					if ($("#receiving-input").val() == 'yes') {
						add_payment();
					}
				}
			});



			function add_payment() {
				$("#modal-confirm").modal('hide');
				$("#show-loader").html('<i class="icon-spinner2 spinner" style="z-index: 30;position: absolute;font-size: 50px;color: #fff"></i>');
				$.ajax({
					type: 'GET',
					url: '../transaction.php',
					data: {
						save_receiving: ""
					},
					success: function(msg) {
						if (msg == '1') {
							beep_success();
							$('.payment-div').prop('onclick', null).off('click');
							$.jGrowl('Receiving successfully submitted.', {
								header: 'Success Notification',
								theme: 'alert-styled-right bg-success'
							});
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							beep_error();
							$("#show-loader").html('');
							$.jGrowl('No product to receive.Please select product before you can submit receiving.', {
								header: 'Success Notification',
								theme: 'alert-styled-right bg-danger'
							});
						}
					},
					error: function(msg) {
						$("#show-loader").html('');
						$.jGrowl('Something went wrong.', {
							header: 'Success Notification',
							theme: 'alert-styled-right bg-danger'
						});
					}
				});

			}

			function showPageLoader() {
				$("#page-loader").css("display", "flex").hide().fadeIn(100);
			}

			$(window).on('load', function() {
				$("#page-loader").fadeOut(100);
			});

			function pos() {
				showPageLoader();
				setTimeout(function() {
					window.location.href = 'pos.php';
				}, 1000);
			}


			$.key('esc', function() {
				if ($("#new-sales").val() == "yes") {
					location.reload();;
				} else {
					$('.modal').modal('hide');
				}
			});


			$.key('f1', function() {

				receive_confirm();
			});
			$.key('f2', function() {
				cancel_receving_confirm();
			});
			$.key('f6', function() {
				new_supplier();
			});
			$.key('f4', function() {
				new_product();
			});

			$.key('f7', function() {
				window.location.href = 'pos.php';
			});




			$.key('f3', function() {
				$('.modal').modal('hide');
				if (parseFloat($("#grand-total").text()) == 0) {
					beep_error();
					$.jGrowl('No product receive.Please select product before you can add discount.', {
						header: 'Error Notification',
						theme: 'alert-styled-right bg-danger'
					});
				} else {
					$("#modal-discount").modal('show');
					setTimeout(function() {
						$("#discount").focus();
					}, 100);
				}

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

								$.jGrowl('New supplier successfully added.', {
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


			function receive_confirm() {
				if ($("#customer-input").val() != "") {
					$("#receiving-input").val('yes');
					$("#modal-confirm").modal('show');
				} else {
					beep_error();
					$.jGrowl('Please select supplier.', {
						header: 'Error Notification',
						theme: 'alert-styled-right bg-danger'
					});
				}

			}

			function closer() {
				location.reload();
			}

			function reloadLocation() {
				window.location.reload();
			}
		</script>
</body>

</html>