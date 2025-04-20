<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $parent_id = (int)$_POST['parent_id'];
    $image = '';

    // آپلود تصویر
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image']);
    }

    // اعتبارسنجی داده‌ها
    if (empty($name)) {
        $error = 'لطفاً نام دسته‌بندی را وارد کنید.';
    }

    // ثبت دسته‌بندی در دیتابیس
    if (empty($error)) {
        if ($db->insert('categories', ['name' => $name, 'parent_id' => $parent_id, 'image' => $image])) {
            $success = 'دسته‌بندی جدید با موفقیت ثبت شد.';
        } else {
            $error = 'خطا در ثبت دسته‌بندی. لطفاً دوباره تلاش کنید.';
        }
    }
}

// دریافت لیست دسته‌بندی‌ها برای انتخاب دسته والد
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن دسته‌بندی جدید - <?php echo SITE_NAME; ?></title>
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
                        <h4 class="mb-0">افزودن دسته‌بندی جدید</h4>
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

                <form method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="parent_id">دسته والد</label>
                            <select id="parent_id" name="parent_id" class="form-select">
                                <option value="0">بدون والد</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label for="image">تصویر دسته‌بندی</label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            ذخیره دسته‌بندی
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