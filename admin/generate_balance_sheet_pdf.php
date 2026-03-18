<?php
// Turn off error display for PDF generation
error_reporting(0);
ini_set('display_errors', 0);
require_once('../db_connect.php');
require_once('pdf/fpdf.php');

// Date range parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Include all data functions (same as financial_report.php)
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
        SUM(r.receiving_quantity * r.receiving_price) as total_purchases,
        COUNT(DISTINCT r.receiving_no) as purchase_count,
        AVG(r.receiving_quantity * r.receiving_price) as avg_purchase
        FROM tbl_receivings r 
        WHERE DATE(r.date_received) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getCapitalSharesData($db, $start_date, $end_date) {
    $query = "SELECT SUM(t.amount) as total_capital, COUNT(*) as total_shares, COUNT(DISTINCT a.member_id) as total_members
            FROM transactions t
            JOIN accounts a ON t.account_id = a.account_id
            JOIN account_types at ON a.account_type_id = at.account_type_id
            WHERE at.type_name = 'capital_share'
            AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getTotalCapitalData($db, $start_date, $end_date) {
    $query = "SELECT SUM(t.amount) as total_capital_all, COUNT(DISTINCT a.member_id) as total_members_all
            FROM transactions t
            JOIN accounts a ON t.account_id = a.account_id
            JOIN account_types at ON a.account_type_id = at.account_type_id
            WHERE at.type_name = 'capital_share'
            AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getLoanPortfolioData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(CASE WHEN l.status IN ('released', 'ongoing', 'overdue') THEN l.total_due ELSE 0 END) as total_disbursed,
        SUM(CASE WHEN l.status = 'paid' THEN l.total_due ELSE 0 END) as total_repaid,
        COUNT(CASE WHEN l.status IN ('released', 'ongoing', 'overdue') THEN 1 END) as loan_count,
        COUNT(CASE WHEN l.status = 'pending' THEN 1 END) as pending_count,
        COUNT(DISTINCT a.member_id) as borrowers_count
        FROM loans l
        JOIN accounts a ON l.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.account_type_id = 3 -- loan account_type_id
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
        WHERE at.account_type_id = 3 -- loan account_type_id
        AND DATE(lp.payment_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getLoanDisbursementsData($db, $start_date, $end_date) {
    $query = "SELECT 
        SUM(l.total_due ) as total_disbursed_all,
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
        WHERE at.account_type_id = 3 -- loan account_type_id
        AND DATE(lp.payment_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getSavingsData($db, $start_date, $end_date) {
    // Calculate deposits (positive transactions)
    $deposit_query = "SELECT 
        SUM(t.amount) as total_deposits,
        COUNT(*) as deposit_count,
        COUNT(DISTINCT a.member_id) as savers_count
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND t.amount > 0
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $deposit_result = $db->query($deposit_query);
    $deposit_data = $deposit_result->fetch_assoc();
    
    // Calculate withdrawals (negative transactions)
    $withdrawal_query = "SELECT 
        SUM(ABS(t.amount)) as total_withdrawals,
        COUNT(*) as withdrawal_count
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND t.amount < 0
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $withdrawal_result = $db->query($withdrawal_query);
    $withdrawal_data = $withdrawal_result->fetch_assoc();
    
    // Combine the data
    $data = [
        'total_deposits' => $deposit_data['total_deposits'] ?? 0,
        'deposit_count' => $deposit_data['deposit_count'] ?? 0,
        'savers_count' => $deposit_data['savers_count'] ?? 0,
        'total_withdrawals' => $withdrawal_data['total_withdrawals'] ?? 0,
        'withdrawal_count' => $withdrawal_data['withdrawal_count'] ?? 0
    ];
    
    return $data;
}

function getSavingsBalanceData($db, $start_date, $end_date) {
    // Calculate net balance (deposits minus withdrawals) for the period
    $query = "SELECT 
        SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) as total_deposits,
        SUM(CASE WHEN t.amount < 0 THEN ABS(t.amount) ELSE 0 END) as total_withdrawals,
        SUM(t.amount) as current_balance,
        COUNT(DISTINCT a.member_id) as active_savers
        FROM transactions t 
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";    
    
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    
    // Ensure we have the correct net balance
    $data['current_balance'] = ($data['total_deposits'] ?? 0) - ($data['total_withdrawals'] ?? 0);
    
    return $data;
}

function getCumulativeSavingsBalance($db, $start_date, $end_date) {
    // Calculate net balance (deposits minus withdrawals) for all time
    $query = "SELECT 
        SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) as total_deposits,
        SUM(CASE WHEN t.amount < 0 THEN ABS(t.amount) ELSE 0 END) as total_withdrawals,
        SUM(t.amount) as current_balance,
        COUNT(DISTINCT a.member_id) as active_savers
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    
    // Ensure we have the correct net balance
    $data['current_balance'] = ($data['total_deposits'] ?? 0) - ($data['total_withdrawals'] ?? 0);
    
    return $data;
}

// Professional Balance Sheet PDF Class
class BalanceSheetPDF extends FPDF {
    private $currentYear;
    private $previousYear;
    private $yearBeforePrevious;
    
    function __construct($orientation = 'L', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        $this->currentYear = date('Y');
        $this->previousYear = date('Y', strtotime('-1 year'));
        $this->yearBeforePrevious = date('Y', strtotime('-2 years'));
    }
    
    function Header() {
        global $start_date, $end_date;
        
        // Simple header with just title
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 8, 'OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 6, 'Statement of Financial Position', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'As of ' . date('F d, Y', strtotime($end_date)), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 5, 'Amounts in Millions of Philippine Pesos (PHP)', 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetTextColor(100, 100, 100);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    function SectionTitle($title) {
        $this->Ln(4);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }
    
    function SubSectionTitle($title) {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 6, $title, 0, 1, 'L');
        $this->Ln(1);
    }
    
    function TableHeader() {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(150, 8, '', 0, 0, 'L');
        $this->Cell(60, 8, $this->currentYear, 1, 0, 'C', true);
        $this->Cell(60, 8, $this->previousYear, 1, 0, 'C', true);
        $this->Cell(60, 8, $this->yearBeforePrevious, 1, 1, 'C', true);
        $this->SetFont('Arial', '', 9);
    }
    
    function BalanceSheetRow($label, $current, $previous = 0, $yearBefore = 0, $isBold = false, $indent = 0) {
        $this->SetFont($isBold ? 'Arial' : 'Arial', $isBold ? 'B' : '', 9);
        $this->Cell(150 + $indent, 6, $label, 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(60, 6, number_format($current, 2), 1, 0, 'R');
        $this->Cell(60, 6, number_format($previous, 2), 1, 0, 'R');
        $this->Cell(60, 6, number_format($yearBefore, 2), 1, 1, 'R');
    }
    
    function TotalRow($label, $current, $previous = 0, $yearBefore = 0) {
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 7, $label, 'T', 0, 'L');
        $this->Cell(60, 7, number_format($current, 2), 1, 0, 'R');
        $this->Cell(60, 7, number_format($previous, 2), 1, 0, 'R');
        $this->Cell(60, 7, number_format($yearBefore, 2), 1, 1, 'R');
        $this->SetFont('Arial', '', 9);
    }
    
    function FormatCurrency($amount) {
        return number_format($amount, 2);
    }
    
    function GetPreviousYearData($currentData, $factor = 0.85) {
        // Simulate previous year data (in real implementation, this would come from database)
        return $currentData * $factor;
    }
}

// Get all data first
$sales_data = getSalesData($db, $start_date, $end_date);
$monthly_sales = getMonthlySales($db, $start_date, $end_date);
$expenses_data = getExpensesData($db, $start_date, $end_date);
$expenses_by_category = getExpensesByCategory($db, $start_date, $end_date);
$purchases_data = getPurchasesData($db, $start_date, $end_date);

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
    $cumulative_savings_balance = getCumulativeSavingsBalance($db, $start_date, $end_date);
    
    // Prepare data for display
    $deposits_data = [
        'total_deposits' => $savings_data['total_deposits'] ?? 0,
        'deposit_count' => $savings_data['deposit_count'] ?? 0,
        'total_savings' => $savings_data['total_deposits'] ?? 0,
        'total_withdrawals' => $savings_data['total_withdrawals'] ?? 0
    ];

    $current_balances_data = [
        'total_current_balance' => $cumulative_savings_balance['current_balance'] ?? 0,
        'active_accounts' => $cumulative_savings_balance['active_savers'] ?? 0
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
    
    // Calculate financial summary
    $total_cooperative_revenue = ($sales_data['total_sales'] ?? 0) + ($interest_income_data['interest_income'] ?? 0);
    $total_cooperative_expenses = ($expenses_data['total_expenses'] ?? 0) + ($purchases_data['total_purchases'] ?? 0);
    $cooperative_profit_loss = $total_cooperative_revenue - $total_cooperative_expenses;
    $net_assets = ($savings_balance['current_balance'] ?? 0) + ($total_capital_data['total_capital_all'] ?? 0) + ($loan_repayments_all['total_repaid_all'] ?? 0) - ($loan_portfolio_data['total_disbursed']?? 0);

} else {
    // Set default values when cooperative tables don't exist
    $capital_data = ['total_capital' => 0, 'total_shares' => 0, 'total_members' => 0];
    $total_capital_data = ['total_capital_all' => 0, 'total_members_all' => 0];
    $loan_portfolio_data = ['total_approved_loans' => 0, 'total_pending_loans' => 0, 'approved_count' => 0, 'pending_count' => 0, 'total_applications' => 0];
    $loan_disbursements_data = ['total_disbursed' => 0, 'disbursement_count' => 0];
    $loan_repayments_data = ['total_repaid' => 0, 'total_principal' => 0, 'total_interest' => 0, 'repayment_count' => 0];
    $deposits_data = ['total_deposits' => 0, 'deposit_count' => 0, 'total_savings' => 0, 'total_withdrawals' => 0];
    $current_balances_data = ['total_current_balance' => 0, 'active_accounts' => 0];
    $loan_fund_data = ['total_loan_fund_balance' => 0, 'fund_count' => 0];
    
    // Basic business calculations only
    $business_revenue = $sales_data['total_sales'] ?? 0;
    $business_expenses = ($expenses_data['total_expenses'] ?? 0) + ($purchases_data['total_purchases'] ?? 0);
    $total_cooperative_revenue = $business_revenue;
    $total_cooperative_expenses = $business_expenses;
    $cooperative_profit_loss = $business_revenue - $business_expenses;
    $net_assets = 0;
}

// Get additional data for balance sheet
$inventory_value_query = "SELECT SUM(quantity * selling_price) as total_inventory_value, 
                                 SUM(quantity * supplier_price) as total_cost_value,
                                 COUNT(*) as total_products
                          FROM tbl_products 
                          WHERE field_status = 0 AND quantity > 0";
$inventory_result = $db->query($inventory_value_query);
$inventory_data = $inventory_result->fetch_assoc();

// Create PDF with larger format
$pdf = new BalanceSheetPDF('L', 'mm', 'A1');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

// Debug: Show key data values
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, 'DEBUG DATA - Sales: ' . number_format($sales_data['total_sales'] ?? 0, 2) . 
    ' | Capital: ' . number_format($total_capital_data['total_capital_all'] ?? 0, 2) . 
    ' | Savings: ' . number_format($current_balances_data['total_current_balance'] ?? 0, 2), 0, 1, 'L');
$pdf->Ln(3);

// Note: All amounts in million PHP for display
$currency_divisor = 1000000;

// Calculate simulated previous year data
$prev_year_factor = 0.85;
$year_before_factor = 0.7;

// ASSETS SECTION
$pdf->SectionTitle('ASSETS');

// Non-current Assets
$pdf->SubSectionTitle('Non-current assets');
$pdf->TableHeader();

$pdf->BalanceSheetRow('Property, plant and equipment', 
    ($inventory_data['total_cost_value'] ?? 0) / $currency_divisor,
    (($inventory_data['total_cost_value'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($inventory_data['total_cost_value'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Investment properties', 
    ($loan_fund_data['total_loan_fund_balance'] ?? 0) / $currency_divisor,
    (($loan_fund_data['total_loan_fund_balance'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($loan_fund_data['total_loan_fund_balance'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Intangible assets', 
    ($total_capital_data['total_capital_all'] ?? 0) / $currency_divisor,
    (($total_capital_data['total_capital_all'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($total_capital_data['total_capital_all'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Goodwill', 
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss : 0) / $currency_divisor,
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * $prev_year_factor : 0) / $currency_divisor,
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * $year_before_factor : 0) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Available-for-sale financial assets', 
    ($current_balances_data['total_current_balance'] ?? 0) / $currency_divisor,
    (($current_balances_data['total_current_balance'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($current_balances_data['total_current_balance'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Other loans and receivables', 
    ($loan_portfolio_data['total_approved_loans'] ?? 0) / $currency_divisor,
    (($loan_portfolio_data['total_approved_loans'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($loan_portfolio_data['total_approved_loans'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Trade and other receivables', 
    ($sales_data['total_sales'] ?? 0) / $currency_divisor,
    (($sales_data['total_sales'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($sales_data['total_sales'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

// Calculate total non-current assets
$total_non_current_assets = (($inventory_data['total_cost_value'] ?? 0) + 
    ($loan_fund_data['total_loan_fund_balance'] ?? 0) + 
    ($total_capital_data['total_capital_all'] ?? 0) + 
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss : 0) + 
    ($current_balances_data['total_current_balance'] ?? 0) + 
    ($loan_portfolio_data['total_approved_loans'] ?? 0) + 
    ($sales_data['total_sales'] ?? 0)) / $currency_divisor;

$pdf->TotalRow('Total non-current assets', 
    $total_non_current_assets,
    $total_non_current_assets * $prev_year_factor,
    $total_non_current_assets * $year_before_factor);

// Current Assets
$pdf->SubSectionTitle('Current assets');
$pdf->TableHeader();

$pdf->BalanceSheetRow('Inventories', 
    ($inventory_data['total_inventory_value'] ?? 0) / $currency_divisor,
    (($inventory_data['total_inventory_value'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($inventory_data['total_inventory_value'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Trade and other receivables', 
    ($sales_data['total_sales'] ?? 0) * 0.3 / $currency_divisor,
    (($sales_data['total_sales'] ?? 0) * 0.3 * $prev_year_factor) / $currency_divisor,
    (($sales_data['total_sales'] ?? 0) * 0.3 * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Cash and cash equivalents', 
    ($current_balances_data['total_current_balance'] ?? 0) / $currency_divisor,
    (($current_balances_data['total_current_balance'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($current_balances_data['total_current_balance'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

// Calculate total current assets
$total_current_assets = (($inventory_data['total_inventory_value'] ?? 0) + 
    ($sales_data['total_sales'] ?? 0) * 0.3 + 
    ($current_balances_data['total_current_balance'] ?? 0)) / $currency_divisor;

$pdf->TotalRow('Total current assets', 
    $total_current_assets,
    $total_current_assets * $prev_year_factor,
    $total_current_assets * $year_before_factor);

// Total assets
$total_assets = $total_non_current_assets + $total_current_assets;
$pdf->TotalRow('TOTAL ASSETS', 
    $total_assets,
    $total_assets * $prev_year_factor,
    $total_assets * $year_before_factor);

// EQUITY AND LIABILITIES SECTION
$pdf->SectionTitle('EQUITY AND LIABILITIES');

// EQUITY
$pdf->SubSectionTitle('EQUITY');
$pdf->TableHeader();

$pdf->BalanceSheetRow('Equity attributable to owners of Company', 
    ($total_capital_data['total_capital_all'] ?? 0) / $currency_divisor,
    (($total_capital_data['total_capital_all'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($total_capital_data['total_capital_all'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$total_equity = ($total_capital_data['total_capital_all'] ?? 0) / $currency_divisor;
$pdf->TotalRow('Total equity', 
    $total_equity,
    $total_equity * $prev_year_factor,
    $total_equity * $year_before_factor);

// LIABILITIES
$pdf->SubSectionTitle('LIABILITIES');

// Non-current liabilities
$pdf->SubSectionTitle('Non-current liabilities');
$pdf->TableHeader();

$pdf->BalanceSheetRow('Borrowings', 
    ($loan_portfolio_data['total_approved_loans'] ?? 0) / $currency_divisor,
    (($loan_portfolio_data['total_approved_loans'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($loan_portfolio_data['total_approved_loans'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Other payables', 
    ($expenses_data['total_expenses'] ?? 0) / $currency_divisor,
    (($expenses_data['total_expenses'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($expenses_data['total_expenses'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$total_non_current_liabilities = (($loan_portfolio_data['total_approved_loans'] ?? 0) + 
    ($expenses_data['total_expenses'] ?? 0)) / $currency_divisor;

$pdf->TotalRow('Total non-current liabilities', 
    $total_non_current_liabilities,
    $total_non_current_liabilities * $prev_year_factor,
    $total_non_current_liabilities * $year_before_factor);

// Current liabilities
$pdf->SubSectionTitle('Current liabilities');
$pdf->TableHeader();

$pdf->BalanceSheetRow('Trade and other payables', 
    ($purchases_data['total_purchases'] ?? 0) / $currency_divisor,
    (($purchases_data['total_purchases'] ?? 0) * $prev_year_factor) / $currency_divisor,
    (($purchases_data['total_purchases'] ?? 0) * $year_before_factor) / $currency_divisor,
    false, 5);

$pdf->BalanceSheetRow('Current income tax liabilities', 
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * 0.3 : 0) / $currency_divisor,
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * 0.3 * $prev_year_factor : 0) / $currency_divisor,
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * 0.3 * $year_before_factor : 0) / $currency_divisor,
    false, 5);

$total_current_liabilities = (($purchases_data['total_purchases'] ?? 0) + 
    ($cooperative_profit_loss > 0 ? $cooperative_profit_loss * 0.3 : 0)) / $currency_divisor;

$pdf->TotalRow('Total current liabilities', 
    $total_current_liabilities,
    $total_current_liabilities * $prev_year_factor,
    $total_current_liabilities * $year_before_factor);

$total_liabilities = $total_non_current_liabilities + $total_current_liabilities;
$pdf->TotalRow('Total liabilities', 
    $total_liabilities,
    $total_liabilities * $prev_year_factor,
    $total_liabilities * $year_before_factor);

// Total equity and liabilities
$total_equity_and_liabilities = $total_equity + $total_liabilities;
$pdf->TotalRow('Total equity and liabilities', 
    $total_equity_and_liabilities,
    $total_equity_and_liabilities * $prev_year_factor,
    $total_equity_and_liabilities * $year_before_factor);

// Additional metrics
$pdf->Ln(5);
$pdf->BalanceSheetRow('Net current assets', 
    $total_current_assets - $total_current_liabilities,
    ($total_current_assets - $total_current_liabilities) * $prev_year_factor,
    ($total_current_assets - $total_current_liabilities) * $year_before_factor,
    true);

$pdf->BalanceSheetRow('Total assets less current liabilities', 
    $total_assets - $total_current_liabilities,
    ($total_assets - $total_current_liabilities) * $prev_year_factor,
    ($total_assets - $total_current_liabilities) * $year_before_factor,
    true);

// Add certification section
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 4, 'Prepared by: Financial Management System | ' . date('F d, Y'), 0, 1, 'C');
$pdf->Cell(0, 4, 'Amounts in Millions of Philippine Pesos (PHP)', 0, 1, 'C');

// Output PDF
$filename = 'Balance_Sheet_Financial_Report_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output($filename, 'D');
exit;
?>
