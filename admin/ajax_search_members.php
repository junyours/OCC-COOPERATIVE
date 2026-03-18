<?php
require('../db_connect.php');

$search = $_GET['q'] ?? '';

$query = $db->prepare("
    SELECT member_id, first_name, last_name, type
    FROM tbl_members
    WHERE CONCAT(first_name, ' ', last_name) LIKE ?
    ORDER BY last_name ASC
    LIMIT 20
");
$searchParam = "%$search%";
$query->bind_param("s", $searchParam);
$query->execute();
$result = $query->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = [
        'id' => $row['member_id'],
        'text' => strtoupper($row['first_name'] . ' ' . $row['last_name']),
        'type' => strtolower($row['type'])
    ];
}

echo json_encode(['results' => $members]);
