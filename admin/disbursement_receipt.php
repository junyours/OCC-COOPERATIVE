<?php
require('../db_connect.php');

if (!isset($_GET['loan_id'])) {
    die("Invalid request: Missing loan ID.");
}
$loan_id = (int) $_GET['loan_id'];


$query = "
    SELECT l.loan_app_id,
           c.name AS member_name,
           c.address,
           la.approved_amount,
           la.approved_term,
           la.interest_rate,
           ld.amount_released,
           ld.mode,
           ld.release_date
    FROM tbl_loan_application l
    JOIN tbl_customer c ON l.customer_id = c.cust_id
    JOIN tbl_loan_approval la ON la.loan_app_id = l.loan_app_id
    LEFT JOIN tbl_loan_disbursement ld ON ld.loan_app_id = l.loan_app_id
    WHERE l.loan_app_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();
$loan = $result->fetch_assoc();

if (!$loan) {
    die("Loan or disbursement not found.");
}

// Loan details
$loan_number = "LN-" . str_pad($loan['loan_app_id'], 6, "0", STR_PAD_LEFT);
$principal = $loan['approved_amount'];
$amount_released = $loan['amount_released'];
$term = $loan['approved_term'];
$interest_rate = $loan['interest_rate'];
$mode = $loan['mode'];
$release_date = $loan['release_date'];

// Monthly EMI calculation (Flat or Reducing Balance can be adjusted here)
$monthly_rate = $interest_rate / 12 / 100;
$emi = $principal * $monthly_rate * pow(1 + $monthly_rate, $term) / (pow(1 + $monthly_rate, $term) - 1);
$emi = round($emi, 2);

// Total payable
$total_payable = round($emi * $term, 2);
?>

<div class="receipt-div" id="print-receipt">
    <div class="text-center">
        <p class="title"><b>OCC COOPERATIVE</b></p>
        <p>Opol Community College, Mis'Or</p>
        <p>Loan Disbursement Receipt</p>

        <hr>
    </div>

    <table style="width:100%; margin-bottom:10px;">
        <tr>
            <td><b>Loan No:</b> <?= $loan_number ?></td>
            <td class="text-right"><b>Date Released:</b> <?= $release_date ?></td>
        </tr>
        <tr>
            <td><b>Member:</b> <?= htmlspecialchars($loan['member_name']) ?></td>
            <td class="text-right"><b>Address:</b> <?= htmlspecialchars($loan['address']) ?></td>
        </tr>
        <tr>
            <td><b>Mode of Release:</b> <?= htmlspecialchars($mode) ?></td>
            <td class="text-right"><b>Amount Released:</b> <?= number_format($amount_released, 2) ?></td>
        </tr>
    </table>

    <table class="table-loan" style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>Principal Amount</th>
                <th>Term (months)</th>
                <th>Interest Rate</th>
                <th>Monthly</th>
                <th>Total Payable</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="right"><?= number_format($principal, 2) ?></td>
                <td align="center"><?= $term ?></td>
                <td align="center"><?= $interest_rate ?>%</td>
                <td align="right"><?= number_format($emi, 2) ?></td>
                <td align="right"><b><?= number_format($total_payable, 2) ?></b></td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <table style="width:100%;">
        <tr>
            <td>
                <p>Issued by:</p><br><br>
                _________________________<br>
                Authorized Signature
            </td>
            <td align="right">
                <p>Received by:</p><br><br>
                _________________________<br>
                Borrower Signature
            </td>
        </tr>
    </table>
</div>