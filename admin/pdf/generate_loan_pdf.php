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

// Get comaker information if exists
$comakerQuery = "
    SELECT lc.*, cm.first_name as comaker_first_name, cm.last_name as comaker_last_name, 
           cm.middle_name as comaker_middle_name, cm.phone as comaker_phone, 
           cm.address as comaker_address, cm.email as comaker_email,
           CONCAT(cm.first_name, ' ', IF(cm.middle_name != '', CONCAT(cm.middle_name, ' '), ''), cm.last_name) as comaker_full_name
    FROM loan_comaker lc
    LEFT JOIN tbl_members cm ON lc.comaker_member_id = cm.member_id
    WHERE lc.loan_id = ? AND lc.status = 'pending'
";

$stmt = $db->prepare($comakerQuery);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$comakerData = $stmt->get_result()->fetch_assoc();

// Create loan application PDF
class LoanApplicationPDF extends FPDF
{
    private $loanData;
    private $memberData;
    private $comakerData;

    public function __construct($loanData, $comakerData = null)
    {
        parent::__construct();
        $this->loanData = $loanData;
        $this->memberData = [
            'full_name' => $loanData['full_name'] ?? 'N/A',
            'phone' => $loanData['phone'] ?? 'N/A',
            'address' => $loanData['address'] ?? 'N/A',
            'email' => $loanData['email'] ?? 'N/A'
        ];
        $this->comakerData = $comakerData;
    }

