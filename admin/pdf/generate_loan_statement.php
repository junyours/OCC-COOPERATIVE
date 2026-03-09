<?php
require_once('../db_connect.php');
require_once('loan_statement_pdf.php');

// Start output buffering to prevent headers issues
ob_start();

// Get loan ID from URL
$loan_id = isset($_GET['loan_id']) ? $_GET['loan_id'] : 0;

if (!$loan_id) {
    die("Loan ID is required");
}

// Get loan details with member information
$loanQuery = "
    SELECT l.*, lt.loan_type_name, m.first_name, m.last_name, m.middle_name, m.email, m.phone, m.address,
           CONCAT(m.first_name, ' ', IF(m.middle_name != '', CONCAT(m.middle_name, ' '), ''), m.last_name) as full_name,
           a.account_id
    FROM loans l
    LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
    LEFT JOIN accounts a ON l.account_id = a.account_id
    LEFT JOIN tbl_members m ON a.member_id = m.member_id
    WHERE l.loan_id = ?
";

$stmt = $db->prepare($loanQuery);   
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$loanData = $stmt->get_result()->fetch_assoc();

if (!$loanData) {
    die("Loan not found");
}

// Get loan transactions
$transactionQuery = "
    SELECT 
        t.transaction_date,
        t.remarks,
        t.reference_no,
        CASE 
            WHEN t.amount > 0 AND tt.type_name IN ('payment', 'principal_payment', 'interest_payment') THEN t.amount
            ELSE NULL
        END as credit,
        CASE 
            WHEN t.amount > 0 AND tt.type_name IN ('penalty', 'service_charge', 'interest_charge') THEN t.amount
            ELSE NULL
        END as debit,
        (
            SELECT SUM(CASE 
                WHEN t2.amount > 0 AND tt2.type_name IN ('payment', 'principal_payment', 'interest_payment') THEN t2.amount
                ELSE -t2.amount
            END)
            FROM transactions t2
            JOIN transaction_types tt2 ON t2.transaction_type_id = tt2.transaction_type_id
            WHERE t2.account_id = ? AND t2.transaction_date <= t.transaction_date
        ) as balance
    FROM transactions t
    JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
    WHERE t.account_id = ?
    ORDER BY t.transaction_date ASC, t.transaction_id ASC
";

$stmt = $db->prepare($transactionQuery);
$stmt->bind_param("ii", $loanData['account_id'], $loanData['account_id']);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate summary
$summaryQuery = "
    SELECT 
        COALESCE(SUM(CASE WHEN tt.type_name IN ('payment', 'principal_payment', 'interest_payment') THEN t.amount ELSE 0 END), 0) as total_payments,
        COALESCE(SUM(CASE WHEN tt.type_name = 'penalty' THEN t.amount ELSE 0 END), 0) as total_penalties,
        COALESCE(l.approved_amount, 0) as principal_amount,
        COALESCE(l.total_interest, 0) as total_interest,
        COALESCE(l.service_charge, 0) as service_charge,
        COALESCE(l.insurance, 0) as insurance,
        COALESCE(l.doc_stamp_fee, 0) as doc_stamp_fee,
        (l.approved_amount + COALESCE(l.total_interest, 0)) / 
            CASE l.term_unit 
                WHEN 'month' THEN l.term_value 
                WHEN 'year' THEN l.term_value * 12 
                ELSE l.term_value 
            END as monthly_amortization
    FROM loans l
    LEFT JOIN transactions t ON l.account_id = t.account_id
    LEFT JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
    WHERE l.loan_id = ?
    GROUP BY l.loan_id
";

$stmt = $db->prepare($summaryQuery);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$summaryResult = $stmt->get_result()->fetch_assoc();

// Calculate totals
$total_loan_amount = ($summaryResult['principal_amount'] ?? 0) + ($summaryResult['total_interest'] ?? 0) + 
                     ($summaryResult['service_charge'] ?? 0) + ($summaryResult['insurance'] ?? 0) + ($summaryResult['doc_stamp_fee'] ?? 0);
$outstanding_balance = $total_loan_amount - ($summaryResult['total_payments'] ?? 0);

$summary = [
    'monthly_amortization' => $summaryResult['monthly_amortization'] ?? 0,
    'total_interest' => $summaryResult['total_interest'] ?? 0,
    'service_charge' => $summaryResult['service_charge'] ?? 0,
    'insurance' => $summaryResult['insurance'] ?? 0,
    'doc_stamp_fee' => $summaryResult['doc_stamp_fee'] ?? 0,
    'principal_amount' => $summaryResult['principal_amount'] ?? 0,
    'total_loan_amount' => $total_loan_amount,
    'total_payments' => $summaryResult['total_payments'] ?? 0,
    'total_penalties' => $summaryResult['total_penalties'] ?? 0,
    'outstanding_balance' => max(0, $outstanding_balance)
];

// Create PDF statement
$pdf = new LoanStatementPDF($loanData, $loanData, $transactions, $summary);
$pdf->CreateLoanStatementPDF();

// Clean output buffer and send PDF
ob_end_clean();

// Output PDF for download
$filename = 'loan_statement_' . $loan_id . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');
?>
