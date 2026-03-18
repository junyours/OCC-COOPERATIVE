<?php
require('../db_connect.php');

if (!isset($_GET['loan_id'])) {
    die("Invalid request: Missing loan ID.");
}

$loan_id = (int) $_GET['loan_id'];


$loanQry = $db->query("
    SELECT 
        l.loan_id, 
        l.approved_amount, 
        l.interest_rate, 
        l.term_value, 
        l.term_unit, 
        l.total_interest,  
        l.total_due, 
        l.status, 
        l.approved_date,
        l.service_charge, 
        l.doc_stamp_fee, 
        l.insurance, 
        l.payment_frequency,
        l.released_date,
        f.fund_name,
        f.current_balance,

        m.first_name, 
        m.last_name, 
        m.address,
        a.account_number

    FROM loans l
    JOIN accounts a ON l.account_id = a.account_id
    JOIN tbl_members m ON a.member_id = m.member_id
    LEFT JOIN tbl_loan_fund f ON l.fund_id = f.fund_id
    WHERE l.loan_id = $loan_id
");

if ($loanQry->num_rows == 0) {
    die("Loan not found.");
}

$loan = $loanQry->fetch_assoc();

// Fetch system settings


$feeQry = $db->query("
    SELECT setting_key, setting_value 
    FROM system_settings 
    WHERE setting_key IN
    ('monthly_savings','monthly_share_capital','loan_doc_stamp_fee','loan_insurance_fee')
");

while ($row = $feeQry->fetch_assoc()) {
    $settings[$row['setting_key']] = (float)$row['setting_value'];
}

$principal = $loan['approved_amount'];
$interest_rate = $loan['interest_rate'];
$term = $loan['term_value'];
$term_unit = $loan['term_unit'];
$service_charge = (float)$loan['service_charge'];
$doc_stamp_fee = (float)$loan['doc_stamp_fee'];
$insurance_fee = (float)$loan['insurance'];
$monthly_savings = $settings['monthly_savings'];
$monthly_share_capital = $settings['monthly_share_capital'];
$total_interest = $loan['total_interest'];
$total_payable = $loan['total_due'];


$loan_number = "LN-" . str_pad($loan_id, 6, '0', STR_PAD_LEFT);
$approval_date = date("M d, Y", strtotime($loan['approved_date']));
$member_name = $loan['first_name'] . " " . $loan['last_name'];
$account_number = $loan['account_number'];
$address = $loan['address'];

$fund_name = $loan['fund_name'] ?? 'N/A';
$fund_balance = isset($loan['current_balance'])
    ? number_format($loan['current_balance'], 2)
    : 'N/A';

$payment_frequency = ucfirst($loan['payment_frequency']);
$released_date = $loan['released_date']
    ? date("M d, Y", strtotime($loan['released_date']))
    : 'Not yet released';


$scheduleQry = $db->query("
SELECT 
    ls.schedule_id,
    ls.due_date,
    ls.principal_due,
    ls.interest_due,
    ls.total_due,

    IFNULL((
        SELECT SUM(lp.amount_paid)
        FROM loan_payments lp
        WHERE lp.loan_id = ls.loan_id
        AND lp.schedule_id = ls.schedule_id
    ),0) AS paid

FROM loan_schedule ls
WHERE ls.loan_id = $loan_id
ORDER BY ls.due_date ASC
");

$schedule = [];
$count = 1;
$running_balance = $total_payable;

while ($row = $scheduleQry->fetch_assoc()) {
    $total_due_schedule = (float)$row['total_due'];


    $running_balance -= $total_due_schedule;

    $schedule[] = [
        "no" => $count++,
        "due_date" => date("M d, Y", strtotime($row['due_date'])),
        "principal_due" => $row['principal_due'],
        "interest_due" => $row['interest_due'],
        "total_due" => $total_due_schedule,
        "balance" => $running_balance >= 0 ? $running_balance : 0
    ];
}

?>
<style>
    .receipt-div {
        max-width: 900px;
        margin: 20px auto;
        padding: 25px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-family: 'Arial', sans-serif;
        background-color: #fdfdfd;
        color: #333;
    }

    /* Header */
    .receipt-div .title {
        font-size: 1.5em;
        margin-bottom: 5px;
    }

    .receipt-div hr {
        border: 1px solid #0479d8;
        margin: 10px 0 20px;
    }

    /* Table styling */
    .table-loan,
    .amortization-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .table-loan th,
    .table-loan td,
    .amortization-table th,
    .amortization-table td {
        border: 1px solid #ccc;
        padding: 8px 10px;
        text-align: center;
        font-size: 0.95em;
    }

    .table-loan th,
    .amortization-table th {
        background-color: #0479d8;
        color: #070505;
        font-weight: bold;
    }

    .table-loan tbody tr:nth-child(even),
    .amortization-table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .table-loan tbody tr:hover,
    .amortization-table tbody tr:hover {
        background-color: #e0f7fa;
    }

    .table-loan td.total,
    .amortization-table td.total {
        font-weight: bold;
        background-color: #b2dfdb;
        color: #000;
    }

    /* Section titles */
    h5 {
        color: #0262b0;
        margin-bottom: 10px;
    }

    /* Signature section */
    .signature-table {
        width: 100%;
        margin-top: 40px;
        font-size: 0.95em;
    }

    .signature-table td {
        vertical-align: top;
        padding: 10px;
    }

    .signature-line {
        margin-top: 60px;
        display: block;
        border-top: 1px solid #333;
        width: 70%;
    }

    .signature-label {
        margin-top: 5px;
        font-weight: bold;
    }

    /* Responsive */
    @media print {
        .receipt-div {
            border: none;
            margin: 0;
            padding: 0;
        }

        .table-loan th,
        .table-loan td,
        .amortization-table th,
        .amortization-table td {
            font-size: 0.85em;
        }
    }

    .receipt-logo {
        width: 100px;
        /* Adjust width as needed */
        height: auto;
        /* Maintain aspect ratio */
        display: block;
        margin: 0 auto 10px;
        /* Center the image and add some bottom spacing */
    }
</style>

<div class="receipt-div" id="print-receipt">

    <div class="text-center">
        <img src="../images/main_logo.jpg" alt="Cooperative Logo" class="receipt-logo">
        <p class="title"><b>OPOL COMMUNITY COLLEGE<br>EMPLOYEES CREDIT COOPERATIVE</b></p>
        <p><b>Loan Details</b></p>
        <hr>
    </div>

    <!-- Loan Info -->
    <table style="width:100%; margin-bottom:15px;">
        <tr>

            <td><b>Loan No:</b> <?= $loan_number ?></td>
            <td class="text-right"><b>Date Approved:</b> <?= $approval_date ?></td>
        </tr>
        <tr>
            <td><b>Member:</b> <?= htmlspecialchars($member_name) ?></td>
            <td class="text-right"><b>Address:</b> <?= htmlspecialchars($address) ?></td>
        </tr>
        <tr>
            <td><b>Account No:</b> <?= htmlspecialchars($account_number) ?></td>
            <td></td>
        </tr>
    </table>

    <!-- Loan Summary -->
    <h5>Loan Summary</h5>
    <table class="table-loan">
        <thead>
            <tr>
                <th>Principal</th>
                <th>Term</th>
                <th>Interest Rate</th>
                <th>Service Charge</th>
                <th>Doc Stamp </th>
                <th>Insurance</th>
                <th>Monthly Savings</th>
                <th>Share Capital</th>
                <th>Total Term Interest</th>
                <th>Total Payable</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= number_format($principal, 2) ?></td>
                <td><?= $term . " " . $term_unit ?></td>
                <td><?= $interest_rate ?>%</td>
                <td><?= number_format($service_charge, 2) ?></td>
                <td><?= number_format($doc_stamp_fee, 2) ?></td>
                <td><?= number_format($insurance_fee, 2) ?></td>
                <td><?= number_format($monthly_savings, 2) ?></td>
                <td><?= number_format($monthly_share_capital, 2) ?></td>
                <td><?= number_format($total_interest, 2) ?></td>
                <td class="total"><?= number_format($total_payable, 2) ?></td>
                <td><?= ucfirst($loan['status']) ?></td>
            </tr>
        </tbody>
    </table>
    </table>



    <!-- <h5>Loan Fund Details</h5>
    <table class="table-loan">
        <thead>
            <tr>
                <th>Fund Name</th>
                <th>Current Fund Balance</th>
                <th>Payment Frequency</th>
                <th>Date Released</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($fund_name) ?></td>
                <td><?= $fund_balance ?></td>
                <td><?= $payment_frequency ?></td>
                <td><?= $released_date ?></td>
            </tr>
        </tbody>
    </table> -->


    <!-- Loan Summary -->
    <h5>Loan Summary</h5>

    <!-- Amortization Schedule -->
    <h5>Amortization Schedule</h5>
    <table class="amortization-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Total Amort.</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedule as $s): ?>
                <tr>
                    <td><?= $s['no'] ?></td>
                    <td><?= $s['due_date'] ?></td>
                    <td><?= number_format($s['principal_due'], 2) ?></td>
                    <td><?= number_format($s['interest_due'], 2) ?></td>
                    <td class="total"><?= number_format($s['total_due'], 2) ?></td>
                    <td class="total"><?= number_format($s['balance'], 2) ?></td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td>
                Issued by:
                <span class="signature-line"></span>
                <div class="signature-label">Authorized Signature</div>
            </td>
            <td align="right">
                Received by:
                <span class="signature-line"></span>
                <div class="signature-label">Borrower Signature</div>
            </td>
        </tr>
    </table>
</div>