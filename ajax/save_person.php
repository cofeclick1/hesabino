<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

try {
    // دریافت و اعتبارسنجی داده‌ها
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    
    // بررسی داده‌های اجباری
    if (empty($firstName) || empty($lastName) || empty($mobile)) {
        echo json_encode(['success' => false, 'message' => 'لطفاً همه فیلدهای ضروری را تکمیل کنید']);
        exit;
    }
    
    // بررسی فرمت موبایل
    if (!preg_match('/^09[0-9]{9}$/', $mobile)) {
        echo json_encode(['success' => false, 'message' => 'شماره موبایل معتبر نیست']);
        exit;
    }
    
    // بررسی تکراری نبودن موبایل
    $existing = $db->query(
        "SELECT id FROM people WHERE mobile = ? AND deleted_at IS NULL",
        [$mobile]
    )->fetch();
    
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'این شماره موبایل قبلاً ثبت شده است']);
        exit;
    }
    
    // شروع تراکنش
    $db->beginTransaction();
    
    // درج شخص جدید
    $personId = $db->insert('people', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'mobile' => $mobile,
        'type' => 'real',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // درج در جدول تغییرات
    $db->insert('changes', [
        'table_name' => 'people',
        'record_id' => $personId,
        'action' => 'create',
        'user_id' => $user['id'],
        'changes' => json_encode([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile' => $mobile,
            'type' => 'real'
        ]),
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // پایان تراکنش
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'person' => [
            'id' => $personId,
            'name' => $firstName . ' ' . $lastName,
            'mobile' => $mobile
        ]
    ]);

} catch (Exception $e) {
    // برگرداندن تراکنش در صورت خطا
    if ($db->inTransaction()) {
        $db->rollback();
    }
    
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در ثبت اطلاعات: ' . $e->getMessage()
    ]);
}