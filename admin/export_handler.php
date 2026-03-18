<?php
require_once '../db_connect.php';
require_once '../vendor/autoload.php';

// Create exports directory if it doesn't exist
if (!file_exists('exports')) {
    mkdir('exports', 0777, true);
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
                $zip_filename = exportAllData($db);
                downloadFile($zip_filename, 'all_data_export.zip');
                break;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

function downloadFile($filepath, $filename) {
    if (file_exists($filepath)) {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
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
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        header('Connection: close');
        
        readfile($filepath);
        unlink($filepath); // Delete temporary file
        exit;
    } else {
        throw new Exception("File not found: $filepath");
    }
}

function exportProducts($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Check which columns exist in the table
    $columns_to_export = [];
    $headers_to_export = [];
    
    // Define all possible columns and their headers
    $possible_columns = [
        'product_id' => 'Product ID',
        'cat_id' => 'Category ID', 
        'product_code' => 'Product Code',
        'product_name' => 'Product Name',
        'quantity' => 'Quantity',
        'selling_price' => 'Selling Price',
        'supplier_price' => 'Supplier Price',
        'critical_qty' => 'Critical Qty',
        'unit' => 'Unit',
        'image' => 'Image',
        'field_status' => 'Field Status'
    ];
    
    // Check which columns actually exist
    $result = $db->query("SHOW COLUMNS FROM tbl_products");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build headers and column lists based on existing columns
    foreach ($possible_columns as $col => $header) {
        if (in_array($col, $existing_columns)) {
            $columns_to_export[] = $col;
            $headers_to_export[] = $header;
        }
    }
    
    // Set headers
    $sheet->fromArray($headers_to_export, null, 'A1');
    
    // Style headers
    $last_col = chr(ord('A') + count($headers_to_export) - 1);
    $sheet->getStyle('A1:' . $last_col . '1')->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $last_col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get data from database
    $query = "SELECT " . implode(', ', $columns_to_export) . " FROM tbl_products ORDER BY product_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $row_data = [];
        foreach ($columns_to_export as $col) {
            $row_data[] = $data[$col];
        }
        $sheet->fromArray($row_data, null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', $last_col) as $col) {
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
    
    // Check which columns exist in the table
    $columns_to_export = [];
    $headers_to_export = [];
    
    // Define all possible columns and their headers
    $possible_columns = [
        'member_id' => 'Member ID', 
        'user_id' => 'User ID',
        'cust_id' => 'Customer ID',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'middle_name' => 'Middle Name',
        'gender' => 'Gender',
        'birthdate' => 'Birthdate',
        'phone' => 'Phone',
        'email' => 'Email',
        'address' => 'Address',
        'type' => 'Type',
        'membership_date' => 'Membership Date'
    ];
    
   
    $result = $db->query("SHOW COLUMNS FROM tbl_members");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    
    foreach ($possible_columns as $col => $header) {
        if (in_array($col, $existing_columns)) {
            $columns_to_export[] = $col;
            $headers_to_export[] = $header;
        }
    }
    
    
    $sheet->fromArray($headers_to_export, null, 'A1');
    
   
    $last_col = chr(ord('A') + count($headers_to_export) - 1);
    $sheet->getStyle('A1:' . $last_col . '1')->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $last_col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
   
    $query = "SELECT " . implode(', ', $columns_to_export) . " FROM tbl_members ORDER BY member_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $row_data = [];
        foreach ($columns_to_export as $col) {
            $row_data[] = $data[$col];
        }
        $sheet->fromArray($row_data, null, 'A' . $row);
        $row++;
    }
    
   
    foreach (range('A', $last_col) as $col) {
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
    
   
    $columns_to_export = [];
    $headers_to_export = [];
    
   
    $possible_columns = [
        'cust_id' => 'Customer ID',
        'name' => 'Name',
        'address' => 'Address',
        'contact' => 'Contact'
    ];
    
    
    $result = $db->query("SHOW COLUMNS FROM tbl_customer");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build headers and column lists based on existing columns
    foreach ($possible_columns as $col => $header) {
        if (in_array($col, $existing_columns)) {
            $columns_to_export[] = $col;
            $headers_to_export[] = $header;
        }
    }
    
    // Set headers
    $sheet->fromArray($headers_to_export, null, 'A1');
    
    // Style headers
    $last_col = chr(ord('A') + count($headers_to_export) - 1);
    $sheet->getStyle('A1:' . $last_col . '1')->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $last_col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get data from database
    $query = "SELECT" . implode(', ', $columns_to_export) . " FROM tbl_customer ORDER BY cust_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $row_data = [];
        foreach ($columns_to_export as $col) {
            $row_data[] = $data[$col];
        }
        $sheet->fromArray($row_data, null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', $last_col) as $col) {
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
    
    // Get data from database
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
    
    // Get data from database
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
    
    // Check which columns exist in the table
    $columns_to_export = [];
    $headers_to_export = [];
    
    // Define all possible columns and their headers
    $possible_columns = [
        'supplier_id' => 'Supplier ID',
        'supplier_name' => 'Supplier Name',
        'supplier_contact' => 'Contact',
        'supplier_address' => 'Address'
    ];
    
    // Check which columns actually exist
    $result = $db->query("SHOW COLUMNS FROM tbl_supplier");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build headers and column lists based on existing columns
    foreach ($possible_columns as $col => $header) {
        if (in_array($col, $existing_columns)) {
            $columns_to_export[] = $col;
            $headers_to_export[] = $header;
        }
    }
    
    // Set headers
    $sheet->fromArray($headers_to_export, null, 'A1');
    
    // Style headers
    $last_col = chr(ord('A') + count($headers_to_export) - 1);
    $sheet->getStyle('A1:' . $last_col . '1')->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $last_col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get data from database
    $query = "SELECT " . implode(', ', $columns_to_export) . " FROM tbl_supplier ORDER BY supplier_id";
    $result = $db->query($query);
    
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $row_data = [];
        foreach ($columns_to_export as $col) {
            $row_data[] = $data[$col];
        }
        $sheet->fromArray($row_data, null, 'A' . $row);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', $last_col) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'exports/suppliers_' . date('Y-m-d_H-i-s') . '.xlsx';
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
    
    // Get data from database
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

function exportCapitalShare($db) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['Transaction ID', 'Account ID', 'Account Number', 'Member Name', 'Amount', 'Reference No', 'Transaction Date', 'Remarks'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    
    // Get data from database
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
    
    // Get data from database
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
        
        // Get total capital share for this member
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
        
        // Get total savings for this member
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
    
    $filename = 'exports/client_balance_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    return $filename;
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
?>
