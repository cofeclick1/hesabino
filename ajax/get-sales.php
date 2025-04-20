<?php
require_once '../includes/init.php';

// تنظیم هدر
header('Content-Type: application/json; charset=utf-8');

try {
    $sales = $db->query("
        SELECT i.invoice_number, c.first_name, c.last_name, i.total_amount, i.created_at
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        ORDER BY i.created_at DESC
    ")->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $sales
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}