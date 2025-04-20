<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$debtorsData = $db->query("
    SELECT customer_name, SUM(amount) as total
    FROM debtors
    GROUP BY customer_name
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($debtorsData as $row) {
    $labels[] = $row['customer_name'];
    $data[] = (float) $row['total'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);