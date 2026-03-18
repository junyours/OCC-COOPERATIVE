<?php
    session_start();
    require('../db_connect.php');  
        $menu_id = $_POST['menu_id']; 
        $result_returns = "SELECT * FROM  tbl_menu  WHERE menu_id='$menu_id'  ";
        $result_data = $db->query($result_returns);
        $datas = $result_data->fetchArray();
        if ($datas[2]!="") {
        	 unlink('../uploads/'.$datas[2]);
        } 
        $temp = explode(".", $_FILES["fileToUpload"]["name"]);
        $newfilename = md5($menu_id) . '.' . end($temp); 
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], "../uploads/" . $newfilename)) 
        {    
            $query = "UPDATE tbl_menu set image_link='".$newfilename."' WHERE menu_id='".$menu_id."'";
            $db->exec($query);    
            $arrayData = array('menu_id' => $menu_id, 'user_id' => $_SESSION['user_id'] );
			$arrayGDetails = json_encode($arrayData);
			$insert_history = "INSERT INTO tbl_history (details,history_type) VALUES ('$arrayGDetails','28')";
			$db->exec($insert_history) ;        
        }

?>