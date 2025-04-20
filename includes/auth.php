<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE username = ?", 
            [$username]
        );
        
        if ($user = $stmt->fetch()) {
            if (password_verify($password, $user['password'])) {
                $this->createSession($user);
                return true;
            }
        }
        return false;
    }
    
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $this->db->beginTransaction();

            // ثبت کاربر
            if ($this->db->insert('users', $data)) {
                $userId = $this->db->lastInsertId();

                // اختصاص نقش پیش‌فرض به کاربر
                $defaultRoleId = $this->db->get('roles', 'id', ['name' => 'user']);
                if ($defaultRoleId) {
                    $this->db->insert('user_roles', [
                        'user_id' => $userId,
                        'role_id' => $defaultRoleId
                    ]);
                }

                $this->db->commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration error: " . $e->getMessage());
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_super_admin'] = (bool)$user['is_super_admin'];
        $_SESSION['last_activity'] = time();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function checkRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
    }

    public function hasPermission($permission) {
        // کاربر سوپر ادمین همه دسترسی‌ها را دارد
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }

        try {
            // بررسی دسترسی‌های کاربر
            $hasPermission = $this->db->query(
                "SELECT COUNT(*) FROM permissions p
                 INNER JOIN role_permissions rp ON p.id = rp.permission_id
                 INNER JOIN user_roles ur ON rp.role_id = ur.role_id
                 WHERE ur.user_id = ? AND p.name = ?",
                [$_SESSION['user_id'], $permission]
            )->fetchColumn();

            return $hasPermission > 0;
        } catch (Exception $e) {
            error_log("Error checking permission: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE id = ?", 
            [$_SESSION['user_id']]
        );
        
        return $stmt->fetch();
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update(
            'users',
            ['password' => $hashedPassword],
            'id = ' . $userId
        );
    }
    
    public function updateUser($userId, $data) {
        return $this->db->update(
            'users',
            $data,
            'id = ' . $userId
        );
    }

    public function check() {
        return $this->isLoggedIn();
    }
}