<?php
class FileUploader {
    private $config;
    private $errors = [];

    public function __construct($config = []) {
        $this->config = array_merge([
            'upload_dir' => '../uploads/',
            'allowed_types' => [],
            'max_size' => 5242880, // 5MB
            'create_directory' => true
        ], $config);
    }

    public function upload($file) {
        if (!is_array($file)) {
            $this->errors[] = 'فایل آپلود شده نامعتبر است';
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        if (!empty($this->config['allowed_types']) && !in_array($file['type'], $this->config['allowed_types'])) {
            $this->errors[] = 'نوع فایل مجاز نیست';
            return false;
        }

        if ($file['size'] > $this->config['max_size']) {
            $this->errors[] = 'حجم فایل بیشتر از حد مجاز است';
            return false;
        }

        // ایجاد دایرکتوری در صورت نیاز
        if ($this->config['create_directory'] && !is_dir($this->config['upload_dir'])) {
            if (!mkdir($this->config['upload_dir'], 0755, true)) {
                $this->errors[] = 'خطا در ایجاد مسیر آپلود';
                return false;
            }
        }

        // تولید نام یکتا برای فایل
        $filename = $this->generateUniqueFilename($file['name']);
        $filepath = $this->config['upload_dir'] . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->errors[] = 'خطا در آپلود فایل';
            return false;
        }

        return $filename;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = preg_replace("/[^a-zA-Z0-9]/", "-", $filename);
        $filename = strtolower($filename);
        return time() . '-' . $filename . '.' . $extension;
    }

    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'حجم فایل بیشتر از حد مجاز است';
            case UPLOAD_ERR_PARTIAL:
                return 'فایل به صورت ناقص آپلود شد';
            case UPLOAD_ERR_NO_FILE:
                return 'فایلی برای آپلود انتخاب نشده است';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'پوشه موقت برای آپلود وجود ندارد';
            case UPLOAD_ERR_CANT_WRITE:
                return 'خطا در نوشتن فایل';
            case UPLOAD_ERR_EXTENSION:
                return 'آپلود فایل متوقف شد';
            default:
                return 'خطای ناشناخته در آپلود فایل';
        }
    }
}