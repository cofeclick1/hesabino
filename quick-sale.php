<?php
require_once 'includes/init.php';
require_once 'includes/jdf.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست مشتریان
$customers = $db->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();

// دریافت آمار فروش امروز
$today = date('Y-m-d');
$today_sales = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
    FROM invoices 
    WHERE DATE(created_at) = ?", 
    [$today]
)->fetch();

// دریافت محصولات پرفروش
$popular_products = $db->query("
    SELECT p.*, c.name as category_name, COUNT(i.id) as sale_count,
        CASE 
            WHEN p.quantity = 0 THEN 'ناموجود'
            WHEN p.quantity <= p.min_quantity THEN CONCAT(p.quantity, ' عدد (کم)')
            ELSE CONCAT(p.quantity, ' عدد')
        END as stock_status
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN invoice_items i ON p.id = i.product_id 
    WHERE p.status = 'active'
    GROUP BY p.id 
    ORDER BY sale_count DESC, p.id DESC
    LIMIT 12
")->fetchAll();

// دریافت دسته‌بندی‌های پرفروش
$popular_categories = $db->query("
    SELECT c.*, COUNT(i.id) as sale_count,
           (SELECT COUNT(*) FROM products p2 WHERE p2.category_id = c.id AND p2.status = 'active') as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    LEFT JOIN invoice_items i ON p.id = i.product_id
    WHERE c.status = 'active'
    GROUP BY c.id
    HAVING product_count > 0
    ORDER BY sale_count DESC
    LIMIT 10
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فروش سریع - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/quick-sale.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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

                <!-- آمار فروش امروز -->
                <div class="row my-4">
                    <div class="col-12">
                        <div class="stats-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>آمار فروش امروز - <?php echo jdate('l j F Y', strtotime($today)); ?></h4>
                                    <p class="mb-0">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        تعداد فروش: <?php echo number_format($today_sales['count']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-money-bill me-2"></i>
                                        مبلغ کل: <?php echo number_format($today_sales['total']); ?> تومان
                                    </p>
                                </div>
                                <div class="col-md-6 text-start">
                                    <div class="fs-5" id="current-time"><?php echo jdate('H:i:s'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- بخش محصولات -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">افزودن محصول به فاکتور</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="barcode-scan">
                                    <i class="fas fa-barcode me-1"></i>
                                    اسکن بارکد
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- جستجوی محصول -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="position-relative">
                                            <input type="text" id="product-search" class="form-control form-control-lg" 
                                                   placeholder="نام، کد یا بارکد محصول را وارد کنید...">
                                            <div id="search-results" class="product-search-results d-none"></div>
                                        </div>
                                    </div>
                                </div>

                                  <!-- دسته‌بندی‌های محصولات -->
                                <div class="category-tabs">
                                    <div class="category-tab active" data-category="">همه محصولات</div>
                                    <?php foreach ($popular_categories as $category): ?>
                                    <div class="category-tab" data-category="<?php echo $category['id']; ?>" 
                                        data-product-count="<?php echo $category['product_count']; ?>">
                                        <?php echo $category['name']; ?>
                                        <small class="text-muted">(<?php echo $category['product_count']; ?>)</small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- محصولات پرفروش -->
                                <div class="product-grid">
         <?php foreach ($popular_products as $product): ?>
        <div class="product-card" data-product-id="<?php echo $product['id']; ?>" 
             data-category="<?php echo $product['category_id']; ?>">
            <div class="product-image">
                <?php if (!empty($product['image'])): ?>
                <img src="<?php echo $product['image']; ?>" 
                     class="img-fluid mb-2" alt="<?php echo $product['name']; ?>">
                <?php else: ?>
                <div class="no-image">
                    <i class="fas fa-image text-muted"></i>
                </div>
                <?php endif; ?>
            </div>
            <h6 class="mb-2"><?php echo $product['name']; ?></h6>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-success fw-bold">
                    <?php echo number_format($product['sale_price']); ?> تومان
                </span>
                <span class="badge bg-<?php echo $product['quantity'] > $product['min_quantity'] ? 'info' : 'warning'; ?>">
                    <?php echo $product['stock_status']; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
                            </div>
                        </div>
                    </div>

                    <!-- بخش فاکتور -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">اطلاعات فاکتور</h5>
                            </div>
                            <div class="card-body">
                                <!-- انتخاب مشتری -->
                                <div class="mb-3">
                                    <label class="form-label">انتخاب مشتری</label>
                                    <div class="input-group">
                                        <select id="customer" class="form-select">
                                            <option value="">مشتری متفرقه</option>
                                            <?php foreach ($customers as $customer): ?>
                                            <option value="<?php echo $customer['id']; ?>">
                                                <?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                                data-bs-target="#newCustomerModal">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- اقلام فاکتور -->
                                <div id="cart-items" class="mb-3"></div>

                                <!-- خلاصه فاکتور -->
                                <div class="bg-light p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>جمع کل:</span>
                                        <span id="subtotal">0 تومان</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>مالیات (9%):</span>
                                        <span id="tax">0 تومان</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>تخفیف:</span>
                                        <div class="input-group input-group-sm" style="width: 150px">
                                            <input type="number" id="discount-amount" class="form-control" value="0">
                                            <select id="discount-type" class="form-select" style="width: 60px">
                                                <option value="amount">تومان</option>
                                                <option value="percent">درصد</option>
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>قابل پرداخت:</span>
                                        <span id="total" class="text-primary">0 تومان</span>
                                    </div>
                                </div>

                                <!-- نحوه پرداخت -->
                                <div class="mb-3">
                                    <label class="form-label">روش پرداخت</label>
                                    <div id="payment-methods">
                                        <div class="mb-2">
                                            <select class="form-select mb-2 payment-method">
                                                <option value="cash">نقدی</option>
                                                <option value="card">کارت بانکی</option>
                                                <option value="cheque">چک</option>
                                                <option value="credit">اعتباری</option>
                                            </select>
                                            <div class="input-group">
                                                <input type="number" class="form-control payment-amount" 
                                                       placeholder="مبلغ">
                                                <button type="button" class="btn btn-outline-danger remove-payment">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="add-payment" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-plus me-1"></i>
                                        افزودن روش پرداخت
                                    </button>
                                </div>

                                <!-- دکمه‌های عملیات -->
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary quick-action-btn" id="save-invoice">
                                        <i class="fas fa-save me-1"></i>
                                        ثبت و چاپ فاکتور
                                    </button>
                                    <button type="button" class="btn btn-info quick-action-btn" id="save-draft">
                                        <i class="fas fa-folder me-1"></i>
                                        ذخیره پیش‌نویس
                                    </button>
                                    <button type="button" class="btn btn-warning quick-action-btn" id="suspend-invoice">
                                        <i class="fas fa-pause me-1"></i>
                                        تعلیق فاکتور
                                    </button>
                                    <button type="button" class="btn btn-danger quick-action-btn" id="clear-cart">
                                        <i class="fas fa-trash me-1"></i>
                                        حذف همه
                                    </button>
                                    </div>
                            </div>
                        </div>

                        <!-- فاکتورهای معلق -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">فاکتورهای معلق</h5>
                            </div>
                            <div class="card-body">
                                <div id="suspended-invoices">
                                    <!-- لیست فاکتورهای معلق اینجا نمایش داده می‌شود -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال افزودن مشتری جدید -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن مشتری جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="new-customer-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نام</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نام خانوادگی</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">موبایل</label>
                                <input type="tel" name="mobile" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">تلفن</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">آدرس</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" id="save-customer" class="btn btn-primary">ذخیره</button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تنظیمات چاپ -->
    <div class="modal fade" id="printSettingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تنظیمات چاپ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="print-logo" class="form-check-input" checked>
                            <label class="form-check-label" for="print-logo">چاپ لوگو</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="print-customer-info" class="form-check-input" checked>
                            <label class="form-check-label" for="print-customer-info">چاپ اطلاعات مشتری</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع چاپگر</label>
                        <select id="printer-type" class="form-select">
                            <option value="thermal">چاپگر حرارتی</option>
                            <option value="a4">چاپگر A4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد نسخه</label>
                        <input type="number" id="print-copies" class="form-control" value="1" min="1" max="5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" id="start-print" class="btn btn-primary">شروع چاپ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/quick-sale.js"></script>
</body>
</html>