<?php
require_once('../db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle member ID validation
    if (isset($_POST['validate_member'])) {
        $member_id = trim($_POST['member_id'] ?? '');
        
        $check_query = "SELECT member_id, first_name, last_name, email FROM tbl_members WHERE member_id = ?";
        $stmt = $db->prepare($check_query);
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $member_result = $stmt->get_result();
        
        if ($member_result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Member ID not found in cooperative system']);
            exit;
        }
        
        $member_data = $member_result->fetch_assoc();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Member ID verified! Found: ' . trim($member_data['first_name'] . ' ' . $member_data['last_name']),
            'member_data' => $member_data
        ]);
        exit;
    }
    
    // Handle registration
    if (isset($_POST['check_register'])) {
        $member_id = trim($_POST['member_id'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        
        // Validate member exists
        $check_query = "SELECT member_id, first_name, last_name, email FROM tbl_members WHERE member_id = ?";
        $stmt = $db->prepare($check_query);
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $member_result = $stmt->get_result();
        
        if ($member_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Member ID not found in cooperative system']);
            exit;
        }
        
        $member_data = $member_result->fetch_assoc();
        
        // Check if user already exists for this member
        $user_check = "SELECT user_id FROM tbl_users WHERE username = ? OR email = ?";
        $user_stmt = $db->prepare($user_check);
        $username = $member_id . '_' . $email; // Unique username
        $user_stmt->bind_param('ss', $username, $email);
        $user_stmt->execute();
        
        if ($user_stmt->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Account already exists for this member']);
            exit;
        }
        
        // Validate password
        if (strlen($password) < 8) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters']);
            exit;
        }
        
        if ($password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
            exit;
        }
        
        try {
            $db->begin_transaction();
            
            // Create customer record
            $customer_name = trim($member_data['first_name'] . ' ' . $member_data['last_name']);
            $customer_query = "INSERT INTO tbl_customer (name, address, contact, field_status) VALUES (?, ?, ?, 0)";
            $customer_stmt = $db->prepare($customer_query);
            $customer_address = $member_data['address'] ?? '';
            $customer_contact = $member_data['phone'] ?? '';
            $customer_stmt->bind_param('sss', $customer_name, $customer_address, $customer_contact);
            $customer_stmt->execute();
            $cust_id = $db->insert_id;
            
            // Create user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $fullname = $customer_name;
            $user_query = "INSERT INTO tbl_users (username, password, usertype, fullname, field_status) VALUES (?, ?, 4, ?, 0)";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bind_param('ssss', $username, $hashed_password, $fullname);
            $user_stmt->execute();
            $user_id = $db->insert_id;
            
            // Update member record with new user_id and cust_id
            $update_member = "UPDATE tbl_members SET user_id = ?, cust_id = ? WHERE member_id = ?";
            $update_stmt = $db->prepare($update_member);
            $update_stmt->bind_param('iii', $user_id, $cust_id, $member_id);
            $update_stmt->execute();
            
            $db->commit();
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Registration successful! You can now login with your Member ID and email.',
                'member_info' => [
                    'member_id' => $member_id,
                    'name' => $customer_name,
                    'username' => $username
                ]
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}

// Function to generate password suggestions
function generatePasswordSuggestions() {
    $suggestions = [];
    $words = ['Coop', 'Credit', 'Member', 'Unity', 'Trust', 'Future', 'Growth', 'Success', 'OCC2025', 'Opol', 'Cooperative'];
    $numbers = ['2025', '2026', '123', '456', '789', '888', '999'];
    $symbols = ['!', '@', '#', '$'];
    
    for ($i = 0; $i < 3; $i++) {
        $word = $words[array_rand($words)];
        $number = $numbers[array_rand($numbers)];
        $symbol = $symbols[array_rand($symbols)];
        $suggestions[] = $word . $number . $symbol;
    }
    
    return array_unique($suggestions);
}

// Handle password suggestion request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_password_suggestions'])) {
    echo json_encode(['suggestions' => generatePasswordSuggestions()]);
}
?>
