<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();
$error = '';
$success = '';
$product_id = (int)$_GET['id'];

// دریافت اطلاعات محصول
$product = $db->query("SELECT * FROM products WHERE id = ?", [$product_id])->fetch();
if (!$product) {
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $code = sanitize($_POST['code']);
    $category_id = (int)$_POST['category_id'];
    $purchase_price = (float)$_POST['purchase_price'];
    $sale_price = (float)$_POST['sale_price'];
    $quantity = (int)$_POST['quantity'];
    $min_quantity = (int)$_POST['min_quantity'];
    $description = sanitize($_POST['description']);
    $status = $_POST['status'] ?? 'inactive';
    
    // اعتبارسنجی داده‌ها
    if (empty($name) || empty($code) || empty($sale_price)) {
        $error = 'لطفاً تمام فیلدهای ضروری را پر کنید.';
    }
    
    // بررسی تکراری نبودن کد محصول
    if (empty($error)) {
        $stmt = $db->query("SELECT id FROM products WHERE code = ? AND id != ?", [$code, $product_id]);
        if ($stmt->rowCount() > 0) {
            $error = 'این کد محصول قبلاً ثبت شده است.';
        }
    }
    
    // آپلود تصویر
    if (empty($error) && !empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image']);
        if ($image === false) {
            $error = 'خطا در آپلود تصویر. لطفاً دوباره تلاش کنید.';
        }
    }
    
    // بروزرسانی محصول در دیتابیس
    if (empty($error)) {
        $productData = [
            'name' => $name,
            'code' => $code,
            'category_id' => $category_id,
            'purchase_price' => $purchase_price,
            'sale_price' => $sale_price,
            'quantity' => $quantity,
            'min_quantity' => $min_quantity,
            'description' => $description,
            'image' => $image ?? $product['image'],
            'status' => $status
        ];
        
        if ($db->update('products', $productData, 'id = ' . $product_id)) {
            $success = 'محصول با موفقیت بروزرسانی شد.';
        } else {
            $error = 'خطا در بروزرسانی محصول. لطفاً دوباره تلاش کنید.';
        }
    }
}

// دریافت دسته‌بندی‌ها
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش محصول - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/products.css">
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
                        <h4 class="mb-0">ویرایش محصول</h4>
                    </div>
                    <div class="col-auto">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            بازگشت به لیست محصولات
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
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">نام محصول <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">کد محصول <span class="text-danger">*</span></label>
                                    <input type="text" id="code" name="code" class="form-control" value="<?php echo htmlspecialchars($product['code']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">دسته‌بندی</label>
                                    <select id="category_id" name="category_id" class="form-select">
                                        <option value="0">بدون دسته‌بندی</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_price">قیمت خرید</label>
                                    <input type="text" id="purchase_price" name="purchase_price" class="form-control" value="<?php echo htmlspecialchars($product['purchase_price']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_price">قیمت فروش <span class="text-danger">*</span></label>
                                    <input type="text" id="sale_price" name="sale_price" class="form-control" value="<?php echo htmlspecialchars($product['sale_price']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">موجودی</label>
                                    <input type="text" id="quantity" name="quantity" class="form-control" value="<?php echo htmlspecialchars($product['quantity']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="min_quantity">حداقل موجودی</label>
                                    <input type="text" id="min_quantity" name="min_quantity" class="form-control" value="<?php echo htmlspecialchars($product['min_quantity']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">توضیحات</label>
                                    <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">تصویر محصول</label>
                                    <input type="file" id="image" name="image" class="form-control">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail mt-2">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">وضعیت</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>فعال</option>
                                        <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                    </select>
                                </div>
                            </div>
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