<?php require('includes/header.php'); ?>
<?php
//$unit = "SELECT * FROM tbl_units ORDER BY unit ASC";
//$result_unit = $db->query($unit);
if (
	!isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
	|| $_SESSION['is_login_yes'] != 'yes'
	|| !in_array((int)$_SESSION['usertype'], [1, 3]) // allow usertype 1 OR 2
) {
	die("Unauthorized access.");
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

	/* Pop effect for breadcrumb links */
	.breadcrumb-elements a {
		display: inline-block;
		/* needed for transform */
		transition: all 0.2s ease;
	}

	.breadcrumb-elements a:hover {
		transform: scale(1.05);
		/* slightly bigger */
		box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
		/* subtle shadow */
		border-radius: 5px;
		/* optional: rounded edges for nicer look */
		background-color: rgba(0, 128, 128, 0.1);
		/* subtle background change */
	}

	img.barcode-hover {
		transition: transform 0.2s ease, box-shadow 0.2s ease;
		cursor: pointer;
		/* shows it’s clickable */
	}

	img.barcode-hover:hover {
		transform: scale(1.1);
		/* slightly bigger on hover */
		box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
		/* subtle shadow pop */
	}

	/* Smooth transition for tabs */
	.nav-tabs>li>a {
		transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
	}

	/* Hover effect */
	.nav-tabs>li>a:hover {
		transform: scale(1.05);
		/* slightly bigger */
		background-color: #b0c4de;
		/* subtle highlight, you can change color */
		color: #000 !important;
		/* ensure text is readable */
	}

	/* Active tab pop effect */
	.nav-tabs>li.active>a {
		transform: scale(1.05);
		font-weight: bold;
		color: #fff !important;
		background-color: #26a69a !important;
		/* your main tab color */
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Product Details</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li><a href="products.php"><i class="icon-barcode2 position-left"></i> Products</a></li>
							<li class="active"><i class="icon-dots position-left"></i>Product Details</li>
						</ul>

						<ul class="breadcrumb-elements">
							<li data-toggle="tooltip" title="Update Product "><a href="javascript:;" onclick="update_product()"><i class="icon-pencil3 position-left"></i> </a></li>
							<li data-toggle="tooltip" title="Upload  Image"><a href="javascript:;" onclick="upload_image()"><i class="icon-upload position-left"></i> </a></li>
							<li data-toggle="tooltip" title="Deduct Inventory"><a href="javascript:;" onclick="deduc_inventory()"><i class="icon-subtract position-left"></i> </a></li>
							<li data-toggle="tooltip" title="Product Damage"><a href="javascript:;" onclick="add_damage()"><i class="icon-add position-left"></i> </a></li>
						</ul>
					</div>
				</div>
				<!-- /page header -->
				<?php

				$product_id = $_GET['product_id'];
				$quantity = 0;

				$query = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
				$result = $db->query($query);

				if ($result) {
					while ($row = $result->fetch_assoc()) { // <-- changed fetchArray() to fetch_assoc()
						$product_id = $row['product_id'];
						$product_name = $row['product_name'];
						$product_code = $row['product_code'];
						$selling_price = $row['selling_price'];
						$supplier_price = $row['supplier_price'];
						$quantity = $row['quantity'];
						$critical_qty = $row['critical_qty'];
						$unit = $row['unit'];
						$image = $row['image'];

						if (!empty($image)) {
							$image_file = '../uploads/' . $image;
						} else {
							$image_file = '../images/no-image.png';
						}
					}
				} else {
					echo "Error: " . $db->error;
				}

				?>

				<!-- Content area -->
				<div class="content">
					<div class="panel panel-flat">
						<div class="panel-body">
							<div class="tabbable">
								<ul class="nav nav-tabs bg-slate nav-justified">
									<li class="active"><a href="#information" data-toggle="tab">Information</a></li>
									<li><a href="#history" data-toggle="tab">History</a></li>
									<li><a href="#damage" data-toggle="tab">Damage</a></li>
								</ul>

								<div class="tab-content">
									<div class="tab-pane active" id="information">
										<div class="panel panel-white border-top-xlg border-top-teal-400">
											<div class="panel-heading">
												<h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Information</h6>
											</div>
											<!-- <input type="text" id="myInputTextField"> -->

											<div class="panel-body">

												<table class="table text-nowrap table-bordered  ">
													<tr class="border-double">
														<td class="text-size-small">Image</td>
														<td> <img alt="<?= $image_file ?>" style="width: 150px;height: 150px;border: 2px solid #eee" src="<?= $image_file ?>" /> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Product Code</td>

														<td>
															<img
																class="barcode-hover"
																alt="<?= $product_name ?> (<?= $product_code ?>)"
																title="Print barcode for <?= $product_name ?>"
																src="barcode.php?codetype=Code39&size=40&text=<?= $product_code ?>&print=true"
																onclick="printBarcode('<?= $product_code ?>', '<?= $product_name ?>')" />
														</td>

													</tr>

													<tr class="border-double">
														<td class="text-size-small">Product ID</td>
														<td>21324<?= $product_id ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Name</td>
														<td><?= $product_name ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Selling Price</td>
														<td><?= number_format($selling_price, 2) ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Supplier Price</td>
														<td><?= number_format($supplier_price, 2) ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">In Stock</td>
														<td><?= $quantity ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Reorder Level</td>
														<td><?= $critical_qty ?> </td>
													</tr>
													<tr class="border-double">
														<td class="text-size-small">Unit</td>
														<td><?= $unit ?> </td>
													</tr>


												</table>
											</div>
										</div>
									</div>
									<div class="tab-pane" id="history">
										<div class="panel panel-white border-top-xlg border-top-teal-400">
											<div class="panel-heading">
												<h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> History</h6>
											</div>
											<!-- <input type="text" id="myInputTextField"> -->
											<div class="panel-body">
												<table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
													<thead>
														<tr style="border-bottom: 4px solid #ddd;background: #eee">
															<th>History ID</th>
															<th>Date</th>
															<th>Type</th>
															<th>Quantity</th>
															<th>Balance</th>
														</tr>
													</thead>
													<tbody>
														<?php
														$product_id = $_GET['product_id'];
														$query = "SELECT * FROM tbl_product_history WHERE product_id='$product_id'";
														$result = $db->query($query);
														while ($row = $result->fetch_assoc()) {
															if ($row['type'] == 1) {
																$type = '<span class="label bg-blue">OUT</span>';
															} else {
																$type = '<span class="label bg-green">IN</span>';
															}
														?>
															<tr>
																<td>023<?= $row['tph_id']; ?></td>
																<td><?= $row['hist_date']; ?></td>
																<td><?= $type ?></td>
																<td style="text-align: center;font-weight: bold;width: 30px"><?= $row['qty']; ?></td>
																<td style="text-align: center;font-weight: bold;width: 30px"><?= $row['balance']; ?></td>
															</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="damage">
										<div class="panel panel-white border-top-xlg border-top-teal-400">
											<div class="panel-heading">
												<h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Damage</h6>
											</div>
											<!-- <input type="text" id="myInputTextField"> -->
											<div class="panel-body">
												<table class="table datatable-button-html5-basic table-hover table-bordered" id="table-damage">
													<thead>
														<tr style="border-bottom: 4px solid #ddd;background: #eee">
															<th>ID</th>
															<th>Date</th>
															<th>Notes</th>
															<th>Quantity</th>
														</tr>
													</thead>
													<tbody>
														<?php
														$product_id = $_GET['product_id'];
														$query = "SELECT * FROM tbl_damage WHERE product_id='$product_id'";
														$result = $db->query($query);
														while ($row = $result->fetch_assoc()) {
														?>
															<tr>
																<td><?= $row['damage_id']; ?></td>
																<td width="250px"><?= $row['date_damage']; ?></td>
																<td><?= $row['notes']; ?></td>
																<td style="text-align: center;font-weight: bold;width: 30px"><?= $row['quantity_damage']; ?></td>
															</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
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
	<div id="modal_damage" class="modal fade">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Damage Form</h5>
				</div>
				<div class="modal-bodys" id="show-code">
					<form action="#" id="form-damage" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="save_damage"></input>
						<input name="product_id" type="hidden" value="<?= $product_id ?>">
						<div class="form-group">
							<label for="exampleInputuname_4" class="col-sm-3 control-label">Quantity</label>
							<div class="col-sm-9">
								<div class="input-group input-group-xlg"> <span class="input-group-addon"><i class="icon-pencil7"></i></span> <input onkeypress='return numbersonly(event)' class="form-control currency" autocomplete="off" name="quantity" id="quantity" placeholder="Enter quantity" type="text" data-error=" Please enter valid quantity." required> </div>
								<div class="help-block with-errors"></div>
							</div>
						</div>
						<div class="form-group">
							<label for="exampleInputuname_4" class="col-sm-3 control-label">Notes</label>
							<div class="col-sm-9">
								<textarea data-error=" Please enter notes." required rows="5" cols="5" class="form-control" placeholder="Enter Notes" name="notes"></textarea>
								<div class="help-block with-errors"></div>

							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Submit Damage</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_deduc" class="modal fade">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Deduc Invetory Form</h5>
				</div>
				<div class="modal-bodys" id="show-code">
					<form action="#" id="form-deduc" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="save_deduc"></input>
						<input name="product_id" type="hidden" value="<?= $product_id ?>">
						<div class="form-group">
							<label for="exampleInputuname_4" class="col-sm-3 control-label">Quantity</label>
							<div class="col-sm-9">
								<div class="input-group input-group-xlg"> <span class="input-group-addon"><i class="icon-pencil7"></i></span> <input class="form-control currency filterme" autocomplete="off" name="quantity" id="quantityDeduc" placeholder="Enter quantity" type="text" data-error=" Please enter valid quantity." required> </div>
								<div class="help-block with-errors"></div>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Submit Form</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="modal-update" class="modal fade" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Update Product Form</h5>
				</div>

				<div class="modal-bodys">
					<form action="#" id="form-update" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="update-product"></input>
						<input type="hidden" name="product_id" value="<?= $product_id ?>"></input>
						<div class="form-body" style="padding-top: 20px">

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Product Name</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control currency" value="<?= $product_name ?>" autocomplete="off" name="product_name" id="discount" placeholder="Enter Product Name" type="text" data-error=" Product Name is required." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Suppplier Price</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control filterme" autocomplete="off" value="<?= $supplier_price ?>" name="supplier_price" id="discount" placeholder="Enter selling price" type="text" data-error=" Please enter valid quantity." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Selling Price</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control filterme" autocomplete="off" value="<?= $selling_price ?>" name="selling_price" id="discount" placeholder="Enter selling price" type="text" data-error=" Please enter valid quantity." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Product Code</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input onkeypress='return numbersonly(event)' class="form-control currency" value="<?= $product_code ?>" autocomplete="off" name="product_code" id="product-code" placeholder="Enter product code" type="text" minlength="8" data-error=" Product Code is required& minimuim of 13 numbers." required>

									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Reorder Level</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input onkeypress='return numbersonly(event)' class="form-control currency" autocomplete="off" value="<?= $critical_qty ?>" name="critical_qty" id="discount" placeholder="Enter quantity" type="text" data-error=" Please enter valid quantity." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Unit</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input type="text" class="form-control" placeholder="pcs,kg,ml,pack,box,etc." name="unit" value="<?= $unit ?>" data-error=" Please enter unit." required>
									</div>
									<div class="help-block with-errors"></div>
								</div>
							</div>

						</div>
						<div class="modal-footer">
							<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-pencil7"></i></b> Save Changes</button>
						</div>
					</form>
				</div>

			</div>
		</div>
	</div>
	<div id="modal_upload" class="modal fade">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">Upload Image Form</h5>
				</div>
				<div class="modal-bodys" id="show-code">
					<form id="form-upload" class="form-horizontal" data-toggle="validator" method="POST" enctype="multipart/form-data >
	                    <input  type=" hidden" name="save_image">
						<input type="hidden" name="product_id" value="<?= $product_id ?>">
						<input type="hidden" name="server_data" value="<?= $HTTP_HOST ?>">
						<div class="form-group">
							<label for="exampleInputuname_4" class="col-sm-3 control-label">Image</label>
							<div class="col-sm-9">
								<div class="input-group input-group-xlg"> <span class="input-group-addon"><i class="icon-upload"></i></span> <input class="form-control " placeholder="Enter quantity" type="file" name="fileToUpload" data-error=" Please select image." required> </div>
								<div class="help-block with-errors"></div>
							</div>
						</div>

				</div>

				<div class="modal-footer">
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-pencil7"></i></b> Update Image</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script src="../js/validator.min.js"></script>
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
			"searching": false,
			"order": [
				[0, "desc"]
			],
			"lengthMenu": [
				[5, 25, 50, -1],
				[5, 25, 50, "All"]
			]
		});
	});



	$('#form-update').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {

					if (msg == 1) {
						$.jGrowl('Product details successfully updated.', {
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


	$('#form-damage').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {
					if (msg == 1) {
						$.jGrowl('Damage product succesfully save.', {
							header: 'Success Notification',
							theme: 'alert-styled-right bg-success'
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

	$('#form-deduc').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {
					console.log(msg);

					if (msg == 1) {
						$.jGrowl('Invetory succesfully updated.', {
							header: 'Success Notification',
							theme: 'alert-styled-right bg-success'
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
					$(':input[type="submit"]').prop('disabled', false);
				},
				error: function(msg) {
					alert('Something went wrong!');
					$(':input[type="submit"]').prop('disabled', false);
				}
			});
			return false;
		}
	});

	function printBarcode(code, name) {
		const barcodeURL = "barcode.php?codetype=Code39&size=40&text=" + code + "&print=true";
		var printWindow = window.open('', '_blank', 'width=400,height=350');
		printWindow.document.write(`
        <html>
            <head>
                <title>Print Barcode</title>
                <style>
                    body {
                        text-align: center;
                        font-family: Arial, sans-serif;
                        margin-top: 20px;
                    }
                    h2 {
                        margin-bottom: 10px;
                        font-size: 20px;
                    }
                    img {
                        width: 300px;
                        margin-bottom: 10px;
                    }
                </style>
            </head>
            <body>
                <h2>` + name + `</h2>
                <img src="` + barcodeURL + `" />
                <div style="font-size: ">` + code + `</div>

                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() { window.close(); }
                    }
                <\/script>
            </body>
        </html>
    `);

		printWindow.document.close();
	}


	$('#form-upload').submit(function(e) {
		e.preventDefault();
		$(':input[type="submit"]').prop('disabled', true);
		$.ajax({
			method: 'post',
			url: 'upload_image.php',
			data: new FormData(this),
			contentType: false,
			cache: false,
			processData: false,
			success: function(data) {
				$.jGrowl('Image successfully uploaded.', {
					header: 'Success Notification',
					theme: 'alert-styled-right bg-success'
				});
				setTimeout(function() {
					location.reload()
				}, 1500);

			}
		});
		return false;
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

	function deduc_inventory() {
		//$('').appendTo('body');
		$("#modal_deduc").modal('show');
	}

	function closer() {
		window.location = 'products.php';
	}

	function update_product() {
		$("#modal-update").modal('show');
	}

	function upload_image() {
		$("#modal_upload").modal('show');
	}

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


</html>