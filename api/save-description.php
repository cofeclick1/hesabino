<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
header('Content-Type: application/json; charset=utf-8');

try {
    // دریافت متن توضیحات
    $text = sanitize($_POST['text'] ?? '');
    $type = sanitize($_POST['type'] ?? 'payment');
    
    if (empty($text)) {
        throw new Exception('متن توضیحات الزامی است');
    }
    
    // بررسی تکراری نبودن متن
    $stmt = $db->query("SELECT id FROM recurring_descriptions WHERE text = ? AND type = ? AND deleted_at IS NULL", 
        [$text, $type]
    );
    
    if ($stmt->fetch()) {
        throw new Exception('این متن قبلاً ثبت شده است');
    }
    
    // درج توضیحات جدید
    $data = [
        'text' => $text,
        'type' => $type,
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $descriptionId = $db->insert('recurring_descriptions', $data);
    
    if (!$descriptionId) {
        throw new Exception('خطا در ثبت توضیحات');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'توضیحات با موفقیت ثبت شد',
        'description' => [
            'id' => $descriptionId,
            'text' => $text
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error in save-description.php: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}