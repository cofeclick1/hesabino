<?php
require_once 'includes/init.php';

// پایان دادن به جلسه کاربر
session_unset();
session_destroy();

// هدایت به صفحه ورود
redirect('login.php');