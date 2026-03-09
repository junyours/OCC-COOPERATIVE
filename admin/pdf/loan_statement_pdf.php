<?php
require('fpdf.php');

class LoanStatementPDF extends FPDF
{
    private $loanData;
    private $memberData;
    private $transactions;
    private $password;
    private $summary;

    public function __construct($loanData, $memberData, $transactions, $summary, $password = null)
    {
        parent::__construct();
        $this->loanData = $loanData;
        $this->memberData = $memberData;
        $this->transactions = $transactions;
        $this->summary = $summary;
        $this->password = $password;
    }
    
    function Header()
    {
        // Compact header with logo
        $this->SetFillColor(41, 128, 185); // Professional blue
        $this->Rect(0, 0, 210, 25, 'F');
        
        // // Add logo (if available)
        // if (file_exists('../../images/main_logo.jpg')) {
        //     $this->Image('../../images/main_logo.jpg', 85, 5, 30, 30, 'JPG');
        // } elseif (file_exists('../images/main_logo.jpg')) {
        //     $this->Image('../images/main_logo.jpg', 85, 5, 30, 30, 'JPG');
        // }
        
        // White text for header
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Statement of Account', 0, 1, 'C');
        
        // Reset text color
        $this->SetTextColor(0, 0, 0);
        
        // Add date and statement number
        $this->SetFont('Arial', '', 9);
        $this->SetXY(140, 18);
        $this->Cell(60, 5, 'Statement Date: ' . date('F j, Y'), 0, 1, 'R');
        $this->SetXY(140, 23);
        $this->Cell(60, 5, 'Statement No: ' . date('Ymd') . '-' . $this->loanData['loan_id'], 0, 0, 'R');
        
        $this->Ln(5);
    }
    
