$(document).ready(function() {
    // متغیرهای عمومی
    let descriptions = [];
    
    // دریافت شرح‌های پرتکرار از سرور
    loadDescriptions();
    
    // راه‌اندازی رویدادها
    $('#btnAddDescription').on('click', showDescriptionModal);
    $('#btnSaveDescription').on('click', saveDescription);
    $('#btnCopyDesc').on('click', showDescriptionList);
    
    // دریافت لیست شرح‌های پرتکرار
    function loadDescriptions() {
        $.ajax({
            url: BASE_PATH + '/api/descriptions.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    descriptions = response.data;
                    updateDescriptionList();
                }
            },
            error: function() {
                showError('خطا در دریافت لیست شرح‌ها');
            }
        });
    }
    
    // نمایش مودال افزودن شرح جدید
    function showDescriptionModal() {
        $('#descriptionModal').modal('show');
    }
    
    // نمایش لیست شرح‌ها
    function showDescriptionList() {
        Swal.fire({
            title: 'انتخاب شرح',
            html: generateDescriptionListHtml(),
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonText: 'بستن',
            width: '600px',
            didOpen: () => {
                setupDescriptionListEvents();
            }
        });
    }
    
    // تولید HTML لیست شرح‌ها
    function generateDescriptionListHtml() {
        if (descriptions.length === 0) {
            return '<p class="text-muted">هیچ شرح پرتکراری یافت نشد</p>';
        }
        
        return `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>شرح</th>
                            <th width="100">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${descriptions.map(desc => `
                            <tr>
                                <td>${desc.text}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-desc" 
                                            data-text="${desc.text}">
                                        انتخاب
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    // راه‌اندازی رویدادهای لیست شرح‌ها
    function setupDescriptionListEvents() {
        $('.select-desc').on('click', function() {
            const text = $(this).data('text');
            $('input[name="description"]').val(text);
            Swal.close();
        });
    }
    
    // ذخیره شرح جدید
    function saveDescription() {
        const text = $('#descriptionText').val().trim();
        
        if (!text) {
            showError('لطفا متن شرح را وارد کنید');
            return;
        }
        
        // غیرفعال کردن دکمه
        $('#btnSaveDescription').prop('disabled', true);
        
        $.ajax({
            url: BASE_PATH + '/api/descriptions.php',
            method: 'POST',
            data: { text },
            success: function(response) {
                if (response.success) {
                    descriptions.unshift(response.data);
                    $('#descriptionModal').modal('hide');
                    $('#descriptionText').val('');
                    updateDescriptionList();
                    showSuccess('شرح با موفقیت ذخیره شد');
                } else {
                    showError(response.message || 'خطا در ذخیره شرح');
                }
            },
            error: function() {
                showError('خطا در ارتباط با سرور');
            },
            complete: function() {
                $('#btnSaveDescription').prop('disabled', false);
            }
        });
    }
    
    // بروزرسانی datalist شرح‌ها
    function updateDescriptionList() {
        const datalist = $('#commonDescriptions');
        datalist.empty();
        
        descriptions.forEach(desc => {
            datalist.append(`<option value="${desc.text}">`);
        });
    }
    
    // نمایش پیام خطا
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: message,
            confirmButtonText: 'تایید'
        });
    }
    
    // نمایش پیام موفقیت
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: message,
            confirmButtonText: 'تایید'
        });
    }
});