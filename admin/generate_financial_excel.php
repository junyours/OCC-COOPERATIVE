<?php
// Turn off error reporting for Excel generation
error_reporting(0);
ini_set('display_errors', 0);
require_once('../db_connect.php');

// Include PhpSpreadsheet library
try {
    require_once '../vendor/autoload.php';
} catch (Exception $e) {
    die('Error loading PhpSpreadsheet library: ' . $e->getMessage());
}
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Axis;
use PhpOffice\PhpSpreadsheet\Chart\GridLines;

// Date range parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Include all data functions (same as PDF)
function getSalesData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(total_amount) as total_sales,
        COUNT(*) as total_orders,
        AVG(total_amount) as avg_order_value,
        COUNT(*) as total_transactions
        FROM tbl_sales 
        WHERE DATE(sales_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getMonthlySales($db, $start_date, $end_date) {
    $query = "SELECT 
        DATE_FORMAT(sales_date, '%Y-%m') as month,
        SUM(total_amount) as monthly_sales,
        COUNT(*) as order_count
        FROM tbl_sales 
        WHERE DATE(sales_date) BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE_FORMAT(sales_date, '%Y-%m')
        ORDER BY month";
    
    $result = $db->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getExpensesData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(expence_amount) as total_expenses,
        COUNT(*) as expense_count,
        AVG(expence_amount) as avg_expense
        FROM tbl_expences 
        WHERE DATE(date_expence) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getExpensesByCategory($db, $start_date, $end_date) {
    $query = "SELECT 
        description,
        SUM(expence_amount) as category_total,
        COUNT(*) as category_count
        FROM tbl_expences 
        WHERE DATE(date_expence) BETWEEN '$start_date' AND '$end_date'
        GROUP BY description
        ORDER BY category_total DESC";
    
    $result = $db->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getPurchasesData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(total_amount) as total_purchases,
        COUNT(*) as purchase_count,
        AVG(total_amount) as avg_purchase
        FROM tbl_receivings 
        WHERE DATE(date_received) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getCapitalSharesData($db, $start_date, $end_date) {
    // Simplified approach based on financial.php reference - just sum all amounts
    $query = "SELECT 
        SUM(t.amount) as total_capital, 
        COUNT(*) as total_shares, 
        COUNT(DISTINCT a.member_id) as total_members
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'capital_share'
        AND t.status = 'active'
        AND DATE(t.created_at) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getTotalCapitalData($db, $start_date, $end_date) {
    // Simplified approach based on financial.php reference - just sum all amounts
    $query = "SELECT 
        SUM(t.amount) as total_capital_all, 
        COUNT(DISTINCT a.member_id) as total_members_all
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'capital_share'
        AND t.status = 'active'
        AND DATE(t.created_at) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getLoanPortfolioData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(CASE WHEN l.status IN ('released', 'ongoing', 'overdue') THEN l.approved_amount ELSE 0 END) as total_disbursed,
        SUM(CASE WHEN l.status = 'paid' THEN l.approved_amount ELSE 0 END) as total_repaid,
        COUNT(CASE WHEN l.status IN ('released', 'ongoing', 'overdue') THEN 1 END) as loan_count,
        COUNT(CASE WHEN l.status = 'pending' THEN 1 END) as pending_count,
        COUNT(DISTINCT a.member_id) as borrowers_count
        FROM loans l
        JOIN accounts a ON l.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.account_type_id = 3
        AND DATE(l.application_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getInterestIncomeData($db, $start_date, $end_date) {
    $query = "SELECT SUM(lp.interest_paid) as interest_income
        FROM loan_payments lp
        JOIN loans l ON lp.loan_id = l.loan_id
        JOIN accounts a ON l.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.account_type_id = 3
        AND DATE(lp.payment_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getLoanDisbursementsData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(l.approved_amount) as total_disbursed_all,
        COUNT(CASE WHEN l.status IN ('released', 'ongoing', 'overdue') THEN 1 END) as disbursement_count_all
        FROM loans l
        JOIN accounts a ON l.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.account_type_id = 3
        AND DATE(l.updated_at) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getLoanRepaymentsData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(lp.amount_paid) as total_repaid_all,
        SUM(lp.interest_paid) as total_interest_all,
        COUNT(lp.payment_id) as repayment_count_all
        FROM loan_payments lp
        JOIN loans l ON lp.loan_id = l.loan_id
        JOIN accounts a ON l.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.account_type_id = 3
        AND DATE(lp.created_at) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getSavingsData($db, $start_date, $end_date) {
    // Simplified approach based on financial.php reference - just sum all amounts
    $query = "SELECT 
        SUM(t.amount) as total_deposits,
        COUNT(*) as deposit_count,
        COUNT(DISTINCT a.member_id) as savers_count
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND t.status = 'active'
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    
    // Set withdrawals to 0 since financial.php doesn't separate them
    $data['total_withdrawals'] = 0;
    $data['withdrawal_count'] = 0;
    
    return $data;
}

function getSavingsBalanceData($db, $start_date, $end_date) {
    // Simplified approach based on financial.php reference - just sum all amounts
    $query = "SELECT 
        SUM(t.amount) as current_balance,
        COUNT(DISTINCT a.member_id) as active_savers
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND t.status = 'active'
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";    
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

// Get all data with error handling
try {
    $sales_data = getSalesData($db, $start_date, $end_date);
} catch (Exception $e) {
    $sales_data = ['total_sales' => 0, 'total_orders' => 0, 'avg_order_value' => 0, 'total_transactions' => 0];
}

try {
    $monthly_sales = getMonthlySales($db, $start_date, $end_date);
} catch (Exception $e) {
    $monthly_sales = [];
}

try {
    $expenses_data = getExpensesData($db, $start_date, $end_date);
} catch (Exception $e) {
    $expenses_data = ['total_expenses' => 0, 'expense_count' => 0, 'avg_expense' => 0];
}

try {
    $expenses_by_category = getExpensesByCategory($db, $start_date, $end_date);
} catch (Exception $e) {
    $expenses_by_category = [];
}

try {
    $purchases_data = getPurchasesData($db, $start_date, $end_date);
} catch (Exception $e) {
    $purchases_data = ['total_purchases' => 0, 'purchase_count' => 0, 'avg_purchase' => 0];
}

// Check if cooperative tables exist
$cooperative_tables_exist = true;
$check_tables = $db->query("SHOW TABLES LIKE 'transactions'");
if ($check_tables->num_rows == 0) {
    $cooperative_tables_exist = false;
}

if ($cooperative_tables_exist) {
    $capital_data = getCapitalSharesData($db, $start_date, $end_date);
    $total_capital_data = getTotalCapitalData($db, $start_date, $end_date);
    $loan_portfolio_data = getLoanPortfolioData($db, $start_date, $end_date);
    $interest_income_data = getInterestIncomeData($db, $start_date, $end_date);
    $loan_disbursements_all = getLoanDisbursementsData($db, $start_date, $end_date);
    $loan_repayments_all = getLoanRepaymentsData($db, $start_date, $end_date);
    $savings_data = getSavingsData($db, $start_date, $end_date);
    $savings_balance = getSavingsBalanceData($db, $start_date, $end_date);
    
    // Prepare data for display
    $deposits_data = [
        'total_deposits' => $savings_data['total_deposits'] ?? 0,
        'deposit_count' => $savings_data['deposit_count'] ?? 0,
        'total_savings' => $savings_data['total_deposits'] ?? 0,
        'total_withdrawals' => $savings_data['total_withdrawals'] ?? 0
    ];
    
    $current_balances_data = [
        'total_current_balance' => $savings_balance['current_balance'] ?? 0,
        'active_accounts' => $savings_balance['active_savers'] ?? 0
    ];
    
    $loan_repayments_data = [
        'total_repaid' => $loan_repayments_all['total_repaid_all'] ?? 0,
        'total_interest' => $loan_repayments_all['total_interest_all'] ?? 0,
        'repayment_count' => $loan_repayments_all['repayment_count_all'] ?? 0
    ];
    
    $loan_disbursements_data = [
        'total_disbursed' => $loan_disbursements_all['total_disbursed_all'] ?? 0,
        'disbursement_count' => $loan_disbursements_all['disbursement_count_all'] ?? 0
    ];
    
    $loan_portfolio_data['total_pending_loans'] = $loan_portfolio_data['pending_count'] ?? 0;
    $loan_portfolio_data['total_approved_loans'] = $loan_portfolio_data['total_disbursed'] ?? 0;
    $loan_portfolio_data['approved_count'] = $loan_portfolio_data['loan_count'] ?? 0;
    $loan_portfolio_data['pending_count'] = $loan_portfolio_data['pending_count'] ?? 0;
    
    // Get loan fund balance from tbl_loan_fund
    $loan_fund_query = "SELECT SUM(current_balance) as total_loan_fund_balance, COUNT(*) as fund_count
                        FROM tbl_loan_fund";
    $loan_fund_result = $db->query($loan_fund_query);
    $loan_fund_data = ['total_loan_fund_balance' => 0, 'fund_count' => 0];
    if ($loan_fund_result) {
        $loan_fund_data = $loan_fund_result->fetch_assoc();
    }
    $loan_portfolio_data['total_approved_loans'] = $loan_portfolio_data['total_disbursed'] ?? 0;
    $loan_portfolio_data['approved_count'] = $loan_portfolio_data['loan_count'] ?? 0;
    $loan_portfolio_data['pending_count'] = $loan_portfolio_data['pending_count'] ?? 0;
    
    // Calculate financial summary
    $total_cooperative_revenue = ($sales_data['total_sales'] ?? 0) + ($interest_income_data['interest_income'] ?? 0);
    $total_cooperative_expenses = ($expenses_data['total_expenses'] ?? 0) + ($purchases_data['total_purchases'] ?? 0);
    $cooperative_profit_loss = $total_cooperative_revenue - $total_cooperative_expenses;
    $net_assets = ($savings_balance['current_balance'] ?? 0) + ($loan_portfolio_data['total_disbursed'] ?? 0) - ($loan_repayments_all['total_repaid_all'] ?? 0);
} else {
    // Set default values when cooperative tables don't exist
    $capital_data = ['total_capital' => 0, 'total_shares' => 0, 'total_members' => 0];
    $total_capital_data = ['total_capital_all' => 0, 'total_members_all' => 0];
    $loan_portfolio_data = ['total_approved_loans' => 0, 'total_pending_loans' => 0, 'approved_count' => 0, 'pending_count' => 0, 'total_applications' => 0];
    $loan_disbursements_data = ['total_disbursed' => 0, 'disbursement_count' => 0];
    $loan_repayments_data = ['total_repaid' => 0, 'total_principal' => 0, 'total_interest' => 0, 'repayment_count' => 0];
    $deposits_data = ['total_deposits' => 0, 'deposit_count' => 0, 'total_savings' => 0, 'total_withdrawals' => 0];
    $current_balances_data = ['total_current_balance' => 0, 'active_accounts' => 0];
    
    // Basic business calculations only
    $business_revenue = $sales_data['total_sales'] ?? 0;
    $business_expenses = ($expenses_data['total_expenses'] ?? 0) + ($purchases_data['total_purchases'] ?? 0);
    $total_cooperative_revenue = $business_revenue;
    $total_cooperative_expenses = $business_expenses;
    $cooperative_profit_loss = $business_revenue - $business_expenses;
    $net_assets = 0;
}

// Get additional data for Excel
$top_products_query = "SELECT p.product_name, SUM(s.quantity_order) as total_quantity, SUM(s.total_amount) as total_revenue
                       FROM tbl_sales s
                       JOIN tbl_products p ON s.product_id = p.product_id
                       WHERE DATE(s.sales_date) BETWEEN '$start_date' AND '$end_date' AND s.field_status = 0
                       GROUP BY p.product_id, p.product_name
                       ORDER BY total_revenue DESC
                       LIMIT 10";
$top_products_result = $db->query($top_products_query);

$customer_analysis_query = "SELECT c.name, COUNT(s.sales_id) as transaction_count, SUM(s.total_amount) as total_spent
                            FROM tbl_sales s
                            JOIN tbl_customer c ON s.cust_id = c.cust_id
                            WHERE DATE(s.sales_date) BETWEEN '$start_date' AND '$end_date' AND s.field_status = 0
                            GROUP BY c.cust_id, c.name
                            ORDER BY total_spent DESC
                            LIMIT 10";
$customer_analysis_result = $db->query($customer_analysis_query);

$inventory_value_query = "SELECT SUM(quantity * selling_price) as total_inventory_value, 
                                 SUM(quantity * supplier_price) as total_cost_value,
                                 COUNT(*) as total_products
                          FROM tbl_products 
                          WHERE field_status = 0 AND quantity > 0";
$inventory_result = $db->query($inventory_value_query);
$inventory_data = $inventory_result->fetch_assoc();

// Create new Spreadsheet with error handling
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Financial Report');
} catch (Exception $e) {
    die('Error creating spreadsheet: ' . $e->getMessage());
}

// Set styles
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2980B9']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

$subHeaderStyle = [
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B9BD5']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

$currencyStyle = [
    'font' => ['size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    'numberFormat' => ['formatCode' => '#,##0.00']
];

$numberStyle = [
    'font' => ['size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    'numberFormat' => ['formatCode' => '#,##0']
];

// Set column widths
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);

// Header Section
$row = 1;
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

$row++;
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'FINANCIAL REPORT');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

$row++;
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'Period: ' . date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)) . ' | Generated: ' . date('F d, Y h:i A'));
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$row += 2;

// Executive Summary
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'EXECUTIVE SUMMARY');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$summaryData = [
    ['Total Revenue', number_format($total_cooperative_revenue, 2), 'Business + Interest Income'],
    ['Total Expenses', number_format($total_cooperative_expenses, 2), 'Operations + Disbursements'],
    [($cooperative_profit_loss >= 0 ? 'Net Profit' : 'Net Loss'), number_format(abs($cooperative_profit_loss), 2), 'Cooperative Operations'],
    ['Net Assets', number_format($net_assets, 2), 'Total Financial Position']
];

foreach ($summaryData as $data) {
    $sheet->setCellValue('A'.$row, $data[0]);
    $sheet->setCellValue('B'.$row, $data[1]);
    $sheet->setCellValue('C'.$row, $data[2]);
    $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'font' => ['size' => 10]
    ]);
    $row++;
}

$row += 2;

// Cooperative Metrics (if available)
if ($cooperative_tables_exist) {
    $sheet->mergeCells('A'.$row.':H'.$row);
    $sheet->setCellValue('A'.$row, 'COOPERATIVE METRICS');
    $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

    $row++;
    $cooperativeData = [
        ['Total Capital Shares', number_format($total_capital_data['total_capital_all'], 2), ($total_capital_data['total_members_all'] ?? 0) . ' members'],
        ['Savings Deposits', number_format($current_balances_data['total_current_balance'], 2), ($current_balances_data['active_accounts'] ?? 0) . ' active accounts'],
        ['Loan Portfolio', number_format($loan_portfolio_data['total_approved_loans'], 2), ($loan_portfolio_data['approved_count'] ?? 0) . ' active loans'],
        ['Loan Fund Balance', number_format($loan_fund_data['total_loan_fund_balance'], 2), ($loan_fund_data['fund_count'] ?? 0) . ' funds'],
        ['Interest Income', number_format($loan_repayments_data['total_interest'], 2), 'Loan Operations']
    ];

    foreach ($cooperativeData as $data) {
        $sheet->setCellValue('A'.$row, $data[0]);
        $sheet->setCellValue('B'.$row, $data[1]);
        $sheet->setCellValue('C'.$row, $data[2]);
        $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'font' => ['size' => 10]
        ]);
        $row++;
    }
    $row += 2;
}

// Monthly Sales Chart Data
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'MONTHLY SALES TREND');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$sheet->setCellValue('A'.$row, 'Month');
$sheet->setCellValue('B'.$row, 'Sales');
$sheet->setCellValue('C'.$row, 'Transactions');
$sheet->setCellValue('D'.$row, 'Average');
$sheet->getStyle('A'.$row.':D'.$row)->applyFromArray($subHeaderStyle);

