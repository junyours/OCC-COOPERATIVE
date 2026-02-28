<?php require('includes/header.php'); ?>
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
							<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Employee</h4>
						</div>
					</div>

					<div class="breadcrumb-line">
						<ul class="breadcrumb">
							<li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
							<li class="active"><i class="icon-users position-left"></i> Employee</li>
						</ul>

						<ul class="breadcrumb-elements">
							<li><a href="javascript:;" data-toggle="modal" data-target="#modal-new"><i class="icon-add position-left text-teal-400"></i>New Employee</a></li>
						</ul>
					</div>
				</div>
				<!-- /page header -->

				<!-- Content area -->
				<div class="content">
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> List of Employee</h6>
						</div>

						<div class="panel-body panel-theme">
							<table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
								<thead>
									<tr style="border-bottom: 4px solid #ddd;background: #eee">
										<th>Employee ID</th>
										<th>Name</th>
										<th>Username</th>
										<th>User Type</th>
										<th>Status</th>
										<!-- <th>Action</th> -->
									</tr>
								</thead>
								<tbody>
									<?php
									$query = "SELECT * FROM tbl_users 
                                  WHERE usertype IN (1,2,3) 
                                 AND user_id != '" . $_SESSION['user_id'] . "'";
									$result = $db->query($query);

									while ($row = $result->fetch_assoc()) {
									?>
										<tr>
											<td><?= $row['user_id']; ?></td>
											<td><?= $row['fullname']; ?></td>
											<td><?= $row['username']; ?></td>
											<td>
												<?php
												if ($row['usertype'] == 1) echo "Admin";
												elseif ($row['usertype'] == 2) echo "Cashier";
												elseif ($row['usertype'] == 3) echo "Treasurer";
												?>
											</td>
											<td style="text-align: center;">
												<?php
												if ($row['field_status'] == 0) {
													echo '<span class="badge bg-blue">Active</span>';
												} else {
													echo '<span class="badge bg-danger">Inactive</span>';
												}
												?>
											</td>
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
		</div>
	</div>

	<!-- ===========================
     NEW EMPLOYEE MODAL
=========================== -->
	<div id="modal-new" class="modal fade" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
					<h5 class="modal-title">New Employee Form</h5>
				</div>

				<div class="modal-bodys">
					<form action="#" id="form-new" class="form-horizontal" data-toggle="validator" role="form">
						<input type="hidden" name="save-cashier"></input>
						<div class="form-body" style="padding-top: 20px">

							<div class="form-group">
								<label class="col-sm-3 control-label">Employee Name</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7"></i></span>
										<input onblur="checkEmployeeDuplicate()" class="form-control" autocomplete="off" name="name" id="employee_name" placeholder="Full Name" type="text" required>
									</div>
									<small id="employee-msg" class="text-danger"></small>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-3 control-label">Username</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-user"></i></span>
										<input onblur="checkUsernameDuplicate()" class="form-control" autocomplete="off" name="username" id="username_field" placeholder="Username" type="text" required>
									</div>
									<small id="username-msg" class="text-danger"></small>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-3 control-label">Password</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-lock"></i></span>
										<input class="form-control" autocomplete="off" name="password" placeholder="Password" type="password" required>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-3 control-label">User Type</label>
								<div class="col-sm-9">
									<div class="input-group input-group-xlg">
										<span class="input-group-addon"><i class="icon-pencil7"></i></span>
										<select class="form-control" name="usertype">
											<option value="3">Treasurer</option>
											<option value="2">Cashier</option>
											<option value="1">Admin</option>
										</select>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-3 control-label">Active</label>
								<div class="col-sm-9">
									<input type="checkbox" value="0" name="field_status">
								</div>
							</div>
						</div>
				</div>

				<div class="modal-footer">
					<button type="submit" id="btn-submit" class="btn bg-teal-400 btn-labeled">
						<b><i class="icon-add"></i></b> Save Employee
					</button>
				</div>
				</form>
			</div>
		</div>
	</div>

	<?php require('includes/footer.php'); ?>
	<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
	<script src="../js/validator.min.js"></script>

	<script>
		$(function() {
			$('.datatable-button-html5-basic').DataTable({
				"order": [
					[0, "desc"]
				]
			});
		});


		function checkEmployeeDuplicate() {
			let name = $("#employee_name").val().trim();
			if (name === "") return;

			$.ajax({
				type: "GET",
				url: "../transaction.php",
				data: {
					check_employee_duplicate: name
				},
				success: function(response) {
					if (response.trim() === "exists") {
						$("#employee-msg").text("Employee name already exists!");
						$("#btn-submit").prop("disabled", true);
					} else {
						$("#employee-msg").text("");
						if ($("#username-msg").text() === "") $("#btn-submit").prop("disabled", false);
					}
				}
			});
		}

		function checkUsernameDuplicate() {
			let username = $("#username_field").val().trim();
			if (username === "") return;

			$.ajax({
				type: "GET",
				url: "../transaction.php",
				data: {
					check_username_duplicate: username
				},
				success: function(response) {
					if (response.trim() === "exists") {
						$("#username-msg").text("Username already exists!");
						$("#btn-submit").prop("disabled", true);
					} else {
						$("#username-msg").text("");
						if ($("#employee-msg").text() === "") $("#btn-submit").prop("disabled", false);
					}
				}
			});
		}

		$('#form-new').validator().on('submit', function(e) {
			if (e.isDefaultPrevented()) {
				return;
			} else {
				if ($("#btn-submit").prop("disabled")) {
					alert("Please fix the duplicate warning before submitting.");
					return false;
				}

				$(':input[type="submit"]').prop('disabled', true);
				var data = $("#form-new").serialize();

				$.ajax({
					type: 'POST',
					url: '../transaction.php',
					data: data,
					success: function(msg) {
						if (msg == '1') {
							$.jGrowl('New employee successfully added.', {
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
					error: function() {
						alert('Something went wrong!');
					}
				});
				return false;
			}
		});
	</script>
</body>

</html>