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
    $stmt = $db->prepare("
        SELECT id 
        FROM recurring_descriptions 
        WHERE text = ? AND type = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$text, $type]);
    
    if ($stmt->fetch()) {
        throw new Exception('این متن قبلاً ثبت شده است');
    }
    
    // درج توضیحات جدید
    $stmt = $db->prepare("
        INSERT INTO recurring_descriptions (text, type, created_by, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    if (!$stmt->execute([$text, $type, $_SESSION['user_id']])) {
        throw new Exception('خطا در ثبت توضیحات');
    }
    
    $id = $db->lastInsertId();
    
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