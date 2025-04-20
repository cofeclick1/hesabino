<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// پردازش AJAX برای آپلود تصویر
if (isset($_POST['imageData'])) {
    try {
        $uploadDir = 'uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'logo_' . uniqid() . '.jpg';
        $uploadFile = $uploadDir . $fileName;
        
        // حذف header داده base64
        $imageData = $_POST['imageData'];
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        
        // تبدیل داده base64 به فایل
        $imageDecoded = base64_decode($imageData);
        if ($imageDecoded === false) {
            throw new Exception('خطا در رمزگشایی تصویر');
        }
        
        if (file_put_contents($uploadFile, $imageDecoded) === false) {
            throw new Exception('خطا در ذخیره تصویر');
        }

        // حذف لوگوی قدیمی
        $sql = "SELECT value FROM settings WHERE `key` = 'organization_logo'";
        $oldLogo = $db->query($sql)->fetchColumn();
        if (!empty($oldLogo) && file_exists($oldLogo) && $oldLogo != 'assets/images/default-logo.png') {
            unlink($oldLogo);
        }
        
        echo json_encode([
            'success' => true,
            'imageUrl' => $uploadFile
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log('خطا در آپلود تصویر: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// ذخیره تنظیمات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // اعتبارسنجی و ذخیره نام سازمان
        if (empty($_POST['organization_name'])) {
            throw new Exception('نام سازمان الزامی است');
        }

        // اعتبارسنجی موبایل
        if (!empty($_POST['organization_mobile']) && !preg_match('/^09[0-9]{9}$/', $_POST['organization_mobile'])) {
            throw new Exception('فرمت شماره موبایل صحیح نیست');
        }

        // اعتبارسنجی ایمیل
        if (!empty($_POST['organization_email']) && !filter_var($_POST['organization_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('فرمت ایمیل صحیح نیست');
        }

        // اعتبارسنجی نرخ مالیات
        $taxRate = floatval($_POST['tax_rate']);
        if ($taxRate < 0 || $taxRate > 100) {
            throw new Exception('نرخ مالیات باید بین 0 تا 100 باشد');
        }

        // تنظیمات برای ذخیره
        $settings = [
            'organization_name' => $_POST['organization_name'],
            'organization_address' => $_POST['organization_address'],
            'organization_phone' => $_POST['organization_phone'],
            'organization_mobile' => $_POST['organization_mobile'],
            'organization_email' => $_POST['organization_email'],
            'tax_rate' => $taxRate,
            'invoice_prefix' => $_POST['invoice_prefix']
        ];

        // اضافه کردن لوگو اگر آپلود شده باشد
        if (!empty($_POST['profile_image'])) {
            $settings['organization_logo'] = $_POST['profile_image'];
        }

        // به‌روزرسانی یا افزودن هر تنظیم
        foreach ($settings as $key => $value) {
            $sql = "SELECT COUNT(*) FROM settings WHERE `key` = ?";
            $count = $db->query($sql, [$key])->fetchColumn();
            
            if ($count > 0) {
                $sql = "UPDATE settings SET `value` = ? WHERE `key` = ?";
                $db->query($sql, [$value, $key]);
            } else {
                $sql = "INSERT INTO settings (`key`, `value`, `type`, `description`) VALUES (?, ?, 'text', ?)";
                $description = '';
                switch ($key) {
                    case 'organization_name':
                        $description = 'نام سازمان';
                        break;
                    case 'organization_address':
                        $description = 'آدرس سازمان';
                        break;
                    case 'organization_phone':
                        $description = 'تلفن ثابت';
                        break;
                    case 'organization_mobile':
                        $description = 'شماره موبایل';
                        break;
                    case 'organization_email':
                        $description = 'ایمیل سازمان';
                        break;
                    case 'organization_logo':
                        $description = 'لوگو سازمان';
                        break;
                    case 'tax_rate':
                        $description = 'نرخ مالیات (درصد)';
                        break;
                    case 'invoice_prefix':
                        $description = 'پیشوند شماره فاکتور';
                        break;
                }
                $db->query($sql, [$key, $value, $description]);
            }
        }

        $db->commit();
        flashMessage('تنظیمات با موفقیت ذخیره شد', 'success');
        header('Location: settings.php');
        exit;

    } catch (Exception $e) {
        $db->rollback();
        flashMessage('خطا در ذخیره تنظیمات: ' . $e->getMessage(), 'danger');
    }
}
// دریافت تنظیمات فعلی
$currentSettings = [];
$sql = "SELECT `key`, `value` FROM settings";
$dbSettings = $db->query($sql)->fetchAll();
foreach ($dbSettings as $setting) {
    $currentSettings[$setting['key']] = $setting['value'];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات سیستم - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <style>

    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content w-100">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid px-4">
                <?php echo showFlashMessage(); ?>
                
                <div class="row g-4 my-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-gradient-primary text-white py-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>
                                    تنظیمات سیستم
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="profile-upload-container mb-4">
                                    <img src="<?php echo !empty($currentSettings['organization_logo']) ? htmlspecialchars($currentSettings['organization_logo']) : 'assets/images/default-logo.png'; ?>" 
                                         alt="لوگو سازمان" 
                                         class="profile-image" 
                                         id="profileImage">
                                    <div class="profile-image-overlay">
                                        <span class="profile-image-text">تغییر لوگو</span>
                                    </div>
                                    <input type="file" 
                                           id="profileImageInput" 
                                           accept="image/*" 
                                           style="display: none;">
                                    <input type="hidden" 
                                           name="profile_image" 
                                           id="profileImageData">
                                </div>

                                <form method="post" action="" id="settings-form">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="organization_name" class="form-label">نام سازمان *</label>
            <input type="text" name="organization_name" id="organization_name" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['organization_name'] ?? ''); ?>" 
                   required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="organization_address" class="form-label">آدرس</label>
            <input type="text" name="organization_address" id="organization_address" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['organization_address'] ?? ''); ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="organization_phone" class="form-label">تلفن ثابت</label>
            <input type="text" name="organization_phone" id="organization_phone" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['organization_phone'] ?? ''); ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="organization_mobile" class="form-label">شماره موبایل</label>
            <input type="text" name="organization_mobile" id="organization_mobile" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['organization_mobile'] ?? ''); ?>"
                   pattern="09[0-9]{9}"
                   placeholder="مثال: 09123456789">
        </div>
        <div class="col-md-4 mb-3">
            <label for="organization_email" class="form-label">ایمیل</label>
            <input type="email" name="organization_email" id="organization_email" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['organization_email'] ?? ''); ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="tax_rate" class="form-label">نرخ مالیات (%)</label>
            <input type="number" name="tax_rate" id="tax_rate" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['tax_rate'] ?? '9'); ?>"
                   min="0" max="100" step="0.1" required>
            <div class="form-text">عددی بین 0 تا 100 وارد کنید</div>
        </div>
        <div class="col-md-4 mb-3">
            <label for="invoice_prefix" class="form-label">پیشوند شماره فاکتور</label>
            <input type="text" name="invoice_prefix" id="invoice_prefix" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($currentSettings['invoice_prefix'] ?? 'INV'); ?>"
                   required>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                ذخیره تنظیمات
            </button>
        </div>
    </div>
