<?php
require_once '../includes/init.php';

try {
    $db->query("DELETE FROM customers WHERE 1");
    echo "لیست مشتریان با موفقیت خالی شد.";
} catch (Exception $e) {
    echo "خطا در پاکسازی لیست مشتریان: " . $e->getMessage();
}