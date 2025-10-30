<?php
// إعدادات الموقع
define('SITE_NAME', 'MK Leveling Systems');
define('SITE_EMAIL', 'mklevelingsys@gmail.com');
define('ADMIN_EMAIL', 'mklevelingsys@gmail.com');

// المسار الأساسي للموقع
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', rtrim($base_url, '/'));

define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');

// المسار المادي للموقع
define('ROOT_PATH', dirname(__DIR__));

define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mkleveling_db');

// إعدادات اللغة
define('DEFAULT_LANG', 'ar');

// إعدادات الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة الترجمة
function t($ar_text, $en_text) {
    $lang = isset($_SESSION['language']) ? $_SESSION['language'] : DEFAULT_LANG;
    return $lang === 'ar' ? $ar_text : $en_text;
}
?>
