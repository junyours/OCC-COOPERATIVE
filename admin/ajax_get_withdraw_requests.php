<?php
require('db_connect.php');

$member_id = intval($_GET['member_id'] ?? 0);
if ($member_id <= 0) {
    echo json_encode([]);
    exit;
}

$q = $db->prepare("
    SELECT request_id, amount
    FROM savings_withdrawal_requests
    WHERE account_id IN (SELECT account_id FROM accounts WHERE member_id = ?)
      AND status = 'pending'
    ORDER BY date_requested ASC
");
$q->bind_param("i", $member_id);
$q->execute();
$res = $q->get_result();

$requests = [];
while ($r = $res->fetch_assoc()) {
    $requests[] = [
        'request_id' => $r['request_id'],
        'amount'     => $r['amount']
    ];
}
echo json_encode($requests);
