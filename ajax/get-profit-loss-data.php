<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$profitLossData = $db->query("
    SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(profit) as total_profit, SUM(loss) as total_loss
    FROM profit_loss
    GROUP BY month
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach ($profitLossData as $row) {
    $labels[] = $row['month'];
    $data[] = (float) ($row['total_profit'] - $row['total_loss']);
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);