foreach ($monthly_sales as $month) {
    $row++;
    $sheet->setCellValue('A'.$row, date('F Y', strtotime($month['month'] . '-01')));
    $sheet->setCellValue('B'.$row, $month['monthly_sales']);
    $sheet->setCellValue('C'.$row, $month['order_count']);
    $sheet->setCellValue('D'.$row, $month['order_count'] > 0 ? $month['monthly_sales'] / $month['order_count'] : 0);
    $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($currencyStyle);
}

$row += 2;

// Top Products
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'TOP PERFORMING PRODUCTS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$sheet->setCellValue('A'.$row, 'Product Name');
$sheet->setCellValue('B'.$row, 'Quantity Sold');
$sheet->setCellValue('C'.$row, 'Total Revenue');
$sheet->getStyle('A'.$row.':C'.$row)->applyFromArray($subHeaderStyle);

while($product = $top_products_result->fetch_assoc()) {
    $row++;
    $sheet->setCellValue('A'.$row, $product['product_name']);
    $sheet->setCellValue('B'.$row, $product['total_quantity']);
    $sheet->setCellValue('C'.$row, $product['total_revenue']);
    $sheet->getStyle('B'.$row.':C'.$row)->applyFromArray($currencyStyle);
}

$row += 2;

// Top Customers
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'TOP CUSTOMERS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$sheet->setCellValue('A'.$row, 'Customer Name');
$sheet->setCellValue('B'.$row, 'Transactions');
$sheet->setCellValue('C'.$row, 'Total Spent');
$sheet->setCellValue('D'.$row, 'Avg. Transaction');
$sheet->getStyle('A'.$row.':D'.$row)->applyFromArray($subHeaderStyle);

