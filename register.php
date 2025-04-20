<?php
require_once 'includes/init.php';

// اگر کاربر قبلاً لاگین کرده است
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$auth = new Auth();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    
    // اعتبارسنجی داده‌ها
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'لطفاً تمام فیلدهای ضروری را پر کنید.';
    } elseif ($password !== $confirm_password) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند.';
    } elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل 6 کاراکتر باشد.';
    } else {
        // بررسی تکراری نبودن نام کاربری
        $stmt = $db->query("SELECT id FROM users WHERE username = ?", [$username]);
        if ($stmt->rowCount() > 0) {
            $error = 'این نام کاربری قبلاً ثبت شده است.';
        } else {
            // بررسی تکراری نبودن ایمیل
            if (!empty($email)) {
                $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
                if ($stmt->rowCount() > 0) {
                    $error = 'این ایمیل قبلاً ثبت شده است.';
                }
            }
            
            if (empty($error)) {
                $userData = [
                    'username' => $username,
                    'password' => $password,
                    'full_name' => $full_name,
                    'email' => $email,
                    'role' => 'user'
                ];
                
                if ($auth->register($userData)) {
                    $success = 'ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.';
                } else {
                    $error = 'خطا در ثبت اطلاعات. لطفاً دوباره تلاش کنید.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت نام - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="login-box">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo">
                        <h2 class="mt-3">ثبت نام در سیستم</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <br>
                            <a href="login.php" class="alert-link">ورود به سیستم</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="login-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="username">نام کاربری <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" id="username" name="username" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="full_name">نام و نام خانوادگی <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user-circle"></i>
                                            </span>
                                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email">ایمیل</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" id="email" name="email" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="password">رمز عبور <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" id="password" name="password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="confirm_password">تکرار رمز عبور <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>
                                    ثبت نام
                                </button>
                            </div>
                            
                            <div class="text-center">
                                قبلاً ثبت نام کرده‌اید؟
                                <a href="login.php" class="text-decoration-none">
                                    ورود به سیستم
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // اعتبارسنجی فرم در سمت کلاینت
        $('form').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (password.length < 6) {
                e.preventDefault();
                alert('رمز عبور باید حداقل 6 کاراکتر باشد.');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('رمز عبور و تکرار آن یکسان نیستند.');
                return false;
            }
        });
    });
    </script>
</body>
</html>