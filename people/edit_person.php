<?php
require_once '../includes/init.php';

// بررسی وجود شناسه شخص
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: ' . BASE_PATH . '/people/people_list.php');
    exit;
}

$db = Database::getInstance();
$error = '';
$success = '';

// دریافت اطلاعات شخص
try {
    $person = $db->get('people', '*', ['id' => $id, 'deleted_at' => null]);
    if (!$person) {
        header('Location: ' . BASE_PATH . '/people/people_list.php');
        exit;
    }
} catch (Exception $e) {
    $error = 'خطا در دریافت اطلاعات: ' . $e->getMessage();
}

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

    // آپلود تصویر پروفایل جدید
    $profileImage = $person['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $newProfileImage = uploadImage($_FILES['profile_image'], '../assets/images/profiles/');
        if ($newProfileImage) {
            // حذف تصویر قبلی اگر وجود داشته باشد
            if (!empty($profileImage) && file_exists('../' . $profileImage)) {
                unlink('../' . $profileImage);
            }
            $profileImage = $newProfileImage;
        } else {
            $error = 'خطا در آپلود تصویر پروفایل';
        }
    }

    // به‌روزرسانی در دیتابیس
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
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['user_id']
            ];

            if ($db->update('people', $data, ['id' => $id])) {
                $success = 'اطلاعات با موفقیت به‌روزرسانی شد';
                // به‌روزرسانی اطلاعات شخص
                $person = array_merge($person, $data);
                // ذخیره نام و نام خانوادگی برای نمایش در نوتیفیکیشن
                echo "<script>
                    var personName = '" . $firstName . ' ' . $lastName . "';
                    var showSuccessMessage = true;
                </script>";
            } else {
                $error = 'خطا در به‌روزرسانی اطلاعات';
            }
        } catch (Exception $e) {
            $error = 'خطا در به‌روزرسانی اطلاعات: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش شخص - <?php echo SITE_NAME; ?></title>
    
    <!-- فونت‌ها و استایل‌ها -->
    <link href="<?php echo BASE_PATH; ?>/assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/sidebar.css">
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
        .history-item {
            padding: 1rem;
            border-left: 3px solid #0d6efd;
            background-color: #f8f9fa;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        .tab-pane {
            padding: 1.5rem 0;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
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
                            <i class="fas fa-user-edit me-2"></i>
                            ویرایش شخص
                        </h4>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo BASE_PATH; ?>/dashboard.php">داشبورد</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="people_list.php">لیست اشخاص</a>
                                </li>
                                <li class="breadcrumb-item active">ویرایش شخص</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-toolbar justify-content-end">
                            <a href="people_list.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-list me-1"></i>
                                لیست اشخاص
                            </a>
                            <a href="new_person.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>
                                افزودن شخص جدید
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

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <!-- محتوای اصلی -->
                <div class="row">
                    <div class="col-md-3">
                        <!-- اطلاعات کلی -->
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <div class="profile-upload mb-3">
                                    <img src="<?php echo !empty($person['profile_image']) ? BASE_PATH . '/' . $person['profile_image'] : BASE_PATH . '/assets/images/default-avatar.png'; ?>" 
                                         alt="تصویر پروفایل" id="profilePreview">
                                    <div class="overlay">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <input type="file" name="profile_image" id="profileInput" 
                                           accept="image/*" form="personForm" style="display: none;">
                                </div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></h5>
                                <p class="text-muted mb-3"><?php echo $person['type'] === 'real' ? 'شخص حقیقی' : 'شخص حقوقی'; ?></p>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" form="personForm">
                                        <i class="fas fa-save me-1"></i>
                                        ذخیره تغییرات
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- آمار -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">آمار کلی</h6>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">تعداد فاکتورها</small>
                                    <h4 class="mb-0">
                                        <?php
                                            $invoiceCount = $db->count('invoices', ['customer_id' => $id]);
                                            echo number_format($invoiceCount);
                                        ?>
                                    </h4>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">مجموع خرید (ریال)</small>
                                    <h4 class="mb-0">
                                        <?php
                                            $totalPurchase = $db->query(
                                                "SELECT COALESCE(SUM(final_amount), 0) as total FROM invoices WHERE customer_id = ? AND status = 'confirmed'",
                                                [$id]
                                            )->fetch()['total'];
                                            echo number_format($totalPurchase);
                                        ?>
                                    </h4>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1">تاریخ ثبت</small>
                                    <div class="text-secondary">
                                        <?php echo toJalali($person['created_at']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <!-- تب‌ها -->
                                <ul class="nav nav-tabs" id="personTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" 
                                                data-bs-target="#info" type="button" role="tab" 
                                                aria-controls="info" aria-selected="true">
                                            <i class="fas fa-user me-1"></i>
                                            اطلاعات اصلی
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="invoices-tab" data-bs-toggle="tab"
                                                data-bs-target="#invoices" type="button" role="tab"
                                                aria-controls="invoices" aria-selected="false">
                                            <i class="fas fa-file-invoice me-1"></i>
                                            فاکتورها
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="history-tab" data-bs-toggle="tab"
                                                data-bs-target="#history" type="button" role="tab"
                                                aria-controls="history" aria-selected="false">
                                            <i class="fas fa-history me-1"></i>
                                            تاریخچه تغییرات
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="personTabsContent">
                                    <!-- تب اطلاعات اصلی -->
                                    <div class="tab-pane fade show active" id="info" role="tabpanel" 
                                         aria-labelledby="info-tab">
                                        <form method="POST" enctype="multipart/form-data" id="personForm">
                                            <div class="row">
                                                <!-- اطلاعات اصلی -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">نام <span class="text-danger">*</span></label>
                                                    <input type="text" name="first_name" class="form-control" 
                                                           value="<?php echo htmlspecialchars($person['first_name']); ?>" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">نام خانوادگی <span class="text-danger">*</span></label>
                                                    <input type="text" name="last_name" class="form-control" 
                                                           value="<?php echo htmlspecialchars($person['last_name']); ?>" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">موبایل <span class="text-danger">*</span></label>
                                                    <input type="text" name="mobile" class="form-control" dir="ltr"
                                                           pattern="09[0-9]{9}" placeholder="09xxxxxxxxx"
                                                           value="<?php echo htmlspecialchars($person['mobile']); ?>" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- اطلاعات تکمیلی -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">نوع شخص</label>
                                                    <select name="type" class="form-select" id="personType">
                                                        <option value="real" <?php echo $person['type'] == 'real' ? 'selected' : ''; ?>>
                                                            حقیقی
                                                        </option>
                                                        <option value="legal" <?php echo $person['type'] == 'legal' ? 'selected' : ''; ?>>
                                                            حقوقی
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">کد ملی</label>
                                                    <input type="text" name="national_code" class="form-control" dir="ltr"
                                                           pattern="[0-9]{10}" placeholder="xxxxxxxxxx"
                                                           value="<?php echo htmlspecialchars($person['national_code']); ?>">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">تلفن ثابت</label>
                                                    <input type="text" name="phone" class="form-control" dir="ltr"
                                                           value="<?php echo htmlspecialchars($person['phone']); ?>">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">ایمیل</label>
                                                    <input type="email" name="email" class="form-control" dir="ltr"
                                                           value="<?php echo htmlspecialchars($person['email']); ?>">
                                                </div>
                                            </div>

                                            <div class="row legal-fields" style="display: <?php echo $person['type'] == 'legal' ? 'flex' : 'none'; ?>;">
                                                <!-- اطلاعات حقوقی -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">نام شرکت</label>
                                                    <input type="text" name="company" class="form-control"
                                                           value="<?php echo htmlspecialchars($person['company']); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">کد اقتصادی</label>
                                                    <input type="text" name="economic_code" class="form-control" dir="ltr"
                                                           value="<?php echo htmlspecialchars($person['economic_code']); ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- اطلاعات مالی و آدرس -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">سقف اعتبار (ریال)</label>
                                                    <input type="number" name="credit_limit" class="form-control" dir="ltr"
                                                           value="<?php echo htmlspecialchars($person['credit_limit']); ?>" min="0">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">آدرس</label>
                                                    <textarea name="address" class="form-control" rows="3"
                                                              placeholder="آدرس کامل..."><?php echo htmlspecialchars($person['address']); ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">یادداشت‌ها</label>
                                                    <textarea name="notes" class="form-control" rows="3"
                                                              placeholder="یادداشت‌های اضافی..."><?php echo htmlspecialchars($person['notes']); ?></textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- تب فاکتورها -->
                                    <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>شماره فاکتور</th>
                                                        <th>تاریخ</th>
                                                        <th>مبلغ کل (ریال)</th>
                                                        <th>وضعیت</th>
                                                        <th>عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $invoices = $db->query(
                                                            "SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10",
                                                            [$id]
                                                        )->fetchAll();
                                                        
                                                        if (!empty($invoices)):
                                                            foreach ($invoices as $invoice):
                                                                $statusClass = '';
                                                                $statusText = '';
                                                                switch ($invoice['status']) {
                                                                    case 'draft':
                                                                        $statusClass = 'secondary';
                                                                        $statusText = 'پیش‌نویس';
                                                                        break;
                                                                    case 'pending':
                                                                        $statusClass = 'warning';
                                                                        $statusText = 'در انتظار تایید';
                                                                        break;
                                                                    case 'confirmed':
                                                                        $statusClass = 'success';
                                                                        $statusText = 'تایید شده';
                                                                        break;
                                                                    case 'cancelled':
                                                                        $statusClass = 'danger';
                                                                        $statusText = 'لغو شده';
                                                                        break;
                                                                }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $invoice['invoice_number']; ?></td>
                                                        <td><?php echo toJalali($invoice['created_at']); ?></td>
                                                        <td><?php echo number_format($invoice['final_amount']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                                <?php echo $statusText; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="../invoices/view_invoice.php?id=<?php echo $invoice['id']; ?>" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                            endforeach;
                                                        else:
                                                    ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4">
                                                            <div class="text-muted">هیچ فاکتوری یافت نشد</div>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if (!empty($invoices)): ?>
                                        <div class="text-end">
                                            <a href="../invoices/invoice_list.php?customer_id=<?php echo $id; ?>" class="btn btn-primary">
                                                <i class="fas fa-list me-1"></i>
                                                مشاهده همه فاکتورها
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- تب تاریخچه تغییرات -->
                                    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                                        <?php
                                            $changes = $db->query(
                                                "SELECT c.*, u.username as user_name
                                                FROM changes c
                                                LEFT JOIN users u ON c.user_id = u.id
                                                WHERE c.table_name = 'people' AND c.record_id = ?
                                                ORDER BY c.created_at DESC
                                                LIMIT 10",
                                                [$id]
                                            )->fetchAll();

                                            if (!empty($changes)):
                                                foreach ($changes as $change):
                                        ?>
                                        <div class="history-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong><?php echo $change['user_name']; ?></strong>
                                                    <span class="text-muted mx-2">•</span>
                                                    <small class="text-muted"><?php echo toJalali($change['created_at']); ?></small>
                                                </div>
                                                <span class="badge bg-info"><?php echo $change['action']; ?></span>
                                            </div>
                                            <div class="text-secondary">
                                                <?php echo $change['description']; ?>
                                            </div>
                                        </div>
                                        <?php
                                                endforeach;
                                            else:
                                        ?>
                                        <div class="text-center py-4">
                                            <div class="text-muted">هیچ تغییری ثبت نشده است</div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/sidebar.js"></script>
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

            // نمایش نوتیفیکیشن در صورت موفقیت
            if (typeof showSuccessMessage !== 'undefined' && showSuccessMessage) {
                Swal.fire({
                    title: 'عملیات موفق',
                    text: 'اطلاعات ' + personName + ' با موفقیت به‌روزرسانی شد',
                    icon: 'success',
                    confirmButtonText: 'تایید',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        popup: 'rtl'
                                            }
                });
            }

            // تایید حذف شخص
            $('#deletePersonBtn').click(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: "این عملیات قابل بازگشت نیست!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'انصراف',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary',
                        popup: 'rtl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'delete_person.php?id=' + <?php echo $id; ?>;
                    }
                });
            });

            // نمایش تب فعال در URL
            var hash = window.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }

            // به‌روزرسانی URL با تغییر تب
            $('.nav-tabs a').click(function(e) {
                $(this).tab('show');
                window.location.hash = this.hash;
            });

            // اعتبارسنجی فرم قبل از ارسال
            $('#personForm').submit(function(e) {
                var mobile = $('input[name="mobile"]').val();
                var mobilePattern = /^09[0-9]{9}$/;
                
                if (!mobilePattern.test(mobile)) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'خطا',
                        text: 'فرمت شماره موبایل صحیح نیست',
                        icon: 'error',
                        confirmButtonText: 'تایید',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            popup: 'rtl'
                        }
                    });
                    return false;
                }

                var nationalCode = $('input[name="national_code"]').val();
                if (nationalCode && !/^[0-9]{10}$/.test(nationalCode)) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'خطا',
                        text: 'کد ملی باید 10 رقم باشد',
                        icon: 'error',
                        confirmButtonText: 'تایید',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            popup: 'rtl'
                        }
                    });
                    return false;
                }
            });

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