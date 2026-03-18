<?php require('includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../db_connect.php');

$message = '';
$imported_count = 0;
$error_count = 0;
$error_details = []; // Track specific error details

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    require_once '../vendor/autoload.php'; 
    
    try {
        $file = $_FILES['excel_file'];
        $file_tmp = $file['tmp_name'];
        $file_name = $file['name'];
        
        // Validate file type
        $allowed_extensions = ['xlsx', 'xls', 'csv'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Please upload a valid Excel file (.xlsx, .xls, or .csv)");
        }
        
        // Load the spreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $highest_row = $worksheet->getHighestRow();
        $highest_column = $worksheet->getHighestColumn();
        
        // Get import type and table mapping
        $import_type = $_POST['import_type'];
        $table_mapping = getTableMapping($import_type);
        
        // Start transaction
        $db->begin_transaction();   
        
        // Disable foreign key checks for member import
        if ($import_type === 'members') {
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
        }
        
        // Process each row
        for ($row = 2; $row <= $highest_row; $row++) { // Start from row 2 (skip headers)
            $data = [];
            
            // Read each column based on mapping
            foreach ($table_mapping['columns'] as $excel_col => $db_col) {
                $cell_value = $worksheet->getCell($excel_col . $row)->getValue();
                $data[$db_col] = trim($cell_value ?? '');   
            }
            
            // Debug: Log the data for problematic rows
            if ($row >= 3 && $row <= 6) {
                error_log("Row $row data: " . json_encode($data));
            }
            
            // Skip completely empty rows
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Validate required fields
            $validation_result = validateRequiredFields($data, $table_mapping['required']);
            if (!$validation_result) {
                $error_count++;
                $missing_fields = [];
                foreach ($table_mapping['required'] as $field) {
                    if (!isset($data[$field]) || empty(trim($data[$field]))) {
                        $missing_fields[] = $field;
                    }
                }
                $error_details[] = "Row $row: Missing required fields - " . implode(', ', $missing_fields);
                continue;
            }
            
            // Insert data
            if (insertData($db, $table_mapping['table'], $data, $table_mapping['data_types'])) {
                $imported_count++;
            } else {
                $error_count++;
                $error_details[] = "Row $row: Database insertion failed";
            }
        }
        
        $db->commit();
        
        // Re-enable foreign key checks
        if ($import_type === 'members') {
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        
        $message = "Import completed! Successfully imported: $imported_count records. Errors: $error_count records.";
        
        if (!empty($error_details)) {
            $message .= "<br><strong>Error Details:</strong><br>" . implode('<br>', array_slice($error_details, 0, 5));
            if (count($error_details) > 5) {
                $message .= "<br>...and " . (count($error_details) - 5) . " more errors.";
            }
        }
        
    } catch (Exception $e) {
        $db->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

function getTableMapping($import_type) {
    $mappings = [
        'products' => [
            'table' => 'tbl_products',
            'columns' => [
                'A' => 'product_id',
                'B' => 'cat_id',
                'C' => 'product_code',
                'D' => 'product_name',
                'E' => 'quantity',
                'F' => 'selling_price',
                'G' => 'supplier_price',
                'H' => 'critical_qty',
                'I' => 'unit',
                'J' => 'image',
                'K' => 'field_status',
                'L' => 'created_at'
            ],
            'required' => ['product_name', 'selling_price', 'product_code'],
            'data_types' => [
                'product_id' => 'int',
                'cat_id' => 'int',
                'quantity' => 'int',
                'selling_price' => 'decimal',       
                'supplier_price' => 'decimal',
                'critical_qty' => 'int',
                'field_status' => 'int'
            ]
        ],
        'members' => [
            'table' => 'tbl_members',
            'columns' => [
                'A' => 'first_name',
                'B' => 'last_name',
                'C' => 'middle_name',
                'D' => 'gender',
                'E' => 'phone',
                'F' => 'address',
                'G' => 'email',
                'H' => 'tin'
            ],
            'required' => ['first_name', 'last_name'],
            'data_types' => [],
            'optional_fields' => ['user_id', 'membership_date'  ] 
           
        ],
        'customers' => [
            'table' => 'tbl_customer',
            'columns' => [
                'A' => 'name',
                'B' => 'address',
                'C' => 'contact'
            ],
            'required' => ['name'],
            'data_types' => []
        ],
        'sales' => [
            'table' => 'tbl_sales',
            'columns' => [
                'A' => 'cust_id',
                'B' => 'product_id',
                'C' => 'quantity_order',
                'D' => 'order_price',
                'E' => 'total_amount',
                'F' => 'sales_date',
                'G' => 'discount_percent',
                'H' => 'discount'
            ],
            'required' => ['cust_id', 'product_id', 'quantity_order', 'total_amount'],
            'data_types' => [
                'cust_id' => 'int',
                'product_id' => 'int',
                'quantity_order' => 'decimal',
                'order_price' => 'decimal',
                'total_amount' => 'decimal',
                'discount_percent' => 'int',
                'discount' => 'decimal'
            ]
        ],
        'expenses' => [
            'table' => 'tbl_expences',
            'columns' => [
                'A' => 'description',
                'B' => 'expence_amount',
                'C' => 'date_expence',
                'D' => 'notes'
            ],
            'required' => ['description', 'expence_amount'],
            'data_types' => [
                'expence_amount' => 'decimal'
            ]
        ],
        'suppliers' => [
            'table' => 'tbl_supplier',
            'columns' => [
                'A' => 'supplier_name',
                'B' => 'supplier_contact',
                'C' => 'supplier_address'
            ],
            'required' => ['supplier_name'],
            'data_types' => []
        ],
        'receivings' => [
            'table' => 'tbl_receivings',
            'columns' => [
                'A' => 'product_id',
                'B' => 'supplier_id',
                'C' => 'receiving_quantity',
                'D' => 'receiving_price',
                'E' => 'discount',
                'F' => 'total_amount',
                'G' => 'date_received'
            ],
            'required' => ['product_id', 'supplier_id', 'receiving_quantity', 'receiving_price'],
            'data_types' => [
                'product_id' => 'int',
                'supplier_id' => 'int',
                'receiving_quantity' => 'int',
                'receiving_price' => 'decimal',
                'discount' => 'decimal',
                'total_amount' => 'decimal'
            ]
        ]
    ];
    
    return $mappings[$import_type] ?? null;
}

function validateRequiredFields($data, $required_fields) {
    $missing = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    // Return true if no missing fields, false otherwise
    return empty($missing);
}

function insertData($db, $table, $data, $data_types) {
    // For members table, handle user_id as optional (set to 0 temporarily)
    if ($table === 'tbl_members') {
        if (!isset($data['user_id'])) {
            $data['user_id'] = 0; // Temporary value, will be updated when user is created
        }
        
        // Normalize gender to match ENUM values
        if (isset($data['gender']) && !empty($data['gender'])) {
            $gender = strtolower(trim($data['gender']));
            if (in_array($gender, ['male', 'm'])) {
                $data['gender'] = 'Male';
            } elseif (in_array($gender, ['female', 'f'])) {
                $data['gender'] = 'Female';
            } else {
                $data['gender'] = 'Other'; // Default to Other for unrecognized values
            }
        } else {
            // Set gender to NULL if not provided (since column allows NULL)
            $data['gender'] = null;
        }
        
        // Set default values for required fields if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        if (!isset($data['type'])) {
            $data['type'] = 'regular';
        }
        if (!isset($data['membership_date'])) {
            $data['membership_date'] = date('Y-m-d H:i:s');
        }
    }
    
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    // Build the query
    $query = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    if (!$stmt) {
        return false;
    }
    
    // Bind parameters with proper types
    $types = '';
    $params = [];
    
    foreach ($columns as $col) {
        $type = $data_types[$col] ?? 'str';
        
        switch ($type) {
            case 'int':
                $types .= 'i';
                $params[] = (int)$data[$col];
                break;
            case 'decimal':
                $types .= 'd';
                $params[] = (float)$data[$col];
                break;
            default:
                $types .= 's';
                $params[] = $data[$col];
        }
    }
    
    // Bind parameters
    $stmt->bind_param($types, ...$params);
    
    return $stmt->execute();
}
?>

<style>
.navbar-brand {
    display: flex;
    align-items: center;
    font-weight: 800;
    color: white;
    text-decoration: none;
    font-size: 16px;
    line-height: 1.2;
}

.navbar-brand img {
    height: 40px;
    width: auto;
    margin-right: 12px;
    border-radius: 20px;
}

.navbar-brand span {
    white-space: nowrap;
}

.import-container {
    background: white;
    padding: 30px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
    border: 1px solid #ddd;
}

.import-form {
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
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 14px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #26a69a;
    box-shadow: 0 0 0 0.2rem rgba(38, 166, 154, 0.25);
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
    color: white;
}

.btn-teal-400 {
    background: #26a69a;
    border-color: #26a69a;
    color: white;
}

.btn-teal-400:hover {
    background: #26a69a;
    border-color: #26a69a;
    color: white;
    opacity: 0.9;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 3px;
    border: 1px solid transparent;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.template-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    margin: 20px 0;
    border: 1px solid #ddd;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.template-card {
    background: white;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #ddd;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.template-card h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.template-card p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
}

.file-upload {
    border: 2px dashed #ddd;
    padding: 40px;
    text-align: center;
    border-radius: 4px;
    margin: 20px 0;
    transition: border-color 0.3s;
    background: #fafafa;
}

.file-upload:hover {
    border-color: #26a69a;
}

.file-upload.dragover {
    border-color: #26a69a;
    background: #f8f9ff;
}

.panel {
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

.panel-white {
    background: white;
}

.panel-heading {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 4px 4px 0 0;
}

.panel-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.panel-body {
    padding: 20px;
}

.border-top-teal-400 {
    border-top-color: #26a69a !important;
}

.text-teal-400 {
    color: #26a69a !important;
}

.border-top-xlg {
    border-top-width: 4px !important;
}
</style>

<body class="layout-boxed navbar-top">
   <div class="navbar navbar-inverse bg-primary navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php"><img src="../images/main_logo.jpg" alt=""><span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span></a>
        </div>
        <div class="navbar-collapse collapse">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">
                <div class="page-header page-header-default">
                    <div class="page-header-content">
                        <div class="page-title">
                            <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Dashboard</span> - Excel Import</h4>
                        </div>
                    </div>
                    <div class="breadcrumb-line">
                        <ul class="breadcrumb">
                            <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                            <li><a href="export_data.php"><i class="icon-upload position-left"></i>Export Data</a></li>
                            <li class="active"><i class="icon-file-excel"></i> Excel Import</li>
                        </ul>
                    </div>
                </div>
                
                <div class="content">
                    <div class="panel panel-white border-top-xlg border-top-teal-400">
                        <div class="panel-heading">
                            <h6 class="panel-title text-teal-400"><i class="icon-file-excel"></i> Import Data from Excel</h6>
                        </div>
                        <div class="panel-body">
                            <p>Upload your Excel file to import data into the system. Make sure your data matches the required format.</p>
                            
                            <?php if ($message): ?>
                            <div class="alert alert-<?php echo $error_count > 0 ? 'danger' : 'success'; ?>">
                                <?php echo $message; ?>
                            </div>
                            <?php endif;?>
                            
                            <form method="POST" enctype="multipart/form-data" class="import-form">
                                <div class="form-group">
                                    <label for="import_type">What type of data are you importing?</label>
                                    <select name="import_type" id="import_type" class="form-control" required>
                                        <option value="">Select data type...</option>
                                        <option value="products">Products</option>
                                        <option value="members">Members</option>
                                        <option value="customers">Customers</option>
                                        <option value="sales">Sales</option>
                                        <option value="expenses">Expenses</option>
                                        <option value="suppliers">Suppliers</option>
                                        <option value="receivings">Receivings (Stock In)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="excel_file">Choose Excel File</label>
                                    <div class="file-upload" id="fileDropZone">
                                        <i class="icon-file-excel" style="font-size: 48px; color: #28a745;"></i>
                                        <p>Drag & drop your Excel file here or click to browse</p>
                                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required style="display: none;">
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" class="btn btn-teal-400">
                                        <i class="icon-upload"></i> Import Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                
                
                    <div class="panel panel-white border-top-xlg border-top-warning">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-info"></i> Import Guidelines</h6>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li><strong>File Format:</strong> Use .xlsx, .xls, or .csv files</li>
                                <li><strong>Headers:</strong> First row should contain column headers</li>
                                <li><strong>Data Validation:</strong> Required fields cannot be empty</li>
                                <li><strong>Duplicates:</strong> Check for existing records before importing</li>
                                <li><strong>Backup:</strong> Always backup your database before importing</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

const fileDropZone = document.getElementById('fileDropZone');
const fileInput = document.getElementById('excel_file');

fileDropZone.addEventListener('click', () => fileInput.click());

fileDropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileDropZone.classList.add('dragover');
});

fileDropZone.addEventListener('dragleave', () => {
    fileDropZone.classList.remove('dragover');
});

fileDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    fileDropZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        updateFileName(files[0].name);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        updateFileName(e.target.files[0].name);
    }
});

function updateFileName(fileName) {
    const fileDropZone = document.getElementById('fileDropZone');
    const fileNameDisplay = fileDropZone.querySelector('p');
    fileNameDisplay.textContent = `Selected: ${fileName}`;
}
</script>

<?php require('includes/footer-text.php'); ?>
<?php require('includes/footer.php'); ?>
