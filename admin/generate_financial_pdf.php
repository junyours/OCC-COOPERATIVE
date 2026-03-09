<?php
// Turn off error display for PDF generation
error_reporting(0);
ini_set('display_errors', 0);
require_once('../db_connect.php');
require_once('pdf/fpdf.php');

// Date range parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Include all data functions
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

// Professional PDF Generation Class
class FinancialPDF extends FPDF {
    function Header() {
        global $start_date, $end_date;
        
        // Set background color for header
        $this->SetFillColor(41, 128, 185);
        $this->Rect(0, 0, 297, 40, 'F');
        
        // Logo (if available)
        if (file_exists('../images/main_logo.jpg')) {
            $this->Image('../images/main_logo.jpg', 15, 10, 25);
        }
        
        // Title
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 8, 'OPOL COMMUNITY COLLEGE', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 6, 'EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 6, 'FINANCIAL REPORT', 0, 1, 'C');
        
        // Reset text color for date info
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 5, 'Period: ' . date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)), 0, 1, 'C');
        $this->Cell(0, 5, 'Generated: ' . date('F d, Y h:i A'), 0, 1, 'C');
        
        // Reset text color for content
        $this->SetTextColor(0, 0, 0);
        $this->Ln(15);
    }
    
    function Footer() {
        $this->SetY(-20);
        $this->SetFillColor(41, 128, 185);
        $this->Rect(0, 200, 297, 20, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 8, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 8, 'Confidential - For Internal Use Only', 0, 0, 'C');
    }
    
    function SectionTitle($title) {
        $this->Ln(5);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }
    
    function SubSectionTitle($title) {
        $this->Ln(3);
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->Ln(2);
    }
    
    function SummaryBox($label, $value, $description = '') {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(52, 152, 219);
        $this->Cell(70, 8, $label, 1, 0, 'L', true);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(70, 8, $value, 1, 0, 'R', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(120, 8, $description, 1, 1, 'L');
    }
    
    function TableRow($label, $value, $isBold = false) {
        $this->SetFont($isBold ? 'Arial' : 'Arial', $isBold ? 'B' : '', 9);
        $this->Cell(120, 6, $label, 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(140, 6, $value, 1, 1, 'R');
    }
    
    function TableHeader($headers, $widths) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }
    
    function TableRowData($data, $widths, $alignments = []) {
        $this->SetFont('Arial', '', 9);
        for ($i = 0; $i < count($data); $i++) {
            $align = $alignments[$i] ?? 'L';
            $this->Cell($widths[$i], 6, $data[$i], 1, 0, $align);
        }
        $this->Ln();
    }
    
    function FormatCurrency($amount) {
        return number_format($amount, 2);
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
    
    // Get loan fund balance from tbl_loan_fund
    $loan_fund_query = "SELECT SUM(current_balance) as total_loan_fund_balance, COUNT(*) as fund_count
                        FROM tbl_loan_fund";
    $loan_fund_result = $db->query($loan_fund_query);
    $loan_fund_data = ['total_loan_fund_balance' => 0, 'fund_count' => 0];
    if ($loan_fund_result) {
        $loan_fund_data = $loan_fund_result->fetch_assoc();
    }
    
    $loan_portfolio_data['total_pending_loans'] = $loan_portfolio_data['pending_count'] ?? 0;
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

// Get additional data for PDF
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

// Create PDF in Landscape mode
$pdf = new FinancialPDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

// Executive Summary
$pdf->SectionTitle('EXECUTIVE SUMMARY');
$pdf->SummaryBox('Total Revenue', $pdf->FormatCurrency($total_cooperative_revenue), 'Business + Interest Income');
$pdf->SummaryBox('Total Expenses', $pdf->FormatCurrency($total_cooperative_expenses), 'Operations + Disbursements');
$pdf->SummaryBox(($cooperative_profit_loss >= 0 ? 'Net Profit' : 'Net Loss'), $pdf->FormatCurrency(abs($cooperative_profit_loss)), 'Cooperative Operations');
$pdf->SummaryBox('Net Assets', $pdf->FormatCurrency($net_assets), 'Total Financial Position');
$pdf->Ln(5);

// Cooperative-Specific Metrics
if ($cooperative_tables_exist) {
    $pdf->SectionTitle('COOPERATIVE METRICS');
    $pdf->SummaryBox('Total Capital Shares', $pdf->FormatCurrency($total_capital_data['total_capital_all']), ($total_capital_data['total_members_all'] ?? 0) . ' members');
    $pdf->SummaryBox('Savings Deposits', $pdf->FormatCurrency($current_balances_data['total_current_balance']), ($current_balances_data['active_accounts'] ?? 0) . ' active accounts');
    $pdf->SummaryBox('Loan Portfolio', $pdf->FormatCurrency($loan_portfolio_data['total_approved_loans']), ($loan_portfolio_data['approved_count'] ?? 0) . ' active loans');
    $pdf->SummaryBox('Loan Fund Balance', $pdf->FormatCurrency($loan_fund_data['total_loan_fund_balance']), ($loan_fund_data['fund_count'] ?? 0) . ' funds');
    $pdf->SummaryBox('Interest Income', $pdf->FormatCurrency($loan_repayments_data['total_interest']), 'Loan Operations');
    $pdf->Ln(5);
}

// Capital Shares & Members
if ($cooperative_tables_exist) {
    $pdf->SectionTitle('CAPITAL SHARES & MEMBERS');
    $pdf->SubSectionTitle('Capital Overview');
    $pdf->TableRow('Total Capital Shares (All Time)', $pdf->FormatCurrency($total_capital_data['total_capital_all']), true);
    $pdf->TableRow('Period Contributions', $pdf->FormatCurrency($capital_data['total_capital']));
    $pdf->TableRow('Total Members', number_format($total_capital_data['total_members_all']));
    $pdf->TableRow('Active Contributors', number_format($capital_data['total_members']));
    $avg_share = ($total_capital_data['total_members_all'] ?? 0) > 0 ? ($total_capital_data['total_capital_all'] ?? 0) / $total_capital_data['total_members_all'] : 0;
    $pdf->TableRow('Average Share per Member', $pdf->FormatCurrency($avg_share));
    $pdf->Ln(3);
}

// Loan Portfolio Summary
if ($cooperative_tables_exist) {
    $pdf->SectionTitle('LOAN PORTFOLIO SUMMARY');
    $pdf->SubSectionTitle('Loan Performance');
    $pdf->TableRow('Total Approved Loans', $pdf->FormatCurrency($loan_portfolio_data['total_approved_loans']), true);
    $pdf->TableRow('Pending Applications', $pdf->FormatCurrency($loan_portfolio_data['total_pending_loans']));
    $pdf->TableRow('Total Disbursed', $pdf->FormatCurrency($loan_disbursements_data['total_disbursed']));
    $pdf->TableRow('Total Repayments', $pdf->FormatCurrency($loan_repayments_data['total_repaid']));
    $pdf->TableRow('Interest Income', $pdf->FormatCurrency($loan_repayments_data['total_interest']));
    $pdf->Ln(3);
}

// Savings & Deposits
if ($cooperative_tables_exist) {
    $pdf->SectionTitle('SAVINGS & DEPOSITS');
    $pdf->SubSectionTitle('Deposit Activity');
    $pdf->TableRow('Total Current Balances', $pdf->FormatCurrency($current_balances_data['total_current_balance']), true);
    $pdf->TableRow('Period Deposits', $pdf->FormatCurrency($deposits_data['total_savings']));
    $pdf->TableRow('Period Withdrawals', $pdf->FormatCurrency(abs($deposits_data['total_withdrawals'])));
    $pdf->TableRow('Active Accounts', number_format($current_balances_data['active_accounts']));
    $avg_balance = ($current_balances_data['active_accounts'] ?? 0) > 0 ? ($current_balances_data['total_current_balance'] ?? 0) / $current_balances_data['active_accounts'] : 0;
    $pdf->TableRow('Average Balance per Account', $pdf->FormatCurrency($avg_balance));
    $pdf->Ln(3);
}

// Revenue Analysis
$pdf->SectionTitle('REVENUE ANALYSIS');
$pdf->SubSectionTitle('Revenue Breakdown');
$pdf->TableRow('Sales Revenue', $pdf->FormatCurrency($sales_data['total_sales']), true);
$pdf->TableRow('Total Transactions', number_format($sales_data['total_transactions']));
$avg_transaction = ($sales_data['total_transactions'] ?? 0) > 0 ? ($sales_data['total_sales'] ?? 0) / $sales_data['total_transactions'] : 0;
$pdf->TableRow('Average Transaction Value', $pdf->FormatCurrency($avg_transaction));
if ($cooperative_tables_exist) {
    $pdf->TableRow('Interest Income', $pdf->FormatCurrency($interest_income_data['interest_income']));
}
$pdf->Ln(3);

// Expense Analysis
$pdf->SectionTitle('EXPENSE ANALYSIS');
$pdf->SubSectionTitle('Expense Breakdown');
$pdf->TableRow('Operating Expenses', $pdf->FormatCurrency($expenses_data['total_expenses']), true);
$pdf->TableRow('Purchase Costs', $pdf->FormatCurrency($purchases_data['total_purchases']));
$pdf->TableRow('Inventory Cost Value', $pdf->FormatCurrency($inventory_data['total_cost_value']));
$pdf->TableRow('Total Expense Count', number_format(($expenses_data['expense_count'] ?? 0) + ($purchases_data['purchase_count'] ?? 0)));
$pdf->Ln(3);

// Top Selling Products
$pdf->SectionTitle('TOP PERFORMING PRODUCTS');
$pdf->SubSectionTitle('Product Performance Analysis');
$pdf->TableHeader(['Product Name', 'Quantity Sold', 'Total Revenue'], [120, 60, 80]);

while($product = $top_products_result->fetch_assoc()) {
    $pdf->TableRowData([
        substr($product['product_name'], 0, 50),
        number_format($product['total_quantity']),
        $pdf->FormatCurrency($product['total_revenue'])
    ], [120, 60, 80], ['L', 'C', 'R']);
}
$pdf->Ln(3);

// Top Customers
$pdf->SectionTitle('TOP CUSTOMERS');
$pdf->SubSectionTitle('Customer Spending Analysis');
$pdf->TableHeader(['Customer Name', 'Transactions', 'Total Spent', 'Avg. Transaction'], [100, 50, 70, 40]);

while($customer = $customer_analysis_result->fetch_assoc()) {
    $pdf->TableRowData([
        substr($customer['name'], 0, 40),
        number_format($customer['transaction_count']),
        $pdf->FormatCurrency($customer['total_spent']),
        $pdf->FormatCurrency($customer['total_spent'] / max(1, $customer['transaction_count']))
    ], [100, 50, 70, 40], ['L', 'C', 'R', 'R']);
}
$pdf->Ln(3);

// Financial Ratios
$pdf->SectionTitle('FINANCIAL RATIOS & KEY PERFORMANCE INDICATORS');
$pdf->SubSectionTitle('Profitability Analysis');
$revenue = $total_cooperative_revenue;
$profit_loss = $cooperative_profit_loss;
$cogs = $purchases_data['total_purchases'] ?? 0;
$inventory_cost = $inventory_data['total_cost_value'] ?? 0;

$pdf->TableRow('Gross Profit Margin', $revenue > 0 ? number_format((($revenue - $cogs) / $revenue) * 100, 1) . '%' : '0.0%', true);
$pdf->TableRow('Net Profit Margin', $revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) . '%' : '0.0%');
$pdf->TableRow('Inventory Turnover', $inventory_cost > 0 ? number_format($cogs / $inventory_cost, 2) : 'N/A');
$pdf->TableRow('Return on Sales', $revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) . '%' : '0.0%');

// Add certification section
$pdf->Ln(10);
$pdf->SectionTitle('REPORT CERTIFICATION');
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, 'This report has been prepared based on the financial records maintained in the cooperative\'s', 0, 1, 'L');
$pdf->Cell(0, 6, 'accounting system for the period specified above. All amounts are in Philippine Peso (PHP).', 0, 1, 'L');
$pdf->Cell(0, 6, 'Prepared by: Financial Management System', 0, 1, 'L');
$pdf->Cell(0, 6, 'Date: ' . date('F d, Y'), 0, 1, 'L');

// Output PDF
$filename = 'Financial_Report_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output($filename, 'D');
exit;
?>
