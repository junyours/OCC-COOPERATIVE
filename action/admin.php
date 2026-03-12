<?php
// paided
/*   
       1 = New sales
       2 = Cancel sale
       4 = void sales
       3 = Active sale
       11 = add products
       12 = update  products info
       13  = product damage
       14  = upload image
       15 = add customer
       16 = edit customer
       17  = add supplier
       18  = edit supplier
       19 = save cashier
       20 = update cashier
       22 = save receiving
       25 = add expences
       26 = system login
       27 = inventory deduction
       28  = upload image menu
    
       
    */
function logout()
{
    session_destroy();
}

function save_product($data)
{
    require('db_connect.php');

    header('Content-Type: application/json'); // important for AJAX

    // Escape text fields
    $product_code   = $db->real_escape_string($data['product_code']);
    $unit           = $db->real_escape_string($data['unit']);
    $product_name   = $db->real_escape_string($data['product_name']);
    $quantity       = (int)$data['quantity'];
    $critical_qty   = (int)$data['critical_qty'];
    $selling_price  = (float)$data['selling_price'];
    $supplier_price = (float)$data['supplier_price'];

    // Insert product
    $query = "
        INSERT INTO tbl_products 
        (product_code, unit, product_name, quantity, critical_qty, selling_price, supplier_price)
        VALUES (
            '$product_code',
            '$unit',
            '$product_name',
            $quantity,
            $critical_qty,
            $selling_price,
            $supplier_price
        )
    ";

    if ($db->query($query)) {


        $product_id = $db->insert_id;
        $arrayData = ['product_id' => $product_id, 'user_id' => $_SESSION['user_id']];
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        // Insert history
        $db->query("
            INSERT INTO tbl_history (date_history, details, history_type)
            VALUES ('$today', '$arrayGDetails', '11')
        ");

        // Insert product history
        $db->query("
            INSERT INTO tbl_product_history
            (hist_date, details, details_type, product_id, qty, balance, type)
            VALUES ('$today', '', '2', '$product_id', '$quantity', '$quantity', '2')
        ");

        // Return success JSON
        echo json_encode(['status' => 'success', 'product_id' => $product_id]);
    } else {
        // Return error JSON
        echo json_encode(['status' => 'error', 'message' => $db->error]);
    }
}



// function save_category($data)
// {
//     require('db_connect.php');
//     $query = "INSERT INTO tbl_category (category_name) VALUES ('" . $data['category_name'] . "')";
//     if ($db->exec($query)) {
//         $result_returns = "SELECT * FROM  tbl_category  ORDER BY cat_id DESC   ";
//         $result_data = $db->query($result_returns);
//         $datas = $result_data->fetchArray();
//         $arrayData = array('cat_id' => $datas[0], 'user_id' => $_SESSION['user_id']);
//         $arrayGDetails = json_encode($arrayData);
//         $today = date("Y-m-d h:i:s");
//         $insert_history = "INSERT INTO tbl_history (date_history,details,history_type) VALUES ('$today','$arrayGDetails','31')";
//         $db->exec($insert_history);
//         echo $datas[0];
//     }
// }

// function save_customer($data){
//     require('db_connect.php');    
//     $query = "INSERT INTO tbl_customer (name,address,contact) VALUES ('".$data['name']."','".$data['address']."','".$data['contact']."')";
//     if( $db->exec($query) ){
//         echo "1";
//         $result_returns = "SELECT * FROM  tbl_customer  ORDER BY cust_id DESC   ";
//         $result_data = $db->query($result_returns);
//         $datas = $result_data->fetchArray();
//         $arrayData = array('cust_id' => $datas[0], 'user_id' => $_SESSION['user_id'] );
//         $arrayGDetails = json_encode($arrayData);
//         $today = date("Y-m-d h:i:s");
//         $insert_history = "INSERT INTO tbl_history (date_history,details,history_type) VALUES ('$today','$arrayGDetails','15')";
//         $db->exec($insert_history) ;
//     }
// }

function update_customer($data)
{
    require('db_connect.php'); // $db must be a mysqli connection

    $cust_id = mysqli_real_escape_string($db, $data['cust_id']);
    $name = mysqli_real_escape_string($db, $data['name']);
    $address = mysqli_real_escape_string($db, $data['address']);
    $contact = mysqli_real_escape_string($db, $data['contact']);

    $query = "UPDATE tbl_customer 
              SET name='$name', address='$address', contact='$contact' 
              WHERE cust_id='$cust_id'";

    if ($db->query($query)) {
        echo "1";
        $arrayData = array('cust_id' => $cust_id, 'user_id' => $_SESSION['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                           VALUES ('$today','$arrayGDetails','16')";
        $db->query($insert_history);
    }
}

function save_supplier($data)
{
    require('db_connect.php');

    $name = mysqli_real_escape_string($db, $data['name']);
    $address = mysqli_real_escape_string($db, $data['address']);
    $contact = mysqli_real_escape_string($db, $data['contact']);

    $query = "INSERT INTO tbl_supplier (supplier_name, supplier_address, supplier_contact) 
              VALUES ('$name', '$address', '$contact')";

    if ($db->query($query)) {
        echo "1";

        $supplier_id = $db->insert_id; // last inserted id

        if (isset($data['receiving_data']) && $data['receiving_data'] == 'yes') {
            $_SESSION['pos-supplier'] = $supplier_id;
            $_SESSION['pos-supplier-name'] = $name;
        }

        $arrayData = array('supplier_id' => $supplier_id, 'user_id' => $_SESSION['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                           VALUES ('$today','$arrayGDetails','17')";
        $db->query($insert_history);
    }
}

function update_supplier($data)
{
    require('db_connect.php');

    $supplier_id = mysqli_real_escape_string($db, $data['supplier_id']);
    $name = mysqli_real_escape_string($db, $data['name']);
    $address = mysqli_real_escape_string($db, $data['address']);
    $contact = mysqli_real_escape_string($db, $data['contact']);

    $query = "UPDATE tbl_supplier 
              SET supplier_name='$name', supplier_address='$address', supplier_contact='$contact' 
              WHERE supplier_id='$supplier_id'";

    if ($db->query($query)) {
        echo "1";
        $arrayData = array('supplier_id' => $supplier_id, 'user_id' => $_SESSION['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                           VALUES ('$today','$arrayGDetails','18')";
        $db->query($insert_history);
    }
}

function save_customer_sales($data)
{
    require('db_connect.php');

    $name = mysqli_real_escape_string($db, $data['name']);
    $address = mysqli_real_escape_string($db, $data['address']);
    $contact = mysqli_real_escape_string($db, $data['contact']);

    $query = "INSERT INTO tbl_customer (name, address, contact) VALUES ('$name', '$address', '$contact')";

    if ($db->query($query)) {
        echo "1";
        $cust_id = $db->insert_id; // last inserted customer ID
        $_SESSION['pos-customer'] = $cust_id;
        $_SESSION['pos-name'] = $data['name'];
    }
}

function save_cashier($data)
{
    require('db_connect.php');

    $field_status = mysqli_real_escape_string($db, $data['field_status']);
    $name = mysqli_real_escape_string($db, $data['name']);
    $username = mysqli_real_escape_string($db, $data['username']);
    $password = mysqli_real_escape_string($db, $data['password']);
    $usertype = mysqli_real_escape_string($db, $data['usertype']);

    $query = "INSERT INTO tbl_users (field_status, fullname, username, password, usertype) 
              VALUES ('$field_status', '$name', '$username', '$password', '$usertype')";

    if ($db->query($query)) {
        echo "1";
        $user_id = $db->insert_id;

        $arrayData = array('cashier_id' => $user_id, 'user_id' => $_SESSION['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                           VALUES ('$today', '$arrayGDetails', '19')";
        $db->query($insert_history);
    }
}

function update_cashier($data)
{
    require('db_connect.php');

    $user_id = mysqli_real_escape_string($db, $data['user_id']);
    $field_status = mysqli_real_escape_string($db, $data['field_status']);
    $name = mysqli_real_escape_string($db, $data['name']);

    if (!empty($data['password'])) {
        $password = mysqli_real_escape_string($db, $data['password']);
        $query = "UPDATE tbl_users 
                  SET field_status='$field_status', fullname='$name', password='$password' 
                  WHERE user_id='$user_id'";
    } else {
        $query = "UPDATE tbl_users 
                  SET field_status='$field_status', fullname='$name' 
                  WHERE user_id='$user_id'";
    }

    if ($db->query($query)) {
        echo "1";
        $arrayData = array('cashier_id' => $user_id, 'user_id' => $_SESSION['user_id']);
        $arrayGDetails = json_encode($arrayData);
        $today = date("Y-m-d H:i:s");

        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) 
                           VALUES ('$today', '$arrayGDetails', '20')";
        $db->query($insert_history);
    }
}

function save_cart($data, $silent = false)
{
    require('db_connect.php');

    $product_id = mysqli_real_escape_string($db, $data['product_id']);
    $user_id = mysqli_real_escape_string($db, $data['user_id']);
    $quantity = intval($data['quantity']);

    // Get product info
    $query_products = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
    $result_product = $db->query($query_products);
    $datas_products = $result_product->fetch_assoc();

    // Check existing cart
    $query2 = "SELECT * FROM tbl_cart WHERE product_id='$product_id' AND user_id='$user_id'";
    $result2 = $db->query($query2);
    $datas2 = $result2->fetch_assoc();

    $qtyValue = $quantity;
    if ($datas2) {
        $qtyValue += $datas2['quantity_order'];
    }

    if ($qtyValue <= $datas_products['quantity']) {
        if ($datas2) {
            $quantity_order = $quantity + $datas2['quantity_order'];
            $query = "UPDATE tbl_cart SET quantity_order='$quantity_order' WHERE cart_id='" . $datas2['cart_id'] . "'";
            $db->query($query);
            if (!$silent) echo json_encode(['message' => 'save2']);
        } else {
            $query = "INSERT INTO tbl_cart (product_id, quantity_order, user_id) 
                      VALUES ('$product_id', '$quantity', '$user_id')";
            $db->query($query);
            if (!$silent) echo json_encode(['message' => 'save']);
        }
    } else {
        if (!$silent) {
            echo json_encode([
                'message' => 'unsave',
                'product_name' => $datas_products['product_name'],
                'quantity_left' => $datas_products['quantity'],
                'quantity_order' => $qtyValue,
                'unit' => $datas_products['unit']
            ]);
        }
    }
}

function save_cartupdate($data)
{
    require('db_connect.php');

    $product_id = mysqli_real_escape_string($db, $data['product_id']);
    $user_id = mysqli_real_escape_string($db, $data['user_id']);
    $quantity = intval($data['quantity']);

    $query_products = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
    $result_product = $db->query($query_products);
    $datas_products = $result_product->fetch_assoc();

    if ($quantity < $datas_products['quantity']) {

        $query = "SELECT * FROM tbl_cart3 WHERE product_id='$product_id' AND user_id='$user_id'";
        $result = $db->query($query);
        $datas = $result->fetch_assoc();

        if ($datas) {
            $quantity_order = $quantity + $datas['quantity_order'];
            $query = "UPDATE tbl_cart3 SET quantity_order='$quantity_order' WHERE cart_id='" . $datas['cart_id'] . "'";
            if ($db->query($query)) {
                echo json_encode(['message' => 'save2']);
            }
        } else {
            $query = "INSERT INTO tbl_cart3 (product_id, quantity_order, user_id) VALUES ('$product_id', '$quantity', '$user_id')";
            if ($db->query($query)) {
                echo json_encode(['message' => 'save']);
            }
        }
    } else {
        echo json_encode([
            'message' => 'unsave',
            'product_name' => $datas_products['product_name'],
            'quantity_left' => $datas_products['quantity'],
            'quantity_order' => $quantity,
            'unit' => $datas_products['unit']
        ]);
    }
}

function save_cart2($data)
{
    require('db_connect.php');

    $product_id = mysqli_real_escape_string($db, $data['product_id']);
    $user_id = mysqli_real_escape_string($db, $data['user_id']);
    $quantity = intval($data['quantity']);

    $query_products = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
    $result_product = $db->query($query_products);
    $datas_products = $result_product->fetch_assoc();
    $supplier_price = $datas_products['supplier_price'];

    $query2 = "SELECT * FROM tbl_cart2 WHERE product_id='$product_id' AND user_id='$user_id'";
    $result = $db->query($query2);
    $datas = $result->fetch_assoc();

    if ($datas) {
        $quantity_order = $quantity + $datas['quantity_order'];
        $query = "UPDATE tbl_cart2 SET quantity_order='$quantity_order' WHERE cart_id='" . $datas['cart_id'] . "'";
    } else {
        $query = "INSERT INTO tbl_cart2 (product_id, quantity_order, user_id, price) 
                  VALUES ('$product_id', '$quantity', '$user_id', '$supplier_price')";
    }

    if ($db->query($query)) {
        echo json_encode(['message' => 'save']);
    }
}

function view_cart($data)
{
    require('db_connect.php');

    $user_id = mysqli_real_escape_string($db, $data['user_id']);

    $products = "SELECT * FROM tbl_cart 
                 INNER JOIN tbl_products ON tbl_cart.product_id = tbl_products.product_id 
                 WHERE tbl_cart.user_id='$user_id' 
                 ORDER BY cart_id ASC";
    $result_products = $db->query($products);

    $i = 0;
    while ($row = $result_products->fetch_assoc()) {
        $image = $row['image'];
        $image_file = !empty($image) ? '../uploads/' . $image : '../images/no-image.png';
        $product_name = (strlen($row['product_name']) > 37) ? substr($row['product_name'], 0, 37) . '...' : $row['product_name'];
        $i++;

        echo "
        <tr order_no='d$i'>
            <td order_no='d$i' style='width:50px;text-align:center;padding:0px;'>$i</td>
            <td style='background:#0279f1;color:#fff;font-weight:bold;border-bottom:1px dotted #ddd;width:63%;'>
                <i title='Delete' onclick='delete_cart(this)' cart_id='" . $row['cart_id'] . "' style='cursor:pointer;color:red' class='icon-trash'></i>&nbsp;&nbsp;
                <img alt='$image_file' style='width:30px;height:30px;border:2px solid #eee;background:#fff' src='$image_file' />
                <span>$product_name</span>
            </td>
            <td style='text-align:right;width:120px'>" . number_format($row['selling_price'], 2) . "</td>
            <td style='width:140px;padding-left:10px'>
                <div class='input-group' style='width:100px;'>
                    <span class='input-group-btn'>
                        <button class='btn btn-default btn-counter' type='button' type_data='minus' cart_id='" . $row['cart_id'] . "'><i class='icon-minus3'></i></button>
                    </span>
                    <input type='number' min='0' step='0.01' onkeydown='update_cart(this)' cart_id='" . $row['cart_id'] . "' quantity_order='" . $row['quantity_order'] . "' value='" . $row['quantity_order'] . "' style='width:70px;text-align:center' class='form-control quantity' placeholder='Quantity'>
                    <span class='input-group-btn'>
                        <button class='btn btn-default btn-counter' type_data='add' cart_id='" . $row['cart_id'] . "' type='button'><i class='icon-plus3'></i></button>
                    </span>
                </div>
            </td>
            <td style='width:50px;padding-left:5px'>(" . $row['unit'] . ")</td>
            <td style='text-align:right;width:150px;font-weight:bold;padding-right:43px;'>" . number_format($row['selling_price'] * $row['quantity_order'], 2) . "</td>
        </tr>
        ";
    }

    if ($i == 0) {
        echo '<div class="no-cart">No Product Added [POS]</div>';
    }
}


function view_cart_panda($data)
{
    require('db_connect.php');
    $products = "SELECT * FROM tbl_cart INNER JOIN tbl_menu ON tbl_cart.product_id=tbl_menu.menu_id WHERE  tbl_cart.user_id='" . $data['user_id'] . "'  AND type=1 ORDER BY cart_id ASC ";
    $result_products = $db->query($products);
    $i = 0;
    while ($row = $result_products->fetch_assoc()) {
        $image = $row['image_link'];
        if ($image != "") {
            $image_file = '../uploads/' . $image;
        } else {
            $image_file = '../images/no-image.png';
        }
        $product_name = (strlen($row['menu_name']) > 37) ? substr($row['menu_name'], 0, 37) . '...' : $row['menu_name'];
        $i++;
        echo "
                <tr order_no='d" . $i . "'>
                    <td  order_no='d" . $i . "' style='width:50px;text-align:'>" . $i . "</td>
                    <td  style='width:340px;background:#26a69a;color:#fff;font-weight:bold;border-bottom:1px dotted #ddd'><i title='Delete' onclick='delete_cart(this)' cart_id='" . $row['cart_id'] . "' style='cursor:pointer;color:#c00505' class='icon-trash '/>&nbsp;&nbsp;" . '<img alt="<?=$image_file?>"  style="width: 30px;height: 30px;border: 2px solid #eee;background:#fff" src="' . $image_file . '" /><span style="" > ' . $product_name . "</span></td>
                    <td style='text-align: center;width: 130px'><input style='width:130px;border: 1px solid #e1dfdf; height: 35px' onchange='update_price(this)' cart_id='" . $row['cart_id'] . "'  value='" . $row['sprice'] . "'   type='number' min='0.00' max='10000.00' step='0.01' ></td>
                    <td style='text-align:right;width:120px;padding-left:20px;max-width:120px;'>
                        <div class='input-group' style='margin-left: 40px;'>
                            <span class='input-group-btn'>
                                <button class='btn btn-default btn-counter' type='button' type_data='minus'  cart_id='" . $row['cart_id'] . "'  ><i class='icon-minus3'></i></button>
                            </span>
                            <input  onkeypress='return numbersonly(event)' onkeydown='update_cart(this)'  cart_id='" . $row['cart_id'] . "' quantity_order='" . $row['quantity_order'] . "'  value='" . $row['quantity_order'] . "'  style='width: 70px;text-align:center' type='text' class='form-control quantity' placeholder='Quantity'>
                            <span class='input-group-btn'>
                                <button class='btn btn-default btn-counter'  type_data='add'  cart_id='" . $row['cart_id'] . "'  type='button'><i class='icon-plus3'></i></button>
                            </span>
                        </div>
                   </td>
                    <td style='text-align: center;width: 150px;font-weight:bold'>" . number_format($row['sprice'] * $row['quantity_order'], 2) . "</td>
                <tr>
            ";
    }
    if ($i == 0) {
        echo '<div class="no-cart">No Product Added [POS]
                </div>
               
                ';
    }
}

// <div>
//                <table>
//                    <tr>
//                        <td>Customer Search <span>[ctrl+p]</span> </td>
//                    </tr>
//                </table>
//            </div>

function view_cart_update($data)
{
    require('db_connect.php');

    $user_id = mysqli_real_escape_string($db, $data['user_id']);

    $products = "SELECT * FROM tbl_cart3 
                 INNER JOIN tbl_products ON tbl_cart3.product_id = tbl_products.product_id 
                 WHERE tbl_cart3.user_id='$user_id' 
                 ORDER BY cart_id ASC";
    $result_products = $db->query($products);

    $i = 0;
    while ($row = $result_products->fetch_assoc()) {
        $image = $row['image'];
        $image_file = !empty($image) ? '../uploads/' . $image : '../images/no-image.png';
        $product_name = (strlen($row['product_name']) > 37) ? substr($row['product_name'], 0, 37) . '...' : $row['product_name'];
        $i++;

        echo "
        <tr order_no='d$i'>
            <td order_no='d$i' style='width:50px;text-align:center;'>$i</td>
            <td style='width:340px;background:#26a69a;color:#fff;font-weight:bold;border-bottom:1px dotted #ddd'>
                <img alt='$image_file' style='width:30px;height:30px;border:2px solid #eee' src='$image_file' />
                <div style='position:absolute;margin-top:-20px;margin-left:65px'>$product_name</div>
            </td>
            <td style='text-align:center;width:100px'>" . $row['unit'] . "</td>
            <td style='text-align:right;width:120px'>" . number_format($row['price'], 2) . "</td>
            <td style='text-align:right;width:120px;padding-left:20px'>
                <input onkeypress='return numbersonly(event)' onchange='update_cart3(this)' 
                       cart_id='" . $row['cart_id'] . "' quantity_order='" . $row['quantity_order'] . "' 
                       style='width:40px;text-align:center' value='" . $row['quantity_order'] . "' >
            </td>
            <td style='text-align:center;width:150px;font-weight:bold'>" . number_format($row['selling_price'] * $row['quantity_order'], 2) . "</td>
        </tr>
        ";
    }

    if ($i == 0) {
        echo '<div style="width:100%;text-align:center;padding-top:250px;padding-bottom:112px;font-size:30px;font-weight:bold;color:#26a69a">No Product Added [POS]</div>';
    }
}

function view_cart2($data)
{
    require('db_connect.php');

    $user_id = mysqli_real_escape_string($db, $data['user_id']);

    $products = "SELECT * FROM tbl_cart2 
                 INNER JOIN tbl_products ON tbl_cart2.product_id = tbl_products.product_id 
                 WHERE tbl_cart2.user_id='$user_id' 
                 ORDER BY cart_id DESC";
    $result_products = $db->query($products);

    $i = 0;
    while ($row = $result_products->fetch_assoc()) {
        $i++;
        echo "
        <tr order_no='d$i'>
            <td order_no='d$i' style='width:50px;text-align:center;'>$i</td>
            <td style='width:260px;background:#0279f1;color:#fff;font-weight:bold;border-bottom:1px dotted #ddd'>
                <i title='Delete' onclick='delete_cart(this)' cart_id='" . $row['cart_id'] . "' style='cursor:pointer;color:#c00505 !important' class='icon-trash'></i>&nbsp;&nbsp;
                " . $row['product_name'] . "
            </td>
            <td style='text-align:right;width:80px'>" . $row['unit'] . "</td>
            <td style='text-align:center;width:150px'>
                <input style='width:130px;border:1px solid #787878;' onkeydown='update_price(this)' 
                       cart_id='" . $row['cart_id'] . "' value='" . $row['price'] . "' type='number' min='0.00' step='0.01'>
            </td>
            <td style='text-align:center;width:70px;padding-left:20px'>
                <input style='border:1px solid #787878;width:40px' type='number' min='0' max='10000' step='0.01' 
                       onkeydown='update_cart(this)' cart_id='" . $row['cart_id'] . "' quantity_order='" . $row['quantity_order'] . "' 
                       value='" . $row['quantity_order'] . "'>
            </td>
            <td style='text-align:right;width:150px;font-weight:bold;padding-right:20px'>" . number_format($row['price'] * $row['quantity_order'], 2) . "</td>
        </tr>
        ";
    }

    if ($i == 0) {
        echo '<div style="width:100%;text-align:center;padding-top:250px;padding-bottom:112px;font-size:30px;font-weight:bold;color:#0052a4">No Product Added [RECEIVING]</div>';
    }
}

function delete_cart($data)
{
    require('db_connect.php');

    $cart_id = intval($data['cart_id']);
    $query = "DELETE FROM tbl_cart WHERE cart_id='$cart_id'";

    if ($db->query($query)) {
        echo "1";
    }
}

function delete_cart_update($data)
{
    require('db_connect.php');

    $cart_id = intval($data['cart_id']);
    $query = "DELETE FROM tbl_cart3 WHERE cart_id='$cart_id'";

    if ($db->query($query)) {
        echo "1";
    }
}

function delete_cart2($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $query = "DELETE FROM tbl_cart2 WHERE cart_id='$cart_id'";
    if ($db->query($query)) {
        echo "1";
    }
}

function update_cart($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $query_select = "SELECT * FROM tbl_cart 
                     INNER JOIN tbl_products ON tbl_cart.product_id = tbl_products.product_id  
                     WHERE tbl_cart.cart_id='$cart_id'";
    $result_product = $db->query($query_select);
    $datas_products = $result_product->fetch_assoc();

    if ($data['quantity_order'] <= $datas_products['quantity']) {
        $query = "UPDATE tbl_cart SET quantity_order='" . intval($data['quantity_order']) . "' WHERE cart_id='$cart_id'";
        if ($db->query($query)) {
            echo json_encode(['message' => 'ok', 'quantity_order' => $data['quantity_order']]);
        }
    } else {
        echo json_encode([
            'message' => 'not_ok',
            'product_name' => $datas_products['product_name'],
            'quantity_left' => $datas_products['quantity'],
            'quantity_order' => $data['quantity_order']
        ]);
    }
}

function update_cart3($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $query_select = "SELECT * FROM tbl_cart3 
                     INNER JOIN tbl_products ON tbl_cart3.product_id = tbl_products.product_id  
                     WHERE tbl_cart3.cart_id='$cart_id'";
    $result_product = $db->query($query_select);
    $datas_products = $result_product->fetch_assoc();

    if ($data['quantity_order'] < $datas_products['quantity']) {
        $query = "UPDATE tbl_cart3 SET quantity_order='" . intval($data['quantity_order']) . "' WHERE cart_id='$cart_id'";
        if ($db->query($query)) {
            echo json_encode(['message' => 'ok']);
        }
    } else {
        $query = "UPDATE tbl_cart3 SET quantity_order='" . intval($data['quantity_order']) . "' WHERE cart_id='$cart_id'";
        if ($db->query($query)) {
            echo json_encode([
                'message' => 'not_ok',
                'product_name' => $datas_products['product_name'],
                'quantity_left' => $datas_products['quantity'],
                'quantity_order' => $data['quantity_order']
            ]);
        }
    }
}

function update_price($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $price = floatval($data['price']);
    $query = "UPDATE tbl_cart2 SET price='$price' WHERE cart_id='$cart_id'";
    if ($db->query($query)) {
        echo json_encode(['message' => 'ok']);
    }
}

function update_price_panda($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $price = floatval($data['price']);
    $query = "UPDATE tbl_cart SET sprice='$price' WHERE cart_id='$cart_id'";
    if ($db->query($query)) {
        echo json_encode(['message' => 'ok']);
    }
}

function update_cart2($data)
{
    require('db_connect.php');
    $cart_id = intval($data['cart_id']);
    $quantity_order = intval($data['quantity_order']);
    $query = "UPDATE tbl_cart2 SET quantity_order='$quantity_order' WHERE cart_id='$cart_id'";
    if ($db->query($query)) {
        echo json_encode(['message' => 'ok']);
    }
}

function view_total($data)
{
    require('db_connect.php');


    $user_id = intval($data['user_id']);
    $other_amount = $_SESSION['other_amount'] ?? 0;

    $query_tax = "SELECT * FROM tbl_settings";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $tax_percent = $datas_tax['tax'] ?? 0;

    $products = "SELECT * FROM tbl_cart 
                 INNER JOIN tbl_products ON tbl_cart.product_id = tbl_products.product_id  
                 WHERE tbl_cart.user_id='$user_id'";
    $result_products = $db->query($products);

    $i = 0;
    $subtotal_amount = 0;
    $all_total = 0;
    $discount = 0;
    $discount_amount = 0;

    while ($row = $result_products->fetch_assoc()) {
        $i++;
        $quantity_order2 = $row['quantity_order'];
        $price2 = $row['selling_price'];
        $total = $price2 * $quantity_order2;
        $all_total += $total;
        $discount = $row['discount'] ?? 0;
        $discount_amount = ($all_total * $discount / 100);
    }

    $subtotal_amount = $all_total;
    $total_amount = $all_total - $discount_amount + $other_amount;

    $vat_amount = $subtotal_amount * ($tax_percent / 100);
    $vat_sales = $i == 0 ? 0 : $total_amount - $vat_amount;

    echo json_encode([
        'total_amount' => number_format($total_amount, 2),
        'subtotal_amount' => number_format($subtotal_amount, 2),
        'discount_percent' => $discount,
        'discount' => number_format($discount_amount, 2),
        'vat_sales' => number_format($vat_sales, 2),
        'vat_amount' => number_format($vat_amount, 2),
        'total_cart' => $i
    ]);
}

function view_total_panda($data)
{
    require('db_connect.php');

    $other_amount = 0;
    if (isset($_SESSION['other_amount'])) {
        $other_amount = $_SESSION['other_amount'];
    }

    $query_tax = "SELECT * FROM  tbl_settings ";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $tax_percent = $datas_tax['tax'];

    $products = "SELECT * FROM tbl_cart INNER JOIN tbl_menu ON tbl_cart.product_id=tbl_menu.menu_id  WHERE  tbl_cart.user_id='" . $data['user_id'] . "' ";
    $result_products = $db->query($products);
    $i = 0;
    $subtotal_amount = 0;
    $all_total = 0;
    $discount = 0;
    while ($row = $result_products->fetch_assoc()) {
        $i++;
        $quantity_order2 = $row['quantity_order'];
        $price2 = $row['sprice'];
        $total = $price2 * $quantity_order2;
        $all_total += $total;
        $discount = $row['discount'];
    }


    $subtotal_amount =  $all_total;
    $total_amount = $all_total - $discount + $other_amount;
    $vat_sales = $total_amount - ($tax_percent / 100);
    $vat_amount = $subtotal_amount * ($tax_percent / 100);
    if ($i == 0) {
        $vat_sales = number_format(0);
    } else {
        $vat_sales = $total_amount  -  $vat_amount;
    }

    $arrayName = array('total_amount' => number_format($total_amount, 2), 'subtotal_amount' => number_format($subtotal_amount, 2), 'discount' => number_format($discount, 2), 'vat_sales' => number_format($vat_sales, 2), 'vat_amount' => number_format($vat_amount, 2), 'total_cart' => $i);
    echo json_encode($arrayName);
}

function view_total_update($data)
{
    require('db_connect.php');
    $user_id = intval($data['user_id']);

    $query_tax = "SELECT * FROM tbl_settings";
    $result_query_tax = $db->query($query_tax);
    $datas_tax = $result_query_tax->fetch_assoc();
    $tax_percent = $datas_tax['tax'] ?? 0;

    $products = "SELECT * FROM tbl_cart3 
                 INNER JOIN tbl_products ON tbl_cart3.product_id = tbl_products.product_id  
                 WHERE tbl_cart3.user_id='$user_id'";
    $result_products = $db->query($products);

    $i = 0;
    $subtotal_amount = 0;
    $all_total = 0;
    $discount = 0;
    $sales_no = '';

    while ($row = $result_products->fetch_assoc()) {
        $i++;
        $quantity_order2 = $row['quantity_order'];
        $price2 = $row['selling_price'];
        $total = $price2 * $quantity_order2;
        $all_total += $total;
        $discount = $row['discount'] ?? 0;
        $sales_no = $row['sales_no'] ?? '';
    }


    $subtotal_amount = $all_total;
    $total_amount = $all_total - $discount;

    $vat_amount = $subtotal_amount * ($tax_percent / 100);
    $vat_sales = $i == 0 ? 0 : $total_amount - $vat_amount;

    echo json_encode([
        'total_amount' => number_format($total_amount, 2),
        'subtotal_amount' => number_format($subtotal_amount, 2),
        'discount' => number_format($discount, 2),
        'vat_sales' => number_format($vat_sales, 2),
        'vat_amount' => number_format($vat_amount, 2),
        'sales_no' => $sales_no,
        'total_cart' => $i
    ]);
}

function view_total2($data)
{
    require('db_connect.php');
    $user_id = intval($data['user_id']);

    $products = "SELECT * FROM tbl_cart2 
                 INNER JOIN tbl_products ON tbl_cart2.product_id = tbl_products.product_id  
                 WHERE tbl_cart2.user_id='$user_id'";
    $result_products = $db->query($products);

    $i = 0;

    $all_total = 0;
    $discount = 0;

    while ($row = $result_products->fetch_assoc()) {
        $quantity_order2 = $row['quantity_order'];
        $price2 = $row['price'];
        $total = $price2 * $quantity_order2;
        $all_total += $total;
        $discount = $row['discount'] ?? 0;
    }

    $total_amount = number_format($all_total - $discount, 2);
    $subtotal_amount = number_format($all_total, 2);

    echo json_encode([
        'total_amount' => $total_amount,
        'subtotal_amount' => $subtotal_amount,
        'discount' => number_format($discount, 2)
    ]);
}

function save_discount($data)
{
    require('db_connect.php');
    $user_id = intval($data['user_id']);
    $discount = floatval($data['discount']);

    $query = "UPDATE tbl_cart SET discount='$discount' WHERE user_id='$user_id'";
    if ($db->query($query)) {
        echo "1";
    }
}

function save_discount2($data)
{
    require('db_connect.php');
    $user_id = intval($data['user_id']);
    $discount = floatval($data['discount']);

    $query = "UPDATE tbl_cart2 SET discount='$discount' WHERE user_id='$user_id'";
    if ($db->query($query)) {
        echo "1";
    }
}

function searh_product($data)
{
    require('db_connect.php');
    $keywords = $db->real_escape_string($data['keywords'] ?? '');

    if ($keywords != "") {
        $products = "SELECT * FROM tbl_products 
                     WHERE product_name LIKE '%$keywords%' OR product_code LIKE '%$keywords%' 
                     ORDER BY product_name ASC";
    } else {
        $products = "SELECT * FROM tbl_products ORDER BY product_name ASC LIMIT 0,10";
    }

    $result_products = $db->query($products);
    $i = 0;
    echo "<ul class='ul-search'>";

    while ($row = $result_products->fetch_assoc()) {
        $i++;
        $image = $row['image'] ?: '';
        $image_file = $image ? '../uploads/' . $image : '../images/no-image.png';
        $product_name = (strlen($row['product_name']) > 25) ? substr($row['product_name'], 0, 25) . '...' : $row['product_name'];

        echo "<li title='" . $row['product_name'] . "' product_id='" . $row['product_id'] . "' product_name='" . $row['product_name'] . "' onclick='select_product(this)'>
                <div class='name-span'>
                    <img alt='" . $image_file . "' style='width:30px;height:30px;border:2px solid #eee' src='" . $image_file . "' /> &nbsp; " . ucwords($product_name) . "
                </div>
              </li>";
    }

    if ($i == 0 && $keywords != "") {
        echo "<div class='no-found'><h3>No product found!<h3></div>";
    } elseif ($keywords == "") {
        echo "<li align='center' style='cursor:default;'>Enter keywords to view more data <i class='icon-arrow-up16'></i></li>";
    }

    echo "</ul>";
}
// /////////
function searh_menu($data)
{
    require('db_connect.php');
    $keywords = $db->real_escape_string($data['keywords'] ?? '');

    if ($keywords != "") {
        $query = "SELECT * FROM tbl_menu WHERE available=1 AND menu_name LIKE '%$keywords%' ORDER BY menu_name ASC";
    } else {
        $query = "SELECT * FROM tbl_menu WHERE available=1 ORDER BY menu_name ASC LIMIT 0,10";
    }

    $result = $db->query($query);
    $i = 0;
    echo "<ul class='ul-search'>";
    while ($row = $result->fetch_assoc()) {
        $i++;
        $image = $row['image_link'] ?: '';
        $image_file = $image ? '../uploads/' . $image : '../images/no-image.png';
        $menu_name = (strlen($row['menu_name']) > 25) ? substr($row['menu_name'], 0, 25) . '...' : $row['menu_name'];

        echo "<li title='" . $row['menu_name'] . "' menu_id='" . $row['menu_id'] . "' menu_name='" . $row['menu_name'] . "' onclick='select_product(this)'>
                <div class='name-span'>
                    <img alt='" . $image_file . "' style='width:30px;height:30px;border:2px solid #eee' src='" . $image_file . "' /> &nbsp; " . ucwords($menu_name) . "
                </div>
              </li>";
    }
    echo "</ul>";

    if ($i == 0) {
        echo "<div class='no-found'><h3>No menu found!<h3></div>";
    }

    if ($keywords == "") {
        echo "<li align='center' style='cursor:default;'>Enter keywords to view more data <i class='icon-arrow-up16'></i></li>";
    }
}

function searh_customer($data)
{
    require('db_connect.php');
    $keywords = $db->real_escape_string($data['keywords_search'] ?? '');

    if ($keywords != "") {
        $query = "SELECT * FROM tbl_customer WHERE name LIKE '%$keywords%' ORDER BY name ASC";
    } else {
        $query = "SELECT * FROM tbl_customer ORDER BY name ASC";
    }

    $result = $db->query($query);
    $i = 0;
    echo "<ul class='ul-search'>";
    while ($row = $result->fetch_assoc()) {
        $i++;
        echo "<li title='" . $row['name'] . "' cust_id='" . $row['cust_id'] . "' name='" . $row['name'] . "' onclick='select_customer(this)'>
                <span class='name-span'>" . $row['name'] . "</span>
              </li>";
    }
    echo "</ul>";

    if ($i == 0) {
        echo "<div class='no-found'><h3>No customer found!<h3></div>";
    }

    if ($keywords == "") {
        echo "<li align='center' style='cursor:default;'>Enter keywords to view more data <i class='icon-arrow-up16'></i></li>";
    }
}

function searh_user($data)
{
    require('db_connect.php');
    $keywords = $db->real_escape_string($data['keywords_search'] ?? '');
    $query = "SELECT * FROM tbl_users WHERE fullname LIKE '%$keywords%' ORDER BY fullname ASC";
    $result = $db->query($query);

    $i = 0;
    echo "<ul class='ul-search'>";
    while ($row = $result->fetch_assoc()) {
        $i++;
        echo "<li title='" . $row['fullname'] . "' user_id='" . $row['user_id'] . "' name='" . $row['fullname'] . "' onclick='select_user(this)'>
                <span class='name-span'>" . $row['fullname'] . "</span>
              </li>";
    }
    echo "</ul>";

    if ($i == 0) {
        echo "<div class='no-found'><h3>No employee found!<h3></div>";
    }
}

function searh_supplier($data)
{
    require('db_connect.php');
    $keywords = $db->real_escape_string($data['keywords_search'] ?? '');
    $query = "SELECT * FROM tbl_supplier WHERE supplier_name LIKE '%$keywords%' ORDER BY supplier_name ASC";
    $result = $db->query($query);

    $i = 0;
    echo "<ul class='ul-search'>";
    while ($row = $result->fetch_assoc()) {
        $i++;
        echo "<li cust_id='" . $row['supplier_id'] . "' name='" . $row['supplier_name'] . "' onclick='select_customer(this)'>
                <span class='name-span'>" . $row['supplier_name'] . "</span>
              </li>";
    }
    echo "</ul>";

    if ($i == 0) {
        echo "<div class='no-found'><h3>No supplier found!<h3></div>";
    }
}

function save_receiving($data)
{
    require('db_connect.php');

    // Session user and supplier
    $user_id = $_SESSION['user_id'];
    $supplier_id = $_SESSION['pos-supplier'];
    $today = date("Y-m-d H:i:s");

    // Fetch all products in cart for this user
    $query_products = "SELECT * FROM tbl_cart2 
                       INNER JOIN tbl_products ON tbl_cart2.product_id=tbl_products.product_id 
                       WHERE tbl_cart2.user_id='$user_id'";
    $result_products = $db->query($query_products);

    if (!$result_products || $result_products->num_rows == 0) {
        echo "0"; // No products in cart
        return;
    }

    $subtotal_amount = 0;
    $discount_total = 0;

    // Generate unique receiving number
    $receiving_no = '120' . round(microtime(true) * 10) . $user_id;

    // First loop: calculate subtotal and total discount
    while ($row = $result_products->fetch_assoc()) {
        $quantity_order = $row['quantity_order'];
        $price = $row['price'];
        $discount = $row['discount'] ?? 0;

        $subtotal_amount += $price * $quantity_order;
        $discount_total += $discount;
    }

    $total_amount = $subtotal_amount - $discount_total;

    // -------------------- ADD SYSTEM HISTORY --------------------
    $history_data = array(
        'receiving_no' => $receiving_no,
        'user_id' => $user_id,
        'supplier_id' => $supplier_id,
        'total_amount' => $total_amount
    );
    $db->query("INSERT INTO tbl_history (date_history, details, history_type) 
                VALUES ('$today', '" . json_encode($history_data) . "', '12')"); // 12 = Receiving

    // Reset pointer to loop again for product updates
    $result_products->data_seek(0);

    while ($row = $result_products->fetch_assoc()) {
        $product_id = $row['product_id'];
        $quantity = $row['quantity'];
        $quantity_order = $row['quantity_order'];
        $price = $row['price'];

        $balance_quantity = $quantity + $quantity_order;

        // -------------------- INSERT INTO PRODUCT HISTORY --------------------
        $db->query("INSERT INTO tbl_product_history (hist_date, details, details_type, product_id, qty, balance, type)
                    VALUES ('$today', '$receiving_no', '2', '$product_id', '$quantity_order', '$balance_quantity', '2')");

        // -------------------- UPDATE PRODUCT QUANTITY --------------------
        $db->query("UPDATE tbl_products SET quantity='$balance_quantity' WHERE product_id='$product_id'");

        // -------------------- INSERT INTO RECEIVINGS TABLE --------------------
        $db->query("INSERT INTO tbl_receivings (date_received, user_id, product_id, receiving_quantity, supplier_id, receiving_price, receiving_no, discount, sub_total, total_amount)
                    VALUES ('$today', '$user_id', '$product_id', '$quantity_order', '$supplier_id', '$price', '$receiving_no', '$discount_total', '$subtotal_amount', '$total_amount')");
    }

    // -------------------- CLEAR CART --------------------
    $db->query("DELETE FROM tbl_cart2 WHERE user_id='$user_id'");

    // -------------------- CLEAR SUPPLIER SESSION --------------------
    $_SESSION['pos-supplier'] = "";
    $_SESSION['pos-supplier-name'] = "";

    echo 1; // success
}

function save_damage($data)
{
    require('db_connect.php');
    $today = date("Y-m-d H:i:s");

    $product_id = $data['product_id'];
    $quantity_damage = $data['quantity'];
    $user_id = $data['user_id'];
    $notes = $data['notes'] ?? '';

    $query_product = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
    $result_product = $db->query($query_product);
    $row = $result_product->fetch_assoc();

    if (!$row) return;

    $quantity = $row['quantity'];

    if ($quantity_damage > $quantity) {
        echo "2"; // error, not enough stock
        return;
    }

    $balance_quantity = $quantity - $quantity_damage;

    // Update product quantity
    $db->query("UPDATE tbl_products SET quantity='$balance_quantity' WHERE product_id='$product_id'");

    // Insert damage record
    $db->query("INSERT INTO tbl_damage (product_id, quantity_damage, notes, user_id, date_damage) 
                VALUES ('$product_id', '$quantity_damage', '$notes', '$user_id', '$today')");

    // Insert history
    $damage_id = $db->insert_id;
    $arrayData = array('damage_id' => $damage_id, 'product_id' => $product_id, 'user_id' => $user_id);
    $db->query("INSERT INTO tbl_history (date_history, details, history_type) VALUES ('$today', '" . json_encode($arrayData) . "', '13')");

    // Insert product history
    $db->query("INSERT INTO tbl_product_history (hist_date, details, details_type, product_id, qty, balance, type)
                VALUES ('$today', '$damage_id', '3', '$product_id', '$quantity_damage', '$balance_quantity', '1')");

    echo "1"; // success
}

function save_deduc($data)
{
    require('db_connect.php');
    $today = date("Y-m-d H:i:s");

    $product_id = $data['product_id'];
    $quantity_deduc = $data['quantity'];
    $user_id = $_SESSION['user_id'];

    $query_product = "SELECT * FROM tbl_products WHERE product_id='$product_id'";
    $result_product = $db->query($query_product);
    $row = $result_product->fetch_assoc();

    if (!$row) return;

    $quantity = $row['quantity'];

    if ($quantity_deduc > $quantity) {
        echo "2";
        return;
    }

    $balance_quantity = $quantity - $quantity_deduc;

    $db->query("UPDATE tbl_products SET quantity='$balance_quantity' WHERE product_id='$product_id'");

    echo "1";

    // Insert history
    $arrayData = array('product_id' => $product_id, 'user_id' => $user_id);
    $db->query("INSERT INTO tbl_history (date_history, details, history_type) VALUES ('$today', '" . json_encode($arrayData) . "', '27')");

    // Insert product history
    $db->query("INSERT INTO tbl_product_history (hist_date, details, details_type, product_id, qty, balance, type)
                VALUES ('$today', '', '2', '$product_id', '$quantity_deduc', '$balance_quantity', '1')");
}
function save_deduc_menu($data)
{
    require('db_connect.php');
    $today = date("Y-m-d H:i:s");
    $menu_id = $data['menu_id'];
    $quantity_damage = $data['quantity'];
    $user_id = $_SESSION['user_id'];

    $query_menu = "SELECT * FROM tbl_menu WHERE menu_id='$menu_id'";
    $result_menu = $db->query($query_menu);
    $row = $result_menu->fetch_assoc();

    if (!$row) return;

    $quantity = $row['quantity'];

    if ($quantity_damage > $quantity) {
        echo "2"; // not enough stock
        return;
    }

    $balance_quantity = $quantity - $quantity_damage;
    $db->query("UPDATE tbl_menu SET quantity='$balance_quantity' WHERE menu_id='$menu_id'");

    echo "1";

    $arrayData = array('menu_id' => $menu_id, 'user_id' => $user_id);
    $db->query("INSERT INTO tbl_history (date_history, details, history_type) VALUES ('$today', '" . json_encode($arrayData) . "', '29')");
}

function update_product($data)
{
    require('db_connect.php');
    $user_id = $_SESSION['user_id'];

    $query = "UPDATE tbl_products SET 
                field_status=0,
                product_code='" . $data['product_code'] . "',
                unit='" . $data['unit'] . "',
                product_name='" . $data['product_name'] . "',
                critical_qty='" . $data['critical_qty'] . "',
                selling_price='" . $data['selling_price'] . "',
                supplier_price='" . $data['supplier_price'] . "'
              WHERE product_id='" . $data['product_id'] . "'";

    if ($db->query($query)) {
        echo "1";
        $arrayData = array('product_id' => $data['product_id'], 'user_id' => $user_id);
        $db->query("INSERT INTO tbl_history (date_history, details, history_type) VALUES ('" . date("Y-m-d H:i:s") . "', '" . json_encode($arrayData) . "', '12')");
    }
}

function update_sales($data)
{
    require('db_connect.php');
    $user_id = $data['user_id'];
    $sales_no = $data['sales_no'];

    $query = "SELECT tbl_sales.*, tbl_products.*, tbl_users.*, tbl_customer.name AS customer_name 
              FROM tbl_sales 
              INNER JOIN tbl_products ON tbl_sales.product_id=tbl_products.product_id 
              INNER JOIN tbl_users ON tbl_sales.user_id=tbl_users.user_id 
              LEFT JOIN tbl_customer ON tbl_sales.cust_id=tbl_customer.cust_id 
              WHERE tbl_sales.sales_no='$sales_no'";

    $result = $db->query($query);

    while ($row = $result->fetch_assoc()) {
        $_SESSION['pos-customer_update'] = $row['customer_name'];
        $_SESSION['pos-custid_update'] = $row['cust_id'] ?? null;

        $insert_cart = "INSERT INTO tbl_cart3 (product_id, quantity_order, price, user_id, sales_id, sales_no, discount) 
                        VALUES ('" . $row['product_id'] . "', '" . $row['quantity_order'] . "', '" . $row['order_price'] . "', '$user_id', '" . $row['sales_id'] . "', '$sales_no', '" . $row['discount'] . "')";
        $db->query($insert_cart);
    }
}

function save_expences($data)
{
    require('db_connect.php');
    $user_id = $data['user_id'];
    $date_added = date("Y-m-d H:i:s");

    $query = "INSERT INTO tbl_expences (date_expence, description, expence_amount, notes, user_id, approve_by) 
              VALUES ('" . $data['date'] . "', '" . $data['description'] . "', '" . $data['expence_amount'] . "', '" . $data['notes'] . "', '$user_id', '" . $data['approve_by'] . "')";

    if ($db->query($query)) {
        echo "1";
        $expence_id = $db->insert_id;
        $arrayData = array('expences_id' => $expence_id, 'user_id' => $user_id);
        $db->query("INSERT INTO tbl_history (date_history, details, history_type) VALUES ('" . date("Y-m-d H:i:s") . "', '" . json_encode($arrayData) . "', '25')");
    }
}

function save_deposit($data)
{
    require('db_connect.php');

    $user_id = $_SESSION['user_id'];
    $date_added = date("Y-m-d H:i:s");

    $amount = (float)$data['amount'];
    $balance = (float)$data['balance'];
    $new_balance = max($balance - $amount, 0);

    $query = "
        INSERT INTO tbl_deposits (user_id, date_added, amount, balance)
        VALUES ('$user_id', '$date_added', '$amount', '$new_balance')
    ";

    if ($db->query($query)) {
        echo "1";

        $deposit_id = $db->insert_id;
        $arrayData = [
            'deposit_id' => $deposit_id,
            'user_id' => $user_id
        ];

        $db->query("
            INSERT INTO tbl_history (date_history, details, history_type)
            VALUES ('" . date("Y-m-d H:i:s") . "', '" . json_encode($arrayData) . "', '32')
        ");
    } else {
        echo $db->error;
    }
}




function save_panda_payment($data)
{
    require('db_connect.php'); // $db is assumed to be MySQLi connection

    $user_id = $_SESSION['user_id'];
    $amount = $data['amount'];
    $today = date("Y-m-d H:i:s");

    // Insert payment
    $query = "INSERT INTO tbl_panda_payment (user_id, amount) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("id", $user_id, $amount); // i=int, d=double
    if ($stmt->execute()) {
        echo "1";

        // Get last inserted payment ID
        $payment_id = $db->insert_id;
        $arrayData = array('payment_id' => $payment_id, 'user_id' => $user_id);

        // Insert history
        $insert_history = "INSERT INTO tbl_history (date_history, details, history_type) VALUES (?, ?, ?)";
        $stmt_history = $db->prepare($insert_history);
        $history_type = 33;
        $details_json = json_encode($arrayData);
        $stmt_history->bind_param("ssi", $today, $details_json, $history_type);
        $stmt_history->execute();
        $stmt_history->close();
    }
    $stmt->close();
}

function update_cash($data)
{
    require('db_connect.php'); // $db is MySQLi connection

    $today = isset($_SESSION['daily-report']) ? $_SESSION['daily-report'] : date("Y-m-d");
    $amount = $data['amount'];

    // Check if entry already exists for today
    $query_check = "SELECT id FROM tbl_beginning_cash WHERE DATE(cash_date) = ?";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bind_param("s", $today);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $entry = $result->fetch_assoc();
    $stmt_check->close();

    if ($entry) {
        // Update existing record
        $query_update = "UPDATE tbl_beginning_cash SET amount = ? WHERE id = ?";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bind_param("di", $amount, $entry['id']);
        if ($stmt_update->execute()) {
            echo "1";
        }
        $stmt_update->close();
    } else {
        // Insert new record
        $query_insert = "INSERT INTO tbl_beginning_cash (cash_date, amount) VALUES (?, ?)";
        $stmt_insert = $db->prepare($query_insert);
        $stmt_insert->bind_param("sd", $today, $amount);
        if ($stmt_insert->execute()) {
            echo "1";
        }
        $stmt_insert->close();
    }
}
