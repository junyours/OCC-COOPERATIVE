<?php
require('db_connect.php');

if (isset($_POST['query']) && !empty($_POST['query'])) {
    $query = $db->real_escape_string($_POST['query']);
    
    $sql = "SELECT CONCAT(first_name, ' ', last_name) as full_name 
            FROM tbl_members 
            WHERE (first_name LIKE '%$query%' OR last_name LIKE '%$query%' OR 
                   CONCAT(first_name, ' ', last_name) LIKE '%$query%')
            ORDER BY first_name, last_name 
            LIMIT 10";
    
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo '<div class="member-search" style="position: absolute; background: white; border: 1px solid #ddd; width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000;">';
        while ($row = $result->fetch_assoc()) {
            echo '<div onclick="select_member(\'' . addslashes($row['full_name']) . '\')" 
                     style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;" 
                     onmouseover="this.style.backgroundColor=\'#f5f5f5\'" 
                     onmouseout="this.style.backgroundColor=\'white\'">
                   ' . htmlspecialchars($row['full_name']) . '
                 </div>';
        }
        echo '</div>';
    }
}
?>
