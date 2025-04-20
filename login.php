<?php
require_once 'includes/init.php';

// اگر کاربر قبلاً لاگین کرده است
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    } else {
        if ($auth->login($username, $password)) {
            // ذخیره آخرین زمان ورود
            $db = Database::getInstance();
            $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$_SESSION['user_id']]);
            
            redirect('dashboard.php');
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-box">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo">
                        <h2 class="mt-3">خوش آمدید</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="login-form">
                        <div class="form-group mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="username" class="form-control" placeholder="نام کاربری" required>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control" placeholder="رمز عبور" required>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                ورود به سیستم
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="register.php" class="text-decoration-none">
                                ثبت نام در سیستم
                            </a>
                            <span class="mx-2">|</span>
                            <a href="forgot-password.php" class="text-decoration-none">
                                فراموشی رمز عبور
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>