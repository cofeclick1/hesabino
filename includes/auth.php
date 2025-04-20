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
            $this->db->insert('users', $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function checkRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
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
}