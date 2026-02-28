<?php require('includes/header.php'); ?>
<?php

if (
    !isset($_SESSION['is_login_yes'], $_SESSION['user_id'], $_SESSION['usertype'])
    || $_SESSION['is_login_yes'] != 'yes'
    || !in_array((int)$_SESSION['usertype'], [1, 3]) // allow usertype 1 OR 2
) {
    die("Unauthorized access.");
}

$unit = "SELECT * FROM tbl_units ORDER BY unit ASC";
$result_unit = $db->query($unit);
?>
<style>
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0px;
        font-weight: 800;
        color: white;
        text-decoration: none;
        font-size: 50px;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        object-fit: contain;
    }

    .navbar-brand span {
        white-space: nowrap;
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

    #table-product tbody tr {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #table-product tbody tr:hover {
        transform: scale(1.02);
        /* slightly bigger */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        /* subtle shadow pop */
    }

    #table-product tbody tr img {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #table-product tbody tr img:hover {
        transform: scale(1.1);
        /* image pop effect */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
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
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Products</h4>
                        </div>
                    </div>

                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li class="active"><i class="icon-barcode2 position-left"></i>Products</li>
                        </ul>

                        <ul class="breadcrumb-elements">
                            <li><a href="#" data-toggle="modal" data-target="#modal-new"><i class="icon-add position-left text-teal-400"></i> New Products</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /page header -->

                <!-- Content area -->
                <div class="content">
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-list text-teal-400 position-left"></i> List of Products</h6>
                        </div>
                        <input type="hidden" name='length_change' id='length_change' value="">
                        <div class="panel-body panel-theme">
                            <table class="table datatable-button-html5-basic table-hover table-bordered" id="table-product">
                                <thead>
                                    <tr class="tr-table">
                                        <th>Image</th>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>In Stock</th>
                                        <th>Selling Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM tbl_products";
                                    $result = $db->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        $image_file = !empty($row['image']) ? '../uploads/' . $row['image'] : '../images/no-image.png';
                                    ?>
                                        <tr style="cursor:pointer;" onclick="view_details(<?= $row['product_id'] ?>)">
                                            <td><img src="<?= $image_file ?>" style="width:90px;height:90px;border:2px solid #eee"></td>
                                            <td>21324<?= $row['product_id'] ?></td>
                                            <td><b><?= $row['product_name'] ?></b></td>
                                            <td style="text-align:center"><b><?= $row['quantity'] ?></b> <?= $row['unit'] ?></td>
                                            <td style="text-align:right"><?= number_format($row['selling_price'], 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php require('includes/footer-text.php'); ?>
            </div>
        </div>
    </div>
    <!-- /page container -->

    <!-- New Product Modal -->
    <div id="modal-new" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-toggle="tooltip" title="Press Esc" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="modal-title">New Product Form</h5>
                </div>
                <div class="modal-body">
                    <form action="#" id="form-new" class="form-horizontal" data-toggle="validator" role="form">
                        <input type="hidden" name="save-product">
                        <div class="form-body" style="padding-top:20px">
                            <div id="display-msg"></div>

                            <!-- Product Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Product Name</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input onblur="checkProductDuplicate()" class="form-control currency" autocomplete="off" name="product_name" id="product_name" placeholder="Enter Product Name" type="text" required data-error="Product Name is required.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Product Code -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Product Code</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input class="form-control currency" autocomplete="off" name="product_code" id="product-code" placeholder="Enter product code" type="text" minlength="8" required data-error="Product Code is required & minimum 8 numbers.">
                                        <span class="input-group-addon text-teal" style="cursor:pointer" onclick="auto_generate()" title="Auto Generate"><i class="icon-database-refresh"></i></span>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Selling Price -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Selling Price</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input class="form-control filterme" autocomplete="off" name="selling_price" placeholder="Enter selling price" type="text" required data-error="Please enter valid amount.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Supplier Price -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Supplier Price</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input class="form-control filterme" autocomplete="off" name="supplier_price" placeholder="Enter supplier price" type="text" required data-error="Please enter valid amount.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Beginning Quantity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Beginning Quantity</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input class="form-control currency" autocomplete="off" name="quantity" onkeypress="return numbersonly(event)" placeholder="Enter quantity" type="text" required data-error="Please enter valid quantity.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Reorder Level -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Reorder Level</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input class="form-control currency" autocomplete="off" name="critical_qty" onkeypress="return numbersonly(event)" placeholder="Enter quantity" type="text" required data-error="Please enter valid quantity.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <!-- Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Unit</label>
                                <div class="col-sm-9">
                                    <div class="input-group input-group-xlg">
                                        <span class="input-group-addon"><i class="icon-pencil7"></i></span>
                                        <input type="text" class="form-control" placeholder="pcs,kg,ml,pack,box,etc." name="unit" required data-error="Please enter unit.">
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button id="btn-submit" type="submit" class="btn bg-teal-400 btn-labeled"><b><i class="icon-add"></i></b> Save Products</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require('includes/footer.php'); ?>
    <script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="../js/validator.min.js"></script>
    <script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>


    <script>
        $(document).ready(function() {
            // Initialize Datatable
            $.extend($.fn.dataTable.defaults, {
                autoWidth: false,
                iDisplayLength: 20,
                dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    searchPlaceholder: 'Type to filter...',
                    lengthMenu: '<span>Show:</span> _MENU_',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: '&rarr;',
                        previous: '&larr;'
                    }
                }
            });

            $('.datatable-button-html5-basic').DataTable({
                order: [
                    [3, 'asc']
                ],
                aLengthMenu: [
                    [20, 50, 100, 200, 500],
                    [20, 50, 100, 200, 500]
                ]
            });

            // Form submit
            $('#form-new').validator().on('submit', function(e) {
                if (e.isDefaultPrevented()) return false;

                e.preventDefault();
                $('#btn-submit').prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: '../transaction.php',
                    data: $('#form-new').serialize(),
                    dataType: 'json', // expect JSON
                    success: function(response) {
                        console.log('AJAX Response:', response); // <-- DEBUG HERE

                        if (response.status === 'success') {
                            window.location.href = 'product-details.php?product_id=' + response.product_id;
                        } else {
                            alert('Save failed: ' + response.message);
                            $('#btn-submit').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error, xhr.responseText);
                        alert('Something went wrong! Check console.');
                        $('#btn-submit').prop('disabled', false);
                    }
                });
                return false;
            });

        });

        // JS Functions
        function closer() {
            window.location.href = 'products.php';
        }

        function view_details(id) {
            window.location.href = 'product-details.php?product_id=' + id;
        }

        function auto_generate() {
            $.get('../transaction.php', {
                auto_generate: 1
            }, function(msg) {
                $('#product-code').val(msg.trim());
            });
        }

        function changePage(el) {
            $('#length_change').val($(el).attr('val')).trigger('change');
        }

        function checkProductDuplicate() {
            let name = $('#product_name').val().trim();
            if (name === '') return;
            $.get('../transaction.php', {
                checkproductExist: 1,
                product_name: name
            }, function(msg) {
                msg = msg.trim();
                if (msg === '1') {
                    $('#btn-submit').prop('disabled', true);
                    $('#display-msg').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button><b>Oh snap!</b> Product Name is already added.</div>');
                } else {
                    $('#btn-submit').prop('disabled', false);
                    $('#display-msg').html('');
                }
            });
        }
    </script>
</body>

</html>