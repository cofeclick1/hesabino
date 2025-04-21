<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    exit('Direct access not permitted');
}

try {
    // دریافت متن توضیحات
    $text = sanitize($_POST['text']);
    $type = sanitize($_POST['type'] ?? 'payment');
    
    if (empty($text)) {
        throw new Exception('متن توضیحات الزامی است');
    }
    
    // بررسی تکراری نبودن متن
    $existingDesc = $db->get('recurring_descriptions', 'id', [
        'text' => $text,
        'type' => $type,
        'deleted_at IS' => null
    ]);
    
    if ($existingDesc) {
        throw new Exception('این متن قبلاً ثبت شده است');
    }
    
    // درج توضیحات جدید
    $id = $db->insert('recurring_descriptions', [
        'text' => $text,
        'type' => $type,
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if (!$id) {
        throw new Exception('خطا در ثبت توضیحات');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'توضیحات با موفقیت ثبت شد',
        'description' => [
            'id' => $id,
            'text' => $text
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}