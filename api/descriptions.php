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
        try {
            $descriptions = $db->query("
                SELECT id, text 
                FROM recurring_descriptions 
                WHERE type = ? 
                AND deleted_at IS NULL
                ORDER BY created_at DESC
            ", ['payment'])->fetchAll();

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
                ],
                'message' => 'شرح با موفقیت ذخیره شد'
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

    case 'PUT':
        parse_str(file_get_contents('php://input'), $data);
        $id = sanitize($data['id'] ?? 0);
        $text = sanitize($data['text'] ?? '');

        if (!$id || !$text) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات ناقص است']);
            exit;
        }

        try {
            $db->beginTransaction();

            $db->update('recurring_descriptions', 
                ['text' => $text, 'updated_by' => $user['id'], 'updated_at' => date('Y-m-d H:i:s')],
                'id = ? AND created_by = ? AND deleted_at IS NULL',
                [$id, $user['id']]
            );

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'شرح با موفقیت به‌روزرسانی شد'
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی اطلاعات'
            ]);
        }
        break;

    case 'DELETE':
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

            echo json_encode([
                'success' => true,
                'message' => 'شرح با موفقیت حذف شد'
            ]);

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