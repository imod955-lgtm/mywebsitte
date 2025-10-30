<?php
// التحقق من تسجيل الدخول
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// تعيين عنوان الصفحة
$page_title = 'الرئيسية';

// الاتصال بقاعدة البيانات
require_once __DIR__ . '/../includes/db.php';

// جلب الإحصائيات
$stats = [
    'total_products' => 0,
    'total_messages' => 0,
    'total_active_products' => 0
];

if (isset($mysqli)) {
    // جلب إجمالي المنتجات
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_products'] = $row['count'];
    }
    
    // جلب إجمالي الرسائل غير المقروءة
    try {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_messages'] = $row['count'];
        }
    } catch (mysqli_sql_exception $e) {
        // في حالة عدم وجود الجدول، نقوم بإنشائه
        if ($e->getCode() == 1146) { // Error code for table doesn't exist
            echo '<div class="alert alert-warning">
                <p>يبدو أن جداول قاعدة البيانات غير موجودة. <a href="create_tables.php" class="btn btn-sm btn-warning">انقر هنا لإنشاء الجداول المطلوبة</a></p>
            </div>';
            $stats['total_messages'] = 0;
        } else {
            throw $e; // إعادة رمي الاستثناء إذا كان خطأ آخر
        }
    }
    
    // جلب عدد المنتجات النشطة
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_active_products'] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* تنسيقات خاصة بصفحة الرئيسية */
        .welcome-card {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .welcome-card h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
        }
        
        .welcome-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 20px;
            max-width: 600px;
            position: relative;
        }
        
        .welcome-actions .btn {
            margin-left: 10px;
            position: relative;
            z-index: 1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .stat-card .icon.primary {
            background: #4361ee;
        }
        
        .stat-card .icon.success {
            background: #2ecc71;
        }
        
        .stat-card .icon.warning {
            background: #f39c12;
        }
        
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 5px 0;
            line-height: 1.2;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .stat-card .progress {
            height: 6px;
            background-color: #f0f2f5;
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
        }
        
        .stat-card .progress-bar {
            background: #4361ee;
            height: 100%;
            border-radius: 3px;
        }
        
        .recent-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .section-header {
            padding: 18px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .section-body {
            padding: 0;
        }
        
        .recent-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .recent-item {
            padding: 15px 25px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item:hover {
            background: #f9f9f9;
        }
        
        .recent-item .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f0f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            color: #4361ee;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .recent-item .item-details {
            flex-grow: 1;
        }
        
        .recent-item .item-title {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 0.95rem;
        }
        
        .recent-item .item-meta {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .recent-item .item-time {
            font-size: 0.8rem;
            color: #95a5a6;
            white-space: nowrap;
            margin-right: 10px;
        }
        
        .view-all {
            padding: 15px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        
        .view-all a {
            color: #4361ee;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .view-all a i {
            margin-right: 5px;
            transition: transform 0.3s;
        }
        
        .view-all a:hover i {
            transform: translateX(-3px);
        }
        
        /* تنسيقات متجاوبة */
        @media (max-width: 992px) {
            .welcome-card h2 {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .welcome-card {
                text-align: center;
                padding: 20px 15px;
            }
            
            .welcome-card::before {
                display: none;
            }
            
            .welcome-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .welcome-actions .btn {
                margin: 0;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
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
            
            <!-- المحتوى الرئيسي -->
            <div class="content" style="padding-top: 80px;">
                <!-- بطاقة الترحيب -->
                <div class="welcome-card" style="margin-top: 20px; margin-bottom: 30px;">
                    <h2>مرحباً بك في لوحة التحكم</h2>
                    <p>هنا يمكنك إدارة محتوى موقعك بكل سهولة ويسر. لديك <?php echo $stats['total_products']; ?> منتج و <?php echo $stats['total_messages']; ?> رسالة جديدة.</p>
                    
                    <div class="welcome-actions" style="margin-top: 20px;">
                        <a href="products.php" class="btn btn-light">
                            <i class="fas fa-box"></i> عرض المنتجات
                        </a>
                        <a href="messages.php" class="btn btn-outline-light">
                            <i class="fas fa-envelope"></i> عرض الرسائل
                        </a>
                    </div>
                </div>
                
                <!-- إحصائيات سريعة -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="value"><?php echo $stats['total_products']; ?></div>
                        <div class="label">إجمالي المنتجات</div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo min(100, ($stats['total_products'] / 50) * 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="value"><?php echo $stats['total_active_products']; ?></div>
                        <div class="label">المنتجات النشطة</div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo $stats['total_products'] > 0 ? ($stats['total_active_products'] / $stats['total_products']) * 100 : 0; ?>%; background: #2ecc71;"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon warning">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="value"><?php echo $stats['total_messages']; ?></div>
                        <div class="label">الرسائل الجديدة</div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo min(100, ($stats['total_messages'] / 20) * 100); ?>%; background: #f39c12;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- آخر المنتجات المضافة -->
                    <div class="col-lg-8">
                        <div class="recent-section">
                            <div class="section-header">
                                <h3><i class="fas fa-box-open me-2"></i> آخر المنتجات المضافة</h3>
                                <a href="products.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                            </div>
                            <div class="section-body">
                                <?php
                                $recent_products = [];
                                $result = $mysqli->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
                                if ($result) {
                                    while ($row = $result->fetch_assoc()) {
                                        $recent_products[] = $row;
                                    }
                                }
                                
                                if (!empty($recent_products)) {
                                    echo '<ul class="recent-list">';
                                    foreach ($recent_products as $product) {
                                        echo '<li class="recent-item">';
                                        echo '<div class="item-icon"><i class="fas fa-box"></i></div>';
                                        echo '<div class="item-details">';
                                        echo '<div class="item-title">' . htmlspecialchars($product['name_ar']) . '</div>';
                                        echo '<div class="item-meta">' . ($product['category_name_ar'] ?? 'غير مصنف') . '</div>';
                                        echo '</div>';
                                        echo '<div class="item-time">' . date('Y-m-d', strtotime($product['created_at'])) . '</div>';
                                        echo '<a href="edit-product.php?id=' . $product['id'] . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    echo '<div class="view-all">';
                                    echo '<a href="products.php">عرض جميع المنتجات <i class="fas fa-arrow-left"></i></a>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="p-4 text-center text-muted">لا توجد منتجات مضافة بعد</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- آخر الرسائل -->
                    <div class="col-lg-4">
                        <div class="recent-section">
                            <div class="section-header">
                                <h3><i class="fas fa-envelope me-2"></i> آخر الرسائل</h3>
                                <a href="messages.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                            </div>
                            <div class="section-body">
                                <?php
                                $recent_messages = [];
                                try {
                                    $result = $mysqli->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            $recent_messages[] = $row;
                                        }
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    // تجاهل الخطأ إذا كان الجدول غير موجود
                                    if ($e->getCode() != 1146) {
                                        throw $e; // إعادة رمي الاستثناء إذا كان خطأ آخر
                                    }
                                }
                                
                                if (!empty($recent_messages)) {
                                    echo '<ul class="recent-list">';
                                    foreach ($recent_messages as $message) {
                                        $unread_class = $message['is_read'] ? '' : 'unread';
                                        echo '<li class="recent-item ' . $unread_class . '">';
                                        echo '<div class="item-icon"><i class="fas fa-envelope' . ($message['is_read'] ? '' : '-open') . '"></i></div>';
                                        echo '<div class="item-details">';
                                        echo '<div class="item-title">' . htmlspecialchars($message['name']) . '</div>';
                                        echo '<div class="item-meta">' . htmlspecialchars($message['email']) . '</div>';
                                        echo '</div>';
                                        echo '<a href="view-message.php?id=' . $message['id'] . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    echo '<div class="view-all">';
                                    echo '<a href="messages.php">عرض جميع الرسائل <i class="fas fa-arrow-left"></i></a>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="p-4 text-center text-muted">لا توجد رسائل واردة</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- تضمين الفوتر -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
    // كود JavaScript الإضافي يذهب هنا
    document.addEventListener('DOMContentLoaded', function() {
        // تحديث الوقت في بطاقات الرسائل
        function updateMessageTimes() {
            document.querySelectorAll('.item-time').forEach(function(el) {
                const date = new Date(el.getAttribute('data-time'));
                el.textContent = formatTimeAgo(date);
            });
        }
        
        // تنسيق الوقت المنقضي
        function formatTimeAgo(date) {
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            let interval = Math.floor(seconds / 31536000);
            if (interval >= 1) return 'منذ ' + interval + ' سنة';
            
            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return 'منذ ' + interval + ' شهر';
            
            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return 'منذ ' + interval + ' يوم';
            
            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return 'منذ ' + interval + ' ساعة';
            
            interval = Math.floor(seconds / 60);
            if (interval >= 1) return 'منذ ' + interval + ' دقيقة';
            
            return 'الآن';
        }
        
        // تحديث الوقت كل دقيقة
        updateMessageTimes();
        setInterval(updateMessageTimes, 60000);
    });
    </script>
</body>
</html>
