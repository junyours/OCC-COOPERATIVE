<?php require('includes/header.php'); ?>

<body class="layout-boxed navbar-top">
    <!-- Main navbar -->
    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.html"><img style="height: 40px!important" src="../images/farmers-logo.png" alt=""></a>
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
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span></h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li class="active"><i class="icon-home2 position-left"></i> Dashboard</li>
                        </ul>
                    </div>
                </div>
                <!-- /page header -->
                <?php require('includes/footer-text.php'); ?>
                <!-- Content area -->
                <div class="content">
                    <?php
                    // current date $january_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y',sales_date) = strftime('%Y',date('now'))  AND strftime('%m',sales_date) = strftime('%m',date('now'))  GROUP BY sales_no ";

                    #january
                    $january = date("Y" . "-01");
                    $january_total = 0;
                    $january_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$january'  GROUP BY sales_no ";
                    $result_january = $db->query($january_query);
                    while ($row_january = $result_january->fetch_assoc()) {
                        $january_subtotal = $row_january['total_amount'];
                        $january_total += $january_subtotal;
                    }
                    #/january

                    #febuary
                    $febuary = date("Y" . "-02");
                    $febuary_total = 0;
                    $febuary_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$febuary'  GROUP BY sales_no ";
                    $result_febuary = $db->query($febuary_query);
                    while ($result_febuary = $result_febuary->fetchArray()) {
                        $febuary_subtotal = $row_febuary['total_amount'];
                        $febuary_total += $febuary_subtotal;
                    }
                    #/febuary


                    #march
                    $march = date("Y" . "-03");
                    $march_total = 0;
                    $march_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$march'  GROUP BY sales_no ";
                    $result_march = $db->query($march_query);
                    while ($row_febuary = $result_march->fetch_assoc()) {
                        $march_subtotal = $row_febuary['total_amount'];
                        $march_total += $march_subtotal;
                    }
                    #/march

                    #april
                    $april = date("Y" . "-04");
                    $april_total = 0;
                    $april_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$april'  GROUP BY sales_no ";
                    $result_april = $db->query($april_query);
                    while ($row_febuary = $result_april->fetch_assoc()) {
                        $april_subtotal = $row_febuary['total_amount'];
                        $april_total += $april_subtotal;
                    }
                    #/april

                    #may
                    $may = date("Y" . "-05");
                    $may_total = 0;
                    $may_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$may'  GROUP BY sales_no ";
                    $result_may = $db->query($may_query);
                    while ($row_may = $result_may->fetch_assoc()) {
                        $may_subtotal = $row_may['total_amount'];
                        $may_total += $may_subtotal;
                    }
                    #/may

                    #june
                    $june = date("Y" . "-06");
                    $june_total = 0;
                    $june_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$june'  GROUP BY sales_no ";
                    $result_june = $db->query($june_query);
                    while ($row_june = $result_june->fetch_assoc()) {
                        $june_subtotal = $row_june['total_amount'];
                        $june_total += $june_subtotal;
                    }
                    #/june

                    #june
                    $july = date("Y" . "-07");
                    $july_total = 0;
                    $july_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$july'  GROUP BY sales_no ";
                    $result_july = $db->query($july_query);
                    while ($row_july = $result_july->fetch_assoc()) {
                        $july_subtotal = $row_july['total_amount'];
                        $july_total += $july_subtotal;
                    }
                    #/june

                    #august
                    $august = date("Y" . "-08");
                    $august_total = 0;
                    $august_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$august'  GROUP BY sales_no ";
                    $result_august = $db->query($august_query);
                    while ($row_august = $result_august->fetch_assoc()) {
                        $august_subtotal = $row_august['total_amount'];
                        $august_total += $august_subtotal;
                    }
                    #/august

                    #september
                    $september = date("Y" . "-09");
                    $september_total = 0;
                    $september_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$september'  GROUP BY sales_no ";
                    $result_september = $db->query($september_query);
                    while ($row_september = $result_september->fetch_assoc()) {
                        $september_subtotal = $row_september['total_amount'];
                        $september_total += $september_subtotal;
                    }
                    #/september

                    #october
                    $october = date("Y" . "-10");
                    $october_total = 0;
                    $october_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$october'  GROUP BY sales_no ";
                    $result_october = $db->query($october_query);
                    while ($row_october = $result_october->fetch_assoc()) {
                        $october_subtotal = $row_october['total_amount'];
                        $october_total += $october_subtotal;
                    }
                    #/october

                    #november
                    $november = date("Y" . "-11");
                    $november_total = 0;
                    $november_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$november'  GROUP BY sales_no ";
                    $result_november = $db->query($november_query);
                    while ($row_november = $result_november->fetch_assoc()) {
                        $november_subtotal = $row_november['total_amount'];
                        $november_total += $november_subtotal;
                    }
                    #/november


                    #december
                    $december = date("Y" . "-12");
                    $december_total = 0;
                    $december_query = "SELECT * FROM tbl_sales WHERE   strftime('%Y-%m', sales_date) = '$december'  GROUP BY sales_no ";
                    $result_december = $db->query($december_query);
                    while ($row_december = $result_december->fetch_assoc()) {
                        $december_subtotal = $row_november['total_amount'];
                        $december_total += $december_subtotal;
                    }
                    #/december


                    ?>

                    <?php
                    $today = date("Y-m-d");
                    $start = strtotime('today GMT');
                    $date_add = date('Y-m-d', strtotime('+1 day', $start));
                    $query = "SELECT * FROM tbl_sales WHERE  sales_date BETWEEN  '$today' AND '$date_add' GROUP BY sales_no ";
                    $result = $db->query($query);
                    $all_subtotal = 0;
                    $all_discount = 0;
                    $all_total = 0;
                    $total_sales = 0;
                    while ($row = $result->fetchArray()) {
                        $subtotal = $row['subtotal'];
                        $discount = $row['discount'];
                        $total_amount = $row['total_amount'];
                        $all_subtotal += $subtotal;
                        $all_discount += $discount;
                        $all_total += $total_amount;
                        $total_sales++;
                    }
                    $vat_sales = $all_subtotal * .12;

                    $customer_select = "SELECT count(*) AS total_customer FROM tbl_customer ";
                    $customer_result = $db->query($customer_select);
                    $customer_row = $customer_result->fetchArray();
                    $customer_total = $customer_row['total_customer'];

                    $user_select = "SELECT count(*) AS total_user FROM tbl_users WHERE usertype!=1 ";
                    $user_result = $db->query($user_select);
                    $user_row = $user_result->fetchArray();
                    $user_total = $user_row['total_user'];

                    $supplier_select = "SELECT count(*) AS total_supplier FROM tbl_supplier ";
                    $supplier_result = $db->query($supplier_select);
                    $supplier_row = $supplier_result->fetchArray();
                    $supplier_total = $supplier_row['total_supplier'];

                    ?>
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body">
                                <div class="media no-margin">
                                    <div class="media-body">
                                        <h3 class="no-margin text-semibold"><?= $total_sales ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">today's Sale</span>
                                    </div>
                                    <div class="media-right media-middle">
                                        <i class="icon-cart icon-3x text-danger-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body panel-body-accent">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-users icon-3x text-success-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold"><?= $user_total ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Employee</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-users icon-3x text-indigo-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold"><?= $customer_total ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Customer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="panel panel-body">
                                <div class="media no-margin">
                                    <div class="media-left media-middle">
                                        <i class="icon-users icon-3x text-blue-400"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h3 class="no-margin text-semibold"><?= $supplier_total ?></h3>
                                        <span class="text-uppercase text-size-mini text-muted">Supplier</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Daily Sales</h6>
                        </div>
                        <!-- <input type="text" id="myInputTextField"> -->
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-3">
                                    <div class="panel panel-body bg-blue-400 has-bg-image">
                                        <div class="media no-margin">
                                            <div class="media-body">
                                                <h3 class="no-margin"><?= number_format($all_subtotal, 2) ?></h3>
                                                <span class="text-uppercase text-size-mini">Sub Total</span>
                                            </div>
                                            <div class="media-right media-middle">
                                                <i class="icon-cash icon-3x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="panel panel-body bg-danger-400 has-bg-image">
                                        <div class="media no-margin">
                                            <div class="media-body">
                                                <h3 class="no-margin"><?= number_format($all_discount, 2) ?></h3>
                                                <span class="text-uppercase text-size-mini">Discount</span>
                                            </div>
                                            <div class="media-right media-middle">
                                                <i class="icon-3x opacity-75">%</i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="panel panel-body bg-success-400 has-bg-image">
                                        <div class="media no-margin">
                                            <div class="media-left media-middle">
                                                <i class="icon-cash icon-3x opacity-75"></i>
                                            </div>
                                            <div class="media-body text-right">
                                                <h3 class="no-margin"><?= number_format($vat_sales, 2) ?></h3>
                                                <span class="text-uppercase text-size-mini">Vat Sales</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="panel panel-body bg-indigo-400 has-bg-image">
                                        <div class="media no-margin">
                                            <div class="media-left media-middle">
                                                <i class="icon-cash icon-3x opacity-75"></i>
                                            </div>
                                            <div class="media-body text-right">
                                                <h3 class="no-margin"><?= number_format($all_total, 2) ?></h3>
                                                <span class="text-uppercase text-size-mini">Total Amount</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-white border-top-xlg border-top-teal-400">
                                <div class="panel-heading">
                                    <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> Latest Sytem History</h6>
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
                                            $query = "SELECT * FROM tbl_history  LIMIT 0,10 ";
                                            $result = $db->query($query);
                                            $i = 0;
                                            $details_data = "";
                                            $result = $db->query($query);
                                            while ($row = $result->fetchArray()) {
                                                $i++;
                                                $details = json_decode($row['details']);
                                                $user_id = $details->user_id;
                                                $query_user = "SELECT * FROM tbl_users WHERE user_id='$user_id'";
                                                $result_user = $db->query($query_user);
                                                $data_user = $result_user->fetchArray();
                                                if ($row['history_type'] == 1) {
                                                    $history_type = "New Sales";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000' . $details->sales_no . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 2) {
                                                    $history_type = "Delete Sales";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000' . $details->sales_no . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 3) {
                                                    $history_type = "Set Active Sales";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> OR #: 0000000000' . $details->sales_no . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 11) {
                                                    $history_type = "New Product";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324' . $details->product_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 12) {
                                                    $history_type = " Product Damage";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324' . $details->product_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 13) {
                                                    $history_type = "Update Product Info";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324' . $details->product_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 14) {
                                                    $history_type = "Upload Product Image";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Product ID: 21324' . $details->product_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 15) {
                                                    $history_type = "New Customer";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Customer ID: 34236' . $details->cust_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 17) {
                                                    $history_type = "New Supplier";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Supplier ID: 762345' . $details->supplier_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 19) {
                                                    $history_type = "New Employee";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Employee ID: 87989' . $details->user_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 22) {
                                                    $history_type = "Receivings";
                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Receiving ID: 0000000023' . $details->user_id . ' <i class="icon-user text-teal-400"></i> Employee : ' . $data_user['fullname'] . ' ';
                                                } elseif ($row['history_type'] == 40) {
                                                    $history_type = "Loan Application";

                                                    // decode details JSON
                                                    $details = json_decode($row['details']);

                                                    // fetch user
                                                    $query_user = "SELECT * FROM tbl_users WHERE user_id='" . $details->user_id . "'";
                                                    $result_user = $db->query($query_user);
                                                    $data_user = $result_user->fetchArray();

                                                    // fetch customer
                                                    $query_cust = "SELECT * FROM tbl_customer WHERE cust_id='" . $details->cust_id . "'";
                                                    $result_cust = $db->query($query_cust);
                                                    $data_cust = $result_cust->fetchArray();

                                                    $details_data = '<i class="icon-barcode2 text-teal-400"></i> Member: ' . $data_cust['name'] .
                                                        ' <i class="icon-coin-dollar text-teal-400"></i> Amount: ' . $details->amount .
                                                        ' <i class="icon-hour-glass2 text-teal-400"></i> Term: ' . $details->term . ' months' .
                                                        ' <i class="icon-user text-teal-400"></i> Employee: ' . $data_user['fullname'];
                                                } else {
                                                    $history_type = $row['history_type'];
                                                    $details_data = "Not Set";
                                                }

                                            ?>
                                                <td><?= $row['history_id'] ?></td>
                                                <td><?= $row['date_history'] ?></td>
                                                <td><?= $history_type ?></td>
                                                <td><?= $details_data ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php if ($i == 0) { ?>
                                        <tr>
                                            <td colspan="10" align="center">
                                                <h2>No data found!</h2>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </table>
                                    <br>
                                    <div align="right"><a href="system-history.php">View All History <i class="icon-circle-right2"></i></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-calendar position-left"></i><b>2025</b> Monthly Sales</h6>
                        </div>
                        <div class="panel-body" style="background-color: #263238; color: #fff; ">
                            <!-- Styles -->
                            <style>
                                #chartdiv {
                                    width: 100%;
                                    height: 500px;
                                }
                            </style>
                            <!-- Resources -->
                            <script src="../amchart/amcharts.js"></script>
                            <script src="../amchart/serial.js"></script>
                            <script src="../amchart/export.min.js"></script>
                            <link rel="stylesheet" href="../amchart/export.css" type="text/css" media="all" />
                            <script src="../amchart/black.js"></script>
                            <!-- Chart code -->
                            <script>
                                var chart = AmCharts.makeChart("chartdiv", {
                                    "theme": "black",
                                    "type": "serial",
                                    "startDuration": 2,
                                    "dataProvider": [{
                                        "country": "January",
                                        "visits": <?= $january_total ?>,
                                        "color": "#FF0F00"
                                    }, {
                                        "country": "Febuary",
                                        "visits": <?= $febuary_total ?>,
                                        "color": "#FF6600"
                                    }, {
                                        "country": "March",
                                        "visits": <?= $march_total ?>,
                                        "color": "#FF9E01"
                                    }, {
                                        "country": "April",
                                        "visits": <?= $april_total ?>,
                                        "color": "#FCD202"
                                    }, {
                                        "country": "May",
                                        "visits": <?= $may_total ?>,
                                        "color": "#F8FF01"
                                    }, {
                                        "country": "June",
                                        "visits": <?= $june_total ?>,
                                        "color": "#B0DE09"
                                    }, {
                                        "country": "July",
                                        "visits": <?= $july_total ?>,
                                        "color": "#04D215"
                                    }, {
                                        "country": "August",
                                        "visits": <?= $august_total ?>,
                                        "color": "#0D8ECF"
                                    }, {
                                        "country": "September",
                                        "visits": <?= $september_total ?>,
                                        "color": "#0D52D1"
                                    }, {
                                        "country": "October",
                                        "visits": <?= $october_total ?>,
                                        "color": "#2A0CD0"
                                    }, {
                                        "country": "November",
                                        "visits": <?= $november_total ?>,
                                        "color": "#8A0CCF"
                                    }, {
                                        "country": "December",
                                        "visits": <?= $december_total ?>,
                                        "color": "#CD0D74"
                                    }],
                                    "valueAxes": [{
                                        "position": "left",
                                        "title": "Visitors"
                                    }],
                                    "graphs": [{
                                        "balloonText": "[[category]]: <b>[[value]]</b>",
                                        "fillColorsField": "color",
                                        "fillAlphas": 1,
                                        "lineAlpha": 0.1,
                                        "type": "column",
                                        "valueField": "visits"
                                    }],
                                    "depth3D": 20,
                                    "angle": 30,
                                    "chartCursor": {
                                        "categoryBalloonEnabled": false,
                                        "cursorAlpha": 0,
                                        "zoomable": false
                                    },
                                    "categoryField": "country",
                                    "categoryAxis": {
                                        "gridPosition": "start",
                                        "labelRotation": 90
                                    },
                                    "export": {
                                        "enabled": false
                                    }

                                });
                            </script>
                            <!-- HTML -->
                            <div id="chartdiv"></div>
                        </div>
                    </div>
                    <!-- /content area -->
                </div>
                <!-- /main content -->
            </div>
            <!-- /page content -->
        </div>
        <!-- /page container -->
</body>
<?php require('includes/footer.php'); ?>

</html>