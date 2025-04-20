<?php
require_once 'includes/init.php';

// بررسی دسترسی کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// توابع مورد نیاز
function getLastCustomerCode()
{
    global $db;
    
    // دریافت تاریخ شمسی بدون تبدیل اعداد به فارسی
    $jalaliDate = jdate('Y-m-d', time(), '', 'Asia/Tehran', 'en');
    list($year, $month, $day) = explode('-', $jalaliDate);
    
    // ساخت کد با فرمت درخواستی: سال(4) + ماه(2) + روز(2) + شماره ترتیبی(4)
    $dateCode = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
    
    try {
        // دریافت آخرین شماره از جدول شمارنده
        $result = $db->get('customer_counter', 'last_number', ['id' => 1]);
        $lastNumber = isset($result['last_number']) ? (int)$result['last_number'] : 0;
        $nextNumber = $lastNumber + 1;
        
        // ترکیب تاریخ و شماره ترتیبی
        return $dateCode . sprintf('%04d', $nextNumber);
        
    } catch (Exception $e) {
        error_log("خطا در نمایش کد مشتری: " . $e->getMessage());
        throw new Exception("خطا در نمایش کد مشتری");
    }
}

function generateAndSaveCustomerCode()
{
    global $db;
    
    // دریافت تاریخ شمسی بدون تبدیل اعداد به فارسی
    $jalaliDate = jdate('Y-m-d', time(), '', 'Asia/Tehran', 'en');
    list($year, $month, $day) = explode('-', $jalaliDate);
    
    // ساخت کد با فرمت درخواستی
    $dateCode = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
    
    try {
        $db->beginTransaction();
        
        // دریافت و افزایش آخرین شماره سریال
        $result = $db->get('customer_counter', 'last_number', ['id' => 1]);
        $lastNumber = isset($result['last_number']) ? (int)$result['last_number'] : 0;
        $newNumber = $lastNumber + 1;
        
        // بروزرسانی شماره در جدول شمارنده
        $db->update('customer_counter', 
            ['last_number' => $newNumber], 
            ['id' => 1]
        );
        
        $db->commit();
        
        // ترکیب تاریخ و شماره سریال
        return $dateCode . sprintf('%04d', $newNumber);
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        error_log("خطا در تولید کد مشتری: " . $e->getMessage());
        throw new Exception("خطا در تولید کد مشتری");
    }
}

// دریافت لیست مشتریان فعال
$query = "SELECT * FROM customers WHERE deleted_at IS NULL ORDER BY created_at DESC";
$customers = $db->query($query)->fetchAll();

// پردازش AJAX برای آپلود تصویر
if (isset($_POST['imageData'])) {
    try {
        $uploadDir = 'uploads/customers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'customer_' . uniqid() . '.jpg';
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

// پردازش فرم افزودن/ویرایش مشتری
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer']) || isset($_POST['edit_customer'])) {
        $isEdit = isset($_POST['edit_customer']);
        
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
            'mobile' => $_POST['mobile'],
            'email' => $_POST['email'] ?? null,
            'address' => $_POST['address'] ?? null,
            'description' => $_POST['description'] ?? null,
            'type' => $_POST['type'] ?? 'real',
            'national_code' => $_POST['national_code'] ?? null,
            'economic_code' => $_POST['economic_code'] ?? null,
            'company' => $_POST['company'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'credit_limit' => $_POST['credit_limit'] ?? 0,
            'credit_balance' => $_POST['credit_balance'] ?? 0
        ];

        if (isset($_POST['profile_image']) && !empty($_POST['profile_image'])) {
            $data['image'] = $_POST['profile_image'];
        }

        $errors = [];
        if (empty($data['first_name'])) $errors[] = 'نام الزامی است';
        if (empty($data['last_name'])) $errors[] = 'نام خانوادگی الزامی است';
        if (empty($data['mobile'])) $errors[] = 'شماره موبایل الزامی است';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'فرمت ایمیل صحیح نیست';
        }
        if (!empty($data['mobile']) && !preg_match('/^09[0-9]{9}$/', $data['mobile'])) {
            $errors[] = 'فرمت شماره موبایل صحیح نیست';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $customer_id = $_POST['customer_id'];
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $db->update('customers', $data, ['id' => $customer_id]);
                    $_SESSION['success_message'] = 'مشتری با موفقیت ویرایش شد';
                } else {
                    $customerCode = generateAndSaveCustomerCode();
                    $data['code'] = $customerCode;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = $_SESSION['user_id'];
                    $data['created_date'] = date('Y-m-d');
                    
                    $db->insert('customers', $data);
                    $_SESSION['success_message'] = 'مشتری با موفقیت اضافه شد';
                }
                
                header('Location: customers.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'خطا در ذخیره اطلاعات: ' . $e->getMessage();
            }
        }
    }
}