while($customer = $customer_analysis_result->fetch_assoc()) {
    $row++;
    $sheet->setCellValue('A'.$row, $customer['name']);
    $sheet->setCellValue('B'.$row, $customer['transaction_count']);
    $sheet->setCellValue('C'.$row, $customer['total_spent']);
    $sheet->setCellValue('D'.$row, $customer['transaction_count'] > 0 ? $customer['total_spent'] / $customer['transaction_count'] : 0);
    $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($currencyStyle);
}

$row += 2;

// Financial Summary Calculations Section
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'FINANCIAL CALCULATIONS & EXPLANATIONS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$calculationData = [
    ['Total Revenue Calculation', 'Sales Revenue + Interest Income', number_format($total_cooperative_revenue, 2), '₱' . number_format($sales_data['total_sales'] ?? 0, 2) . ' + ₱' . number_format($interest_income_data['interest_income'] ?? 0, 2)],
    ['Total Expenses Calculation', 'Operating Expenses + Purchase Costs', number_format($total_cooperative_expenses, 2), '₱' . number_format($expenses_data['total_expenses'] ?? 0, 2) . ' + ₱' . number_format($purchases_data['total_purchases'] ?? 0, 2)],
    ['Net Profit/Loss Calculation', 'Total Revenue - Total Expenses', number_format(abs($cooperative_profit_loss), 2), '₱' . number_format($total_cooperative_revenue, 2) . ' - ₱' . number_format($total_cooperative_expenses, 2)],
    ['Net Assets Calculation', 'Savings + Capital + Interest - Outstanding Loans', number_format($net_assets, 2), '₱' . number_format($savings_balance['current_balance'] ?? 0, 2) . ' + ₱' . number_format($total_capital_data['total_capital_all'] ?? 0, 2) . ' + ₱' . number_format($interest_income_data['interest_income'] ?? 0, 2) . ' - ₱' . number_format($loan_portfolio_data['total_approved_loans'] ?? 0, 2)]
];

