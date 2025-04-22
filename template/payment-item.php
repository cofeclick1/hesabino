<template id="paymentItemTemplate">
    <div class="payment-item border rounded p-3 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="avatar-wrapper rounded-circle bg-light d-flex align-items-center justify-content-center" 
                     style="width: 48px; height: 48px;">
                    <img src="<?php echo BASE_PATH ?>/assets/images/avatar.png" alt="" class="person-avatar" 
                         style="max-width: 32px; max-height: 32px;">
                </div>
            </div>
            <div class="col">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">شخص</label>
                        <div class="input-group">
                            <div class="search-wrapper position-relative flex-grow-1">
                                <input type="text" class="form-control person-search-input" 
                                       placeholder="نام، موبایل یا کد ملی را وارد کنید...">
                                <div class="search-results position-absolute w-100 bg-white border rounded-bottom shadow-sm" 
                                     style="display: none; z-index: 1050;"></div>
                                <input type="hidden" name="person_id[]" class="person-id" required>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-add-person">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label required">مبلغ</label>
                        <div class="input-group">
                            <input type="text" class="form-control amount-input text-start" 
                                   required autocomplete="off">
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