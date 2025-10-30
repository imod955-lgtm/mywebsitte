<?php
// الاتصال بقاعدة البيانات
require_once __DIR__ . '/../includes/db.php';

if (isset($mysqli)) {
    // إنشاء جدول الرسائل إذا لم يكن موجوداً
    $sql = "CREATE TABLE IF NOT EXISTS `messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `subject` varchar(255) DEFAULT NULL,
        `message` text NOT NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($mysqli->query($sql) === TRUE) {
        echo "تم إنشاء جدول الرسائل بنجاح<br>";
    } else {
        echo "خطأ في إنشاء الجدول: " . $mysqli->error . "<br>";
    }

    // إضافة بعض البيانات التجريبية
    $test_messages = [
        ['أحمد محمد', 'ahmed@example.com', 'استفسار عن الخدمات', 'مرحباً، أود الاستفسار عن الخدمات المتاحة لديكم.', 0],
        ['سارة أحمد', 'sara@example.com', 'طلب تواصل', 'أرغب في التواصل مع المسؤول عن الموقع.', 0],
        ['محمد علي', 'mohamed@example.com', 'شكراً لكم', 'شكراً على الخدمة المميزة التي تقدمونها.', 1]
    ];

    foreach ($test_messages as $msg) {
        $stmt = $mysqli->prepare("INSERT INTO messages (name, email, subject, message, is_read) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $msg[0], $msg[1], $msg[2], $msg[3], $msg[4]);
        if ($stmt->execute()) {
            echo "تمت إضافة رسالة تجريبية: " . $msg[0] . "<br>";
        } else {
            echo "خطأ في إضافة الرسالة: " . $stmt->error . "<br>";
        }
    }

    // إغلاق الاتصال
    $mysqli->close();
} else {
    echo "فشل الاتصال بقاعدة البيانات";
}

echo "<p>تم الانتهاء من إنشاء الجداول. <a href='dashboard.php'>العودة للوحة التحكم</a></p>";
?>