foreach ($calculationData as $data) {
    $sheet->setCellValue('A'.$row, $data[0]);
    $sheet->setCellValue('B'.$row, $data[1]);
    $sheet->setCellValue('C'.$row, $data[2]);
    $sheet->setCellValue('D'.$row, $data[3]);
    $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'font' => ['size' => 10]
    ]);
    $row++;
}

$row += 2;

// Financial Ratios with Detailed Explanations
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'FINANCIAL RATIOS WITH CALCULATIONS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$revenue = $total_cooperative_revenue;
$profit_loss = $cooperative_profit_loss;
$cogs = $purchases_data['total_purchases'] ?? 0;
$inventory_cost = $inventory_data['total_cost_value'] ?? 0;

$ratioData = [
    ['Gross Profit Margin', '(Revenue - COGS) ÷ Revenue × 100', (($revenue > 0 ? number_format((($revenue - $cogs) / $revenue) * 100, 1) : 0) . '%'), 'Measures profitability after direct costs'],
    ['Net Profit Margin', '(Net Profit ÷ Revenue) × 100', (($revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) : 0) . '%'), 'Overall profitability after all expenses'],
    ['Inventory Turnover', 'COGS ÷ Average Inventory Value', (($inventory_cost > 0 ? number_format($cogs / $inventory_cost, 2) : 'N/A')), 'How quickly inventory is sold'],
    ['Return on Sales', '(Net Profit ÷ Revenue) × 100', (($revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) : 0) . '%'), 'Efficiency of generating profit from sales']
];

