<?php require('includes/header.php'); ?>
<?php
$cust_id = $_GET['cust_id'];
$query = "SELECT * FROM tbl_customer WHERE cust_id='$cust_id'";
$result = $db->query($query);

while ($row = $result->fetch_assoc()) {
	$name = $row['name'];
	$address = $row['address'];
	$contact = $row['contact'];
	$cust_id = $row['cust_id'];
}
?>

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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Customer Details</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li class="active">Employee</li>
						</ul>

						<ul class="breadcrumb-elements">
							<li><a href="javascript:;" onclick="update_product()"><i class="icon-pencil3 position-left"></i> Update Details</a></li>
						</ul>
					</div>
				</div>
				<!-- /page header -->

				<!-- Content area -->
				<div class="content">
					<div class="row">
						<div class="col-xs-4">
							<div class="panel panel-white border-top-xlg border-top-teal-400">
								<div class="panel-heading">
									<h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Details</h6>
								</div>
								<!-- <input type="text" id="myInputTextField"> -->
								<div class="panel-body">
									<table class="table text-nowrap table-bordered  ">

										<tr class="border-double">
											<td class="text-size-small" style="width: 120px">Customer ID</td>
											<td>0432<?= $cust_id ?> </td>
										</tr>
										<!-- <tr class="border-double">
											<td class="text-size-small">Product Code</td>
											<td><?= $product_code ?> </td>
										</tr> -->
										<tr class="border-double">
											<td class="text-size-small">Name</td>
											<td><?= $name ?> </td>
										</tr>
										<tr class="border-double">
											<td class="text-size-small">Address</td>
											<td><?= $address ?> </td>
										</tr>
										<tr class="border-double">
											<td class="text-size-small">Contact</td>
											<td><?= $contact ?> </td>
										</tr>
									</table>
								</div>
							</div>
						</div>

						<div class="col-xs-8">
							<div class="panel panel-white border-top-xlg border-top-teal-400">
								<div class="panel-heading">
									<h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Sales</h6>
								</div>
								<!-- <input type="text" id="myInputTextField"> -->
								<div class="panel-body">
									<div id="text-alert" style="text-align: center;width: 100%;display: none">
										<h3>No sales found!</h3>
									</div>
									<table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
										<thead>
											<tr style="border-bottom: 4px solid #ddd;background: #eee">
												<th>Sales ID</th>
												<th>Sales No.</th>
												<th>Employee</th>
												<th>Sub Total</th>
												<th>Discount</th>
												<th>Amount Due</th>
											</tr>
										</thead>
										<tr>
											<?php

											$today = date("Y-m-d");
											$start = strtotime('today GMT');
											$date_add = date('Y-m-d', strtotime('+1 day', $start));
											$counts = 0;
											$query = "SELECT * FROM tbl_sales INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id   WHERE   tbl_sales.cust_id='" . $cust_id . "' ";
											$result = $db->query($query);
											while ($row = $result->fetch_assoc()) {
												$counts++;
											?>
												<td><?= $row['sales_id'] ?></td>
												<td>9087956<?= $row['sales_no'] ?></td>
												<td><?= $row['fullname'] ?></td>
												<td style="text-align: right;"><?= number_format($row['subtotal'], 2) ?></td>
												<td style="text-align: right;"><?= number_format($row['discount'], 2) ?></td>
												<td style="text-align: right;"><?= number_format($row['total_amount'], 2) ?></td>
										</tr>
									<?php } ?>
									<?php if ($counts == 0) { ?>
										<tr>
											<td colspan="10" align="center">
												<h2>No data found!</h2>
											</td>
										</tr>
									<?php } ?>
									</tbody>
									</table>
								</div>
							</div>

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
</body>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script src="../js/validator.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script type="text/javascript">
	var counts = '<?= $counts ?>';
	if (counts == 0) {
		{
			$("#table-product").hide();
			$("#text-alert").show();
		}
	}
	$(function() {

		// Table setup
		// ------------------------------
		// Setting datatable defaults
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
				[0, "desc"]
			],
			"lengthMenu": [
				[5, 25, 50, -1],
				[5, 25, 50, "All"]
			],
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
						extend: 'excelHtml5',
						className: 'btn btn-default',
						text: '<i class="icon-copy3 position-left"></i> Excel'
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
			}
		});
	});

	$('#form-generate').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			//$(':input[type="submit"]').append('&nbsp; <i class="fa fa-spinner fa-spin"></i>');
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {
					console.log(msg);
					$("#show-code").html(msg);
					$(':input[type="submit"]').prop('disabled', false);

				},
				error: function(msg) {
					alert('Something went wrong!');
				}
			});
			return false;
		}
	});

	$('#form-damage').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			$(':input[type="submit"]').append('<span id="loader">&nbsp;&nbsp; <i class="icon-spinner2 spinner"></i></span>');
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {

					if (msg == 1) {
						$.jGrowl('Damage product succesfully save.', {
							header: 'Success Notification',
							theme: 'alert-styled-right bg-info'
						});
						setTimeout(function() {
							location.reload()
						}, 1500);
					} else if (msg == 2) {
						$.jGrowl('Quantity entered is greater than quantity left.', {
							header: 'Error Notification',
							theme: 'alert-styled-right bg-warning'
						});
						setTimeout(function() {
							$("#loader").html("");
						}, 1000);

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

	function print_receipt() {
		var contents = $("#show-code").html();
		var frame1 = $('<iframe />');
		frame1[0].name = "frame1";
		frame1.css({
			"position": "absolute",
			"top": "-1000000px"
		});
		$("body").append(frame1);
		var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
		frameDoc.document.open();
		//Create a new HTML document.
		frameDoc.document.write('<html><head><title></title>');
		frameDoc.document.write('</head><body>');
		//Append the external CSS file.
		/* frameDoc.document.write('<link href="css/print.css" rel="stylesheet" type="text/css" />');*/
		frameDoc.document.write(contents);
		frameDoc.document.write('</body></html>');
		frameDoc.document.close();
		setTimeout(function() {
			window.frames["frame1"].focus();
			window.frames["frame1"].print();
			frame1.remove();
		}, 500);
	}

	function add_damage() {
		//$('').appendTo('body');
		$("#modal_damage").modal('show');
	}

	function closer() {
		window.location = 'products.php';
	}
</script>

</html>