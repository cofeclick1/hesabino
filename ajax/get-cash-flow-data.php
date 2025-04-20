<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

$db = Database::getInstance();

$query = "SELECT DATE(created_at) as date,
         SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance
         FROM transactions 
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at)
         ORDER BY date";

$result = $db->query($query)->fetchAll();

$data = [];
$labels = [];

foreach ($result as $row) {
    $labels[] = $row['date'];
    $data[] = (float)$row['balance'];
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'labels' => $labels
]);