foreach ($ratioData as $data) {
    $sheet->setCellValue('A'.$row, $data[0]);
    $sheet->setCellValue('B'.$row, $data[1]);
    $sheet->setCellValue('C'.$row, $data[2]);
    $sheet->setCellValue('D'.$row, $data[3]);
    $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'font' => ['size' => 10]
    ]);
    $row++;
}

$row += 2;

// Financial Insights Section
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'FINANCIAL HEALTH INSIGHTS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

// Calculate margin separately to avoid complex ternary
$profit_margin = ($revenue > 0 ? ($profit_loss / $revenue) * 100 : 0);
$profitability_text = ($profit_loss >= 0 ? 'The cooperative is generating profit' : 'The cooperative is operating at a loss');

$row++;
$insightData = [
    ['Profitability Analysis', $profitability_text, number_format($profit_margin, 1) . '%', 'Profit Margin'],
    ['Asset Growth', 'Net assets represent cooperative financial position', number_format($net_assets, 2), 'Net Assets'],
    ['Revenue Diversification', (($interest_income_data['interest_income'] ?? 0) > 0 ? 'Interest income contributes to revenue diversity' : 'Revenue relies primarily on sales operations'), '', ''],
    ['Liquidity Position', ($net_assets > 0 ? 'Strong financial position' : 'Needs attention'), number_format($loan_fund_data['total_loan_fund_balance'] ?? 0, 2), 'Loan Fund Balance']
];

