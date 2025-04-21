<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// بررسی دسترسی
if (!$auth->hasPermission('payment.add') && !$_SESSION['is_super_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// دریافت داده‌ها
$text = sanitize($_POST['text'] ?? '');

if (empty($text)) {
    echo json_encode(['success' => false, 'message' => 'متن شرح الزامی است']);
    exit;
}

try {
    // ذخیره شرح در دیتابیس
    $db->insert('recurring_descriptions', [
        'text' => $text,
        'type' => 'payment',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $db->lastInsertId(),
            'text' => $text
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در ذخیره اطلاعات'
    ]);
}