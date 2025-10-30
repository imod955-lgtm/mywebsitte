<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in'])) {
    die('يجب تسجيل الدخول أولاً');
}

$message = '';

// معالجة نموذج إضافة مستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من صحة البيانات
    if (empty($username) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger">جميع الحقول مطلوبة</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="alert alert-danger">كلمتا المرور غير متطابقتين</div>';
    } else {
        // تشفير كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // بدء المعاملة
        $mysqli->begin_transaction();
        
        try {
            // إضافة المستخدم الجديد
            $query = "INSERT INTO users (username, email, password, full_name, is_active) VALUES (?, ?, ?, ?, 1)";
            $stmt = $mysqli->prepare($query);
            $full_name = $username; // يمكن تغييره لاحقاً
            $stmt->bind_param('ssss', $username, $email, $hashed_password, $full_name);
            $stmt->execute();
            $user_id = $mysqli->insert_id;
            
            // الحصول على معرف دور "مدير النظام"
            $query = "SELECT id FROM roles WHERE name = 'مدير النظام' LIMIT 1";
            $result = $mysqli->query($query);
            $role = $result->fetch_assoc();
            $admin_role_id = $role['id'];
            
            // ربط المستخدم بدور "مدير النظام"
            $query = "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $user_id, $admin_role_id);
            $stmt->execute();
            
            $mysqli->commit();
            $message = '<div class="alert alert-success">تم إنشاء المستخدم بنجاح</div>';
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = '<div class="alert alert-danger">حدث خطأ: ' . $e->getMessage() . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء مدير جديد - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            width: 100%;
        }
        button:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إنشاء مدير جديد</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">إنشاء مستخدم</button>
            </div>
        </form>
        
        <a href="users/" class="back-link">← العودة إلى قائمة المستخدمين</a>
    </div>
</body>
</html>