foreach ($insightData as $data) {
    $sheet->setCellValue('A'.$row, $data[0]);
    $sheet->setCellValue('B'.$row, $data[1]);
    $sheet->setCellValue('C'.$row, $data[2]);
    $sheet->setCellValue('D'.$row, $data[3]);
    $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'font' => ['size' => 10]
    ]);
    $row++;
}

$row += 2;

// Financial Ratios
$sheet->mergeCells('A'.$row.':H'.$row);
$sheet->setCellValue('A'.$row, 'FINANCIAL RATIOS & KPIS');
$sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);

$row++;
$ratiosData = [
    ['Gross Profit Margin', (($total_cooperative_revenue > 0 ? number_format((($total_cooperative_revenue - ($purchases_data['total_purchases'] ?? 0)) / $total_cooperative_revenue) * 100, 1) : 0) . '%')],
    ['Net Profit Margin', (($total_cooperative_revenue > 0 ? number_format(($cooperative_profit_loss / $total_cooperative_revenue) * 100, 1) : 0) . '%')],
    ['Inventory Turnover', (($inventory_data['total_cost_value'] ?? 0) > 0 ? number_format(($purchases_data['total_purchases'] ?? 0) / $inventory_data['total_cost_value'], 2) : 'N/A')],
    ['Return on Sales', (($total_cooperative_revenue > 0 ? number_format(($cooperative_profit_loss / $total_cooperative_revenue) * 100, 1) : 0) . '%')]
];

foreach ($ratiosData as $data) {
    $sheet->setCellValue('A'.$row, $data[0]);
    $sheet->setCellValue('B'.$row, $data[1]);
    $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'font' => ['size' => 10]
    ]);
    $row++;
}

// Create Charts Sheet with Enhanced Data and Conditional Formatting
$chartsSheet = $spreadsheet->createSheet();
$chartsSheet->setTitle('Charts & Graphs');

// Monthly Sales Chart Data with Visual Enhancement
$chartsRow = 1;
$chartsSheet->setCellValue('A1', 'MONTHLY SALES TREND');
$chartsSheet->getStyle('A1')->applyFromArray($headerStyle);

$chartsRow = 3;
$chartsSheet->setCellValue('A'.$chartsRow, 'Month');
$chartsSheet->setCellValue('B'.$chartsRow, 'Sales Amount');
$chartsSheet->setCellValue('C'.$chartsRow, 'Transactions');
$chartsSheet->setCellValue('D'.$chartsRow, 'Average');
$chartsSheet->getStyle('A'.$chartsRow.':D'.$chartsRow)->applyFromArray($subHeaderStyle);

$startRow = $chartsRow + 1;
$maxSales = 0;
foreach ($monthly_sales as $month) {
    if ($month['monthly_sales'] > $maxSales) {
        $maxSales = $month['monthly_sales'];
    }
}

