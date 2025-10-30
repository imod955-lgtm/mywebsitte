<?php
/**
 * إعدادات اتصال قاعدة البيانات
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');      // كلمة المرور الافتراضية فارغة في XAMPP
define('DB_NAME', 'mkleveling_db');

// إعدادات إضافية
define('DB_CHARSET', 'utf8mb4');

try {
    // إنشاء اتصال جديد بقاعدة البيانات
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // التحقق من وجود أخطاء في الاتصال
    if ($mysqli->connect_errno) {
        throw new Exception("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
    }
    
    // تعيين ترميز الأحرف إلى UTF-8
    if (!$mysqli->set_charset(DB_CHARSET)) {
        throw new Exception("فشل تعيين ترميز الأحرف إلى " . DB_CHARSET . ": " . $mysqli->error);
    }
    
    // تعيين المنطقة الزمنية
    $mysqli->query("SET time_zone = '+03:00'");
    
} catch (Exception $e) {
    // في حالة الخطأ، نستخدم إعدادات افتراضية
    $mysqli = null;
    error_log($e->getMessage());
}
?>
