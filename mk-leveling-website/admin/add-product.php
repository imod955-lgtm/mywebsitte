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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name_ar = trim($_POST['name_ar'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $description_ar = trim($_POST['description_ar'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate inputs
    if (empty($name_ar)) {
        $errors[] = 'اسم المنتج بالعربية مطلوب';
    }
    
    if (empty($name_en)) {
        $errors[] = 'اسم المنتج بالإنجليزية مطلوب';
    }
    
    
    // Handle image upload or URL
    $image_url = '';
    
    // Check if image URL is provided
    $image_url_input = trim($_POST['image_url'] ?? '');
    
    // If URL is provided, validate it
    if (!empty($image_url_input)) {
        if (filter_var($image_url_input, FILTER_VALIDATE_URL)) {
            $image_url = $image_url_input;
        } else {
            $errors[] = 'الرجاء إدخال رابط صحيح للصورة';
        }
    } 
    // If file is uploaded
    elseif (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image_upload']['type'];
        $file_size = $_FILES['image_upload']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'نوع الملف غير مسموح به. يرجى تحميل صورة بصيغة JPG أو PNG أو GIF';
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB max size
            $errors[] = 'حجم الصورة كبير جداً. الحد الأقصى المسموح به هو 5 ميجابايت';
        } else {
            $upload_dir = '../images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $target_path)) {
                $image_url = 'images/products/' . $file_name;
            } else {
                $errors[] = 'حدث خطأ أثناء رفع الملف';
            }
        }
    } else {
        $errors[] = 'يرجى اختيار صورة للمنتج أو إدخال رابط صورة';
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO products (name_ar, name_en, description_ar, description_en, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name_ar, $name_en, $description_ar, $description_en, $image_url, $is_active);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'تمت إضافة المنتج بنجاح';
            header('Location: products.php');
            exit;
        } else {
            $errors[] = 'حدث خطأ أثناء إضافة المنتج';
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
    <title>إضافة منتج جديد - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        /* Custom styles for file upload */
        .file-upload-wrapper {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 2rem;
        }
        
        .file-upload-wrapper:hover {
            background-color: #f8f9fa;
        }
        
        .nav-tabs .nav-link {
            color: #4f46e5;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: #4f46e5;
            font-weight: 600;
            border-bottom: 3px solid #4f46e5;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        
        .tab-content {
            padding: 1rem 0;
        }
        
        /* Animation for drag and drop */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .drag-over {
            animation: pulse 1.5s infinite;
            border-color: #4f46e5 !important;
            background-color: #f0f4ff !important;
        }
    </style>
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
                    <h1>إضافة منتج جديد</h1>
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
                                           value="<?php echo htmlspecialchars($_POST['name_ar'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="name_en">اسم المنتج بالإنجليزية *</label>
                                    <input type="text" id="name_en" name="name_en" class="form-control" required
                                           value="<?php echo htmlspecialchars($_POST['name_en'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="description_ar">الوصف بالعربية</label>
                                    <textarea id="description_ar" name="description_ar" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description_ar'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description_en">الوصف بالإنجليزية</label>
                                    <textarea id="description_en" name="description_en" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description_en'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>رفع صورة المنتج</label>
                                
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs mb-3" id="imageUploadTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                                            <i class="fas fa-upload me-1"></i> رفع صورة
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="url-tab" data-bs-toggle="tab" data-bs-target="#url" type="button" role="tab">
                                            <i class="fas fa-link me-1"></i> رابط صورة
                                        </button>
                                    </li>
                                </ul>
                                
                                <!-- Tab Content -->
                                <div class="tab-content" id="imageUploadTabsContent">
                                    <!-- Upload Tab -->
                                    <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                        <div class="file-upload-wrapper" style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer;" id="drop-area">
                                            <input type="file" id="image_upload" name="image_upload" accept="image/*" style="display: none;">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: #6b7280; margin-bottom: 10px;"></i>
                                            <p id="file-name" style="margin: 10px 0; font-size: 1rem; color: #6b7280;">
                                                اسحب وأفلت الصورة هنا أو انقر للاختيار
                                            </p>
                                            <p class="text-muted" style="font-size: 0.875rem;">
                                                الصيغ المدعومة: JPG, PNG, GIF (الحد الأقصى: 5 ميجابايت)
                                            </p>
                                        </div>
                                        <div id="image-preview" style="margin-top: 15px; text-align: center; display: none;">
                                            <img id="preview" src="#" alt="معاينة الصورة" style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        </div>
                                    </div>
                                    
                                    <!-- URL Tab -->
                                    <div class="tab-pane fade" id="url" role="tabpanel">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                                            <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                                        </div>
                                        <div id="url-preview" style="margin-top: 15px; text-align: center; display: none;">
                                            <img id="url-preview-img" src="#" alt="معاينة الصورة" style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
                                    <label for="is_active" class="form-check-label">نشط</label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ المنتج
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
    
    <!-- Include Bootstrap JS for tabs if not already included -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Initialize Bootstrap tabs
    var tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEl.forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function (e) {
            // Reset previews when switching tabs
            document.getElementById('image-preview').style.display = 'none';
            document.getElementById('url-preview').style.display = 'none';
        });
    });

    // Handle file upload preview
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('image_upload');
    const fileName = document.getElementById('file-name');
    const preview = document.getElementById('preview');
    
    // Click to select file
    dropArea.addEventListener('click', () => fileInput.click());
    
    // Handle file selection
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
                fileName.textContent = file.name;
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Handle URL preview
    const urlInput = document.getElementById('image_url');
    urlInput.addEventListener('input', function() {
        const url = this.value.trim();
        const urlPreview = document.getElementById('url-preview');
        const urlPreviewImg = document.getElementById('url-preview-img');
        
        if (url) {
            // Simple URL validation
            try {
                new URL(url);
                urlPreviewImg.src = url;
                urlPreview.style.display = 'block';
                
                // Check if image loads successfully
                urlPreviewImg.onload = function() {
                    urlPreview.style.display = 'block';
                };
                
                urlPreviewImg.onerror = function() {
                    urlPreview.style.display = 'none';
                };
            } catch (e) {
                urlPreview.style.display = 'none';
            }
        } else {
            urlPreview.style.display = 'none';
        }
    });
    
    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.style.borderColor = '#4f46e5';
        dropArea.style.backgroundColor = '#f0f4ff';
    }
    
    function unhighlight() {
        dropArea.style.borderColor = '#d1d5db';
        dropArea.style.backgroundColor = '';
    }
    
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    }
    
    // Function to translate text using MyMemory API
    async function translateText(text, targetLang = 'en') {
        if (!text.trim()) return '';
        
        try {
            // Encode the text for URL
            const encodedText = encodeURIComponent(text);
            const url = `https://api.mymemory.translated.net/get?q=${encodedText}&langpair=ar|${targetLang}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data && data.responseData && data.responseData.translatedText) {
                return data.responseData.translatedText;
            }
            return '';
        } catch (error) {
            console.error('Translation error:', error);
            return '';
        }
    }
    
    // Auto-translate product name
    document.getElementById('name_ar').addEventListener('blur', async function() {
        const arText = this.value.trim();
        const enField = document.getElementById('name_en');
        
        if (arText && (!enField.value || enField.value === '')) {
            enField.placeholder = 'جاري الترجمة...';
            const translated = await translateText(arText, 'en');
            if (translated) {
                enField.value = translated;
            }
            enField.placeholder = '';
        }
    });
    
    // Auto-translate description
    document.getElementById('description_ar').addEventListener('blur', async function() {
        const arText = this.value.trim();
        const enField = document.getElementById('description_en');
        
        if (arText && (!enField.value || enField.value === '' || enField.value.startsWith('[English'))) {
            enField.placeholder = 'جاري الترجمة...';
            const translated = await translateText(arText, 'en');
            if (translated) {
                enField.value = translated;
            }
            enField.placeholder = '';
        }
    });
    
    // Add loading indicator
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .translating::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);    
    </script>
</body>
</html>
