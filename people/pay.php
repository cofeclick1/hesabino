<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->hasPermission('payments_add') && !$_SESSION['is_super_admin']) {  // تغییر از payment_add به payments_add
    $_SESSION['error'] = 'شما دسترسی لازم برای این عملیات را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// دریافت لیست پروژه‌ها
$projects = $db->query("
    SELECT id, name, code, logo_path 
    FROM projects 
    WHERE status = 'active' 
    AND deleted_at IS NULL
    ORDER BY name
")->fetchAll();

// دریافت لیست واحدهای پول
$currencies = $db->query("
    SELECT code, symbol, name 
    FROM currencies 
    WHERE is_active = 1 
    ORDER BY is_default DESC, name
")->fetchAll();

// دریافت شماره سند بعدی
$nextDocNumber = $db->query("
    SELECT COALESCE(MAX(CAST(SUBSTRING(document_number, 5) AS SIGNED)) + 1, 1) as next_number 
    FROM payments 
    WHERE document_number REGEXP '^PAY-[0-9]+$'
")->fetch()['next_number'];

$nextDocNumber = 'PAY-' . str_pad($nextDocNumber, 5, '0', STR_PAD_LEFT);

// دریافت شرح‌های پرتکرار
$descriptions = $db->query("
    SELECT text 
    FROM recurring_descriptions 
    WHERE type = 'payment' 
    AND deleted_at IS NULL 
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'پرداخت جدید';
require_once '../includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content p-4 flex-grow-1">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-light me-2" onclick="history.back()">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <h4 class="mb-0"><?php echo $pageTitle ?></h4>
                </div>
                <div class="d-flex">
                    <button type="button" id="btnCalculate" class="btn btn-outline-primary me-2" title="راس‌گیری">
                        <i class="fas fa-calculator"></i>
                        <span class="d-none d-md-inline">راس‌گیری</span>
                    </button>
                    <button type="button" id="btnNew" class="btn btn-outline-success me-2" title="پرداخت جدید">
                        <i class="fas fa-plus"></i>
                        <span class="d-none d-md-inline">جدید</span>
                    </button>
                    <button type="submit" form="paymentForm" class="btn btn-primary" title="ذخیره">
                        <i class="fas fa-save"></i>
                        <span class="d-none d-md-inline">ذخیره</span>
                    </button>
                </div>
            </div>

            <!-- Main Form -->
            <form id="paymentForm" class="needs-validation" novalidate>
                <div class="row">
                    <!-- اطلاعات اصلی -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <!-- شماره سند -->
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">شماره سند</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="document_number" 
                                                   value="<?php echo $nextDocNumber; ?>" required>
                                            <div class="input-group-text">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="autoNumber" checked>
                                                    <label class="form-check-label" for="autoNumber">خودکار</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- تاریخ -->
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">تاریخ</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control date-picker" name="date" required>
                                            <button type="button" class="btn btn-outline-secondary" id="btnToday">امروز</button>
                                        </div>
                                    </div>

                                    <!-- انتخاب پروژه -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">پروژه</label>
                                        <div class="input-group">
                                            <select name="project_id" class="form-select select2" required>
                                                <option value="">انتخاب پروژه...</option>
                                                <?php foreach ($projects as $project): ?>
                                                <option value="<?php echo $project['id']; ?>" 
                                                        data-logo="<?php echo $project['logo_path']; ?>">
                                                    <?php echo $project['name']; ?>
                                                    <?php if (!empty($project['code'])): ?>
                                                        (<?php echo $project['code']; ?>)
                                                    <?php endif; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary" id="btnNewProject">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- شرح -->
                                    <div class="col-md-9 mb-3">
                                        <label class="form-label">شرح</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="description" 
                                                   list="commonDescriptions">
                                            <button type="button" class="btn btn-outline-secondary" id="btnCopyDesc">
                                                <i class="fas fa-list"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="btnAddDescription">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <datalist id="commonDescriptions">
                                                <?php foreach ($descriptions as $desc): ?>
                                                <option value="<?php echo htmlspecialchars($desc); ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>

                                    <!-- واحد پول -->
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label required">واحد پول</label>
                                        <select class="form-select" name="currency_code" required>
                                            <?php foreach ($currencies as $currency): ?>
                                            <option value="<?php echo $currency['code']; ?>" 
                                                    data-symbol="<?php echo $currency['symbol']; ?>">
                                                <?php echo $currency['name']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- آیتم‌های پرداخت -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div id="paymentItems">
                                    <!-- آیتم‌ها اینجا اضافه می‌شوند -->
                                </div>
                                <button type="button" id="btnAddItem" class="btn btn-outline-primary mt-3">
                                    <i class="fas fa-plus me-1"></i>
                                    افزودن آیتم
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- پنل جمع و پرداخت -->
                    <div class="col-lg-4">
                        <div class="card mb-4 sticky-top" style="top: 1rem;">
                            <div class="card-body">
                                <h6 class="card-title mb-4">اطلاعات پرداخت</h6>
                                
                                <!-- جمع مبالغ -->
                                <div class="d-flex justify-content-between mb-3">
                                    <span>جمع کل:</span>
                                    <span id="totalAmount" class="fw-bold">0</span>
                                </div>
                                
                                <!-- باقیمانده -->
                                <div class="d-flex justify-content-between mb-4">
                                    <span>باقیمانده:</span>
                                    <span id="remainingAmount" class="text-danger fw-bold">0</span>
                                </div>
                                
                                <button type="button" id="btnAddPayment" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-1"></i>
                                    افزودن پرداخت
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Payment Items Template -->
            <template id="paymentItemTemplate">
                <div class="payment-item border rounded p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-wrapper rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px;">
                                <img src="" alt="" class="person-avatar" style="max-width: 32px; max-height: 32px;">
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">شخص</label>
                                    <div class="input-group">
                                        <select class="form-select person-search" required data-placeholder="جستجو و انتخاب شخص...">
                                            <option value="">انتخاب کنید</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-add-person">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required">مبلغ</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control amount-input text-start" required>
                                        <span class="input-group-text currency-symbol">ریال</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">شرح</label>
                                    <input type="text" class="form-control item-description">
                                </div>
                                <div class="col-md-1 mb-3 text-end">
                                    <label class="d-block">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Payment Modal -->
            <div class="modal fade" id="paymentModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">افزودن پرداخت</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="paymentDetailForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label required">روش پرداخت</label>
                                    <select class="form-select" name="paymentMethod" required>
                                        <option value="">انتخاب روش پرداخت...</option>
                                        <option value="cash">نقدی</option>
                                        <option value="card">کارت بانکی</option>
                                        <option value="cheque">چک</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">مبلغ</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control amount-input text-start" name="amount" required>
                                        <span class="input-group-text currency-symbol">ریال</span>
                                    </div>
                                </div>

                                <div id="cardDetails" class="payment-details d-none">
                                    <div class="mb-3">
                                        <label class="form-label required">شماره کارت</label>
                                        <input type="text" class="form-control" name="cardNumber" 
                                               pattern="[0-9]{16}" maxlength="16">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">شماره پیگیری</label>
                                        <input type="text" class="form-control" name="trackingNumber">
                                    </div>
                                </div>

                                <div id="chequeDetails" class="payment-details d-none">
                                    <div class="mb-3">
                                        <label class="form-label required">شماره چک</label>
                                        <input type="text" class="form-control" name="chequeNumber">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required">تاریخ سررسید</label>
                                        <input type="text" class="form-control date-picker" name="dueDate">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">بانک</label>
                                        <select class="form-select" name="bankName">
                                            <option value="">انتخاب بانک...</option>
                                            <option value="mellat">بانک ملت</option>
                                            <option value="melli">بانک ملی</option>
                                            <option value="saderat">بانک صادرات</option>
                                            <option value="tejarat">بانک تجارت</option>
                                            <option value="pasargad">بانک پاسارگاد</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">توضیحات</label>
                                    <textarea class="form-control" name="description" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                            <button type="button" class="btn btn-primary" id="savePayment">ثبت پرداخت</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Person Modal -->
            <div class="modal fade" id="personModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">افزودن شخص جدید</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="personForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label required">نام</label>
                                    <input type="text" class="form-control" name="firstName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">نام خانوادگی</label>
                                    <input type="text" class="form-control" name="lastName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">موبایل</label>
                                    <input type="text" class="form-control ltr text-start" name="mobile" 
                                           pattern="[0-9]{11}" maxlength="11">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">کد ملی</label>
                                    <input type="text" class="form-control ltr text-start" name="nationalCode" 
                                           pattern="[0-9]{10}" maxlength="10">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">توضیحات</label>
                                    <textarea class="form-control" name="description" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                            <button type="button" class="btn btn-primary" id="savePerson">ذخیره</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Modal -->
            <div class="modal fade" id="projectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">افزودن پروژه جدید</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="projectForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label required">نام پروژه</label>
                                    <input type="text" class="form-control" name="projectName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">کد پروژه</label>
                                    <input type="text" class="form-control" name="projectCode">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">توضیحات</label>
                                    <textarea class="form-control" name="projectDescription" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">لوگو</label>
                                    <input type="file" class="form-control" name="projectLogo" accept="image/*">
                                    <div class="form-text">فرمت‌های مجاز: JPG، PNG - حداکثر سایز: 2MB</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="projectActive" checked>
                                        <label class="form-check-label">پروژه فعال است</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="projectDefault">
                                        <label class="form-check-label">پروژه پیش‌فرض</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                            <button type="button" class="btn btn-primary" id="saveProject">ذخیره</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Modal -->
            <div class="modal fade" id="descriptionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">افزودن شرح جدید</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="descriptionForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label required">متن شرح</label>
                                    <input type="text" class="form-control" id="descriptionText" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                            <button type="button" class="btn btn-primary" id="saveDescription">ذخیره</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// اضافه کردن فایل‌های CSS و JS مورد نیاز
$customCss = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
    'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
    'https://unpkg.com/persian-datepicker@latest/dist/css/persian-datepicker.min.css', 
    BASE_PATH . '/assets/css/payments.css'
];

$customJs = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fa.js',
    'https://unpkg.com/persian-date@latest/dist/persian-date.min.js',
    'https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js',
    'https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js',
    BASE_PATH . '/assets/js/payments.js',
    BASE_PATH . '/assets/js/recurring-descriptions.js'
];

require_once '../includes/footer.php';
?>