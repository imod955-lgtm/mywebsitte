<?php
// التحقق من تسجيل الدخول
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// الاتصال بقاعدة البيانات
require_once __DIR__ . '/../includes/db.php';

// جلب المنتجات من قاعدة البيانات
$products = [];
if (isset($mysqli)) {
    $result = $mysqli->query("SELECT * FROM products ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* تنسيقات مخصصة لصفحة المنتجات */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 180px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-details {
            padding: 15px;
        }
        
        .product-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            line-height: 1.4;
        }
        
        .product-description {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 60px;
        }
        
        .product-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-top: 1px solid #f0f0f0;
            background: #f9f9f9;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
            margin-right: 5px;
        }
        
        .no-products {
            text-align: center;
            padding: 40px 20px;
            background: #fff;
            border-radius: 8px;
            border: 1px dashed #ddd;
            margin-top: 20px;
        }
        
        .no-products i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .no-products p {
            color: #666;
            margin-bottom: 20px;
        }
        
        /* تحسينات للشريط الجانبي */
        .sidebar {
            background: #2c3e50;
        }
        
        .sidebar .logo {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav ul li a {
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
            border-right: 3px solid transparent;
        }
        
        .sidebar-nav ul li a:hover,
        .sidebar-nav ul li.active > a {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-right-color: #4361ee;
        }
        
        .sidebar-nav ul li a i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* تحسينات للهيدر */
        .main-header {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 0 20px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .page-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #2c3e50;
        }
        
        /* تحسينات للأزرار */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #4361ee;
            border: 1px solid #4361ee;
        }
        
        .btn-primary:hover {
            background: #3a56d4;
            border-color: #3a56d4;
        }
        
        .btn-outline-primary {
            color: #4361ee;
            border: 1px solid #4361ee;
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: #4361ee;
            color: #fff;
        }
        
        .btn-outline-danger {
            color: #dc3545;
            border: 1px solid #dc3545;
            background: transparent;
        }
        
        .btn-outline-danger:hover {
            background: #dc3545;
            color: #fff;
        }
        
        /* تحسينات للكروت */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- تضمين الشريط الجانبي -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <!-- تضمين الهيدر -->
            <?php include 'includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1>المنتجات</h1>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منتج جديد
                    </a>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($products)): ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image" style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 180px;">
                                    <?php 
                                    $image_path = '';
                                    $image_found = false;
                                    
                                    if (!empty($product['image_url'])) {
                                        // المحاولة الأولى: المسار المباشر
                                        $direct_path = $product['image_url'];
                                        $full_direct_path = __DIR__ . '/../' . ltrim($direct_path, '/');
                                        
                                        // المحاولة الثانية: البحث في مجلد الصور
                                        $image_name = basename($product['image_url']);
                                        $images_path = __DIR__ . '/../images/' . $image_name;
                                        
                                        // التحقق من وجود الصورة في أي من المسارات
                                        if (file_exists($full_direct_path)) {
                                            $image_path = $direct_path;
                                            $image_found = true;
                                        } elseif (file_exists($images_path)) {
                                            $image_path = 'images/' . $image_name;
                                            $image_found = true;
                                        } elseif (file_exists(__DIR__ . '/../' . $image_name)) {
                                            $image_path = $image_name;
                                            $image_found = true;
                                        }
                                        
                                        // عرض الصورة إذا وجدت
                                        if ($image_found) {
                                            echo '<img src="../' . ltrim($image_path, '/') . '" alt="' . htmlspecialchars($product['name_ar']) . '" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 5px;">';
                                        }
                                    }
                                    
                                    // عرض أيقونة افتراضية إذا لم يتم العثور على الصورة
                                    if (!$image_found) {
                                        echo '<div class="text-center p-3">';
                                        echo '<i class="fas fa-box-open" style="font-size: 2.5rem; color: #6c757d; margin-bottom: 0.5rem;"></i>';
                                        echo '<div style="font-size: 0.8rem; color: #6c757d;">' . $product['image_url'] . '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <div class="product-details">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name_ar']); ?></h3>
                                    <p class="product-description">
                                        <?php 
                                        $description = strip_tags($product['description_ar']);
                                        echo mb_substr($description, 0, 150) . (mb_strlen($description) > 150 ? '...' : '');
                                        ?>
                                    </p>
                                </div>
                                <div class="product-actions">
                                    <span class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $product['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                    </span>
                                    <div class="action-buttons">
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="delete_product.php" method="POST" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المنتج؟')">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h4>لا توجد منتجات متاحة</h4>
                        <p>لم يتم إضافة أي منتجات بعد. يمكنك البدء بإضافة منتجات جديدة.</p>
                        <a href="add-product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة منتج جديد
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- تضمين الفوتر -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    // تفعيل عناصر التلميحات
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>
