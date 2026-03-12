<?php require('includes/header.php'); ?>
<?php
require_once '../db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';

// Create exports directory if it doesn't exist
if (!file_exists('exports')) {
    mkdir('exports', 0777, true);
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$message = '';
$export_files = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export_type'])) {
    try {
        $export_type = $_POST['export_type'];
        
        switch ($export_type) {
            case 'products':
                $filename = exportProducts($db);
                downloadFile($filename, 'products_actual_data.xlsx');
                break;
            case 'members':
                $filename = exportMembers($db);
                downloadFile($filename, 'members_actual_data.xlsx');
                break;
            case 'customers':
                $filename = exportCustomers($db);
                downloadFile($filename, 'customers_actual_data.xlsx');
                break;
            case 'sales':
                $filename = exportSales($db);
                downloadFile($filename, 'sales_actual_data.xlsx');
                break;
            case 'expenses':
                $filename = exportExpenses($db);
                downloadFile($filename, 'expenses_actual_data.xlsx');
                break;
            case 'suppliers':
                $filename = exportSuppliers($db);
                downloadFile($filename, 'suppliers_actual_data.xlsx');
                break;
            case 'receivings':
                $filename = exportReceivings($db);
                downloadFile($filename, 'receivings_actual_data.xlsx');
                break;
            case 'capital_share':
                $filename = exportCapitalShare($db);
                downloadFile($filename, 'capital_share_actual_data.xlsx');
                break;
            case 'deposits':
                $filename = exportDeposits($db);
                downloadFile($filename, 'deposits_actual_data.xlsx');
                break;
            case 'client_balance':
                $filename = exportClientBalance($db);
                downloadFile($filename, basename($filename));
                break;
            case 'all':
                // For "all" export, create a zip file
                $zip_filename = exportAllData($db);
                downloadFile($zip_filename, 'all_data_export.zip');
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

function downloadFile($filepath, $filename) {
    if (file_exists($filepath)) {
        // Determine content type based on file extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $content_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        
        if ($extension === 'zip') {
            $content_type = 'application/zip';
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath); // Delete temporary file
        exit;
    } else {
        throw new Exception("File not found: $filepath");
    }
}

function exportAllData($db) {
    // Create all individual files
    $files = [
        exportProducts($db),
        exportMembers($db),
        exportCustomers($db),
        exportSales($db),
        exportExpenses($db),
        exportSuppliers($db),
        exportReceivings($db),
        exportCapitalShare($db),
        exportDeposits($db),
        exportClientBalance($db)
    ];
    
    // Create zip file
    $zip_filename = 'exports/all_data_' . date('Y-m-d_H-i-s') . '.zip';
    $zip = new ZipArchive();
    
    if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
        
        // Delete individual files after adding to zip
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        return $zip_filename;
    } else {
        throw new Exception("Cannot create zip file");
    }
}

function exportProducts($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers - matching actual database structure
    $headers = ['Product ID', 'Category ID', 'Product Code', 'Product Name', 'Quantity', 'Selling Price', 'Supplier Price', 'Critical Qty', 'Unit', 'Image', 'Field Status', 'Created At'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:L1')->getFont()->setBold(true);
    $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database - using correct column names
    $query = "SELECT product_id, cat_id, product_code, product_name, quantity, selling_price, supplier_price, critical_qty, unit, image, field_status, created_at 
              FROM tbl_products ORDER BY product_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['product_id'],
            $data['cat_id'],
            $data['product_code'],
            $data['product_name'],
            $data['quantity'],
            $data['selling_price'],
            $data['supplier_price'],
            $data['critical_qty'],
            $data['unit'],
            $data['image'],
            $data['field_status'],
            $data['created_at']
        ], null, 'A' . $row);
        $row++;
    }
                
    // Auto-size columns
    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/products_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportMembers($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['First Name', 'Last Name', 'Middle Name', 'Gender', 'Birthdate', 'Phone', 'Email', 'Address', 'Type', 'Membership Date'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT first_name, last_name, middle_name, gender, birthdate, phone, email, address, type, membership_date 
              FROM tbl_members ORDER BY member_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['gender'],
            $data['birthdate'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['type'],
            $data['membership_date']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/members_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportCustomers($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Customer ID', 'Name', 'Address', 'Contact', 'Created At'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT cust_id, name, address, contact, created_at 
              FROM tbl_customer ORDER BY cust_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['cust_id'],
            $data['name'],
            $data['address'],
            $data['contact'],
            $data['created_at']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/customers_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportSales($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Sales ID', 'Sales No', 'Customer ID', 'Product ID', 'Quantity', 'Order Price', 'Total Amount', 'Sales Date', 'Discount %', 'Discount'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT sales_id, sales_no, cust_id, product_id, quantity_order, order_price, total_amount, sales_date, discount_percent, discount 
              FROM tbl_sales ORDER BY sales_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['sales_id'],
            $data['sales_no'],
            $data['cust_id'],
            $data['product_id'],
            $data['quantity_order'],
            $data['order_price'],
            $data['total_amount'],
            $data['sales_date'],
            $data['discount_percent'],
            $data['discount']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/sales_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportExpenses($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Expense ID', 'Description', 'Amount', 'Date', 'Notes', 'Approved By'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT expences_id, description, expence_amount, date_expence, notes, approve_by 
              FROM tbl_expences ORDER BY expences_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['expences_id'],
            $data['description'],
            $data['expence_amount'],
            $data['date_expence'],
            $data['notes'],
            $data['approve_by']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/expenses_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportSuppliers($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Supplier ID', 'Supplier Name', 'Contact', 'Address', 'Created At'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT supplier_id, supplier_name, supplier_contact, supplier_address, created_at 
              FROM tbl_supplier ORDER BY supplier_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['supplier_id'],
            $data['supplier_name'],
            $data['supplier_contact'],
            $data['supplier_address'],
            $data['created_at']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/suppliers_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportCapitalShare($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Transaction ID', 'Account ID', 'Account Number', 'Member Name', 'Amount', 'Reference No', 'Transaction Date', 'Remarks'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    

    $query = "SELECT t.transaction_id, t.account_id, a.account_number, 
              CONCAT(m.first_name, ' ', m.last_name) as member_name, 
              t.amount, t.reference_no, t.transaction_date, t.remarks
              FROM transactions t
              LEFT JOIN accounts a ON t.account_id = a.account_id
              LEFT JOIN tbl_members m ON a.member_id = m.member_id
              WHERE t.transaction_type_id = 3 AND t.status = 'active'
              ORDER BY t.transaction_date DESC";
    $result = $db->query($query);
    
    $row = 2;
    $total_amount = 0;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['transaction_id'],
            $data['account_id'],
            $data['account_number'],
            $data['member_name'],
            $data['amount'],
            $data['reference_no'],
            $data['transaction_date'],
            $data['remarks']
        ], null, 'A' . $row);
        $total_amount += $data['amount'];
        $row++;
    }
    
    // Add total row
    $sheet->fromArray(['', '', '', 'TOTAL', $total_amount, '', '', ''], null, 'A' . $row);
    $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
    $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5CC');
    
    // Auto-size columns
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/capital_share_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportDeposits($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Transaction ID', 'Account ID', 'Account Number', 'Member Name', 'Amount', 'Reference No', 'Transaction Date', 'Remarks'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database - savings/deposit transactions
    $query = "SELECT t.transaction_id, t.account_id, a.account_number, 
              CONCAT(m.first_name, ' ', m.last_name) as member_name, 
              t.amount, t.reference_no, t.transaction_date, t.remarks
              FROM transactions t
              LEFT JOIN accounts a ON t.account_id = a.account_id
              LEFT JOIN tbl_members m ON a.member_id = m.member_id
              WHERE t.transaction_type_id = 1 AND t.status = 'active'
              ORDER BY t.transaction_date DESC";
    $result = $db->query($query);
    
    $row = 2;
    $total_amount = 0;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['transaction_id'],
            $data['account_id'],
            $data['account_number'],
            $data['member_name'],
            $data['amount'],
            $data['reference_no'],
            $data['transaction_date'],
            $data['remarks']
        ], null, 'A' . $row);
        $total_amount += $data['amount'];
        $row++;
    }
    
    // Add total row
    $sheet->fromArray(['', '', '', 'TOTAL', $total_amount, '', '', ''], null, 'A' . $row);
    $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
    $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5CC');
    
    // Auto-size columns
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/deposits_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportClientBalance($db) {
    // Create exports directory if it doesn't exist
    if (!file_exists('exports')) {
        mkdir('exports', 0777, true);
    }
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Member ID', 'Member Name', 'Address', 'Phone', 'Member Type', 'Total Capital Share', 'Total Savings Balance', 'Total Balance'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get all members
    $query = "SELECT m.member_id, CONCAT(m.first_name, ' ', m.last_name) as member_name, 
              m.address, m.phone, m.type, m.status
              FROM tbl_members m
              WHERE m.status = 'active'
              ORDER BY m.first_name, m.last_name";
    $result = $db->query($query);
    
    $row = 2;
    $grand_total_capital = 0;
    $grand_total_savings = 0;
    $grand_total_balance = 0;
    
    while ($member = $result->fetch_assoc()) {
        $member_id = $member['member_id'];
        
        // Get total capital share for this member (matching customer_history.php)
        $total_capital = 0;
        $capital_result = $db->query("
            SELECT SUM(t.amount) AS total_capital
            FROM transactions t
            INNER JOIN accounts a ON a.account_id = t.account_id
            INNER JOIN account_types at ON at.account_type_id = a.account_type_id
            WHERE a.member_id = $member_id
            AND at.type_name = 'capital_share'
            AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE)
        ");
        if ($capital_result && $capital_result->num_rows > 0) {
            $capital_data = $capital_result->fetch_assoc();
            $total_capital = $capital_data['total_capital'] ?? 0;
        }
        
        // Get total savings for this member (matching customer_history.php)
        $total_savings = 0;
        $savings_result = $db->query("
            SELECT SUM(t.amount) AS total
            FROM transactions t
            INNER JOIN accounts a ON a.account_id = t.account_id
            INNER JOIN account_types at ON at.account_type_id = a.account_type_id
            WHERE a.member_id = $member_id
            AND at.type_name = 'savings'
            AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE)
        ");
        if ($savings_result && $savings_result->num_rows > 0) {
            $savings_data = $savings_result->fetch_assoc();
            $total_savings = $savings_data['total'] ?? 0;
        }
        
        // Calculate total balance
        $total_balance = $total_capital + $total_savings;
        
        $sheet->fromArray([
            $member['member_id'],
            $member['member_name'],
            $member['address'],
            $member['phone'],
            ucfirst($member['type']),
            $total_capital,
            $total_savings,
            $total_balance
        ], null, 'A' . $row);
        
        $grand_total_capital += $total_capital;
        $grand_total_savings += $total_savings;
        $grand_total_balance += $total_balance;
        
        $row++;
    }
    
    // Add grand total row
    $sheet->fromArray(['', 'GRAND TOTAL', '', '', '', $grand_total_capital, $grand_total_savings, $grand_total_balance], null, 'A' . $row);
    $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
    $sheet->getStyle('F' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5CC');
    
    // Auto-size columns
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generate unique filename to avoid conflicts
    $filename = 'exports/client_balance_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}

function exportReceivings($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Receiving ID', 'Receiving No', 'Product ID', 'Supplier ID', 'Quantity', 'Price', 'Discount', 'Total Amount', 'Date Received'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get actual data from database
    $query = "SELECT receiving_id, receiving_no, product_id, supplier_id, receiving_quantity, receiving_price, discount, total_amount, date_received 
              FROM tbl_receivings ORDER BY receiving_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->fromArray([
            $data['receiving_id'],
            $data['receiving_no'],
            $data['product_id'],
            $data['supplier_id'],
            $data['receiving_quantity'],
            $data['receiving_price'],
            $data['discount'],
            $data['total_amount'],
            $data['date_received']
        ], null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/receivings_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
}
?>

<style>
.export-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.export-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    margin: 5px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.export-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
    text-align: center;
}

.export-card h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.export-card p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
}

.file-list {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
}

.file-list h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.file-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.file-list li {
    padding: 5px 0;
    border-bottom: 1px solid #ddd;
}

.file-list li:last-child {
    border-bottom: none;
}

.file-list a {
    color: #007bff;
    text-decoration: none;
}

.file-list a:hover {
    text-decoration: underline;
}
</style>

<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="page-header page-header-default">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-download2 position-left"></i> <span class="text-semibold">Export Database Data</span></h4>
                    </div>
                </div>
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                        <li class="active"><i class="icon-download2 position-left"></i> Export Data</li>
                    </ul>
                </div>
            </div>
            
            <div class="content">
                <div class="export-container">
                    <h2><i class="icon-database"></i> Export Your Actual Database Data</h2>
                    <p>Download your real data from the database as Excel files. Choose specific tables or export everything at once.</p>
                    
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="export-form" action="export_handler.php" target="_blank">
                        <div class="form-group">
                            <label for="export_type">Select what to export:</label>
                            <select name="export_type" id="export_type" class="form-control" required>
                                <option value="">Choose export type...</option>
                                <option value="products">Products</option>
                                <option value="members">Members</option>
                                <option value="customers">Customers</option>
                                <option value="sales">Sales</option>
                                <option value="expenses">Expenses</option>
                                <option value="suppliers">Suppliers</option>
                                <option value="receivings">Receivings (Stock In)</option>
                                <option value="capital_share">💰 Capital Share</option>
                                <option value="deposits">💳 Deposits (Savings)</option>
                                <option value="client_balance">📊 Client Balance Summary</option>
                                <option value="all">🎉 Export All Data</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-download"></i> Export Data
                        </button>
                    </form>
                    
                    <?php if (!empty($export_files)): ?>
                    <div class="file-list">
                        <h4><i class="icon-file-excel"></i> Download Your Exported Files:</h4>
                        <ul>
                            <?php foreach ($export_files as $file): ?>
                            <li>
                                <a href="<?php echo $file; ?>" download>
                                    <i class="icon-download"></i> <?php echo basename($file); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="export-grid">
                        <div class="export-card">
                            <h4>Products</h4>
                            <p>All product information, prices, and inventory data</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="products">
                                <button type="submit" class="btn btn-success">Export Products</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Members</h4>
                            <p>Complete member database with contact details</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="members">
                                <button type="submit" class="btn btn-success">Export Members</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Customers</h4>
                            <p>Customer information and contact details</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="customers">
                                <button type="submit" class="btn btn-success">Export Customers</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Sales</h4>
                            <p>All sales transactions and order history</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="sales">
                                <button type="submit" class="btn btn-success">Export Sales</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Expenses</h4>
                            <p>Expense records and financial data</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="expenses">
                                <button type="submit" class="btn btn-success">Export Expenses</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Suppliers</h4>
                            <p>Supplier information and contact data</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="suppliers">
                                <button type="submit" class="btn btn-success">Export Suppliers</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>Receivings</h4>
                            <p>Stock-in records and inventory movements</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="receivings">
                                <button type="submit" class="btn btn-success">Export Receivings</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>💰 Capital Share</h4>
                            <p>Client capital share contributions with totals</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="capital_share">
                                <button type="submit" class="btn btn-success">Export Capital Share</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>💳 Deposits (Savings)</h4>
                            <p>All deposit transactions and balances</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="deposits">
                                <button type="submit" class="btn btn-success">Export Deposits</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>📊 Client Balance Summary</h4>
                            <p>Complete client balance report with capital + savings</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="client_balance">
                                <button type="submit" class="btn btn-success">Export Balance Summary</button>
                            </form>
                        </div>
                        
                        <div class="export-card">
                            <h4>🎉 Export All</h4>
                            <p>Download complete database backup</p>
                            <form method="POST" action="export_handler.php" target="_blank">
                                <input type="hidden" name="export_type" value="all">
                                <button type="submit" class="btn btn-warning">Export Everything</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require('includes/footer-text.php'); ?>
<?php require('includes/footer.php'); ?>
