<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
header('Content-Type: application/json; charset=utf-8');

try {
    // بررسی دسترسی
    if (!$auth->hasPermission('projects_add') && !$_SESSION['is_super_admin']) {
        throw new Exception('شما دسترسی لازم برای این عملیات را ندارید');
    }
    
    // دریافت داده‌های پروژه
    $projectName = sanitize($_POST['projectName'] ?? '');
    $projectCode = sanitize($_POST['projectCode'] ?? '');
    $projectDescription = sanitize($_POST['projectDescription'] ?? '');
    $isActive = isset($_POST['projectActive']) ? 1 : 0;
    $isDefault = isset($_POST['projectDefault']) ? 1 : 0;
    
    if (empty($projectName)) {
        throw new Exception('نام پروژه الزامی است');
    }
    
    // بررسی تکراری نبودن نام پروژه
    $stmt = $db->prepare("SELECT id FROM projects WHERE name = ? AND deleted_at IS NULL");
    $stmt->execute([$projectName]);
    if ($stmt->fetch()) {
        throw new Exception('پروژه‌ای با این نام قبلاً ثبت شده است');
    }
    
    // آپلود لوگو اگر ارسال شده باشد
    $logoPath = null;
    if (isset($_FILES['projectLogo']) && $_FILES['projectLogo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['projectLogo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($ext, $allowedTypes)) {
            throw new Exception('فرمت فایل لوگو معتبر نیست');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            throw new Exception('حجم فایل لوگو نباید بیشتر از 2 مگابایت باشد');
        }
        
        $logoPath = uniqid('project_') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $logoPath)) {
            throw new Exception('خطا در آپلود فایل لوگو');
        }
    }
    
    // شروع تراکنش
    $db->beginTransaction();
    
    // اگر این پروژه پیش‌فرض است، پروژه‌های دیگر از حالت پیش‌فرض خارج شوند
    if ($isDefault) {
        $stmt = $db->prepare("UPDATE projects SET is_default = 0 WHERE is_default = 1");
        $stmt->execute();
    }
    
    // درج پروژه جدید
    $stmt = $db->prepare("
        INSERT INTO projects (
            name, code, description, logo_path, 
            status, is_default, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $status = $isActive ? 'active' : 'inactive';
    if (!$stmt->execute([
        $projectName, 
        $projectCode, 
        $projectDescription,
        $logoPath,
        $status,
        $isDefault,
        $_SESSION['user_id']
    ])) {
        throw new Exception('خطا در ثبت پروژه');
    }
    
    $projectId = $db->lastInsertId();
    
    $db->commit();
    
    // ارسال پاسخ موفقیت
    echo json_encode([
        'success' => true,
        'message' => 'پروژه با موفقیت ثبت شد',
        'project' => [
            'id' => $projectId,
            'name' => $projectName,
            'logo_path' => $logoPath ? BASE_PATH . '/uploads/logos/' . $logoPath : null
        ]
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollback();
    }
    
    // اگر فایل لوگو آپلود شده بود، حذف شود
    if (isset($logoPath) && file_exists(__DIR__ . '/../uploads/logos/' . $logoPath)) {
        unlink(__DIR__ . '/../uploads/logos/' . $logoPath);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}