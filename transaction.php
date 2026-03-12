<?php
ini_set('max_execution_time', 0);
session_start();
date_default_timezone_set('Asia/Manila');
date_default_timezone_get();
//error_reporting(0);
require 'password_email.php';
require('action/home.php');
require('action/admin.php');
require('backup.php');

$base_url = "http://localhost:8080/api/public/api/";

// autoBackupDaily();


if (isset($_GET['admin-logout'])) {
    session_destroy();
    header('Location: index.php');
}

if (isset($_POST['check-login'])) {
    require('db_connect.php');

    $data = array(
        'username' => $_POST['username'],
        'password' => $_POST['password']
    );

    $check = check_user($data);

    if ($check) {
        if ($check['usertype'] == 1) {
            $_SESSION['session_type'] = 'admin';
            echo "1";
        } elseif ($check['usertype'] == 2) {
            $_SESSION['session_type'] = 'cashier';
            echo "2";
        } elseif ($check['usertype'] == 4) {
            $_SESSION['session_type'] = 'member';
            echo "4";
        } else {
            $_SESSION['session_type'] = 'treasurer';
            echo "3";
        }

        $_SESSION['is_login_yes'] = 'yes';
        $_SESSION['user_id'] = $check['user_id'];
        $_SESSION['username'] = $check['username'];
        $_SESSION['usertype'] = $check['usertype'];
        $_SESSION['fullname'] = $check['fullname'];

        $arrayData = array('user_id' => $check['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        // MySQLi query
        $insert_history = $db->prepare("INSERT INTO tbl_history (date_history, details, history_type) VALUES (?, ?, ?)");
        $history_type = 26;
        $insert_history->bind_param("ssi", $today, $arrayGDetails, $history_type);
        $insert_history->execute();
    } else {
        echo "5";
    }
}


if (isset($_POST['save-product'])) {
    require('db_connect.php');

    // Collect and sanitize input
    $data = array(
        'product_code'   => $_POST['product_code'],
        'unit'           => $_POST['unit'],
        'product_name'   => mysqli_real_escape_string($db, $_POST['product_name']),
        'quantity'       => $_POST['quantity'],
        'critical_qty'   => $_POST['critical_qty'],
        'selling_price'  => $_POST['selling_price'],
        'supplier_price' => $_POST['supplier_price']
    );


    save_product($data);
}

// if (isset($_POST['save-category'])) {
//     $data = array('category_name' => $_POST['category_name']);
//     save_category($data);
// }

if (isset($_POST['save-menu'])) {
    require('db_connect.php');
    $is_track = isset($_POST['is_track']) ? 1 : 0;

    $temp = explode(".", $_FILES["fileToUpload"]["name"]);
    $newfilename = md5(rand()) . '.' . end($temp);
    $quantity_product = 0;
    $image_ext = end($temp);

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], "./uploads/" . $newfilename)) {
        $product_name = mysqli_real_escape_string($db, $_POST['product_name']);
        $selling_price = $_POST['selling_price'];
        $product_code = $_POST['product_code'];
        $unit = $_POST['unit'];
        $cat_id = $_POST['cat_id'];

        $query = "INSERT INTO tbl_menu 
            (menu_name, price, image_link, quantity, product_code, unit, is_track, cat_id) 
            VALUES ('$product_name', '$selling_price', '$newfilename', '$quantity_product', '$product_code', '$unit', '$is_track', '$cat_id')";

        if ($db->query($query)) {
            $result_returns = "SELECT * FROM tbl_menu ORDER BY menu_id DESC LIMIT 1";
            $result_data = $db->query($result_returns);
            $datas = $result_data->fetch_assoc();

            $arrayData = array('menu_id' => $product_code, 'user_id' => $_SESSION['user_id']);
            $arrayGDetails = mysqli_real_escape_string($db, json_encode($arrayData));
            $today = date("Y-m-d H:i:s");

            $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) VALUES ('$today', '$arrayGDetails', '30')";
            $db->query($insert_history);

            echo $datas['menu_id'];
        }
    }
}

if (isset($_POST['save-menu-inventory'])) {
    require('db_connect.php');
    $menu_id = $_POST['menu_id'];
    $quantity = $_POST['quantity'];

    for ($i = 0; $i < count($menu_id); $i++) {
        $quantityData = $quantity[$i] != "" ? $quantity[$i] : 0;

        $menus = "SELECT * FROM tbl_menu WHERE menu_id='" . $menu_id[$i] . "'";
        $result_menus = $db->query($menus);
        $row = $result_menus->fetch_assoc();

        $balance_quantity = $quantityData + $row['quantity'];

        $query_update = "UPDATE tbl_menu SET quantity='" . $balance_quantity . "' WHERE menu_id='" . $menu_id[$i] . "'";
        if ($db->query($query_update)) {
            echo "1";
        }
    }
}




if (isset($_GET['check_employee_duplicate'])) {
    require('db_connect.php'); // $db is MySQLi connection
    $name = trim($_GET['check_employee_duplicate']);

    // Prepare MySQLi statement
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM tbl_users WHERE fullname = ?");
    $stmt->bind_param("s", $name); // "s" = string
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo ($row['cnt'] > 0) ? "exists" : "ok";

    $stmt->close();
    exit;
}

if (isset($_GET['check_username_duplicate'])) {
    require('db_connect.php');
    $username = trim($_GET['check_username_duplicate']);

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM tbl_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo ($row['cnt'] > 0) ? "exists" : "ok";
    $stmt->close();
    exit;
}

if (isset($_POST['save-cashier'])) {

    require('db_connect.php');

    $name     = mysqli_real_escape_string($db, $_POST['name']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];
    $status   = isset($_POST['field_status']) ? 0 : 1;


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO tbl_users 
              (fullname, username, password, usertype, field_status) 
              VALUES 
              ('$name', '$username', '$hashed_password', '$usertype', '$status')";

    if ($db->query($query)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['update-cashier'])) {

    require 'db_connect.php';

    $user_id  = intval($_POST['user_id']);
    $fullname = $db->real_escape_string($_POST['name']);
    $password = trim($_POST['password']);

    // Get current data
    $check = $db->query("SELECT password, fullname FROM tbl_users WHERE user_id = $user_id");
    $old   = $check->fetch_assoc();

    $updates = [];
    $changed = false;


    if ($fullname !== $old['fullname']) {
        $updates[] = "fullname = '$fullname'";
        $changed = true;
    }


    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $updates[] = "password = '$hashed'";
        $changed = true;
    }


    if (!$changed) {
        echo "no_changes";
        exit;
    }

    $sql = "UPDATE tbl_users SET " . implode(', ', $updates) . " WHERE user_id = $user_id";

    if ($db->query($sql)) {
        echo "1";
    } else {
        echo "0";
    }
}


if (isset($_POST['update-cash'])) {

    $data = array('amount' => $_POST['amount']);
    update_cash($data);
}


if (isset($_POST['save-customer'])) {

    $data = array(
        'first_name' => trim($_POST['first_name']),
        'last_name'  => trim($_POST['last_name']),
        'middle_name'=> trim($_POST['middle_name']),
        'gender'     => $_POST['gender'],
        'email'      => trim($_POST['email']),

        'address'    => trim($_POST['address']),
        'contact'    => trim($_POST['contact']),
        'member_type' => $_POST['member_type'],
        'capital_share' => isset($_POST['capital_share']) ? floatval($_POST['capital_share']) : 0
    );

    echo save_member($data);
    exit;
}


function save_member($data)
{
    require('db_connect.php');

    $full_name = $data['first_name'] . ' ' . $data['last_name'];




    $db->begin_transaction();

    try {
        // Insert into tbl_customer
        $stmt = $db->prepare("INSERT INTO tbl_customer (name, address, contact) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $data['address'], $data['contact']);
        if (!$stmt->execute()) throw new Exception("Customer Insert Error: " . $stmt->error);
        $cust_id = $stmt->insert_id;
        $stmt->close();

        // Insert into tbl_users

        $plain_password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'), 0, 8);


        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
        $usertype = 4;

        $stmt = $db->prepare("INSERT INTO tbl_users (username, password, usertype, fullname) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $data['email'], $hashed_password, $usertype, $full_name);
        if (!$stmt->execute()) throw new Exception("User Insert Error: " . $stmt->error);
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Insert into tbl_members
        $stmt = $db->prepare(
            "INSERT INTO tbl_members
    (user_id, cust_id, first_name, last_name, middle_name,gender, email, address, phone, type)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iissssssss",
            $user_id,
            $cust_id,
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['gender'],
            $data['email'],
            $data['address'],
            $data['contact'],
            $data['member_type']
        );
        if (!$stmt->execute()) throw new Exception("Member Insert Error: " . $stmt->error);
        $member_id = $stmt->insert_id;
        $stmt->close();

        // Ensure account type exists
        $stmt = $db->prepare("SELECT account_type_id FROM account_types WHERE type_name='capital_share' LIMIT 1");
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) throw new Exception("Account type 'Capital Share' missing");
        $stmt->bind_result($account_type_id);
        $stmt->fetch();
        $stmt->close();

        // Insert account
        $stmt = $db->prepare("INSERT INTO accounts (member_id, account_type_id, account_number) VALUES (?, ?, ?)");
        $account_number = 'CS-' . str_pad($member_id, 6, '0', STR_PAD_LEFT);
        $stmt->bind_param("iis", $member_id, $account_type_id, $account_number);
        if (!$stmt->execute()) throw new Exception("Account Insert Error: " . $stmt->error);
        $account_id = $stmt->insert_id;
        $stmt->close();

        // Insert initial capital share transaction if regular member
        if ($data['member_type'] === 'regular' && $data['capital_share'] > 0) {
            // Ensure transaction type exists
            $stmt = $db->prepare("SELECT transaction_type_id FROM transaction_types WHERE type_name='capital_share' LIMIT 1");
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) throw new Exception("Transaction type 'Capital Share' missing");
            $stmt->bind_result($transaction_type_id);
            $stmt->fetch();
            $stmt->close();




            $reference_no = 'CS-' . str_pad($account_id, 6, '0', STR_PAD_LEFT);

            $stmt = $db->prepare(
                "INSERT INTO transactions 
    (account_id, transaction_type_id, amount, transaction_date, reference_no, remarks) 
    VALUES (?, ?, ?, NOW(), ?, ?)"
            );

            $remarks = 'Initial Capital Share';

            $stmt->bind_param(
                "iidss",
                $account_id,
                $transaction_type_id,
                $data['capital_share'],
                $reference_no,
                $remarks
            );

            if (!$stmt->execute()) {
                throw new Exception("Capital Share Transaction Insert Error: " . $stmt->error);
            }

            $stmt->close();
        }

        $db->commit();

        /* SEND EMAIL */
        if (!sendMemberEmail($data['email'], $full_name, $plain_password)) {
            error_log("Email sending failed for: " . $data['email']);
        }

        return "1";
    } catch (Exception $e) {
        $db->rollback();
        return "0";
    }
}



if (isset($_POST['update-customer'])) {
    $data = array('cust_id' => $_POST['cust_id'], 'name' => $_POST['name'], 'address' => $_POST['address'], 'contact' => $_POST['contact']);
    update_customer($data);
}

if (isset($_POST['save-supplier'])) {
    if (isset($_POST['receiving-input'])) {
        $receiving_data = 'yes';
    } else {
        $receiving_data = 'no';
    }
    $data = array('name' => $_POST['supplier_name'], 'address' => $_POST['supplier_address'], 'contact' => $_POST['supplier_contact'], 'receiving_data' => $receiving_data);
    save_supplier($data);
}

if (isset($_POST['update-supplier'])) {
    if (isset($_POST['receiving-input'])) {
        $receiving_data = 'yes';
    } else {
        $receiving_data = 'no';
    }
    $data = array('supplier_id' => $_POST['supplier_id'], 'name' => $_POST['supplier_name'], 'address' => $_POST['supplier_address'], 'contact' => $_POST['supplier_contact'], 'receiving_data' => $receiving_data);
    update_supplier($data);
}

if (isset($_POST['save-customer-sales'])) {
    $data = array('name' => $_POST['name'], 'address' => $_POST['address'], 'contact' => $_POST['contact']);
    save_customer_sales($data);
}

if (isset($_POST['save_cart'])) {
    $is_update = trim($_POST['is_update']);
    $data = array('type' => $_POST['type'], 'product_id' => $_POST['product_id'], 'quantity' => $_POST['quantity'], 'user_id' => $_SESSION['user_id']);
    if ($is_update == 'true') {
        save_cartupdate($data);
    } else {
        save_cart($data);
    }
}

if (isset($_POST['save_cart2'])) {
    $data = array('product_id' => $_POST['product_id'], 'quantity' => $_POST['quantity'], 'user_id' => $_SESSION['user_id']);
    save_cart2($data);
}


if (isset($_POST['save_cartbarcode'])) {

    require('db_connect.php');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['message' => 'unauthenticated']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $barcode = trim($_POST['barcode']);
    $barcode = preg_replace('/[^\x20-\x7E]/', '', $barcode);


    $barcode = $db->real_escape_string($barcode);

    // Fetch product
    $stmt = $db->prepare("SELECT product_id, quantity FROM tbl_products WHERE product_code = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: application/json');

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $product_id = $row['product_id'];
        $product_stock = (int)$row['quantity'];

        // Check current cart
        $stmt_cart = $db->prepare("SELECT quantity_order FROM tbl_cart WHERE product_id = ? AND user_id = ?");
        $stmt_cart->bind_param("ii", $product_id, $user_id);
        $stmt_cart->execute();
        $cart_result = $stmt_cart->get_result();

        $current_qty = 0;
        if ($cart_result && $cart_result->num_rows > 0) {
            $cart_data = $cart_result->fetch_assoc();
            $current_qty = (int)$cart_data['quantity_order'];
        }

        $desired_qty = $current_qty + 1;

        if ($desired_qty > $product_stock) {
            echo json_encode([
                'message' => 'unsave',
                'quantity_order' => $desired_qty,
                'quantity_left' => $product_stock
            ]);
        } else {
            // Save or update cart
            save_cart([
                'type' => 'add',
                'product_id' => $product_id,
                'quantity' => 1,
                'user_id' => $user_id
            ], true);

            echo json_encode(['message' => 'save']);
        }
    } else {
        // Product not found
        echo json_encode(['message' => 'not_found']);
    }

    exit;
}

if (isset($_POST['save_cart2barcode'])) {



    require('db_connect.php');

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['message' => 'unauthenticated']);
        exit; // ✅ STOP SCRIPT
    }

    $user_id = $_SESSION['user_id'];

    $barcode = trim($_POST['barcode']);
    $barcode = preg_replace('/[^\x20-\x7E]/', '', $barcode);

    $stmt = $db->prepare("SELECT product_id, quantity, supplier_price FROM tbl_products WHERE product_code = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $product_id = $row['product_id'];
        $current_stock = (int)$row['quantity'];
        $supplier_price = $row['supplier_price'];

        // Increase stock
        $new_stock = $current_stock + 1;
        $stmt_update = $db->prepare("UPDATE tbl_products SET quantity = ? WHERE product_id = ?");
        $stmt_update->bind_param("ii", $new_stock, $product_id);
        $stmt_update->execute();

        // Receiving cart logic
        $stmt_cart = $db->prepare("SELECT cart_id, quantity_order FROM tbl_cart2 WHERE product_id = ? AND user_id = ?");
        $stmt_cart->bind_param("ii", $product_id, $user_id);
        $stmt_cart->execute();
        $cart_result = $stmt_cart->get_result();

        if ($cart_result->num_rows > 0) {
            $cart_data = $cart_result->fetch_assoc();
            $new_qty = $cart_data['quantity_order'] + 1;

            $stmt_up_cart = $db->prepare("UPDATE tbl_cart2 SET quantity_order = ? WHERE cart_id = ?");
            $stmt_up_cart->bind_param("ii", $new_qty, $cart_data['cart_id']);
            $stmt_up_cart->execute();
        } else {
            $stmt_ins = $db->prepare("INSERT INTO tbl_cart2 (product_id, quantity_order, user_id, price) VALUES (?, 1, ?, ?)");
            $stmt_ins->bind_param("iid", $product_id, $user_id, $supplier_price);
            $stmt_ins->execute();
        }

        echo json_encode([
            'message' => 'save',
            'product_id' => $product_id,
            'new_stock' => $new_stock,
            'user_id' => $user_id
        ]);
        exit;
    }

    echo json_encode(['message' => 'not_found']);
    exit;
}



