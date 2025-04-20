<?php
require_once 'init.php';

// بررسی درخواست AJAX
if (!isAjax()) {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر است']);
    exit;
}

// بررسی دسترسی کاربر
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
    exit;
}

try {
    if (!isset($_FILES['image'])) {
        throw new Exception('تصویری دریافت نشد');
    }

    $file = $_FILES['image'];
    $fileName = generateRandomString(10) . '_' . basename($file['name']);
    $uploadPath = '../uploads/customers/';
    
    // بررسی و ایجاد دایرکتوری
    if (!file_exists($uploadPath)) {
        if (!mkdir($uploadPath, 0755, true)) {
            throw new Exception('خطا در ایجاد پوشه آپلود');
        }
    }
    
    if (!is_writable($uploadPath)) {
        throw new Exception('پوشه آپلود قابل نوشتن نیست');
    }
    
    $targetPath = $uploadPath . $fileName;
    
    // بررسی نوع فایل
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        throw new Exception('فایل انتخاب شده تصویر نیست');
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('فرمت فایل مجاز نیست. فقط jpg, png و gif مجاز هستند');
    }
    
    // بررسی سایز فایل (حداکثر 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('حجم فایل نباید بیشتر از 2 مگابایت باشد');
    }
    
    // بررسی ابعاد تصویر
    if ($check[0] > 2000 || $check[1] > 2000) {
        throw new Exception('ابعاد تصویر نباید بیشتر از 2000x2000 پیکسل باشد');
    }
    
    // ذخیره فایل
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('خطا در آپلود فایل');
    }
    
    // ثبت لاگ
    logActivity($user['id'], 'آپلود تصویر پروفایل مشتری');
    
    echo json_encode([
        'success' => true,
        'imageUrl' => str_replace('../', '', $targetPath),
        'message' => 'تصویر با موفقیت آپلود شد'
    ]);
    
} catch (Exception $e) {
    error_log("خطا در آپلود تصویر پروفایل: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}