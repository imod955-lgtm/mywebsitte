<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo 'غير مصرح بالوصول';
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'طريقة الطلب غير مسموح بها';
    exit;
}

// Get user ID from POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

// Prevent deleting the main admin account
if ($user_id === 1) {
    $_SESSION['error_message'] = 'لا يمكن حذف حساب المدير الرئيسي';
    header('Location: index.php');
    exit;
}

// Check if user exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'المستخدم غير موجود';
    header('Location: index.php');
    exit;
}

try {
    // Start transaction
    $mysqli->begin_transaction();
    
    // Delete user roles
    $stmt = $mysqli->prepare("DELETE FROM user_roles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Delete user
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    
    $_SESSION['success_message'] = 'تم حذف المستخدم بنجاح';
    
} catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $_SESSION['error_message'] = 'حدث خطأ أثناء حذف المستخدم: ' . $e->getMessage();
}

// Redirect back to users list
header('Location: index.php');
exit;