// if (isset($_POST['save-cart2'])) {
//     $data = array('product_id' => $_POST['product_id'], 'quantity' => $_POST['quantity'], 'user_id' => $_SESSION['user_id']);
//     save_cart2($data);
// }

if (isset($_GET['view_cart'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_cart($data);
}

if (isset($_GET['view_cart_panda'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_cart_panda($data);
}

if (isset($_GET['view_cart_update'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_cart_update($data);
}

if (isset($_GET['view_cart2'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_cart2($data);
}

if (isset($_GET['delete_cart'])) {
    $data = array('cart_id' => $_GET['cart_id']);
    delete_cart($data);
}

if (isset($_GET['delete_cart_update'])) {

    $data = array('cart_id' => $_GET['cart_id']);
    delete_cart_update($data);
}

if (isset($_GET['delete_cart2'])) {
    $data = array('cart_id' => $_GET['cart_id']);
    delete_cart2($data);
}

if (isset($_GET['cancel_receiving'])) {
    require('db_connect.php');

    $query = "DELETE FROM tbl_cart2 WHERE user_id='" . $_SESSION['user_id'] . "'";
    $db->query($query);   // MySQL uses query(), not exec()
}

if (isset($_GET['cancel_sale'])) {
    require('db_connect.php');

    $query = "DELETE FROM tbl_cart WHERE user_id='" . $_SESSION['user_id'] . "'";
    $db->query($query);   // MySQL uses query(), not exec()
}


if (isset($_GET['update_cart'])) {
    $data = array('cart_id' => $_GET['cart_id'], 'quantity_order' => $_GET['new_quantity_order']);
    update_cart($data);
}

if (isset($_GET['update_cart3'])) {
    $data = array('cart_id' => $_GET['cart_id'], 'quantity_order' => $_GET['new_quantity_order']);
    update_cart3($data);
}



if (isset($_GET['update_price'])) {
    $data = array('cart_id' => $_GET['cart_id'], 'price' => $_GET['price']);
    update_price($data);
}

if (isset($_GET['update_price_panda'])) {
    $data = array('cart_id' => $_GET['cart_id'], 'price' => $_GET['price']);
    update_price_panda($data);
}


if (isset($_GET['update_cart2'])) {
    $data = array('cart_id' => $_GET['cart_id'], 'quantity_order' => $_GET['new_quantity_order']);
    update_cart2($data);
}

if (isset($_GET['other_amount'])) {
    $_SESSION['other_amount'] = $_GET['amount'];
}

if (isset($_GET['payment_type'])) {
    if ($_GET['type'] == 1) {
        $_SESSION['payment_type'] = false;
    } else {
        $_SESSION['payment_type'] = true;
        $_SESSION['other_amount'] = 0;
    }
}


if (isset($_POST['save-payment'])) {

    require('db_connect.php');

    $user_id = $_SESSION['user_id'] ?? 0;
    if ($user_id == 0) {
        echo json_encode(['success' => 0, 'error' => 'Invalid session']);
        exit;
    }

    $po_no   = $_SESSION['po_no'] ?? '';
    $check_no = $_SESSION['check_no'] ?? '';
    $amount_paid = $_POST['payment'] ?? 0;

    // GET TAX
    $query_tax = "SELECT tax FROM tbl_settings LIMIT 1";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $tax_percent = $datas_tax['tax'] ?? 0;

    // OTHER AMOUNT
    $other_amount = $_SESSION['other_amount'] ?? 0;

    // SALES NUMBER
    $sales_no = '0000' . round(microtime(true) * 100) . $user_id;

    // CUSTOMER
    $cust_id = $_SESSION['pos-customer'] ?? 1;

    // GET CART ITEMS (FIRST PASS – TOTALS)
    $products_sql = "
        SELECT c.*, p.selling_price, p.quantity 
        FROM tbl_cart c 
        INNER JOIN tbl_products p ON c.product_id = p.product_id 
        WHERE c.user_id = '$user_id'
    ";

    $result_products = $db->query($products_sql);

    if ($result_products->num_rows == 0) {
        echo json_encode(['success' => 0, 'error' => 'Cart is empty']);
        exit;
    }

    $subtotal_amount = 0;
    $discount_percent = 0;

    while ($row = $result_products->fetch_assoc()) {
        $subtotal_amount += $row['selling_price'] * $row['quantity_order'];
        $discount_percent = $row['discount'];
    }

    $discount_amount = ($subtotal_amount * $discount_percent) / 100;
    $total_amount = $subtotal_amount - $discount_amount + $other_amount;

    // PAYMENT TYPE & BALANCE
    $payment_type = isset($_SESSION['payment_type'])
        ? (int) $_SESSION['payment_type']
        : 1; // default CASH


    // Ensure numeric values
    $total_amount = (float) $total_amount;
    $amount_paid  = (float) $amount_paid;

    // Safely calculate balance
    $balance = max($total_amount - $amount_paid, 0);

    $sales_date = date("Y-m-d H:i:s");
    $success = 0;

    // START TRANSACTION
    $db->begin_transaction();

    try {

        // SECOND PASS – INSERT SALES & UPDATE STOCK
        $result_products = $db->query($products_sql);

        while ($row = $result_products->fetch_assoc()) {

            $product_id = $row['product_id'];
            $quantity_order = $row['quantity_order'];
            $price = $row['selling_price'];
            $balance_quantity = $row['quantity'] - $quantity_order;

            if ($balance_quantity < 0) {
                throw new Exception("Insufficient stock for product ID $product_id");
            }

            $today = date("Y-m-d H:i:s");

            // PRODUCT HISTORY
            $db->query("
                INSERT INTO tbl_product_history
                (hist_date, details, details_type, product_id, qty, balance, type)
                VALUES
                ('$today', '$sales_no', '1', '$product_id', '$quantity_order', '$balance_quantity', '1')
            ");

            // UPDATE STOCK
            $db->query("
                UPDATE tbl_products 
                SET quantity = '$balance_quantity' 
                WHERE product_id = '$product_id'
            ");

            // INSERT SALES
            $db->query("
                INSERT INTO tbl_sales
                (
                    user_id, product_id, quantity_order, subtotal, total_amount,
                    discount_percent, discount, cust_id, sales_no, tax_percent,
                    amount_paid, order_price, sales_date, other_amount,
                    balance, sales_type, po_no, check_no
                )
                VALUES
                (
                    '$user_id', '$product_id', '$quantity_order',
                    '$subtotal_amount', '$total_amount',
                    '$discount_percent', '$discount_amount',
                    '$cust_id', '$sales_no', '$tax_percent',
                    '$amount_paid', '$price', '$sales_date',
                    '$other_amount', '$balance', '$payment_type',
                    '$po_no', '$check_no'
                )
            ");
        }

        // INSERT HISTORY LOG
        $history_data = json_encode([
            'sales_no' => $sales_no,
            'user_id'  => $user_id
        ]);

        $db->query("
            INSERT INTO tbl_history (date_history, details, history_type)
            VALUES ('$sales_date', '$history_data', '1')
        ");

        // CLEAR CART
        $db->query("DELETE FROM tbl_cart WHERE user_id = '$user_id'");

        // CLEAR SESSIONS
        unset(
            $_SESSION['pos-customer'],
            $_SESSION['pos-name'],
            $_SESSION['po_no'],
            $_SESSION['check_no'],
            $_SESSION['other_amount']
        );

        $db->commit();
        $success = 1;
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
        exit;
    }

    echo json_encode([
        'success'  => $success,
        'sales_no' => $sales_no
    ]);
}


if (isset($_POST['update-payment'])) {

    require('db_connect.php');

    // Restore product quantities from old sales
    $sales_select = "
        SELECT * 
        FROM tbl_sales 
        INNER JOIN tbl_products 
            ON tbl_sales.product_id = tbl_products.product_id 
        WHERE sales_no = '{$_POST['sales_no']}'
    ";
    $result_sales_select = $db->query($sales_select);

    while ($rowsales = $result_sales_select->fetch_assoc()) {

        $product_id = $rowsales['product_id'];
        $quantity = $rowsales['quantity'];
        $quantity_order = $rowsales['quantity_order'];
        $price = $rowsales['selling_price'];

        $balance_quantity = $quantity + $quantity_order;

        $query_history = "
            INSERT INTO tbl_product_history (product_id, qty, balance, type)
            VALUES ('$product_id', '$quantity_order', '$balance_quantity', '2')
        ";
        $db->query($query_history);

        $query_update = "
            UPDATE tbl_products 
            SET quantity = '$balance_quantity' 
            WHERE product_id = '$product_id'
        ";
        $db->query($query_update);
    }

    // Remove old sales
    $quer3 = "DELETE FROM tbl_sales WHERE sales_no = '{$_POST['sales_no']}'";
    $db->query($quer3);

    $amount_paid = $_POST['payment'];

    // Get tax
    $query_tax = "SELECT * FROM tbl_settings";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $tax_percent = $datas_tax['tax'];

    $sales_no = $_POST['sales_no'];
    $data_success = "";

    // Cart items
    $products = "
        SELECT * 
        FROM tbl_cart3 
        INNER JOIN tbl_products 
            ON tbl_cart3.product_id = tbl_products.product_id 
        WHERE tbl_cart3.user_id = '{$_SESSION['user_id']}'
    ";
    $result_products = $db->query($products);

    $subtotal_amount = 0;
    $discount = 0;

    $cust_id = !empty($_SESSION['pos-customer']) ? $_SESSION['pos-customer'] : 1;

    // Calculate totals
    while ($row = $result_products->fetch_assoc()) {
        $quantity_order2 = $row['quantity_order'];
        $price2 = $row['selling_price'];
        $discount = $row['discount'];

        $subtotal = $price2 * $quantity_order2;
        $subtotal_amount += $subtotal;
    }

    $sub_total = $subtotal_amount;
    $total_amount = $sub_total - $discount;
    $sales_date = date("Y-m-d H:i:s");

    // Re-run query for insert loop
    $result_products = $db->query($products);

    while ($row = $result_products->fetch_assoc()) {

        $product_id = $row['product_id'];
        $quantity = $row['quantity'];
        $quantity_order = $row['quantity_order'];
        $price = $row['selling_price'];

        $balance_quantity = $quantity - $quantity_order;

        $query_history = "
            INSERT INTO tbl_product_history (product_id, qty, balance, type)
            VALUES ('$product_id', '$quantity_order', '$balance_quantity', '1')
        ";
        $db->query($query_history);

        $query_update = "
            UPDATE tbl_products 
            SET quantity = '$balance_quantity' 
            WHERE product_id = '$product_id'
        ";
        $db->query($query_update);

        $query = "
            INSERT INTO tbl_sales
            (
                user_id, product_id, quantity_order, subtotal, total_amount,
                discount, cust_id, sales_no, tax_percent,
                amount_paid, order_price, sales_date
            )
            VALUES
            (
                '{$_SESSION['user_id']}', '$product_id', '$quantity_order',
                '$sub_total', '$total_amount', '$discount',
                '$cust_id', '$sales_no', '$tax_percent',
                '$amount_paid', '$price', '$sales_date'
            )
        ";

        if ($db->query($query)) {
            $data_success = 1;
            unset($_SESSION['pos-custid_update']);
            unset($_SESSION['pos-customer_update']);
        }
    }

    // History log
    $arrayData = array(
        'sales_no' => $sales_no,
        'user_id'  => $_SESSION['user_id']
    );

    $arrayGDetails = json_encode($arrayData);
    $today = date("Y-m-d H:i:s");

    $insert_history = "
        INSERT INTO tbl_history (date_history, details, history_type)
        VALUES ('$today', '$arrayGDetails', '4')
    ";
    $db->query($insert_history);

    // Clear cart
    $quer2 = "DELETE FROM tbl_cart3 WHERE user_id = '{$_SESSION['user_id']}'";
    $db->query($quer2);

    echo json_encode(array(
        'success' => $data_success,
        'sales_no' => $sales_no
    ));
}



if (isset($_GET['save_receiving'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    save_receiving($data);
}

if (isset($_GET['view_total'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_total($data);
}

if (isset($_GET['view_total_panda'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_total_panda($data);
}

if (isset($_GET['view_total_update'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_total_update($data);
}

if (isset($_GET['view_total2'])) {
    $data = array('user_id' => $_SESSION['user_id']);
    view_total2($data);
}

if (isset($_POST['save-discount'])) {
    $data = array('user_id' => $_SESSION['user_id'], 'discount' =>  $_POST['discount']);
    save_discount($data);
}

if (isset($_POST['save-discount2'])) {
    $data = array('user_id' => $_SESSION['user_id'], 'discount' =>  $_POST['discount']);
    save_discount2($data);
}

if (isset($_POST['keywords_search'])) {
    $data = array('keywords_search' => $_POST['keywords_search']);
    searh_product($data);
}

if (isset($_GET['search_customer'])) {
    $data = array('keywords_search' => $_GET['keywords_search']);
    searh_customer($data);
}

if (isset($_GET['search_user'])) {
    $data = array('keywords_search' => $_GET['keywords']);
    searh_user($data);
}

if (isset($_GET['search_supplier'])) {
    $data = array('keywords_search' => $_GET['keywords']);
    searh_supplier($data);
}

if (isset($_GET['session_customer'])) {
    if ($_GET['is_update'] == 'true') {
        $_SESSION['pos-customer_update'] =  $_GET['name'];
        $_SESSION['pos-custid_update'] = $_GET['cust_id'];
    } else {
        $_SESSION['pos-customer'] = $_GET['cust_id'];
        $_SESSION['pos-name'] = $_GET['name'];
    }
}

if (isset($_GET['session_supplier'])) {
    $_SESSION['pos-supplier'] = $_GET['supplier_id'];
    $_SESSION['pos-supplier-name'] = $_GET['name'];
}

if (isset($_POST['submit-sales'])) {

    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['sale-report'] = $daterange;
    $_SESSION['sale-report-from'] = $myDateFROM;
    $_SESSION['sale-report-to'] = $myDateTO;

    if ($_POST['date-required'] != NULL) {
        $_SESSION['sales-date-required'] = "yes";
    } else {
        unset($_SESSION['sales-date-required']);
    }

    if ($_POST['custname'] != "") {
        $_SESSION['sale-report-customer'] = $_POST['cust_id'];
    } else {
        unset($_SESSION['sale-report-customer']);
    }
    if ($_POST['username'] != "") {
        $_SESSION['sale-report-user'] = $_POST['user_id'];
    } else {
        unset($_SESSION['sale-report-user']);
    }

    if ($_POST['status'] != "") {
        $_SESSION['sale-report-status'] = $_POST['status'];
    } else {
        unset($_SESSION['sale-report-status']);
    }

    if ($_POST['register'] != "") {
        $_SESSION['sale-report-register'] = $_POST['register'];
    } else {
        unset($_SESSION['sale-report-register']);
    }

    if ($_POST['sales-type'] != "") {
        $_SESSION['sale-report-type'] = $_POST['sales-type'];
    } else {
        unset($_SESSION['sale-report-type']);
    }
}

if (isset($_POST['clear_filter_sales'])) {
    unset($_SESSION['sale-report']);
    unset($_SESSION['sale-report-from']);
    unset($_SESSION['sale-report-to']);
    unset($_SESSION['sale-report-customer']);
    unset($_SESSION['sale-report-user']);
    unset($_SESSION['sale-report-status']);
    unset($_SESSION['sale-report-register']);
    unset($_SESSION['sales-date-required']);
    unset($_SESSION['sale-report-type']);
}

if (isset($_POST['history-report'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['history-report'] = $daterange;
    $_SESSION['history-report-from'] = $myDateFROM;
    $_SESSION['history-report-to'] = $myDateTO;
    if ($_POST['type'] != "") {
        $_SESSION['sale-report-type'] = $_POST['type'];
    } else {
        unset($_SESSION['sale-report-type']);
    }
}
if (isset($_POST['clear_filter_history'])) {
    unset($_SESSION['history-report']);
    unset($_SESSION['history-report-from']);
    unset($_SESSION['history-report-to']);
    unset($_SESSION['sale-report-type']);
}

if (isset($_POST['employee-report'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['employee-report'] = $daterange;
    $_SESSION['employee-report-from'] = $myDateFROM;
    $_SESSION['employee-report-to'] = $myDateTO;
    $_SESSION['employee-report-user'] = $_POST['user_id'];
}



if (isset($_POST['daily-sales-report'])) {
    $daterange = $_POST['date'];
    $myDateFROM = date("Y-m-d", strtotime($daterange));
    $_SESSION['daily-report-input'] = $_POST['date'];
    $_SESSION['daily-report'] = $myDateFROM;
}

if (isset($_POST['daily-sales-report2'])) {
    $daterange = $_POST['date'];
    $myDateFROM = date("Y-m-d", strtotime($daterange));
    $_SESSION['daily-report-input2'] = $_POST['date'];
    $_SESSION['daily-report2'] = $myDateFROM;
}

if (isset($_POST['submit-receiving'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['receiving-report'] = $daterange;
    $_SESSION['receiving-report-from'] = $myDateFROM;
    $_SESSION['receiving-report-to'] = $myDateTO;
    if ($_POST['custname'] != "") {
        $_SESSION['receiving-report-customer'] = $_POST['cust_id'];
    } else {
        unset($_SESSION['receiving-report-customer']);
    }
    if ($_POST['username'] != "") {
        $_SESSION['receiving-report-user'] = $_POST['user_id'];
    } else {
        unset($_SESSION['receiving-report-user']);
    }

    if ($_POST['date-required'] != NULL) {
        $_SESSION['receiving-date-required'] = "yes";
    } else {
        unset($_SESSION['receiving-date-required']);
    }
}

if (isset($_POST['submit-delivery'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['delivery-report'] = $daterange;
    $_SESSION['delivery-report-from'] = $myDateFROM;
    $_SESSION['delivery-report-to'] = $myDateTO;
    if ($_POST['product_name'] != "") {
        $_SESSION['delivered-report-product'] = $_POST['product_id'];
    } else {
        unset($_SESSION['delivered-report-product']);
    }

    if ($_POST['supllier'] != "") {
        $_SESSION['delivered-report-supplier'] = $_POST['supplier_id'];
    } else {
        unset($_SESSION['delivered-report-supplier']);
    }
    var_dump($_POST);
}


if (isset($_POST['clear_filter_receiving'])) {
    unset($_SESSION['receiving-report-customer']);
    unset($_SESSION['receiving-report-user']);
    unset($_SESSION['receiving-report']);
    unset($_SESSION['receiving-report-from']);
    unset($_SESSION['receiving-report-to']);
    unset($_SESSION['receiving-date-required']);
}


if (isset($_POST['submit-expences'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['expences-report'] = $daterange;
    $_SESSION['expences-report-from'] = $myDateFROM;
    $_SESSION['expences-report-to'] = $myDateTO;
    if ($_POST['username'] != "") {
        $_SESSION['expences-report-user'] = $_POST['user_id'];
    } else {
        unset($_SESSION['expences-report-user']);
    }

    if ($_POST['date-required'] != NULL) {
        $_SESSION['expences-date-required'] = "yes";
    } else {
        unset($_SESSION['expences-date-required']);
    }
}



if (isset($_POST['submit-damage-report'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['damage-report'] = $daterange;
    $_SESSION['damage-report-from'] = $myDateFROM;
    $_SESSION['damage-report-to'] = $myDateTO;
    if ($_POST['product_name'] != "") {
        $_SESSION['damage-report-product'] = $_POST['product_id'];
    } else {
        unset($_SESSION['damage-report-product']);
    }
}

if (isset($_POST['clear_filter_damage'])) {
    unset($_SESSION['damage-report']);
    unset($_SESSION['damage-report-from']);
    unset($_SESSION['damage-report-to']);
    unset($_SESSION['damage-report-product']);
}

if (isset($_POST['submit-inventory-report'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['inv-report'] = $daterange;
    $_SESSION['inv-report-from'] = $myDateFROM;
    $_SESSION['inv-report-to'] = $myDateTO;
    if ($_POST['product_name'] != "") {
        $_SESSION['inv-report-product'] = $_POST['product_id'];
    } else {
        unset($_SESSION['inv-report-product']);
    }
}


if (isset($_POST['clear_filter_expences'])) {
    unset($_SESSION['expences-report-user']);
    unset($_SESSION['expences-report']);
    unset($_SESSION['expences-report-from']);
    unset($_SESSION['expences-report-to']);
    unset($_SESSION['expences-date-required']);
}

if (isset($_POST['submit-invetory'])) {
    if ($_POST['username'] != "") {
        $_SESSION['inventory-report-product'] = $_POST['user_id'];
    } else {
        unset($_SESSION['inventory-report-product']);
    }
}


if (isset($_POST['clear_filter_inventory'])) {
    unset($_SESSION['inventory-report-product']);
}


if (isset($_POST['submit-sold'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['sold-report'] = $daterange;
    $_SESSION['sold-report-from'] = $myDateFROM;
    $_SESSION['sold-report-to'] = $myDateTO;
    if ($_POST['username'] != "") {
        $_SESSION['sold-report-product'] = $_POST['user_id'];
    } else {
        unset($_SESSION['sold-report-product']);
    }

    if ($_POST['date-required'] != NULL) {
        $_SESSION['sold-date-required'] = "yes";
    } else {
        unset($_SESSION['sold-date-required']);
    }
}

if (isset($_POST['clear_filter_sold'])) {
    unset($_SESSION['sold-report']);
    unset($_SESSION['sold-report-from']);
    unset($_SESSION['sold-report-to']);
    unset($_SESSION['sold-report-product']);
    unset($_SESSION['sold-date-required']);
}


if (isset($_GET['search_products'])) {
    $data = array('keywords' => $_GET['keywords']);
    searh_product($data);
}

if (isset($_GET['searh_menu'])) {
    $data = array('keywords' => $_GET['keywords']);
    searh_menu($data);
}

if (isset($_POST['generate-barcode'])) {
    $barcode = $_POST['generate-barcode'];
    $quantity = $_POST['quantity'];
    require('admin/generate_barcode.php');
}

if (isset($_POST['save_damage'])) {
    $data = array('product_id' => $_POST['product_id'], 'quantity' => $_POST['quantity'], 'notes' => $_POST['notes'], 'user_id' => $_SESSION['user_id']);
    save_damage($data);
}

if (isset($_POST['save_deduc'])) {
    $data = array('product_id' => $_POST['product_id'], 'quantity' => $_POST['quantity'], 'user_id' => $_SESSION['user_id']);
    save_deduc($data);
}

if (isset($_POST['save_deduc_menu'])) {
    $data = array('menu_id' => $_POST['menu_id'], 'quantity' => $_POST['quantity'], 'user_id' => $_SESSION['user_id']);
    save_deduc_menu($data);
}

if (isset($_GET['auto_generate'])) {

    require('db_connect.php');

    $random = randomNumber(8);

    $query  = "SELECT * FROM tbl_products WHERE product_code = '$random'";
    $result = $db->query($query);
    $data   = $result->fetch_assoc();

    if ($data) {
        $random = randomNumber(8);
    }

    echo $random;
}

if (isset($_GET['auto_generate_menu'])) {

    require('db_connect.php');

    $random = randomNumber(8);

    $query  = "SELECT * FROM tbl_menu WHERE product_code = '$random'";
    $result = $db->query($query);
    $data   = $result->fetch_assoc();

    if ($data) {
        $random = randomNumber(8);
    }

    echo $random;
}

function randomNumber($length)
{
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}



if (isset($_POST['update-product'])) {
    $data = array('product_id' => $_POST['product_id'], 'product_code' => $_POST['product_code'], 'unit' => $_POST['unit'], 'product_name' => SQLite3::escapeString($_POST['product_name']), 'critical_qty' => $_POST['critical_qty'], 'selling_price' => $_POST['selling_price'], 'supplier_price' => $_POST['supplier_price']);  //var_dump($data);
    update_product($data);
}

if (isset($_POST['user_sales_details'])) {
    $user_id = $_POST['user_id'];
    require('admin/user-sales-details.php');
}

if (isset($_POST['sales_report_details'])) {
    $sales_no = $_POST['sales_no'];
    require('admin/sales_details.php');
}

if (isset($_POST['receiving_report_details'])) {
    $receiving_no = $_POST['receiving_no'];
    require('admin/receiving-details.php');
}

if (isset($_POST['save_image'])) {
    require('db_connect.php');
    $product_id = $_POST['product_id'];
    var_dump($product_id);
    $temp = explode(".", $_FILES["fileToUpload"]["name"]);
    $newfilename = md5($product_id) . '.' . end($temp);
}

if (isset($_POST['save_image_menu'])) {
    require('db_connect.php');
    $menu_id = $_POST['menu_id'];
    $temp = explode(".", $_FILES["fileToUpload"]["name"]);
    $newfilename = md5($menu_id) . '.' . end($temp);
}

if (isset($_POST['update_sales'])) {
    require('db_connect.php');

    // Delete cart items for the user
    $query = "DELETE FROM tbl_cart3 WHERE user_id='" . $_SESSION['user_id'] . "'";
    $db->query($query);

    // Prepare data for updating sales
    $data = array(
        'sales_no' => $_POST['sales_no'],
        'user_id'  => $_SESSION['user_id']
    );

    // Call your update_sales function (ensure it uses MySQLi internally)
    update_sales($data);
}



if (isset($_GET['delete_sales'])) {
    require('db_connect.php');
    // $query_sales = "SELECT * FROM tbl_sales INNER JOIN tbl_menu ON tbl_sales.menu_id = tbl_menu.menu_id   WHERE tbl_sales.sales_no='".$_GET['sales_id']."'  ";
    // $result = $db->query($query_sales);
    // while($row = $result->fetchArray()) { 
    //     $menu_id = $row['menu_id'];

    //     $quantity = $row['quantity'];
    //     $quantity_order = $row['quantity_order'];
    //     $new_quantity = $quantity - $quantity_order;
    //     $query_history = "INSERT INTO tbl_product_history (product_id,qty,balance,type) VALUES ('$product_id','$quantity_order','$new_quantity','1')";
    //     $db->exec($query_history) ;
    //     $query_update = "UPDATE tbl_products set quantity='".$new_quantity."' WHERE product_id='".$product_id."'";
    //     $db->exec($query_update);

    // }
    $query_update2 = "UPDATE tbl_sales 
                  SET field_status = 0, sales_status = 3 
                  WHERE sales_no = '" . $_GET['sales_id'] . "'";
    $db->query($query_update2);

    // Prepare history entry
    $arrayData = array(
        'sales_no' => $_GET['sales_id'],
        'user_id'  => $_SESSION['user_id']
    );
    $arrayGDetails = json_encode($arrayData);
    $today = date("Y-m-d H:i:s");

    // Insert into history
    $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                   VALUES ('$today', '$arrayGDetails', '2')";
    $db->query($insert_history);
}

if (isset($_GET['active_sales'])) {
    require('db_connect.php');

    // $query_sales = "SELECT * FROM tbl_sales INNER JOIN tbl_products ON tbl_sales.product_id = tbl_products.product_id   WHERE tbl_sales.sales_no='".$_GET['sales_id']."'  ";
    // $result = $db->query($query_sales);
    // while($row = $result->fetchArray()) { 
    //     $product_id = $row['product_id'];
    //     $product_id= $row['product_id'];
    //     $quantity = $row['quantity'];
    //     $quantity_order = $row['quantity_order'];
    //     $new_quantity = $quantity + $quantity_order;
    //     $query_history = "INSERT INTO tbl_product_history (product_id,qty,balance,type) VALUES ('$product_id','$quantity_order','$new_quantity','2')";
    //     $db->exec($query_history) ;
    //     $query_update = "UPDATE tbl_products set quantity='".$new_quantity."' WHERE product_id='".$product_id."'";
    //     $db->exec($query_update);
    // }


    $query_update2 = "UPDATE tbl_sales 
                  SET field_status = 0, sales_status = 1 
                  WHERE sales_no = '" . $_GET['sales_id'] . "'";
    $db->query($query_update2);


    $arrayData = array(
        'sales_no' => $_GET['sales_id'],
        'user_id'  => $_SESSION['user_id']
    );
    $arrayGDetails = json_encode($arrayData);
    $today = date("Y-m-d H:i:s");

    $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                   VALUES ('$today', '$arrayGDetails', '3')";
    $db->query($insert_history);
}

if (isset($_POST['save-expences'])) {
    $data = array('date' => $_POST['date'], 'description' => $_POST['description'], 'expence_amount' => $_POST['expence_amount'], 'notes' => $_POST['notes'], 'user_id' => $_SESSION['user_id'], 'approve_by' => $_POST['approve_by']);
    save_expences($data);
}

if (isset($_POST['set-close'])) {
    require('db_connect.php');

    if (!empty($_POST['sales_id']) && is_array($_POST['sales_id'])) {
        foreach ($_POST['sales_id'] as $sales_data) {
            $sales_data = (int)$sales_data; // cast to integer for safety
            $query_update = "UPDATE tbl_sales SET register = 1 WHERE sales_id = $sales_data";
            $db->query($query_update);
        }
    }
}

if (isset($_POST['update_databasess'])) {

    $qry_str = "?x=10&y=20";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/api/public/api/test_data' . $qry_str);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $content = trim(curl_exec($ch));
    curl_close($ch);
    print $content;
}

if (isset($_POST['update_database'])) {
    require('db_connect.php');
    echo "saves";
    die();
    $query_customer = "SELECT * FROM tbl_customer WHERE field_status=0 ";
    $result_customer = $db->query($query_customer);
    $customer_count = 0;
    $customer =  array();
    while ($row_customer = $result_customer->fetchArray()) {
        $customer[] = $row_customer;
        $customer_count++;
    }

    $query_supplier = "SELECT * FROM tbl_supplier WHERE field_status=0 ";
    $result_supplier = $db->query($query_supplier);
    $supplier_count = 0;
    $supplier =  array();
    while ($row_supplier = $result_supplier->fetchArray()) {
        $supplier[] = $row_supplier;
        $supplier_count++;
    }

    $query_user = "SELECT * FROM tbl_users WHERE field_status=0 ";
    $result_user = $db->query($query_user);
    $user_count = 0;
    $user =  array();
    while ($row_user = $result_user->fetchArray()) {
        $user[] = $row_user;
        $user_count++;
    }

    $query_products = "SELECT * FROM tbl_products WHERE field_status=0 ";
    $result_products = $db->query($query_products);
    $products_count = 0;
    $products =  array();
    while ($row_products = $result_products->fetchArray()) {
        $products[] = $row_products;
        $products_count++;
    }

    $query_damage = "SELECT * FROM tbl_damage WHERE field_status=0 ";
    $result_damage = $db->query($query_damage);
    $damage_count = 0;
    $damage =  array();
    while ($row_damage = $result_damage->fetchArray()) {
        $damage[] = $row_damage;
        $damage_count++;
    }

    $query_expences = "SELECT * FROM tbl_expences WHERE field_status=0 ";
    $result_expences = $db->query($query_expences);
    $expences_count = 0;
    $expences =  array();
    while ($row_expences = $result_expences->fetchArray()) {
        $expences[] = $row_expences;
        $expences_count++;
    }

    $query_product_history = "SELECT * FROM tbl_product_history WHERE field_status=0 ";
    $result_product_history = $db->query($query_product_history);
    $product_history_count = 0;
    $product_history =  array();
    while ($row_product_history = $result_product_history->fetchArray()) {
        $product_history[] = $row_product_history;
        $product_history_count++;
    }

    $query_history = "SELECT * FROM tbl_history WHERE field_status=0 ";
    $result_history = $db->query($query_history);
    $history_count = 0;
    $product_history =  array();
    while ($row_history = $result_history->fetchArray()) {
        $history[] = $row_history;
        $history_count++;
    }

    $query_receivings = "SELECT * FROM tbl_receivings WHERE field_status=0 ";
    $result_receivings = $db->query($query_receivings);
    $receivings_count = 0;
    $receivings =  array();
    while ($row_receivings = $result_receivings->fetchArray()) {
        $receivings[] = $row_receivings;
        $receivings_count++;
    }

    $query_sales = "SELECT * FROM tbl_sales WHERE field_status=0  ";
    $result_sales = $db->query($query_sales);
    $sales_count = 0;
    $sales =  array();
    while ($row_sales = $result_sales->fetchArray()) {
        $sales[] = $row_sales;
        $sales_count++;
    }

    $all_count = $customer_count + $supplier_count + $user_count + $products_count + $damage_count + $expences_count + $product_history_count + $receivings_count + $sales_count;
    //var_dump($sales_count);
    //$arrayData = array('sales_no' => 4, 'user_id' => $_SESSION['user_id'] );
    // $arrayGDetails = json_encode($arrayData);
    $fields = array(
        'sales'             => json_encode($sales),
        /*'customer'          => json_encode($customer),
            'supplier'          => json_encode($supplier),
            'user'              => json_encode($user),
            'products'          => json_encode($products),
            'damage'            => json_encode($damage),
            'expences'          => json_encode($expences),
            'receivings'         => json_encode($receivings),
            'product_history'   => json_encode($product_history),
            'history'   => json_encode($history)*/
    );

    $url = $base_url . 'updateDatabase';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    $data_return = json_decode($server_output);
    if ($data_return->success == true) {
        echo "saves";
        //if ($data_return->count==$all_count) {
    } else {
        echo "Server not found";
    }
    curl_close($ch);
}

if (isset($_POST['update_daily_sales'])) {
    require('db_connect.php');

    $sales = array();
    $expences = array();
    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day', strtotime($today)));

    // Determine which date to use for the report
    $report_date = isset($_SESSION['daily-report2']) ? $_SESSION['daily-report2'] : $today;

    // --- SALES QUERY ---
    if ($report_date == $today) {
        $query = "
            SELECT *
            FROM tbl_sales s
            INNER JOIN tbl_users u ON s.user_id = u.user_id
            LEFT JOIN tbl_customer c ON s.cust_id = c.cust_id
            WHERE s.sales_date BETWEEN '$today 00:00:00' AND '$date_add 00:00:00'
              AND s.sales_status != 3
            GROUP BY s.sales_no
        ";
    } else {
        $query = "
            SELECT *
            FROM tbl_sales s
            INNER JOIN tbl_users u ON s.user_id = u.user_id
            LEFT JOIN tbl_customer c ON s.cust_id = c.cust_id
            WHERE DATE(s.sales_date) = '$report_date'
              AND s.sales_status != 3
            GROUP BY s.sales_no
        ";
    }

    $result = $db->query($query);
    while ($row_sales = $result->fetch_assoc()) {
        $sales[] = $row_sales;
    }

    // --- EXPENSES QUERY ---
    if ($report_date == $today) {
        $query2 = "
            SELECT *
            FROM tbl_expences e
            INNER JOIN tbl_users u ON e.user_id = u.user_id
            WHERE e.date_expence BETWEEN '$today 00:00:00' AND '$date_add 00:00:00'
        ";
    } else {
        $query2 = "
            SELECT *
            FROM tbl_expences e
            INNER JOIN tbl_users u ON e.user_id = u.user_id
            WHERE DATE(e.date_expence) = '$report_date'
        ";
    }

    $result = $db->query($query2);
    while ($row_expences = $result->fetch_assoc()) {
        $expences[] = $row_expences;
    }

    // --- STORE SETTINGS ---
    $query_tax = "SELECT * FROM tbl_settings LIMIT 1";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $store_id = $datas_tax['store_id'];

    // --- TODAY'S SALES (for update payload) ---
    $query_sales = "SELECT * FROM tbl_sales WHERE DATE(sales_date) = '$today'";
    $result_sales = $db->query($query_sales);
    $sales_today = array();
    while ($row_sales = $result_sales->fetch_assoc()) {
        $sales_today[] = $row_sales;
    }

    // Prepare data for API
    $fields = array(
        'sales'    => json_encode($sales_today),
        'expences' => json_encode($expences),
        'store_id' => $store_id
    );

    $url = $base_url . 'updateSales';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $data_return = json_decode($server_output);

    if (!empty($data_return) && isset($data_return->success) && $data_return->success == true) {
        echo "saves";
    } else {
        echo "error";
    }

    curl_close($ch);
}

if (isset($_POST['update_database_spicific'])) {
    require('db_connect.php');
    $data = array();
    $type = $_POST['stype'];

    // Map types to tables
    $tables = [
        1 => 'tbl_users',
        2 => 'tbl_customer',
        3 => 'tbl_products',
        4 => 'tbl_product_history',
        5 => 'tbl_damage',
        6 => 'tbl_supplier',
        7 => 'tbl_receivings',
        8 => 'tbl_history'
    ];

    if (isset($tables[$type])) {
        $table = $tables[$type];
        $query = "SELECT * FROM $table";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Get store_id from settings
    $query_tax = "SELECT * FROM tbl_settings LIMIT 1";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $store_id = $datas_tax['store_id'];

    $fields = [
        'data'     => json_encode($data),
        'store_id' => $store_id,
        'type'     => $type
    ];

    $url = $base_url . 'updateDatabaseSpicific';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    $data_return = json_decode($server_output);
    if ($data_return->success == true) {
        echo "saves";
    } else {
        echo "error";
    }
    curl_close($ch);
}

// Inventory report
if (isset($_GET['inventory-report2'])) {
    require('db_connect.php');

    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day', strtotime('today GMT')));

    $from = $_SESSION['inv-report-from'] ?? $today;
    $to   = $_SESSION['inv-report-to'] ?? $today;

    $data_query = "
        SELECT * FROM tbl_product_history
        WHERE hist_date BETWEEN '$today' AND '$date_add'
        ORDER BY tph_id ASC
    ";

    $queryTotal = "
        SELECT * FROM tbl_product_history
        WHERE hist_date BETWEEN '$today' AND '$date_add'
    ";

    $records = $db->query($data_query);
    while ($row = $records->fetch_assoc()) {
        $details = $row['details'];
        $quantity = '';
        $receiving_no = '';

        if ($row['details_type'] == 1) {
            $queryDetails = "SELECT * FROM tbl_sales WHERE sales_no='$details'";
            $result = $db->query($queryDetails);
            while ($rowDetails = $result->fetch_assoc()) {
                $quantity = $rowDetails['quantity_order'];
            }
        } elseif ($row['details_type'] == 2) {
            $queryDetails = "SELECT * FROM tbl_receivings WHERE receiving_no='$details'";
            $result = $db->query($queryDetails);
            while ($rowDetails = $result->fetch_assoc()) {
                $receiving_no = $rowDetails['receiving_no'];
                $quantity = $rowDetails['receiving_quantity'];
            }
        }
    }
}

if (isset($_GET['inventory-report'])) {
    require('db_connect.php');
    $data = array();
    $search = $_POST['search'] ?? '';
    $draw = $_POST['draw'] ?? 1;
    $length = $_POST['length'] ?? 10;
    $start = $_POST['start'] ?? 0;

    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day', strtotime('today GMT')));

    $from = $_SESSION['inv-report-from'] ?? $today;
    $to   = $_SESSION['inv-report-to'] ?? $today;

    // Decide date range
    if ($today == $from || $today == $to) {
        $data_query = "
            SELECT * FROM tbl_product_history
            WHERE hist_date BETWEEN '$today' AND '$date_add'
            ORDER BY tph_id ASC
            LIMIT $length OFFSET $start
        ";
        $queryTotal = "
            SELECT COUNT(*) AS total FROM tbl_product_history
            WHERE hist_date BETWEEN '$today' AND '$date_add'
        ";
    } else {
        $data_query = "
            SELECT * FROM tbl_product_history
            WHERE hist_date BETWEEN '$from' AND '$to'
            ORDER BY tph_id ASC
            LIMIT $length OFFSET $start
        ";
        $queryTotal = "
            SELECT COUNT(*) AS total FROM tbl_product_history
            WHERE hist_date BETWEEN '$from' AND '$to'
        ";
    }

    // Get total records
    $result = $db->query($queryTotal);
    $recordsTotal = $result->fetch_assoc()['total'] ?? 0;

    // Fetch paginated records
    $records = $db->query($data_query);
    while ($row = $records->fetch_assoc()) {
        $details = $row['details'];
        $date = $row['hist_date'];
        $quantity = '';
        $product_name = '';
        $unit = '';
        $employee = '';
        $customer = '';
        $all_details = '';

        if ($row['details_type'] == 1) {
            $queryDetails = "
                SELECT s.*, p.product_name, p.unit, u.fullname, c.name AS customer_name
                FROM tbl_sales s
                INNER JOIN tbl_products p ON s.product_id = p.product_id
                INNER JOIN tbl_users u ON s.user_id = u.user_id
                INNER JOIN tbl_customer c ON s.cust_id = c.cust_id
                WHERE s.sales_no = '$details'
            ";
            $resultDetails = $db->query($queryDetails);
            if ($rowDetails = $resultDetails->fetch_assoc()) {
                $product_name = $rowDetails['product_name'];
                $quantity = '-' . $rowDetails['quantity_order'];
                $unit = $rowDetails['unit'];
                $employee = $rowDetails['fullname'];
                $customer = $rowDetails['customer_name'];
                $all_details = 'Bill No. <a href="javascript:;" onclick="view_details(this)" sales-no="' . $rowDetails['sales_no'] . '">' . $rowDetails['sales_no'] . '</a>';
            }
        } elseif ($row['details_type'] == 2) { // Receivings
            $queryDetails = "
                SELECT r.*, p.product_name, p.unit, u.fullname
                FROM tbl_receivings r
                INNER JOIN tbl_products p ON r.product_id = p.product_id
                INNER JOIN tbl_users u ON r.user_id = u.user_id
                WHERE r.receiving_no = '$details'
            ";
            $resultDetails = $db->query($queryDetails);
            if ($rowDetails = $resultDetails->fetch_assoc()) {
                $receiving_no = $rowDetails['receiving_no'];
                $product_name = $rowDetails['product_name'];
                $quantity = $rowDetails['receiving_quantity'];
                $unit = $rowDetails['unit'];
                $employee = $rowDetails['fullname'];
                $all_details = 'Receiving No. <a href="javascript:;" onclick="view_details_recieving(this)" reciving-no="' . $rowDetails['receiving_no'] . '">' . $rowDetails['receiving_no'] . '</a>';
            }
        } elseif ($row['details_type'] == 3) { // Damage
            $queryDetails = "
                SELECT d.*, p.product_name, p.unit, u.fullname
                FROM tbl_damage d
                INNER JOIN tbl_products p ON d.product_id = p.product_id
                INNER JOIN tbl_users u ON d.user_id = u.user_id
                WHERE d.damage_id = '$details'
            ";
            $resultDetails = $db->query($queryDetails);
            if ($rowDetails = $resultDetails->fetch_assoc()) {
                $product_name = $rowDetails['product_name'];
                $quantity = '-' . $rowDetails['quantity_damage'];
                $unit = $rowDetails['unit'];
                $employee = $rowDetails['fullname'];
                $all_details = 'Damage ID : ' . $rowDetails['damage_id'];
            }
        }

        $data[] = array(
            date('F d, Y h:i A', strtotime($date)),
            $product_name,
            $employee,
            $customer,
            $unit,
            $quantity,
            $all_details,
        );
    }

    echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data,
    ));
}

if (isset($_GET['search-report'])) {
    require('db_connect.php');
    $data = array();
    $search = $_POST['search'] ?? '';
    $draw = $_POST['draw'] ?? 1;
    $length = $_POST['length'] ?? 10;
    $start = $_POST['start'] ?? 0;

    // Filters from session
    $query_status = isset($_SESSION['sale-report-status']) ? "AND tbl_sales.sales_status=" . $_SESSION['sale-report-status'] : "";
    $query_register = isset($_SESSION['sale-report-register']) ? "AND tbl_sales.register='" . $_SESSION['sale-report-register'] . "'" : "";
    $query_type = isset($_SESSION['sale-report-type']) ? "AND tbl_sales.sales_type='" . $_SESSION['sale-report-type'] . "'" : "";
    $user_query = isset($_SESSION['sale-report-user']) ? "AND tbl_sales.user_id='" . $_SESSION['sale-report-user'] . "'" : "";
    $customer_query = isset($_SESSION['sale-report-customer']) ? "AND tbl_sales.cust_id='" . $_SESSION['sale-report-customer'] . "'" : "";

    // Date filter
    $date_condition = "1"; // default no date filter
    if (isset($_SESSION['sale-report']) && isset($_SESSION['sales-date-required'])) {
        $from = $_SESSION['sale-report-from'];
        $to = $_SESSION['sale-report-to'];
        $today = date("Y-m-d");
        $date_add = date('Y-m-d', strtotime('+1 day'));

        if ($today == $from || $today == $to) {
            $date_condition = "tbl_sales.sales_date BETWEEN '$today' AND '$date_add'";
        } else {
            $date_condition = "tbl_sales.sales_date BETWEEN '$from' AND '$to'";
        }
    }

    // Query sales records with proper MySQL GROUP BY and aggregation
    $query_sales = "
    SELECT
        MAX(tbl_sales.sales_id) AS sales_id,
        tbl_sales.sales_no,
        MAX(tbl_sales.sales_date) AS sales_date,
        MAX(tbl_sales.total_amount) AS total_amount,
        MAX(tbl_sales.other_amount) AS other_amount,
        MAX(tbl_sales.balance) AS balance,
        MAX(tbl_sales.sales_status) AS sales_status,
        MAX(tbl_sales.sales_type) AS sales_type,
        MAX(tbl_sales.register) AS register,
        MAX(tbl_users.fullname) AS fullname,
        MAX(tbl_customer.name) AS customer_name
    FROM tbl_sales
    INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
    LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
    WHERE $date_condition
          $user_query
          $customer_query
          $query_status
          $query_register
          $query_type
    GROUP BY tbl_sales.sales_no
    ORDER BY sales_id DESC
    LIMIT $length OFFSET $start
    ";

    // Query total records
    $queryTotal = "
    SELECT COUNT(DISTINCT tbl_sales.sales_no) AS total
    FROM tbl_sales
    INNER JOIN tbl_users ON tbl_sales.user_id = tbl_users.user_id
    LEFT JOIN tbl_customer ON tbl_sales.cust_id = tbl_customer.cust_id
    WHERE $date_condition
          $user_query
          $customer_query
          $query_status
          $query_register
          $query_type
    ";

    // Get total records
    $resultTotal = $db->query($queryTotal);
    $recordsTotal = $resultTotal->fetch_assoc()['total'] ?? 0;

    // Fetch sales records
    $records = $db->query($query_sales);
    while ($row = $records->fetch_assoc()) {
        $sales_id = str_pad($row['sales_id'], 8, '0', STR_PAD_LEFT);

        // Sales status
        switch ($row['sales_status']) {
            case 1:
                $sales_status = '<label class="label label-primary">Active</label>';
                break;
            case 2:
                $sales_status = '<label class="label label-primary">Updated</label>';
                break;
            case 3:
                $sales_status = '<label class="label label-danger">Cancelled</label>';
                break;
            default:
                $sales_status = '<label class="label label-default">Unknown</label>';
                break;
        }

        // Register status
        $register = $row['register'] == 0
            ? '<label class="label label-primary">Open</label>'
            : '<label class="label label-success">Closed</label>';

        // Sales type
        $typeSales = $row['sales_type'] == 0
            ? '<label class="label label-danger">Charge</label>'
            : '<label class="label label-success">Cash</label>';

        // Payment button
        $button_payment = '';
        if ($row['balance'] > 0) {
            $button_payment = '<a href="javascript:;" data-toggle="tooltip" title="Add Payment" sales_id="' . $sales_id . '" sales_no="' . $row['sales_no'] . '" balance="' . $row['balance'] . '" onclick="add_payment(this)"><i class="icon-diff-added position-left text-primary"></i></a>';
        }

        // Action buttons
        $actionButton = $button_payment . '<a href="javascript:;" data-toggle="tooltip" title="Change Status" sales_no="' . $row['sales_no'] . '" onclick="set_active(this)"><i class="icon-pencil7 position-left text-info"></i></a>';
        if ($row['sales_status'] != 3) {
            $actionButton = $button_payment . '<a title="Cancel Sales" sales_no="' . $row['sales_no'] . '" onclick="delete_sales(this)" href="javascript:;" data-toggle="tooltip"><i class="icon-trash position-left text-danger"></i></a>';
        }

        $balance = max(0, $row['balance']);

        $data[] = array(
            date('F d, Y h:i A', strtotime($row['sales_date'])),
            '<a href="javascript:;" onclick="view_details(this)" sales-id="' . $sales_id . '" sales-no="' . $row['sales_no'] . '">' . $sales_id . '</a>',
            $row['fullname'],
            $row['customer_name'],
            number_format($row['total_amount'], 2),
            number_format($row['other_amount'], 2),
            number_format($balance, 2),
            $sales_status,
            $typeSales,
            $actionButton
        );
    }

    echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data,
        'search2' => $search,
        'length2' => $length,
        'draw2' => $draw,
        'start2' => $start
    ));
}

if (isset($_GET['expences-report'])) {
    require('db_connect.php');
    $data = array();
    $search = $_POST['search'] ?? '';
    $draw = $_POST['draw'] ?? 1;
    $length = $_POST['length'] ?? 10;
    $start = $_POST['start'] ?? 0;

    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day'));

    $user_query = isset($_SESSION['expences-report-user']) ? "AND tbl_expences.user_id='" . $_SESSION['expences-report-user'] . "'" : "";

    if (isset($_SESSION['expences-report']) && $_SESSION['expences-report'] != "") {
        $from = $_SESSION['expences-report-from'];
        $to = $_SESSION['expences-report-to'];
    } else {
        $from = $today;
        $to = $today;
    }

    // Date filter condition
    $date_condition = ($today == $from || $today == $to)
        ? "date_expence BETWEEN '$today' AND '$date_add'"
        : "date_expence BETWEEN '$from' AND '$to'";

    // Data query with pagination
    $data_query = "
        SELECT tbl_expences.*, tbl_users.fullname
        FROM tbl_expences
        INNER JOIN tbl_users ON tbl_expences.user_id = tbl_users.user_id
        WHERE $date_condition $user_query
        ORDER BY date_expence ASC
        LIMIT $length OFFSET $start
    ";

    // Total records query
    $queryTotal = "
        SELECT COUNT(*) AS total
        FROM tbl_expences
        INNER JOIN tbl_users ON tbl_expences.user_id = tbl_users.user_id
        WHERE $date_condition $user_query
    ";

    // Fetch total records
    $resultTotal = $db->query($queryTotal);
    $recordsTotal = $resultTotal->fetch_assoc()['total'] ?? 0;

    // Fetch data
    $records = $db->query($data_query);
    while ($row = $records->fetch_assoc()) {
        $data[] = array(
            $row['expences_id'],
            date('F d, Y', strtotime($row['date_expence'])),
            $row['fullname'],
            $row['description'],
            $row['approve_by'],
            $row['notes'],
            number_format($row['expence_amount'], 2)
        );
    }

    // Return JSON
    echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ));
}

if (isset($_GET['damage-report'])) {
    require('db_connect.php');
    $data = array();
    $search = $_POST['search'] ?? '';
    $draw = $_POST['draw'] ?? 1;
    $length = $_POST['length'] ?? 10;
    $start = $_POST['start'] ?? 0;

    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day'));

    $product_query = isset($_SESSION['damage-report-product'])
        ? "AND tbl_damage.product_id='" . $_SESSION['damage-report-product'] . "'"
        : "";

    if (isset($_SESSION['damage-report']) && $_SESSION['damage-report'] != "") {
        $from = $_SESSION['damage-report-from'];
        $to = $_SESSION['damage-report-to'];
    } else {
        $from = $today;
        $to = $today;
    }

    // Date condition
    $date_condition = ($today == $from || $today == $to)
        ? "date_damage BETWEEN '$today' AND '$date_add'"
        : "date_damage BETWEEN '$from' AND '$to'";

    // Data query with pagination
    $data_query = "
        SELECT tbl_damage.*, tbl_products.product_name
        FROM tbl_damage
        INNER JOIN tbl_products ON tbl_damage.product_id = tbl_products.product_id
        WHERE $date_condition $product_query
        ORDER BY date_damage ASC
        LIMIT $length OFFSET $start
    ";

    // Total records
    $queryTotal = "
        SELECT COUNT(*) AS total
        FROM tbl_damage
        INNER JOIN tbl_products ON tbl_damage.product_id = tbl_products.product_id
        WHERE $date_condition $product_query
    ";

    // Get total records
    $resultTotal = $db->query($queryTotal);
    $recordsTotal = $resultTotal->fetch_assoc()['total'] ?? 0;

    // Fetch data
    $records = $db->query($data_query);
    while ($row = $records->fetch_assoc()) {
        $data[] = array(
            $row['damage_id'],
            date('F d, Y h:i A', strtotime($row['date_damage'])),
            $row['product_name'],
            $row['notes'],
            $row['quantity_damage']
        );
    }

    // Return JSON
    echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ));
}


if (isset($_GET['checkproductExist'])) {
    require('db_connect.php');
    $product_name = $_GET['product_name'];
    $query = "SELECT * FROM tbl_products WHERE product_name='" . $db->real_escape_string($product_name) . "'";
    $result = $db->query($query);
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
}

if (isset($_GET['cancelUpdate'])) {
    require('db_connect.php');
    $user_id = $db->real_escape_string($_SESSION['user_id']);
    $query = "DELETE FROM tbl_cart3 WHERE user_id='$user_id'";
    $db->query($query);
}

if (isset($_GET['delete_ingredients'])) {
    require('db_connect.php');
    $ingrdents_id = $db->real_escape_string($_GET['ingrdents_id']);
    $query = "DELETE FROM tbl_menu_ingredents WHERE ingrdents_id='$ingrdents_id'";
    $db->query($query);
}

if (isset($_POST['save_menu_ingredients'])) {
    require('db_connect.php');
    $product_id = $_POST['product_id'];
    $menu_id = $db->real_escape_string($_POST['menu_id']);
    $quantity = $_POST['quantity'];

    for ($i = 0; $i < count($product_id); $i++) {
        $prod_id = $db->real_escape_string($product_id[$i]);
        $quantityData = $quantity[$i] != "" ? $db->real_escape_string($quantity[$i]) : 0;
        $query = "INSERT INTO tbl_menu_ingredents (menu_id, product_id, quantity_menu) 
                  VALUES ('$menu_id', '$prod_id', '$quantityData')";
        $db->query($query);
    }
}

if (isset($_POST['save-deposit'])) {
    require('db_connect.php');
    $data = array('balance' => $_POST['balance'], 'amount' => $_POST['amount']);
    save_deposit($data);
}

if (isset($_POST['save-panda-payment'])) {
    require('db_connect.php');
    $data = array('amount' => $_POST['amount']);
    save_panda_payment($data);
}

if (isset($_POST['submit-deposit'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['deposit-report'] = $daterange;
    $_SESSION['deposit-report-from'] = $myDateFROM;
    $_SESSION['deposit-report-to'] = $myDateTO;
    if ($_POST['username'] != "") {
        $_SESSION['deposit-report-user'] = $_POST['user_id'];
    } else {
        unset($_SESSION['deposit-report-user']);
    }

    if ($_POST['date-required'] != NULL) {
        $_SESSION['deposit-date-required'] = "yes";
    } else {
        unset($_SESSION['deposit-date-required']);
    }
}

if (isset($_GET['deposit-report'])) {
    require('db_connect.php');
    $data = array();
    $search = $_POST['search'];
    $draw = $_POST['draw'];
    $length = $_POST['length'];
    $start = $_POST['start'];
    $startDate = strtotime('today GMT');
    $today = date("Y-m-d");
    $date_add = date('Y-m-d', strtotime('+1 day', $startDate));

    if (isset($_SESSION['deposit-report-user'])) {
        $user_id = $db->real_escape_string($_SESSION['deposit-report-user']);
        $user_query = "AND tbl_deposits.user_id='$user_id'";
    } else {
        $user_query = "";
    }

    if (isset($_SESSION['deposit-report']) && $_SESSION['deposit-report'] != "") {
        $from = $db->real_escape_string($_SESSION['deposit-report-from']);
        $to = $db->real_escape_string($_SESSION['deposit-report-to']);
    } else {
        $from = $today;
        $to = $today;
    }



    if ($today == $from || $today == $to) {
        $data_query = "
            SELECT * FROM tbl_deposits
            INNER JOIN tbl_users ON tbl_deposits.user_id = tbl_users.user_id
            WHERE date_added BETWEEN '$today' AND '$date_add' $user_query
            ORDER BY date_added ASC
            LIMIT $length OFFSET $start
        ";
        $queryTotal = "
            SELECT * FROM tbl_deposits
            INNER JOIN tbl_users ON tbl_deposits.user_id = tbl_users.user_id
            WHERE date_added BETWEEN '$today' AND '$date_add' $user_query
        ";
    } else {
        $data_query = "
            SELECT * FROM tbl_deposits
            INNER JOIN tbl_users ON tbl_deposits.user_id = tbl_users.user_id
            WHERE date_added BETWEEN '$from' AND '$to'
            ORDER BY date_added ASC
            LIMIT $length OFFSET $start
        ";
        $queryTotal = "
            SELECT * FROM tbl_deposits
            WHERE date_added BETWEEN '$from' AND '$to'
        ";
    }


    $result = $db->query($queryTotal);
    $recordsTotal = $result->num_rows;


    $records = $db->query($data_query);
    while ($row = $records->fetch_assoc()) {
        $data[] = array(
            $row['deposit_id'],
            date('F d, Y h:i:s', strtotime($row['date_added'])),
            $row['fullname'],
            number_format($row['amount'], 2),
        );
    }

    echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data,
    ));
}

if (isset($_POST['clear_filter_deposits'])) {
    unset($_SESSION['deposit-report-user']);
    unset($_SESSION['deposit-report']);
    unset($_SESSION['deposit-report-from']);
    unset($_SESSION['deposit-report-to']);
    unset($_SESSION['deposit-date-required']);
}
if (isset($_POST['save-payment-charge'])) {
    require('db_connect.php');

    $payment = $db->real_escape_string($_POST['amount']);
    $cr_no = !empty($_POST['cr_no']) && is_numeric($_POST['cr_no']) ? $_POST['cr_no'] : NULL;
    $sales_no = $db->real_escape_string($_POST['sales_no']);
    $user_id = $_SESSION['user_id'];

    // Get current balance
    $query = "SELECT balance FROM tbl_sales WHERE sales_no='$sales_no'";
    $result = $db->query($query);

    $balance = 0;
    if ($row = $result->fetch_assoc()) {
        $balance = $row['balance'];
    }

    if ($payment > $balance) {
        echo 2;
    } else {
        $new_balance = $balance - $payment;
        $date = date("Y-m-d H:i:s");


        $query = "INSERT INTO tbl_payments (date_payment, added_by, amount_paid, sales_no, cr_no)
                  VALUES ('$date', '$user_id', '$payment', '$sales_no', " . ($cr_no ?? 'NULL') . ")";

        if ($db->query($query)) {

            $query_update = "UPDATE tbl_sales SET balance='$new_balance' WHERE sales_no='$sales_no'";
            if ($db->query($query_update)) {
                echo 1;
            }
        }
    }
}



if (isset($_POST['submit-soa-generate'])) {

    if (!isset($_POST['sales_no_checkbox'])) {
        echo 2;
    } else {
        $sales_no = $_POST['sales_no_checkbox'];
        require_once('./admin/soa-form.php');
    }
}

if (isset($_POST['set_po_data'])) {
    $_SESSION["po_no"] = $_POST["po_no"];
}
if (isset($_POST['set_check_data'])) {
    $_SESSION["check_no"] = $_POST["check_no"];
}

if (isset($_POST['update_delivery_address'])) {
    require('db_connect.php');

    $sales_no = $db->real_escape_string($_POST['sales_no']);
    $delivery_address = $db->real_escape_string($_POST['delivery_address']);

    $query_update = "UPDATE tbl_sales SET delivery_address='$delivery_address' WHERE sales_no='$sales_no'";

    if ($db->query($query_update)) {
        echo "1";
    }
}

if (isset($_POST['update_salesman'])) {
    require('db_connect.php');

    $sales_no = $db->real_escape_string($_POST['sales_no']);
    $salesman = $db->real_escape_string($_POST['salesman']);

    $query_update = "UPDATE tbl_sales SET salesman='$salesman' WHERE sales_no='$sales_no'";

    if ($db->query($query_update)) {
        echo "1";
    }
}


if (isset($_POST['submit-soa'])) {
    $_SESSION['soa-customer'] = $_POST['cust_id'];
}

if (isset($_POST['submit-seller'])) {
    $daterange = $_POST['date'];
    $datef = explode(" -", $daterange);
    $date_from = trim($datef[0]);
    $date_to = trim($datef[1]);
    $myDateFROM = date("Y-m-d", strtotime($date_from));
    $myDateTO = date("Y-m-d", strtotime($date_to));
    $_SESSION['seller-report'] = $daterange;
    $_SESSION['seller-report-from'] = $myDateFROM;
    $_SESSION['seller-report-to'] = $myDateTO;
}

/// Add or Update Fund
if (isset($_POST['save-loan-fund'])) {
    require('db_connect.php');

    $fund_name = $db->real_escape_string(trim($_POST['fund_name']));
    $starting = floatval($_POST['starting_balance']);

    if (!empty($_POST['update_id'])) {
        $id = intval($_POST['update_id']);

        $query = "UPDATE tbl_loan_fund 
                  SET fund_name='$fund_name', 
                      starting_balance=$starting, 
                      current_balance=$starting 
                  WHERE fund_id=$id";
    } else {
        $current = $starting;
        $query = "INSERT INTO tbl_loan_fund (fund_name, starting_balance, current_balance) 
                  VALUES ('$fund_name', $starting, $current)";
    }

    if ($db->query($query)) {
        echo "1";
    } else {
        echo "DB Error: " . $db->error;
    }
    exit;
}



if (isset($_POST['save-loan-application'])) {

    require('db_connect.php');

    $member_id = (int)($_POST['member_id'] ?? 0);
    $loan_type_id = (int)($_POST['loan_type_id'] ?? 0);
    $requested_amount = floatval($_POST['requested_amount'] ?? 0);
    $purpose = $db->real_escape_string($_POST['purpose'] ?? '');
    $user_id  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $today = date("Y-m-d H:i:s");

    if ($member_id <= 0 || $loan_type_id <= 0 || $requested_amount <= 0) {

        echo "Validation Error: Missing required fields.";

        exit;
    }
    $db->begin_transaction();
    try {

        $acc_res = $db->query("
            SELECT account_id 
            FROM accounts 
            WHERE member_id=$member_id 
            AND account_type_id=3 
            LIMIT 1
        ");
        if ($acc_res->num_rows > 0) {
            $account_id = $acc_res->fetch_assoc()['account_id'];
        } else {
            $account_number = 'LN-' . str_pad($member_id, 5, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
            $db->query("
                INSERT INTO accounts 
                (member_id, account_type_id, account_number) 
                VALUES 
                ($member_id, 3, '$account_number')
            ");
            $account_id = $db->insert_id;
        }
        $lt_res = $db->query("
            SELECT term_value, term_unit, payment_frequency, interest_rate 
            FROM loan_types 
            WHERE loan_type_id=$loan_type_id 
            LIMIT 1
        ");
        if ($lt_res->num_rows == 0)
            throw new Exception("Loan type not found.");

        $lt = $lt_res->fetch_assoc();
        $term_value = (int)$lt['term_value'];
        $term_unit = $db->real_escape_string($lt['term_unit']);
        $payment_frequency = $db->real_escape_string($lt['payment_frequency']);
        $interest_rate = (float)$lt['interest_rate'];

        $stmt = $db->prepare("
            INSERT INTO loans 
            (account_id, loan_type_id, requested_amount, interest_rate, term_value, term_unit, payment_frequency, status, application_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");

        $stmt->bind_param(
            "iidissss",
            $account_id,
            $loan_type_id,
            $requested_amount,
            $interest_rate,
            $term_value,
            $term_unit,
            $payment_frequency,
            $today
        );

        $stmt->execute();

        // Get the newly inserted loan ID
        $loan_id = $db->insert_id;

        // Check if a co-maker was provided
        // Check if loan type requires comaker
        $check = $db->query("
SELECT require_comaker
FROM loan_types
WHERE loan_type_id=$loan_type_id
LIMIT 1
")->fetch_assoc();

        $require_comaker = $check['require_comaker'] ?? 0;

        if ($require_comaker == 1 && empty($_POST['comaker_member_id'])) {
            throw new Exception("This loan type requires a co-maker.");
        }

        // Insert co-maker
        if (!empty($_POST['comaker_member_id'])) {

            $comaker_member_id = (int)$_POST['comaker_member_id'];

            $stmt = $db->prepare("
        INSERT INTO loan_comaker 
        (loan_id, comaker_member_id, status)
        VALUES (?, ?, 'pending')
    ");

            $stmt->bind_param("ii", $loan_id, $comaker_member_id);

            $stmt->execute();
        }


        $detailsArray = [
            "member_id" => $member_id,
            "loan_type_id" => $loan_type_id,
            "requested_amount" => $requested_amount,
            "purpose" => $purpose,
            'user_id'    => $user_id,
            "status" => "pending",
            "date" => $today
        ];

        $detailsJson = $db->real_escape_string(json_encode($detailsArray));
        $historyQuery = "
            INSERT INTO tbl_history
            (details,
            history_type,
            field_status)
            VALUES
            ( '$detailsJson',55,0)
        ";

        $db->query($historyQuery);
        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

if (isset($_POST['search_member'])) {

    require('db_connect.php');

    $search = $db->real_escape_string($_POST['search']);
    $member_id = (int)$_POST['member_id'];

    $query = $db->query("
        SELECT member_id,
        CONCAT(first_name,' ',last_name) AS name
        FROM tbl_members
        WHERE type='regular'
        AND member_id != $member_id
        AND CONCAT(first_name,' ',last_name) LIKE '%$search%'
        ORDER BY name ASC
    ");

    $data = [];

    while ($row = $query->fetch_assoc()) {
        $data[] = [
            "id" => $row['member_id'],
            "text" => $row['name']
        ];
    }

    echo json_encode($data);
    exit;
}


if (isset($_POST['approve_loan'])) {
    require('db_connect.php');

    $loan_id = (int)($_POST['loan_app_id'] ?? 0);
    $fund_id = (int)($_POST['fund_id'] ?? 0);
    $approved_amount = floatval($_POST['approved_amount'] ?? 0);
    $approved_term = trim($_POST['approved_term'] ?? '');
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    $today = date("Y-m-d");

    if ($loan_id <= 0 || $approved_amount <= 0) {
        echo "Invalid loan data";
        exit;
    }

    $db->begin_transaction();
    try {

        $term_parts = explode(' ', $approved_term);
        $term_value = (int)($term_parts[0] ?? 0);
        $term_unit = $term_parts[1] ?? 'months';

        $loanQry = $db->query("SELECT loan_type_id FROM loans WHERE loan_id = $loan_id FOR UPDATE");
        if ($loanQry->num_rows == 0) throw new Exception("Loan not found");
        $loan = $loanQry->fetch_assoc();

        $typeQry = $db->query("SELECT interest_rate, payment_frequency FROM loan_types WHERE loan_type_id = {$loan['loan_type_id']}");
        if ($typeQry->num_rows == 0) throw new Exception("Loan type config missing");
        $type = $typeQry->fetch_assoc();

        $interest_rate = (float)$type['interest_rate'];
        $frequency = $type['payment_frequency'];

        $fee_type = 'percent';
        $fee_value = 0;
        $insurance_rate = 0;
        $doc_stamp_fee = 0;
        $monthly_saving = 0;
        $monthly_share = 0;

        $feeQry = $db->query("
            SELECT setting_key, setting_value
            FROM system_settings
            WHERE setting_key IN (
                'loan_processing_fee_type',
                'loan_processing_fee_value',
                'loan_insurance_fee',
                'loan_doc_stamp_fee',
                'monthly_savings',
                'monthly_share_capital'
            )
        ");

        while ($row = $feeQry->fetch_assoc()) {
            switch ($row['setting_key']) {
                case "loan_processing_fee_type":
                    $fee_type = $row['setting_value'];
                    break;
                case "loan_processing_fee_value":
                    $fee_value = (float)$row['setting_value'];
                    break;
                case "loan_insurance_fee":
                    $insurance_rate = (float)$row['setting_value'];
                    break;
                case "loan_doc_stamp_fee":
                    $doc_stamp_fee = (float)$row['setting_value'];
                    break;
                case "monthly_savings":
                    $monthly_saving = (float)$row['setting_value'];
                    break;
                case "monthly_share_capital":
                    $monthly_share = (float)$row['setting_value'];
                    break;
            }
        }




        if ($fee_type == "percent") {
            $processing_fee = round($approved_amount * ($fee_value / 100), 2);
        } else {
            $processing_fee = round($fee_value, 2);
        }

        $insurance_fee = round($approved_amount * ($insurance_rate / 100), 2);

        $doc_fee = round($approved_amount * ($doc_stamp_fee / 100), 2);


        if ($term_unit == "months") {
            $total_interest = round($approved_amount * ($interest_rate / 100) * $term_value, 2);
        } elseif ($term_unit == "weeks") {
            $months = $term_value / 4;
            $total_interest = round($approved_amount * ($interest_rate / 100) * $months, 2);
        } else {
            $months = $term_value / 30;
            $total_interest = round($approved_amount * ($interest_rate / 100) * $months, 2);
        }


        $monthly_saving = round($monthly_saving, 2);
        $monthly_share  = round($monthly_share, 2);

        // Calculate total monthly savings and share capital for the entire term
        $total_monthly_savings = round($monthly_saving * $term_value, 2);
        $total_monthly_share = round($monthly_share * $term_value, 2);

        $total_due = round(
            $approved_amount +
                $total_interest +
                $processing_fee +
                $insurance_fee +
                $doc_fee +
                $total_monthly_savings +
                $total_monthly_share,
            2
        );


        $stmt = $db->prepare("
    UPDATE loans
    SET status='approved',
        fund_id=?,
        approved_amount=?,
        service_charge=?,
        insurance=?,
        doc_stamp_fee=?,
        interest_rate=?,
        term_value=?,
        term_unit=?,
        approved_date=?,
        total_interest=?,
        total_due=?
    WHERE loan_id=?
");

        $stmt->bind_param(
            "idddddissddi",
            $fund_id,
            $approved_amount,
            $processing_fee,
            $insurance_fee,
            $doc_fee,
            $interest_rate,
            $term_value,
            $term_unit,
            $today,
            $total_interest,
            $total_due,
            $loan_id
        );

        $stmt->execute();

        if ($fund_id > 0) {
            $fundQry = $db->query("SELECT current_balance FROM tbl_loan_fund WHERE fund_id=$fund_id FOR UPDATE");
            if ($fundQry->num_rows == 0) throw new Exception("Selected fund not found");

            $fund = $fundQry->fetch_assoc();
            $new_balance = $fund['current_balance'] - $approved_amount;

            if ($new_balance < 0)
                throw new Exception("Insufficient fund balance to approve loan");

            $stmtFund = $db->prepare("UPDATE tbl_loan_fund SET current_balance=? WHERE fund_id=?");
            $stmtFund->bind_param("di", $new_balance, $fund_id);
            $stmtFund->execute();
        }

        $db->query("DELETE FROM loan_schedule WHERE loan_id=$loan_id");

        $stmt2 = $db->prepare("
            INSERT INTO loan_schedule(
                loan_id, due_date, principal_due, interest_due, total_due, status
            ) VALUES (?, ?, ?, ?, ?, 'pending')
        ");

        $base_total_due = round($total_due / $term_value, 2);
        $base_interest_due = round($total_interest / $term_value, 2);

        $running_total = 0;

        for ($i = 1; $i <= $term_value; $i++) {

            if ($frequency == "monthly")
                $due_date = date("Y-m-d", strtotime("+$i month", strtotime($today)));
            elseif ($frequency == "weekly")
                $due_date = date("Y-m-d", strtotime("+$i week", strtotime($today)));
            else
                $due_date = date("Y-m-d", strtotime("+$i day", strtotime($today)));

            if ($i == $term_value) {
                $schedule_total = round($total_due - $running_total, 2);
            } else {
                $schedule_total = $base_total_due;
            }

            $interest_due = $base_interest_due;
            $principal_due = round($schedule_total - $interest_due, 2);

            $running_total += $schedule_total;

            $stmt2->bind_param(
                "isddd",
                $loan_id,
                $due_date,
                $principal_due,
                $interest_due,
                $schedule_total
            );

            $stmt2->execute();
        }

        $details = [
            "loan_id" => $loan_id,
            "approved_amount" => $approved_amount,
            "total_due" => $total_due,
            "user_id" => $user_id,
            "date" => $today
        ];

        $json = $db->real_escape_string(json_encode($details));
        $db->query("INSERT INTO tbl_history (details, history_type, field_status) VALUES ('$json', 56, 0)");

        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}


// if (isset($_POST['approve_loan'])) {
//     require('db_connect.php');

//     $loan_id = (int)($_POST['loan_app_id'] ?? 0);
//     $fund_id = (int)($_POST['fund_id'] ?? 0);
//     $approved_amount = floatval($_POST['approved_amount'] ?? 0);
//     $approved_term = trim($_POST['approved_term'] ?? '');
//     $user_id = (int)($_SESSION['user_id'] ?? 0);

//     $today = date("Y-m-d");

//     if ($loan_id <= 0 || $approved_amount <= 0) {
//         echo "Invalid loan data";
//         exit;
//     }

//     $db->begin_transaction();
//     try {

//         $term_parts = explode(' ', $approved_term);
//         $term_value = (int)($term_parts[0] ?? 0);
//         $term_unit = $term_parts[1] ?? 'months';

//         $loanQry = $db->query("SELECT loan_type_id FROM loans WHERE loan_id = $loan_id FOR UPDATE");
//         if ($loanQry->num_rows == 0) throw new Exception("Loan not found");
//         $loan = $loanQry->fetch_assoc();

//         $typeQry = $db->query("SELECT interest_rate, payment_frequency FROM loan_types WHERE loan_type_id = {$loan['loan_type_id']}");
//         if ($typeQry->num_rows == 0) throw new Exception("Loan type config missing");
//         $type = $typeQry->fetch_assoc();

//         $interest_rate = (float)$type['interest_rate'];
//         $frequency = $type['payment_frequency'];

//         $fee_type = 'percent';
//         $fee_value = 0;
//         $insurance_rate = 0;
//         $doc_stamp_fee = 0;
//         $monthly_saving = 0;
//         $monthly_share = 0;

//         $feeQry = $db->query("
//             SELECT setting_key, setting_value
//             FROM system_settings
//             WHERE setting_key IN (
//                 'loan_processing_fee_type',
//                 'loan_processing_fee_value',
//                 'loan_insurance_fee',
//                 'loan_doc_stamp_fee',
//                 'monthly_savings',
//                 'monthly_share_capital'
//             )
//         ");

//         while ($row = $feeQry->fetch_assoc()) {
//             switch ($row['setting_key']) {
//                 case "loan_processing_fee_type":
//                     $fee_type = $row['setting_value'];
//                     break;
//                 case "loan_processing_fee_value":
//                     $fee_value = (float)$row['setting_value'];
//                     break;
//                 case "loan_insurance_fee":
//                     $insurance_rate = (float)$row['setting_value'];
//                     break;
//                 case "loan_doc_stamp_fee":
//                     $doc_stamp_fee = (float)$row['setting_value'];
//                     break;
//                 case "monthly_savings":
//                     $monthly_saving = (float)$row['setting_value'];
//                     break;
//                 case "monthly_share_capital":
//                     $monthly_share = (float)$row['setting_value'];
//                     break;
//             }
//         }




//         if ($fee_type == "percent") {
//             $processing_fee = round($approved_amount * ($fee_value / 100), 2);
//         } else {
//             $processing_fee = round($fee_value, 2);
//         }

//         $insurance_fee = round($approved_amount * ($insurance_rate / 100), 2);

//         $doc_fee = round($approved_amount * ($doc_stamp_fee / 100), 2);


//         if ($term_unit == "months") {
//             $total_interest = round($approved_amount * ($interest_rate / 100) * $term_value, 2);
//         } elseif ($term_unit == "weeks") {
//             $months = $term_value / 4;
//             $total_interest = round($approved_amount * ($interest_rate / 100) * $months, 2);
//         } else {
//             $months = $term_value / 30;
//             $total_interest = round($approved_amount * ($interest_rate / 100) * $months, 2);
//         }


//         $monthly_saving = round($monthly_saving, 2);
//         $monthly_share  = round($monthly_share, 2);


//         $total_due = round(
//             $approved_amount +
//                 $total_interest +
//                 $processing_fee +
//                 $insurance_fee +
//                 $doc_fee +
//                 $monthly_saving +
//                 $monthly_share,
//             2
//         );


//         $stmt = $db->prepare("
//     UPDATE loans
//     SET status='approved',
//         fund_id=?,
//         approved_amount=?,
//         service_charge=?,
//         insurance=?,
//         doc_stamp_fee=?,
//         interest_rate=?,
//         term_value=?,
//         term_unit=?,
//         approved_date=?,
//         total_interest=?,
//         total_due=?
//     WHERE loan_id=?
// ");

//         $stmt->bind_param(
//             "idddddissddi",
//             $fund_id,
//             $approved_amount,
//             $processing_fee,
//             $insurance_fee,
//             $doc_fee,
//             $interest_rate,
//             $term_value,
//             $term_unit,
//             $today,
//             $total_interest,
//             $total_due,
//             $loan_id
//         );

//         $stmt->execute();

//         if ($fund_id > 0) {
//             $fundQry = $db->query("SELECT current_balance FROM tbl_loan_fund WHERE fund_id=$fund_id FOR UPDATE");
//             if ($fundQry->num_rows == 0) throw new Exception("Selected fund not found");

//             $fund = $fundQry->fetch_assoc();
//             $new_balance = $fund['current_balance'] - $approved_amount;

//             if ($new_balance < 0)
//                 throw new Exception("Insufficient fund balance to approve loan");

//             $stmtFund = $db->prepare("UPDATE tbl_loan_fund SET current_balance=? WHERE fund_id=?");
//             $stmtFund->bind_param("di", $new_balance, $fund_id);
//             $stmtFund->execute();
//         }

//         $db->query("DELETE FROM loan_schedule WHERE loan_id=$loan_id");

//         $stmt2 = $db->prepare("
//             INSERT INTO loan_schedule(
//                 loan_id, due_date, principal_due, interest_due, total_due, status
//             ) VALUES (?, ?, ?, ?, ?, 'pending')
//         ");

//         $base_total_due = round($total_due / $term_value, 2);
//         $base_interest_due = round($total_interest / $term_value, 2);

//         $running_total = 0;

//         for ($i = 1; $i <= $term_value; $i++) {

//             if ($frequency == "monthly")
//                 $due_date = date("Y-m-d", strtotime("+$i month", strtotime($today)));
//             elseif ($frequency == "weekly")
//                 $due_date = date("Y-m-d", strtotime("+$i week", strtotime($today)));
//             else
//                 $due_date = date("Y-m-d", strtotime("+$i day", strtotime($today)));

//             if ($i == $term_value) {
//                 $schedule_total = round($total_due - $running_total, 2);
//             } else {
//                 $schedule_total = $base_total_due;
//             }

//             $interest_due = $base_interest_due;
//             $principal_due = round($schedule_total - $interest_due, 2);

//             $running_total += $schedule_total;

//             $stmt2->bind_param(
//                 "isddd",
//                 $loan_id,
//                 $due_date,
//                 $principal_due,
//                 $interest_due,
//                 $schedule_total
//             );

//             $stmt2->execute();
//         }

//         $details = [
//             "loan_id" => $loan_id,
//             "approved_amount" => $approved_amount,
//             "total_due" => $total_due,
//             "user_id" => $user_id,
//             "date" => $today
//         ];

//         $json = $db->real_escape_string(json_encode($details));
//         $db->query("INSERT INTO tbl_history (details, history_type, field_status) VALUES ('$json', 56, 0)");

//         $db->commit();
//         echo "1";
//     } catch (Exception $e) {
//         $db->rollback();
//         echo "Error: " . $e->getMessage();
//     }
//     exit;
// }
    

if (isset($_GET['loan_receipt'])) {
    require('db_connect.php');
    $loan_id = (int)($_GET['loan_id'] ?? 0);
    if ($loan_id <= 0) {
        echo "Invalid Loan ID";
        exit;
    }
    $_GET['loan_id'] = $loan_id;
    require('admin/loan_receipt.php');
    exit;
}





if (isset($_POST['decline_loan'])) {
    require('db_connect.php');

    $loan_id = isset($_POST['loan_app_id']) ? (int)$_POST['loan_app_id'] : 0;
    if ($loan_id <= 0) {
        echo "Invalid loan ID.";
        exit;
    }

    $decline_date = date("Y-m-d H:i:s");

    // Begin transaction
    $db->begin_transaction();

    try {
        $stmt = $db->prepare("UPDATE loans SET status='rejected', approved_date=? WHERE loan_id=?");
        $stmt->bind_param("si", $decline_date, $loan_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $db->commit();
            echo "1";
        } else {
            $db->rollback();
            echo "Error: Loan not found or already declined.";
        }
    } catch (Exception $e) {
        $db->rollback();
        echo "DB Error: " . $e->getMessage();
    }

    exit;
}

if (isset($_POST['disburse_loan'])) {
    require('db_connect.php');

    $loan_id = (int)($_POST['loan_id'] ?? 0);
    $release_date = date("Y-m-d");
    $user_id = $_SESSION['user_id'] ?? 0;

    $db->begin_transaction();
    try {

        $res = $db->query("SELECT approved_amount, account_id FROM loans WHERE loan_id=$loan_id AND status='approved' LIMIT 1");
        if ($res->num_rows == 0) throw new Exception("Loan not found or not approved.");
        $loan = $res->fetch_assoc();
        $principal = (float)$loan['approved_amount'];
        $account_id = (int)$loan['account_id'];

        $stmt = $db->prepare("UPDATE loans SET status='ongoing', released_date=? WHERE loan_id=?");
        $stmt->bind_param("si", $release_date, $loan_id);
        $stmt->execute();


        $stmt_sched = $db->prepare("UPDATE loan_schedule SET status='ongoing' WHERE loan_id=?");
        $stmt_sched->bind_param("i", $loan_id);
        $stmt_sched->execute();


        $stmt_tran = $db->prepare("INSERT INTO transactions (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at) VALUES (?, 4, ?, ?, ?, ?, NOW())");
        $reference_no = "LN-" . str_pad($loan_id, 5, "0", STR_PAD_LEFT) . "-" . time();
        $remarks = "Loan released for loan ID $loan_id";
        $stmt_tran->bind_param("idsss", $account_id, $principal, $reference_no, $remarks, $release_date);
        $stmt_tran->execute();

        $details = [
            "loan_id" => $loan_id,
            "released_amount" => $principal,
            "user_id" => $user_id,
            "date" => $release_date
        ];
        $json = $db->real_escape_string(json_encode($details));
        $db->query("INSERT INTO tbl_history (details, history_type, field_status) VALUES ('$json', 57, 0)");

        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}


if (isset($_POST['cancelledloan'])) {
    require('db_connect.php');

    $loan_id = (int)($_POST['loan_id'] ?? 0);
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $today = date("Y-m-d");

    if ($loan_id <= 0) {
        echo "Invalid loan ID";
        exit;
    }

    $db->begin_transaction();

    try {

        $loanQry = $db->query("
            SELECT approved_amount, fund_id, status
            FROM loans
            WHERE loan_id = $loan_id
            FOR UPDATE
        ");

        if ($loanQry->num_rows == 0)
            throw new Exception("Loan not found");

        $loan = $loanQry->fetch_assoc();

        if ($loan['status'] != 'approved')
            throw new Exception("Only approved loans can be cancelled");

        $approved_amount = (float)$loan['approved_amount'];
        $fund_id = (int)$loan['fund_id'];

        if ($fund_id <= 0)
            throw new Exception("No fund linked to this loan");

     
        $fundQry = $db->query("
            SELECT current_balance
            FROM tbl_loan_fund
            WHERE fund_id = $fund_id
            FOR UPDATE
        ");

        if ($fundQry->num_rows == 0)
            throw new Exception("Loan fund not found");

        $fund = $fundQry->fetch_assoc();
        $new_balance = $fund['current_balance'] + $approved_amount;

        $stmtFund = $db->prepare("
            UPDATE tbl_loan_fund
            SET current_balance=?
            WHERE fund_id=?
        ");
        $stmtFund->bind_param("di", $new_balance, $fund_id);
        $stmtFund->execute();

       
        $stmt = $db->prepare("
            UPDATE loans
            SET status='canceled'
            WHERE loan_id=?
        ");
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();

        
        $db->query("DELETE FROM loan_schedule WHERE loan_id=$loan_id");

        $details = [
            "loan_id" => $loan_id,
            "approved_amount" => $approved_amount,
            "action" => "Loan Cancelled",
            "user_id" => $user_id,
            "date" => $today
        ];

        $json = $db->real_escape_string(json_encode($details));
        $db->query("
            INSERT INTO tbl_history (details, history_type, field_status)
            VALUES ('$json', 57, 0)
        ");

        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }

    exit;
}


function applyLoanPenalty($db, $loan_id)
{
    $today = date("Y-m-d");

   
    $settings = [];
    $res = $db->query("
        SELECT setting_key, setting_value
        FROM system_settings
        WHERE setting_key IN (
            'loan_grace_period_days',
            'loan_penalty_frequency',
            'loan_penalty_type',
            'loan_penalty_value'
        )
    ");

    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $grace     = (int)($settings['loan_grace_period_days'] ?? 0);
    $frequency = $settings['loan_penalty_frequency'] ?? 'monthly';
    $type      = $settings['loan_penalty_type'] ?? 'fixed';
    $value     = (float)($settings['loan_penalty_value'] ?? 0);

    $stmt = $db->prepare("
        SELECT schedule_id, due_date, principal_due, interest_due, penalty_due, status
        FROM loan_schedule
        WHERE loan_id = ?
        AND status IN ('pending','ongoing')
    ");
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $due = $row['due_date'];
        $penalty_start = date("Y-m-d", strtotime("$due + $grace days"));


        if ($today <= $penalty_start) continue;


        $days_overdue = floor((strtotime($today) - strtotime($penalty_start)) / 86400);


        if ($frequency == "daily") $periods = $days_overdue;
        elseif ($frequency == "weekly") $periods = floor($days_overdue / 7);
        else $periods = floor($days_overdue / 30);

        if ($periods <= 0) continue;


        $base = $row['principal_due'] + $row['interest_due'];
        if ($type == "percent") {
            $new_penalty = ($base * $value / 100) * $periods;
        } else {
            $new_penalty = $value * $periods;
        }


        $stmt2 = $db->prepare("
            UPDATE loan_schedule
            SET
                penalty_due = penalty_due + ?,
                total_due = principal_due + interest_due + penalty_due + ?,
                status = ?
            WHERE schedule_id = ?
        ");


        $status = ($row['status'] == 'pending' && $today > $due) ? 'overdue' : $row['status'];

        $stmt2->bind_param("ddsi", $new_penalty, $new_penalty, $status, $row['schedule_id']);
        $stmt2->execute();
    }
}


if (isset($_POST['save-loan-payments'])) {

    require('db_connect.php');

    $loan_id     = (int)($_POST['loan_id'] ?? 0);
    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    $account_id  = (int)($_POST['account_id'] ?? 0);
    $amount_paid = (float)($_POST['amount_paid'] ?? 0);

    $payment_date = date("Y-m-d");


    if ($amount_paid <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid payment amount."
        ]);
        exit;
    }

    if ($amount_paid <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid payment amount."
        ]);
        exit;
    }

    $balStmt = $db->prepare("
    SELECT IFNULL(SUM(principal_due + interest_due + penalty_due),0) AS balance
    FROM loan_schedule
    WHERE loan_id = ?
    AND status = 'ongoing'
");

    $balStmt->bind_param("i", $loan_id);
    $balStmt->execute();

    $loan_balance = (float)$balStmt->get_result()->fetch_assoc()['balance'];

    if ($amount_paid > $loan_balance) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid payment. Amount exceeds remaining balance of " . number_format($loan_balance, 2)
        ]);
        exit;
    }



    $db->begin_transaction();

    try {


        applyLoanPenalty($db, $loan_id);

        $schedQry = $db->query("
            SELECT *
            FROM loan_schedule
            WHERE schedule_id = $schedule_id
            AND loan_id = $loan_id
            LIMIT 1
        ");
        if ($schedQry->num_rows == 0) throw new Exception("Schedule not found.");
        $sched = $schedQry->fetch_assoc();

        $principal_due = (float)$sched['principal_due'];
        $interest_due  = (float)$sched['interest_due'];
        $penalty_due   = (float)$sched['penalty_due'];


        $remaining = $amount_paid;


        $penalty_paid = min($remaining, $penalty_due);
        $remaining -= $penalty_paid;


        $interest_paid = min($remaining, $interest_due);
        $remaining -= $interest_paid;

        $principal_paid = min($remaining, $principal_due);
        $remaining -= $principal_paid;


        $reference = "PMT-" . str_pad($loan_id, 6, "0", STR_PAD_LEFT) . "-" . time();

        $stmt = $db->prepare("
            INSERT INTO loan_payments
            (loan_id, schedule_id, account_id, amount_paid, principal_paid, interest_paid, penalty_paid, payment_date, reference_no)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "iiiddddss",
            $loan_id,
            $schedule_id,
            $account_id,
            $amount_paid,
            $principal_paid,
            $interest_paid,
            $penalty_paid,
            $payment_date,
            $reference
        );
        $stmt->execute();


        $new_penalty_due = max(0, $penalty_due - $penalty_paid);
        $new_total_due   = max(0, ($principal_due - $principal_paid) + ($interest_due - $interest_paid) + $new_penalty_due);
        $status          = ($new_total_due <= 0) ? 'paid' : 'ongoing';

        $stmt2 = $db->prepare("
            UPDATE loan_schedule
            SET penalty_due = ?, total_due = ?, status = ?
            WHERE schedule_id = ?
        ");
        $stmt2->bind_param("ddsi", $new_penalty_due, $new_total_due, $status, $schedule_id);
        $stmt2->execute();


        if ($remaining > 0) {
            $futureSchedules = $db->query("
                SELECT schedule_id, principal_due, interest_due, penalty_due
                FROM loan_schedule
                WHERE loan_id = $loan_id
                AND schedule_id > $schedule_id
                ORDER BY due_date ASC
            ");

            while ($row = $futureSchedules->fetch_assoc()) {
                $sched_id  = $row['schedule_id'];
                $principal = (float)$row['principal_due'];
                $interest  = (float)$row['interest_due'];
                $penalty   = (float)$row['penalty_due'];

                $extra = $remaining;


                $principal_payment = min($extra, $principal);
                $principal -= $principal_payment;
                $extra -= $principal_payment;


                $interest_payment = min($extra, $interest);
                $interest -= $interest_payment;
                $extra -= $interest_payment;


                $penalty_payment = min($extra, $penalty);
                $penalty -= $penalty_payment;
                $extra -= $penalty_payment;

                $remaining = $extra;

                $total_due = $principal + $interest + $penalty;
                $sched_status = ($total_due <= 0) ? 'paid' : 'ongoing';

                $stmtAdj = $db->prepare("
                    UPDATE loan_schedule
                    SET principal_due = ?, interest_due = ?, penalty_due = ?, total_due = ?, status = ?
                    WHERE schedule_id = ?
                ");
                $stmtAdj->bind_param("ddddsi", $principal, $interest, $penalty, $total_due, $sched_status, $sched_id);
                $stmtAdj->execute();

                if ($remaining <= 0) break;
            }
        }


        $stmt3 = $db->prepare("
            INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at)
            VALUES (?,?,?,?,?,?,NOW())
        ");
        $type    = 5;
        $remarks = "Loan payment ref $reference";
        $stmt3->bind_param("iidsss", $account_id, $type, $amount_paid, $reference, $remarks, $payment_date);
        $stmt3->execute();


        $json = json_encode([
            "loan_id"   => $loan_id,
            "reference" => $reference,
            "amount"    => $amount_paid
        ]);
        $stmt4 = $db->prepare("
            INSERT INTO tbl_history
            (details, history_type, field_status)
            VALUES (?,58,0)
        ");
        $stmt4->bind_param("s", $json);
        $stmt4->execute();


        $balQry = $db->query("
            SELECT SUM(principal_due + interest_due + penalty_due)
            - (SELECT IFNULL(SUM(principal_paid + interest_paid + penalty_paid),0) 
               FROM loan_payments WHERE loan_id=$loan_id) AS balance
            FROM loan_schedule
            WHERE loan_id=$loan_id
        ");
        $bal = $balQry->fetch_assoc()['balance'] ?? 0;

        if ($bal <= 0) {


            $db->query("
        UPDATE loans
        SET status='paid'
        WHERE loan_id=$loan_id
    ");


            $db->query("
        UPDATE loan_schedule
        SET 
            principal_due = 0,
            interest_due = 0,
            penalty_due = 0,
            total_due = 0,
            status = 'paid'
        WHERE loan_id = $loan_id
    ");
        }

        $db->commit();

        echo json_encode([
            "success"   => true,
            "reference" => $reference
        ]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }

    exit;
}


if (isset($_POST['view_loanpayments_receipt'])) {
    require('db_connect.php');

    $_GET['reference_no'] = $_POST['reference_no'];

    require('admin/loan_payment_receipt.php');

    exit;
}

if (isset($_POST['view_capital_receipt'])) {
    require('db_connect.php');

    $_GET['reference_no'] = $_POST['reference_no'];

    require('admin/capital_share_details.php');

    exit;
}


if (isset($_POST['add_loan_type'])) {
    require('db_connect.php');

    $name = trim($_POST['loan_type_name'] ?? '');
    $interest = floatval($_POST['interest_rate'] ?? 0);
    $term_value = intval($_POST['term_value'] ?? 0);
    $term_unit = trim($_POST['term_unit'] ?? '');
    $frequency = trim($_POST['payment_frequency'] ?? '');
    $comaker = intval($_POST['require_comaker'] ?? 0);
    $status = trim($_POST['status'] ?? 'inactive');

    if ($name === '' || $term_value <= 0 || !in_array($term_unit, ['days', 'weeks', 'months']) || !in_array($frequency, ['daily', 'weekly', 'monthly'])) {
        echo "error: invalid input";
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO loan_types
        (loan_type_name, interest_rate, term_value, term_unit, payment_frequency, require_comaker, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("sdissis", $name, $interest, $term_value, $term_unit, $frequency, $comaker, $status);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}


if (isset($_POST['save-capital-share'])) {

    require('db_connect.php');


    $member_id = intval($_POST['member_id']);
    $amount    = floatval($_POST['amount']);
    $remarks   = 'Capital Share Contribution';

    $user_id  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $fullname = isset($_SESSION['fullname'])
        ? $db->real_escape_string($_SESSION['fullname'])
        : "System";


    $minimum_capital_share = 250;
    if ($amount < $minimum_capital_share) {
        echo "Error: Minimum monthly capital share is " . number_format($minimum_capital_share, 2);
        exit;
    }


    if ($member_id <= 0 || $amount <= 0) {
        echo "Invalid input";
        exit;
    }

    $db->begin_transaction();

    try {
        $typeQuery = $db->query("
            SELECT account_type_id 
            FROM account_types 
            WHERE type_name = 'capital_share'
            LIMIT 1
        ");

        if (!$typeQuery || $typeQuery->num_rows == 0) {
            throw new Exception("Capital Share account type not found.");
        }

        $type = $typeQuery->fetch_assoc();
        $account_type_id = intval($type['account_type_id']);
        $stmt = $db->prepare("
            SELECT account_id 
            FROM accounts 
            WHERE member_id = ? 
            AND account_type_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $member_id, $account_type_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();
            $account_id = intval($account['account_id']);
        } else {
            $account_number = 'CAP-' . $member_id . '-' . time();

            $stmt = $db->prepare("
                INSERT INTO accounts 
                (member_id, account_type_id, account_number)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param("iis", $member_id, $account_type_id, $account_number);

            $account_id = intval($stmt->insert_id);

            if ($account_id <= 0) {
                throw new Exception("Failed to create account.");
            }
        }


        $reference = 'CS-' . str_pad($account_id, 6, '0', STR_PAD_LEFT) . '-' . date('ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 4));




        $typeQuery = $db->query("
            SELECT transaction_type_id 
            FROM transaction_types 
            WHERE type_name = 'deposit'
            LIMIT 1
        ");

        if (!$typeQuery || $typeQuery->num_rows == 0) {
            throw new Exception("Deposit transaction type not found.");
        }

        $type = $typeQuery->fetch_assoc();
        $transaction_type_id = intval($type['transaction_type_id']);

        $stmt = $db->prepare("
            INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, remarks)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iidss",
            $account_id,
            $transaction_type_id,
            $amount,
            $reference,
            $remarks
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to save transaction.");
        }
        $detailsArray = [

            'member_id'  => $member_id,
            'account_id' => $account_id,
            'amount'     => $amount,
            'reference'  => $reference,
            'type'       => 'capital_share',
            'user_id'    => $user_id,
            'employee'   => $fullname,
            'date'       => date("Y-m-d H:i:s")

        ];
        $detailsJson = $db->real_escape_string(json_encode($detailsArray));
        $historyQuery = "
            INSERT INTO tbl_history
            (details, history_type, field_status)
            VALUES
            ('$detailsJson', 50, 0)
        ";
        if (!$db->query($historyQuery)) {
            throw new Exception("Failed to save history.");
        }
        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}



if (isset($_POST['view_savings_receipt'])) {
    require('db_connect.php');
    $_GET['reference_no'] = $_POST['reference_no'];
    require('admin/savings_details.php');
    exit;
}


if (isset($_POST['save-savings'])) {

    require('db_connect.php');


    $member_id = intval($_POST['member_id']);
    $amount    = floatval($_POST['amount']);
    $remarks   = 'Savings Deposit';

    $user_id  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $fullname = isset($_SESSION['fullname'])
        ? $db->real_escape_string($_SESSION['fullname'])
        : "System";


    $minimum_savings = 250;
    if ($amount < $minimum_savings) {
        echo "Error: Minimum monthly savings required is" . number_format($minimum_savings, 2);
        exit;
    }


    if ($member_id <= 0 || $amount <= 0) {
        echo "Invalid input";
        exit;
    }

    $db->begin_transaction();

    try {

        $typeQuery = $db->query("
            SELECT account_type_id 
            FROM account_types 
            WHERE type_name = 'savings'
            LIMIT 1
        ");

        if (!$typeQuery || $typeQuery->num_rows == 0) {
            throw new Exception("Savings account type not found.");
        }

        $type = $typeQuery->fetch_assoc();
        $account_type_id = intval($type['account_type_id']);


        $stmt = $db->prepare("
            SELECT account_id 
            FROM accounts 
            WHERE member_id = ? 
            AND account_type_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $member_id, $account_type_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();
            $account_id = intval($account['account_id']);
        } else {
            $account_number = 'SAV-' . $member_id . '-' . time();

            $stmt = $db->prepare("
                INSERT INTO accounts 
                (member_id, account_type_id, account_number)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $member_id, $account_type_id, $account_number);
            $stmt->execute();

            $account_id = intval($stmt->insert_id);

            if ($account_id <= 0) {
                throw new Exception("Failed to create savings account.");
            }
        }


        $reference = 'SAV-' . str_pad($account_id, 6, '0', STR_PAD_LEFT) . '-' . date('ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 4));


        $typeQuery = $db->query("
            SELECT transaction_type_id 
            FROM transaction_types 
            WHERE type_name = 'deposit'
            LIMIT 1
        ");

        if (!$typeQuery || $typeQuery->num_rows == 0) {
            throw new Exception("Deposit transaction type not found.");
        }

        $type = $typeQuery->fetch_assoc();
        $transaction_type_id = intval($type['transaction_type_id']);

        $stmt = $db->prepare("
            INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, remarks)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iidss",
            $account_id,
            $transaction_type_id,
            $amount,
            $reference,
            $remarks
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to save savings transaction.");
        }

        $detailsArray = [
            'member_id'  => $member_id,
            'account_id' => $account_id,
            'amount'     => $amount,
            'reference'  => $reference,
            'type'       => 'savings',
            'user_id'    => $user_id,
            'employee'   => $fullname,
            'date'       => date("Y-m-d H:i:s")
        ];
        $detailsJson = $db->real_escape_string(json_encode($detailsArray));

        $historyQuery = "
            INSERT INTO tbl_history
            (details, history_type, field_status)
            VALUES
            ('$detailsJson', 51, 0)
        ";

        if (!$db->query($historyQuery)) {
            throw new Exception("Failed to save history.");
        }

        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}


/* =========================================================
   REQUEST WITHDRAWAL
========================================================= */
if (isset($_POST['action_type']) && $_POST['action_type'] === 'request_withdrawal') {
    require('db_connect.php');

    $account_id = intval($_POST['account_id'] ?? 0);
    $amount     = floatval($_POST['amount'] ?? 0);
    $reason     = trim($_POST['reason'] ?? '');

    if ($account_id <= 0 || $amount <= 0 || empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO savings_withdrawal_requests
        (account_id, amount, reason, status, date_requested)
        VALUES (?, ?, ?, 'pending', NOW())
    ");

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => $db->error]);
        exit;
    }

    $stmt->bind_param("ids", $account_id, $amount, $reason);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Request submitted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    exit;
}

/* =========================================================
   APPROVE WITHDRAWAL (store negative amounts and sync reference)
========================================================= */
if (isset($_POST['action_type']) && $_POST['action_type'] === 'approve_withdrawal') {
    require('db_connect.php');

    $request_id  = intval($_POST['request_id'] ?? 0);
    $approved_by = $_SESSION['user_id'] ?? 0;

    if ($request_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit;
    }

    $db->begin_transaction();

    try {
        /* -----------------------------
           Step 1: Get Request Info
        ----------------------------- */
        $stmt = $db->prepare("
            SELECT account_id, amount, reason, remarks
            FROM savings_withdrawal_requests
            WHERE request_id = ? AND status = 'pending'
            LIMIT 1
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $req = $result->fetch_assoc();
        $stmt->close();

        if (!$req) {
            throw new Exception("Invalid or already processed request.");
        }

        $account_id = intval($req['account_id']);
        $amount     = floatval($req['amount']);
        $remarks    = $req['remarks'] ?: ($req['reason'] ?: 'Withdrawal approved');

        /* -----------------------------
           Step 2: Check Current Balance
        ----------------------------- */
        $stmt = $db->prepare("
            SELECT IFNULL(SUM(
                CASE 
                    WHEN tt.type_name IN ('deposit','capital_share','loan_release') THEN t.amount
                    WHEN tt.type_name = 'withdrawal' THEN t.amount
                    ELSE 0
                END
            ),0) AS balance
            FROM transactions t
            JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
            WHERE t.account_id = ?
        ");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $balance = $stmt->get_result()->fetch_assoc()['balance'];
        $stmt->close();

        if ($balance < $amount) {
            throw new Exception("Insufficient savings balance. Current: ₱$balance, Requested: ₱$amount");
        }

        /* -----------------------------
           Step 3: Get Withdrawal Type ID
        ----------------------------- */
        $type_q = $db->query("
            SELECT transaction_type_id 
            FROM transaction_types 
            WHERE type_name = 'withdrawal'
            LIMIT 1
        ");
        $type = $type_q->fetch_assoc();
        if (!$type) {
            throw new Exception("Withdrawal transaction type not found.");
        }
        $type_id = intval($type['transaction_type_id']);

        /* -----------------------------
           Step 4: Generate Reference Number
        ----------------------------- */
        $reference_no = 'WD-' . str_pad($account_id, 6, '0', STR_PAD_LEFT) . '-' . date('ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 4));

        /* -----------------------------
           Step 5: Insert Transaction (negative amount)
        ----------------------------- */
        $amount_to_insert = -1 * $amount; // store as negative

        $stmt = $db->prepare("
            INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at)
            VALUES (?, ?, ?, ?, ?, CURDATE(), NOW())
        ");
        $stmt->bind_param("iidss", $account_id, $type_id, $amount_to_insert, $reference_no, $remarks);
        $stmt->execute();
        $stmt->close();

        /* -----------------------------
           Step 6: Update Withdrawal Request
           (store same reference_no as transaction)
        ----------------------------- */
        $stmt = $db->prepare("
            UPDATE savings_withdrawal_requests
            SET status = 'approved',
                approved_by = ?,
                date_approved = NOW(),
                reference_no = ?
            WHERE request_id = ?
        ");
        $stmt->bind_param("isi", $approved_by, $reference_no, $request_id);
        $stmt->execute();
        $stmt->close();

        /* -----------------------------
           Step 7: Commit Transaction
        ----------------------------- */
        $db->commit();

        echo json_encode([
            'status' => 'success',
            'reference_no' => $reference_no
        ]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
/* =========================================================
   REJECT WITHDRAWAL
========================================================= */
if (isset($_POST['action_type']) && $_POST['action_type'] === 'reject_withdrawal') {
    require('db_connect.php');

    $request_id = intval($_POST['request_id'] ?? 0);
    $approved_by = $_SESSION['user_id'] ?? 0;

    if ($request_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE savings_withdrawal_requests
        SET status = 'rejected',
            approved_by = ?,
            date_approved = NOW()
        WHERE request_id = ?
        AND status = 'pending'
    ");
    $stmt->bind_param("ii", $approved_by, $request_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success']);
    exit;
}



// ----------------------------
// Save Distribution Cycle
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_distribution') {
    require('db_connect.php');

    $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
    $dividend_amount = isset($_POST['dividend_amount']) ? (float)$_POST['dividend_amount'] : 0;
    $patronage_amount = isset($_POST['patronage_amount']) ? (float)$_POST['patronage_amount'] : 0;
    $members = isset($_POST['members']) ? json_decode($_POST['members'], true) : [];

    if (!$members) {
        echo "0|No members to save";
        exit;
    }


    $stmt_check = $db->prepare("
SELECT COUNT(*) AS cnt
FROM distribution_cycles
WHERE YEAR(created_at) = ?
");
    $stmt_check->bind_param("i", $year);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result()->fetch_assoc();

    if ($res_check['cnt'] > 0) {
        echo "0|Distribution for year $year already exists. You cannot insert twice for the same year.";
        exit;
    }


    $db->begin_transaction();

    try {

        $stmt = $db->prepare("
INSERT INTO distribution_cycles (dividend_amount, patronage_amount)
VALUES (?, ?)
");
        $stmt->bind_param("dd", $dividend_amount, $patronage_amount);
        $stmt->execute();
        $cycle_id = $db->insert_id;


        $stmt = $db->prepare("
INSERT INTO distribution_records
(cycle_id, cust_id, share_capital, total_purchases, dividend, patronage, total_benefit)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

        foreach ($members as $m) {
            $stmt->bind_param(
                "iiddddd",
                $cycle_id,
                $m['id'],
                $m['share'],
                $m['purchase'],
                $m['dividend'],
                $m['patronage'],
                $m['total']
            );
            $stmt->execute();
        }

        $db->commit();
        echo "1";
    } catch (Exception $e) {
        $db->rollback();
        echo "0|" . $e->getMessage();
    }

    exit;
}


if (isset($_POST['action']) && $_POST['action'] === 'get_distribution_records') {
    require('db_connect.php');
    $cycle_id = intval($_POST['cycle_id']);

    $stmt = $db->prepare("
SELECT dr.*, c.name AS customer_name,
dd.amount_disbursed, dd.payment_method, dd.reference_no, dd.disbursed_at, dd.remarks
FROM distribution_records dr
JOIN tbl_customer c ON c.cust_id = dr.cust_id
LEFT JOIN distribution_disbursements dd ON dd.record_id = dr.id
WHERE dr.cycle_id = ?
");
    $stmt->bind_param("i", $cycle_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $records = [];
    while ($r = $res->fetch_assoc()) {
        $records[] = $r;
    }

    echo json_encode($records);
    exit;
}

// Save a disbursement
if (isset($_POST['action']) && $_POST['action'] === 'save_disbursement') {
    require('db_connect.php');
    $record_id = intval($_POST['record_id']);
    $payment_method = $_POST['payment_method'];
    $reference_no = $_POST['reference_no'];
    $user_id = intval($_SESSION['user_id']);

    // Get record info
    $stmt = $db->prepare("SELECT * FROM distribution_records WHERE id = ?");
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $rec = $stmt->get_result()->fetch_assoc();

    if (!$rec) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    $amount = $rec['total_benefit'];
    $cycle_id = $rec['cycle_id'];
    $cust_id = $rec['cust_id'];
    $disbursed_at = date('Y-m-d H:i:s');

    $stmt = $db->prepare("
INSERT INTO distribution_disbursements
(record_id, cust_id, cycle_id, amount_disbursed, payment_method, reference_no, disbursed_by, disbursed_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
    $stmt->bind_param(
        "iiiissis",
        $record_id,
        $cust_id,
        $cycle_id,
        $amount,
        $payment_method,
        $reference_no,
        $user_id,
        $disbursed_at
    );

    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            'success' => true,
            'disbursed_at' => $disbursed_at,
            'record_id' => $record_id,
            'amount_disbursed' => $amount,
            'reference_no' => $reference_no
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save disbursement.']);
    }
    exit;
}


if (isset($_POST['action_type']) && $_POST['action_type'] === 'void_transaction') {

    require('db_connect.php');

    $transaction_id = intval($_POST['transaction_id'] ?? 0);
    $reason         = trim($_POST['reason'] ?? '');
    $voided_by      = $_SESSION['user_id'] ?? 0;

    if ($transaction_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid transaction']);
        exit;
    }

    $db->begin_transaction();

    try {

        /* -----------------------------
           Step 1: Get Original Transaction
        ----------------------------- */
        $stmt = $db->prepare("
            SELECT *
            FROM transactions
            WHERE transaction_id = ?
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trx = $result->fetch_assoc();
        $stmt->close();

        if (!$trx) {
            throw new Exception("Transaction not found or already voided.");
        }

        $account_id = $trx['account_id'];
        $type_id    = $trx['transaction_type_id'];
        $amount     = $trx['amount'];
        $reference  = $trx['reference_no'];

        /* -----------------------------
           Step 2: Create Reversal Transaction
           Reverse the amount
        ----------------------------- */
        $reverse_amount = -1 * $amount;

        $reverse_reference = "VOID-" . $reference;

        $stmt = $db->prepare("
            INSERT INTO transactions
            (account_id, transaction_type_id, amount, reference_no, remarks, transaction_date, created_at, reversed_transaction_id)
            VALUES (?, ?, ?, ?, ?, CURDATE(), NOW(), ?)
        ");

        $remarks = "Reversal of $reference";
        $stmt->bind_param(
            "iidssi",
            $account_id,
            $type_id,
            $reverse_amount,
            $reverse_reference,
            $remarks,
            $transaction_id
        );
        $stmt->execute();
        $stmt->close();

        /* -----------------------------
           Step 3: Mark Original as Voided
        ----------------------------- */
        $stmt = $db->prepare("
            UPDATE transactions
            SET status = 'voided',
                voided_at = NOW(),
                voided_by = ?,
                void_reason = ?
            WHERE transaction_id = ?
        ");
        $stmt->bind_param("isi", $voided_by, $reason, $transaction_id);
        $stmt->execute();
        $stmt->close();

        /* -----------------------------
           Step 4: Special Handling (Important!)
        ----------------------------- */

        // If loan payment → reverse loan_schedule
        if ($type_id == 5) { // loan payment type id

            $db->query("
                UPDATE loan_schedule
                SET status='ongoing'
                WHERE loan_id IN (
                    SELECT loan_id FROM loan_payments WHERE reference_no='$reference'
                )
            ");

            $db->query("
                DELETE FROM loan_payments
                WHERE reference_no='$reference'
            ");

            $db->query("
                UPDATE loans SET status='ongoing'
                WHERE loan_id IN (
                    SELECT loan_id FROM loan_schedule WHERE reference_no='$reference'
                )
            ");
        }

        /* -----------------------------
           Step 5: Log History
        ----------------------------- */
        $details = json_encode([
            'voided_transaction_id' => $transaction_id,
            'reference' => $reference,
            'reason' => $reason,
            'voided_by' => $voided_by,
            'date' => date("Y-m-d H:i:s")
        ]);

        $db->query("
            INSERT INTO tbl_history
            (details, history_type, field_status)
            VALUES ('$details', 99, 0)
        ");

        $db->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {

        $db->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