</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- مودال برش تصویر -->
    <div class="modal fade" id="cropperModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">برش تصویر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container mb-3">
                        <img id="cropperImage" src="" style="max-width: 100%;">
                    </div>
                    <div class="preview-container d-none">
                        <img id="previewImage" src="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="cropButton">برش و ذخیره</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/dashboard.js"></script>
<script>
$(document).ready(function() {
    let cropper;
    const profileImage = document.getElementById('profileImage');
    const profileImageInput = document.getElementById('profileImageInput');
    const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));
    const cropperImage = document.getElementById('cropperImage');
    const cropButton = document.getElementById('cropButton');

    // کلیک روی تصویر لوگو
    $('.profile-upload-container').click(function() {
        profileImageInput.click();
    });

    // تغییر فایل انتخاب شده
    profileImageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                cropperImage.src = e.target.result;
                cropperModal.show();

                // ایجاد Cropper بعد از نمایش مودال
                setTimeout(() => {
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        cropBoxResizable: false,
                        cropBoxMovable: false,
                        minContainerWidth: 200,
                        minContainerHeight: 200,
                        responsive: true
                    });
                }, 200);
            };
            reader.readAsDataURL(file);
        }
    });

    // دکمه برش و ذخیره
    cropButton.addEventListener('click', function() {
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        const imageData = canvas.toDataURL('image/jpeg', 0.95);
        
        // ارسال تصویر به سرور
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                imageData: imageData
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // به‌روزرسانی تصویر لوگو
                        profileImage.src = result.imageUrl;
                        // ذخیره آدرس تصویر در فیلد مخفی
                        document.getElementById('profileImageData').value = result.imageUrl;
                        // بستن مودال
                        cropperModal.hide();
                        // نمایش پیام موفقیت
                        Swal.fire({
                            icon: 'success',
                            title: 'موفقیت',
                            text: 'لوگو با موفقیت آپلود شد',
                            confirmButtonText: 'تایید'
                        });
                    } else {
                        throw new Error(result.message || 'خطا در آپلود لوگو');
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: e.message,
                        confirmButtonText: 'تایید'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در برقراری ارتباط با سرور',
                    confirmButtonText: 'تایید'
                });
            }
        });
    });

    // پاکسازی cropper در زمان بستن مودال
    $('#cropperModal').on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    // اعتبارسنجی فرم
    $('#settings-form').on('submit', function(e) {
        const mobile = $('#organization_mobile').val().trim();
        const email = $('#organization_email').val().trim();
        const taxRate = parseFloat($('#tax_rate').val());
        const errors = [];

        // بررسی نام سازمان
        if ($('#organization_name').val().trim() === '') {
            errors.push('نام سازمان الزامی است');
        }

        // بررسی فرمت موبایل
        if (mobile && !/^09[0-9]{9}$/.test(mobile)) {
            errors.push('فرمت شماره موبایل صحیح نیست');
        }

        // بررسی فرمت ایمیل
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('فرمت ایمیل صحیح نیست');
        }

        // بررسی نرخ مالیات
        if (isNaN(taxRate) || taxRate < 0 || taxRate > 100) {
            errors.push('نرخ مالیات باید عددی بین 0 تا 100 باشد');
        }

        if (errors.length > 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'خطا در اعتبارسنجی',
                html: errors.join('<br>'),
                confirmButtonText: 'تایید'
            });
            return;
        }

        // تأیید قبل از ارسال فرم
        e.preventDefault();
        Swal.fire({
            title: 'آیا اطمینان دارید؟',
            text: 'آیا مطمئن هستید که می‌خواهید اطلاعات را ذخیره کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، ذخیره کن',
            cancelButtonText: 'خیر'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#settings-form')[0].submit();
            }
        });
    });
});
</script>
</body>
</html>