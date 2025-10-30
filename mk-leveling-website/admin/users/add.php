<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$errors = [];
$success = '';

// Get all roles
$roles = [];
$result = $mysqli->query("SELECT * FROM roles");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Get all permissions
$permissions = [];
$result = $mysqli->query("SELECT * FROM permissions ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $selected_roles = $_POST['roles'] ?? [];
    $selected_permissions = $_POST['permissions'] ?? [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = 'اسم المستخدم مطلوب';
    }
    
    if (empty($password)) {
        $errors[] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors[] = 'يجب أن تكون كلمة المرور 6 أحرف على الأقل';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'كلمة المرور غير متطابقة';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح';
    }
    
    if (empty($full_name)) {
        $errors[] = 'الاسم الكامل مطلوب';
    }
    
    // Check if username or email already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
    }
    
    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // Insert user
            $stmt = $mysqli->prepare("INSERT INTO users (username, password, email, full_name, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $hashed_password, $email, $full_name, $is_active);
            $stmt->execute();
            $user_id = $mysqli->insert_id;
            
            // Assign roles
            if (!empty($selected_roles)) {
                $stmt = $mysqli->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                foreach ($selected_roles as $role_id) {
                    $role_id = (int)$role_id;
                    $stmt->bind_param("ii", $user_id, $role_id);
                    $stmt->execute();
                }
            }
            
            // Assign direct permissions (if any)
            if (!empty($selected_permissions)) {
                // This is a simplified example - in a real app, you might have a user_permissions table
                // For now, we'll just store them in a JSON field or similar
                $permissions_json = json_encode($selected_permissions);
                $stmt = $mysqli->prepare("UPDATE users SET permissions = ? WHERE id = ?");
                $stmt->bind_param("si", $permissions_json, $user_id);
                $stmt->execute();
            }
            
            $mysqli->commit();
            
            $_SESSION['success_message'] = 'تمت إضافة المستخدم بنجاح';
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = 'حدث خطأ أثناء إضافة المستخدم: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستخدم جديد - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/users.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <?php include '../includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1>إضافة مستخدم جديد</h1>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-right"></i> رجوع
                    </a>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" id="userForm">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="username">اسم المستخدم *</label>
                                    <input type="text" id="username" name="username" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                           dir="auto" style="text-align: right; font-family: 'Tajawal', sans-serif;">
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="email">البريد الإلكتروني *</label>
                                    <input type="email" id="email" name="email" class="form-control ltr-input" required 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                           dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="full_name">الاسم الكامل *</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                           dir="auto" style="text-align: right; font-family: 'Tajawal', sans-serif;">
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="password">كلمة المرور *</label>
                                    <input type="password" id="password" name="password" class="form-control ltr-input" required
                                           dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="confirm_password">تأكيد كلمة المرور *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control ltr-input" required
                                           dir="ltr" style="text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    <small id="passwordHelp" class="form-text text-muted">يجب أن تكون كلمة المرور 6 أحرف على الأقل</small>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
                                        <label for="is_active" class="form-check-label">الحساب نشط</label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <h5>الأدوار</h5>
                                    <div class="row">
                                        <?php foreach ($roles as $role): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" id="role_<?php echo $role['id']; ?>" 
                                                           name="roles[]" value="<?php echo $role['id']; ?>" 
                                                           class="form-check-input role-checkbox"
                                                           data-role-id="<?php echo $role['id']; ?>">
                                                    <label for="role_<?php echo $role['id']; ?>" class="form-check-label">
                                                        <?php echo htmlspecialchars($role['name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <h5>الصلاحيات المباشرة</h5>
                                    <div class="row">
                                        <?php foreach ($permissions as $permission): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" id="perm_<?php echo $permission['id']; ?>" 
                                                           name="permissions[]" value="<?php echo $permission['id']; ?>" 
                                                           class="form-check-input permission-checkbox">
                                                    <label for="perm_<?php echo $permission['id']; ?>" class="form-check-label">
                                                        <?php echo htmlspecialchars($permission['name']); ?>
                                                        <small class="text-muted d-block"><?php echo htmlspecialchars($permission['description']); ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-actions mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> حفظ
                                    </button>
                                    <a href="index.php" class="btn btn-outline">
                                        <i class="fas fa-times"></i> إلغاء
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include '../includes/footer.php'; ?>
        </main>
    </div>
    
    <script>
        // Password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.getElementById('userForm');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('كلمة المرور غير متطابقة');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
        
        // Form submission
        form.addEventListener('submit', function(event) {
            if (password.value.length < 6) {
                event.preventDefault();
                alert('يجب أن تكون كلمة المرور 6 أحرف على الأقل');
                return false;
            }
            
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                alert('كلمة المرور غير متطابقة');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