foreach ($monthly_sales as $index => $month) {
    $chartsRow++;
    $chartsSheet->setCellValue('A'.$chartsRow, date('M Y', strtotime($month['month'] . '-01')));
    $chartsSheet->setCellValue('B'.$chartsRow, $month['monthly_sales']);
    $chartsSheet->setCellValue('C'.$chartsRow, $month['order_count']);
    $chartsSheet->setCellValue('D'.$chartsRow, $month['order_count'] > 0 ? $month['monthly_sales'] / $month['order_count'] : 0);
    
    // Apply conditional formatting for visual bar chart effect
    $barLength = ($maxSales > 0) ? ($month['monthly_sales'] / $maxSales) * 15 : 0;
    if ($barLength > 0) {
        $columns = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
        for ($i = 0; $i < min($barLength, 15); $i++) {
            $colLetter = $columns[$i] ?? 'T';
            $chartsSheet->setCellValue($colLetter . $chartsRow, '|');
            $chartsSheet->getStyle($colLetter . $chartsRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2980B9']],
                'font' => ['color' => ['rgb' => '2980B9']]
            ]);
        }
    }
    
    $chartsSheet->getStyle('B'.$chartsRow.':D'.$chartsRow)->applyFromArray($currencyStyle);
}

// Add simple chart representation
$chartsRow += 3;
$chartsSheet->setCellValue('A'.$chartsRow, '📊 Visual Sales Chart:');
$chartsSheet->getStyle('A'.$chartsRow)->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E8']]
]);

// Revenue vs Expenses Chart Data with Visual Bars
$chartsRow += 2;
$chartsSheet->setCellValue('A'.$chartsRow, 'REVENUE VS EXPENSES COMPARISON');
$chartsSheet->getStyle('A'.$chartsRow)->applyFromArray($headerStyle);

$chartsRow += 2;
$revenueExpenseData = [
    ['Sales Revenue', $sales_data['total_sales'] ?? 0, 'revenue'],
    ['Interest Income', $interest_income_data['interest_income'] ?? 0, 'revenue'],
    ['Operating Expenses', $expenses_data['total_expenses'] ?? 0, 'expense'],
    ['Purchase Costs', $purchases_data['total_purchases'] ?? 0, 'expense']
];

$chartsSheet->setCellValue('A'.$chartsRow, 'Category');
$chartsSheet->setCellValue('B'.$chartsRow, 'Amount');
$chartsSheet->setCellValue('C'.$chartsRow, 'Type');
$chartsSheet->setCellValue('D'.$chartsRow, 'Visual');
$chartsSheet->getStyle('A'.$chartsRow.':D'.$chartsRow)->applyFromArray($subHeaderStyle);

$maxAmount = 0;
foreach ($revenueExpenseData as $data) {
    if ($data[1] > $maxAmount) {
        $maxAmount = $data[1];
    }
}

$barStartRow = $chartsRow + 1;
foreach ($revenueExpenseData as $index => $data) {
    $chartsRow++;
    $chartsSheet->setCellValue('A'.$chartsRow, $data[0]);
    $chartsSheet->setCellValue('B'.$chartsRow, $data[1]);
    $chartsSheet->setCellValue('C'.$chartsRow, $data[2]);
    
    // Create visual bar chart
    $barLength = ($maxAmount > 0) ? ($data[1] / $maxAmount) * 20 : 0;
    $barColor = ($data[2] === 'revenue') ? '4CAF50' : 'F44336'; // Green for revenue, red for expenses
    
    $columns = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];
    for ($i = 0; $i < min($barLength, 20); $i++) {
        $colLetter = $columns[$i] ?? 'Y';
        $chartsSheet->setCellValue($colLetter . $chartsRow, '|');
        $chartsSheet->getStyle($colLetter . $chartsRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $barColor]],
            'font' => ['color' => ['rgb' => $barColor]]
        ]);
    }
    
    $chartsSheet->getStyle('B'.$chartsRow)->applyFromArray($currencyStyle);
    $chartsSheet->getStyle('C'.$chartsRow)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $barColor === '4CAF50' ? 'E8F5E8' : 'FFEBEE']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
}

// Portfolio Distribution with Visual Pie Chart
$chartsRow += 3;
$chartsSheet->setCellValue('A'.$chartsRow, 'PORTFOLIO DISTRIBUTION');
$chartsSheet->getStyle('A'.$chartsRow)->applyFromArray($headerStyle);

