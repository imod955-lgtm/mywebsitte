<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// إذا كان المستخدم مسجل الدخول بالفعل، قم بتوجيهه إلى لوحة التحكم
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        // البحث عن المستخدم في قاعدة البيانات
        $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // التحقق من كلمة المرور
            if (password_verify($password, $user['password'])) {
                // تسجيل الدخول ناجح
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                
                header('Location: dashboard.php');
                exit;
            }
        }
        
        // إذا وصلنا إلى هنا، فالمصادقة فشلت
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        
    } catch (Exception $e) {
        $error = 'حدث خطأ في النظام. يرجى المحاولة لاحقاً.';
        // يمكنك تسجيل الخطأ في ملف السجل هنا
        // error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 120px;
            height: auto;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .back-to-home {
            display: inline-block;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-to-home:hover {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../images/logo.png" alt="MK Leveling Systems">
        </div>
        
        <h1>تسجيل الدخول إلى لوحة التحكم</h1>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>
        
        <a href="../index.php" class="back-to-home">
            <i class="fas fa-arrow-right"></i> العودة إلى الموقع الرئيسي
        </a>
    </div>
</body>
</html>