// حذف مشتری
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db->update('customers', 
            ['deleted_at' => date('Y-m-d H:i:s')],
            ['id' => $_GET['delete']]
        );
        $_SESSION['success_message'] = 'مشتری با موفقیت حذف شد';
        header('Location: customers.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'خطا در حذف مشتری: ' . $e->getMessage();
    }
}

// دریافت اطلاعات مشتری برای ویرایش
$editCustomer = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editCustomer = $db->get('customers', '*', ['id' => $_GET['edit']]);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مشتریان - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <style>
    .profile-upload-container {
        width: 150px;
        height: 150px;
        position: relative;
        margin: 0 auto 20px;
    }
    .profile-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
    }
    .profile-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .profile-upload-container:hover .profile-image-overlay {
        opacity: 1;
    }
    .profile-image-text {
        color: white;
        font-size: 14px;
    }
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
    .preview-container {
        width: 150px;
        height: 150px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%;
    }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content w-100">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid px-4">
                <div class="row g-4 my-4">
                    <div class="col-12">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success_message']; 
                                unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <?php echo $editCustomer ? 'ویرایش مشتری' : 'افزودن مشتری جدید'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" id="customerForm">
                                    <?php if ($editCustomer): ?>
                                        <input type="hidden" name="customer_id" value="<?php echo $editCustomer['id']; ?>">
                                    <?php endif; ?>

                                    <div class="profile-upload-container mb-4">
                                        <img src="<?php echo !empty($editCustomer['image']) ? htmlspecialchars($editCustomer['image']) : 'assets/images/default-user.png'; ?>" 
                                             alt="تصویر پروفایل" 
                                             class="profile-image" 
                                             id="profileImage">
                                        <div class="profile-image-overlay">
                                            <span class="profile-image-text">تغییر تصویر</span>
                                        </div>
                                        <input type="file" 
                                               id="profileImageInput" 
                                               accept="image/*" 
                                               style="display: none;">
                                        <input type="hidden" 
                                               name="profile_image" 
                                               id="profileImageData">
                                    </div>

                                    <?php if (!$editCustomer): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">کد مشتری</label>
                                                <?php 
                                                try {
                                                    $customerCode = getLastCustomerCode();
                                                    echo '<input type="text" class="form-control" value="' . htmlspecialchars($customerCode) . '" readonly>';
                                                } catch (Exception $e) {
                                                    echo '<input type="text" class="form-control" value="خطا در نمایش کد" readonly>';
                                                    echo '<div class="text-danger small mt-1">'. htmlspecialchars($e->getMessage()) .'</div>';
                                                }
                                                ?>
                                                <small class="text-muted">کد مشتری به صورت خودکار تولید می‌شود</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="first_name" class="form-label">نام *</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                   value="<?php echo htmlspecialchars($editCustomer['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="last_name" class="form-label">نام خانوادگی *</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name"
                                                   value="<?php echo htmlspecialchars($editCustomer['last_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="mobile" class="form-label">موبایل *</label>
                                            <input type="text" class="form-control" id="mobile" name="mobile"
                                                   value="<?php echo htmlspecialchars($editCustomer['mobile'] ?? ''); ?>" 
                                                   pattern="09[0-9]{9}" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="type" class="form-label">نوع مشتری</label>
                                            <select class="form-select" id="type" name="type">
                                                <option value="real" <?php echo ($editCustomer['type'] ?? 'real') == 'real' ? 'selected' : ''; ?>>
                                                    حقیقی
                                                </option>
                                                <option value="legal" <?php echo ($editCustomer['type'] ?? '') == 'legal' ? 'selected' : ''; ?>>
                                                    حقوقی
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="national_code" class="form-label">کد ملی</label>
                                            <input type="text" class="form-control" id="national_code" name="national_code"
                                                   value="<?php echo htmlspecialchars($editCustomer['national_code'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="phone" class="form-label">تلفن ثابت</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                   value="<?php echo htmlspecialchars($editCustomer['phone'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row" id="company-fields">
                                        <div class="col-md-6 mb-3">
                                            <label for="company" class="form-label">نام شرکت</label>
                                            <input type="text" class="form-control" id="company" name="company"
                                                   value="<?php echo htmlspecialchars($editCustomer['company'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="economic_code" class="form-label">کد اقتصادی</label>
                                            <input type="text" class="form-control" id="economic_code" name="economic_code"
                                                   value="<?php echo htmlspecialchars($editCustomer['economic_code'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="email" class="form-label">ایمیل</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?php echo htmlspecialchars($editCustomer['email'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="credit_limit" class="form-label">سقف اعتبار</label>
                                            <input type="number" step="0.01" class="form-control" id="credit_limit" 
                                                   name="credit_limit" value="<?php echo $editCustomer['credit_limit'] ?? '0.00'; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="credit_balance" class="form-label">مانده اعتبار</label>
                                            <input type="number" step="0.01" class="form-control" id="credit_balance" 
                                                   name="credit_balance" value="<?php echo $editCustomer['credit_balance'] ?? '0.00'; ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label">آدرس</label>
                                            <textarea class="form-control" id="address" name="address" 
                                                      rows="3"><?php echo htmlspecialchars($editCustomer['address'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="notes" class="form-label">یادداشت‌ها</label>
                                            <textarea class="form-control" id="notes" name="notes" 
                                                      rows="3"><?php echo htmlspecialchars($editCustomer['notes'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <button type="submit" name="<?php echo $editCustomer ? 'edit_customer' : 'add_customer'; ?>" 
                                                    class="btn btn-primary">
                                                <?php echo $editCustomer ? 'ویرایش مشتری' : 'افزودن مشتری'; ?>
                                            </button>
                                            <?php if ($editCustomer): ?>
                                                <a href="customers.php" class="btn btn-secondary">انصراف</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
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

                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">لیست مشتریان</h5>
                                <input type="text" id="searchInput" class="form-control form-control-sm w-auto" 
                                       placeholder="جستجو در مشتریان...">
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">تصویر</th>
                                                <th>کد مشتری</th>
                                                <th>نام و نام خانوادگی</th>
                                                <th>نوع</th>
                                                <th>موبایل</th>
                                                <th>شرکت</th>
                                                <th>سقف اعتبار</th>
                                                <th>مانده اعتبار</th>
                                                <th>تاریخ ثبت</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customersTable">
                                            <?php if (empty($customers)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-2x mb-2"></i>
                                                            <p>هیچ مشتری‌ای یافت نشد</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($customers as $customer): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if (!empty($customer['image'])): ?>
                                                                <img src="<?php echo htmlspecialchars($customer['image']); ?>" 
                                                                     alt="تصویر مشتری" class="rounded-circle" width="40" height="40"
                                                                     style="object-fit: cover;">
                                                            <?php else: ?>
                                                                <img src="assets/images/default-user.png" 
                                                                     alt="بدون تصویر" class="rounded-circle" width="40" height="40">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="font-monospace">
                                                            <?php echo htmlspecialchars($customer['code'] ?? '-'); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($customer['name']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $customer['type'] == 'legal' ? 'bg-info' : 'bg-primary'; ?>">
                                                                <?php echo $customer['type'] == 'legal' ? 'حقوقی' : 'حقیقی'; ?>
                                                            </span>
                                                        </td>
                                                        <td dir="ltr" class="text-start">
                                                            <?php echo htmlspecialchars($customer['mobile']); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($customer['company'] ?? '-'); ?>
                                                        </td>
                                                        <td class="text-start">
                                                            <?php if ($customer['credit_limit'] > 0): ?>
                                                                <span class="text-primary">
                                                                    <?php echo number_format($customer['credit_limit'], 0); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">0</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-start">
                                                            <?php if ($customer['credit_balance'] != 0): ?>
                                                                <span class="text-<?php echo $customer['credit_balance'] >= 0 ? 'success' : 'danger'; ?>">
                                                                    <?php echo number_format(abs($customer['credit_balance']), 0); ?>
                                                                    <?php echo $customer['credit_balance'] >= 0 ? '+' : '-'; ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">0</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo jdate('Y/m/d', strtotime($customer['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="customers.php?edit=<?php echo $customer['id']; ?>" 
                                                                   class="btn btn-warning" title="ویرایش">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="customers.php?delete=<?php echo $customer['id']; ?>" 
                                                                   class="btn btn-danger" 
                                                                   onclick="return confirm('آیا از حذف این مشتری اطمینان دارید؟')"
                                                                   title="حذف">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
    $(document).ready(function() {
        let cropper;
        const profileImage = document.getElementById('profileImage');
        const profileImageInput = document.getElementById('profileImageInput');
        const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));
        const cropperImage = document.getElementById('cropperImage');
        const cropButton = document.getElementById('cropButton');

        // کلیک روی تصویر پروفایل
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
    
    // ارسال تصویر به سرور با AJAX
    $.ajax({
        url: 'customers.php',
        method: 'POST',
        data: {
            imageData: imageData
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    // به‌روزرسانی تصویر پروفایل
                    profileImage.src = result.imageUrl;
                    // ذخیره آدرس تصویر در فیلد مخفی
                    document.getElementById('profileImageData').value = result.imageUrl;
                    // بستن مودال
                    cropperModal.hide();
                    // نمایش پیام موفقیت با SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'موفقیت',
                        text: 'تصویر با موفقیت آپلود شد',
                        confirmButtonText: 'تایید'
                    });
                } else {
                    throw new Error(result.message || 'خطا در آپلود تصویر');
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در آپلود تصویر: ' + e.message,
                    confirmButtonText: 'تایید'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: 'خطا در برقراری ارتباط با سرور: ' + error,
                confirmButtonText: 'تایید'
            });
        }
    });
});

        // نمایش/مخفی کردن فیلدهای مربوط به شرکت
        function toggleCompanyFields() {
            if ($('#type').val() === 'legal') {
                $('#company-fields').show();
            } else {
                $('#company-fields').hide();
            }
        }

        // اجرای اولیه
        toggleCompanyFields();

        // تغییر وضعیت نمایش فیلدها با تغییر نوع مشتری
        $('#type').change(toggleCompanyFields);

        // جستجو در جدول
        $('#searchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#customersTable tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // اعتبارسنجی فرم
        $('#customerForm').on('submit', function(e) {
            const mobile = $('#mobile').val();
            const email = $('#email').val();
            const errors = [];

            // بررسی فرمت موبایل
            if (!/^09[0-9]{9}$/.test(mobile)) {
                errors.push('فرمت شماره موبایل صحیح نیست');
            }

            // بررسی فرمت ایمیل
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('فرمت ایمیل صحیح نیست');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
            }
        });

        // پاکسازی cropper در زمان بستن مودال
        $('#cropperModal').on('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });
    });
    </script>
</body>
</html>
                