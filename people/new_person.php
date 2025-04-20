<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر

$db = Database::getInstance();
$error = '';
$success = '';

// پردازش فرم در صورت ارسال
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $mobile = sanitize($_POST['mobile']);
    $type = sanitize($_POST['type']);
    $nationalCode = sanitize($_POST['national_code']);
    $phone = sanitize($_POST['phone']);
    $company = sanitize($_POST['company']);
    $economicCode = sanitize($_POST['economic_code']);
    $email = sanitize($_POST['email']);
    $creditLimit = floatval($_POST['credit_limit']);
    $address = sanitize($_POST['address']);
    $notes = sanitize($_POST['notes']);
    
    // اعتبارسنجی داده‌ها
    if (empty($firstName)) {
        $error = 'نام الزامی است';
    } elseif (empty($lastName)) {
        $error = 'نام خانوادگی الزامی است';
    } elseif (empty($mobile)) {
        $error = 'شماره موبایل الزامی است';
    } elseif (!preg_match('/^09[0-9]{9}$/', $mobile)) {
        $error = 'فرمت شماره موبایل صحیح نیست';
    }

    // آپلود تصویر پروفایل
    $profileImage = '';
    if (!empty($_FILES['profile_image']['name'])) {
        $profileImage = uploadImage($_FILES['profile_image'], '../assets/images/profiles/');
        if (!$profileImage) {
            $error = 'خطا در آپلود تصویر پروفایل';
        }
    }

    // ثبت در دیتابیس
    if (empty($error)) {
        try {
            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'mobile' => $mobile,
                'type' => $type,
                'national_code' => $nationalCode,
                'phone' => $phone,
                'company' => $company,
                'economic_code' => $economicCode,
                'email' => $email,
                'credit_limit' => $creditLimit,
                'address' => $address,
                'notes' => $notes,
                'profile_image' => $profileImage,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $_SESSION['user_id']
            ];

            if ($db->insert('people', $data)) {
                $success = 'شخص جدید با موفقیت ثبت شد';
                // ذخیره نام و نام خانوادگی برای نمایش در نوتیفیکیشن
                echo "<script>
                    var personName = '" . $firstName . ' ' . $lastName . "';
                    var showSuccessMessage = true;
                </script>";
                // پاک کردن فرم
                $_POST = [];
            } else {
                $error = 'خطا در ثبت اطلاعات';
            }
        } catch (Exception $e) {
            $error = 'خطا در ثبت اطلاعات: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شخص جدید - <?php echo SITE_NAME; ?></title>
    
    <!-- فونت‌ها و استایل‌ها -->
    <link href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
    <!-- اضافه کردن SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .profile-upload {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
            cursor: pointer;
            border-radius: 50%;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-upload img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        .profile-upload .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transition: all 0.3s ease;
        }
        .profile-upload:hover .overlay {
            opacity: 1;
        }
        .profile-upload .fa-camera {
            font-size: 1.5rem;
            color: #fff;
        }
        .profile-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
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

            <!-- Page Content -->
            <div class="container-fluid px-4 py-4">
                <div class="row mb-4">
                    <div class="col">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            افزودن شخص جدید
                        </h4>
                    </div>
                    <div class="col-auto">
                        <a href="people_list.php" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i>
                            لیست اشخاص
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="personForm">
                            <!-- آپلود تصویر پروفایل -->
                            <div class="text-center mb-4">
                                <div class="profile-upload">
                                    <img src="<?php echo BASE_PATH; ?>/assets/images/default-avatar.png" 
                                         alt="تصویر پروفایل" id="profilePreview">
                                    <div class="overlay">
                                        <i class="fas fa-camera fa-2x"></i>
                                    </div>
                                    <input type="file" name="profile_image" id="profileInput" 
                                           accept="image/*" style="display: none;">
                                </div>
                                <small class="text-muted">برای آپلود تصویر کلیک کنید</small>
                            </div>

                            <div class="row">
                                <!-- اطلاعات اصلی -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">نام <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">نام خانوادگی <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">موبایل <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" class="form-control" dir="ltr"
                                           pattern="09[0-9]{9}" placeholder="09xxxxxxxxx"
                                           value="<?php echo $_POST['mobile'] ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- اطلاعات تکمیلی -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">نوع شخص</label>
                                    <select name="type" class="form-select" id="personType">
                                        <option value="real" <?php echo (($_POST['type'] ?? '') == 'real') ? 'selected' : ''; ?>>
                                            حقیقی
                                        </option>
                                        <option value="legal" <?php echo (($_POST['type'] ?? '') == 'legal') ? 'selected' : ''; ?>>
                                            حقوقی
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">کد ملی</label>
                                    <input type="text" name="national_code" class="form-control" dir="ltr"
                                           pattern="[0-9]{10}" placeholder="xxxxxxxxxx"
                                           value="<?php echo $_POST['national_code'] ?? ''; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">تلفن ثابت</label>
                                    <input type="text" name="phone" class="form-control" dir="ltr"
                                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ایمیل</label>
                                    <input type="email" name="email" class="form-control" dir="ltr"
                                           value="<?php echo $_POST['email'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="row legal-fields" style="display: none;">
                                <!-- اطلاعات حقوقی -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">نام شرکت</label>
                                    <input type="text" name="company" class="form-control"
                                           value="<?php echo $_POST['company'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">کد اقتصادی</label>
                                    <input type="text" name="economic_code" class="form-control" dir="ltr"
                                           value="<?php echo $_POST['economic_code'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <!-- اطلاعات مالی و آدرس -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">سقف اعتبار (ریال)</label>
                                    <input type="number" name="credit_limit" class="form-control" dir="ltr"
                                           value="<?php echo $_POST['credit_limit'] ?? '0'; ?>" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">آدرس</label>
                                    <textarea name="address" class="form-control" rows="3"
                                              placeholder="آدرس کامل..."><?php echo $_POST['address'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">یادداشت‌ها</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                              placeholder="یادداشت‌های اضافی..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    ذخیره اطلاعات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/sidebar.js"></script>
    <!-- اضافه کردن اسکریپت SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // نمایش/مخفی کردن فیلدهای حقوقی
            $('#personType').change(function() {
                if ($(this).val() === 'legal') {
                    $('.legal-fields').slideDown();
                } else {
                    $('.legal-fields').slideUp();
                }
            });

            // نمایش پیش‌نمایش تصویر
            $('#profileInput').change(function(e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profilePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            // کلیک روی تصویر پروفایل
            $('.profile-upload').click(function() {
                $('#profileInput').click();
            });

            // تنظیم وضعیت اولیه فیلدهای حقوقی
            if ($('#personType').val() === 'legal') {
                $('.legal-fields').show();
            }

            // نمایش نوتیفیکیشن در صورت موفقیت
            if (typeof showSuccessMessage !== 'undefined' && showSuccessMessage) {
                Swal.fire({
                    title: 'عملیات موفق',
                    text: 'شخص ' + personName + ' با موفقیت افزوده شد',
                    icon: 'success',
                    confirmButtonText: 'تایید',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        popup: 'rtl'
                    }
                });
            }
        });
    </script>
</body>
</html>