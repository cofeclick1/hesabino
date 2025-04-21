<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// بررسی درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

// دریافت داده‌ها
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$mobile = sanitize($_POST['mobile'] ?? '');

// اعتبارسنجی
if (empty($firstName) || empty($lastName) || empty($mobile)) {
    echo json_encode(['success' => false, 'message' => 'لطفاً همه فیلدهای ضروری را تکمیل کنید']);
    exit;
}

if (!preg_match('/^09[0-9]{9}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'شماره موبایل معتبر نیست']);
    exit;
}

try {
    // بررسی تکراری نبودن موبایل
    $existing = $db->query(
        "SELECT id FROM people WHERE mobile = ? AND deleted_at IS NULL",
        [$mobile]
    )->fetch();

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'این شماره موبایل قبلاً ثبت شده است']);
        exit;
    }

    // درج شخص جدید
    $personId = $db->insert('people', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'mobile' => $mobile,
        'type' => 'real',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // ثبت در جدول تغییرات
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

    echo json_encode([
        'success' => true,
        'person' => [
            'id' => $personId,
            'name' => $firstName . ' ' . $lastName
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در ثبت اطلاعات'
    ]);
}