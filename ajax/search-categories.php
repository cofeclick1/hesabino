<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance();

$search = sanitize($_GET['q'] ?? '');
$categories = $db->query("SELECT id, name FROM categories WHERE name LIKE ? ORDER BY name", ["%{$search}%"])->fetchAll();

echo json_encode($categories);