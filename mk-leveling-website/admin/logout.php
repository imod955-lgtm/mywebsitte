<?php
// بدء الجلسة
session_start();

// حذف جميع متغيرات الجلسة
$_SESSION = array();

// إذا كنت تريد إنهاء الجلسة بالكامل، قم بإلغاء ملف تعريف الارتباط
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header("Location: login.php");
exit;
?>
