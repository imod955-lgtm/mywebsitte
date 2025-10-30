<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('يجب تسجيل الدخول أولاً');
}

// الحصول على معرف المستخدم الحالي
$user_id = $_SESSION['user_id'] ?? 0;

// التحقق مما إذا كان المستخدم الحالي هو المدير
$is_admin = false;
$query = "SELECT r.name FROM users u 
          JOIN user_roles ur ON u.id = ur.user_id 
          JOIN roles r ON ur.role_id = r.id 
          WHERE u.id = ? AND r.name = 'مدير النظام'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $is_admin = true;
}

if (!$is_admin) {
    die('غير مصرح لك بتنفيذ هذه العملية');
}

// بدء المعاملة
$mysqli->begin_transaction();

try {
    // 1. التأكد من وجود دور "مدير النظام"
    $query = "SELECT id FROM roles WHERE name = 'مدير النظام' LIMIT 1";
    $result = $mysqli->query($query);
    
    if ($result->num_rows === 0) {
        // إنشاء دور "مدير النظام" إذا لم يكن موجوداً
        $query = "INSERT INTO roles (name, description) VALUES ('مدير النظام', 'لديه صلاحيات كاملة على النظام')";
        $mysqli->query($query);
        $admin_role_id = $mysqli->insert_id;
        echo "<p>تم إنشاء دور 'مدير النظام' بنجاح</p>";
    } else {
        $row = $result->fetch_assoc();
        $admin_role_id = $row['id'];
    }
    
    // 2. ربط المستخدم الحالي بدور "مدير النظام"
    $query = "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $user_id, $admin_role_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<p>تم ربط حسابك بدور 'مدير النظام' بنجاح</p>";
    } else {
        echo "<p>حسابك مرتبط بالفعل بدور 'مدير النظام'</p>";
    }
    
    // 3. التأكد من وجود جميع الصلاحيات الأساسية
    $permissions = [
        ['manage_users', 'إدارة المستخدمين', 'يمكنه إدارة المستخدمين'],
        ['manage_products', 'إدارة المنتجات', 'يمكنه إدارة المنتجات'],
        ['manage_orders', 'إدارة الطلبات', 'يمكنه إدارة الطلبات'],
        ['manage_settings', 'إدارة الإعدادات', 'يمكنه تعديل إعدادات الموقع'],
        ['view_reports', 'عرض التقارير', 'يمكنه عرض التقارير']
    ];
    
    foreach ($permissions as $perm) {
        $query = "INSERT IGNORE INTO permissions (permission_key, name, description) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sss', $perm[0], $perm[1], $perm[2]);
        $stmt->execute();
    }
    
    // 4. ربط جميع الصلاحيات بدور "مدير النظام"
    $query = "INSERT IGNORE INTO role_permissions (role_id, permission_id) 
              SELECT ?, id FROM permissions";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $admin_role_id);
    $stmt->execute();
    
    // تأكيد المعاملة
    $mysqli->commit();
    
    echo "<div style='background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #2e7d32;'>تم تحديث الصلاحيات بنجاح!</h3>";
    echo "<p>تم منحك جميع الصلاحيات بنجاح.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    // التراجع عن التغييرات في حالة حدوث خطأ
    $mysqli->rollback();
    echo "<div style='background-color: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #c62828;'>حدث خطأ أثناء تحديث الصلاحيات</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// إضافة زر للعودة
echo "<div style='margin-top: 20px;'>";
echo "<a href='users/' style='background-color: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>";
echo "الذهاب إلى إدارة المستخدمين";
echo "</a>";
echo "</div>";
?>
