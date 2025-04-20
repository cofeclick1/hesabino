<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();
$error = '';
$success = '';

function generateProductCode($db) {
    do {
        $code = rand(100000, 999999);
        $stmt = $db->query("SELECT id FROM products WHERE code = ?", [$code]);
    } while ($stmt->rowCount() > 0);
    return $code;
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
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $technical_features = sanitize($_POST['technical_features']);
    $customs_tariff_code = sanitize($_POST['customs_tariff_code']);
    $barcode = sanitize($_POST['barcode']);
    $store_barcode = sanitize($_POST['store_barcode']);
    $image = '';

    // اعتبارسنجی داده‌ها
    if (empty($name) || empty($code) || empty($sale_price)) {
        $error = 'لطفاً تمام فیلدهای ضروری را پر کنید.';
    }
    
    // بررسی تکراری نبودن کد محصول
    if (empty($error)) {
        $stmt = $db->query("SELECT id FROM products WHERE code = ?", [$code]);
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
    
    // ثبت محصول در دیتابیس
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
            'brand' => $brand,
            'model' => $model,
            'technical_features' => $technical_features,
            'customs_tariff_code' => $customs_tariff_code,
            'barcode' => $barcode,
            'store_barcode' => $store_barcode,
            'image' => $image,
            'status' => $status
        ];
        
        if ($db->insert('products', $productData)) {
            $success = 'محصول جدید با موفقیت ثبت شد.';
        } else {
            $error = 'خطا در ثبت محصول. لطفاً دوباره تلاش کنید.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن محصول جدید - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-group label {
            font-weight: bold;
        }
        .alert {
            border-radius: 8px;
        }
        .hint-text {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
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
                <div class="row g-4 my-4">
                    <div class="col">
                        <h4 class="mb-0">افزودن محصول جدید</h4>
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
                    <div class="card-header">
                        <h5 class="mb-0">فرم افزودن محصول</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">نام محصول <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                    <small class="hint-text">مثال: لپ‌تاپ</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">کد محصول <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="code" name="code" class="form-control" required>
                                        <button type="button" id="generate-code" class="btn btn-outline-secondary">تولید کد</button>
                                    </div>
                                    <small class="hint-text">مثال: 123456</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">دسته‌بندی</label>
                                    <select id="category_id" name="category_id" class="form-select search-category">
                                        <option value="0">بدون دسته‌بندی</option>
                                    </select>
                                    <small class="hint-text">دسته‌بندی محصول را انتخاب کنید.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_price">قیمت خرید</label>
                                    <input type="text" id="purchase_price" name="purchase_price" class="form-control">
                                    <small class="hint-text">مثال: 5000000</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_price">قیمت فروش <span class="text-danger">*</span></label>
                                    <input type="text" id="sale_price" name="sale_price" class="form-control" required>
                                    <small class="hint-text">مثال: 6000000</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">موجودی</label>
                                    <input type="text" id="quantity" name="quantity" class="form-control">
                                    <small class="hint-text">مثال: 50</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="min_quantity">حداقل موجودی</label>
                                    <input type="text" id="min_quantity" name="min_quantity" class="form-control">
                                    <small class="hint-text">مثال: 10</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">توضیحات</label>
                                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                                    <small class="hint-text">توضیحات تکمیلی درباره محصول.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">تصویر محصول</label>
                                    <input type="file" id="image" name="image" class="form-control">
                                    <small class="hint-text">فرمت‌های مجاز: JPG, PNG</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">وضعیت</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="active">فعال</option>
                                        <option value="inactive">غیرفعال</option>
                                    </select>
                                    <small class="hint-text">وضعیت محصول را انتخاب کنید.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand">برند</label>
                                    <input type="text" id="brand" name="brand" class="form-control">
                                    <small class="hint-text">مثال: Apple</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model">مدل</label>
                                    <input type="text" id="model" name="model" class="form-control">
                                    <small class="hint-text">مثال: MacBook Pro 2021</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="technical_features">ویژگی‌های فنی</label>
                                    <textarea id="technical_features" name="technical_features" class="form-control" rows="4"></textarea>
                                    <small class="hint-text">ویژگی‌های فنی محصول را وارد کنید.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customs_tariff_code">کد تعرفه گمرکی</label>
                                    <input type="text" id="customs_tariff_code" name="customs_tariff_code" class="form-control">
                                    <small class="hint-text">در صورت وارداتی بودن، کد تعرفه گمرکی محصول را وارد کنید.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="barcode">بارکد محصول</label>
                                    <input type="text" id="barcode" name="barcode" class="form-control">
                                    <button type="button" id="scan-barcode" class="btn btn-outline-secondary mt-2">اسکن بارکد</button>
                                    <small class="hint-text">بارکد محصول را وارد کنید یا از بارکدخوان استفاده کنید.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store_barcode">بارکد فروشگاه</label>
                                    <input type="text" id="store_barcode" name="store_barcode" class="form-control" readonly>
                                    <button type="button" id="generate-store-barcode" class="btn btn-outline-secondary mt-2">تولید بارکد فروشگاه</button>
                                    <small class="hint-text">بارکد فروشگاه برای چاپ روی محصول.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            ذخیره محصول
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script>
        $(document).ready(function() {
            function generateRandomCode() {
                return Math.floor(100000 + Math.random() * 900000);
            }

            $('#generate-code').click(function() {
                $('#code').val(generateRandomCode());
            });

            $('#scan-barcode').click(function() {
                alert('اسکن بارکد محصول برای بروزرسانی بعدی فعال خواهد شد.');
            });

            $('#generate-store-barcode').click(function() {
                var category = $('#category_id option:selected').text();
                if(category) {
                    $.ajax({
                        url: 'https://api.mymemory.translated.net/get',
                        dataType: 'json',
                        data: {
                            q: category,
                            langpair: 'fa|en'
                        },
                        success: function(data) {
                            var translatedText = data.responseData.translatedText;
                            var prefix = translatedText.replace(/\s+/g, '').substring(0, 5).toLowerCase();
                            $.ajax({
                                url: 'generate_barcode.php',
                                method: 'POST',
                                data: { prefix: prefix },
                                success: function(response) {
                                    var result = JSON.parse(response);
                                    var barcode = result.barcode;
                                    $('#store_barcode').val(barcode);
                                }
                            });
                        }
                    });
                } else {
                    alert('لطفاً دسته‌بندی را انتخاب کنید.');
                }
            });

            // Generate product code automatically when the page loads
            $('#code').val(generateRandomCode());

            $('.search-category').select2({
                placeholder: 'دسته‌بندی را انتخاب کنید',
                allowClear: true,
                ajax: {
                    url: 'search-categories.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
</body>
</html>