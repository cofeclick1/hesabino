<?php
require_once '../includes/init.php';

try {
    $date = new DateTime();
    $currentDate = $date->format('Y-m-d');

    $customers = $db->query("SELECT * FROM customers WHERE created_date = ?", [$currentDate])->fetchAll();
    echo json_encode(['success' => true, 'data' => $customers], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطا در دریافت لیست مشتری‌ها', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}