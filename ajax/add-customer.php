<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        'status' => 'error',
        'message' => 'درخواست نامعتبر است'
    ]));
}

try {
    $db->beginTransaction();

    // اعتبارسنجی داده‌ها
    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['mobile'])) {
        throw new Exception('لطفاً تمام فیلدهای الزامی را تکمیل کنید');
    }

    if (!preg_match('/^09[0-9]{9}$/', $_POST['mobile'])) {
        throw new Exception('فرمت شماره موبایل صحیح نیست');
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('فرمت ایمیل صحیح نیست');
    }

    // بررسی تکراری نبودن موبایل
    $exists = $db->query(
        "SELECT COUNT(*) FROM customers WHERE mobile = ? AND deleted_at IS NULL",
        [$_POST['mobile']]
    )->fetchColumn();

    if ($exists > 0) {
        throw new Exception('این شماره موبایل قبلاً ثبت شده است');
    }

    // تولید کد مشتری
    $jalaliDate = jdate('Y-m-d', time(), '', 'Asia/Tehran', 'en');
    list($year, $month, $day) = explode('-', $jalaliDate);
    $dateCode = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
    
    // دریافت و افزایش آخرین شماره سریال
    $result = $db->query("SELECT last_number FROM customer_counter WHERE id = 1")->fetch();
    $lastNumber = isset($result['last_number']) ? (int)$result['last_number'] : 0;
    $newNumber = $lastNumber + 1;
    
    // بروزرسانی شماره در جدول شمارنده
    $db->update('customer_counter', 
        ['last_number' => $newNumber],
        ['id' => 1]
    );
    
    // ترکیب تاریخ و شماره سریال
    $customerCode = $dateCode . sprintf('%04d', $newNumber);

    // درج مشتری جدید
    $data = [
        'code' => $customerCode,
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
        'mobile' => $_POST['mobile'],
        'email' => $_POST['email'] ?? null,
        'address' => $_POST['address'] ?? null,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => $_SESSION['user_id'],
        'created_date' => date('Y-m-d')
    ];

    $db->insert('customers', $data);
    $customerId = $db->lastInsertId();

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'مشتری با موفقیت ثبت شد',
        'customer_id' => $customerId
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}