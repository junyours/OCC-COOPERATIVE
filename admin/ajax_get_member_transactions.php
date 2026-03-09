<?php
require('db_connect.php');

$member_id = intval($_GET['member_id'] ?? 0);

if ($member_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT t.transaction_id,
       t.amount,
       t.reference_no,
       t.transaction_date,
       tt.type_name
FROM transactions t
JOIN accounts a ON t.account_id = a.account_id
JOIN transaction_types tt ON t.transaction_type_id = tt.transaction_type_id
WHERE a.member_id = ?
AND t.status = 'active'
ORDER BY t.transaction_date DESC
";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
