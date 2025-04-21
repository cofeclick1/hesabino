<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // دریافت شرح‌های پرتکرار از دیتابیس
    $descriptions = $db->query("
        SELECT id, text 
        FROM recurring_descriptions 
        WHERE type = 'payment' 
        AND deleted_at IS NULL
        ORDER BY created_at DESC
    ")->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $descriptions
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در دریافت اطلاعات'
    ]);
}