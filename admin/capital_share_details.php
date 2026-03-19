<?php
require('db_connect.php');

if (!isset($_GET['reference_no'])) {
    die("Reference number not provided.");
}

$reference_no = $db->real_escape_string($_GET['reference_no']);

// 1️⃣ Fetch transaction and member details
$query = "
SELECT 
    t.reference_no,
    t.transaction_date,
    t.amount,
    t.remarks,
    m.member_id,
    CONCAT(m.first_name,' ',m.last_name) AS member_name,
    m.address,
    m.phone
FROM transactions t
INNER JOIN accounts a 
    ON a.account_id = t.account_id
INNER JOIN account_types at 
    ON at.account_type_id = a.account_type_id
INNER JOIN tbl_members m 
    ON m.member_id = a.member_id
WHERE t.reference_no = '$reference_no'
AND at.type_name = 'capital_share'
LIMIT 1
";

$result = $db->query($query);

if ($result->num_rows == 0) {
    die("Capital share not found.");
}

$row = $result->fetch_assoc();

// 2️⃣ Fetch employee info from tbl_history
$historyResult = $db->query("
    SELECT details 
    FROM tbl_history 
    WHERE history_type = 50 
      AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.reference')) = '$reference_no'
    LIMIT 1
");

if ($historyResult && $historyResult->num_rows > 0) {
    $historyRow = $historyResult->fetch_assoc();
    $details = json_decode($historyRow['details'], true);
    $employee_name = $details['employee'] ?? 'System';
    $user_id = $details['user_id'] ?? null;

    // Get username from user_id
    if ($user_id) {
        $res = $db->query("SELECT username FROM tbl_users WHERE user_id = $user_id LIMIT 1");
        $username = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['username'] : '-';
    } else {
        $username = '-';
    }
} else {
    $employee_name = 'System';
    $username = '-';
}

$member_name = $row['member_name'];
$amount = $row['amount'];
$date = $row['transaction_date'];
$remarks = $row['remarks'];
$address = $row['address'];
$phone = $row['phone'];
?>

<!DOCTYPE html>
<html>

<head>

    <title>Capital Share Receipt</title>
    <style>
        .heading-elements {
            background: none !important;
            margin-top: -30px;
        }

        .receipt-box {
            max-width: 400px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
        }

        .center {
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>

</head>

<body>

    <div class="content" style="margin:0px">

        <div class="panel panel-flat">

            <div class="panel-body">

                <div class="tabbable">

                    <ul class="nav nav-tabs bg-slate nav-justified">

                        <li class="active">
                            <a href="#capital_receipt" data-toggle="tab">
                                Receipt
                            </a>
                        </li>

                        <li>
                            <a href="#details" data-toggle="tab">
                                Details
                            </a>
                        </li>

                    </ul>


                    <div class="tab-content">


                        <!-- RECEIPT TAB -->
                        <div class="tab-pane active" id="capital_receipt">
                            <div class="center no-print" style="margin:15px">
                                <button onclick="print_receipt('capital-print-area')"
                                    class="btn bg-teal-400 btn-labeled">
                                    <i class="icon-printer"></i> Print
                                </button>
                            </div>

                            <div id="capital-print-area">
                                <?php
                                // This will include the capital_receipt.php which has the receipt table/layout
                                require('capital_receipt.php');
                                ?>
                            </div>
                        </div>



                        <!-- DETAILS TAB -->
                        <div class="tab-pane" id="details">

                            <table class="table table-bordered">

                                <tr>
                                    <td width="40%">Reference No</td>
                                    <td><b><?= htmlspecialchars($reference_no) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Member Name</td>
                                    <td><b><?= htmlspecialchars($member_name) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Address</td>
                                    <td><b><?= htmlspecialchars($address) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Phone</td>
                                    <td><b><?= htmlspecialchars($phone) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Date</td>
                                    <td><b><?= date("F d, Y h:i A", strtotime($date)) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Amount</td>
                                    <td><b>₱<?= number_format($amount, 2) ?></b></td>
                                </tr>

                                <tr>
                                    <td>Remarks</td>
                                    <td><b><?= htmlspecialchars($remarks) ?></b></td>
                                </tr>
                                <tr>
                                    <td>Processed By</td>
                                    <td>
                                        <b><?= htmlspecialchars($employee_name) ?></b><br>
                                    </td>
                                </tr>


                            </table>

                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>


    <script>
        function print_receipt(areaId) {
            var contents = document.getElementById(areaId).innerHTML;
            var frame = document.createElement('iframe');
            frame.name = "framePrint";
            frame.style.position = "absolute";
            frame.style.top = "-10000px";
            document.body.appendChild(frame);

            var doc = frame.contentWindow || frame.contentDocument.document || frame.contentDocument;
            doc.document.open();
            doc.document.write('<html><head><title>Print</title></head><body>');
            doc.document.write(contents);
            doc.document.write('</body></html>');
            doc.document.close();

            setTimeout(function() {
                window.frames["framePrint"].focus();
                window.frames["framePrint"].print();
                document.body.removeChild(frame);
            }, 500);
        }
    </script>

</body>

</html>