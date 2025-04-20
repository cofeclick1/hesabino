<?php
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - سیستم حسابداری هوشمند</title>
    
    <!-- فونت‌ها و استایل‌ها -->
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- نوار ناوبری -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
                <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">امکانات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#screenshots">تصاویر</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">تماس با ما</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-primary me-2">ورود</a>
                    <a href="register.php" class="btn btn-primary">ثبت نام</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- بخش هدر -->
    <header class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-up">
                    <h1 class="display-4 fw-bold mb-4">سیستم حسابداری هوشمند پاره سنگ</h1>
                    <p class="lead mb-4">مدیریت هوشمند کسب و کار شما با پیشرفته‌ترین ابزارهای حسابداری و مدیریت موجودی</p>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            شروع رایگان
                            <i class="fas fa-arrow-left ms-2"></i>
                        </a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">
                            امکانات
                            <i class="fas fa-list ms-2"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="assets/images/dashboard-preview.png" alt="داشبورد" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </header>
    
    <!-- بخش ویژگی‌ها -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">امکانات سیستم</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3>مدیریت محصولات</h3>
                        <p>مدیریت کامل محصولات، دسته‌بندی و موجودی انبار با گزارش‌های پیشرفته</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>فروش سریع</h3>
                        <p>صدور فاکتور فروش به صورت سریع و آسان با امکان چاپ و خروجی PDF</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>گزارش‌گیری پیشرفته</h3>
                        <p>انواع گزارش‌های مالی و آماری با نمودارهای تحلیلی و قابلیت خروجی Excel</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- بخش تصاویر -->
    <section id="screenshots" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">تصاویر محیط نرم‌افزار</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="screenshot-card">
                        <img src="assets/images/screenshot-1.jpg" alt="تصویر داشبورد" class="img-fluid rounded-4">
                        <div class="overlay">
                            <h4>داشبورد مدیریتی</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="screenshot-card">
                        <img src="assets/images/screenshot-2.jpg" alt="مدیریت محصولات" class="img-fluid rounded-4">
                        <div class="overlay">
                            <h4>مدیریت محصولات</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="screenshot-card">
                        <img src="assets/images/screenshot-3.jpg" alt="گزارش‌گیری" class="img-fluid rounded-4">
                        <div class="overlay">
                            <h4>گزارش‌گیری</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- بخش تماس -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-up">
                    <h2 class="mb-4">تماس با ما</h2>
                    <p class="mb-4">برای کسب اطلاعات بیشتر و یا درخواست پشتیبانی با ما در تماس باشید.</p>
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt fa-fw me-3"></i>
                            <div>
                                تهران، خیابان ولیعصر، پلاک 123
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone fa-fw me-3"></i>
                            <div>
                                021-12345678
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope fa-fw me-3"></i>
                            <div>
                                info@example.com
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="map-container rounded-4 shadow-sm">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d207371.97277209726!2d51.21971799999999!3d35.6891975!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3f8e[...]
                            width="100%"
                            height="400"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- فوتر -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
                    <span class="ms-2"><?php echo SITE_NAME; ?></span>
                </div>
                <div class="col-md-4 text-center my-3 my-md-0">
                    <div class="social-links">
                        <a href="#" class="mx-2"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="mx-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="mx-2"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <p class="mb-0">تمامی حقوق محفوظ است &copy; <?php echo date('Y'); ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- اسکریپت‌ها -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // راه‌اندازی کتابخانه AOS برای انیمیشن‌ها
        AOS.init({
            duration: 800,
            once: true
        });
    
        // تغییر استایل نوار ناوبری هنگام اسکرول
        $(window).scroll(function() {
            if ($(window).scrollTop() > 50) {
                $('.navbar').addClass('navbar-scrolled');
            } else {
                $('.navbar').removeClass('navbar-scrolled');
            }
        });
    
        // اسکرول نرم به بخش‌های مختلف
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this.hash);
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });
    </script>
</body>
</html>