<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('يجب تسجيل الدخول أولاً');
}

echo '<h2>معلومات الجلسة الحالية</h2>';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// الحصول على معلومات المستخدم الحالي
$user_id = $_SESSION['user_id'] ?? 0;
$query = "SELECT u.*, r.name as role_name 
          FROM users u 
          LEFT JOIN user_roles ur ON u.id = ur.user_id 
          LEFT JOIN roles r ON ur.role_id = r.id 
          WHERE u.id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();

echo '<h2>معلومات المستخدم الحالي</h2>';
echo '<pre>';
print_r($current_user);
echo '</pre>';

// الحصول على جميع المستخدمين وصلاحياتهم
$query = "SELECT u.*, GROUP_CONCAT(r.name) as roles 
          FROM users u 
          LEFT JOIN user_roles ur ON u.id = ur.user_id 
          LEFT JOIN roles r ON ur.role_id = r.id 
          GROUP BY u.id";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    echo '<h2>جميع المستخدمين</h2>';
    echo '<table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">';
    echo '<tr><th>ID</th><th>اسم المستخدم</th><th>البريد الإلكتروني</th><th>نشط</th><th>الأدوار</th></tr>';
    
    while ($user = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $user['id'] . '</td>';
        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        echo '<td>' . ($user['is_active'] ? 'نعم' : 'لا') . '</td>';
        echo '<td>' . ($user['roles'] ?: 'لا توجد أدوار') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
} else {
    echo '<p>لا يوجد مستخدمين</p>';
}

// إضافة زر لإصلاح الصلاحيات
if ($current_user && strpos($current_user['role_name'] ?? '', 'مدير') !== false) {
    echo '<div style="margin-top: 20px;">';
    echo '<form method="post" action="fix_permissions.php" style="display: inline-block;">';
    echo '<input type="hidden" name="fix_permissions" value="1">';
    echo '<button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">إصلاح صلاحيات المدير</button>';
    echo '</form>';
    echo '</div>';
}
?>
