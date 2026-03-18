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
		height: 25px;
		width: 25px;
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
</style>

<?php

require('../db_connect.php');
if (isset($_SESSION['receiving-report-user']) != "") {
	$user_query_name = "SELECT * FROM tbl_users WHERE user_id='" . $_SESSION['receiving-report-user'] . "' ";
	$user_queryname = $db->query($user_query_name);
	while ($row = $user_queryname->fetch_assoc()) {
		$selected_user = $row['fullname'];
	}
} else {
	$selected_user = "";
}

if (isset($_SESSION['receiving-report-customer']) != "") {
	$customer_query_name = "SELECT * FROM tbl_supplier WHERE supplier_id='" . $_SESSION['receiving-report-customer'] . "' ";
	$customer_queryname = $db->query($customer_query_name);
	while ($row = $customer_queryname->fetch_assoc()) {
		$selected_customer = $row['supplier_name'];
	}
} else {
	$selected_customer = "";
}


if (isset($_SESSION['receiving-report-customer']) != "") {
	$customer_query = "AND tbl_receivings.supplier_id='" . $_SESSION['receiving-report-customer'] . "' ";
} else {
	$customer_query = "";
}
$query = "SELECT * FROM tbl_supplier";
$customer = $db->query($query);
$query1 = "SELECT * FROM tbl_users";
$employee = $db->query($query1);

if (isset($_SESSION['receiving-report'])) {
	$btn_color = 'bg-danger-400';
} else {
	$btn_color = 'bg-slate-400';
}

if (isset($_SESSION['receiving-date-required'])) {
	$checkbox = 'checked';
} else {
	$checkbox = '';
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
		/* prevent text from wrapping to next line */
	}
</style>

<body class="layout-boxed navbar-top">
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - Receiving Report</h4>
						</div>
					</div>
					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li><a href="javascript:;">Reports</a></li>
							<li class="active">Receiving</li>
						</ul>
						<form class="heading-form" id="form-sales" method="POST">
							<input type="hidden" name="submit-receiving">
							<ul class="breadcrumb-elements">
								<li style="padding-top: 2px;padding-right: 2px">
									<div class="input-group">
										<span data-toggle="tooltip" title="Enable/disable Receiving Date" class="input-group-addon">
											<label class="containers">
												<input type="checkbox" name="date-required" <?= $checkbox ?>>
												<span class="checkmark"></span>
											</label>
										</span>
										<input type="text" autocomplete="off" name="date" class="form-control daterange-buttons" value=" <?php if (isset($_SESSION['receiving-report']) != "") { ?>   <?= $_SESSION['receiving-report'] ?> <?php } else { ?> <?= date("m-d-Y") ?> - <?= date("m-d-Y") ?>  <?php } ?>">
									</div>
								</li>

								<li data-toggle="tooltip" title="Supplier" style="padding-top: 2px;padding-right: 2px">
									<div class="btn-group">
										<input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['receiving-report-customer']) != "") {
																							echo  $_SESSION['receiving-report-customer'];
																						} ?>" name="cust_id" id="cust_id">
										<input autocomplete="off" type="search" class="form-control" id="customer-input" value="<?php if (isset($_SESSION['receiving-report-customer']) != "") {
																																	echo  $selected_customer;
																																} ?>" name="custname">
										<span id="searchclear" class="glyphicon glyphicon-remove-circle"></span>
										<div id="show-search-customer"></div>
									</div>

								</li>
								<li data-toggle="tooltip" title="Employee" style="padding-top: 2px;padding-right: 2px">
									<div class="btn-group">
										<input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['receiving-report-user']) != "") {
																							echo  $_SESSION['receiving-report-user'];
																						} ?>" name="user_id" id="user_id">
										<input autocomplete="off" type="search" class="form-control" id="user-input" value="<?php if (isset($_SESSION['receiving-report-user']) != "") {
																																echo  $selected_user;
																															} ?>" name="username">
										<span id="searchclearuser" class="glyphicon glyphicon-remove-circle"></span>
										<div id="show-search-user"></div>
									</div>
								</li>
								<li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
								<li data-toggle="tooltip" title="Clear" style="padding-top: 2px;padding-right: 20px"><button type="button" onclick="clear_filter()" class="btn <?= $btn_color ?>"><b><i class="icon-filter4"></i> Filter</b></button></li>
							</ul>
						</form>
					</div>
				</div>
				<div class="content">
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-list text-teal-400"></i> List of Receiving report <a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
						</div>

						<div class="panel-body product-div2">

							<table class="table datatable-button-html5-basic table-hover table-bordered  ">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee">
										<th>Receiving ID</th>
										<th>Receiving No.</th>
										<th>Date</th>
										<th>Employee</th>
										<th>Supplier</th>
										<th>Sub Total</th>
										<th>Discount</th>
										<th>Total</th>
									</tr>
								</thead>
								<tr>
									<?php
									$total = 0;
									$i = 0;

									// Determine date range
									$today = date("Y-m-d");
									$start = strtotime('today GMT');
									$date_add = date('Y-m-d', strtotime('+1 day', $start));

									$user_query = isset($_SESSION['receiving-report-user']) ? "AND r.user_id='" . $_SESSION['receiving-report-user'] . "' " : "";
									$customer_query = isset($_SESSION['receiving-report-customer']) ? "AND r.supplier_id='" . $_SESSION['receiving-report-customer'] . "' " : "";

									// Build query
									if (isset($_SESSION['receiving-report'])) {
										if (isset($_SESSION['receiving-date-required'])) {
											$from = $_SESSION['receiving-report-from'];
											$to = $_SESSION['receiving-report-to'];

											if ($today == $from || $today == $to) {
												$date_from = $today;
												$date_to = $date_add;
											} else {
												$date_from = $from;
												$date_to = $to;
											}
										} else {
											$date_from = null;
											$date_to = null;
										}
									} else {
										$date_from = null;
										$date_to = null;
									}

									// Base query
									$query = "SELECT 
            r.receiving_no,
            MAX(r.receiving_id) AS receiving_id,
            MAX(r.date_received) AS date_received,
            u.fullname,
            s.supplier_name,
            SUM(r.receiving_quantity * r.receiving_price) AS sub_total,
            SUM(r.discount) AS discount
          FROM tbl_receivings r
          INNER JOIN tbl_users u ON r.user_id=u.user_id
          LEFT JOIN tbl_supplier s ON r.supplier_id=s.supplier_id
          WHERE 1=1
          $user_query
          $customer_query";

									if ($date_from && $date_to) {
										$query .= " AND r.date_received BETWEEN '$date_from' AND '$date_to' ";
									}

									$query .= " GROUP BY r.receiving_no, u.fullname, s.supplier_name
            ORDER BY MAX(r.receiving_id) DESC";

									$result = $db->query($query);
									$total = 0;
									while ($row = $result->fetch_assoc()) {
										$i++;
										$sub_total = $row['sub_total'];
										$discount = $row['discount'];
										$total += $sub_total;
									?>
								<tr>
									<td>08945<?= $row['receiving_id'] ?></td>
									<td><a href="javascript:;" onclick="view_details(this)" reciving-no='<?= $row['receiving_no'] ?>'><?= $row['receiving_no'] ?></a></td>
									<td><?= date("F d, Y h:i:s", strtotime($row['date_received'])); ?></td>
									<td><?= $row['fullname'] ?></td>
									<td><?= $row['supplier_name'] ?></td>
									<td style="text-align: right;"><?= number_format($sub_total, 2) ?></td>
									<td style="text-align: right;"><?= number_format($discount, 2) ?></td>
									<td style="text-align: right;"><b><?= number_format($total, 2) ?></b></td>
								</tr>
							<?php } ?>

							<?php if ($i == 0) { ?>
								<tr>
									<td colspan="8" align="center">
										<h2>No data found!</h2>
									</td>
								</tr>
							<?php } ?>

							<?php if ($i == 0) { ?>
								<tr>
									<td colspan="7" align="center">
										<h2>No data found!</h2>
									</td>
								</tr>
							<?php } ?>
							</table>
						</div>
					</div>

				</div>
				<?php require('includes/footer-text.php'); ?>

			</div>

		</div>

	</div>
