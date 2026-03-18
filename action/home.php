<?php
function check_user($data){
    require('db_connect.php');

    // Get user by username only
    $stmt = $db->prepare("SELECT * FROM tbl_users WHERE username = ? AND field_status IN (0,1)");
    $stmt->bind_param("s", $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verify the password hash
        if (password_verify($data['password'], $user['password'])) {
            return $user;
        } else {
            return false; // password mismatch
        }
    } else {
        return false; // username not found
    }
}
?>
