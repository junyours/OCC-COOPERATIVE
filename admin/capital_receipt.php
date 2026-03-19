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
    m.member_id,
    m.first_name,
    m.last_name,
    c.name,
    c.address,
    c.contact,

    a.account_number

FROM transactions t

INNER JOIN accounts a ON a.account_id = t.account_id
INNER JOIN tbl_members m ON m.member_id = a.member_id
INNER JOIN tbl_customer c ON c.cust_id = m.cust_id

WHERE t.reference_no = ?
LIMIT 1
";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $reference_no);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Receipt not found.");
}

$member_name = $data['first_name'] . " " . $data['last_name'];
$amount = $data['amount'];
$date = date("F d, Y h:i A", strtotime($data['transaction_date']));
$account_number = $data['account_number'];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Capital Share Receipt</title>
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
    </style>
</head>

<body onload="window.print()">

    <div class="receipt">

        <div class="text-center">
            <h3>OPOL COMMUNITY COLLEGE <br>EMPLOYEES CREDIT COOPERATIVE</h3>
            <p>Capital Share Receipt</p>
        </div>

        <div class="line"></div>

        <p>
            Reference No:<br>
            <b><?= $reference_no ?></b>
        </p>

        <p>
            Date:<br>
            <?= $date ?>
        </p>

        <div class="line"></div>

        <p>
            Member Name:<br>
            <b><?= $member_name ?></b>
        </p>

        <p>
            Account No:<br>
            <?= $account_number ?>
        </p>

        <div class="line"></div>

        <p>
            Capital Share Amount:
        </p>

        <h2 class="text-right">
            ₱ <?= number_format($amount, 2) ?>
        </h2>

        <div class="line"></div>

        <p>
            Remarks:<br>
            <?= $data['remarks'] ?>
        </p>

        <br><br>

        <p class="text-center">
            _______________________<br>
            Authorized Signature
        </p>

    </div>

</body>

</html>