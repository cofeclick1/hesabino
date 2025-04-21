<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

// بررسی دسترسی
if (!$auth->hasPermission('payment.add') && !$_SESSION['is_super_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید']);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // دریافت لیست شرح‌ها
        try {
            $descriptions = $db->query("
                SELECT id, text 
                FROM recurring_descriptions 
                WHERE type = 'payment' 
                AND deleted_at IS NULL
                ORDER BY created_at DESC
            ")->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $descriptions
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات'
            ]);
        }
        break;

    case 'POST':
        // افزودن شرح جدید
        $text = sanitize($_POST['text'] ?? '');
        
        if (empty($text)) {
            echo json_encode(['success' => false, 'message' => 'متن شرح الزامی است']);
            exit;
        }

        try {
            $db->beginTransaction();

            $db->insert('recurring_descriptions', [
                'text' => $text,
                'type' => 'payment',
                'created_by' => $user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $id = $db->lastInsertId();

            $db->commit();

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $id,
                    'text' => $text
                ]
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'خطا در ذخیره اطلاعات'
            ]);
        }
        break;

    case 'DELETE':
        // حذف شرح
        parse_str(file_get_contents('php://input'), $data);
        $id = sanitize($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'شناسه شرح نامعتبر است']);
            exit;
        }

        try {
            $db->beginTransaction();

            $db->update('recurring_descriptions', 
                ['deleted_at' => date('Y-m-d H:i:s')],
                'id = ? AND created_by = ? AND deleted_at IS NULL',
                [$id, $user['id']]
            );

            $db->commit();

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'خطا در حذف اطلاعات'
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}