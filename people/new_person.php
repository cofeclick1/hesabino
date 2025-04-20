<?php
require_once '../includes/init.php';

// بررسی دسترسی کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/login.php');
    exit;
}
if (!$auth->hasPermission('people_add')) {
    header('Location: ' . BASE_PATH . '/dashboard.php');
    $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید';
    exit;
}

// ایجاد نمونه از دیتابیس
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
    $tags = isset($_POST['tags']) ? sanitize($_POST['tags']) : '';
    $birthday = sanitize($_POST['birthday']);
    $province = sanitize($_POST['province']);
    $city = sanitize($_POST['city']);
    $postalCode = sanitize($_POST['postal_code']);
    $website = sanitize($_POST['website']);
    $instagramId = sanitize($_POST['instagram_id']);
    $telegramId = sanitize($_POST['telegram_id']);
    $whatsappNumber = sanitize($_POST['whatsapp_number']);
    $bankName = sanitize($_POST['bank_name']);
    $accountNumber = sanitize($_POST['account_number']);
    $cardNumber = sanitize($_POST['card_number']);
    $ibanNumber = sanitize($_POST['iban_number']);

    // اعتبارسنجی داده‌ها
    if (empty($firstName)) {
        $error = 'نام الزامی است';
    } elseif (empty($lastName)) {
        $error = 'نام خانوادگی الزامی است';
    } elseif (empty($mobile)) {
        $error = 'شماره موبایل الزامی است';
    } elseif (!preg_match('/^09[0-9]{9}$/', $mobile)) {
        $error = 'فرمت شماره موبایل صحیح نیست';
    } elseif (!empty($nationalCode) && !validateNationalCode($nationalCode)) {
        $error = 'کد ملی معتبر نیست';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'ایمیل معتبر نیست';
    } elseif (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = 'آدرس وب‌سایت معتبر نیست';
    } elseif (!empty($postalCode) && !preg_match('/^[0-9]{10}$/', $postalCode)) {
        $error = 'کد پستی معتبر نیست';
    } elseif (!empty($cardNumber) && !validateCardNumber($cardNumber)) {
        $error = 'شماره کارت بانکی معتبر نیست';
    } elseif (!empty($ibanNumber) && !validateIBAN($ibanNumber)) {
        $error = 'شماره شبا معتبر نیست';
    }

    // بررسی تکراری نبودن شماره موبایل
    if (empty($error)) {
        $existingPerson = $db->get('people', '*', ['mobile' => $mobile, 'deleted_at' => null]);
        if ($existingPerson) {
            $error = 'شماره موبایل وارد شده قبلاً ثبت شده است';
        }
    }

    // آپلود و بهینه‌سازی تصویر پروفایل
    $profileImage = '';
    if (!empty($_FILES['profile_image']['name'])) {
        try {
            $uploader = new ImageUploader($_FILES['profile_image']);
            $uploader->setAllowedTypes(['image/jpeg', 'image/png', 'image/webp']);
            $uploader->setMaxSize(2 * 1024 * 1024); // 2MB
            $uploader->setPath('../assets/images/profiles/');
            $uploader->setDimensions(300, 300);
            $uploader->setQuality(80);
            
            $profileImage = $uploader->upload();
            if (!$profileImage) {
                $error = 'خطا در آپلود تصویر: ' . $uploader->getError();
            }
        } catch (Exception $e) {
            $error = 'خطا در آپلود تصویر: ' . $e->getMessage();
        }
    }

    // ثبت در دیتابیس
    if (empty($error)) {
        try {
            $db->beginTransaction();

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
                'created_by' => $_SESSION['user_id'],
                'tags' => $tags,
                'birthday' => !empty($birthday) ? convertJalaliToGregorian($birthday) : null,
                'province' => $province,
                'city' => $city,
                'postal_code' => $postalCode,
                'website' => $website,
                'instagram_id' => $instagramId,
                'telegram_id' => $telegramId,
                'whatsapp_number' => $whatsappNumber,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'card_number' => $cardNumber,
                'iban_number' => $ibanNumber
            ];

            if ($personId = $db->insert('people', $data)) {
                // ثبت در تاریخچه تغییرات
                $changeLog = [
                    'table_name' => 'people',
                    'record_id' => $personId,
                    'action' => 'create',
                    'user_id' => $_SESSION['user_id'],
                    'changes' => json_encode($data),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $db->insert('changes', $changeLog);

                $db->commit();
                $success = 'شخص جدید با موفقیت ثبت شد';
                // ذخیره نام و نام خانوادگی برای نمایش در نوتیفیکیشن
                echo "<script>
                    var personName = '" . $firstName . ' ' . $lastName . "';
                    var showSuccessMessage = true;
                    var redirectToEdit = true;
                    var personId = " . $personId . ";
                </script>";
                // پاک کردن فرم
                $_POST = [];
            } else {
                throw new Exception('خطا در ثبت اطلاعات');
            }
        } catch (Exception $e) {
            $db->rollback();
            $error = 'خطا در ثبت اطلاعات: ' . $e->getMessage();
        }
    }
}

