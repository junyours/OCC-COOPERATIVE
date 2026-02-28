<?php require('includes/header.php'); ?>
<?php
$query = "SELECT * FROM tbl_customer";
$result = $db->query($query);
?>

<body>
	<div id="spinner_div"></div>
	<div class="row top-row">
		<div class="col-lg-3 logo-head">
			<img src="../images/logo.png" style="height: 80px;width: 80px">
		</div>
		<div class="col-lg-8 middle-div">
			navigation
		</div>
		<div class="col-lg-1 top-user">
			<a href="index.php"> <img src="../images/home-icon.png" style="height: 80px;width: 80px"></a>
		</div>
	</div>
	<div class="row ">
		<div class="col-lg-1">
			<div class="sidebar-main">
				<table class="table-sidebar">
					<tr>
						<td>
							<a href="">
								<div><i class="icon-users"></i><span><br>User</span></div>
							</a>
						</td>
					</tr>
					<tr>
						<td>
							<a href="">
								<div><i class="icon-users"></i><span><br>User</span></div>
							</a>
						</td>
					</tr>

				</table>
			</div>
		</div>
		<div class="col-lg-11">
			<div class="breadcrumb-line"><a class="breadcrumb-elements-toggle"><i class="icon-menu-open"></i></a>
				<ul class="breadcrumb">
					<li><a href="index.html"><i class="icon-home2 position-left"></i> Home</a></li>
					<li class="active">Dashboard</li>
				</ul>

				<ul class="breadcrumb-elements">
					<li><a href="#"><i class="icon-comment-discussion position-left"></i> Support</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<i class="icon-gear position-left"></i>
							Settings
							<span class="caret"></span>
						</a>

						<ul class="dropdown-menu dropdown-menu-right">
							<li><a href="#"><i class="icon-user-lock"></i> Account security</a></li>
							<li><a href="#"><i class="icon-statistics"></i> Analytics</a></li>
							<li><a href="#"><i class="icon-accessibility"></i> Accessibility</a></li>
							<li class="divider"></li>
							<li><a href="#"><i class="icon-gear"></i> All settings</a></li>
						</ul>
					</li>
				</ul>
			</div>
			<div class="main-div">
				<div class="panel panel-theme">
					<div class="panel-heading">
						<h6 class="panel-title">Input with icon<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
						<div class="heading-elements">
							<form class="heading-form" action="#">
								<div class="form-group has-feedback">
									<input type="search" class="form-control" placeholder="Search...">
									<div class="form-control-feedback">
										<i class="icon-search4 text-size-base text-muted"></i>
									</div>
								</div>
							</form>
						</div>
					</div>

					<div class="panel-body panel-body-main">
						<div class=" main-content">
							<table class="table datatable-button-html5-basic table-hover table-bordered" width="100%">
								<thead>
									<tr class="tr-theme">
										<th>Customer ID</th>
										<th>Name</th>
										<th>Address</th>
										<th>Contact</th>
									</tr>
								</thead>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<tr style="cursor: pointer;" title="View Details" onclick="view_details(this)" cust_id="<?= $row['cust_id']; ?>">
											<td>34236<?= $row['cust_id']; ?></td>
											<td><?= $row['name']; ?></td>
											<td><?= $row['address']; ?></td>
											<td><?= $row['contact']; ?></td>

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

	<?php require('includes/footer.php'); ?>
	<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
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
	</script>
</body>

</html>