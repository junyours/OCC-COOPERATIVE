<?php require('includes/header.php'); ?>
<style type="text/css">
	#show-search-user {
		background-color: #002639;
		min-height: 300px;
		max-height: 300px;
		overflow-y: auto;
		z-index: 100;
		position: absolute;
		width: 100%;
		display: none;
	}

	#show-search-user::-webkit-scrollbar-track {
		background-color: #1573a7;
	}

	#show-search-user::-webkit-scrollbar {
		width: 12px;
		background-color: #F5F5F5;
	}

	#show-search-user::-webkit-scrollbar-thumb {
		background-color: #0086cf;
	}

	#show-search-customer {
		position: absolute;
		min-height: 300px;
		max-height: 300px;
		overflow-y: scroll;
		background: #002639;
		width: 100%;
		z-index: 10;
		padding: 0px !important;
		display: none;
	}

	#show-search-customer::-webkit-scrollbar-track {
		background-color: #1573a7;
	}

	#show-search-customer::-webkit-scrollbar {
		width: 12px;
		background-color: #F5F5F5;
	}

	#show-search-customer::-webkit-scrollbar-thumb {
		background-color: #0086cf;
	}

	.ul-search {
		list-style-type: none;
		background: #002639;
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
		border-bottom: 2px solid #5d7a88;
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
</style>

<?php

if (
	!isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
	|| $_SESSION['is_login_yes'] != 'yes'
	|| $_SESSION['usertype'] != 1
) {
	die("Unauthorized access.");
}

require('db_connect.php');
$query = "SELECT * FROM tbl_customer";
$customer = $db->query($query);
$query1 = "SELECT * FROM tbl_users";
$employee = $db->query($query1);
$sales_report_type_text = "";
if (isset($_SESSION['sale-report-type'])) {
	if ($_SESSION['sale-report-type'] == 1) {
		$sales_report_type_text = "New Sales";
	}
	if ($_SESSION['sale-report-type'] == 2) {
		$sales_report_type_text = "Delete Sales";
	}
	if ($_SESSION['sale-report-type'] == 3) {
		$sales_report_type_text = "Set Active Sales";
	}
	if ($_SESSION['sale-report-type'] == 11) {
		$sales_report_type_text = "New Product";
	}
	if ($_SESSION['sale-report-type'] == 26) {
		$sales_report_type_text = "Login";
	}
	$sales_report_type = $_SESSION['sale-report-type'];
	$type_query = "AND tbl_history.history_type='" . $_SESSION['sale-report-type'] . "' ";
} else {
	$sales_report_type = "";
	$type_query = "";
	$sales_report_type_text = "All";
}

if (isset($_SESSION['history-report'])) {
	$btn_color = 'bg-danger-400';
} else {
	$btn_color = 'bg-slate-400';
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - System History</h4>
						</div>
					</div>
					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i>Dashboard</a></li>
							<li><a href="javascript:;"><i class="icon-chart position-left"></i>Reports</a></li>
							<li class="active"><i class="icon-dots"></i> System History</li>
						</ul>
						<form class="heading-form" id="form-sales" method="POST">
							<input type="hidden" name="history-report">
							<ul class="breadcrumb-elements">
								<li data-toggle="tooltip" title="Date" style="padding-top: 2px;padding-right: 2px">
									<div class="input-group">
										<span class="input-group-addon"><i class="icon-calendar"></i></span>
										<input style="width: 300px" type="text" autocomplete="off" name="date" class="form-control daterange-buttons " value=" <?php if (isset($_SESSION['history-report']) != "") { ?>   <?= $_SESSION['history-report'] ?> <?php } else { ?> <?= date("m-d-Y") ?> - <?= date("m-d-Y") ?>  <?php } ?>">
									</div>
								</li>
								<input type="hidden" id="input-type" name="type" value="<?= $sales_report_type ?>">
								<li data-toggle="tooltip" title="Type" class="text-center" style="padding-top: 2px;padding-right: 2px;width: auto">
									<div class="btn-group">
										<button type="button" class="btn btn-default btn-rounded"> <span id="span-type"><?= $sales_report_type_text ?></span> </button>
										<button type="button" class="btn btn-default btn-rounded dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-menu-right">
											<li onclick="select_type(this)" status-val="1" status-name="New Sales"><a href="#"><i class="icon-circle text-default-400"></i> New Sales</a></li>
											<li onclick="select_type(this)" status-val="2" status-name="Delete Sales"><a href="#"><i class="icon-circle text-default-400"></i> Delete Sales</a></li>
											<li onclick="select_type(this)" status-val="12" status-name="New Received Product"><a href="#"><i class="icon-circle text-default-400"></i> New Received Product</a></li>
											<li onclick="select_type(this)" status-val="3" status-name="Set Active Sales"><a href="#"><i class="icon-circle text-default-400"></i> Set Active Sales</a></li>
											<li onclick="select_type(this)" status-val="11" status-name="New Product"><a href="#"><i class="icon-circle text-default-400"></i> New Product</a></li>
											<li onclick="select_type(this)" status-val="50" status-name="Capital Share Deposit"><a href="#"><i class="icon-circle text-default-400"></i>Capital Share Deposit</a></li>
											<li onclick="select_type(this)" status-val="51" status-name="savings Deposit"><a href="#"><i class="icon-circle text-default-400"></i>Savings Deposit</a></li>
											<li onclick="select_type(this)" status-val="15" status-name="New Member"><a href="#"><i class="icon-circle text-default-400"></i> New Member</a></li>
											<li onclick="select_type(this)" status-val="26" status-name="login"><a href="#"><i class="icon-circle text-default-400"></i> Login</a></li>
											<li onclick="select_type(this)" status-val="" status-name="all"><a href="#"><i class="icon-circle text-default-400"></i> All</a></li>
										</ul>
									</div>
								</li>
								<li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
								<li style="padding-top: 2px;padding-right: 200px"><button data-toggle="tooltip" title="Clear" type="button" onclick="clear_filter()" class="btn <?= $btn_color ?>"><b><i class="icon-filter4"></i> Clear Filter</b></button></li>
							</ul>
						</form>
					</div>
				</div>
				<div class="content">
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-chart text-teal-400"></i> System History<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
						</div>

						<div class="panel-body product-div2">
							<table class="table datatable-button-html5-basic table-hover table-bordered">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee">
										<th>History ID</th>
										<th>Date</th>
										<th>History Type</th>
										<th>Details</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (isset($_SESSION['history-report'])) {
										$from = $_SESSION['history-report-from'];
										$to = $_SESSION['history-report-to'];
										$today = date("Y-m-d");
										$start = strtotime('today GMT');
										$date_add = date('Y-m-d', strtotime('+1 day', $start));


										if ($today == $from || $today == $to) {
											$query = "SELECT * FROM tbl_history  WHERE  date_history BETWEEN  '$today' AND '$date_add' $type_query ";
										} else {
											$query = "SELECT * FROM tbl_history  WHERE  date_history BETWEEN  '$from' AND '$to' $type_query ";
										}
									} else {
										$query = "SELECT * FROM tbl_history  ";
										$result = $db->query($query);
									}
									$i = 0;

									$result = $db->query($query);
									while ($row = $result->fetch_assoc()) {
										$i++;


										$details = json_decode($row['details'] ?? '');
										if (!$details) $details = new stdClass();



										$user_id = $details->user_id ?? 0;
										$employee_name = 'Unknown User';

										if ($user_id) {
											$result_user = $db->query("SELECT fullname FROM tbl_users WHERE user_id='$user_id' LIMIT 1");
											if ($result_user && $result_user->num_rows > 0) {
												$data_user = $result_user->fetch_assoc();
												$employee_name = $data_user['fullname'] ?? 'Unknown User';
											}
										}

										// ---------------- SAFE CUSTOMER FETCH ----------------
										$customer_name = 'Unknown Member';
										if (!empty($details->cust_id)) {
											$result_cust = $db->query("SELECT name FROM tbl_customer WHERE cust_id='{$details->cust_id}' LIMIT 1");
											if ($result_cust && $result_cust->num_rows > 0) {
												$data_cust = $result_cust->fetch_assoc();
												$customer_name = $data_cust['name'] ?? 'Unknown Member';
											}
										}

										// ---------------- HISTORY TYPES ----------------
										if ($row['history_type'] == 1) {
											$history_type = "Sales";
											$details_data = '<i class="icon-cart text-teal-400"></i> Bill No.:' . ($details->sales_no ?? '-') .
												' <i class="icon-user text-teal-400"></i> Employee : ' . $employee_name;
										} elseif ($row['history_type'] == 2) {
											$history_type = "Delete Sales";
											$details_data = '<i class="icon-trash text-teal-400"></i> Bill No.:' . ($details->sales_no ?? '-') .
												' <i class="icon-user text-teal-400"></i> Employee : ' . $employee_name;
										} elseif ($row['history_type'] == 11) {
											$history_type = "New Product";
											$details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: ' . ($details->product_id ?? '-') .
												' <i class="icon-user text-teal-400"></i> Employee : ' . $employee_name;
										} elseif ($row['history_type'] == 12) {
											$history_type = "Received Product";
											$details_data = '<i class="icon-download text-teal-400"></i> Receiving NO: ' . ($details->receiving_no ?? '-') .
												' <i class="icon-user text-teal-400"></i> Employee : ' . $employee_name;
										} elseif ($row['history_type'] == 15) {
											$history_type = "New Member";
											$details_data = '<i class="icon-users text-teal-400"></i> Member Name: ' . ($customer_name ?? '-') .
												' <i class="icon-user text-teal-400"></i> Employee : ' . $employee_name;
										} elseif ($row['history_type'] == 26) {
											$history_type = "Login";
											$details_data = '<i class="icon-calendar text-teal-400"></i> Date: ' . $row['date_history'] .
												' <i class="icon-user text-teal-400"></i> User : ' . $employee_name;
										} elseif ($row['history_type'] == 55) {
											$history_type = "Loan Application";
											$member_name = 'Unknown Member';
											if (!empty($details->member_id)) {
												$mid = intval($details->member_id);
												$result_member = $db->query("
                                            SELECT CONCAT(first_name,' ',last_name) AS name
                                            FROM tbl_members
                                            WHERE member_id = '$mid'
                                            LIMIT 1
                                             ");
												if ($result_member && $result_member->num_rows > 0) {
													$data_member = $result_member->fetch_assoc();
													$member_name = $data_member['name'];
												}
											}
											// Loan Type Name
											$loan_type_name = '-';
											if (!empty($details->loan_type_id)) {
												$ltid = intval($details->loan_type_id);
												$result_lt = $db->query("
                                                 SELECT loan_type_name
                                                 FROM loan_types
                                                 WHERE loan_type_id = '$ltid'
                                                  LIMIT 1
                                                  ");
												if ($result_lt && $result_lt->num_rows > 0) {
													$data_lt = $result_lt->fetch_assoc();
													$loan_type_name = $data_lt['loan_type_name'];
												}
											}
											$amount = isset($details->requested_amount)
												? '₱' . number_format($details->requested_amount, 2)
												: '-';
											$purpose = $details->purpose ?? '-';
											$details_data =
												'<i class="icon-users text-teal-400"></i> Member: ' . $member_name .
												' <i class="icon-file-text text-teal-400"></i> Loan Type: ' . $loan_type_name .
												' <i class="icon-coin-dollar text-teal-400"></i> Amount: ' . $amount .
												' <i class="icon-book text-teal-400"></i> Purpose: ' . $purpose .
												' <i class="icon-user text-teal-400"></i> Employee: ' . $employee_name;
										} elseif ($row['history_type'] == 51) {
											$history_type = "Savings Deposit";
											$member_name = 'Unknown Member';
											if (!empty($details->member_id)) {
												$mid = intval($details->member_id);
												$result_member = $db->query("
												    SELECT CONCAT(first_name,' ',last_name) AS name
												    FROM tbl_members
												    WHERE member_id = '$mid'
												    LIMIT 1
												");
												if ($result_member && $result_member->num_rows > 0) {
													$data_member = $result_member->fetch_assoc();
													$member_name = $data_member['name'];
												}
											}
											$amount = isset($details->amount)
												? '₱' . number_format($details->amount, 2)
												: '-';
											$reference = $details->reference ?? '-';
											$details_data =
												'<i class="icon-users text-teal-400"></i> Member: ' . $member_name .
												' <i class="icon-file-text text-teal-400"></i> Ref#: ' . $reference .
												' <i class="icon-coin-dollar text-teal-400"></i> Amount: ' . $amount .
												' <i class="icon-user text-teal-400"></i> Employee: ' . $employee_name;
										} elseif ($row['history_type'] == 50) {
											$history_type = "Capital Share Deposit";
											$member_name = 'Unknown Member';
											if (!empty($details->member_id)) {
												$mid = intval($details->member_id);
												$result_member = $db->query("
												    SELECT CONCAT(first_name,' ',last_name) AS name
												    FROM tbl_members
												    WHERE member_id = '$mid'
												    LIMIT 1
												");
												if ($result_member && $result_member->num_rows > 0) {
													$data_member = $result_member->fetch_assoc();
													$member_name = $data_member['name'];
												}
											}
											$amount = isset($details->amount)
												? '₱' . number_format($details->amount, 2)
												: '-';
											$reference = $details->reference ?? '-';
											$details_data =
												'<i class="icon-users text-teal-400"></i> Member: ' . $member_name .
												' <i class="icon-file-text text-teal-400"></i> Ref#: ' . $reference .
												' <i class="icon-coin-dollar text-teal-400"></i> Amount: ' . $amount .
												' <i class="icon-user text-teal-400"></i> Employee: ' . $employee_name;
										} else {
											$history_type = $row['history_type'];
											$details_data = "Not Set";
										}
									?>
										<tr>
											<td><?= $row['history_id'] ?></td>
											<td><?= $row['date_history'] ?></td>
											<td><?= $history_type ?></td>
											<td><?= $details_data ?></td>
										</tr>
									<?php } ?>

									<?php if ($i == 0) { ?>
										<tr>
											<td colspan="4" align="center">
												<h2>No data found!</h2>
											</td>
										</tr>
									<?php } ?>
								</tbody>
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

		// Basic initialization
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
		var sales_no = $(el).attr('sales-no');
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
				$("#title-all").html('Bill No. : <b class="text-danger">' + sales_no + '</b>');
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

	function clear_filter() {
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: {
				clear_filter_history: ""
			},
			success: function(msg) {
				location.reload();
			}

		});
	}

	function select_type(el) {
		$("#span-type").html($(el).attr('status-name'));
		$("#input-type").val($(el).attr('status-val'));
	}
</script>


</html>