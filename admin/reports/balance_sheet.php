<?php

require('../../db_connect.php');

$as_of = $_GET['as_of'] ?? date('Y-m-d');

// Total Savings
$stmt = $db->prepare("
    SELECT IFNULL(SUM(t.amount),0)
    FROM transactions t
    JOIN accounts a ON t.account_id = a.account_id
    WHERE a.account_type_id = 1
    AND t.status='active'
    AND t.transaction_date <= ?
");
$stmt->bind_param("s", $as_of);
$stmt->execute();
$stmt->bind_result($total_savings);
$stmt->fetch();
$stmt->close();

// Total Capital Share
$stmt = $db->prepare("
    SELECT IFNULL(SUM(t.amount),0)
    FROM transactions t
    JOIN accounts a ON t.account_id = a.account_id
    WHERE a.account_type_id = 2
    AND t.status='active'
    AND t.transaction_date <= ?
");
$stmt->bind_param("s", $as_of);
$stmt->execute();
$stmt->bind_result($total_capital);
$stmt->fetch();
$stmt->close();

// Loan Receivable
$result = $db->query("
    SELECT IFNULL(SUM(l.total_due - IFNULL(p.total_paid,0)),0)
    FROM loans l
    LEFT JOIN (
        SELECT loan_id, SUM(amount_paid) total_paid
        FROM loan_payments
        GROUP BY loan_id
    ) p ON l.loan_id = p.loan_id
    WHERE l.status='ongoing'
");

$row = $result->fetch_row();
$loan_receivable = $row[0];

?>

<div class="container">
    <h3>Statement of Financial Position</h3>

    <form method="GET" class="mb-3">
        <input type="date" name="as_of" value="<?= $as_of ?>" required>
        <button class="btn btn-primary btn-sm">Generate</button>
    </form>

    <table class="table table-bordered">
        <tr>
            <th colspan="2">ASSETS</th>
        </tr>
        <tr>
            <td>Loan Receivable</td>
            <td><?= number_format($loan_receivable, 2) ?></td>
        </tr>

        <tr>
            <th colspan="2">LIABILITIES</th>
        </tr>
        <tr>
            <td>Savings Deposits</td>
            <td><?= number_format($total_savings, 2) ?></td>
        </tr>

        <tr>
            <th colspan="2">EQUITY</th>
        </tr>
        <tr>
            <td>Capital Share</td>
            <td><?= number_format($total_capital, 2) ?></td>
        </tr>
    </table>
</div>