    function Footer()
    {
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
        
        // Add contact info
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'For inquiries, contact: (088) 123-4567 | occ.coop@email.com', 0, 1, 'C');
    }
    
    function SectionTitle($title)
    {
        $this->Ln(3);
        $this->SetFillColor(41, 128, 185);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }
    
    function LabelValue($label, $value, $width = 60)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($width, 6, $label, 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, ': ' . $value, 0, 1, 'L');
    }
    
    function InfoBox($title, $data)
    {
        $this->SetFillColor(245, 245, 245);
        $this->Rect(20, $this->GetY(), 170, 6, 'F');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(170, 6, $title, 0, 1, 'L', true);
        
        $this->SetFillColor(250, 250, 250);
        $this->SetFont('Arial', '', 8);
        foreach ($data as $key => $value) {
            $this->Cell(60, 5, $key, 1, 0, 'L', true);
            $this->Cell(110, 5, $value, 1, 1, 'L', true);
        }
        $this->Ln(3);
    }
    
    function TransactionTableHeader()
    {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(22, 5, 'Date', 1, 0, 'C', true);
        $this->Cell(55, 5, 'Description', 1, 0, 'L', true);
        $this->Cell(22, 5, 'Ref', 1, 0, 'C', true);
        $this->Cell(28, 5, 'Debit', 1, 0, 'R', true);
        $this->Cell(28, 5, 'Credit', 1, 0, 'R', true);
        $this->Cell(28, 5, 'Balance', 1, 1, 'R', true);
    }
    
    function TransactionRow($transaction)
    {
        $this->SetFont('Arial', '', 7);
        $this->Cell(22, 4, date('M d, Y', strtotime($transaction['transaction_date'])), 1, 0, 'C');
        $this->Cell(55, 4, substr($transaction['remarks'], 0, 30), 1, 0, 'L');
        $this->Cell(22, 4, substr($transaction['reference_no'] ?? '-', 0, 10), 1, 0, 'C');
        $this->Cell(28, 4, $transaction['debit'] ? number_format($transaction['debit'], 2) : '', 1, 0, 'R');
        $this->Cell(28, 4, $transaction['credit'] ? number_format($transaction['credit'], 2) : '', 1, 0, 'R');
        $this->Cell(28, 4, number_format($transaction['balance'], 2), 1, 1, 'R');
    }
    
    function SummaryRow($label, $value, $isBold = false)
    {
        $this->SetFont($isBold ? 'Arial' : 'Arial', $isBold ? 'B' : '', 10);
        $this->Cell(140, 6, $label, 0, 0, 'R');
        $this->Cell(30, 6, number_format($value, 2), 0, 1, 'R');
    }
    
    function CreateLoanStatementPDF()
    {
        $this->AddPage();
        $this->AliasNbPages();
        
        // Loan Information Section
        $this->SectionTitle('LOAN INFORMATION');
        
        $loanInfo = [
            'Loan No' => 'LN' . str_pad($this->loanData['loan_id'], 6, '0', STR_PAD_LEFT),
            'Loan Type' => $this->loanData['loan_type_name'],
            'Date Released' => $this->loanData['released_date'] ? date('F j, Y', strtotime($this->loanData['released_date'])) : 'Not Released',
            'Maturity Date' => 'N/A', // Calculate if needed or remove if not available
            'Status' => strtoupper($this->loanData['status'])
        ];
        $this->InfoBox('Loan Details', $loanInfo);
        
        // Member Information Section
        $this->SectionTitle('MEMBER INFORMATION');
        
        $memberInfo = [
            'Member Name' => $this->memberData['full_name'] ?? 'N/A',
            'Member ID' => $this->memberData['member_id'] ?? 'N/A',
            'Contact Number' => $this->memberData['phone'] ?? 'N/A'
        ];
        $this->InfoBox('Member Details', $memberInfo);
        
        // Loan Terms Section
        $this->SectionTitle('LOAN TERMS');
        
        $loanTerms = [
            'Principal Amount' => number_format($this->loanData['approved_amount'] ?? 0, 2),
            'Interest Rate' => ($this->loanData['interest_rate'] ?? 0) . '% per annum',
            'Term' => ($this->loanData['term_value'] ?? 0) . ' ' . ($this->loanData['term_unit'] ?? 'month') . '(s)',
            'Payment Frequency' => ucfirst($this->loanData['payment_frequency'] ?? 'monthly'),
            'Monthly Amortization' => number_format($this->summary['monthly_amortization'] ?? 0, 2)
        ];
        $this->InfoBox('Loan Terms & Conditions', $loanTerms);
        
        // Financial Summary Section
        $this->SectionTitle('FINANCIAL SUMMARY');
        
        $financialSummary = [
            'Original Principal' => number_format($this->summary['principal_amount'] ?? 0, 2),
            'Total Interest' => number_format($this->summary['total_interest'] ?? 0, 2),
            'Service Charge' => number_format($this->summary['service_charge'] ?? 0, 2),
            'Insurance' => number_format($this->summary['insurance'] ?? 0, 2),
            'Doc Stamp Fee' => number_format($this->summary['doc_stamp_fee'] ?? 0, 2),
            'Total Payments Received' => number_format($this->summary['total_payments'] ?? 0, 2),
            'Penalties Charged' => number_format($this->summary['total_penalties'] ?? 0, 2),
            'Outstanding Balance' => number_format(($this->summary['principal_amount'] ?? 0) + ($this->summary['total_interest'] ?? 0) + ($this->summary['service_charge'] ?? 0) + ($this->summary['insurance'] ?? 0) + ($this->summary['doc_stamp_fee'] ?? 0) - ($this->summary['total_payments'] ?? 0), 2)
        ];
        $this->InfoBox('Financial Summary', $financialSummary);
        
        // Transaction History Section
        $this->SectionTitle('TRANSACTION HISTORY');
        
        // Add table header
        $this->TransactionTableHeader();
        
        // Add transactions
        if (!empty($this->transactions)) {
            foreach ($this->transactions as $transaction) {
                $this->TransactionRow($transaction);
            }
        } else {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 8, 'No transactions found for this loan.', 1, 1, 'C');
        }
        
        // Payment Information Section
        $this->Ln(10);
        $this->SectionTitle('PAYMENT INFORMATION');
        
        $this->SetFont('Arial', '', 9);
        $paymentInfo = [
            'Accepted Payment Methods:',
            '- Cash payments at cooperative office',
            '- Online transfer (if available)',
            '',
            'Payment Schedule:',
            '- Due on the ' . ucfirst($this->loanData['payment_frequency'] ?? 'monthly') . ' basis',
            '- Late payments incur penalties as per cooperative policy',
            '- Early payments are accepted and encouraged'
        ];
        
        foreach ($paymentInfo as $info) {
            $this->Cell(10, 5, '', 0, 0);
            $this->MultiCell(180, 5, $info, 0, 'L');
            $this->Ln(2);
        }
        
        // Contact Information
        $this->Ln(10);
        $this->SectionTitle('CONTACT INFORMATION');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, 'For any inquiries regarding your loan statement, please contact:', 0, 1, 'L');
        $this->Ln(3);
        $this->Cell(20, 5, '', 0, 0);
        $this->Cell(0, 5, '- Office: OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'L');
        $this->Cell(20, 5, '', 0, 0);
        $this->Cell(0, 5, '- Phone: (09958458761', 0, 1, 'L');
        $this->Cell(20, 5, '', 0, 0);
        $this->Cell(0, 5, '- Email: occ.coop@email.com', 0, 1, 'L');
        $this->Cell(20, 5, '', 0, 0);
        $this->Cell(0, 5, '- Office Hours: Monday to Friday, 8:00 AM - 5:00 PM', 0, 1, 'L');
        
    }

}

