<?php
require('../db_connect.php');
$query = "SELECT * FROM tbl_receivings  INNER JOIN tbl_products ON tbl_receivings.product_id=tbl_products.product_id INNER JOIN tbl_users ON tbl_receivings.user_id=tbl_users.user_id  LEFT JOIN tbl_supplier  ON tbl_receivings.supplier_id=tbl_supplier.supplier_id WHERE tbl_receivings.receiving_no='" . $receiving_no . "'  ";
$total = 0;
$result = $db->query($query);
while ($row = $result->fetch_assoc()) {
    $cashier = $row['fullname'];
    $supplier = $row['supplier_name'];
    $quantity = $row['receiving_quantity'];
    $price = $row['receiving_price'];
    $sub_total = $quantity * $price;
    $total += $sub_total;
    $receiving_no = $row['receiving_no'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Receipt</title>
    <style type="text/css">
        .receipt-div {
            width: 300px;
            background: #eee;
            /*color: #0505ca;*/
            padding: 15px;
            font-family: calibri;
        }

        .heading {
            width: 100%;
            text-align: center;
            margin-left: 20px;
        }

        .table-product {
            width: 100%;

        }

        .product-name {
            max-width: 80px;
        }

        .text-right {
            text-align: right;
        }

        .table-summary,
        .table-total,
        .barcode-div {
            width: 100%;
        }

        .table-total {}

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="content" style="margin: 0px!important">
        <div class="row">
            <div class="col-xs-6">
                <div class="panel panel-white border-top-xlg border-top-teal-400">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Details</h6>
                    </div>
                    <!-- <input type="text" id="myInputTextField"> -->

                    <div class="panel-body">
                        <table class="table datatable-button-html5-basic table-hover table-bordered   dataTable no-footer">
                            <tr>
                                <td>Employee</td>
                                <td style="text-align: center;font-weight: bold"><?= $cashier ?></td>
                            </tr>
                            <tr>
                                <td>Supplier</td>
                                <td style="text-align: center;font-weight: bold"><?= $supplier ?></td>
                            </tr>

                            <tr>
                                <td>Total</td>
                                <td style="text-align: right;font-weight: bold"><?= number_format($total, 2) ?></td>
                            </tr>

                        </table>

                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="panel panel-white border-top-xlg border-top-teal-400">
                    <div class="panel-heading">
                        <h6 class="panel-title"><i class="icon-list position-left text-teal-400"></i> Products</h6>
                    </div>
                    <!-- <input type="text" id="myInputTextField"> -->

                    <div class="panel-body">

                        <table class="table datatable-button-html5-basic table-hover table-bordered   dataTable no-footer">
                            <tr>
                                <thead>
                                    <th width="70%">Product Name</th>
                                    <th style="text-align: center;">Quantity</th>
                                    <th style="text-align: right;">Price</th>
                                </thead>
                            </tr>
                            <?php
                            $result = $db->query($query);
                            while ($row2 = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td class="product-name" width="50%"><?= $row2['product_name'] ?></td>
                                    <td class="text-center"><?= $row2['receiving_quantity'] ?></td>
                                    <td class="text-center"><?= number_format($row2['receiving_price'], 2) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>