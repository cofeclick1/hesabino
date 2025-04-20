<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$receivedChequesData = $db->query("
    SELECT cheque_number, amount, due_date
    FROM cheques
    WHERE status = 'received'
    ORDER BY due_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($receivedChequesData as $row) {
    $labels[] = $row['cheque_number'] . ' - ' . jdate("Y/m/d", strtotime($row['due_date']));
    $data[] = (float) $row['amount'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);