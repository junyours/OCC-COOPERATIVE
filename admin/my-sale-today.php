<?php session_start(); ?>
<?php
require('../db_connect.php');

?>
<table class="table  datatable-button-html5-basic table-hover table-bordered">
    <thead>
        <tr style="border-bottom: 4px solid #ddd;background: #eee;color: #333">
            <th>Date</th>
            <th>Bill No.</th>
            <th>Employee</th>
            <th>Customer</th>
            <th>Sub Total</th>
            <th>Discount</th>
            <th>Amount Due</th>
            <!-- <th>Action</th>
             -->
        </tr>
    </thead>
    <tbody>
        <?php
        $today = date("Y-m-d");
        $tomorrow = date("Y-m-d", strtotime("+1 day"));

        $sql = "
SELECT 
    MAX(s.sales_id) AS sales_id,
    s.sales_no,
    MAX(s.sales_date) AS sales_date,
    MAX(u.fullname) AS fullname,
    MAX(c.name) AS customer_name,
    SUM(s.subtotal) AS subtotal,
    SUM(s.discount) AS discount,
    SUM(s.total_amount) AS total_amount
FROM tbl_sales s
INNER JOIN tbl_users u ON s.user_id = u.user_id
LEFT JOIN tbl_customer c ON s.cust_id = c.cust_id
WHERE s.sales_date >= ? 
AND s.sales_date < ?
AND s.user_id = ?
GROUP BY s.sales_no
ORDER BY sales_date DESC
";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi", $today, $tomorrow, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $total = 0;

        while ($row = $result->fetch_assoc()) {
            $total += $row['total_amount'];

            // Proper 8-digit padding
            $sales_id = str_pad($row['sales_id'], 8, "0", STR_PAD_LEFT);
        ?>
            <tr style="color:#333">
                <td><?= date('F d, Y h:i A', strtotime($row['sales_date'])) ?></td>
                <td><?= $sales_id ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td style="text-align:right;"><?= number_format($row['subtotal'], 2) ?></td>
                <td style="text-align:right;"><?= number_format($row['discount'], 2) ?></td>
                <td style="text-align:right;"><?= number_format($row['total_amount'], 2) ?></td>
                <!-- <td align="center">
                <?php if ($row['sales_status'] == 3) { ?>
                <label class="label label-danger">Cancelled</label>
                <?php } else { ?>
                <label class="label label-success">Active</label>
                <?php } ?>
            </td>
            <td align="center">
                <?php if ($row['sales_type'] == 1) { ?>
                <label class="label label-success"></label>
                <p><b>Order #</b><?= $row['sales_no'] ?><p>
                <?php } else { ?>
                <label class="label label-primary">Default</label>
                <?php } ?>
            </td> -->
            </tr>
        <?php } ?>
    </tbody>
</table>
<div id="modal-confirm" class="modal fade">
    <input type="hidden" id="cancel-input">
    <input type="hidden" id="sales-id-input">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Confirmation!!!</h5>
            </div>
            <div class="modal-bodys">
                <h3>Are you sure you wan't to cancel this sale?</h3>
                <h5>Sales Details and Product Inventory will updated. </h5>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn bg-danger-400 btn-labeled"><b><i class="icon-cancel-square"></i></b> NO</button>
                <a type="button" id="delete_sale" onclick="delete_sale()" href="javascript:;" class="btn bg-teal-400 btn-labeled"><b><i class="icon-checkbox-checked"></i></b> YES</a>
            </div>
        </div>
    </div>
</div>
</div>
<!-- Core JS files -->
<script type="text/javascript" src="../assets/js/core/libraries/jquery.min.js"></script>
<script type="text/javascript" src="../assets/js/core/libraries/bootstrap.min.js"></script>
<!-- /core JS files -->
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../assets/js/plugins/notifications/jgrowl.min.js"></script>
<script type="text/javascript">
    $(function() {

        // Table setup
        // ------------------------------
        // Setting datatable defaults
        $.extend($.fn.dataTable.defaults, {
            autoWidth: false,
            dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
            language: {
                search: '<span></span> _INPUT_',
                searchPlaceholder: 'Type to search...',
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
                [0, "asc"]
            ],
            buttons: {

            }
        });


    });

    function delete_sales(el) {
        var sales_no = $(el).attr('sales_no');
        $("#sales-id-input").val(sales_no);
        $("#cancel-input").val('yes');
        $("#modal-confirm").modal('show');
    }

    function delete_sale() {
        $("#delete_sale").attr('disabled', true);
        $.ajax({
            type: 'GET',
            url: '../transaction.php',
            data: {
                delete_sales: "",
                sales_id: $("#sales-id-input").val()
            },
            success: function(msg) {
                console.log(msg);
                $.jGrowl('Sales successfully cancelled.', {
                    header: 'Success Notification',
                    theme: 'alert-styled-right bg-success'
                });

                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(msg) {
                alert('Something went wrong!');
            }
        });
    }
</script>