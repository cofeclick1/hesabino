    <!-- اسکریپت‌های اصلی -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    
    <!-- تنظیمات پایه برای همه صفحات -->
    <script>
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        const ASSETS_URL = '<?php echo BASE_PATH; ?>/assets';
        
        // نمایش نوتیفیکیشن‌ها
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'موفقیت',
                text: '<?php echo $_SESSION['success']; ?>',
                confirmButtonText: 'تایید'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonText: 'تایید'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
    
    <!-- اسکریپت‌های اختصاصی -->
    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
    <?php if (isset($customJs) && is_array($customJs)): ?>
        <?php foreach ($customJs as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>