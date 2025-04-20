<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

$db = Database::getInstance();

$query = "SELECT type, SUM(amount) as total
         FROM transactions
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY type";

$result = $db->query($query)->fetchAll();

$data = [];
$labels = [];

foreach ($result as $row) {
    $labels[] = $row['type'] == 'income' ? 'درآمد' : 'هزینه';
    $data[] = (float)$row['total'];
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'labels' => $labels
]);