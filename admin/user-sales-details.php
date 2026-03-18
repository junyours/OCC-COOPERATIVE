
<?php
  require('../db_connect.php');
?>
<div class="panel panel-white">
   <div class="panel-heading">
      <h6 class="panel-title"><i class="icon-chart text-teal-400"></i> List OF Sales<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
      <div class="heading-elements" style="padding-right: ">
         
      </div>
   </div>
   <div class="panel-body">
        <table class="table datatable-button-html5-basic table-hover table-bordered  ">
            <thead>
                   <tr style="border-bottom: 4px solid #ddd;background: #eee">
                     <th>Sales ID</th>
                     <th>Transaction #</th>
                     <th>Customer</th>
                     <th>Sub Total</th>
                     <th>Discount</th>
                     <th >Amount Due</th>
                   </tr>
            </thead>
            <tr>
               <?php 

                    $today = date("Y-m-d");
                    $start = strtotime('today GMT');
                    $date_add = date('Y-m-d', strtotime('+1 day', $start));
                    $query = "SELECT * FROM tbl_sales INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id  LEFT JOIN tbl_customer ON tbl_sales.cust_id=tbl_customer.cust_id  WHERE  sales_date BETWEEN  '$today' AND '$date_add' AND tbl_sales.user_id='".$user_id."' GROUP BY tbl_sales.sales_no ";
                     $result = $db->query($query);
                     while($row = $result->fetchArray()) {
               ?>
               <td><?= $row['sales_id']?></td>
               <td>0000000000<?= $row['sales_no']?></td>
               <td><?= $row['name']?></td>
               <td style="text-align: right;"><?= number_format($row['subtotal'],2) ?></td>
               <td style="text-align: right;"><?= number_format($row['discount'],2) ?></td>
               <td style="text-align: right;font-weight: bold;"><?= number_format($row['total_amount'],2) ?></td>
            </tr>
         <?php } ?>
         </table>
   </div>
    
</div>
</div>
