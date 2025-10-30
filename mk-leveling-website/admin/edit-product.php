<?php
// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';
$product = null;

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product data
if ($product_id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

// Redirect if product not found
if (!$product) {
    $_SESSION['error_message'] = 'المنتج غير موجود';
    header('Location: products.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name_ar = trim($_POST['name_ar'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $description_ar = trim($_POST['description_ar'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_image = $product['image_url'];
    
    // Validate inputs
    if (empty($name_ar)) {
        $errors[] = 'اسم المنتج بالعربية مطلوب';
    }
    
    if (empty($name_en)) {
        $errors[] = 'اسم المنتج بالإنجليزية مطلوب';
    }
    
    
    // Handle file upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'نوع الملف غير مسموح به. يرجى تحميل صورة بصيغة JPG أو PNG أو GIF';
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB max size
            $errors[] = 'حجم الصورة كبير جداً. الحد الأقصى المسموح به هو 5 ميجابايت';
        } else {
            $upload_dir = '../images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if it exists and is not the default image
                if (!empty($current_image) && $current_image !== 'images/placeholder.png') {
                    $old_image_path = '../' . $current_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $current_image = 'images/products/' . $file_name;
            } else {
                $errors[] = 'حدث خطأ أثناء رفع الملف';
            }
        }
    }
    
    // If no errors, update the product
    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE products SET name_ar = ?, name_en = ?, description_ar = ?, description_en = ?, image_url = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssssii", $name_ar, $name_en, $description_ar, $description_en, $current_image, $is_active, $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'تم تحديث المنتج بنجاح';
            header('Location: products.php');
            exit;
        } else {
            $errors[] = 'حدث خطأ أثناء تحديث المنتج';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1>تعديل المنتج</h1>
                    <a href="products.php" class="btn btn-outline">
                        <i class="fas fa-arrow-right"></i> رجوع
                    </a>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-right: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name_ar">اسم المنتج بالعربية *</label>
                                    <input type="text" id="name_ar" name="name_ar" class="form-control" required 
                                           value="<?php echo htmlspecialchars($product['name_ar']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="name_en">اسم المنتج بالإنجليزية *</label>
                                    <input type="text" id="name_en" name="name_en" class="form-control" required
                                           value="<?php echo htmlspecialchars($product['name_en']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="description_ar">الوصف بالعربية</label>
                                    <textarea id="description_ar" name="description_ar" class="form-control" rows="4"><?php echo htmlspecialchars($product['description_ar']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description_en">الوصف بالإنجليزية</label>
                                    <textarea id="description_en" name="description_en" class="form-control" rows="4"><?php echo htmlspecialchars($product['description_en']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>الصورة الحالية</label>
                                <div style="margin-bottom: 10px;">
                                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="صورة المنتج الحالية" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #e0e0e0;">
                                </div>
                                
                                <label for="image">تغيير الصورة</label>
                                <div class="file-upload" id="file-upload">
                                    <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p id="file-name">انقر لتغيير صورة المنتج</p>
                                    <p class="text-muted">الحجم الأقصى: 5 ميجابايت</p>
                                </div>
                                <div id="image-preview" style="margin-top: 15px; display: none;">
                                    <img id="preview" src="#" alt="معاينة الصورة" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                    <div class="file-upload" id="file-upload">
                                        <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p id="file-name">انقر لتغيير صورة المنتج</p>
                                        <p class="text-muted">الحجم الأقصى: 5 ميجابايت</p>
                                    </div>
                                    <div id="image-preview" style="margin-top: 15px; display: none;">
                                        <img id="preview" src="#" alt="معاينة الصورة" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                    <label for="is_active" class="form-check-label">نشط</label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التغييرات
                                </button>
                                <a href="products.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <script>
    // معاينة الصورة قبل الرفع
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('current-image');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    // Toggle sidebar
    document.querySelector('.toggle-sidebar').addEventListener('click', function() {
        document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
    });
    
    // File upload preview
    const fileUpload = document.getElementById('file-upload');
    const fileInput = document.getElementById('image');
    const fileName = document.getElementById('file-name');
    const preview = document.getElementById('preview');
    const imagePreview = document.getElementById('image-preview');
    
    fileUpload.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
            fileName.textContent = this.files[0].name;
        }
    });
    
    // Allow drag and drop
    fileUpload.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3498db';
        this.style.backgroundColor = 'rgba(52, 152, 219, 0.1)';
    });
    
    fileUpload.addEventListener('dragleave', function() {
        this.style.borderColor = '#e0e0e0';
        this.style.backgroundColor = 'transparent';
    });
    
    fileUpload.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#e0e0e0';
        this.style.backgroundColor = 'transparent';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });
    </script>
</body>
</html>
