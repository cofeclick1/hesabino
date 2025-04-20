<?php
require_once '../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $today = date('Y-m-d');
    $customers = $db->query(
        "SELECT id, name FROM customers WHERE DATE(created_at) = ? ORDER BY id DESC",
        [$today]
    )->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'customers' => $customers
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در دریافت لیست مشتریان: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}