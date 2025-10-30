<?php
// تفعيل عرض الأخطاء للمساعدة في التصحيح
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../includes/db.php';

// التحقق من وجود معرف المنتج
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    $_SESSION['error'] = 'معرّف المنتج غير صالح';
    header('Location: products.php');
    exit;
}

$product_id = (int)$_POST['product_id'];

try {
    // بدء المعاملة
    $mysqli->begin_transaction();

    // 1. جلب مسار صورة المنتج
    $stmt = $mysqli->prepare("SELECT image FROM products WHERE id = ?");
    if (!$stmt) {
        throw new Exception('فشل في إعداد الاستعلام: ' . $mysqli->error);
    }
    
    $stmt->bind_param('i', $product_id);
    if (!$stmt->execute()) {
        throw new Exception('فشل في تنفيذ الاستعلام: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        // حذف ملف الصورة إذا وجد
        if (!empty($product['image'])) {
            $image_path = __DIR__ . '/../' . $product['image'];
            if (file_exists($image_path)) {
                if (!unlink($image_path)) {
                    throw new Exception('فشل في حذف صورة المنتج');
                }
            }
        }
    }
    
    // 2. حذف المنتج من قاعدة البيانات
    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) {
        throw new Exception('فشل في إعداد استعلام الحذف: ' . $mysqli->error);
    }
    
    $stmt->bind_param('i', $product_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            throw new Exception('لم يتم العثور على المنتج المحدد');
        }
        $mysqli->commit();
        $_SESSION['success'] = 'تم حذف المنتج بنجاح';
    } else {
        throw new Exception('فشل في حذف المنتج');
    }
    
} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = 'حدث خطأ أثناء حذف المنتج: ' . $e->getMessage();
}

// إعادة التوجيه إلى صفحة المنتجات
header('Location: products.php');
exit;
