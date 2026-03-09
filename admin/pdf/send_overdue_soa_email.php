<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off display errors for JSON responses
ob_start(); // Start output buffering

require_once('../db_connect.php');
require_once('../../PHPMailer/src/PHPMailer.php');
require_once('../../PHPMailer/src/SMTP.php');
require_once('../../PHPMailer/src/Exception.php');
require_once('email_config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get email configuration
$emailConfig = require('email_config.php');

function sendSOAEmail($loan_id, $db, $emailConfig, $urgent_overdue = false) {
    try {
        // Get loan details with member information
        $loanQuery = "
            SELECT l.*, lt.loan_type_name, m.first_name, m.last_name, m.middle_name, m.email,
                   CONCAT(m.first_name, ' ', IF(m.middle_name != '', CONCAT(m.middle_name, ' '), ''), m.last_name) as full_name,
                   a.account_id
            FROM loans l
            LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
            LEFT JOIN accounts a ON l.account_id = a.account_id
            LEFT JOIN tbl_members m ON a.member_id = m.member_id
            WHERE l.loan_id = ? AND (l.status = 'overdue' OR l.status = 'ongoing')
        ";
        
        $stmt = $db->prepare($loanQuery);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        $loanData = $stmt->get_result()->fetch_assoc();
        
        if (!$loanData) {
            return ['success' => false, 'message' => 'Overdue loan not found'];
        }
        
        if (empty($loanData['email'])) {
            return ['success' => false, 'message' => 'Member email not found'];
        }
        
        // Generate SOA PDF
        require_once('loan_statement_pdf.php');
        
        // Get loan details for PDF generation
        $loanQuery = "
            SELECT l.*, lt.loan_type_name
            FROM loans l
            LEFT JOIN loan_types lt ON l.loan_type_id = lt.loan_type_id
            WHERE l.loan_id = ?
        ";
        $stmt = $db->prepare($loanQuery);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        $loanDataForPdf = $stmt->get_result()->fetch_assoc();
        
        // Get member information for PDF
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
        $memberDataForPdf = $stmt->get_result()->fetch_assoc();
        
        // Get loan transactions for PDF
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
        $transactionsForPdf = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Calculate summary for PDF
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
        $summaryResultForPdf = $stmt->get_result()->fetch_assoc();
        
        // Calculate totals and outstanding balance for PDF
        $total_loan_amount_pdf = $summaryResultForPdf['principal_amount'] + $summaryResultForPdf['total_interest'] + 
                             $summaryResultForPdf['service_charge'] + $summaryResultForPdf['insurance'] + $summaryResultForPdf['doc_stamp_fee'];
        $outstanding_balance_pdf = $total_loan_amount_pdf - $summaryResultForPdf['total_payments'];
        
        $summaryForPdf = [
            'monthly_amortization' => $summaryResultForPdf['monthly_amortization'],
            'total_interest' => $summaryResultForPdf['total_interest'],
            'service_charge' => $summaryResultForPdf['service_charge'],
            'insurance' => $summaryResultForPdf['insurance'],
            'doc_stamp_fee' => $summaryResultForPdf['doc_stamp_fee'],
            'total_loan_amount' => $total_loan_amount_pdf,
            'total_payments' => $summaryResultForPdf['total_payments'],
            'total_penalties' => $summaryResultForPdf['total_penalties'],
            'outstanding_balance' => max(0, $outstanding_balance_pdf)
        ];
        
        // Create PDF
        $pdf = new LoanStatementPDF($loanDataForPdf, $memberDataForPdf, $transactionsForPdf, $summaryForPdf);
        $pdf->CreateLoanStatementPDF();
        
        // Save PDF to temporary file
        $filename = 'SOA_' . $loan_id . '_' . date('Y-m-d') . '.pdf';
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        $pdf->Output($tempPath, 'F');
        
        // Send email
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $emailConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $emailConfig['username'];
            $mail->Password   = $emailConfig['password'];
            $mail->SMTPSecure = $emailConfig['encryption']; // Using 'tls' 
            $mail->Port       = $emailConfig['port'];
            
            
            $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
            $mail->addAddress($loanData['email'], $loanData['full_name']);
            
           
            $mail->addCC($emailConfig['admin_email'], $emailConfig['admin_name']);
            
            
            $mail->addAttachment($tempPath, $filename);
            
            // Content
            $mail->isHTML(true);
            
            if ($urgent_overdue) {
                $mail->Subject = '🚨 URGENT: Immediate Payment Required - Overdue Loan #' . str_pad($loan_id, 6, '0', STR_PAD_LEFT);
            } else {
                $mail->Subject = 'URGENT: Overdue Loan Statement of Account - Loan #' . str_pad($loan_id, 6, '0', STR_PAD_LEFT);
            }
            
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <div style="background-color: ' . ($urgent_overdue ? '#dc3545' : '#26a69a') . '; color: white; padding: 20px; text-align: center;">
                        <h2 style="margin: 0;">OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE</h2>
                        <p style="margin: 5px 0;">Statement of Account - ' . ($urgent_overdue ? ' OVERDUE NOTICE' : 'OVERDUE NOTICE') . '</p>
                    </div>
                    
                    <div style="padding: 20px; background-color: #f9f9f9;">
                        <p>Dear <strong>' . htmlspecialchars($loanData['full_name']) . '</strong>,</p>
                        
                        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                            <h3 style="color: #856404; margin-top: 0;">⚠️ ' . ($urgent_overdue ? ' URGENT: IMMEDIATE PAYMENT REQUIRED' : 'OVERDUE LOAN NOTICE') . '</h3>
                            <p style="color: #856404; margin-bottom: 0;">' . ($urgent_overdue ? 'Your loan is seriously overdue. Immediate payment is required to avoid further penalties and potential collection action.' : 'Your loan is now overdue. Please settle your payment immediately to avoid penalties.') . '</p>
                        </div>
                        
                        <h3>Loan Details:</h3>
                        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Loan Number:</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">#' . str_pad($loan_id, 6, '0', STR_PAD_LEFT) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Loan Type:</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($loanData['loan_type_name']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Application Date:</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . date('F d, Y', strtotime($loanData['application_date'])) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Amount:</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . number_format($loanData['requested_amount'], 2) . '</td>
                            </tr>
                        </table>
                        
                        <p>Please find your detailed Statement of Account attached to this email.</p>
                        
                        <h3>Payment Options:</h3>
                        <ul>
                            <li>Cash payments at cooperative office</li>
                            <li>Bank deposit to cooperative account</li>
                            <li>Online transfer (if available)</li>
                        </ul>
                        
                        <h3>Contact Information:</h3>
                        <p>
                            <strong>Office:</strong> OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE<br>
                            <strong>Phone:</strong> (09958458761<br>
                            <strong>Email:</strong> coop.cooperative.06@gmail.com<br>
                            <strong>Office Hours:</strong> Monday to Friday, 8:00 AM - 5:00 PM
                        </p>
                        
                        <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 20px 0;">
                            <p style="color: #0c5460; margin: 0;"><strong>Please settle your payment ' . ($urgent_overdue ? 'IMMEDIATELY to avoid further penalties and collection action' : 'immediately to avoid additional penalties and maintain good standing') . ' with the cooperative.</strong></p>
                        </div>
                        
                        <p>Thank you for your prompt attention to this matter.</p>
                        
                        <p>Best regards,<br>
                        <strong>OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE</strong><br>
                        <em>Financial Services Department</em></p>
                    </div>
                    
                    <div style="background-color: #333; color: white; padding: 10px; text-align: center; font-size: 12px;">
                        <p style="margin: 0;">This is an automated message. Please do not reply to this email.</p>
                        <p style="margin: 5px 0;">© 2024 OPOL COMMUNITY COLLEGE EMPLOYEES CREDIT COOPERATIVE</p>
                    </div>
                </div>
            ';
            
            $mail->AltBody = 'Dear ' . $loanData['full_name'] . ', Your loan #' . str_pad($loan_id, 6, '0', STR_PAD_LEFT) . ' is overdue. Please find your Statement of Account attached and settle your payment immediately.';
            
            $mail->send();
            
            // Clean up temporary file
            unlink($tempPath);
            
            // Log the email sent
            $logQuery = "INSERT INTO email_logs (loan_id, member_email, email_type, sent_date, status) VALUES (?, ?, 'overdue_soa', NOW(), 'sent')";
            $logStmt = $db->prepare($logQuery);
            $logStmt->bind_param("is", $loan_id, $loanData['email']);
            $logStmt->execute();
            
            return ['success' => true, 'message' => 'SOA email sent successfully to ' . $loanData['email']];
            
        } catch (Exception $e) {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            // Log the failed email
            $logQuery = "INSERT INTO email_logs (loan_id, member_email, email_type, sent_date, status, error_message) VALUES (?, ?, 'overdue_soa', NOW(), 'failed', ?)";
            $logStmt = $db->prepare($logQuery);
            $errorMsg = $mail->ErrorInfo;
            $logStmt->bind_param("iss", $loan_id, $loanData['email'], $errorMsg);
            $logStmt->execute();
            
            return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Send email for specific loan ID
if (isset($_GET['loan_id'])) {
    $loan_id = $_GET['loan_id'];
    $urgent_overdue = isset($_GET['urgent_overdue']) && $_GET['urgent_overdue'] == 'true';
    $result = sendSOAEmail($loan_id, $db, $emailConfig, $urgent_overdue);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message']]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
}

// Send emails to all overdue and ongoing loans
if (isset($_GET['send_all_overdue']) && $_GET['send_all_overdue'] == 'true') {
    // Get all overdue and ongoing loans
    $overdueQuery = "
        SELECT l.loan_id, m.email, m.first_name, m.last_name, l.status
        FROM loans l
        LEFT JOIN accounts a ON l.account_id = a.account_id
        LEFT JOIN tbl_members m ON a.member_id = m.member_id
        WHERE (l.status = 'overdue' OR l.status = 'ongoing') AND m.email IS NOT NULL AND m.email != ''
    ";
    
    $overdueResult = $db->query($overdueQuery);
    $sentCount = 0;
    $failedCount = 0;
    $results = [];
    
    while ($loan = $overdueResult->fetch_assoc()) {
        // Use the same SOA email function for individual loans
        $result = sendSOAEmail($loan['loan_id'], $db, $emailConfig);
        
        $results[] = [
            'loan_id' => $loan['loan_id'],
            'member' => $loan['first_name'] . ' ' . $loan['last_name'],
            'email' => $loan['email'],
            'status' => $loan['status'], // Add loan status to response
            'result' => $result
        ];
        
        if ($result['success']) {
            $sentCount++;
        } else {
            $failedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Email sending completed. Sent: $sentCount, Failed: $failedCount",
        'details' => $results
    ]);
}

// Clean output buffer and send response
ob_end_flush();
?>
