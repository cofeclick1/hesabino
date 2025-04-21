<?php
// بررسی دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- فونت‌ها و استایل‌های اصلی -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
    
    <!-- استایل‌های اختصاصی -->
    <?php if (isset($customCss) && is_array($customCss)): ?>
        <?php foreach ($customCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- تنظیمات متا -->
    <meta name="theme-color" content="#007bff">
    <meta name="description" content="<?php echo SITE_NAME; ?> - سیستم حسابداری و مدیریت کسب و کار">
</head>
<body class="rtl">