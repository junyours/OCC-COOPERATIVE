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
</style>
<?php

require('../db_connect.php');
if (isset($_SESSION['inventory-report-product']) != "") {
	$user_query_name = "SELECT * FROM tbl_products WHERE product_id='" . $_SESSION['inventory-report-product'] . "' ";
	$user_queryname = $db->query($user_query_name);
	while ($row = $user_queryname->fetch_assoc()) {
		$selected_product = $row['product_name'];
	}
} else {
	$selected_product = "";
}


$query1 = "SELECT * FROM tbl_users";
$employee = $db->query($query1);

if (isset($_SESSION['inventory-report-product'])) {
	$btn_color = 'bg-danger-400';
} else {
	$btn_color = 'bg-slate-400';
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - Product Available Report</h4>
						</div>
					</div>
					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li><a href="javascript:;"><i class="icon-chart position-left"></i>Reports</a></li>
							<li class="active">Product Available</li>
						</ul>
					</div>
				</div>
				<div class="content">
					<div class="panel panel-body ">
						<form class="heading-form" id="form-sales" method="POST">
							<input type="hidden" name="submit-invetory">
							<ul class="breadcrumb-elements" style="float:left">

								<li data-toggle="tooltip" title="Products" style="padding-top: 2px;padding-right: 2px">
									<div class="btn-group">
										<input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['inventory-report-product']) != "") {
																							echo  $_SESSION['inventory-report-product'];
																						} ?>" name="user_id" id="user_id">
										<input style="width: 350px" autocomplete="off" type="search" class="form-control" id="user-input" value="<?php if (isset($_SESSION['inventory-report-product']) != "") {
																																						echo  $selected_product;
																																					} ?>" name="username">
										<span id="searchclearuser" class="glyphicon glyphicon-remove-circle"></span>
										<div id="show-search-user"></div>
									</div>
								</li>
								<li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
								<li data-toggle="tooltip" title="Clear" style="padding-top: 2px;padding-right: 20px"><button type="button" onclick="clear_filter()" class="btn <?= $btn_color ?>"><b><i class="icon-filter4"></i> Filter</b></button></li>
								<li data-toggle="tooltip" title="Export Excel" style="padding-top: 2px;padding-right: 2px;margin-left:-18px"><button type="button" onClick="window.location.href='downloadPDF.php?type=product-available&export=excel&backlink=product-available.php'" class="btn bg-success-700"><b><i class="icon-file-excel"></i></b></button></li>
								<li data-toggle="tooltip" title="Export PDF" style="padding-top: 2px;padding-right: 2px"><button type="button" onClick="window.location.href='downloadPDF.php?type=product-available&export=pdf&backlink=product-available.php'" class="btn bg-danger-700"><b><i class="icon-file-pdf"></i></b></button></li>
							</ul>
						</form>
					</div>
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-chart text-teal-400"></i> List of Receiving report <a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
						</div>

						<div class="panel-body product-div2">

							<table class="table datatable-button-html5-basic table-hover table-bordered  ">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee">
										<th>Image</th>
										<th>Product Code</th>
										<th>Product ID</th>
										<th>Product Name</th>
										<th> In Stock</th>
									</tr>
								</thead>
								<tr>
									<?php

									if (isset($_SESSION['inventory-report-product'])) {

										$query = "SELECT * FROM tbl_products  WHERE product_id='" . $_SESSION['inventory-report-product'] . "'  ORDER BY product_name ASC ";
									} else {
										$query = "SELECT * FROM tbl_products  ORDER BY product_name ASC";
									}
									$i = 0;
									$result = $db->query($query);
									$total = 0;
									while ($row = $result->fetch_assoc()) {
										$i++;
										$image = $row['image'];
										if ($image != "") {
											$image_file = '../uploads/' . $image;
										} else {
											$image_file = '../images/no-image.png';
										}

									?>
										<td> <img alt="<?= $image_file ?>" style="width: 90px;height: 90px;border: 2px solid #eee" src="<?= $image_file ?>" /> </td>
										<td><img alt="<?= $product_code ?>" src="barcode.php?codetype=Code39&size=40&text=<?= $row['product_code']; ?>&print=true" /></td>
										<td style="width: 160px">21324<?= $row['product_id']; ?></td>
										<td><b><?= $row['product_name']; ?></b></td>
										<td style="text-align: center;width: 140px"><?= $row['quantity']; ?></td>
								</tr>
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
				<form action="#" id="form-payment" class="form-horizontal" data-toggle="validator" role="form">
					<div id="show-data-all"></div>

			</div>

			<div class="modal-footer" id="footer-sales">
				<div class="row pull-right">
					<div class="col-md-6  no-padding ">
						<div id="show-button"></div>
					</div>
				</div>

				</form>
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

		$('.datatable-button-html5-basic').DataTable({
			"bDestroy": true,
			"aaSorting": [],
			"ordering": false,
			"searching": false,
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



	$("#user-input").keyup(function() {
		$("#show-search-user").show();
		var keywords = $(this).val();
		if (keywords != "") {
			$.ajax({
				type: 'GET',
				url: '../transaction.php',
				data: {
					search_products: "",
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
				search_products: "",
				keywords: ""
			},
			success: function(msg) {
				console.log(msg);
				$("#show-search-user").html(msg);
			},
			error: function(msg) {
				alert('Something went wrong!');
			}
		});
	});





	function select_product(el) {
		var user_id = $(el).attr('product_id');
		var name = $(el).attr('product_name');
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
				clear_filter_inventory: ""
			},
			success: function(msg) {
				location.reload();
			}

		});
	}
</script>


</html>