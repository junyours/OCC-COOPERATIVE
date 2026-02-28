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

    .containers {
        display: block;
        position: relative;
        padding-left: 25px;
        margin-bottom: 22px;
        cursor: pointer;
        font-size: 14px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        color: #5a5959;
    }


    .containers input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }


    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #bfbfbf;
    }


    .containers:hover input~.checkmark {
        background-color: #bfbfbf;
    }


    .containers input:checked~.checkmark {
        background-color: #26a69a;
    }


    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }


    .containers input:checked~.checkmark:after {
        display: block;
    }


    .containers .checkmark:after {
        left: 9px;
        top: 5px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 3px 3px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    p.text-title {
        line-height: 10px !important;
    }
</style>

<?php
if (isset($_SESSION['soa-customer'])) {
    $selected_customer = "";
    $cust_id = $_SESSION['soa-customer'];
    $query = "SELECT 
tbl_sales.sales_no,
MAX(tbl_sales.sales_id) as sales_id,
MAX(tbl_sales.sales_date) as sales_date,
MAX(tbl_sales.total_amount) as total_amount,
MAX(tbl_sales.other_amount) as other_amount,
MAX(tbl_sales.balance) as balance,
MAX(tbl_sales.sales_status) as sales_status,
MAX(tbl_customer.name) as name
FROM tbl_sales
LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
WHERE tbl_sales.cust_id='$cust_id'
AND tbl_sales.balance > 0
GROUP BY tbl_sales.sales_no
";
    $result = $db->query($query);
    $customer_query_name = "SELECT * FROM tbl_customer WHERE cust_id='" . $_SESSION['soa-customer'] . "' ";
    $customer_queryname = $db->query($customer_query_name);
    while ($row = $customer_queryname->fetch_assoc()) {
        $selected_customer = $row['name'];
    }
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
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard </span> - Statement of Account</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="javascript:;">Reports</a></li>
                            <li class="active">Statement of Account</li>
                        </ul>
                    </div>
                </div>
                <div class="content">
                    <div class="panel panel-body ">
                        <form class="heading-form" id="form-sales" method="POST">
                            <input type="hidden" name="submit-soa">
                            <ul class="breadcrumb-elements" style="float:left">

                                <li data-toggle="tooltip" title="Customer" style="padding-top: 2px;padding-right: 2px">
                                    <div class="btn-group">
                                        <input autocomplete="off" type="hidden" value="<?php if (isset($_SESSION['soa-customer']) != "") {
                                                                                            echo  $_SESSION['soa-customer'];
                                                                                        } ?>" name="cust_id" id="cust_id">
                                        <input style="width: 230px" autocomplete="off" type="search" class="form-control" id="customer-input" value="<?php if (isset($_SESSION['soa-customer']) != "") {
                                                                                                                                                            echo  $selected_customer;
                                                                                                                                                        } ?>" name="custname">
                                        <span id="searchclear" class="glyphicon glyphicon-remove-circle"></span>
                                        <div id="show-search-customer"></div>
                                    </div>
                                </li>
                                <li data-toggle="tooltip" title="Search" style="padding-top: 2px;padding-right: 2px"><button type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-search4"></i></b> Search</button></li>
                            </ul>
                        </form>
                    </div>
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> List of Unpaid Sales <a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                        </div>

                        <div class="panel-body product-div2">
                            <form id="form-generate">
                                <input type="hidden" name="submit-soa-generate">
                                <?php if (isset($_SESSION['soa-customer'])) {  ?>
                                    <table class="table datatable-button-html5-basic table-hover table-bordered" width="100%">
                                        <thead>
                                            <tr class="tr-table">
                                                <th></th>
                                                <th>Date</th>
                                                <th>Bill No.</th>
                                                <th>Amount Due</th>
                                                <th>Other Amount</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 0;
                                            while ($row = $result->fetch_assoc()) {
                                                $sales_id = '00000000' . $row['sales_id'];
                                                if ($row['sales_status'] == 2) {
                                                    $sales_status = '<label class="label label-primary">Updated</label>';
                                                } elseif ($row['sales_status'] == 1) {
                                                    $sales_status = '<label class="label label-primary">Active</label>';
                                                } elseif ($row['sales_status'] == 3) {
                                                    $sales_status = '<label class="label label-danger">Cancelled</label>';
                                                }
                                                $i++;


                                            ?>
                                                <tr>
                                                    <td>
                                                        <label class="containers">
                                                            <input type="checkbox" name="sales_no_checkbox[]" class="sales_no_checkbox" value="<?= $row['sales_no'] ?>">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </td>
                                                    <td><?= date('F d, Y h:i A', strtotime($row['sales_date'])) ?></td>
                                                    <td><a href="javascript:" onclick="view_details(this)" value="<?= $row['sales_no'] ?>" sales-id="<?= $sales_id ?>" sales-no="<?= $row['sales_no'] ?>"><?= $sales_id ?></a></td>
                                                    <td class="text-rught"><?= number_format($row['total_amount'], 2) ?></td>
                                                    <td class="text-rught"><?= number_format($row['other_amount'], 2) ?></td>
                                                    <td class="text-rught"><?= number_format($row['balance'], 2) ?></td>
                                                    <td class="text-center"><?= $sales_status ?></td>


                                                </tr>
                                            <?php } ?>
                                            <?php if ($i == 0) { ?>
                                                <tr>
                                                    <td colspan="6" class="center">
                                                        <h3>No sales found!</h3>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="tr-table">
                                                <th></th>
                                                <th>Date</th>
                                                <th>Bill No.</th>
                                                <th>Amount Due</th>
                                                <th>Other Amount</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <br>
                                    <button type="submit" class="btn bg-teal-400">Generate SOA</button>
                            </form>
                        <?php } else { ?>
                            <div class="text-center">
                                <h3>Please select customer!</h3>
                            </div>
                        <?php } ?>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Statement of Account</h5>
                <button type="button" class="close" title="Click to close (Esc)" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="center" style="padding-top: 20px">
                    <button onclick="print_receipt()" type="button" class="btn bg-teal-400 btn-labeled btn-labeled-left"><b><i class="icon-printer"></i></b> Print</button>
                </div>
                <div id="print_receipt_data">
                    <div id="show-data-all"></div>
                </div>

            </div>

        </div>
    </div>
</div>

<div id="modal-all2" class="modal fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-all"></h5>
                <button type="button" class="close" title="Click to close (Esc)" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="show-data-all2"></div>
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
    let sales_no = null;

    function view_details(el) {
        sales_no = $(el).attr('sales-no');
        var sales_id = $(el).attr('sales-id');
        $("#show-data-all2").html('<div style="width:100%;height:100%;position:absolute;left:50%;right:50%;top:40%;"><img src="../images/LoaderIcon.gif"  ></div>');
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                sales_report_details: "",
                sales_no: sales_no
            },
            success: function(msg) {
                $("#modal-all2").modal('show');
                $("#show-button").html('');
                $("#title-all2").html('Bill No. : <b class="text-danger">' + sales_id + '</b>');
                $("#show-data-all2").html(msg);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    }

    function closer() {
        window.location = 'products.php';
    }

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



    $("#customer-input").keyup(function() {
        $("#show-search-customer").show();
        var keywords = $(this).val();
        if (keywords != "") {
            $.ajax({
                type: 'GET',
                url: '../transaction.php',
                data: {
                    search_customer: "",
                    keywords_search: keywords
                },
                success: function(msg) {
                    $("#show-search-customer").html(msg);
                    $("#show-loader").html('');
                },
                error: function(msg) {
                    alert('Something went wrong!');
                }
            });
        } else {
            $("#customer-input").click();
        }

    });

    $("#customer-input").click(function() {
        $("#show-search-customer").show();
        $("#show-search-user").hide();
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                search_customer: "",
                keywords_search: ""
            },
            success: function(msg) {
                $("#show-search-customer").html(msg);
                $("#show-loader").html('');
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    });

    $("#searchclear").click(function() {
        $("#customer-input").val("");
        $("#show-search-customer").hide();
    });

    function select_customer(el) {
        var cust_id = $(el).attr('cust_id');
        var name = $(el).attr('name');
        $("#cust_id").val(cust_id);
        $("#customer-input").val(name);
        $("#show-search-customer").hide();

    }

    $('#form-generate').on('submit', function(e) {
        // $(':input[type="submit"]').prop('disabled', true);
        var data = $("#form-generate").serialize();
        console.log(data);
        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: data,
            success: function(msg) {
                if (msg.trim() === '2') {
                    $.jGrowl('Please select sales.', {
                        header: 'Error Notification',
                        theme: 'alert-styled-right bg-danger'
                    });
                    return;
                }
                $("#show-data-all").html(msg);
                $("#modal-all").modal('show');
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
        return false;
    });

    function print_receipt() {
        var contents = $("#print_receipt_data").html();
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({
            "position": "absolute",
            "top": "-1000000px"
        });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write('<html><head><title></title>');
        frameDoc.document.write('</head><body>');
        frameDoc.document.write(contents);
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function() {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
    }

    function delivery_address(el) {

        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                update_delivery_address: '',
                sales_no: sales_no,
                delivery_address: $(el).val()
            },
            success: function(msg) {

            }

        });
    }

    function salesman(el) {

        $.ajax({
            type: 'POST',
            url: '../transaction.php',
            data: {
                update_salesman: '',
                sales_no: sales_no,
                salesman: $(el).val()
            },
            success: function(msg) {

            }

        });
    }
</script>


</html>