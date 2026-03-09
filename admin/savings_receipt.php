<?php
error_reporting(0);
require('db_connect.php');

if (!isset($_GET['reference_no'])) {
    die("Invalid reference.");
}

$reference_no = $_GET['reference_no'];

$query = "
SELECT 
    t.reference_no,
    t.amount,
    t.transaction_date,
    t.remarks,
    tt.type_name,

    m.member_id,
    m.first_name,
    m.last_name,

    a.account_number

FROM transactions t

INNER JOIN accounts a ON a.account_id = t.account_id
INNER JOIN account_types at ON at.account_type_id = a.account_type_id
INNER JOIN tbl_members m ON m.member_id = a.member_id
INNER JOIN transaction_types tt ON tt.transaction_type_id = t.transaction_type_id

WHERE t.reference_no = ?
AND at.type_name = 'savings'
LIMIT 1
";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $reference_no);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Savings receipt not found.");
}

$member_name = $data['first_name'] . " " . $data['last_name'];
$amount = $data['amount'];
$date = date("F d, Y h:i A", strtotime($data['transaction_date']));
$account_number = $data['account_number'];

// Determine transaction type
if ($amount > 0) {
    $transaction_type = "Deposit";
    $display_amount = number_format($amount, 2);
} else {
    $transaction_type = "Withdrawal";
    $display_amount = number_format(abs($amount), 2); // display positive value
}


$historyResult = $db->query("
    SELECT details 
    FROM tbl_history 
    WHERE history_type = 51
      AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.reference')) = '$reference_no'
    LIMIT 1
");

if ($historyResult && $historyResult->num_rows > 0) {

    $historyRow = $historyResult->fetch_assoc();
    $details = json_decode($historyRow['details'], true);

    $employee_name = $details['employee'] ?? 'System';
} else {

    $employee_name = 'System';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Savings Receipt</title>
    <style>
        body {
            font-family: calibri;
            font-size: 12px;
        }

        .receipt {
            width: 300px;
            margin: auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .line {
            border-top: 1px dashed black;
            margin: 5px 0;
        }

        .type {
            font-weight: bold;
            color: <?= $transaction_type == 'Deposit' ? 'green' : 'red' ?>;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="receipt">

        <div class="text-center">
            <h3>OPOL COMMUNITY COLLEGE <br> EMPLOYEES CREDIT COOPERATIVE</h3>
            <p>Receipt</p>
        </div>

        <div class="line"></div>

        <p>
            Reference No:<br>
            <b><?= htmlspecialchars($reference_no) ?></b>
        </p>

        <p>
            Date:<br>
            <?= $date ?>
        </p>

        <div class="line"></div>

        <p>
            Member Name:<br>
            <b><?= htmlspecialchars($member_name) ?></b>
        </p>

        <p>
            Account No:<br>
            <?= htmlspecialchars($account_number) ?>
        </p>

        <div class="line"></div>

        <p>Transaction Type:<br>
            <span class="type"><?= $transaction_type ?></span>
        </p>

        <p>Amount:</p>
        <h2 class="text-right">
            ₱ <?= $display_amount ?>
        </h2>

        <div class="line"></div>

        <p>
            Remarks:<br>
            <?= htmlspecialchars($data['remarks']) ?>
        </p>


        <div class="line"></div>

        <p>
            Processed By:<br>
            <?= htmlspecialchars($employee_name) ?>
        </p>


        <br><br>

        <p class="text-center">
            _______________________<br>
            Authorized Signature
        </p>

    </div>

</body>

</html>