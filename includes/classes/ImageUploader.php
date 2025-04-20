<?php
class ImageUploader {
    private $file;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxSize = 2097152; // 2MB
    private $path = '../assets/images/profiles/';
    private $width = 300;
    private $height = 300;
    private $quality = 80;
    private $error = '';

    public function __construct($file) {
        $this->file = $file;
    }

    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
    }

    public function setMaxSize($size) {
        $this->maxSize = $size;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setDimensions($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function setQuality($quality) {
        $this->quality = $quality;
    }

    public function getError() {
        return $this->error;
    }

    public function validate() {
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->error = 'خطا در آپلود فایل';
            return false;
        }

        if (!in_array($this->file['type'], $this->allowedTypes)) {
            $this->error = 'فرمت فایل مجاز نیست';
            return false;
        }

        if ($this->file['size'] > $this->maxSize) {
            $this->error = 'حجم فایل بیشتر از حد مجاز است';
            return false;
        }

        return true;
    }

    public function upload() {
        if (!$this->validate()) {
            return false;
        }

        // ساخت نام یکتا برای فایل
        $extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $this->path . $filename;

        try {
            // ایجاد مسیر در صورت عدم وجود
            if (!file_exists($this->path)) {
                mkdir($this->path, 0755, true);
            }

            // خواندن تصویر اصلی
            switch ($this->file['type']) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($this->file['tmp_name']);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($this->file['tmp_name']);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($this->file['tmp_name']);
                    break;
                default:
                    throw new Exception('فرمت تصویر پشتیبانی نمی‌شود');
            }

            // محاسبه ابعاد جدید با حفظ تناسب
            $width = imagesx($source);
            $height = imagesy($source);
            $ratio = $width / $height;

            if ($ratio > 1) {
                $newWidth = $this->width;
                $newHeight = $this->width / $ratio;
            } else {
                $newHeight = $this->height;
                $newWidth = $this->height * $ratio;
            }

            // ایجاد تصویر جدید
            $thumb = imagecreatetruecolor($newWidth, $newHeight);

            // حفظ شفافیت برای PNG
            if ($this->file['type'] == 'image/png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            }

            // تغییر سایز تصویر
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // ذخیره تصویر
            switch ($this->file['type']) {
                case 'image/jpeg':
                    imagejpeg($thumb, $uploadPath, $this->quality);
                    break;
                case 'image/png':
                    imagepng($thumb, $uploadPath, 9);
                    break;
                case 'image/webp':
                    imagewebp($thumb, $uploadPath, $this->quality);
                    break;
            }

            // آزادسازی حافظه
            imagedestroy($source);
            imagedestroy($thumb);

            return str_replace('../', '', $uploadPath);
        } catch (Exception $e) {
            $this->error = 'خطا در پردازش تصویر: ' . $e->getMessage();
            return false;
        }
    }
}