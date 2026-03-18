<?php  require('includes/header.php');?>
<style type="text/css">
#show-search-user{
    background-color: #002639;
	min-height: 300px;
    max-height: 300px;
	overflow-y: auto;
	z-index: 100;
	position: absolute;
	width: 100%;
	display: none;
}
#show-search-user::-webkit-scrollbar-track
{
	background-color: #1573a7;
}

#show-search-user::-webkit-scrollbar
{
	width: 12px;
	background-color: #F5F5F5;
}

#show-search-user::-webkit-scrollbar-thumb
{
	background-color:#0086cf;
}


.ul-search{
    list-style-type: none;
    background: #002639;
    color: #fff;
    margin-left: -25px;
    font-size: 12px;
}

.ul-search li{
    padding-top: 10px;
    padding-left: 10px;
    padding-bottom:10px;
    height: 40px;
    font-size: 12px;
    cursor: pointer;
}
.ul-search li{
   border-bottom: 2px solid #5d7a88;
}
.name-span{
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
   	if (isset($_SESSION['employee-report-user'])!="") {
		$user_query_name = "SELECT * FROM tbl_users WHERE user_id='".$_SESSION['employee-report-user']."' ";
		$user_queryname = $db->query($user_query_name);
		while($row = $user_queryname->fetchArray()) {
			$selected_user = $row['fullname'];
		}
	}else{
		$selected_user = "";
	}

	if (isset($_SESSION['sale-report-customer'])!="") {
		$customer_query_name = "SELECT * FROM tbl_customer WHERE cust_id='".$_SESSION['sale-report-customer']."' ";
		$customer_queryname = $db->query($customer_query_name);
		while($row = $customer_queryname->fetchArray()) {
			$selected_customer = $row['name'];
		}
	}else{
		$selected_customer = "";
	}

	if (isset($_SESSION['sale-report-customer'])!="") {
		$customer_query = "AND tbl_sales.cust_id='".$_SESSION['sale-report-customer']."' ";
	}else{
		$customer_query = "";
	}
   $query = "SELECT * FROM tbl_customer";
   $customer = $db->query($query);
   $query1 = "SELECT * FROM tbl_users";
   $employee = $db->query($query1);

   if (isset($_SESSION['sale-report-user'])) {
       $btn_color = 'bg-danger-400';
   }else{
   	   $btn_color = 'bg-slate-400';
   }
?>
<body class="layout-boxed navbar-top">
	<div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
		<div class="navbar-header">
	<a class="navbar-brand" href="index.php"><img style="height: 40px!important" src="../images/logo2.png" alt=""></a>
			<ul class="nav navbar-nav visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
			</ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">
			<?php  require('includes/sidebar.php');?>
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
							<input type="hidden" name="employee-report">
						<ul class="breadcrumb-elements">
						     <li data-toggle="tooltip" title="Date" style="padding-top: 2px;padding-right: 2px">
						    	<div class="input-group">
			                        <span class="input-group-addon"><i class="icon-calendar"></i></span>
			                         <input style="width: 200px" type="text" autocomplete="off" name="date" class="form-control daterange-buttons " value=" <?php if (isset($_SESSION['history-report'])!="") {?>   <?= $_SESSION['history-report'] ?> <?php }else{?> <?= date("m-d-Y")?> - <?= date("m-d-Y")?>  <?php }?>"> 
			                    </div>
						    </li>	
						    <li  data-toggle="tooltip" title="Employee" style="padding-top: 2px;padding-right: 2px">
						    	<div class="btn-group">
			                        <input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['employee-report-user'])!="") { echo  $_SESSION['employee-report-user']; } ?>" name="user_id" id="user_id">
								    <input  style="width: 180px" autocomplete="off" type="search" class="form-control" id="user-input"  value="<?php if (isset($_SESSION['employee-report-user'])!="") { echo  $selected_user; } ?>" name="username"  >
								    <span id="searchclearuser" class="glyphicon glyphicon-remove-circle"></span>
								     <div id="show-search-user" ></div>
								</div>
						    </li>  
						    <li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
						    <li  style="padding-top: 2px;padding-right: 200px"><button data-toggle="tooltip" title="Clear" type="button"  onclick="clear_filter()" class="btn <?= $btn_color ?>"><b><i class="icon-filter4"></i> Clear Filter</b></button></li>
						</ul>
						</form>
					</div>
				</div>
				<div class="content">
					<div class="panel panel-white border-top-xlg border-top-teal-400">
						<div class="panel-heading">
							<h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Sales report form<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
						    <div class="heading-elements">
		                	</div>
						</div>
						
						<div class="panel-body product-div2">
			                
						    <table class="table datatable-button-html5-basic table-hover table-bordered  ">
						       <thead>
						       	     <tr style="border-bottom: 4px solid #ddd;background: #eee">
							   	    	<th>History ID</th>
							   	    	<th>Date</th>
							   	    	<th>History Type</th>
							   	    	<th>Details</th>
							   	    </tr>
						       </thead>
						       <tr>
									<?php 
									    if (isset($_SESSION['history-report'])) {
										    $from = $_SESSION['history-report-from'];
						     	            $to = $_SESSION['history-report-to'];
						     	            $today = date("Y-m-d");
						     	            $start = strtotime('today GMT');
						     	            $date_add = date('Y-m-d', strtotime('+1 day', $start)); 
						     	            if (isset($_SESSION['sale-report-user'])) {
					     	            		$user_query = "AND tbl_sales.user_id='".$_SESSION['sale-report-user']."' ";
					     	            	}else{
					     	            		$user_query = "";
					     	            	}
					     	            	if (isset($_SESSION['sale-report-customer'])) {
					     	            		$customer_query = "AND tbl_sales.cust_id='".$_SESSION['sale-report-customer']."' ";
					     	            	}else{
					     	            		$customer_query = "";
					     	            	}

						     	            if ($today==$from || $today==$to) {
						     	            	$query = "SELECT * FROM tbl_history  WHERE  date_history BETWEEN  '$today' AND '$date_add'  ";
						     	            }else{
						     	            	$query = "SELECT * FROM tbl_history  WHERE  date_history BETWEEN  '$from' AND '$to' ";
						     	            }
					     	            }else{
						     	            $query = "SELECT * FROM tbl_history  ";
				                            $result = $db->query($query);
					     	            }
									    $i = 0 ;
									    $details_data = "";
			                            $result = $db->query($query);
			                            while($row = $result->fetchArray()) {
			                            	$i++;
			                            	$details = json_decode($row['details']);
			                            	$user_id = $details->user_id;
			                            	$query_user = "SELECT * FROM tbl_users WHERE user_id='$user_id'";
			                            	$result_user = $db->query($query_user);
			                            	$data_user = $result_user->fetchArray();
			                            	if ($row['history_type']==1) {
			                            		$history_type = "Sales";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000'.$details->sales_no.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==2) {
			                            		$history_type = "Delete Sales";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000'.$details->sales_no.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==3) {
			                            		$history_type = "Set Active Sales";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000'.$details->sales_no.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==11) {
			                            		$history_type = "New Product";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000'.$details->sales_no.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==12) {
			                            		$history_type = " Product Damage";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324'.$details->product_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==13) {
			                            		$history_type = "Update Product Info";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324'.$details->product_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==14) {
			                            		$history_type = "Upload Product Image";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324'.$details->product_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==15) {
			                            		$history_type = "New Customer";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Customer ID: 34236'.$details->cust_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==17) {
			                            		$history_type = "New Supplier";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Supplier ID: 762345'.$details->supplier_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==19) {
			                            		$history_type = "New Employee";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Employee ID: 87989'.$details->user_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}elseif ($row['history_type']==22) {
			                            		$history_type = "Receivings";
			                            		$details_data = '<i class="icon-barcode2 text-teal-400"></i> Receiving ID: 0000000023'.$details->user_id.' <i class="icon-user text-teal-400"></i> Employee : '.$data_user['fullname'].' ';
			                            	}

			                            	else{
			                            		$history_type = $row['history_type'];
			                            		$details_data ="Not Set";
			                            	}

			                         ?>
									<td><?= $row['history_id']?></td>
									<td><?= $row['date_history']?></td>
									<td><?= $history_type?></td>
									<td><?=  $details_data ?></td>
								</tr>
							<?php } ?>
							<?php if ($i==0) {?>
								<tr>
									<td colspan="10" align="center"><h2>No data found!</h2></td>
								</tr>
							<?php }?>
							</table>
						</div>
				    </div>

				</div>
				<?php  require('includes/footer-text.php');?>

			</div>

		</div>

	</div>
