<?php session_start(); ?>
<?php
require('../db_connect.php');
$query = "SELECT * FROM tbl_customer";
$result = $db->query($query);
?>
<table class="table datatable-button-html5-basic table-hover table-bordered  ">
   <thead>
      <tr style="border-bottom: 4px solid #ddd;background: #eee">
         <th>Customer ID</th>
         <th>Name</th>
         <th>Address</th>
         <th>Contact</th>

      </tr>
   </thead>
   <tbody>
      <?php while ($row = $result->fetch_assoc()) { ?>
         <tr>
            <td><?= $row['cust_id']; ?></td>
            <td><?= $row['name']; ?></td>
            <td><?= $row['address']; ?></td>
            <td><?= $row['contact']; ?></td>
         </tr>
      <?php } ?>
   </tbody>
</table>

<!-- Core JS files -->
<script type="text/javascript" src="../assets/js/core/libraries/jquery.min.js"></script>
<!-- /core JS files -->
<script type="text/javascript" src="../assets/js/plugins/tables/datatables/datatables.min.js"></script>
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
            [0, "desc"]
         ],
         buttons: {

         }
      });


   });
</script>