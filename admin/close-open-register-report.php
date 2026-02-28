<?php require('includes/header.php'); ?>

<body class="layout-boxed navbar-top">

	<!-- Main navbar -->
	<div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php"><img style="height: 40px!important" src="../images/logo2.png" alt=""></a>

			<ul class="nav navbar-nav visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
			</ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">
			<?php require('includes/sidebar.php'); ?>
		</div>
	</div>
	<!-- /main navbar -->

	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- Page header -->
				<div class="page-header page-header-default">
					<div class="page-header-content">
						<div class="page-title">
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Open Register</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li><i class="icon-chart position-left"></i> Reports</li>
							<li class="active"><i class="icon-dots position-left"></i> Open Register</li>
						</ul>

					</div>
				</div>
				<!-- /page header -->

				<!-- Content area -->
				<div class="content">
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-list text-teal-400"></i> List of Open Registered Sales<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
							<div class="heading-elements">

							</div>
						</div>
						<div style="text-align: right;padding: 20px">
							<button onclick="enter_open()" type="button" class="btn bg-teal-400 btn-labeled btn-rounded heading-btn"><b><i class="icon-enter"></i></b> Close Register</button>
						</div>
						<div class="panel-body product-div2">
							<form id="form-submit">
								<input type="hidden" name="set-close">
								<table class="table datatable-button-html5-basic table-hover table-bordered  ">
									<thead>
										<tr style="border-bottom: 4px solid #ddd;background: #eee">
											<th></th>
											<th>Sales ID</th>
											<th>Bill No.</th>
											<th>Sales Date</th>
											<th>Employee</th>
											<th>Customer</th>
											<th>Amount Due</th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</thead>
									<tr>

										<?php
										$i = 0;
										$today = date("Y-m-d");
										$start = strtotime('today GMT');
										$date_add = date('Y-m-d', strtotime('+1 day', $start));
										$query = "SELECT * FROM tbl_sales INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id  LEFT JOIN tbl_customer ON tbl_sales.cust_id=tbl_customer.cust_id  WHERE  register=0 GROUP BY sales_no ";
										$result = $db->query($query);
										while ($row = $result->fetch_assoc()) {
											$i++;
										?>


											<td width="30px" align="center"><input name="sales_id[]" value="<?= $row['sales_id'] ?>" type="checkbox"></td>

											<td><?= $row['sales_id'] ?></td>
											<td><a href="javascript:;" onclick="view_details(this)" sales-no='<?= $row['sales_no'] ?>'><?= $row['sales_no'] ?></a></td>
											<td><?= date('F d, Y h:i A', strtotime($row['sales_date'])) ?></td>
											<td><?= $row['fullname'] ?></td>
											<td><?= $row['name'] ?></td>
											<td style="text-align: right;"><b><?= number_format($row['total_amount'], 2) ?></b></td>
											<td align="center">
												<?php if ($row['sales_status'] == 3) { ?>
													<label class="label label-danger">Cancelled</label>
												<?php } else { ?>
													<label class="label label-success">Active</label>
												<?php } ?>
											</td>
											<td align="center">
												<?php if ($row['sales_status'] == 3) { ?>
													<a href="javascript:;" data-toggle="tooltip" data-original-title="Set Active" sales_no="<?= $row['sales_no'] ?>" onclick="set_active(this)"><i class="icon-pencil7 position-left text-info"></i> </a>
												<?php } else { ?>
													<!-- <a href="javascript:;" data-toggle="tooltip" data-original-title="Void" sales_no="<?= $row['sales_no'] ?>" onclick="update_sales(this)" ><i class="icon-pencil position-left text-info"></i> </a> -->
													<a sales_no="<?= $row['sales_no'] ?>" onclick="delete_sales(this)" href="javascript:;" data-toggle="tooltip" data-original-title="Cancel" href="javascript:;"><i class="icon-trash position-left text-danger"></i> </a>
												<?php } ?>
											</td>
									</tr>
								<?php } ?>
							</form>
							<?php if ($i == 0) { ?>
								<tr>
									<td colspan="11" align="center">
										<h2>No data found!</h2>
									</td>
								</tr>
							<?php } ?>
							</table>
						</div>
					</div>

				</div>
				<!-- /content area -->
				<?php require('includes/footer-text.php'); ?>

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</div>
	<!-- /page container -->
	<div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="title-all"></h5>
					<a href="javascript:;" class="close" onclick="closer()">&times;</a>
				</div>

				<div class="modal-body">
					<form action="#" id="form-payment" class="form-horizontal" data-toggle="validator" role="form">
						<div id="show-data-all"></div>

				</div>

				<div class="modal-footer" id="footer-sales">
					<div class="row pull-right">
						<div class="col-md-6 ">
							<button type="button" class="btn btn-danger btn-labeled " data-dismiss="modal"><b><i class="icon-cross"></i></b> Close[Esc]</button>
						</div>
						<div class="col-md-6  no-padding ">
							<div id="show-button"></div>
						</div>
					</div>

					</form>
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
					<h3>Are you sure you wan't to cancel this sale?</h3>
					<h5>Sales Details and Product Inventory will updated. </h5>
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
					<h3>Are you sure you wan't to active this sale?</h3>
					<h5>Sales Details and Product Inventory will updated. </h5>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> NO</button>
					<a type="button" id="active_sale" onclick="active_sale()" href="javascript:;" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> YES</a>
				</div>
			</div>
		</div>
	</div>
	</div>
	<div id="modal-confirm3" class="modal fade">
		<input type="hidden" id="cancel-input">
		<input type="hidden" id="sales-id-input">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"> Confirmation!!!</h5>
				</div>
				<div class="modal-bodys">
					<h3>Are you sure you wan't to close this sale?</h3>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> NO</button>
					<button type="button" id="btn-close-register" onclick="set_close()" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> YES</button>
				</div>
			</div>
		</div>
	</div>
	</div>
