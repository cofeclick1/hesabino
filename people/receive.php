<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->hasPermission('receipts_add')) {
    $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید';
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

// دریافت اطلاعات پایه
$projects = $db->query("SELECT id, name FROM projects WHERE deleted_at IS NULL ORDER BY name")->fetchAll();
$banks = $db->query("SELECT id, name FROM banks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();
$currencies = $db->query("SELECT id, name, symbol FROM currencies WHERE is_active = 1 ORDER BY is_default DESC, name")->fetchAll();

// دریافت آخرین شماره دریافت
$lastNumber = $db->query("
    SELECT receipt_number 
    FROM receipts 
    WHERE deleted_at IS NULL 
    ORDER BY receipt_number DESC 
    LIMIT 1
")->fetch();

$nextNumber = $lastNumber ? $lastNumber['receipt_number'] + 1 : 1;

// دریافت شرح‌های پرکاربرد
$commonDescriptions = $db->query("
    SELECT DISTINCT description 
    FROM receipts 
    WHERE deleted_at IS NULL 
    AND description IS NOT NULL 
    GROUP BY description 
    HAVING COUNT(*) > 1 
    ORDER BY COUNT(*) DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'دریافت جدید';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    
    <!-- فونت‌ها و استایل‌ها -->
    <link href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/receipt.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <!-- Persian DatePicker -->
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- نامبر فرمت -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/autonumeric@4.10.3/dist/autoNumeric.min.css">
    
    <style>
       
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Navbar -->
            <?php include '../includes/navbar.php'; ?>

            <!-- Receipt Header -->
            <div class="receipt-header sticky-top">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <a href="receive_list.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-right"></i>
                                بازگشت
                            </a>
                        </div>
                        <div class="col">
                            <h1 class="receipt-title mb-0">
                                <i class="fas fa-file-invoice"></i>
                                دریافت جدید
                            </h1>
                        </div>
                        <div class="col-auto">
                            <div class="receipt-actions d-flex">
                                <button type="button" class="btn btn-success" id="saveReceipt">
                                    <i class="fas fa-save"></i>
                                    ذخیره
                                </button>
                                <button type="button" class="btn btn-primary" id="copyReceipt">
                                    <i class="fas fa-copy"></i>
                                    کپی
                                </button>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" id="printReceipt">
                                                <i class="fas fa-print me-2"></i>
                                                چاپ
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="exportPdf">
                                                <i class="fas fa-file-pdf me-2"></i>
                                                خروجی PDF
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" id="deleteReceipt">
                                                <i class="fas fa-trash me-2"></i>
                                                حذف
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="container-fluid px-4 py-4">
                <form id="receiptForm" class="needs-validation" novalidate>
                    <!-- اطلاعات پایه -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label required">شماره</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="receipt_number"
                                                   value="<?php echo $nextNumber; ?>" required>
                                            <button type="button" class="btn btn-outline-secondary" id="generateNumber">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label required">تاریخ</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" name="date" 
                                                   required readonly>
                                            <button type="button" class="btn btn-outline-secondary" id="setToday">
                                                امروز
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">پروژه</label>
                                        <div class="input-group">
                                            <select class="form-select select2" name="project_id">
                                                <option value="">انتخاب کنید</option>
                                                <?php foreach ($projects as $project): ?>
                                                <option value="<?php echo $project['id']; ?>">
                                                    <?php echo htmlspecialchars($project['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary" id="addProject">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">واحد پول</label>
                                        <select class="form-select" name="currency_id">
                                            <?php foreach ($currencies as $currency): ?>
                                            <option value="<?php echo $currency['id']; ?>">
                                                <?php echo htmlspecialchars($currency['name'] . ' (' . $currency['symbol'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">شرح</label>
                                        <input type="text" class="form-control" name="description" 
                                               list="commonDescriptions">
                                        <datalist id="commonDescriptions">
                                            <?php foreach ($commonDescriptions as $desc): ?>
                                            <option value="<?php echo htmlspecialchars($desc); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اشخاص -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">اشخاص</h5>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary btn-sm" id="addPerson">
                                        <i class="fas fa-plus"></i>
                                        افزودن شخص
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="personsList">
                                <!-- Template for person item -->
                                <template id="personTemplate">
                                    <div class="person-item mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label required">شخص</label>
                                                    <div class="input-group">
                                                        <select class="form-select person-select" name="persons[%index%][person_id]" required>
                                                            <option value="">انتخاب کنید</option>
                                                        </select>
                                                        <button type="button" class="btn btn-outline-secondary add-new-person">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label required">مبلغ</label>
                                                    <input type="text" class="form-control amount-input" 
                                                           name="persons[%index%][amount]" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">شرح</label>
                                                    <input type="text" class="form-control" 
                                                           name="persons[%index%][description]">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label class="form-label d-block">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger remove-person">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- تراکنش‌ها -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">تراکنش‌ها</h5>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary btn-sm" id="addTransaction">
                                        <i class="fas fa-plus"></i>
                                        افزودن تراکنش
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="transactionsList">
                                <!-- Template for transaction item -->
                                <template id="transactionTemplate">
                                    <div class="transaction-item">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label required">نوع تراکنش</label>
                                                    <select class="form-select transaction-type" 
                                                            name="transactions[%index%][type]" required>
                                                        <option value="">انتخاب کنید</option>
                                                        <option value="cash">نقدی</option>
                                                        <option value="bank">بانک</option>
                                                        <option value="card">کارت خوان</option>
                                                        <option value="cheque">چک</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label required">مبلغ</label>
                                                    <input type="text" class="form-control amount-input" 
                                                           name="transactions[%index%][amount]" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2 bank-fields d-none">
                                                <div class="form-group">
                                                    <label class="form-label">بانک</label>
                                                    <select class="form-select" name="transactions[%index%][bank_id]">
                                                        <option value="">انتخاب کنید</option>
                                                        <?php foreach ($banks as $bank): ?>
                                                        <option value="<?php echo $bank['id']; ?>">
                                                            <?php echo htmlspecialchars($bank['name']); ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="form-label">شماره مرجع</label>
                                                    <input type="text" class="form-control" 
                                                           name="transactions[%index%][reference]">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label class="form-label">کارمزد</label>
                                                    <input type="text" class="form-control amount-input" 
                                                           name="transactions[%index%][fee]">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label class="form-label d-block">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger remove-transaction">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- فیلدهای چک -->
                                        <div class="cheque-fields d-none mt-3">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label class="form-label">شماره چک</label>
                                                        <input type="text" class="form-control" 
                                                               name="transactions[%index%][cheque_number]">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label class="form-label">تاریخ چک</label>
                                                        <input type="text" class="form-control datepicker" 
                                                               name="transactions[%index%][cheque_date]" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label class="form-label">بانک صادرکننده</label>
                                                        <select class="form-select" name="transactions[%index%][issuing_bank_id]">
                                                            <option value="">انتخاب کنید</option>
                                                            <?php foreach ($banks as $bank): ?>
                                                            <option value="<?php echo $bank['id']; ?>">
                                                                <?php echo htmlspecialchars($bank['name']); ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">شعبه</label>
                                                        <input type="text" class="form-control" 
                                                               name="transactions[%index%][bank_branch]">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">صاحب حساب</label>
                                                        <input type="text" class="form-control" 
                                                               name="transactions[%index%][account_owner]">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- جمع‌بندی -->
                            <div class="totals mt-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>جمع مبالغ:</span>
                                            <span class="total-amount" id="totalAmount">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>باقیمانده:</span>
                                            <span class="remaining-amount" id="remainingAmount">0</span>
                                        </div>
                                    </div>
                                    <div class="col-md-8 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            ذخیره دریافت
                                        </button>
                                        <a href="receive_list.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            انصراف
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal افزودن شخص جدید -->
    <div class="modal fade" id="newPersonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن شخص جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newPersonForm">
                        <div class="mb-3">
                            <label class="form-label required">نام</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">نام خانوادگی</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">موبایل</label>
                            <input type="text" class="form-control" name="mobile" 
                                   pattern="09[0-9]{9}" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveNewPerson">ذخیره</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal افزودن پروژه جدید -->
    <div class="modal fade" id="newProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن پروژه جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newProjectForm">
                        <div class="mb-3">
                            <label class="form-label required">نام پروژه</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveNewProject">ذخیره</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.3/dist/autoNumeric.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            // تنظیمات Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                language: {
                    noResults: function() {
                        return "نتیجه‌ای یافت نشد";
                    }
                }
            });

            // تنظیمات تقویم شمسی
            $('.datepicker').persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: false,
                autoClose: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });

            // تنظیمات ورودی مبلغ
            $('.amount-input').each(function() {
                new AutoNumeric(this, {
                    digitGroupSeparator: ',',
                    decimalPlaces: 0,
                    minimumValue: '0'
                });
            });

            // افزودن شخص
            let personIndex = 0;
            $('#addPerson').click(function() {
                const template = document.getElementById('personTemplate').innerHTML
                    .replace(/%index%/g, personIndex++);
                $('#personsList').append(template);
                initializeNewPersonRow();
            });

            // افزودن تراکنش
            // ادامه کد قبلی...
            let transactionIndex = 0;
            $('#addTransaction').click(function() {
                const template = document.getElementById('transactionTemplate').innerHTML
                    .replace(/%index%/g, transactionIndex++);
                $('#transactionsList').append(template);
                initializeNewTransactionRow();
            });

            // حذف شخص
            $(document).on('click', '.remove-person', function() {
                $(this).closest('.person-item').remove();
                calculateTotals();
            });

            // حذف تراکنش
            $(document).on('click', '.remove-transaction', function() {
                $(this).closest('.transaction-item').remove();
                calculateTotals();
            });

            // نمایش/مخفی کردن فیلدهای اضافی براساس نوع تراکنش
            $(document).on('change', '.transaction-type', function() {
                const transactionItem = $(this).closest('.transaction-item');
                const bankFields = transactionItem.find('.bank-fields');
                const chequeFields = transactionItem.find('.cheque-fields');

                bankFields.addClass('d-none');
                chequeFields.addClass('d-none');

                if (this.value === 'bank' || this.value === 'card') {
                    bankFields.removeClass('d-none');
                } else if (this.value === 'cheque') {
                    bankFields.removeClass('d-none');
                    chequeFields.removeClass('d-none');
                }
            });

            // محاسبه جمع مبالغ
            function calculateTotals() {
                let totalPersons = 0;
                let totalTransactions = 0;

                // جمع مبالغ اشخاص
                $('.person-item .amount-input').each(function() {
                    totalPersons += AutoNumeric.getNumber(this) || 0;
                });

                // جمع مبالغ تراکنش‌ها
                $('.transaction-item .amount-input').each(function() {
                    if (!$(this).hasClass('fee-input')) {
                        totalTransactions += AutoNumeric.getNumber(this) || 0;
                    }
                });

                // نمایش مبالغ
                $('#totalAmount').text(new Intl.NumberFormat('fa-IR').format(totalPersons));
                $('#remainingAmount').text(new Intl.NumberFormat('fa-IR').format(totalPersons - totalTransactions));
            }

            // راه‌اندازی Select2 برای ردیف جدید شخص
            function initializeNewPersonRow() {
                const newRow = $('.person-item').last();
                newRow.find('.person-select').select2({
                    theme: 'bootstrap-5',
                    ajax: {
                        url: BASE_PATH + '/ajax/search_people.php',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'جستجوی شخص...',
                    minimumInputLength: 2,
                    templateResult: formatPerson,
                    templateSelection: formatPerson
                });

                // تنظیمات ورودی مبلغ برای ردیف جدید
                newRow.find('.amount-input').each(function() {
                    new AutoNumeric(this, {
                        digitGroupSeparator: ',',
                        decimalPlaces: 0,
                        minimumValue: '0'
                    });
                });
            }

            // راه‌اندازی اولیه برای تراکنش جدید
            function initializeNewTransactionRow() {
                const newRow = $('.transaction-item').last();
                
                // تنظیمات تقویم شمسی
                newRow.find('.datepicker').persianDatepicker({
                    format: 'YYYY/MM/DD',
                    initialValue: false,
                    autoClose: true,
                    calendar: {
                        persian: {
                            locale: 'fa'
                        }
                    }
                });

                // تنظیمات ورودی مبلغ
                newRow.find('.amount-input').each(function() {
                    new AutoNumeric(this, {
                        digitGroupSeparator: ',',
                        decimalPlaces: 0,
                        minimumValue: '0'
                    });
                });
            }

            // فرمت نمایش نتایج جستجوی شخص
            function formatPerson(person) {
                if (person.loading) return person.text;
                if (!person.id) return person.text;
                
                return $(`
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="${person.avatar}" class="avatar-sm" alt="${person.text}">
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="font-weight-bold">${person.text}</div>
                            ${person.mobile ? `<small class="text-muted">${person.mobile}</small>` : ''}
                        </div>
                    </div>
                `);
            }

            // تنظیم تاریخ امروز
            $('#setToday').click(function() {
                const today = new persianDate();
                $(this).closest('.form-group').find('.datepicker').val(today.format('YYYY/MM/DD'));
            });

            // ذخیره شخص جدید
            $('#saveNewPerson').click(function() {
                const form = $('#newPersonForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                $.ajax({
                    url: BASE_PATH + '/ajax/save_person.php',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            const newOption = new Option(response.person.name, response.person.id, true, true);
                            $('.person-select').last().append(newOption).trigger('change');
                            $('#newPersonModal').modal('hide');
                            form[0].reset();
                            
                            Swal.fire({
                                title: 'موفق',
                                text: 'شخص جدید با موفقیت ثبت شد',
                                icon: 'success',
                                confirmButtonText: 'تایید'
                            });
                        } else {
                            Swal.fire({
                                title: 'خطا',
                                text: response.message || 'خطا در ثبت شخص جدید',
                                icon: 'error',
                                confirmButtonText: 'تایید'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'خطا',
                            text: 'خطا در برقراری ارتباط با سرور',
                            icon: 'error',
                            confirmButtonText: 'تایید'
                        });
                    }
                });
            });

            // ذخیره پروژه جدید
            $('#saveNewProject').click(function() {
                const form = $('#newProjectForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                $.ajax({
                    url: BASE_PATH + '/ajax/save_project.php',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            const newOption = new Option(response.project.name, response.project.id, true, true);
                            $('select[name="project_id"]').append(newOption).trigger('change');
                            $('#newProjectModal').modal('hide');
                            form[0].reset();
                            
                            Swal.fire({
                                title: 'موفق',
                                text: 'پروژه جدید با موفقیت ثبت شد',
                                icon: 'success',
                                confirmButtonText: 'تایید'
                            });
                        } else {
                            Swal.fire({
                                title: 'خطا',
                                text: response.message || 'خطا در ثبت پروژه جدید',
                                icon: 'error',
                                confirmButtonText: 'تایید'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'خطا',
                            text: 'خطا در برقراری ارتباط با سرور',
                            icon: 'error',
                            confirmButtonText: 'تایید'
                        });
                    }
                });
            });

            // اعتبارسنجی و ذخیره فرم
            $('#receiptForm').submit(function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    this.reportValidity();
                    return;
                }

                const formData = new FormData(this);
                
                $.ajax({
                    url: BASE_PATH + '/ajax/save_receipt.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'موفق',
                                text: 'دریافت با موفقیت ثبت شد',
                                icon: 'success',
                                confirmButtonText: 'تایید'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'receive_list.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'خطا',
                                text: response.message || 'خطا در ثبت دریافت',
                                icon: 'error',
                                confirmButtonText: 'تایید'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'خطا',
                            text: 'خطا در برقراری ارتباط با سرور',
                            icon: 'error',
                            confirmButtonText: 'تایید'
                        });
                    }
                });
            });

            // محاسبه مجدد جمع‌ها با تغییر مبالغ
            $(document).on('change keyup', '.amount-input', calculateTotals);

            // نمایش تایید حذف
            $('#deleteReceipt').click(function() {
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: 'این عملیات قابل بازگشت نیست',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ارسال درخواست حذف
                        $.ajax({
                            url: BASE_PATH + '/ajax/delete_receipt.php',
                            method: 'POST',
                            data: {
                                receipt_id: $('#receiptForm').data('id')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'موفق',
                                        text: 'دریافت با موفقیت حذف شد',
                                        icon: 'success',
                                        confirmButtonText: 'تایید'
                                    }).then(() => {
                                        window.location.href = 'receive_list.php';
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'خطا',
                                        text: response.message || 'خطا در حذف دریافت',
                                        icon: 'error',
                                        confirmButtonText: 'تایید'
                                    });
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>