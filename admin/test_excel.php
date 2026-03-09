<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting Excel export test...\n";

try {
    // Include PhpSpreadsheet library
    require_once '../vendor/autoload.php';
    echo "✓ PhpSpreadsheet loaded successfully\n";
    
    // Create simple spreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Test');
    $sheet->setCellValue('B1', 'Excel Export');
    
    echo "✓ Spreadsheet created successfully\n";
    
    // Create writer
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    echo "✓ Writer created successfully\n";
    
    // Set headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="test.xlsx"');
    header('Cache-Control: max-age=0');
    echo "✓ Headers set successfully\n";
    
    // Output file
    $writer->save('php://output');
    echo "✓ File output completed\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "✗ Line: " . $e->getLine() . "\n";
    echo "✗ File: " . $e->getFile() . "\n";
}

exit;
?>