</body>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>
<script type="text/javascript" src="../js/jquery.key.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>

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
			"order": [
				[1, "desc"]
			],
			"lengthMenu": [
				[10, 25, 50, -1],
				[10, 25, 50, "All"]
			]

		});
	});


	function enter_open() {
		$("#modal-confirm3").modal('show');
	}

	function set_close() {
		//$("#btn-close-register").attr('disabled', true);
		$('#form-submit').submit();
	}

	$('#form-submit').on('submit', function(e) {
		$("#modal-confirm2").modal('hide');
		var data = $(this).serialize();
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: data,
			success: function(msg) {
				$.jGrowl('Sales successfully closed.', {
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
		return false;
	});

	$('#form-sales').on('submit', function(e) {
		$(':input[type="submit"]').prop('disabled', true);
		var data = $("#form-sales").serialize();
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: data,
			success: function(msg) {
				console.log(msg);
				location.reload();
			},
			error: function(msg) {
				alert('Something went wrong!');
			}
		});
		return false;
	});



	function view_user_sales(el) {
		var fullname = $(el).attr('fullname');
		var user_id = $(el).attr('user-id');
		$("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: {
				user_sales_details: "",
				user_id: user_id
			},
			success: function(msg) {
				$("#modal-all").modal('show');
				$("#show-button").html('');
				$("#title-all").html('<b>' + fullname + '</b> Sales Details');
				$("#show-data-all").html(msg);
			},
			error: function(msg) {
				alert('Something went wrong!');
			}
		});
		return false;
	}

	function view_details(el) {
		$("#show-data-all").html("");
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
				$("#title-all").html('OR # : <b class="text-danger">' + sales_no + '</b>');
				$("#show-data-all").html(msg);
			},
			error: function(msg) {
				alert('Something went wrong!');
			}
		});
		return false;
	}

	function update_sales(el) {
		var sales_no = $(el).attr('sales_no');
		$("#spinner_div").fadeIn();
		$.ajax({
			type: 'POST',
			url: '../transaction.php',
			data: {
				update_sales: "",
				sales_no: sales_no
			},
			success: function(msg) {
				window.location = 'pos.php?update=true&sales_no=' + sales_no;

			},
			error: function(msg) {
				alert('Something went wrong!');
			}
		});
		return false;
	}

	function delete_sales(el) {
		var sales_no = $(el).attr('sales_no');
		$("#sales-id-input").val(sales_no);
		$("#cancel-input").val('yes');
		$("#modal-confirm").modal('show');
	}

	/*
	$.key('y', function() {
	   if ($("#cancel-input").val()=='yes') {
	   	    delete_sale();
	   }
	});

	$.key('n', function() {
	   if ($("#cancel-input").val()=='yes') {
	   	$("#cancel-input").val("");
	   	  $('.modal').modal('hide');
	   }
	});*/

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

	function closer() {
		$(".modal").modal('hide');
	}
</script>


</html>