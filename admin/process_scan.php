<?php
require 'db_connect.php'; // Your SQLite3 DB connection

if (!isset($_GET['code'])) {
    echo "No barcode scanned!";
    exit;
}

$code = $_GET['code'];

// Fetch product info from SQLite3
$stmt = $db->prepare("SELECT * FROM tbl_products WHERE product_code = :code");
$stmt->bind_param(':code', $code, );
$result = $stmt->execute();
$product = $result->fetch_assoc();

if ($product) {
    echo "<h2>Product Details</h2>";
    echo "Product Name: " . htmlspecialchars($product['product_name']) . "<br>";
    echo "Product Code: " . htmlspecialchars($product['product_code']) . "<br>";
    echo "Price: " . number_format($product['selling_price'], 2) . "<br>";

    if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])) {
        echo "<img src='../uploads/" . htmlspecialchars($product['image']) . "' width='150'>";
    }
} else {
    echo "Product not found for barcode: " . htmlspecialchars($code);
}
