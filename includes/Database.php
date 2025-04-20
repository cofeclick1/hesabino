<?php
class Database {
    private $pdo;
    private $inTransaction = false;

    public function __construct($host, $dbname, $username, $password) {
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci"
                ]
            );
        } catch (PDOException $e) {
            throw new Exception('خطا در اتصال به پایگاه داده: ' . $e->getMessage());
        }
    }

    public function beginTransaction() {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->pdo->beginTransaction();
        }
        return $this->inTransaction;
    }

    public function commit() {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->pdo->commit();
        }
        return false;
    }

    public function rollback() {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->pdo->rollBack();
        }
        return false;
    }

    public function inTransaction() {
        return $this->inTransaction;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('خطا در اجرای کوئری: ' . $e->getMessage());
        }
    }

    public function get($table, $columns = '*', $where = []) {
        $sql = "SELECT " . $columns . " FROM " . $table;
        $params = [];
        
        if (!empty($where)) {
            $whereClauses = [];
            foreach ($where as $key => $value) {
                $whereClauses[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function getAll($table, $columns = '*', $where = []) {
        $sql = "SELECT " . $columns . " FROM " . $table;
        $params = [];
        
        if (!empty($where)) {
            $whereClauses = [];
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $whereClauses[] = "`$key` IN ($placeholders)";
                    $params = array_merge($params, $value);
                } else {
                    $whereClauses[] = "`$key` = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data) {
        $columns = implode('`, `', array_keys($data));
        $values = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO `$table` (`$columns`) VALUES ($values)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('خطا در درج اطلاعات: ' . $e->getMessage());
        }
    }

    public function update($table, $data, $where) {
        $setClauses = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $setClauses[] = "`$key` = " . $value[0];
            } else {
                $setClauses[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        
        $whereClauses = [];
        foreach ($where as $key => $value) {
            $whereClauses[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE `$table` SET " . implode(', ', $setClauses);
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('خطا در بروزرسانی اطلاعات: ' . $e->getMessage());
        }
    }

    public function delete($table, $where) {
        $whereClauses = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $whereClauses[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $whereClauses);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('خطا در حذف اطلاعات: ' . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}