$chartsRow += 2;
if ($cooperative_tables_exist) {
    $portfolioData = [
        ['Capital Shares', $total_capital_data['total_capital_all']],
        ['Savings', $current_balances_data['total_current_balance']],
        ['Loan Portfolio', $loan_portfolio_data['total_approved_loans']],
        ['Interest Income', $loan_repayments_data['total_interest']]
    ];
} else {
    $portfolioData = [
        ['Sales Revenue', $sales_data['total_sales'] ?? 0],
        ['Operating Expenses', $expenses_data['total_expenses'] ?? 0],
        ['Purchase Costs', $purchases_data['total_purchases'] ?? 0],
        ['Net Position', $cooperative_profit_loss]
    ];
}

$chartsSheet->setCellValue('A'.$chartsRow, 'Component');
$chartsSheet->setCellValue('B'.$chartsRow, 'Value');
$chartsSheet->setCellValue('C'.$chartsRow, 'Percentage');
$chartsSheet->getStyle('A'.$chartsRow.':C'.$chartsRow)->applyFromArray($subHeaderStyle);

$totalPortfolio = 0;
foreach ($portfolioData as $data) {
    $totalPortfolio += abs($data[1]);
}

$pieColors = ['2196F3', '4CAF50', 'FF9800', 'F44336']; // Blue, Green, Orange, Red
$pieStartRow = $chartsRow + 1;

foreach ($portfolioData as $index => $data) {
    $chartsRow++;
    $percentage = ($totalPortfolio > 0) ? ($data[1] / $totalPortfolio) * 100 : 0;
    $chartsSheet->setCellValue('A'.$chartsRow, $data[0]);
    $chartsSheet->setCellValue('B'.$chartsRow, $data[1]);
    $chartsSheet->setCellValue('C'.$chartsRow, number_format($percentage, 1) . '%');
    
    // Create visual pie slice representation
    $sliceSize = round(($percentage / 100) * 20);
    $color = $pieColors[$index % count($pieColors)];
    
    $columns = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];
    for ($i = 0; $i < $sliceSize; $i++) {
        $colLetter = $columns[$i] ?? 'Y';
        $chartsSheet->setCellValue($colLetter . $chartsRow, '|');
        $chartsSheet->getStyle($colLetter . $chartsRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            'font' => ['color' => ['rgb' => $color]]
        ]);
    }
    
    $chartsSheet->getStyle('B'.$chartsRow)->applyFromArray($currencyStyle);
    $chartsSheet->getStyle('C'.$chartsRow)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color . '33']], // Add transparency
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
}

// Add Summary Statistics
$chartsRow += 3;
$chartsSheet->setCellValue('A'.$chartsRow, '📈 SUMMARY STATISTICS');
$chartsSheet->getStyle('A'.$chartsRow)->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']]
]);

$chartsRow += 2;
$summaryStats = [
    ['Total Revenue', $total_cooperative_revenue],
    ['Total Expenses', $total_cooperative_expenses],
    ['Net Profit/Loss', $cooperative_profit_loss],
    ['Profit Margin', ($total_cooperative_revenue > 0 ? ($cooperative_profit_loss / $total_cooperative_revenue) * 100 : 0) . '%']
];

foreach ($summaryStats as $index => $stat) {
    $chartsRow++;
    $chartsSheet->setCellValue('A'.$chartsRow, $stat[0] . ':');
    $chartsSheet->setCellValue('B'.$chartsRow, is_numeric($stat[1]) ? number_format($stat[1], 2) : $stat[1]);
    $chartsSheet->getStyle('A'.$chartsRow)->applyFromArray(['font' => ['bold' => true]]);
    $chartsSheet->getStyle('B'.$chartsRow)->applyFromArray($currencyStyle);
}

// Set active sheet back to main
$spreadsheet->setActiveSheetIndex(0);

// Create Excel writer with error handling
try {
    $writer = new Xlsx($spreadsheet);
} catch (Exception $e) {
    die('Error creating Excel writer: ' . $e->getMessage());
}

// Set headers
try {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Financial_Report_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');
} catch (Exception $e) {
    die('Error setting headers: ' . $e->getMessage());
}

// Output file
try {
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    die('Error saving file: ' . $e->getMessage());
}
?>
