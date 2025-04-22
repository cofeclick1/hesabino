<?php
class Validator {
    private $rules = [];
    private $errors = [];
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function addRule($field, $rules, $message = '') {
        $this->rules[$field] = [
            'rules' => explode('|', $rules),
            'message' => $message
        ];
    }

    public function validate($data) {
        $this->errors = [];

        foreach ($this->rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            foreach ($rule['rules'] as $singleRule) {
                $params = [];
                
                // اگر قانون پارامتر داشته باشد
                if (strpos($singleRule, ':') !== false) {
                    list($singleRule, $param) = explode(':', $singleRule, 2);
                    $params = explode(',', $param);
                }

                $method = 'validate' . ucfirst($singleRule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($value, $params, $field)) {
                        $this->errors[$field] = $rule['message'] ?: $this->getDefaultMessage($singleRule, $field);
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return reset($this->errors);
    }

    private function validateRequired($value) {
        return !empty($value);
    }

    private function validateNumeric($value) {
        return is_numeric($value);
    }

    private function validateMin($value, $params) {
        return floatval($value) >= floatval($params[0]);
    }

    private function validateMax($value, $params) {
        return floatval($value) <= floatval($params[0]);
    }

    private function validateEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function validateUnique($value, $params, $field) {
        list($table) = $params;
        $id = $_POST['id'] ?? null;
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?";
        $params = [$value];
        
        if ($id) {
            $sql .= " AND id != ?";
            $params[] = $id;
        }
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] == 0;
    }

    private function getDefaultMessage($rule, $field) {
        $messages = [
            'required' => 'فیلد ' . $field . ' الزامی است',
            'numeric' => 'فیلد ' . $field . ' باید عددی باشد',
            'min' => 'فیلد ' . $field . ' باید بزرگتر یا مساوی با مقدار تعیین شده باشد',
            'max' => 'فیلد ' . $field . ' باید کوچکتر یا مساوی با مقدار تعیین شده باشد',
            'email' => 'فرمت ایمیل نامعتبر است',
            'unique' => 'این ' . $field . ' قبلاً ثبت شده است'
        ];

        return $messages[$rule] ?? 'فیلد ' . $field . ' نامعتبر است';
    }
}