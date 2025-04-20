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
            error_log("Error loading user permissions: " . $e->getMessage());
        }
    }
    
    public function login($username, $password) {
        try {
            $user = $this->db->get('users', '*', ['username' => $username]);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->createSession($user);
                $this->userId = $user['id'];
                $this->loadUserPermissions();
                return true;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
        }
        return false;
    }
    
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $this->db->beginTransaction();

            // ثبت کاربر
            $userId = $this->db->insert('users', $data);
            if (!$userId) {
                throw new Exception("خطا در ثبت کاربر");
            }

            // اختصاص نقش پیش‌فرض به کاربر
            $defaultRoleId = $this->db->get('roles', 'id', ['name' => 'user']);
            if ($defaultRoleId) {
                $this->db->insert('user_roles', [
                    'user_id' => $userId,
                    'role_id' => $defaultRoleId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        session_start();
        $this->userId = null;
        $this->userPermissions = [];
    }
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_super_admin'] = (bool)$user['is_super_admin'];
        $_SESSION['last_activity'] = time();
    }
    
    public function check() {
        return $this->userId !== null;
    }
    
    public function isLoggedIn() {
        return $this->check();
    }
    
    public function checkRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
    }

    public function hasPermission($permission) {
        // کاربر سوپر ادمین همه دسترسی‌ها را دارد
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }

        return in_array($permission, $this->userPermissions);
    }

    public function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            return $this->db->get('users', '*', ['id' => $this->userId]);
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }

    public function getUserPermissions() {
        return $this->userPermissions;
    }

    public function getUserRoles() {
        try {
            return $this->db->query(
                "SELECT r.* FROM roles r
                 INNER JOIN user_roles ur ON r.id = ur.role_id
                 WHERE ur.user_id = ?",
                [$this->userId]
            )->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user roles: " . $e->getMessage());
            return [];
        }
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            return $this->db->update(
                'users',
                ['password' => $hashedPassword],
                ['id' => $userId]
            );
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUser($userId, $data) {
        try {
            // حذف فیلدهای حساس از آرایه داده
            unset($data['password']);
            unset($data['is_super_admin']);
            
            return $this->db->update(
                'users',
                $data,
                ['id' => $userId]
            );
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function addUserToRole($userId, $roleId) {
        try {
            return $this->db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error adding user to role: " . $e->getMessage());
            return false;
        }
    }

    public function removeUserFromRole($userId, $roleId) {
        try {
            return $this->db->delete('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        } catch (Exception $e) {
            error_log("Error removing user from role: " . $e->getMessage());
            return false;
        }
    }
}