</body>
<?php  require('includes/footer.php');?>
 <div id="modal-all" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-full">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="title-all"></h5>
				<button type="button" class="close" title="Click to close (Esc)" data-dismiss="modal">&times;</button>
			</div>

			<div class="modal-body"   >
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

<!-- Theme JS files -->

<script type="text/javascript" src="../assets/js/plugins/forms/styling/switchery.min.js"></script>

<!-- 
<script type="text/javascript" src="../assets/js/pages/form_checkboxes_radios.js"></script> -->
<!-- /theme JS files -->

<script type="text/javascript">
	
	$(function() {
		$('[data-toggle="tooltip"]').tooltip(); 
	    $.extend( $.fn.dataTable.defaults, {
	        autoWidth: false,
	        dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
	        language: {
	            search: '<span>Filter:</span> _INPUT_',
	            searchPlaceholder: 'Type to filter...',
	            lengthMenu: '<span>Show:</span> _MENU_',
	            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
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
	            buttons: [
	                {
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

	    if (Array.prototype.forEach) {
	        var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
	        elems.forEach(function(html) {
	            var switchery = new Switchery(html);
	        });
	    }
	    else {
	        var elems = document.querySelectorAll('.switchery');
	        for (var i = 0; i < elems.length; i++) {
	            var switchery = new Switchery(elems[i]);
	        }
	    }
	});


	$('#form-sales').on('submit', function (e) 
	{
	    if ($("#user-input").val()!="") {
		    $(':input[type="submit"]').prop('disabled', true);
		    var data = $("#form-sales").serialize();
		    $.ajax({
		           type      :      'POST',
		           url       :      '../transaction.php',
		           data      :       data,
		            success  :       function(msg)     
		            {     
		                location.reload();
		            },
		            error  :       function(msg)     
		            { 
		                alert('Something went wrong!');
		            }
		    });
		    return false;
	   }else{
	   	    alert('please select user');
	   }
	});

	$( "#user-input" ).keyup(function() {
	    $("#show-search-user").show();
	    var keywords = $(this).val(); 
	    if (keywords!="") {
	        $.ajax({
	            type      :      'GET',
	            url       :      '../transaction.php',
	            data      :       {search_user:"",keywords:keywords},
	            success  :       function(msg)     
	            {  
	                $("#show-search-user").html(msg);
	            },
	            error  :       function(msg)     
	            { 
	                alert('Something went wrong!');
	            }
	        });
	    }else{
	        $( "#user-input" ).click();
	    }

	});

	$( "#user-input" ).click(function() {
	    $("#show-search-user").show();
	    $("#show-search-customer").hide();
	    $.ajax({
	        type      :      'GET',
	        url       :      '../transaction.php',
	        data      :       {search_user:"",keywords :""},
	        success  :       function(msg)     
	        {   
	            $("#show-search-user").html(msg);
	        },
	        error  :       function(msg)     
	        { 
	            alert('Something went wrong!');
	        }
	    });
	});

	function select_user(el)
	{   
	    var user_id = $(el).attr('user_id');
	    var name = $(el).attr('name');
	    $("#user_id").val(user_id);
	    $("#user-input").val(name);
	    $("#show-search-user").hide();
	}

	 $("#searchclearuser").click(function(){
    	$("#user-input").val("");
        $("#show-search-user").hide();
    });







	function closer()
	{
		window.location='products.php';
	}

	function  view_details(el)
	{
		var sales_no = $(el).attr('sales-no');
		$("#show-data-all").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
	    $.ajax({
	           type      :      'POST',
	           url       :      '../transaction.php',
	           data      :       {sales_report_details:"",sales_no:sales_no},
	            success  :       function(msg)     
	            {  
	            	$("#modal-all").modal('show');
				    $("#show-button").html('');
				    $("#title-all").html('OR # : <b class="text-danger">'+sales_no+'</b>');
				    $("#show-data-all").html(msg);
	            },
	            error  :       function(msg)     
	            { 
	                alert('Something went wrong!');
	            }
	    });
	    return false;
	}


 



</script>

 
</html>