// Function to generate loan statement PDF
function generateLoanStatementPDF($loan_id, $db)
{
    // Get loan details with loan type
    $loanQuery = "
        SELECT l.*, lt.loan_type_name
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
        WHERE l.loan_id = ?
    ";
    $stmt = $db->prepare($loanQuery);
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $loanData = $stmt->get_result()->fetch_assoc();
    
    if (!$loanData) {
        throw new Exception("Loan not found");
    }
    
    // Get member information
    $memberQuery = "
        SELECT m.member_id, m.first_name, m.last_name, m.middle_name, 
               m.phone, m.address,
               CONCAT(m.first_name, ' ', IF(m.middle_name != '', CONCAT(m.middle_name, ' '), ''), m.last_name) as full_name
        FROM tbl_members m
        JOIN accounts a ON m.member_id = a.member_id
        WHERE a.account_id = ?
    ";
    $stmt = $db->prepare($memberQuery);
    $stmt->bind_param("i", $loanData['account_id']);
    $stmt->execute();
    $memberData = $stmt->get_result()->fetch_assoc();
    
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
    
    // Calculate totals and outstanding balance
    $total_loan_amount = $summaryResult['principal_amount'] + $summaryResult['total_interest'] + 
                         $summaryResult['service_charge'] + $summaryResult['insurance'] + $summaryResult['doc_stamp_fee'];
    $outstanding_balance = $total_loan_amount - $summaryResult['total_payments'];
    
    $summary = [
        'monthly_amortization' => $summaryResult['monthly_amortization'],
        'total_interest' => $summaryResult['total_interest'],
        'service_charge' => $summaryResult['service_charge'],
        'insurance' => $summaryResult['insurance'],
        'doc_stamp_fee' => $summaryResult['doc_stamp_fee'],
        'total_loan_amount' => $total_loan_amount,
        'total_payments' => $summaryResult['total_payments'],
        'total_penalties' => $summaryResult['total_penalties'],
        'outstanding_balance' => max(0, $outstanding_balance)
    ];
    
    // Create PDF
    $pdf = new LoanStatementPDF($loanData, $memberData, $transactions, $summary);
    $pdf->CreateLoanStatementPDF();
    
    // Output PDF
    $filename = 'loan_statement_' . $loan_id . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D'); // D for download, I for inline display
    
    return $filename;
}
?>