    public function CreateLoanApplicationPDF()
    {
        $this->AddPage();
        
        // Compact Professional Header
        $this->SetFillColor(26, 69, 138); // Professional blue
        $this->Rect(0, 0, 210, 25, 'F');
        
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'LOAN APPLICATION FORM', 0, 1, 'C');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'Application No: ' . str_pad($this->loanData['loan_id'] ?? 0, 6, '0', STR_PAD_LEFT), 0, 1, 'C');

        
    
        // Application Details Section
        $this->SetY(30);
        $this->SectionHeader('APPLICATION DETAILS');
        
        $this->SetFont('Arial', '', 9);
        $this->TwoColumn('Application Date:', date('F d, Y', strtotime($this->loanData['application_date'] ?? 'now')));
        $this->TwoColumn('Loan Type:', $this->loanData['loan_type_name'] ?? 'N/A');
        $this->TwoColumn('Purpose:', $this->loanData['purpose'] ?? 'Not specified');
        
        // Financial Details Section
        $this->Ln(5);
        $this->SectionHeader('FINANCIAL DETAILS');
        
        $this->SetFont('Arial', '', 9);
        $this->TwoColumn('Requested Amount:', 'PHP ' . number_format($this->loanData['requested_amount'] ?? 0, 2));
        $this->TwoColumn('Approved Amount:', 'PHP ' . number_format($this->loanData['approved_amount'] ?? 0, 2));
        $this->TwoColumn('Interest Rate:', ($this->loanData['interest_rate'] ?? 0) . '% per annum');
        $this->TwoColumn('Term:', ($this->loanData['term_value'] ?? 0) . ' ' . ($this->loanData['term_unit'] ?? 'month') . '(s)');
        $this->TwoColumn('Payment Frequency:', ucfirst($this->loanData['payment_frequency'] ?? 'monthly'));
        
        // Applicant Information Section
        $this->Ln(5);
        $this->SectionHeader('APPLICANT INFORMATION');
        
        $this->SetFont('Arial', '', 9);
        $this->TwoColumn('Full Name:', $this->memberData['full_name']);
        $this->TwoColumn('Contact Number:', $this->memberData['phone']);
        $this->TwoColumn('Email Address:', $this->memberData['email']);
        
        // Address (full width)
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(30, 5, 'Address:', 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(0, 5, $this->memberData['address'], 0, 'L');
        
        // Comaker Information Section (if exists)
        if ($this->comakerData) {
            $this->Ln(5);
            $this->SectionHeader('COMAKER INFORMATION');
            
            $this->SetFont('Arial', '', 9);
            $this->TwoColumn('Full Name:', $this->comakerData['comaker_full_name'] ?? 'N/A');
            $this->TwoColumn('Contact Number:', $this->comakerData['comaker_phone'] ?? 'N/A');
            $this->TwoColumn('Email Address:', $this->comakerData['comaker_email'] ?? 'N/A');
            
            // Comaker Address (full width)
            $this->Ln(2);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(30, 5, 'Address:', 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->MultiCell(0, 5, $this->comakerData['comaker_address'] ?? 'N/A', 0, 'L');
        }
        
        // Terms and Conditions Box
        $this->Ln(5);
        $this->SetFillColor(240, 240, 240);
        $this->Rect(10, $this->GetY(), 190, 25, 'F');
        $this->SetY($this->GetY() + 3);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'TERMS AND CONDITIONS:', 0, 1, 'L');
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(0, 3, '1. I certify that all information provided is true and correct.\n2. I agree to abide by cooperative\'s loan policies and procedures.\n3. I authorize the cooperative to verify all information provided.\n4. I understand that any false information may result in loan denial.', 0, 'L');
        
        // Signature Section
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, 'SIGNATURES', 0, 1, 'L');
        
        // Applicant Signature
        $this->Ln(8);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'APPLICANT:', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(60, 5, '_____________________________', 0, 0, 'L');
        $this->Cell(5, 5, '', 0, 0, 'C');
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(55, 5, 'Signature over Printed Name', 0, 0, 'L');
        $this->Cell(40, 5, '', 0, 0, 'C');
        $this->Cell(50, 5, '_____________________________', 0, 0, 'R');
        $this->Ln(4);
        $this->Cell(60, 4, '', 0, 0, 'L');
        $this->Cell(55, 4, '', 0, 0, 'L');
        $this->Cell(40, 4, '', 0, 0, 'C');
        $this->SetFont('Arial', '', 8);
        $this->Cell(50, 4, 'Date', 0, 1, 'R');
        
        // Comaker Signature (if exists)
        if ($this->comakerData) {
            $this->Ln(8);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 4, 'COMAKER:', 0, 1, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(60, 5, '_____________________________', 0, 0, 'L');
            $this->Cell(5, 5, '', 0, 0, 'C');
            $this->SetFont('Arial', 'I', 7);
            $this->Cell(55, 5, 'Signature over Printed Name', 0, 0, 'L');
            $this->Cell(40, 5, '', 0, 0, 'C');
            $this->Cell(50, 5, '_____________________________', 0, 0, 'R');
            $this->Ln(4);
            $this->Cell(60, 4, '', 0, 0, 'L');
            $this->Cell(55, 4, '', 0, 0, 'L');
            $this->Cell(40, 4, '', 0, 0, 'C');
            $this->SetFont('Arial', '', 8);
            $this->Cell(50, 4, 'Date', 0, 1, 'R');
        }
        
        // Footer
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 3, 'OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE', 0, 1, 'C');

    }
    
    private function SectionHeader($title)
    {
        $this->SetFillColor(26, 69, 138);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }
    
    private function TwoColumn($label, $value)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(55, 5, $label, 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $value, 0, 1, 'L');
    }
    
    private function getStatusColor($status)
    {
        switch(strtolower($status)) {
            case 'pending': return [255, 193, 7];    // Yellow
            case 'approved': return [40, 167, 69];    // Green
            case 'ongoing': return [23, 162, 184];    // Cyan
            case 'completed': return [102, 16, 242];  // Purple
            case 'overdue': return [220, 53, 69];     // Red
            case 'paid': return [40, 167, 69];        // Green
            case 'rejected': return [108, 117, 125];  // Gray
            default: return [108, 117, 125];          // Gray
        }
    }
}

// Create and output PDF
$pdf = new LoanApplicationPDF($loanData, $comakerData);
$pdf->CreateLoanApplicationPDF();

// Clean output buffer and send PDF
ob_end_clean();

// Output PDF for download
$filename = 'loan_application_' . $loan_id . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');
?>
