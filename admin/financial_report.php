<?php require('includes/header.php'); ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../db_connect.php');

// Date range parameters
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-d');




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
    $query = "SELECT SUM(t.amount) as total_capital, COUNT(*) as total_shares, COUNT(DISTINCT a.member_id) as total_members
            FROM transactions t
            JOIN accounts a ON t.account_id = a.account_id
            JOIN account_types at ON a.account_type_id = at.account_type_id
            WHERE at.account_type_id = 2 -- capital_share account_type_id
            AND t.status = 'active'
            AND EXISTS (
                SELECT 1 FROM transaction_types tt 
                WHERE tt.transaction_type_id = t.transaction_type_id 
                AND tt.type_name = 'deposit'
            )
            AND DATE(t.created_at) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function getTotalCapitalData($db, $start_date, $end_date) {
    $query = "SELECT SUM(t.amount) as total_capital_all, COUNT(DISTINCT a.member_id) as total_members_all
            FROM transactions t
            JOIN accounts a ON t.account_id = a.account_id
            JOIN account_types at ON a.account_type_id = at.account_type_id
            WHERE at.account_type_id = 2 -- capital_share account_type_id
            AND t.status = 'active'
            AND EXISTS (
                SELECT 1 FROM transaction_types tt 
                WHERE tt.transaction_type_id = t.transaction_type_id 
                AND tt.type_name = 'deposit'
            )
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
        WHERE at.account_type_id = 3 -- loan account_type_id
        AND DATE(lp.payment_date) BETWEEN '$start_date' AND '$end_date'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

// 6. SAVINGS/DEPOSITS QUERIES
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

function getCumulativeSavingsBalance($db) {
    // Simplified approach based on financial.php reference - just sum all amounts
    $query = "SELECT 
        SUM(t.amount) as current_balance,
        COUNT(DISTINCT a.member_id) as active_savers
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN account_types at ON a.account_type_id = at.account_type_id
        WHERE at.type_name = 'savings'
        AND t.status = 'active'";
    
    $result = $db->query($query);
    return $result->fetch_assoc();
}

// =====================================
// DATA EXECUTION SECTION
// =====================================

// Execute all queries and get data
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
    // Cooperative data queries
    $capital_data = getCapitalSharesData($db, $start_date, $end_date);
    $total_capital_data = getTotalCapitalData($db, $start_date, $end_date);
    $loan_portfolio_data = getLoanPortfolioData($db, $start_date, $end_date);
    $interest_income_data = getInterestIncomeData($db, $start_date, $end_date);
    $loan_disbursements_all = getLoanDisbursementsData($db, $start_date, $end_date);
    $loan_repayments_all = getLoanRepaymentsData($db, $start_date, $end_date);
    $savings_data = getSavingsData($db, $start_date, $end_date);
    $savings_balance = getSavingsBalanceData($db, $start_date, $end_date);
    $cumulative_savings_balance = getCumulativeSavingsBalance($db);
    
    // Prepare data for display
    $deposits_data = [
        'total_deposits' => $savings_data['total_deposits'] ?? 0,
        'deposit_count' => $savings_data['deposit_count'] ?? 0,
        'total_savings' => $savings_data['total_deposits'] ?? 0,
        'total_withdrawals' => $savings_data['total_withdrawals'] ?? 0
    ];
    
    $current_balances_data = [
        'total_current_balance' => $cumulative_savings_balance['current_balance'] ?? 0, // Use cumulative balance
        'active_accounts' => $cumulative_savings_balance['active_savers'] ?? 0
    ];
    
    // Also prepare period balance for display if needed
    $period_balance_data = [
        'period_balance' => $savings_balance['current_balance'] ?? 0,
        'period_active_accounts' => $savings_balance['active_savers'] ?? 0
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
    $loan_fund_data = ['total_loan_fund_balance' => 0, 'fund_count' => 0];
    
    // Basic business calculations only
    $business_revenue = $sales_data['total_sales'] ?? 0;
    $business_expenses = ($expenses_data['total_expenses'] ?? 0) + ($purchases_data['total_purchases'] ?? 0);
    $total_cooperative_revenue = $business_revenue;
    $total_cooperative_expenses = $business_expenses;
    $cooperative_profit_loss = $business_revenue - $business_expenses;
    $net_assets = ($inventory_data['total_inventory_value'] ?? 0);
}

// Top selling products
$top_products_query = "SELECT p.product_name, SUM(s.quantity_order) as total_quantity, SUM(s.total_amount) as total_revenue
                       FROM tbl_sales s
                       JOIN tbl_products p ON s.product_id = p.product_id
                       WHERE DATE(s.sales_date) BETWEEN '$start_date' AND '$end_date' AND s.field_status = 0
                       GROUP BY p.product_id, p.product_name
                       ORDER BY total_revenue DESC
                       LIMIT 10";
$top_products_result = $db->query($top_products_query);

// Monthly sales trend
$monthly_sales_query = "SELECT DATE_FORMAT(sales_date, '%Y-%m') as month, SUM(total_amount) as monthly_sales, COUNT(*) as monthly_transactions
                        FROM tbl_sales 
                        WHERE DATE(sales_date) BETWEEN '$start_date' AND '$end_date' AND field_status = 0
                        GROUP BY DATE_FORMAT(sales_date, '%Y-%m')
                        ORDER BY month";
$monthly_sales_result = $db->query($monthly_sales_query);

// Expense categories
$expense_categories_query = "SELECT description, SUM(expence_amount) as category_total
                            FROM tbl_expences 
                            WHERE DATE(date_expence) BETWEEN '$start_date' AND '$end_date' AND field_status = 0
                            GROUP BY description
                            ORDER BY category_total DESC
                            LIMIT 10";
$expense_categories_result = $db->query($expense_categories_query);

// Inventory value (current inventory - not date filtered)
$inventory_value_query = "SELECT SUM(quantity * selling_price) as total_inventory_value, 
                                 SUM(quantity * supplier_price) as total_cost_value,
                                 COUNT(*) as total_products
                          FROM tbl_products 
                          WHERE field_status = 0 AND quantity > 0";
$inventory_result = $db->query($inventory_value_query);
$inventory_data = $inventory_result->fetch_assoc();

// Date-filtered inventory movements
$inventory_movements_query = "SELECT 
        SUM(CASE WHEN r.field_status = 0 THEN r.receiving_quantity * p.selling_price ELSE 0 END) as purchases_value,
        SUM(CASE WHEN s.field_status = 0 THEN s.quantity_order * p.selling_price ELSE 0 END) as sales_value,
        SUM(CASE WHEN d.field_status = 0 THEN d.quantity_damage * p.selling_price ELSE 0 END) as damage_value
        FROM tbl_products p
        LEFT JOIN tbl_receivings r ON p.product_id = r.product_id AND DATE(r.date_received) BETWEEN '$start_date' AND '$end_date'
        LEFT JOIN tbl_sales s ON p.product_id = s.product_id AND DATE(s.sales_date) BETWEEN '$start_date' AND '$end_date'
        LEFT JOIN tbl_damage d ON p.product_id = d.product_id AND DATE(d.date_damage) BETWEEN '$start_date' AND '$end_date'
        WHERE p.field_status = 0";
$inventory_movements_result = $db->query($inventory_movements_query);
$inventory_movements_data = $inventory_movements_result->fetch_assoc();

// Calculate period inventory change
$period_inventory_change = ($inventory_movements_data['purchases_value'] ?? 0) - ($inventory_movements_data['sales_value'] ?? 0) - ($inventory_movements_data['damage_value'] ?? 0);

// Damage losses
$damage_losses_query = "SELECT SUM(d.quantity_damage * p.selling_price) as total_damage_loss
                        FROM tbl_damage d
                        JOIN tbl_products p ON d.product_id = p.product_id
                        WHERE DATE(d.date_damage) BETWEEN '$start_date' AND '$end_date' AND d.field_status = 0";
$damage_result = $db->query($damage_losses_query);
$damage_data = $damage_result->fetch_assoc();

// Customer analysis
$customer_analysis_query = "SELECT c.name, COUNT(s.sales_id) as transaction_count, SUM(s.total_amount) as total_spent
                            FROM tbl_sales s
                            JOIN tbl_customer c ON s.cust_id = c.cust_id
                            WHERE DATE(s.sales_date) BETWEEN '$start_date' AND '$end_date' AND s.field_status = 0
                            GROUP BY c.cust_id, c.name
                            ORDER BY total_spent DESC
                            LIMIT 10";
$customer_analysis_result = $db->query($customer_analysis_query);
?>

<style>
    .financial-metric {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .metric-value {
        font-size: 2.5em;
        font-weight: bold;
        margin: 10px 0;
    }
    
    .metric-label {
        font-size: 0.9em;
        opacity: 0.9;
    }
    
    .profit-positive {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .profit-negative {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    }
    
    .panel {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .panel-heading {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }
    
    .panel-body {
        padding: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .date-filter {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .chart-container {
        height: 300px;
        margin: 20px 0;
    }
    
    .progress-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 5px 0;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.3s ease;
    }
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
</style>

<body class="layout-boxed navbar-top">

    <div class="navbar navbar-inverse bg-teal-400 navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../images/main_logo.jpg" alt="Logo">
                <span>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</span>
            </a>
        </div>
        <div class="navbar-collapse collapse" id="navbar-mobile">
            <?php require('includes/sidebar.php'); ?>
        </div>
    </div>

<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="page-header page-header-default">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Financial Report</span></h4>
                    </div>
                </div>
                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="index.php"><i class="icon-home2 position-left"></i> Dashboard</a></li>
                        <li class="active"><i class="icon-calculator position-left"></i> Financial Report</li>
                    </ul>
                </div>
            </div>
            
            <div class="content">

<!-- Database Status Notification -->
<?php if (!$cooperative_tables_exist): ?>
<div class="alert alert-warning" style="margin-bottom: 20px;">
    <h4><i class="fa fa-exclamation-triangle"></i> Cooperative Tables Not Found</h4>
    <p>The cooperative-specific tables (accounts, transactions) are not currently in your database. The report shows business operations only.</p>
    <p><strong>Expected table structure:</strong></p>
    <ul>
        <li><code>accounts</code> - Member accounts (savings, loans, capital shares)</li>
        <li><code>account_types</code> - Account type definitions</li>
        <li><code>transactions</code> - All financial transactions</li>
        <li><code>transaction_types</code> - Transaction type definitions</li>
    </ul>
    <p><strong>Transaction types needed:</strong> deposit, withdrawal, capital_share, loan_release, loan_payment, cancelled_loan</p>
</div>
<?php else: ?>
<div class="alert alert-success" style="margin-bottom: 20px;">
    <h4><i class="fa fa-check-circle"></i> Full Cooperative Mode Active</h4>
    <p>All cooperative financial components are available including capital shares, loans, and savings through the accounts and transactions system.</p>
</div>
<?php endif; ?>


<!-- Key Financial Metrics -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">₱<?php echo number_format($total_cooperative_revenue, 2); ?></div>
            <div class="metric-label">Business + Interest Income</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="metric-label">Total Expenses</div>
            <div class="metric-value">₱<?php echo number_format($total_cooperative_expenses, 2); ?></div>
            <div class="metric-label">Operations + Disbursements</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric <?php echo $cooperative_profit_loss >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
            <div class="metric-label"><?php echo $cooperative_profit_loss >= 0 ? 'Net Profit' : 'Net Loss'; ?></div>
            <div class="metric-value">₱<?php echo number_format(abs($cooperative_profit_loss), 2); ?></div>
            <div class="metric-label">Cooperative Operations</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="metric-label">Net Assets</div>
            <div class="metric-value">₱<?php echo number_format($net_assets, 2); ?></div>
            <div class="metric-label">Total Financial Position</div>
        </div>
    </div>
</div>

<!-- Cooperative-Specific Metrics -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="metric-label">Total Capital Shares</div>
            <div class="metric-value">₱<?php echo number_format($total_capital_data['total_capital_all'] ?? 0, 2); ?></div>
            <div class="metric-label"><?php echo $total_capital_data['total_members_all'] ?? 0; ?> members</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="metric-label">Savings Deposits</div>
            <div class="metric-value">₱<?php echo number_format($current_balances_data['total_current_balance'] ?? 0, 2); ?></div>
            <div class="metric-label"><?php echo $current_balances_data['active_accounts'] ?? 0; ?> active accounts</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="metric-label">Loan Portfolio</div>
            <div class="metric-value">₱<?php echo number_format($loan_portfolio_data['total_approved_loans'] ?? 0, 2); ?></div>
            <div class="metric-label"><?php echo $loan_portfolio_data['approved_count'] ?? 0; ?> active loans</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="metric-label">Interest Income</div>
            <div class="metric-value">₱<?php echo number_format($loan_repayments_data['total_interest'] ?? 0, 2); ?></div>
            <div class="metric-label">Loan Operations</div>
        </div>
    </div>
</div>


<!-- Date Filter -->
<div class="date-filter">
    <form method="GET" class="form-inline">
        <div class="form-group mr-3">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control ml-2" value="<?php echo $start_date; ?>">
        </div>
        <div class="form-group mr-3">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control ml-2" value="<?php echo $end_date; ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-filter"></i> Filter Report
        </button>
        <button type="button" class="btn btn-secondary ml-2" onclick="clearFilter()">
            <i class="fa fa-times"></i> Clear Filter
        </button>
        <a href="generate_financial_pdf.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger ml-2" target="_blank">
            <i class="fa fa-file-pdf-o"></i> Export PDF
        </a>
        <a href="generate_financial_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success ml-2">
            <i class="fa fa-file-excel-o"></i> Export Excel
        </a>
    </form>
</div>


<!-- Cooperative Financial Panels -->
<div class="row">
    <!-- Capital Shares & Members -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-users"></i> Capital Shares & Members</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Total Capital Shares (All Time)</strong></td>
                        <td>₱<?php echo number_format($total_capital_data['total_capital_all'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Period Contributions</strong></td>
                        <td>₱<?php echo number_format($capital_data['total_capital'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Members</strong></td>
                        <td><?php echo $total_capital_data['total_members_all'] ?? 0; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active Contributors</strong></td>
                        <td><?php echo $capital_data['total_members'] ?? 0; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Average Share per Member</strong></td>
                        <td>₱<?php echo ($total_capital_data['total_members_all'] ?? 0) > 0 ? number_format(($total_capital_data['total_capital_all'] ?? 0) / $total_capital_data['total_members_all'], 2) : '0.00'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Loan Portfolio Summary -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-handshake-o"></i> Loan Portfolio Summary</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Total Approved Loans</strong></td>
                        <td>₱<?php echo number_format($loan_portfolio_data['total_approved_loans'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pending Applications</strong></td>
                        <td>₱<?php echo number_format($loan_portfolio_data['total_pending_loans'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Disbursed</strong></td>
                        <td>₱<?php echo number_format($loan_disbursements_data['total_disbursed'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Repayments</strong></td>
                        <td>₱<?php echo number_format($loan_repayments_data['total_repaid'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Interest Income</strong></td>
                        <td style="color: green;">₱<?php echo number_format($loan_repayments_data['total_interest'] ?? 0, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Savings & Cash Management -->
<div class="row">
    <!-- Savings/Deposits -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-piggy-bank"></i> Savings & Deposits</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Total Current Balances</strong></td>
                        <td>₱<?php echo number_format($current_balances_data['total_current_balance'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Period Deposits</strong></td>
                        <td style="color: green;">+₱<?php echo number_format($deposits_data['total_savings'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Period Withdrawals</strong></td>
                        <td style="color: red;">₱<?php echo number_format(abs($deposits_data['total_withdrawals'] ?? 0), 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active Accounts</strong></td>
                        <td><?php echo $current_balances_data['active_accounts'] ?? 0; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Average Balance per Account</strong></td>
                        <td>₱<?php echo ($current_balances_data['active_accounts'] ?? 0) > 0 ? number_format(($current_balances_data['total_current_balance'] ?? 0) / $current_balances_data['active_accounts'], 2) : '0.00'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cash & Fund Management -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-money"></i> Cash & Fund Management</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Beginning Cash</strong></td>
                        <td>₱<?php echo number_format($beginning_cash_data['total_beginning_cash'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Loan Fund Balance</strong></td>
                        <td>₱<?php echo number_format($loan_fund_data['total_loan_fund_balance'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Inventory Value</strong></td>
                        <td>₱<?php echo number_format($inventory_data['total_inventory_value'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Outstanding Loans</strong></td>
                        <td style="color: orange;">₱<?php echo number_format(($loan_disbursements_data['total_disbursed'] ?? 0) - ($loan_repayments_data['total_principal'] ?? 0), 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Net Financial Position</strong></td>
                        <td style="font-weight: bold; color: <?php echo $net_assets >= 0 ? 'green' : 'red'; ?>;">₱<?php echo number_format($net_assets, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Financial Panels -->
<div class="row">
    <!-- Revenue Breakdown -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-money"></i> Revenue Breakdown</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Sales Revenue</strong></td>
                        <td>₱<?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Transactions</strong></td>
                        <td><?php echo $sales_data['total_transactions'] ?? 0; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Average Transaction Value</strong></td>
                        <td>₱<?php echo number_format(($sales_data['total_sales'] ?? 0) / max(1, $sales_data['total_transactions']), 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Damage Losses</strong></td>
                        <td style="color: red;">-₱<?php echo number_format($damage_data['total_damage_loss'] ?? 0, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Expense Breakdown -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-credit-card"></i> Expense Breakdown</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Operating Expenses</strong></td>
                        <td>₱<?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Purchase Costs</strong></td>
                        <td>₱<?php echo number_format($purchases_data['total_purchases'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Inventory Cost Value</strong></td>
                        <td>₱<?php echo number_format($inventory_data['total_cost_value'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Expense Count</strong></td>
                        <td><?php echo ($expenses_data['total_expense_count'] ?? 0) + ($purchases_data['total_purchase_count'] ?? 0); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Products and Customers -->
<div class="row">
    <!-- Top Selling Products -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-star"></i> Top Selling Products</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Revenue</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_revenue = 0;
                            $products_data = [];
                            while($row = $top_products_result->fetch_assoc()) {
                                $products_data[] = $row;
                                if($row['total_revenue'] > $max_revenue) $max_revenue = $row['total_revenue'];
                            }
                            
                            foreach($products_data as $product): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo $product['total_quantity']; ?></td>
                                <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $max_revenue > 0 ? ($product['total_revenue'] / $max_revenue) * 100 : 0; ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Customers -->
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-users"></i> Top Customers</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Transactions</th>
                                <th>Total Spent</th>
                                <th>Avg. Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $customers_data = [];
                            while($row = $customer_analysis_result->fetch_assoc()) {
                                $customers_data[] = $row;
                            }
                            
                            foreach($customers_data as $customer): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo $customer['transaction_count']; ?></td>
                                <td>₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                                <td>₱<?php echo number_format($customer['total_spent'] / max(1, $customer['transaction_count']), 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend and Expense Categories -->
<div class="row">
    <!-- Monthly Sales Trend -->
    <div class="col-lg-8">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-line-chart"></i> Monthly Sales Trend</h4>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Sales</th>
                                <th>Transactions</th>
                                <th>Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $monthly_data = [];
                            while($row = $monthly_sales_result->fetch_assoc()) {
                                $monthly_data[] = $row;
                            }
                            
                            foreach($monthly_data as $month): 
                            ?>
                            <tr>
                                <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                <td>₱<?php echo number_format($month['monthly_sales'], 2); ?></td>
                                <td><?php echo $month['monthly_transactions']; ?></td>
                                <td>₱<?php echo number_format($month['monthly_sales'] / max(1, $month['monthly_transactions']), 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expense Categories -->
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-pie-chart"></i> Expense Categories</h4>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $expense_data = [];
                            while($row = $expense_categories_result->fetch_assoc()) {
                                $expense_data[] = $row;
                            }
                            
                            foreach($expense_data as $expense): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td>₱<?php echo number_format($expense['category_total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Ratios -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-heading">
                <h4><i class="fa fa-calculator"></i> Financial Ratios & KPIs</h4>
            </div>
            <div class="panel-body">
                <?php 
                // Define variables for financial ratios
                $revenue = $total_cooperative_revenue;
                $profit_loss = $cooperative_profit_loss;
                $cogs = $purchases_data['total_purchases'] ?? 0;
                ?>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Gross Profit Margin:</strong>
                        <p class="text-primary"><?php echo $revenue > 0 ? number_format((($revenue - ($purchases_data['total_purchases'] ?? 0)) / $revenue) * 100, 1) : 0; ?>%</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Net Profit Margin:</strong>
                        <p class="<?php echo $profit_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) : 0; ?>%
                        </p>
                    </div>
                    <div class="col-md-3">
                        <strong>Inventory Turnover:</strong>
                        <p class="text-info"><?php 
                        $inventory_cost = $inventory_data['total_cost_value'] ?? 0;
                        $cogs = $purchases_data['total_purchases'] ?? 0;
                        echo $inventory_cost > 0 ? number_format($cogs / $inventory_cost, 2) : 'N/A'; 
                        ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Return on Sales:</strong>
                        <p class="<?php echo $profit_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $revenue > 0 ? number_format(($profit_loss / $revenue) * 100, 1) : 0; ?>%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
        </div>
    </div>
</div>

<?php require('includes/footer-text.php'); ?>
</body>


<?php require('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Clear Filter Function
function clearFilter() {
    // Set default dates (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // Format dates as YYYY-MM-DD
    const startDate = firstDay.toISOString().split('T')[0];
    const endDate = today.toISOString().split('T')[0];
    
    // Set input values
    document.getElementById('start_date').value = startDate;
    document.getElementById('end_date').value = endDate;
    
    // Submit form to refresh with default dates
    window.location.href = 'financial_report.php?start_date=' + startDate + '&end_date=' + endDate;
}

// Monthly Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
        datasets: [{
            label: 'Monthly Sales',
            data: <?php echo json_encode(array_column($monthly_data, 'monthly_sales')); ?>,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Expense Categories Chart
const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($expense_data, 'description')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($expense_data, 'category_total')); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40',
                '#FF6384',
                '#C9CBCF',
                '#4BC0C0',
                '#FF6384'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
