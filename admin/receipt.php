<?php
error_reporting(0);

require('db_connect.php');
if (isset($_GET['sales_no'])) {
    $sales_no = $_GET['sales_no'];
}



$query = "SELECT * FROM tbl_sales  INNER JOIN tbl_products ON tbl_sales.product_id=tbl_products.product_id INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id  LEFT JOIN tbl_customer  ON tbl_sales.cust_id=tbl_customer.cust_id WHERE tbl_sales.sales_no='" . $sales_no . "'  ";
//$query = "SELECT * FROM tbl_sales  INNER JOIN tbl_products ON tbl_sales.product_id=tbl_products.product_id INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id ORDER BY sales_id DESC LIMIT 0,1  ";


$result = $db->query($query);
while ($row = $result->fetch_assoc()) {
    $sales_id = '00000000' . $row['sales_id'];
    $dr_id = '0589' . $row['sales_id'];
    $cashier = $row['fullname'];
    $customer = $row['name'];
    $address = $row['address'];
    $total_amount = $row['total_amount'];
    $subtotal = $row['subtotal'];
    $other_amount = $row['other_amount'];
    $discount = $row['discount'];
    $discount_percent = $row['discount_percent'];
    $sales_no = $row['sales_no'];
    $amount_paid = $row['amount_paid'];
    $vat_sales = number_format($row['total_amount'] - ($row['tax_percent'] / 100), 2);
    $vat_amount = number_format($row['subtotal'] * ($row['tax_percent'] / 100), 2);
    $sales_date = $row['sales_date'];
    $unit = $row['unit'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Receipt</title>

</head>


<body>




    <div class="receipt-div" id="print-receipt">


        <style type="text/css">
            @page {
                margin: 10;
                font-size: 10 !important;
                /* font-family: calibri; */
            }

            @media print {

                p,
                table {
                    font-size: 10pt;
                }

                .pagebreak {
                    clear: both;
                    page-break-after: always;
                }

                table,
                figure {
                    page-break-inside: avoid;
                }

                table {
                    font-size: 80%;
                    border-spacing: 2px;
                }

            }

            .receipt-div {
                /* width: 230px; */
                /* background: red; */
                padding: 2px !important;
                font-family: calibri;
                font-size: 10pt !important;
            }

            .text-left {
                text-align: left;
            }

            .text-title {
                font-weight: bold;
                font-size: 20px;
                line-height: 1px !important;
            }

            .title-p {
                font-size: 10px;
                line-height: 1px !important;
                font-weight: 500;
                text-decoration: underline;
                font-weight: 600;
            }

            .title-p2 {
                font-size: 10px;
                line-height: 1px !important;
            }

            .sales-invoice {
                padding: 4px 10;
                width: 154px;
                text-align: center;
                border: 1px solid #000;
                border-radius: 5px;
                font-weight: 600;
                font-size: 10pt !important;
            }

            .table-order td,
            .table-order th {
                border: 1px solid black;
                padding: 1px;
                font-size: 10pt;
            }

            .table-order {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }

            .authorized {
                line-height: 0px !important;
                margin-left: 15px;
                font-size: 10pt;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .docoment-text {
                margin-top: 50px;
            }

            .heading {
                /* border: 1px solid #000;*/
                padding: 30px;
            }
        </style>
        <div class="heading">
            <table style="width: 100%;text-align: center;">
                <thead>
                    <tr valign="top">
                        <th class="text-left" width="60%" style="line-height: 3px;">
                            <div>
                                <p class="text-title">OPOL COMMUNITY COLLEGE <br> EMPLOYEES CREDIT COOPERATIVE</p>
                                <p class="text-title">POS</p>
                                <p class="title-p2" style="font-size:11px"></p>
                                <!-- <p class="title-p2" style="font-size:11px">Non Vat Reg. TIN 704-089-270-001</p> -->
                            </div>
                        </th>
                        <th class="text-right" width="40%" style="line-height: 3px;">
                            <div>

                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
            <br>
            <table style="width: 100%;">
                <thead>
                    <tr valign="top">
                        <th class="text-left" width="50%" style="line-height: 3px;">
                            <p style="font-size:10px">Date:</p>
                            <p style="font-size:10px">SOLD to: <?= $customer ?></p>
                            <p style="font-size:10px">Address: <?= $address ?></p>


                        </th>
                        <th class="text-right" width="50%" style="line-height: 3px;">
                            <span class="sales-invoice" style="font-size:10px">SALES INVOICE</span>
                            <p style="margin-top:20px;font-size:10px">Invoice No.<b><?= $sales_id ?><b></p>

                        </th>
                    </tr>
                    <thead>
            </table>
            <div style="min-height: 30px"></div>
            <table style="width: 100%;" class="table-order" border="1" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align:center;font-size:10px">QTY.</th>
                        <th style="width: 60px; text-align:center;font-size:10px">Unit</th>
                        <th class="text-center">ARTICLES</th>
                        <th style="width: 100px; text-align:center;font-size:10px">Unit Price</th>
                        <th style="width: 100px; text-align:center;font-size:10px">Amount</th>
                    </tr>
                    <thead>
                    <tbody>
                        <?php
                        $i = 0;
                        $result = $db->query($query);
                        while ($row2 = $result->fetch_assoc()) {
                            $i++;
                        ?>
                            <tr>
                                <td class="text-center" style="font-size:10px"><?= $row2['quantity_order'] ?></td>
                                <td class="text-center" style="font-size:10px"><?= $row2['quantity_order'] ?></td>
                                <td style="font-size:10px"><?= $row2['product_name'] ?></td>
                                <td class="text-right" style="font-size:10px"><?= number_format($row2['order_price'], 2) ?></td>
                                <td class="text-right" style="font-size:10px"><?= number_format($row2['order_price'] * $row2['quantity_order'], 2) ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right" style="font-size:10px">Total Sales</th>
                        <th class="text-right" style="font-size:10px"><?= number_format($subtotal, 2) ?></th>
                    <tr>
                    <tr>
                        <th colspan="4" class="text-right" style="font-size:10px">Discount(<?= $discount_percent ?>%)</th>
                        <th class="text-right" style="font-size:10px"><?= number_format($discount, 2) ?></th>
                    <tr>
                    <tr>
                        <th colspan="4" class="text-right" style="font-size:10px">TOTAL AMUNT DUE</th>
                        <th class="text-right" style="font-size:10px"><?= number_format($total_amount, 2) ?></th>
                    <tr>
                </tfoot>
            </table>
            <BR>
            <table style="width: 100%;text-align: center;" style="line-height: 3px;" ]>
                <thead>
                    <tr>
                        <th class="text-left" width="50%" style="line-height: 3px;">
                            <p style="font-size:10px">Issued by: </p>
                            <p style="font-size:10px"> _______________________</p>
                            <div class="authorized" style="font-size:10px">Authorized Signature</div>
                        </th>
                        <th class="text-right" width="50%" style="line-height: 3px;">
                            <div>
                                <p style="font-size:10px">Received by: </p>
                                <p style="font-size:10px"> _______________________</p>
                                <div class="authorized" style="font-size:10px">Authorized Signature</div>
                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
            <table style="width: 100%;text-align: center;" class="docoment-text" border="0" width="100%" cellpadding="0" cellspacing="0" style="line-height: 3px;">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">

                        </th>
                        <th class="text-right" width="50%" style="line-height: 3px;">
                            <div>

                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
        </div>
        <!-- other div -->
        <div class="pagebreak"></div>
        <div class="heading" style="margin-top: 50px !important">
            <table style="width: 100%;text-align: center;">
                <thead>
                    <tr valign="top">
                        <th class="text-left" width="60%" style="line-height: 3px;">
                            <div>
                                <p class="text-title">OCC COOPERATIVE</p>
                                <p class="text-title">POS</p>

                                <p class="title-p2" style="font-size:11px">Opol Community College Mis'Or</p>

                            </div>
                        </th>
                        <th class="text-right" width="40%" style="line-height: 3px;">
                            <div>

                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
            <br>
            <table style="width: 100%;">
                <thead>
                    <tr valign="top">
                        <th class="text-left" width="50%" style="line-height: 3px;">
                            <p style="font-size:10px">Date:</p>
                            <p style="font-size:10px">SOLD to: <?= $customer ?></p>
                            <p style="font-size:10px">Address: <?= $address ?></p>


                        </th>
                    </tr>
                    <thead>
            </table>

            <div style="min-height: 30px"></div>
            <table style="width: 100%;" class="table-order" border="1" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align:center;font-size:10px">QTY.</th>
                        <th style="width: 60px; text-align:center;font-size:10px">Unit</th>
                        <th class="text-center">ARTICLES</th>
                    </tr>
                    <thead>
                    <tbody>
                        <?php
                        $i = 0;
                        $result = $db->query($query);
                        while ($row2 = $result->fetch_assoc()) {
                            $i++;
                        ?>
                            <tr>
                                <td class="text-center" style="font-size:10px"><?= $row2['quantity_order'] ?></td>
                                <td class="text-center" style="font-size:10px"><?= $row2['quantity_order'] ?></td>
                                <td style="font-size:10px"><?= $row2['product_name'] ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px">&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
            </table>
            <BR>
            <table style="width: 100%;text-align: center;" style="line-height: 3px;">
                <thead>
                    <tr>
                        <th class="text-left" width="50%" style="line-height: 3px;">
                            <p style="font-size:10px">Issued by: </p>
                            <p style="font-size:10px"> _______________________</p>
                            <div class="authorized" style="font-size:10px">Authorized Signature</div>
                        </th>
                        <th class="text-right" width="50%" style="line-height: 3px;">
                            <div>
                                <p style="font-size:10px">Received by: </p>
                                <p style="font-size:10px"> _______________________</p>
                                <div class="authorized" style="font-size:10px">Authorized Signature</div>
                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
            <table style="width: 100%;text-align: center;" class="docoment-text" border="0" width="100%" cellpadding="0" cellspacing="0" style="line-height: 3px;">
                <thead>
                    <tr>
                        <th class="text-left" width="50%">

                        </th>
                        <th class="text-right" width="50%" style="line-height: 3px;">
                            <div>
                                <!-- <p class="title-p" style="font-size:10px">"THIS DOCUMENT IS NOT VALID FOR</p>
                               <p class="title-p" style="font-size:10px">CLAIMING INPUT TAXES"</p> -->
                            </div>
                        </th>
                    </tr>
                    <thead>
            </table>
        </div>



    </div>
</body>

</html>