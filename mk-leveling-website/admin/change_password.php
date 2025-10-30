<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in'])) {
    die('يجب تسجيل الدخول أولاً');
}

$message = '';
$user_id = $_SESSION['user_id'] ?? 0;

// معالجة نموذج تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من صحة البيانات
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = '<div class="alert alert-danger">جميع الحقول مطلوبة</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">كلمتا المرور الجديدتين غير متطابقتين</div>';
    } else {
        // التحقق من كلمة المرور الحالية
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // تحديث كلمة المرور
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('si', $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">تم تحديث كلمة المرور بنجاح</div>';
            } else {
                $message = '<div class="alert alert-danger">حدث خطأ أثناء تحديث كلمة المرور</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">كلمة المرور الحالية غير صحيحة</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير كلمة المرور - لوحة التحكم</title>
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
        <h1>تغيير كلمة المرور</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">كلمة المرور الحالية</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">كلمة المرور الجديدة</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">تغيير كلمة المرور</button>
            </div>
        </form>
        
        <a href="dashboard.php" class="back-link">← العودة إلى لوحة التحكم</a>
    </div>
</body>
</html>
