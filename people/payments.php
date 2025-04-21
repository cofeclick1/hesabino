<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->check()) {
    redirect('login.php');
}

// بررسی دسترسی به صفحه
if (!$auth->hasPermission('payments_add')) {
    die('شما دسترسی لازم برای مشاهده این صفحه را ندارید');
}

$pageTitle = 'ثبت پرداخت';
require_once '../templates/header.php';

// دریافت لیست پروژه‌ها
$projects = $db->query("
    SELECT id, name, code, logo_path 
    FROM projects 
    WHERE deleted_at IS NULL 
    AND (status = 'active' OR is_default = 1)
    ORDER BY is_default DESC, name ASC
")->fetchAll();

// دریافت لیست واحدهای پول
$currencies = $db->query("
    SELECT id, name, code, symbol 
    FROM currencies 
    WHERE is_active = 1 
    ORDER BY is_default DESC, name ASC
")->fetchAll();

// دریافت توضیحات پرتکرار
$descriptions = $db->query("
    SELECT text 
    FROM recurring_descriptions 
    WHERE type = 'payment' 
    AND deleted_at IS NULL 
    ORDER BY use_count DESC, last_used_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_COLUMN);

// دریافت شماره سند خودکار
$lastNumber = $db->query("
    SELECT MAX(CAST(document_number AS UNSIGNED)) as last_number 
    FROM payments 
    WHERE document_number REGEXP '^[0-9]+$'
")->fetch()['last_number'] ?? 0;

$nextNumber = $lastNumber + 1;
?>

<!-- شروع محتوای صفحه -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">ثبت پرداخت جدید</h5>
        <div>
            <button type="button" class="btn btn-success" id="btnSave">
                <i class="fas fa-save"></i>
                ذخیره
            </button>
            <button type="button" class="btn btn-warning" id="btnCalculate">
                <i class="fas fa-calculator"></i>
                راس‌گیری
            </button>
            <button type="button" class="btn btn-secondary" id="btnNew">
                <i class="fas fa-plus"></i>
                جدید
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="paymentForm" class="needs-validation" novalidate>
            <!-- ردیف اول -->
            <div class="row g-3">
                <!-- شماره سند -->
                <div class="col-md-3">
                    <label class="form-label">شماره سند</label>
                    <div class="input-group">
                        <input type="text" name="document_number" class="form-control ltr" 
                            value="<?php echo $nextNumber; ?>" required>
                        <div class="input-group-text">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autoNumber" checked>
                                <label class="form-check-label">خودکار</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تاریخ -->
                <div class="col-md-3">
                    <label class="form-label">تاریخ</label>
                    <div class="input-group">
                        <input type="text" name="date" class="form-control date-picker ltr" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnToday">
                            امروز
                        </button>
                    </div>
                </div>

                <!-- پروژه -->
                <div class="col-md-4">
                    <label class="form-label">پروژه</label>
                    <div class="input-group">
                        <select name="project_id" class="form-select select2" required>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" 
                                    data-logo="<?php echo $project['logo_path']; ?>">
                                <?php echo $project['name']; ?>
                                <?php if ($project['code']): ?>
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

                <!-- واحد پول -->
                <div class="col-md-2">
                    <label class="form-label">واحد پول</label>
                    <select name="currency_code" class="form-select select2" required>
                        <?php foreach ($currencies as $currency): ?>
                        <option value="<?php echo $currency['code']; ?>" 
                                data-symbol="<?php echo $currency['symbol']; ?>">
                            <?php echo $currency['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- توضیحات -->
            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label">توضیحات</label>
                    <div class="input-group">
                        <input type="text" name="description" class="form-control" list="commonDescriptions">
                        <button type="button" class="btn btn-outline-secondary" id="btnAddDescription">
                            <i class="fas fa-plus"></i>
                        </button>
                        <datalist id="commonDescriptions">
                            <?php foreach ($descriptions as $text): ?>
                            <option value="<?php echo htmlspecialchars($text); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- جدول آیتم‌های پرداخت -->
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>شخص</th>
                            <th style="width: 250px;">مبلغ</th>
                            <th>توضیحات</th>
                            <th style="width: 40px;">
                                <button type="button" class="btn btn-sm btn-success" id="btnAddItem">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="paymentItems">
                        <!-- آیتم‌ها اینجا اضافه می‌شوند -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">جمع کل:</th>
                            <th class="text-start" colspan="3">
                                <span id="totalAmount">0</span>
                                <span class="currency-symbol">ریال</span>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2">مانده قابل پرداخت:</th>
                            <th class="text-start" colspan="3">
                                <span id="remainingAmount" class="text-danger">0</span>
                                <span class="currency-symbol">ریال</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- دکمه‌های پرداخت -->
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" id="btnAddPayment">
                        <i class="fas fa-money-bill"></i>
                        افزودن پرداخت
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- قالب آیتم پرداخت -->
<template id="paymentItemTemplate">
    <tr class="payment-item">
        <td class="text-center align-middle item-number">1</td>
        <td>
            <select class="form-select select2 person-search" required>
                <option value="">انتخاب کنید</option>
            </select>
        </td>
        <td>
            <div class="input-group">
                <input type="text" class="form-control ltr amount-input text-start" required>
                <span class="input-group-text currency-symbol">ریال</span>
            </div>
        </td>
        <td>
            <input type="text" class="form-control item-description">
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-danger delete-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<!-- مودال افزودن پرداخت -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن پرداخت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentDetailForm" class="needs-validation" novalidate>
                    <!-- روش پرداخت -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">روش پرداخت</label>
                            <select name="paymentMethod" class="form-select" required>
                                <option value="">انتخاب کنید</option>
                                <option value="cash">نقدی</option>
                                <option value="card">کارت خوان</option>
                                <option value="cheque">چک</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">مبلغ</label>
                            <div class="input-group">
                                <input type="text" name="amount" class="form-control ltr amount-input text-start" required>
                                <span class="input-group-text currency-symbol">ریال</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تاریخ پرداخت</label>
                            <div class="input-group">
                                <input type="text" name="paymentDate" class="form-control date-picker ltr" required>
                                <button type="button" class="btn btn-outline-secondary btnToday">امروز</button>
                            </div>
                        </div>
                    </div>

                    <!-- جزئیات کارت -->
                    <div id="cardDetails" class="payment-details d-none">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">شماره کارت</label>
                                <input type="text" name="cardNumber" class="form-control ltr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">شماره پیگیری</label>
                                <input type="text" name="trackingNumber" class="form-control ltr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">نام بانک</label>
                                <select name="bankName" class="form-select">
                                    <option value="">انتخاب کنید</option>
                                    <option value="melli">بانک ملی</option>
                                    <option value="mellat">بانک ملت</option>
                                    <option value="saderat">بانک صادرات</option>
                                    <option value="tejarat">بانک تجارت</option>
                                    <option value="sepah">بانک سپه</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- جزئیات چک -->
                    <div id="chequeDetails" class="payment-details d-none">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">شماره چک</label>
                                <input type="text" name="chequeNumber" class="form-control ltr">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">تاریخ سررسید</label>
                                <input type="text" name="dueDate" class="form-control date-picker ltr">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نام بانک</label>
                                <input type="text" name="chequeBankName" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">شعبه</label>
                                <input type="text" name="branchName" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">شماره حساب</label>
                                <input type="text" name="accountNumber" class="form-control ltr">
                            </div>
                        </div>
                    </div>

                    <!-- توضیحات پرداخت -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">توضیحات</label>
                            <textarea name="paymentDescription" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" form="paymentDetailForm" class="btn btn-primary">ثبت پرداخت</button>
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن پروژه -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن پروژه جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="projectForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">نام پروژه</label>
                            <input type="text" name="projectName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">کد پروژه</label>
                            <input type="text" name="projectCode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">لوگو</label>
                            <input type="file" name="projectLogo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">توضیحات</label>
                            <textarea name="projectDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="projectActive" class="form-check-input" id="projectActive" checked>
                                <label class="form-check-label" for="projectActive">پروژه فعال است</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success" id="saveProject">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن توضیحات -->
<div class="modal fade" id="descriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن توضیحات پرتکرار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="descriptionForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">متن توضیحات</label>
                        <textarea id="descriptionText" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success" id="saveDescription">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<?php
// افزودن اسکریپت‌ها
$scripts = [
    'assets/js/cleave.min.js',
    'assets/js/payments.js'
];
require_once '../templates/footer.php';
?>