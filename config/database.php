<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }

    // اضافه کردن متد lastInsertId
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("خطا در اجرای کوئری: " . $e->getMessage());
        }
    }
    
    public function get($table, $columns = '*', $where = [], $params = []) {
        $sql = "SELECT " . $columns . " FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE ";
            if (is_array($where)) {
                $conditions = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "$key = ?";
                    $params[] = $value;
                }
                $sql .= implode(" AND ", $conditions);
            } else {
                $sql .= $where;
            }
        }
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO " . $table . " (" . implode(", ", $fields) . ") 
                VALUES (" . implode(", ", $values) . ")";
        
        try {
            $stmt = $this->query($sql, array_values($data));
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("خطا در درج اطلاعات: " . $e->getMessage());
        }
    }
    
    public function update($table, $data, $where) {
        $fields = array_keys($data);
        $set = [];
        $params = [];
        
        foreach ($fields as $field) {
            $set[] = "$field = ?";
            $params[] = $data[$field];
        }
        
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE " . $table . " SET " . implode(", ", $set) . 
               " WHERE " . implode(" AND ", $conditions);
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("خطا در بروزرسانی اطلاعات: " . $e->getMessage());
        }
    }
    
    public function delete($table, $where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }
        
        $sql = "DELETE FROM " . $table . " WHERE " . implode(" AND ", $conditions);
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("خطا در حذف اطلاعات: " . $e->getMessage());
        }
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollback() {
        return $this->conn->rollBack();
    }
}