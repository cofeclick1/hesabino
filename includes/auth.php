<?php
class Auth {
    private $db;
    private $permissions = null;
    
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
                
                // بروزرسانی آخرین ورود
                $this->db->update(
                    'users',
                    ['last_login' => date('Y-m-d H:i:s')],
                    ['id' => $user['id']]
                );
                
                return true;
            }
        }
        return false;
    }
    
    public function register($data) {
        // بررسی تکراری نبودن نام کاربری و ایمیل
        $existingUser = $this->db->get('users', 'id', ['username' => $data['username']]);
        if ($existingUser) {
            throw new Exception('این نام کاربری قبلاً ثبت شده است');
        }
        
        if (!empty($data['email'])) {
            $existingEmail = $this->db->get('users', 'id', ['email' => $data['email']]);
            if ($existingEmail) {
                throw new Exception('این ایمیل قبلاً ثبت شده است');
            }
        }
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $this->db->beginTransaction();
            
            // درج کاربر جدید
            $userId = $this->db->insert('users', [
                'username' => $data['username'],
                'password' => $data['password'],
                'email' => $data['email'] ?? null,
                'full_name' => $data['full_name'] ?? null,
                'role' => 'user',
                'status' => 'active',
                'created_at' => $data['created_at']
            ]);
            
            if ($userId) {
                // اختصاص نقش پیش‌فرض به کاربر
                $defaultRole = $this->db->get('roles', '*', ['name' => 'user']);
                if ($defaultRole) {
                    $this->db->insert('user_roles', [
                        'user_id' => $userId,
                        'role_id' => $defaultRole['id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // اختصاص دسترسی‌های پیش‌فرض
                    $defaultPermissions = [
                        'dashboard_view',
                        'profile_view',
                        'profile_edit'
                    ];
                    
                    foreach ($defaultPermissions as $permName) {
                        $perm = $this->db->get('permissions', '*', ['name' => $permName]);
                        if ($perm) {
                            $this->db->insert('role_permissions', [
                                'role_id' => $defaultRole['id'],
                                'permission_id' => $perm['id']
                            ]);
                        }
                    }
                }
                
                $this->db->commit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
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
        
        // بارگذاری دسترسی‌ها
        $this->loadPermissions();
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // بررسی timeout نشست
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function checkRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
    }

    public function hasPermission($permission) {
        // اگر کاربر سوپر ادمین است
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }

        // اگر دسترسی‌ها هنوز بارگذاری نشده‌اند
        if ($this->permissions === null) {
            $this->loadPermissions();
        }

        return in_array($permission, $this->permissions);
    }

    private function loadPermissions() {
        if (!$this->isLoggedIn()) {
            $this->permissions = [];
            return;
        }

        try {
            // بارگذاری همه دسترسی‌های کاربر
            $stmt = $this->db->query(
                "SELECT DISTINCT p.name 
                FROM permissions p 
                INNER JOIN role_permissions rp ON p.id = rp.permission_id 
                INNER JOIN user_roles ur ON rp.role_id = ur.role_id 
                WHERE ur.user_id = ?",
                [$_SESSION['user_id']]
            );
            
            $this->permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Error loading permissions: " . $e->getMessage());
            $this->permissions = [];
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->get('users', '*', ['id' => $_SESSION['user_id']]);
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update(
            'users',
            ['password' => $hashedPassword],
            ['id' => $userId]
        );
    }
    
    public function updateUser($userId, $data) {
        return $this->db->update('users', $data, ['id' => $userId]);
    }

    public function check() {
        return $this->isLoggedIn();
    }
}