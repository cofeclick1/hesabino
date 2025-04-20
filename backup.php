<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// مسیر پوشه پشتیبان‌ها
$backupDir = 'backups/';

// بررسی و ایجاد پوشه پشتیبان در صورت عدم وجود
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// ایجاد پشتیبان از دیتابیس
if (isset($_POST['create_backup'])) {
    $backupFile = $backupDir . 'backup_' . date('Ymd_His') . '.sql';
    $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > " . $backupFile;
    exec($command, $output, $result);

    if ($result === 0) {
        flashMessage('پشتیبان دیتابیس با موفقیت ایجاد شد', 'success');
    } else {
        flashMessage('خطا در ایجاد پشتیبان دیتابیس: ' . implode("\n", $output), 'danger');
    }

    header('Location: backup.php');
    exit;
}

// ایجاد پشتیبان از فایل‌های اکسل
if (isset($_POST['create_excel_backup'])) {
    if (!extension_loaded('zip')) {
        flashMessage('اکستنشن zip در PHP فعال نیست', 'danger');
    } else {
        $backupFile = $backupDir . 'excel_backup_' . date('Ymd_His') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE) === TRUE) {
            $excelFiles = glob('path_to_excel_files/*.xlsx'); // مسیر فایل‌های اکسل
            foreach ($excelFiles as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
            if (file_exists($backupFile)) {
                flashMessage('پشتیبان فایل‌های اکسل با موفقیت ایجاد شد', 'success');
            } else {
                flashMessage('خطا در ایجاد پشتیبان فایل‌های اکسل: فایل ZIP ایجاد نشد', 'danger');
            }
        } else {
            flashMessage('خطا در ایجاد پشتیبان فایل‌های اکسل', 'danger');
        }
    }

    header('Location: backup.php');
    exit;
}

// دانلود فایل پشتیبان
if (isset($_GET['download'])) {
    $file = $backupDir . $_GET['download'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        flush();
        readfile($file);
        exit;
    } else {
        flashMessage('فایل پشتیبان یافت نشد', 'danger');
        header('Location: backup.php');
        exit;
    }
}

// دریافت لیست فایل‌های پشتیبان
$backups = [];
if (is_dir($backupDir)) {
    if ($dh = opendir($backupDir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..' && (pathinfo($file, PATHINFO_EXTENSION) == 'sql' || pathinfo($file, PATHINFO_EXTENSION) == 'zip')) {
                $backups[] = $file;
            }
        }
        closedir($dh);
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پشتیبان‌گیری - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Top Navbar -->
            <?php include 'includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <?php echo showFlashMessage(); ?>
                <div class="row g-4 my-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-primary">
                                <h4 class="card-title">پشتیبان‌گیری</h4>
                                <p class="card-category">مدیریت پشتیبان‌های سیستم</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <button type="submit" name="create_backup" class="btn btn-primary">ایجاد پشتیبان دیتابیس</button>
                                    <button type="submit" name="create_excel_backup" class="btn btn-secondary">ایجاد پشتیبان فایل‌های اکسل</button>
                                </form>
                                <hr>
                                <h4>لیست پشتیبان‌ها</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>نام فایل</th>
                                            <th>تاریخ ایجاد</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($backups)): ?>
                                            <?php foreach ($backups as $backup): ?>
                                                <tr>
                                                    <td><?php echo $backup; ?></td>
                                                    <td><?php echo date('Y-m-d H:i:s', filemtime($backupDir . $backup)); ?></td>
                                                    <td>
                                                        <a href="backup.php?download=<?php echo urlencode($backup); ?>" class="btn btn-sm btn-success">دانلود</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3">هیچ پشتیبانی موجود نیست</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>