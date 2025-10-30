<?php
// بدء الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /mk-leveling-website/admin/login.php');
    exit();
}

// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/includes/db.php';

// معالجة تحديث البيانات
$success = '';
$error = '';

// جلب بيانات المستخدم الحالي
$user_id = $_SESSION['user_id'];
$user = null;
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // التحقق من صحة البيانات
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'اسم المستخدم مطلوب';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح';
    }
    
    if (empty($full_name)) {
        $errors[] = 'الاسم الكامل مطلوب';
    }
    
    // التحقق من كلمة المرور إذا تم إدخالها
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = 'يجب إدخال كلمة المرور الحالية';
        } elseif (empty($new_password)) {
            $errors[] = 'يجب إدخال كلمة المرور الجديدة';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'يجب أن تكون كلمة المرور 6 أحرف على الأقل';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'كلمة المرور الجديدة غير متطابقة';
        } else {
            // التحقق من صحة كلمة المرور الحالية
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = 'كلمة المرور الحالية غير صحيحة';
            }
        }
    }
    
    // إذا لم تكن هناك أخطاء، قم بتحديث البيانات
    if (empty($errors)) {
        try {
            $mysqli->begin_transaction();
            
            // تحديث البيانات الأساسية
            $query = "UPDATE users SET username = ?, email = ?, full_name = ?";
            $types = "sss";
            $params = [$username, $email, $full_name];
            
            // تحديث كلمة المرور إذا تم إدخالها
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query .= ", password = ?";
                $types .= "s";
                $params[] = $hashed_password;
            }
            
            $query .= " WHERE id = ?";
            $types .= "i";
            $params[] = $user_id;
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            // تحديث بيانات الجلسة
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_email'] = $email;
            
            $mysqli->commit();
            $success = 'تم تحديث الملف الشخصي بنجاح';
            
            // إعادة تحميل بيانات المستخدم
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
            }
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = 'حدث خطأ أثناء تحديث البيانات: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .profile-card {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .form-group.required label:after {
            content: ' *';
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1><i class="fas fa-user-cog"></i> الملف الشخصي</h1>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($user['full_name'] ?? 'المستخدم'); ?></h3>
                        <p class="mb-0"><?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group required">
                                        <label for="username" class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control ltr-input" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required
                                               dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group required">
                                        <label for="email" class="form-label">البريد الإلكتروني</label>
                                        <input type="email" class="form-control ltr-input" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                                               dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-group required">
                                        <label for="full_name" class="form-label">الاسم الكامل</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required
                                               dir="auto" style="text-align: right; font-family: 'Tajawal', sans-serif;">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">تغيير كلمة المرور</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                                        <input type="password" class="form-control ltr-input" id="current_password" name="current_password"
                                               placeholder="أدخل كلمة المرور الحالية"
                                               dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                                        <input type="password" class="form-control ltr-input" id="new_password" name="new_password"
                                               placeholder="أدخل كلمة المرور الجديدة"
                                               dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                        <small class="form-text text-muted">اتركه فارغاً إذا لم ترغب في تغيير كلمة المرور</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور</label>
                                        <input type="password" class="form-control ltr-input" id="confirm_password" name="confirm_password"
                                               placeholder="أعد إدخال كلمة المرور الجديدة"
                                               dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحقق من تطابق كلمتي المرور
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('كلمة المرور غير متطابقة!');
                return false;
            }
            
            if (newPassword.length > 0 && newPassword.length < 6) {
                e.preventDefault();
                alert('يجب أن تكون كلمة المرور 6 أحرف على الأقل');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
