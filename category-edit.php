<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();
$error = '';
$success = '';
$category_id = (int)$_GET['id'];

// دریافت اطلاعات دسته‌بندی
$category = $db->query("SELECT * FROM categories WHERE id = ?", [$category_id])->fetch();
if (!$category) {
    redirect('categories.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    
    // اعتبارسنجی داده‌ها
    if (empty($name)) {
        $error = 'لطفاً نام دسته‌بندی را وارد کنید.';
    }
    
    // بروزرسانی دسته‌بندی در دیتابیس
    if (empty($error)) {
        if ($db->update('categories', ['name' => $name], 'id = ' . $category_id)) {
            $success = 'دسته‌بندی با موفقیت بروزرسانی شد.';
        } else {
            $error = 'خطا در بروزرسانی دسته‌بندی. لطفاً دوباره تلاش کنید.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش دسته‌بندی - <?php echo SITE_NAME; ?></title>
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
        <div class="main-content">
            <!-- Top Navbar -->
            <?php include 'includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <div class="row g-4 my-4">
                    <div class="col">
                        <h4 class="mb-0">ویرایش دسته‌بندی</h4>
                    </div>
                    <div class="col-auto">
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            بازگشت به لیست دسته‌بندی‌ها
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>