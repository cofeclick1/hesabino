<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// بررسی درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
    exit;
}

// دریافت داده‌ها
$name = sanitize($_POST['name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

// اعتبارسنجی
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'نام پروژه الزامی است']);
    exit;
}

try {
    // بررسی تکراری نبودن نام پروژه
    $existing = $db->query(
        "SELECT id FROM projects WHERE name = ? AND deleted_at IS NULL",
        [$name]
    )->fetch();

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'این نام پروژه قبلاً ثبت شده است']);
        exit;
    }

    // درج پروژه جدید
    $projectId = $db->insert('projects', [
        'name' => $name,
        'description' => $description,
        'status' => 'active',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode([
        'success' => true,
        'project' => [
            'id' => $projectId,
            'name' => $name
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در ثبت اطلاعات'
    ]);
}