// تنظیمات پایه نوتیفیکیشن‌ها
const ToastConfig = {
    // نوتیفیکیشن ساده پایین صفحه
    simpleToast: {
        toast: true,
        position: 'bottom-center',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            popup: 'custom-toast',
            title: 'custom-toast-title'
        }
    },
    
    // نوتیفیکیشن هشدار
    warningToast: {
        toast: true,
        position: 'bottom-center',
        showConfirmButton: false,
        timer: 3000,
        icon: 'warning',
        timerProgressBar: true,
        customClass: {
            popup: 'custom-toast',
            title: 'custom-toast-title'
        }
    },
    
    // نوتیفیکیشن خطا
    errorToast: {
        toast: true,
        position: 'bottom-center',
        showConfirmButton: false,
        timer: 3000,
        icon: 'error',
        timerProgressBar: true,
        customClass: {
            popup: 'custom-toast custom-toast-error',
            title: 'custom-toast-title'
        }
    }
};

// توابع نمایش نوتیفیکیشن
const Notifications = {
    // نمایش نوتیفیکیشن ساده موفقیت
    showSuccess: function(message) {
        const Toast = Swal.mixin(ToastConfig.simpleToast);
        Toast.fire({
            icon: 'success',
            title: message
        });
    },
    
    // نمایش نوتیفیکیشن هشدار
    showWarning: function(message) {
        const Toast = Swal.mixin(ToastConfig.warningToast);
        Toast.fire({
            title: message
        });
    },
    
    // نمایش نوتیفیکیشن خطا
    showError: function(message) {
        const Toast = Swal.mixin(ToastConfig.errorToast);
        Toast.fire({
            title: message
        });
    }
};
// نمودار فروش
let salesChartOptions = {
    chart: {
        type: 'line',
        height: 350,
        dir: 'rtl',
        fontFamily: 'IRANSans'
    },
    series: [{
        name: 'فروش',
        data: []
    }],
    xaxis: {
        categories: [],
        labels: {
            style: {
                fontFamily: 'IRANSans'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                fontFamily: 'IRANSans'
            },
            formatter: function(value) {
                return new Intl.NumberFormat('fa-IR').format(value);
            }
        }
    },
    tooltip: {
        y: {
            formatter: function(value) {
                return new Intl.NumberFormat('fa-IR').format(value) + ' ریال';
            }
        }
    }
};