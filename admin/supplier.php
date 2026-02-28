<?php
require('includes/header.php');
$query = "SELECT * FROM tbl_supplier WHERE supplier_id!=1 ";
$result = $db->query($query);
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Supplier</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li class="active">Supplier</li>
						</ul>

						<ul class="breadcrumb-elements">
							<li><a href="javascript:;" data-toggle="modal" data-target="#modal_new"><i class="icon-add position-left text-teal-400"></i> New Supplier</a></li>
						</ul>
					</div>
				</div>
				<!-- /page header -->

				<!-- Content area -->
				<div class="content">
					<div class="panel  panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> Supplier List</h6>
						</div>
						<!-- <input type="text" id="myInputTextField"> -->
						<div class="panel-body">
							<table class="table datatable-button-html5-basic table-hover table-bordered" width="100%">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee;">
										<th>Supplier ID</th>
										<th>Name</th>
										<th>Address</th>
										<th>Contact</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<tr>
											<td>762345<?= $row['supplier_id']; ?></td>
											<td><?= $row['supplier_name']; ?></td>
											<td><?= $row['supplier_address']; ?></td>
											<td><?= $row['supplier_contact']; ?></td>
											<td title="Edit" style="width: 40px;text-align: center;"><button onclick="edit_details(this)" supplier_id="<?= $row['supplier_id']; ?>" supplier_name="<?= $row['supplier_name']; ?>" supplier_address="<?= $row['supplier_address']; ?>" supplier_contact="<?= $row['supplier_contact']; ?>" type="button" class="btn border-teal text-teal-400 btn-flat btn-icon btn-xs"><i class="icon-pencil7"></i></button></td>

										</tr>
									<?php } ?>
								</tbody>
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
	<div id="modal_new" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title"> New Supplier Form</h5>
				</div>

				<div class="modal-bodys">
					<form action="#" id="form-customer" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="save-supplier"></input>
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
									<textarea name="supplier_address" rows="5" cols="5" class="form-control" placeholder="Address"></textarea>
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
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Supplier</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>
	<div id="modal_edit" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title"> Edit Supplier Form</h5>
				</div>

				<div class="modal-bodys">
					<form action="#" id="form-customer-edit" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="update-supplier"></input>
						<input type="hidden" name="supplier_id" id="supplier_id"></input>
						<div class="form-body">
							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Name</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control" id="supplier_name" name="supplier_name" placeholder="Name" type="text" data-error=" Name is required." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Address</label>
								<div class="col-sm-9">
									<textarea name="supplier_address" id="supplier_address" rows="5" cols="5" class="form-control" placeholder="Address"></textarea>
								</div>

							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Contact</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control" id="supplier_contact" name="supplier_contact" placeholder="Contact" type="text" data-error=" Contact is required.">
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

						</div>
				</div>
				<hr>
				<div class="modal-footer">
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-pencil"></i></b> Save Changes</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>
</body>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script src="../js/validator.min.js"></script>

<script type="text/javascript">
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
			]
		});
	});
</script>
<script type="text/javascript">
	$(document).ready(function() {});
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
							window.location = 'supplier.php';
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

	function view_details(el) {
		var cust_id = $(el).attr('cust_id');
		window.location = 'customer-details.php?cust_id=' + cust_id;
	}

	function edit_details(el) {
		$("#modal_edit").modal('show');
		$("#supplier_name").val($(el).attr('supplier_name'));
		$("#supplier_address").val($(el).attr('supplier_address'));
		$("#supplier_contact").val($(el).attr('supplier_contact'));
		$("#supplier_id").val($(el).attr('supplier_id'));
	}

	$('#form-customer-edit').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {
					if (msg == '1') {

						$.jGrowl('Supplier successfully updated.', {
							header: 'Success Notification',
							theme: 'alert-styled-right bg-success'
						});
						setTimeout(function() {
							window.location = 'supplier.php';
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
</script>

</html>