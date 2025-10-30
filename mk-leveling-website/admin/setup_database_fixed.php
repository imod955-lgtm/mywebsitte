<?php
// إعدادات الاتصال بقاعدة البيانات
$db_host = 'localhost';
$db_user = 'root';     // اسم المستخدم الافتراضي لـ XAMPP
$db_pass = '';         // كلمة المرور الافتراضية لـ XAMPP (فارغة)
$db_name = 'mkleveling_db'; // تأكد من أن اسم قاعدة البيانات صحيح

// إنشاء اتصال
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// التحقق من الاتصال
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// تعيين ترميز الأحرف
$mysqli->set_charset("utf8mb4");

echo "<h2>بدء إعداد قاعدة البيانات</h2>";

// تعطيل فحص المفاتيح الأجنبية مؤقتاً
$mysqli->query('SET FOREIGN_KEY_CHECKS=0');
echo "<p>تم تعطيل فحص المفاتيح الأجنبية</p>";

// حذف الجداول إذا كانت موجودة
$tables = [
    'user_roles', 
    'role_permissions', 
    'users', 
    'roles', 
    'permissions'
];

foreach ($tables as $table) {
    if ($mysqli->query("DROP TABLE IF EXISTS `$table`")) {
        echo "<p>تم حذف الجدول $table إذا كان موجوداً</p>";
    } else {
        echo "<p style='color:red;'>خطأ في حذف الجدول $table: " . $mysqli->error . "</p>";
    }
}

// تمكين فحص المفاتيح الأجنبية
$mysqli->query('SET FOREIGN_KEY_CHECKS=1');
echo "<p>تم تفعيل فحص المفاتيح الأجنبية</p>";

// قراءة ملف SQL
$sql_file = dirname(__DIR__) . '/database/create_user_tables.sql';
if (!file_exists($sql_file)) {
    die("<p style='color:red;'>خطأ: ملف SQL غير موجود في المسار: $sql_file</p>");
}

// تنفيذ استعلامات SQL
$sql = file_get_contents($sql_file);

if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    echo "<p style='color:green;'>تم تنفيذ استعلامات SQL بنجاح</p>";
} else {
    echo "<p style='color:red;'>خطأ في تنفيذ استعلامات SQL: " . $mysqli->error . "</p>";
}

// التحقق من إنشاء الجداول
$tables_created = [];
$result = $mysqli->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables_created[] = $row[0];
    }
    $result->free();
}

echo "<h3>الجداول الموجودة في قاعدة البيانات:</h3>";
echo "<ul>";
foreach ($tables_created as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";

// التحقق من وجود المستخدم الافتراضي
$result = $mysqli->query("SELECT * FROM users WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<div style='background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>تم إنشاء المستخدم الافتراضي بنجاح</h3>";
    echo "<p><strong>اسم المستخدم:</strong> admin</p>";
    echo "<p><strong>كلمة المرور:</strong> admin123</p>";
    echo "<p><strong>البريد الإلكتروني:</strong> " . htmlspecialchars($user['email']) . "</p>";
    echo "</div>";
} else {
    echo "<p style='color:red;'>تحذير: لم يتم العثور على المستخدم الافتراضي</p>";
}

// إضافة زر للعودة إلى لوحة التحكم
echo "<div style='margin-top: 30px;'>";
echo "<a href='../admin/' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>";
echo "الذهاب إلى لوحة التحكم";
echo "</a>";
echo "</div>";

// إغلاق الاتصال
$mysqli->close();
?>
