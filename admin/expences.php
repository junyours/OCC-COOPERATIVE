<?php require('includes/header.php'); ?>

<?php
if (
	!isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
	|| $_SESSION['is_login_yes'] != 'yes'
	|| !in_array((int)$_SESSION['usertype'], [1, 3])
) {
	die("Unauthorized access.");
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Expenses</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li class="active">Expenses</li>
						</ul>

						<ul class="breadcrumb-elements">
							<li><a data-toggle="modal" data-target="#modal-new" href="javascript:;"><i class="icon-add text-teal-400 position-left"></i> New Expences</a></li>
						</ul>
					</div>
				</div>
				<!-- /page header -->

				<!-- Content area -->
				<div class="content">
					<div class="panel  panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> List of Expenses</h6>
						</div>
						<!-- <input type="text" id="myInputTextField"> -->
						<div class="panel-body panel-theme">
							<table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee">
										<th>Expences ID</th>
										<th>Employee</th>
										<th>Date</th>
										<th>Description</th>
										<th>Approved By</th>
										<th>Note</th>
										<th>Amount</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$query = "SELECT * FROM tbl_expences INNER JOIN tbl_users ON tbl_expences.user_id = tbl_users.user_id";
									$result = $db->query($query);

									while ($row = $result->fetch_assoc()) {
									?>
										<tr>
											<td>67688<?= $row['expences_id']; ?></td>
											<td><?= $row['fullname']; ?></td>
											<td><?= date("F d, Y", strtotime($row['date_expence'])); ?></td>
											<td><?= $row['description']; ?></td>
											<td><?= $row['approve_by']; ?></td>
											<td width="30%"><?= $row['notes']; ?></td>
											<td align="right"><b><?= number_format($row['expence_amount'], 2); ?></b></td>
										</tr>
									<?php
									}
									?>

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
	<div id="modal-new" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title"> New Expences Form</h5>
				</div>

				<div class="modal-bodys">
					<form action="#" id="form-expences" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="save-expences"></input>
						<div class="form-body">
							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Description</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control" name="description" placeholder="Description" type="text" data-error=" Description is required." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Date</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input name="date" value="<?= date("Y-m-d") ?>" type="text" class="form-control pickadate-selectors picker__input picker__input--active" id="P1916777366" aria-haspopup="true" aria-expanded="true" aria-readonly="false" aria-owns="P1916777366_root">
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Amount</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control filterme" autocomplete="off" name="expence_amount" placeholder="Amount" type="text" data-error=" Please enter valid amount." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Approved By</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7 text-size-base"></i></span>
										<input class="form-control " autocomplete="off" name="approve_by" placeholder="Enter Name" type="text" data-error=" Please enter name." required>
									</div>

									<div class="help-block with-errors"></div>
								</div>
							</div>

							<div class="form-group">
								<label for="exampleInputuname_4" class="col-sm-3 control-label">Note</label>
								<div class="col-sm-9">
									<textarea rows="5" cols="5" class="form-control" placeholder="Enter Notes" name="notes"></textarea>
									<div class="help-block with-errors"></div>
								</div>
							</div>

						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Expences</button>
				</div>
			</div>
		</div>
	</div>
	</div>
</body>
<?php require('includes/footer.php'); ?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>

<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>

<script src="../js/validator.min.js"></script>
<script type="text/javascript">
	$(function() {
		$('.pickadate-selectors').pickadate({
			format: 'yyyy-mm-dd',
			hiddenPrefix: 'prefix__',
			hiddenSuffix: '__suffix',
			clear: ''
		});

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

	$('#form-expences').validator().on('submit', function(e) {
		if (e.isDefaultPrevented()) {} else {
			$(':input[type="submit"]').prop('disabled', true);
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: '../transaction.php',
				data: data,
				success: function(msg) {
					if (msg == '1') {

						$.jGrowl('New expences successfully added.', {
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
</script>

</html>