// دریافت لیست استان‌ها از دیتابیس
$provinces = $db->query("SELECT id, name FROM provinces ORDER BY name")->fetchAll();
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
    <!-- اضافه کردن Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Persian DatePicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jalalidatepicker@0.1.0/dist/jalalidatepicker.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .profile-upload {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            position: relative;
            cursor: pointer;
            border-radius: 50%;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .profile-upload img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            transition: all 0.3s ease;
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
            flex-direction: column;
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
            font-size: 2rem;
            color: #fff;
            margin-bottom: 5px;
        }
        .profile-upload:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .profile-upload:hover img {
            border-color: #0d6efd;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .card-body {
            padding: 2rem;
        }
        .btn-toolbar {
            gap: 0.5rem;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #0d6efd;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
            border-bottom-color: #0d6efd;
        }
        .tab-pane {
            padding: 1.5rem 0;
        }
        .required-star {
            color: #dc3545;
            margin-right: 4px;
        }
        .social-input {
            position: relative;
        }
        .social-input .form-control {
            padding-right: 40px;
        }
        .social-input i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.2rem;
        }
        .tag-input {
            min-height: 100px;
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
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            افزودن شخص جدید
                        </h4>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo BASE_PATH; ?>/dashboard.php">داشبورد</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="people_list.php">لیست اشخاص</a>
                                </li>
                                <li class="breadcrumb-item active">افزودن شخص</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-toolbar justify-content-end">
                            <a href="people_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-list me-1"></i>
                                لیست اشخاص
                            </a>
                        </div>
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
                        <form method="POST" enctype="multipart/form-data" id="personForm" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-3">
                                    <!-- تصویر پروفایل و اطلاعات اصلی -->
                                    <div class="text-center mb-4">
                                        <div class="profile-upload">
                                            <img src="<?php echo BASE_PATH; ?>/assets/images/default-avatar.png" 
                                                 alt="تصویر پروفایل" id="profilePreview">
                                            <div class="overlay">
                                                <i class="fas fa-camera"></i>
                                                <small>تغییر تصویر</small>
                                            </div>
                                            <input type="file" name="profile_image" id="profileInput" 
                                                   accept="image/*" style="display: none;">
                                        </div>
                                        <small class="text-muted d-block">حداکثر حجم: 2 مگابایت</small>
                                        <small class="text-muted d-block">فرمت‌های مجاز: JPG، PNG، WebP</small>
                                    </div>

                                    <!-- تگ‌ها -->
                                    <div class="mb-4">
                                        <label class="form-label">تگ‌ها</label>
                                        <textarea name="tags" class="form-control tag-input" 
                                                  placeholder="تگ‌ها را با کاما جدا کنید..."><?php echo $_POST['tags'] ?? ''; ?></textarea>
                                    </div>

                                    <!-- دکمه‌های عملیات -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            ذخیره اطلاعات
                                        </button>
                                        <a href="people_list.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            انصراف
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <!-- تب‌ها -->
                                    <ul class="nav nav-tabs" id="personTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" 
                                                    data-bs-target="#basic" type="button" role="tab" 
                                                    aria-controls="basic" aria-selected="true">
                                                <i class="fas fa-user me-1"></i>
                                                اطلاعات اصلی
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                                    data-bs-target="#contact" type="button" role="tab"
                                                    aria-controls="contact" aria-selected="false">
                                                <i class="fas fa-address-book me-1"></i>
                                                اطلاعات تماس
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="bank-tab" data-bs-toggle="tab"
                                                    data-bs-target="#bank" type="button" role="tab"
                                                    aria-controls="bank" aria-selected="false">
                                                <i class="fas fa-university me-1"></i>
                                                اطلاعات بانکی
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="other-tab" data-bs-toggle="tab"
                                                    data-bs-target="#other" type="button" role="tab"
                                                    aria-controls="other" aria-selected="false">
                                                <i class="fas fa-cog me-1"></i>
                                                سایر اطلاعات
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="personTabsContent">
                                        <!-- تب اطلاعات اصلی -->
                                        <div class="tab-pane fade show active" id="basic" role="tabpanel" 
                                             aria-labelledby="basic-tab">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">
                                                        نام
                                                        <span class="required-star">*</span>
                                                    </label>
                                                    <input type="text" name="first_name" class="form-control" 
                                                           value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                                                    <div class="invalid-feedback">
                                                        لطفاً نام را وارد کنید
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">
                                                        نام خانوادگی
                                                        <span class="required-star">*</span>
                                                    </label>
                                                    <input type="text" name="last_name" class="form-control" 
                                                           value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                                                    <div class="invalid-feedback">
                                                        لطفاً نام خانوادگی را وارد کنید
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">
                                                        موبایل
                                                        <span class="required-star">*</span>
                                                    </label>
                                                    <input type="text" name="mobile" class="form-control" dir="ltr"
                                                           pattern="09[0-9]{9}" placeholder="09xxxxxxxxx"
                                                           value="<?php echo $_POST['mobile'] ?? ''; ?>" required>
                                                    <div class="invalid-feedback">
                                                        لطفاً شماره موبایل معتبر وارد کنید
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
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
                                                    <div class="invalid-feedback">
                                                        کد ملی باید 10 رقم باشد
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">تاریخ تولد</label>
                                                    <input type="text" name="birthday" class="form-control" 
                                                           data-jdp data-jdp-format="YYYY/MM/DD"
                                                           value="<?php echo $_POST['birthday'] ?? ''; ?>">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">سقف اعتبار (ریال)</label>
                                                    <input type="number" name="credit_limit" class="form-control" dir="ltr"
                                                           value="<?php echo $_POST['credit_limit'] ?? '0'; ?>" min="0">
                                                </div>
                                            </div>

                                            <div class="row legal-fields" style="display: none;">
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
                                        </div>

                                        <!-- تب اطلاعات تماس -->
                                        <div class="tab-pane fade" id="contact" role="tabpanel" 
                                             aria-labelledby="contact-tab">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">تلفن ثابت</label>
                                                    <input type="text" name="phone" class="form-control" dir="ltr"
                                                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ایمیل</label>
                                                    <input type="email" name="email" class="form-control" dir="ltr"
                                                           value="<?php echo $_POST['email'] ?? ''; ?>">
                                                    <div class="invalid-feedback">
                                                        لطفاً یک ایمیل معتبر وارد کنید
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">استان</label>
                                                    <select name="province" class="form-select province-select">
                                                        <option value="">انتخاب کنید</option>
                                                        <?php foreach ($provinces as $province): ?>
                                                        <option value="<?php echo $province['id']; ?>" 
                                                                <?php echo (($_POST['province'] ?? '') == $province['id']) ? 'selected' : ''; ?>>
                                                            <?php echo $province['name']; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">شهر</label>
                                                    <select name="city" class="form-select city-select" disabled>
                                                        <option value="">ابتدا استان را انتخاب کنید</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">آدرس کامل</label>
                                                    <textarea name="address" class="form-control" rows="3"
                                                              placeholder="آدرس کامل را وارد کنید..."><?php echo $_POST['address'] ?? ''; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">کد پستی</label>
                                                    <input type="text" name="postal_code" class="form-control" dir="ltr"
                                                           pattern="[0-9]{10}" placeholder="xxxxxxxxxx"
                                                           value="<?php echo $_POST['postal_code'] ?? ''; ?>">
                                                    <div class="invalid-feedback">
                                                        کد پستی باید 10 رقم باشد
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">وب‌سایت</label>
                                                    <input type="url" name="website" class="form-control" dir="ltr"
                                                           placeholder="https://example.com"
                                                           value="<?php echo $_POST['website'] ?? ''; ?>">
                                                    <div class="invalid-feedback">
                                                        لطفاً یک آدرس وب‌سایت معتبر وارد کنید
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">اینستاگرام</label>
                                                    <div class="social-input">
                                                        <input type="text" name="instagram_id" class="form-control" dir="ltr"
                                                           placeholder="نام کاربری اینستاگرام"
                                                           value="<?php echo $_POST['instagram_id'] ?? ''; ?>">
                                                        <i class="fab fa-instagram"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">تلگرام</label>
                                                    <div class="social-input">
                                                        <input type="text" name="telegram_id" class="form-control" dir="ltr"
                                                               placeholder="نام کاربری تلگرام"
                                                               value="<?php echo $_POST['telegram_id'] ?? ''; ?>">
                                                        <i class="fab fa-telegram"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">واتس‌اپ</label>
                                                    <div class="social-input">
                                                        <input type="text" name="whatsapp_number" class="form-control" dir="ltr"
                                                               placeholder="شماره واتس‌اپ"
                                                               value="<?php echo $_POST['whatsapp_number'] ?? ''; ?>">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- تب اطلاعات بانکی -->
                                        <div class="tab-pane fade" id="bank" role="tabpanel" 
                                             aria-labelledby="bank-tab">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">نام بانک</label>
                                                    <select name="bank_name" class="form-select">
                                                        <option value="">انتخاب کنید</option>
                                                        <option value="mellat" <?php echo (($_POST['bank_name'] ?? '') == 'mellat') ? 'selected' : ''; ?>>بانک ملت</option>
                                                        <option value="melli" <?php echo (($_POST['bank_name'] ?? '') == 'melli') ? 'selected' : ''; ?>>بانک ملی</option>
                                                        <option value="saderat" <?php echo (($_POST['bank_name'] ?? '') == 'saderat') ? 'selected' : ''; ?>>بانک صادرات</option>
                                                        <option value="tejarat" <?php echo (($_POST['bank_name'] ?? '') == 'tejarat') ? 'selected' : ''; ?>>بانک تجارت</option>
                                                        <option value="parsian" <?php echo (($_POST['bank_name'] ?? '') == 'parsian') ? 'selected' : ''; ?>>بانک پارسیان</option>
                                                        <option value="pasargad" <?php echo (($_POST['bank_name'] ?? '') == 'pasargad') ? 'selected' : ''; ?>>بانک پاسارگاد</option>
                                                        <option value="saman" <?php echo (($_POST['bank_name'] ?? '') == 'saman') ? 'selected' : ''; ?>>بانک سامان</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">شماره حساب</label>
                                                    <input type="text" name="account_number" class="form-control" dir="ltr"
                                                           value="<?php echo $_POST['account_number'] ?? ''; ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">شماره کارت</label>
                                                    <input type="text" name="card_number" class="form-control" dir="ltr"
                                                           pattern="[0-9]{16}" placeholder="xxxx-xxxx-xxxx-xxxx"
                                                           value="<?php echo $_POST['card_number'] ?? ''; ?>">
                                                    <div class="invalid-feedback">
                                                        شماره کارت باید 16 رقم باشد
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">شماره شبا</label>
                                                    <input type="text" name="iban_number" class="form-control" dir="ltr"
                                                           pattern="IR[0-9]{24}" placeholder="IR000000000000000000000000"
                                                           value="<?php echo $_POST['iban_number'] ?? ''; ?>">
                                                    <div class="invalid-feedback">
                                                        شماره شبا باید با IR شروع شود و 24 رقم باشد
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- تب سایر اطلاعات -->
                                        <div class="tab-pane fade" id="other" role="tabpanel" 
                                             aria-labelledby="other-tab">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">یادداشت‌ها</label>
                                                    <textarea name="notes" class="form-control" rows="5"
                                                              placeholder="یادداشت‌های اضافی..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Persian DatePicker -->
    <script src="https://cdn.jsdelivr.net/npm/jalalidatepicker@0.1.0/dist/jalalidatepicker.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // فعال‌سازی Select2
            $('.province-select, .city-select').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // فعال‌سازی تاریخ شمسی
            jalaliDatepicker.startWatch();

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

            // اعتبارسنجی فرم
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();

                        // نمایش تب حاوی اولین خطا
                        var firstInvalidInput = form.querySelector(':invalid');
                        if (firstInvalidInput) {
                            var tabPane = firstInvalidInput.closest('.tab-pane');
                            if (tabPane) {
                                var tabId = tabPane.id;
                                $(`#personTabs button[data-bs-target="#${tabId}"]`).tab('show');
                            }
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // دریافت شهرها بر اساس استان
            $('.province-select').change(function() {
                var provinceId = $(this).val();
                var citySelect = $('.city-select');
                
                if (provinceId) {
                    $.ajax({
                        url: '<?php echo BASE_PATH; ?>/ajax/get_cities.php',
                        type: 'POST',
                        data: {province_id: provinceId},
                        success: function(response) {
                            citySelect.html(response).prop('disabled', false);
                        }
                    });
                } else {
                    citySelect.html('<option value="">ابتدا استان را انتخاب کنید</option>').prop('disabled', true);
                }
            });

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
                }).then((result) => {
                    if (result.isConfirmed && typeof redirectToEdit !== 'undefined' && redirectToEdit) {
                        window.location.href = 'edit_person.php?id=' + personId;
                    }
                });
            }

            // نمایش پیام‌های خطا به صورت SweetAlert
            <?php if ($error): ?>
            Swal.fire({
                title: 'خطا',
                text: '<?php echo $error; ?>',
                icon: 'error',
                confirmButtonText: 'تایید',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    popup: 'rtl'
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>