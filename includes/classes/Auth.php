<?php
class Auth {
    private static $instance = null;
    private $db;
    private $userId = null;
    private $userPermissions = [];

    private function __construct() {
        $this->db = Database::getInstance();
        $this->initUser();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initUser() {
        if (isset($_SESSION['user_id'])) {
            $this->userId = $_SESSION['user_id'];
            $this->loadUserPermissions();
        }
    }

    private function loadUserPermissions() {
        try {
            // دریافت نقش‌های کاربر
            $roles = $this->db->query(
                "SELECT r.* FROM roles r
                 INNER JOIN user_roles ur ON r.id = ur.role_id
                 WHERE ur.user_id = ?",
                [$this->userId]
            )->fetchAll();

            // دریافت دسترسی‌های هر نقش
            foreach ($roles as $role) {
                $permissions = $this->db->query(
                    "SELECT p.name FROM permissions p
                     INNER JOIN role_permissions rp ON p.id = rp.permission_id
                     WHERE rp.role_id = ?",
                    [$role['id']]
                )->fetchAll();

                foreach ($permissions as $permission) {
                    $this->userPermissions[] = $permission['name'];
                }
            }

            // حذف دسترسی‌های تکراری
            $this->userPermissions = array_unique($this->userPermissions);
        } catch (Exception $e) {
            // در صورت خطا، لاگ کردن خطا
            error_log("Error loading user permissions: " . $e->getMessage());
        }
    }

    public function hasPermission($permission) {
        // کاربر سوپر ادمین همه دسترسی‌ها را دارد
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }

        return in_array($permission, $this->userPermissions);
    }

    public function check() {
        return $this->userId !== null;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getUserPermissions() {
        return $this->userPermissions;
    }

    public function login($username, $password) {
        try {
            $user = $this->db->get('users', '*', ['username' => $username]);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_super_admin'] = (bool)$user['is_super_admin'];
                
                $this->userId = $user['id'];
                $this->loadUserPermissions();
                
                return true;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
        }
        
        return false;
    }

    public function logout() {
        session_destroy();
        $this->userId = null;
        $this->userPermissions = [];
    }
}