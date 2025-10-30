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

// Fetch current settings
$settings = [];
$result = $mysqli->query("SELECT * FROM settings");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // General Settings
    $site_name_ar = trim($_POST['site_name_ar'] ?? '');
    $site_name_en = trim($_POST['site_name_en'] ?? '');
    $site_description_ar = trim($_POST['site_description_ar'] ?? '');
    $site_description_en = trim($_POST['site_description_en'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address_ar = trim($_POST['address_ar'] ?? '');
    $address_en = trim($_POST['address_en'] ?? '');
    
    // Social Media
    $facebook_url = trim($_POST['facebook_url'] ?? '');
    $twitter_url = trim($_POST['twitter_url'] ?? '');
    $instagram_url = trim($_POST['instagram_url'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    
    // SEO Settings
    $meta_title_ar = trim($_POST['meta_title_ar'] ?? '');
    $meta_title_en = trim($_POST['meta_title_en'] ?? '');
    $meta_description_ar = trim($_POST['meta_description_ar'] ?? '');
    $meta_description_en = trim($_POST['meta_description_en'] ?? '');
    $meta_keywords_ar = trim($_POST['meta_keywords_ar'] ?? '');
    $meta_keywords_en = trim($_POST['meta_keywords_en'] ?? '');
    
    // Validate required fields
    if (empty($site_name_ar)) {
        $errors[] = 'اسم الموقع بالعربية مطلوب';
    }
    
    if (empty($site_name_en)) {
        $errors[] = 'اسم الموقع بالإنجليزية مطلوب';
    }
    
    if (empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صحيح';
    }
    
    // Handle logo upload
    $logo_url = $settings['logo_url'] ?? '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['logo']['type'];
        $file_size = $_FILES['logo']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'نوع ملف الشعار غير مدعوم. يرجى تحميل صورة بصيغة JPG أو PNG أو GIF أو SVG';
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB max size
            $errors[] = 'حجم ملف الشعار كبير جداً. الحد الأقصى المسموح به هو 2 ميجابايت';
        } else {
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $file_name = 'logo_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                // Delete old logo if it exists and is not the default one
                if (!empty($logo_url) && $logo_url !== 'images/logo.png' && file_exists('../' . $logo_url)) {
                    unlink('../' . $logo_url);
                }
                $logo_url = 'images/' . $file_name;
            } else {
                $errors[] = 'حدث خطأ أثناء رفع ملف الشعار';
            }
        }
    }
    
    // Handle favicon upload
    $favicon_url = $settings['favicon_url'] ?? '';
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png'];
        $file_type = $_FILES['favicon']['type'];
        $file_size = $_FILES['favicon']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'نوع ملف الأيقونة غير مدعوم. يرجى تحميل ملف بصيغة ICO أو PNG';
        } elseif ($file_size > 1 * 1024 * 1024) { // 1MB max size
            $errors[] = 'حجم ملف الأيقونة كبير جداً. الحد الأقصى المسموح به هو 1 ميجابايت';
        } else {
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            $file_name = 'favicon.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $target_path)) {
                // Delete old favicon if it exists and is not the default one
                if (!empty($favicon_url) && $favicon_url !== 'images/favicon.ico' && file_exists('../' . $favicon_url)) {
                    unlink('../' . $favicon_url);
                }
                $favicon_url = 'images/' . $file_name;
            } else {
                $errors[] = 'حدث خطأ أثناء رفع ملف الأيقونة';
            }
        }
    }
    
    // If no errors, save settings
    if (empty($errors)) {
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // General Settings
            $settings_to_update = [
                'site_name_ar' => $site_name_ar,
                'site_name_en' => $site_name_en,
                'site_description_ar' => $site_description_ar,
                'site_description_en' => $site_description_en,
                'contact_email' => $contact_email,
                'phone' => $phone,
                'address_ar' => $address_ar,
                'address_en' => $address_en,
                'logo_url' => $logo_url,
                'favicon_url' => $favicon_url,
                'facebook_url' => $facebook_url,
                'twitter_url' => $twitter_url,
                'instagram_url' => $instagram_url,
                'youtube_url' => $youtube_url,
                'meta_title_ar' => $meta_title_ar,
                'meta_title_en' => $meta_title_en,
                'meta_description_ar' => $meta_description_ar,
                'meta_description_en' => $meta_description_en,
                'meta_keywords_ar' => $meta_keywords_ar,
                'meta_keywords_en' => $meta_keywords_en
            ];
            
            // Prepare the update statement
            $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            
            // Update each setting
            foreach ($settings_to_update as $key => $value) {
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
            }
            
            // Commit transaction
            $mysqli->commit();
            
            // Update session settings
            $_SESSION['success_message'] = 'تم حفظ الإعدادات بنجاح';
            
            // Refresh the page to show updated settings
            header('Location: settings.php');
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $mysqli->rollback();
            $errors[] = 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
        }
        
        .tab-button.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin-top: 10px;
            border: 1px solid #e0e0e0;
            padding: 5px;
            border-radius: 4px;
        }
        
        .favicon-preview {
            width: 32px;
            height: 32px;
            margin-top: 10px;
            border: 1px solid #e0e0e0;
            padding: 5px;
            border-radius: 4px;
        }
        
        .file-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .file-upload:hover {
            border-color: #3498db;
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .file-upload i {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
            display: block;
        }
        
        .settings-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .settings-section .section-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
        }
        
        .settings-section .section-body {
            padding: 20px;
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
                    <h1>الإعدادات</h1>
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
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="settings-tabs">
                        <button type="button" class="tab-button active" data-tab="general">عام</button>
                        <button type="button" class="tab-button" data-tab="social">وسائل التواصل الاجتماعي</button>
                        <button type="button" class="tab-button" data-tab="seo">تحسين محركات البحث (SEO)</button>
                    </div>
                    
                    <!-- General Settings Tab -->
                    <div id="general-tab" class="tab-content active">
                        <div class="settings-section">
                            <div class="section-header">
                                <i class="fas fa-globe"></i> الإعدادات العامة
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="site_name_ar">اسم الموقع (العربية) *</label>
                                        <input type="text" id="site_name_ar" name="site_name_ar" class="form-control" required
                                               value="<?php echo htmlspecialchars($settings['site_name_ar'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="site_name_en">اسم الموقع (الإنجليزية) *</label>
                                        <input type="text" id="site_name_en" name="site_name_en" class="form-control" required
                                               value="<?php echo htmlspecialchars($settings['site_name_en'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="site_description_ar">وصف الموقع (العربية)</label>
                                        <textarea id="site_description_ar" name="site_description_ar" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description_ar'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="site_description_en">وصف الموقع (الإنجليزية)</label>
                                        <textarea id="site_description_en" name="site_description_en" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description_en'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="contact_email">البريد الإلكتروني للاتصال *</label>
                                        <input type="email" id="contact_email" name="contact_email" class="form-control" required
                                               value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">رقم الهاتف</label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                               value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="address_ar">العنوان (العربية)</label>
                                        <textarea id="address_ar" name="address_ar" class="form-control" rows="2"><?php echo htmlspecialchars($settings['address_ar'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address_en">العنوان (الإنجليزية)</label>
                                        <textarea id="address_en" name="address_en" class="form-control" rows="2"><?php echo htmlspecialchars($settings['address_en'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <div class="section-header">
                                <i class="fas fa-image"></i> الشعار والأيقونة
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="logo">شعار الموقع</label>
                                        <input type="file" id="logo" name="logo" accept="image/*" class="form-control-file" style="display: none;">
                                        <div class="file-upload" onclick="document.getElementById('logo').click();">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>انقر لرفع شعار جديد</p>
                                            <p class="text-muted">الحجم الأقصى: 2 ميجابايت (JPEG, PNG, GIF, SVG)</p>
                                        </div>
                                        <?php if (!empty($settings['logo_url'])): ?>
                                            <div class="mt-2">
                                                <p>الشعار الحالي:</p>
                                                <img src="../<?php echo htmlspecialchars($settings['logo_url']); ?>" alt="شعار الموقع" class="logo-preview">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="favicon">أيقونة الموقع (Favicon)</label>
                                        <input type="file" id="favicon" name="favicon" accept=".ico,image/x-icon,image/png" class="form-control-file" style="display: none;">
                                        <div class="file-upload" onclick="document.getElementById('favicon').click();">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>انقر لرفع أيقونة جديدة</p>
                                            <p class="text-muted">الحجم الأقصى: 1 ميجابايت (ICO أو PNG)</p>
                                        </div>
                                        <?php if (!empty($settings['favicon_url'])): ?>
                                            <div class="mt-2">
                                                <p>الأيقونة الحالية:</p>
                                                <img src="../<?php echo htmlspecialchars($settings['favicon_url']); ?>" alt="أيقونة الموقع" class="favicon-preview">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media Tab -->
                    <div id="social-tab" class="tab-content">
                        <div class="settings-section">
                            <div class="section-header">
                                <i class="fas fa-share-alt"></i> وسائل التواصل الاجتماعي
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="facebook_url">فيسبوك</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fab fa-facebook-f"></i></span>
                                            </div>
                                            <input type="url" id="facebook_url" name="facebook_url" class="form-control" placeholder="https://facebook.com/username"
                                                   value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="twitter_url">تويتر</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                            </div>
                                            <input type="url" id="twitter_url" name="twitter_url" class="form-control" placeholder="https://twitter.com/username"
                                                   value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="instagram_url">إنستغرام</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                            </div>
                                            <input type="url" id="instagram_url" name="instagram_url" class="form-control" placeholder="https://instagram.com/username"
                                                   value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="youtube_url">يوتيوب</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                            </div>
                                            <input type="url" id="youtube_url" name="youtube_url" class="form-control" placeholder="https://youtube.com/username"
                                                   value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SEO Tab -->
                    <div id="seo-tab" class="tab-content">
                        <div class="settings-section">
                            <div class="section-header">
                                <i class="fas fa-search"></i> إعدادات SEO
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="meta_title_ar">عنوان الصفحة (العربية)</label>
                                        <input type="text" id="meta_title_ar" name="meta_title_ar" class="form-control"
                                               value="<?php echo htmlspecialchars($settings['meta_title_ar'] ?? ''); ?>">
                                        <small class="text-muted">سيظهر في نتائج محركات البحث (60 حرف كحد أقصى)</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="meta_title_en">عنوان الصفحة (الإنجليزية)</label>
                                        <input type="text" id="meta_title_en" name="meta_title_en" class="form-control"
                                               value="<?php echo htmlspecialchars($settings['meta_title_en'] ?? ''); ?>">
                                        <small class="text-muted">سيظهر في نتائج محركات البحث (60 حرف كحد أقصى)</small>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="meta_description_ar">وصف الصفحة (العربية)</label>
                                        <textarea id="meta_description_ar" name="meta_description_ar" class="form-control" rows="2"><?php echo htmlspecialchars($settings['meta_description_ar'] ?? ''); ?></textarea>
                                        <small class="text-muted">سيظهر في نتائج محركات البحث (160 حرف كحد أقصى)</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="meta_description_en">وصف الصفحة (الإنجليزية)</label>
                                        <textarea id="meta_description_en" name="meta_description_en" class="form-control" rows="2"><?php echo htmlspecialchars($settings['meta_description_en'] ?? ''); ?></textarea>
                                        <small class="text-muted">سيظهر في نتائج محركات البحث (160 حرف كحد أقصى)</small>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="meta_keywords_ar">الكلمات المفتاحية (العربية)</label>
                                        <input type="text" id="meta_keywords_ar" name="meta_keywords_ar" class="form-control"
                                               value="<?php echo htmlspecialchars($settings['meta_keywords_ar'] ?? ''); ?>">
                                        <small class="text-muted">افصل بين الكلمات بفاصلة (,) - مثال: متجر, تسوق, ملابس</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="meta_keywords_en">الكلمات المفتاحية (الإنجليزية)</label>
                                        <input type="text" id="meta_keywords_en" name="meta_keywords_en" class="form-control"
                                               value="<?php echo htmlspecialchars($settings['meta_keywords_en'] ?? ''); ?>">
                                        <small class="text-muted">Separate keywords with commas - Example: store, shop, clothes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                        <button type="reset" class="btn btn-outline">
                            <i class="fas fa-undo"></i> إعادة تعيين
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <script>
    // Toggle sidebar
    document.querySelector('.toggle-sidebar').addEventListener('click', function() {
        document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
    });
    
    // Tab switching
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Show file name when selected
    document.getElementById('logo').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'لم يتم اختيار ملف';
        const uploadText = this.parentNode.querySelector('p');
        if (uploadText) {
            uploadText.textContent = fileName;
        }
        
        // Show preview
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'logo-preview mt-2';
                
                const previewContainer = document.querySelector('#logo').parentNode;
                const oldPreview = previewContainer.querySelector('.logo-preview');
                if (oldPreview) {
                    previewContainer.replaceChild(preview, oldPreview);
                } else {
                    previewContainer.appendChild(preview);
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    document.getElementById('favicon').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'لم يتم اختيار ملف';
        const uploadText = this.parentNode.querySelector('p');
        if (uploadText) {
            uploadText.textContent = fileName;
        }
        
        // Show preview
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'favicon-preview mt-2';
                
                const previewContainer = document.querySelector('#favicon').parentNode;
                const oldPreview = previewContainer.querySelector('.favicon-preview');
                if (oldPreview) {
                    previewContainer.replaceChild(preview, oldPreview);
                } else {
                    previewContainer.appendChild(preview);
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Character counters for SEO fields
    function setupCharacterCounter(inputId, maxLength) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const counter = document.createElement('small');
        counter.className = 'character-counter float-left';
        counter.style.display = 'block';
        counter.style.marginTop = '5px';
        counter.style.color = '#666';
        
        input.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = input.value.length;
            counter.textContent = `${length} / ${maxLength} حرف`;
            
            if (length > maxLength) {
                counter.style.color = '#e74c3c';
            } else if (length > maxLength * 0.8) {
                counter.style.color = '#f39c12';
            } else {
                counter.style.color = '#666';
            }
        }
        
        input.addEventListener('input', updateCounter);
        updateCounter();
    }
    
    // Setup character counters for SEO fields
    setupCharacterCounter('meta_title_ar', 60);
    setupCharacterCounter('meta_title_en', 60);
    setupCharacterCounter('meta_description_ar', 160);
    setupCharacterCounter('meta_description_en', 160);
    </script>
</body>
</html>
