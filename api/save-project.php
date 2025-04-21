<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
header('Content-Type: application/json; charset=utf-8');

try {
    // دریافت داده‌های پروژه
    $projectName = sanitize($_POST['projectName'] ?? '');
    $projectCode = sanitize($_POST['projectCode'] ?? '');
    $projectDescription = sanitize($_POST['projectDescription'] ?? '');
    $isActive = isset($_POST['projectActive']) ? 1 : 0;
    
    if (empty($projectName)) {
        throw new Exception('نام پروژه الزامی است');
    }
    
    // بررسی تکراری نبودن نام پروژه
    $stmt = $db->query("SELECT id FROM projects WHERE name = ? AND deleted_at IS NULL", [$projectName]);
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
        
        $logoPath = '/uploads/logos/' . $logoPath;
    }
    
    // درج پروژه جدید
    $data = [
        'name' => $projectName,
        'code' => $projectCode,
        'description' => $projectDescription,
        'logo_path' => $logoPath,
        'status' => $isActive ? 'active' : 'inactive',
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $projectId = $db->insert('projects', $data);
    
    if (!$projectId) {
        throw new Exception('خطا در ثبت پروژه');
    }
    
    // ارسال پاسخ موفقیت
    echo json_encode([
        'success' => true,
        'message' => 'پروژه با موفقیت ثبت شد',
        'project' => [
            'id' => $projectId,
            'name' => $projectName,
            'logo_path' => $logoPath ? BASE_PATH . $logoPath : null
        ]
    ]);
    
} catch (Exception $e) {
    // اگر فایل لوگو آپلود شده بود، حذف شود
    if (isset($logoPath) && file_exists(__DIR__ . '/..' . $logoPath)) {
        unlink(__DIR__ . '/..' . $logoPath);
    }
    
    error_log('Error in save-project.php: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}