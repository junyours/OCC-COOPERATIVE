<?php  require('includes/header.php');?>
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
            <?php  require('includes/sidebar.php');?>
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
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Category</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li class="active"><i class="icon-clipboard3 position-left"></i>Category</li>
                        </ul>
                        <ul class="breadcrumb-elements">
                            <li><a href="javascript:;" data-toggle="modal" data-target="#modal-new"><i class="icon-clipboard3 position-left text-teal-400"  ></i>New Category</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /page header -->
                <!-- Content area -->
                <div class="content">
                    <div class="panel  panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i>  List of Category</h6>
                        </div>
                        <input type="hidden" name='length_change' id='length_change' value="">
                        <div class="panel-body panel-theme">
                            <table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
                                <thead>
                                    <tr class="tr-table">
                                        <th >ID</th>
                                        <th >Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $query = "SELECT * FROM tbl_category";
                                        $result = $db->query($query);
                                        while($row = $result->fetch_assoc()) {

                                    ?>
                                    <tr>
                                        <td><?= $row['cat_id'];?></td>
                                        <td><?= $row['category_name'];?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php  require('includes/footer-text.php');?>
                <!-- /content area -->
            </div>
            <!-- /main content -->
        </div>
        <!-- /page content -->
    </div>
    <!-- /page container -->
</body>
<div id="modal-new" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button> 
                <h5 class="modal-title">New Category Form  </h5>
            </div>
            <div class="modal-bodys">
                <form action="#" id="form-new" class="form-horizontal" data-toggle="validator" role="form">
                    <input type="hidden" name="save-category" ></input>
                    <div class="form-body" style="padding-top: 20px">
                        <div id="display-msg"></div>
                        <div class="form-group">
                            <label for="exampleInputuname_4" class="col-sm-3 control-label"  >Name</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-xlg">
                                    <span class="input-group-addon" ><i class="icon-pencil7 text-size-base"></i></span>
                                    <input   class="form-control"  name="category_name" id="discount" placeholder="Enter  Name" type="text"    data-error="  Name is required." required >
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
            <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Category</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php  require('includes/footer.php');?>
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script src="../js/validator.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script> 
<script type="text/javascript">
	$(function() {

	    $.extend( $.fn.dataTable.defaults, {
	        autoWidth: false,
	        iDisplayLength: 20,
	        dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
	        language: {
	            search: '<span>Filter:</span> _INPUT_',
	            searchPlaceholder: 'Type to filter...',
	            lengthMenu: '<span>Show:</span> _MENU_',
	            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
	        }
	    });

	    // Basic initialization

	    var oTable = $('.datatable-button-html5-basic').DataTable({
	    	"order": [[ 1, "asc" ]],
			"aLengthMenu": [[20, 50, 100, 200, 500], [20, 50, 100, 200, 500]],

	    });
	    // $('#length_change').val(oTable.page.len());
	    // $('#length_change').change( function() { 
		//      oTable.page.len( $(this).val() ).draw();
		// });
	    /*$('.datatable-button-html5-basic').DataTable({
	    	"order": [[ 3, "desc" ]]
	    });*/
	    //$("#table-product_length").html('');
	});
	
	$('#form-deduc').on('submit', function (e) {
		e.isDefaultPrevented();
		$(':input[type="submit"]').prop('disabled', true);
		var data = $("#form-deduc").serialize();
		$.ajax({
				type      :      'POST',
				url       :      '../transaction.php',
				data      :       data,
				success  :       function(msg)     
				{    console.log(msg);
					// $.jGrowl('Menu successfully   updated.', {
                    //         header: 'Success Notification',
                    //         theme: 'alert-styled-right bg-success'
                    //     });
                    // setTimeout(function(){ location.reload()  }, 1500);          
				},
				error  :       function(msg)     
				{ 
					$(':input[type="submit"]').prop('disabled', false);
					alert('Something went wrong!');
				}
		});
		return false;
	});
	$('#form-add').on('submit', function (e) {
		e.isDefaultPrevented();
		//$(':input[type="submit"]').prop('disabled', true);
		var data = $("#form-add").serialize();
		$.ajax({
				type      :      'POST',
				url       :      '../transaction.php',
				data      :       data,
				success  :       function(msg)     
				{  console.log(msg);
					$.jGrowl('Menu successfully   updated.', {
                            header: 'Success Notification',
                            theme: 'alert-styled-right bg-success'
                        });
                    setTimeout(function(){ location.reload()  }, 1500);          
				},
				error  :       function(msg)     
				{ 
					$(':input[type="submit"]').prop('disabled', false);
					alert('Something went wrong!');
				}
		});
		return false;
	});

	$('#form-new').validator().on('submit', function (e) 
        {
        if (e.isDefaultPrevented()) 
        {
        }else { 
            $(':input[type="submit"]').prop('disabled', true);
            var data = $("#form-new").serialize();
            $.ajax({
                    type      :      'POST',
                    url       :      '../transaction.php',
                    data      :       new FormData(this),
                    contentType: false,  
                    cache: false,  
                    processData:false,  
                    success  :       function(msg)     
                    {  console.log(msg);
                        $.jGrowl('Category successfully saved.', {
                            header: 'Success Notification',
                            theme: 'alert-styled-right bg-success'
                        });
                         setTimeout(function(){  location.reload();   }, 1000);
                    },
                    error  :       function(msg)     
                    { 
                        alert('Something went wrong!');
                    }
            });
            return false;
        } 
    });

    function closer()
    {
    	window.location='products.php';
    }

    function view_details(el)
    {
    	var menu_id = $(el).attr('menu_id');
    	window.location='menu-details.php?menu_id='+menu_id;
    }

    function auto_generate()
    {
    	$.ajax({
               type      :      'GET',
               url       :      '../transaction.php',
               data      :       {auto_generate_menu:""},
                success  :       function(msg)     
                { 
                    $("#product-code").val(msg);
                },
                error  :       function(msg)     
                { 
                    alert('Something went wrong!');
                }
        });
        return false;
    }

    function changePage(el)
    {
    	$("#length_change").val($(el).attr('val'));
    	$("#length_change").trigger('change');
    }

    function checkProductDuplicate(e){
		/// error kang boss dave na platform
    	// let product_name = $(e).val();
    	// $.ajax({
        //         type      :      'GET',
        //         //dataType  : 'JSON',
        //         url       :      '../transaction.php',
        //         data      :       {checkproductExist:"", product_name : product_name},
        //         success  :       function(msg)     
        //         {     console.log(msg);
        //             if(msg === '1'){
        //             	$("#btn-submit").prop('disabled', true);
        //             	$("#display-msg").html(`
        //             		<div class="alert alert-danger  alert-dismissible">
		// 						<button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding-right: 20px">
		// 					    <span aria-hidden="true">&times;</span>
		// 					  </button>
		// 						<span class="font-weight-semibold"><b>Oh snap!</b></span> Product Name is already added.
		// 				    </div>
        //             	`)
        //             }else{
        //             	$("#btn-submit").prop('disabled', false);
        //             	$("#display-msg").html('');
        //             }
        //         },
        //         error  :       function(msg)     
        //         { 
        //             alert('Something went wrong!');
        //         }
        // });
    }

	function check_menu(el)
	{
		if($(el).is(':checked'))
		{
			$(el).closest('tr').find('input.quantity').prop('disabled', false);
		}else{

		$(el).closest('tr').find('input.quantity').prop('disabled', true);
		$(el).closest('tr').find('input.quantity').val('');
		}
	}
</script>

</html>
