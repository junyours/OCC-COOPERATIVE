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

   require('db_connect.php');
   	if (isset($_SESSION['employee-report-user'])!="") {
		$user_query_name = "SELECT * FROM tbl_users WHERE user_id='".$_SESSION['employee-report-user']."' ";
		$user_queryname = $db->query($user_query_name);
		while($row = $user_queryname->fetch_assoc()) {
			$selected_user = $row['fullname'];
		}
	}else{
		$selected_user = "";
	}

	if (isset($_SESSION['sale-report-customer'])!="") {
		$customer_query_name = "SELECT * FROM tbl_customer WHERE cust_id='".$_SESSION['sale-report-customer']."' ";
		$customer_queryname = $db->query($customer_query_name);
		while($row = $customer_queryname->fetch_assoc()) {
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
?>
<body class="layout-boxed navbar-top">
	<div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php"><img src="../assets/images/logo_light.png" alt=""></a>

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
							<li class="active"><i class="icon-chart"></i> Sales Report Form</li>
						</ul>
					</div>
				</div>
				<div class="content">
					<div class="col-lg-6">
						<div class="panel panel-white border-top-xlg border-top-teal-400">
							<div class="panel-heading">
								<h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Sales report form<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
							</div>
							<div class="panel-body">
								<form class="form-horizontal" action="#" style="width: 70%">
								<?php if ($_GET['type']=='sales') {?>
									<fieldset class="content-group" >
										<div class="form-group">
											<label class="control-label col-lg-5">Sales Date</label>
											<div class="col-lg-7">
												<div class="input-group">
							                        <span class="input-group-addon"><i class="icon-calendar"></i></span>
							                         <input  type="text" autocomplete="off" name="date" class="form-control daterange-buttons " value="<?= date("m-d-Y")?> - <?= date("m-d-Y")?>"> 
							                         <!-- <span  class="input-group-addon" data-popup="popover" data-placement="top" data-trigger="hover" data-content="enable/disable sales date selection" ><input  type="checkbox" name=""></span> -->
							                    </div>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-lg-5">Default text input</label>
											<div class="col-lg-7">
												<div class="btn-group">
							                        <input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['sale-report-customer'])!="") { echo  $_SESSION['sale-report-customer']; } ?>" name="cust_id" id="cust_id">
												    <input style="width: 100%"  autocomplete="off" type="search" class="form-control" id="customer-input" value="<?php if (isset($_SESSION['sale-report-customer'])!="") { echo  $selected_customer; } ?>" name="custname" >
												    <span id="searchclear" class="glyphicon glyphicon-remove-circle"></span>
												     <div id="show-search-customer" ></div>
												</div>
											</div>
										</div>
									</fieldset>
								<?php }?>

									<div class="text-right">
										<button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button>
									</div>
								</form>
							</div>
					    </div>
				    </div>
				</div>
				<?php  require('includes/footer-text.php');?>

			</div>

		</div>

	</div>
</body>
<?php  require('includes/footer.php');?>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/ui/moment/moment.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/anytime.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.date.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/picker.time.js"></script>
<script type="text/javascript" src="../assets/js/plugins/pickers/pickadate/legacy.js"></script>
<script type="text/javascript" src="../assets/js/pages/picker_date.js"></script>

<script type="text/javascript">
	
	/* ------------------------------------------------------------------------------
*
*  # Tooltips and popovers
*
*  Specific JS code additions for components_popups.html page
*
*  Version: 1.0
*  Latest update: Aug 1, 2015
*
* ---------------------------------------------------------------------------- */

$(function() {

    $('[data-popup="popover"]').popover();
   
	
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
