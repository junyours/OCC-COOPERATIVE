<?php
require('../db_connect.php');

if (!isset($_GET['reference_no'])) {
    die("Invalid request: Missing reference number.");
}

$reference_no = $db->real_escape_string($_GET['reference_no']);
$loanQry = $db->query("
    SELECT lp.loan_id
    FROM loan_payments lp
    WHERE lp.reference_no = '$reference_no'
");

if ($loanQry->num_rows == 0) {
    die("Loan payment not found.");
}

$loan_id = $loanQry->fetch_assoc()['loan_id'];

$loanQry = $db->query("
SELECT 
l.loan_id,
l.approved_amount,
l.interest_rate,
l.insurance,
l.service_charge,
l.doc_stamp_fee,
l.term_value,
l.term_unit,
l.total_interest,
l.total_due,
l.status,
l.approved_date,
m.first_name,
m.last_name,
m.address

FROM loans l
JOIN accounts a ON l.account_id = a.account_id
JOIN tbl_members m ON a.member_id = m.member_id
WHERE l.loan_id = $loan_id
");

if ($loanQry->num_rows == 0) {
    die("Loan not found.");
}

$loan = $loanQry->fetch_assoc();

$fee_type = 'percent';
$fee_value = 0;

$feeQry = $db->query("
SELECT setting_key, setting_value
FROM system_settings
WHERE setting_key IN ('loan_processing_fee_type','loan_processing_fee_value')
");

while ($row = $feeQry->fetch_assoc()) {
    if ($row['setting_key'] == "loan_processing_fee_type") $fee_type = $row['setting_value'];
    if ($row['setting_key'] == "loan_processing_fee_value") $fee_value = (float)$row['setting_value'];
}

$processing_fee = ($fee_type == "percent") ? $loan['approved_amount'] * ($fee_value / 100) : $fee_value;


$term = $loan['term_value'];
$monthly_payment = $loan['total_due'] / $term;

$scheduleQry = $db->query("
SELECT due_date, total_due
FROM loan_schedule
WHERE loan_id = $loan_id
ORDER BY due_date ASC
");

$schedule = [];
$count = 1;
while ($row = $scheduleQry->fetch_assoc()) {
    $schedule[] = [
        "no" => $count++,
        "due_date" => date("M d, Y", strtotime($row['due_date'])),
        "amount" => $row['total_due']
    ];
}


$payments_result = $db->query("
SELECT reference_no, payment_date, principal_paid, interest_paid, penalty_paid, amount_paid
FROM loan_payments
WHERE loan_id = $loan_id
ORDER BY payment_date ASC, payment_id ASC
");

$payments = [];
$remaining_balance = floatval($loan['total_due']);

while ($p = $payments_result->fetch_assoc()) {

    $principal_paid = floatval($p['principal_paid']);
    $interest_paid  = floatval($p['interest_paid']);
    $penalty_paid   = floatval($p['penalty_paid']);
    $amount_paid    = floatval($p['amount_paid']);

    // subtract TOTAL payment (not just principal)
    $remaining_balance -= $amount_paid;

    if ($remaining_balance < 0) {
        $remaining_balance = 0;
    }

    $payments[] = [
        "reference_no" => $p['reference_no'],
        "payment_date" => date("M d, Y", strtotime($p['payment_date'])),
        "principal_paid" => $principal_paid,
        "interest_paid" => $interest_paid,
        "penalty_paid" => $penalty_paid,
        "amount_paid" => $amount_paid,
        "remaining" => $remaining_balance
    ];
}


$loan_number = "LN-" . str_pad($loan_id, 6, '0', STR_PAD_LEFT);
$approval_date = date("M d, Y", strtotime($loan['approved_date']));
$member_name = $loan['first_name'] . " " . $loan['last_name'];
$insurance = $loan['insurance'];
$processing_fee = $loan['service_charge'];
$doc_stampp_fee = $loan['doc_stamp_fee'];
$principal = $loan['approved_amount'];
$interest_rate = $loan['interest_rate'];
$total_interest = $loan['total_interest'];
$total_payable = $loan['total_due'];
$address = $loan['address'];
?>

<div class="receipt-div" id="print-receipt">
    <div class="text-center">
        <p class="title"><b>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</b></p>
        <p><b>Loan Details</b></p>
        <hr>
    </div>

    <table style="width:100%; margin-bottom:10px;">
        <tr>
            <td><b>Loan No:</b> <?= $loan_number ?></td>
            <td class="text-right"><b>Date Approved:</b> <?= $approval_date ?></td>
        </tr>
        <tr>
            <td><b>Member:</b> <?= htmlspecialchars($member_name) ?></td>
            <td class="text-right"><b>Address:</b> <?= htmlspecialchars($address) ?></td>
        </tr>
    </table>

    <!-- LOAN SUMMARY -->
    <table class="table-loan" style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>Principal</th>
                <th>Term</th>
                <th>Interest Rate</th>
                <th>Processing Fee</th>
                <th>Insurance</th>
                <th>Doc Stampp</th>
                <th>Total Interest</th>
                <th>Total Payable</th>
                <th>Status</th>
                <th>Monthly Payment</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="right"><?= number_format($principal, 2) ?></td>
                <td align="center"><?= $term . " " . $loan['term_unit'] ?></td>
                <td align="center"><?= $interest_rate ?>%</td>
                <td align="right"><?= number_format($processing_fee, 2) ?></td>
                <td align="right"><?= number_format($insurance, 2) ?></td>
                <td align="right"><?= number_format($doc_stampp_fee, 2) ?></td>
                <td align="right"><?= number_format($total_interest, 2) ?></td>
                <td align="right"><b><?= number_format($total_payable, 2) ?></b></td>
                <td align="center"><?= ucfirst($loan['status']) ?></td>
                <td align="right"><b><?= number_format($monthly_payment, 2) ?></b></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <!-- PAYMENT SCHEDULE -->
    <h5>Payment Schedule</h5>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Amount Due</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedule as $s): ?>
                <tr>
                    <td align="center"><?= $s['no'] ?></td>
                    <td align="center"><?= $s['due_date'] ?></td>
                    <td align="right"><?= number_format($s['amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" align="right"><b>Total</b></td>
                <td align="right"><b><?= number_format($total_payable, 2) ?></b></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <!-- MEMBER PAYMENTS -->
    <h5>Member Payments</h5>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date Paid</th>
                <th class="text-right">Principal</th>
                <th class="text-right">Interest</th>
                <th class="text-right">Penalty</th>
                <th class="text-right">Total Paid</th>
                <th class="text-right">Remaining Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['reference_no']) ?></td>
                        <td><?= $p['payment_date'] ?></td>
                        <td align="right"><?= number_format($p['principal_paid'], 2) ?></td>
                        <td align="right"><?= number_format($p['interest_paid'], 2) ?></td>
                        <td align="right"><?= number_format($p['penalty_paid'], 2) ?></td>
                        <td align="right"><?= number_format($p['amount_paid'], 2) ?></td>
                        <td align="right"><?= number_format($p['remaining'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" align="center">No payments made yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>

    <table style="width:100%;">
        <tr>
            <td>
                Issued by:<br><br><br>
                _________________________<br>
                Authorized Signature
            </td>
            <td align="right">
                Received by:<br><br><br>
                _________________________<br>
                Borrower Signature
            </td>
        </tr>
    </table>
</div>