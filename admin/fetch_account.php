<?php
require('../db_connect.php');

// Make sure $member_id and $year are set
$member_id = $_SESSION['member_id'] ?? 0;
$year = date('Y');
?>

<div class="tab-pane" id="loan">
    <div class="panel panel-white border-top-xlg border-top-teal-400">
        <div class="panel-heading">
            <h6 class="panel-title">
                <i class="icon-coins position-left text-teal-400"></i> Loan Payments (<?= $year; ?>)
            </h6>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr style="background:#eee">
                        <th>Reference</th>
                        <th>Date</th>
                        <th class="text-right text-success">Principal</th>
                        <th class="text-right text-success">Interest</th>
                        <th class="text-right text-danger">Penalty</th>
                        <th class="text-right">Total Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $loan_result = $db->query("
                        SELECT t.transaction_date, t.amount AS total_paid,
                               t.reference_no, t.remarks
                        FROM transactions t
                        INNER JOIN accounts a ON a.account_id = t.account_id
                        WHERE a.member_id = $member_id
                          AND t.transaction_type_id = 5
                          AND YEAR(t.transaction_date) = $year
                        ORDER BY t.transaction_date DESC, t.transaction_id DESC
                    ");

                    if ($loan_result && $loan_result->num_rows > 0) {
                        while ($l = $loan_result->fetch_assoc()) {

                            $reference = htmlspecialchars($l['reference_no']);
                            $date = date('M d, Y', strtotime($l['transaction_date']));
                            $total_paid = floatval($l['total_paid']);
                            $status = htmlspecialchars($l['remarks']); // optional: use remarks for status

                            echo "
                            <tr>
                                <td>
                                    <a href='javascript:void(0);'
                                       onclick='view_loan_receipt(this)'
                                       data-reference='{$reference}'
                                       style='font-weight:600; color:#26a69a;'>{$reference}</a>
                                </td>
                                <td>{$date}</td>
                                <td class='text-right text-success'>-</td>
                                <td class='text-right text-success'>-</td>
                                <td class='text-right text-danger'>-</td>
                                <td class='text-right'>₱" . number_format($total_paid, 2) . "</td>
                                <td>{$status}</td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "
                        <tr>
                            <td colspan='7' class='text-center'>
                                No loan payment transactions found for {$year}.
                            </td>
                        </tr>
                        ";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>