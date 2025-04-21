<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!isUserHaveAccess('payment.add')) {
    redirectTo('dashboard.php', 'شما دسترسی لازم برای این عملیات را ندارید');
}

// دریافت لیست پروژه‌ها
$projects = $db->query("
    SELECT id, name, logo_path 
    FROM projects 
    WHERE status = 'active' 
    ORDER BY name
")->fetchAll();

// دریافت لیست واحدهای پول
$currencies = $db->query("
    SELECT code, symbol, name 
    FROM currencies 
    WHERE is_active = 1 
    ORDER BY is_default DESC, name
")->fetchAll();

$pageTitle = 'پرداخت جدید';
require_once '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-right"></i>
            </a>
            <h4 class="mb-0"><?php echo $pageTitle ?></h4>
        </div>
        <div class="d-flex">
            <button type="button" id="btnCalculate" class="btn btn-outline-primary me-2">
                <i class="fas fa-chart-line me-1"></i>
                راس‌گیری
            </button>
            <button type="button" id="btnNew" class="btn btn-outline-success me-2">
                <i class="fas fa-plus me-1"></i>
                جدید
            </button>
            <button type="button" id="btnSave" class="btn btn-primary" form="paymentForm">
                <i class="fas fa-save me-1"></i>
                ذخیره
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
                                <label for="documentNumber" class="form-label">شماره سند</label>
                                <input type="text" class="form-control" id="documentNumber" name="documentNumber" readonly>
                            </div>

                            <!-- تاریخ -->
                            <div class="col-md-3 mb-3">
                                <label for="date" class="form-label">تاریخ</label>
                                <input type="text" class="form-control date-picker" id="date" name="date" required>
                                <div class="invalid-feedback">لطفا تاریخ را وارد کنید</div>
                            </div>

                            <!-- انتخاب پروژه -->
                            <div class="col-md-6 mb-3">
                                <label for="projectId" class="form-label">پروژه</label>
                                <select class="form-select" id="projectId" name="projectId">
                                    <option value="">انتخاب پروژه...</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" 
                                            data-logo="<?php echo $project['logo_path']; ?>">
                                        <?php echo $project['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- شرح -->
                            <div class="col-md-9 mb-3">
                                <label for="description" class="form-label">شرح</label>
                                <input type="text" class="form-control" id="description" name="description" 
                                       list="commonDescriptions">
                                <datalist id="commonDescriptions">
                                    <option value="پرداخت بابت خرید">
                                    <option value="پرداخت هزینه">
                                    <option value="پرداخت حقوق">
                                </datalist>
                            </div>

                            <!-- واحد پول -->
                            <div class="col-md-3 mb-3">
                                <label for="currencyCode" class="form-label">واحد پول</label>
                                <select class="form-select" id="currencyCode" name="currencyCode" required>
                                    <?php foreach ($currencies as $currency): ?>
                                    <option value="<?php echo $currency['code']; ?>" 
                                            data-symbol="<?php echo $currency['symbol']; ?>">
                                        <?php echo $currency['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">لطفا واحد پول را انتخاب کنید</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- آیتم‌های پرداخت -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div id="paymentItems">
                            <!-- Template for payment items - will be populated by JS -->
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
                            <span id="totalAmount">0</span>
                        </div>
                        
                        <!-- باقیمانده -->
                        <div class="d-flex justify-content-between mb-4">
                            <span>باقیمانده:</span>
                            <span id="remainingAmount" class="text-danger">0</span>
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
</div>

<!-- Template for Payment Items -->
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
                        <label class="form-label">شخص</label>
                        <div class="contact-selector">
                            <input type="text" class="form-control person-search" required>
                            <div class="invalid-feedback">لطفا شخص را انتخاب کنید</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">مبلغ</label>
                        <div class="input-group">
                            <input type="text" class="form-control amount-input" required>
                            <span class="input-group-text currency-symbol">ریال</span>
                            <div class="invalid-feedback">لطفا مبلغ را وارد کنید</div>
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

<!-- Modal for Payment -->
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
                        <label class="form-label">روش پرداخت</label>
                        <select class="form-select" name="paymentMethod" required>
                            <option value="">انتخاب روش پرداخت...</option>
                            <option value="cash">نقدی</option>
                            <option value="card">کارت بانکی</option>
                            <option value="cheque">چک</option>
                        </select>
                        <div class="invalid-feedback">لطفا روش پرداخت را انتخاب کنید</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">مبلغ</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="amount" required>
                            <span class="input-group-text currency-symbol">ریال</span>
                            <div class="invalid-feedback">لطفا مبلغ را وارد کنید</div>
                        </div>
                    </div>

                    <div id="cardDetails" class="payment-details d-none">
                        <div class="mb-3">
                            <label class="form-label">شماره کارت</label>
                            <input type="text" class="form-control" name="cardNumber">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شماره پیگیری</label>
                            <input type="text" class="form-control" name="trackingNumber">
                        </div>
                    </div>

                    <div id="chequeDetails" class="payment-details d-none">
                        <div class="mb-3">
                            <label class="form-label">شماره چک</label>
                            <input type="text" class="form-control" name="chequeNumber">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاریخ سررسید</label>
                            <input type="text" class="form-control date-picker" name="dueDate">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">بانک</label>
                            <input type="text" class="form-control" name="bankName">
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

<?php require_once '../includes/footer.php'; ?>