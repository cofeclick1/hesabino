<?php
require_once '../includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}
// تنظیم عنوان صفحه و متغیرهای اولیه
$pageTitle = 'افزودن محصول جدید';
$db = Database::getInstance();
$error = '';
$success = '';
// بررسی دسترسی کاربر
if (!$auth->hasPermission('products_add') && !$_SESSION['is_super_admin']) {
    $_SESSION['error'] = 'شما دسترسی لازم برای این عملیات را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// تنظیمات پایه
$pageTitle = 'افزودن محصول جدید';
$db = Database::getInstance();
$error = '';
$success = '';

// تولید کد محصول
function generateProductCode($db) {
    do {
        $code = rand(100000, 999999);
        $stmt = $db->query("SELECT id FROM products WHERE code = ?", [$code]);
    } while ($stmt->rowCount() > 0);
    return $code;
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // دریافت داده‌ها
        $name = sanitize($_POST['name']);
        $code = sanitize($_POST['code']);
        $category_id = (int)$_POST['category_id'];
        $purchase_price = str_replace(',', '', $_POST['purchase_price']);
        $sale_price = str_replace(',', '', $_POST['sale_price']);
        $quantity = (int)$_POST['quantity'];
        $min_quantity = (int)$_POST['min_quantity'];
        $description = sanitize($_POST['description']);
        $status = $_POST['status'] ?? 'active';
        $brand = sanitize($_POST['brand']);
        $model = sanitize($_POST['model']);
        $technical_features = sanitize($_POST['technical_features']);
        $customs_tariff_code = sanitize($_POST['customs_tariff_code']);
        $barcode = sanitize($_POST['barcode']);
        $store_barcode = sanitize($_POST['store_barcode']);
        $image = '';

        // اعتبارسنجی داده‌ها
        $validator = new Validator();
        $validator->addRule('name', 'required', 'لطفاً نام محصول را وارد کنید');
        $validator->addRule('code', 'required|unique:products', 'کد محصول تکراری است');
        $validator->addRule('sale_price', 'required|numeric|min:0', 'قیمت فروش باید عدد مثبت باشد');
        
        if (!$validator->validate($_POST)) {
            throw new Exception($validator->getFirstError());
        }

        // آپلود تصویر
        if (!empty($_FILES['image']['name'])) {
            $uploader = new FileUploader([
                'upload_dir' => '../uploads/products/',
                'allowed_types' => ['image/jpeg', 'image/png'],
                'max_size' => 5242880, // 5MB
                'create_directory' => true
            ]);

            $uploadedFile = $uploader->upload($_FILES['image']);
            if ($uploadedFile) {
                $image = 'uploads/products/' . $uploadedFile;
            }
        }

        // ذخیره در دیتابیس
        $productData = [
            'name' => $name,
            'code' => $code,
            'category_id' => $category_id ?: null,
            'purchase_price' => $purchase_price,
            'sale_price' => $sale_price,
            'quantity' => $quantity,
            'min_quantity' => $min_quantity,
            'description' => $description,
            'brand' => $brand,
            'model' => $model,
            'technical_features' => $technical_features,
            'customs_tariff_code' => $customs_tariff_code,
            'barcode' => $barcode,
            'store_barcode' => $store_barcode,
            'image' => $image,
            'status' => $status,
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->beginTransaction();
        
        $productId = $db->insert('products', $productData);
        if (!$productId) {
            throw new Exception('خطا در ثبت اطلاعات محصول');
        }

        // ثبت در تاریخچه
        logActivity('افزودن محصول جدید: ' . $name, 'product', $productId);

        $db->commit();
        $success = 'محصول جدید با موفقیت ثبت شد';

        // ریدایرکت به صفحه لیست محصولات
        if (!empty($_POST['redirect_to_list'])) {
            redirect('list.php');
        }

    } catch (Exception $e) {
        $db->rollback();
        $error = $e->getMessage();
    }
}

// دریافت لیست دسته‌بندی‌ها
$categories = $db->query("
    SELECT id, name, parent_id 
    FROM categories 
    WHERE status = 'active' 
    AND deleted_at IS NULL 
    ORDER BY parent_id ASC, name ASC
")->fetchAll();

// تنظیم عنوان صفحه
$pageTitle = 'افزودن محصول جدید';

// شامل کردن فایل header
require_once '../includes/header.php';
?>

<!-- شروع محتوای اصلی -->
<div class="container-fluid">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- محتوای اصلی -->
        <div class="main-content flex-grow-1 p-4">
            <!-- نوار بالایی -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-dark me-3 sidebar-toggler">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0"><?php echo $pageTitle ?></h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>
                        لیست محصولات
                    </a>
                    <button type="submit" form="productForm" name="redirect_to_list" value="1" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        ذخیره و بازگشت
                    </button>
                    <button type="submit" form="productForm" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        ذخیره
                    </button>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- فرم اصلی -->
            <form id="productForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row g-4">
                    <!-- اطلاعات اصلی -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#basic-info">
                                            <i class="fas fa-info-circle me-1"></i>
                                            اطلاعات اصلی
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#technical-info">
                                            <i class="fas fa-cogs me-1"></i>
                                            مشخصات فنی
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#inventory">
                                            <i class="fas fa-box me-1"></i>
                                            موجودی و قیمت
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#other">
                                            <i class="fas fa-ellipsis-h me-1"></i>
                                            سایر
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- تب اطلاعات اصلی -->
                                    <div class="tab-pane fade show active" id="basic-info">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-label required">نام محصول</label>
                                                    <input type="text" class="form-control" id="name" name="name" required
                                                           value="<?php echo old('name') ?>"
                                                           placeholder="نام کامل محصول را وارد کنید">
                                                    <div class="invalid-feedback">لطفاً نام محصول را وارد کنید</div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="code" class="form-label required">کد محصول</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="code" name="code" required
                                                               value="<?php echo old('code', generateProductCode($db)) ?>"
                                                               placeholder="کد منحصر به فرد محصول">
                                                        <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                                            <i class="fas fa-random"></i>
                                                            تولید کد
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback">کد محصول الزامی است</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category_id" class="form-label">دسته‌بندی</label>
                                                    <select class="form-control select2-category" id="category_id" name="category_id">
                                                        <option value="">انتخاب دسته‌بندی...</option>
                                                    </select>
                                                    <div class="form-text">برای جستجو نام دسته‌بندی را تایپ کنید</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="brand" class="form-label">برند</label>
                                                    <input type="text" class="form-control" id="brand" name="brand"
                                                           value="<?php echo old('brand') ?>"
                                                           placeholder="نام برند محصول">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="description" class="form-label">توضیحات</label>
                                                    <textarea class="form-control" id="description" name="description" rows="4"
                                                              placeholder="توضیحات تکمیلی محصول..."><?php echo old('description') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- تب مشخصات فنی -->
                                    <div class="tab-pane fade" id="technical-info">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="model" class="form-label">مدل</label>
                                                    <input type="text" class="form-control" id="model" name="model"
                                                           value="<?php echo old('model') ?>"
                                                           placeholder="مدل محصول">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="customs_tariff_code" class="form-label">کد تعرفه گمرکی</label>
                                                    <input type="text" class="form-control" id="customs_tariff_code" 
                                                           name="customs_tariff_code"
                                                           value="<?php echo old('customs_tariff_code') ?>"
                                                           placeholder="کد تعرفه گمرکی محصول">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="technical_features" class="form-label">ویژگی‌های فنی</label>
                                                    <textarea class="form-control" id="technical_features" name="technical_features" 
                                                              rows="4"
                                                              placeholder="هر ویژگی را در یک خط جداگانه بنویسید..."
                                                    ><?php echo old('technical_features') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- تب موجودی و قیمت -->
                                    <div class="tab-pane fade" id="inventory">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="purchase_price" class="form-label">قیمت خرید</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control price-input text-start" 
                                                               id="purchase_price" name="purchase_price"
                                                               value="<?php echo old('purchase_price') ?>"
                                                               placeholder="0">
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sale_price" class="form-label required">قیمت فروش</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control price-input text-start" 
                                                               id="sale_price" name="sale_price" required
                                                               value="<?php echo old('sale_price') ?>"
                                                               placeholder="0">
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                    <div class="invalid-feedback">قیمت فروش الزامی است</div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div id="profitMargin"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="quantity" class="form-label">موجودی</label>
                                                    <input type="number" class="form-control" id="quantity" name="quantity"
                                                           value="<?php echo old('quantity', 0) ?>"
                                                           min="0" placeholder="0">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="min_quantity" class="form-label">حداقل موجودی</label>
                                                    <input type="number" class="form-control" id="min_quantity" name="min_quantity"
                                                           value="<?php echo old('min_quantity', 0) ?>"
                                                           min="0" placeholder="0">
                                                    <div class="form-text">هشدار کمبود موجودی در این مقدار نمایش داده می‌شود</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- تب سایر -->
                                    <div class="tab-pane fade" id="other">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="barcode" class="form-label">بارکد محصول</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="barcode" name="barcode"
                                                               value="<?php echo old('barcode') ?>"
                                                               placeholder="بارکد محصول را وارد یا اسکن کنید">
                                                        <button type="button" class="btn btn-outline-secondary" id="scanBarcode">
                                                            <i class="fas fa-barcode"></i>
                                                            اسکن
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="store_barcode" class="form-label">بارکد فروشگاه</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="store_barcode" 
                                                               name="store_barcode" readonly
                                                               value="<?php echo old('store_barcode') ?>"
                                                               placeholder="بارکد فروشگاه به صورت خودکار تولید می‌شود">
                                                        <button type="button" class="btn btn-outline-secondary" id="generateStoreBarcode">
                                                            <i class="fas fa-sync"></i>
                                                            تولید بارکد
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status" class="form-label">وضعیت</label>
                                                    <select class="form-control" id="status" name="status">
                                                        <option value="active" <?php echo old('status') == 'active' ? 'selected' : '' ?>>فعال</option>
                                                        <option value="inactive" <?php echo old('status') == 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ستون سمت راست -->
                    <div class="col-lg-4">
                        <!-- کارت تصویر محصول -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-image me-2"></i>
                                    تصویر محصول
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="image-upload-wrapper" id="imageUploadWrapper">
                                    <input type="file" id="image" name="image" class="d-none" accept="image/*">
                                    <div class="upload-content text-center">
                                        <i class="fas fa-cloud-upload-alt mb-3"></i>
                                        <p class="mb-1">برای آپلود تصویر کلیک کنید یا فایل را اینجا رها کنید</p>
                                        <small class="text-muted">حداکثر حجم: 5MB | فرمت‌های مجاز: JPG, PNG</small>
                                    </div>
                                </div>
                                <div id="imagePreview" class="mt-3 text-center d-none">
                                    <img src="" alt="پیش‌نمایش تصویر" class="img-fluid rounded">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                                        <i class="fas fa-trash me-1"></i>
                                        حذف تصویر
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال اسکن بارکد -->
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اسکن بارکد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reader"></div>
            </div>
        </div>
    </div>
</div>

<?php
// افزودن فایل‌های CSS و JS مورد نیاز
$customCss = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
    'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
    BASE_PATH . '/assets/css/products.css'
];

$customJs = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
    'https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js',
    'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/dist/html5-qrcode.min.js',
    BASE_PATH . '/assets/js/products.js'
];

require_once '../includes/footer.php';
?>