</body>
<?php require('includes/footer.php'); ?>
<div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-full">
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
<script type="text/javascript">
	$(function() {
		$('[data-toggle="tooltip"]').tooltip();
		$.extend($.fn.dataTable.defaults, {
			autoWidth: false,
			dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
			language: {
				search: '<span>Filter:</span> _INPUT_',
				searchPlaceholder: 'Type to filter...',
				lengthMenu: '<span>Show:</span> _MENU_',
				paginate: {
					'first': 'First',
					'last': 'Last',
					'next': '&rarr;',
					'previous': '&larr;'
				}
			}
		});

		$('.datatable-button-html5-basic').DataTable({
			buttons: {
				dom: {
					button: {
						className: 'btn btn-default'
					}
				},
				buttons: [{
						extend: 'copyHtml5',
						className: 'btn btn-default',
						text: '<i class="icon-copy3 position-left"></i> Copy'
					},
					{
						extend: 'csvHtml5',
						className: 'btn btn-default',
						text: '<i class="icon-copy3 position-left"></i> CSV'
					},
					{
						extend: 'pdfHtml5',
						className: 'btn btn-default',
						text: '<i class="icon-copy3 position-left"></i> pdf'
					}

				]
			},
			"order": [
				[0, "desc"]
			]
		});
	});
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
		var receiving_no = $(el).attr('reciving-no');
		$("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: {
				receiving_report_details: "",
				receiving_no: receiving_no
			},
			success: function(msg) { //console.log(msg);
				$("#modal-all").modal('show');
				$("#show-button").html('');
				$("#title-all").html('Receiving No. : <b class="text-danger">' + receiving_no + '</b>');
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
					search_supplier: "",
					keywords: keywords
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
				search_supplier: "",
				keywords: ""
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

	function clear_filter() {
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: {
				clear_filter_receiving: ""
			},
			success: function(msg) {
				location.reload();
			}

		});
	}
</script>


</html>