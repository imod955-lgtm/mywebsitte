<?php
// بدء الجلسة
session_start();

// الاتصال بقاعدة البيانات
require_once __DIR__ . '/includes/db.php';

// جلب الإعدادات
$site_title = "المنتجات - MK Leveling Systems";
$current_language = isset($_SESSION['language']) ? $_SESSION['language'] : 'ar';

// معلومات الاتصال
$contact_phone = "+962796051510";
$contact_email = "mklevelingsys@gmail.com";
$company_address = "عمان- الاردن";

// جلب المنتجات من قاعدة البيانات
$products = [];
if (isset($mysqli)) {
    $query = "SELECT * FROM products WHERE is_active = 1";
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

// دوال الترجمة
function t($ar_text, $en_text) {
    global $current_language;
    return $current_language === 'ar' ? $ar_text : $en_text;
}

// تبديل اللغة
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* تنسيقات إضافية */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }
        
        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 220px;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .product-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin: 0 0 15px;
            min-height: 60px;
        }
        
        .product-description {
            color: #7f8c8d;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .product-price {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .btn-view-more {
            background: #f8f9fa;
            color: #3498db;
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .btn-view-more:hover {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        /* تنسيقات للشاشات الصغيرة */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="inner-header">
        <div class="header-overlay"></div>
        
        <!-- Navigation -->
        <nav class="navbar">
            <div class="container">
                <div class="nav-content">
                    <div class="logo">
                        <a href="index.php" style="display: flex; align-items: center; gap: 10px;">
                            <img src="images/logo.png" alt="MK Leveling Systems Logo" class="header-logo" style="max-width: 100px; height: auto;">
                            <div class="logo-text" style="text-align: right; direction: ltr;">
                                <div class="top">MK LEVELING</div>
                                <div class="bottom">SYSTEMS</div>
                            </div>
                        </a>
                    </div>
                    
                    <nav>
                        <ul>
                            <li><a href="index.php"><?php echo t('الرئيسية', 'Home'); ?></a></li>
                            <li><a href="about.php"><?php echo t('عن الشركة', 'About'); ?></a></li>
                            <li class="active"><a href="products.php"><?php echo t('المنتجات', 'Products'); ?></a></li>
                            <li><a href="contact.php"><?php echo t('اتصل بنا', 'Contact'); ?></a></li>
                        </ul>
                    </nav>
                    
                    <div class="lang-switcher">
                        <button class="<?php echo $current_language === 'ar' ? 'active' : ''; ?>" onclick="switchLanguage('ar')">العربية</button>
                        <button class="<?php echo $current_language === 'en' ? 'active' : ''; ?>" onclick="switchLanguage('en')">English</button>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Title -->
        <div class="page-title" style="text-align: center; margin-top: 20px;">
            <div class="container">
                <h1 style="margin: 0; padding: 10px 0;"><?php echo t('المنتجات', 'Products'); ?></h1>
            </div>
        </div>
    </header>

    <!-- قسم المنتجات -->
    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo t('منتجاتنا', 'Our Products'); ?></h2>
                <p class="section-description">
                    <?php echo t('اكتشف أحدث منتجاتنا المتميزة في مجال أنظمة تسوية البلاط', 'Discover our latest products in tile leveling systems'); ?>
                </p>
            </div>
            
            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                // Get the image URL from the database
                                $imageUrl = $product['image_url'] ?? '';
                                $imagePath = '';
                                
                                // Check different possible image locations
                                if (!empty($imageUrl)) {
                                    // Remove any leading slashes or backslashes
                                    $imageUrl = ltrim($imageUrl, '/\\');
                                    
                                    // Check if the image exists in different possible locations
                                    $possiblePaths = [
                                        $imageUrl,  // Original path
                                        'images/products/' . basename($imageUrl),  // Just the filename in images/products
                                        'admin/' . $imageUrl,  // Path relative to admin
                                        'admin/images/products/' . basename($imageUrl)  // Admin products directory
                                    ];
                                    
                                    // Try each path until we find an existing image
                                    foreach ($possiblePaths as $path) {
                                        if (file_exists($path) && is_file($path)) {
                                            $imagePath = $path;
                                            break;
                                        }
                                    }
                                }
                                
                                // If no valid image found, use placeholder
                                if (empty($imagePath) || !file_exists($imagePath)) {
                                    $imagePath = 'images/placeholder.png';
                                }
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name_ar']); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                            <div class="product-info">
                                <div class="product-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo t('أنظمة تسوية البلاط', 'Tile Leveling Systems'); ?>
                                </div>
                                <h3 class="product-title">
                                    <?php echo $current_language === 'ar' ? htmlspecialchars($product['name_ar']) : htmlspecialchars($product['name_en']); ?>
                                </h3>
                                <p class="product-description">
                                    <?php 
                                    $description = $current_language === 'ar' ? $product['description_ar'] : $product['description_en'];
                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                    ?>
                                </p>
                                <div class="product-footer">
                                    <a href="https://wa.me/962796051510?text=<?php echo urlencode('مرحباً، أريد طلب المنتج: ' . ($current_language === 'ar' ? $product['name_ar'] : $product['name_en'])); ?>" class="whatsapp-btn" target="_blank">
                                        <i class="fab fa-whatsapp"></i> <?php echo t('اطلب الآن', 'Order Now'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 40px 0;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #bdc3c7; margin-bottom: 15px;"></i>
                        <h3 style="color: #7f8c8d;"><?php echo t('لا توجد منتجات متاحة حالياً', 'No products available at the moment'); ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <!-- العمود الأول: معلومات الشركة -->
                <div class="footer-about">
                    <div class="logo-text">
                        <div class="top">MK LEVELING</div>
                        <div class="bottom">SYSTEMS</div>
                    </div>
                    <p><?php echo t('متخصصون في أنظمة تسوية البلاط بجودة عالية وتصميم مبتكر يلبي احتياجات المحترفين والهواة.', 'Specialists in tile leveling systems with high quality and innovative design that meets the needs of professionals and amateurs.'); ?></p>
                </div>
                
                <!-- العمود الثاني: الروابط السريعة -->
                <div class="footer-column">
                    <h3><?php echo t('روابط سريعة', 'Quick Links'); ?></h3>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-left"></i> <?php echo t('الرئيسية', 'Home'); ?></a></li>
                        <li><a href="about.php"><i class="fas fa-chevron-left"></i> <?php echo t('عن الشركة', 'About'); ?></a></li>
                        <li><a href="products.php"><i class="fas fa-chevron-left"></i> <?php echo t('المنتجات', 'Products'); ?></a></li>
                        <li><a href="contact.php"><i class="fas fa-chevron-left"></i> <?php echo t('اتصل بنا', 'Contact'); ?></a></li>
                    </ul>
                </div>
                
                <!-- العمود الثالث: وسائل التواصل -->
                <div class="footer-column">
                    <h3><?php echo t('وسائل التواصل', 'Contact Methods'); ?></h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-phone"></i> <span><?php echo t('الهاتف:', 'Phone:'); ?> <?php echo $contact_phone; ?></span></li>
                        <li><i class="fas fa-envelope"></i> <span><?php echo t('البريد الإلكتروني:', 'Email:'); ?> <?php echo $contact_email; ?></span></li>
                        <li><i class="fas fa-map-marker-alt"></i> <span><?php echo t('العنوان:', 'Address:'); ?> <?php echo $company_address; ?></span></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p><?php echo t('جميع الحقوق محفوظة', 'All Rights Reserved'); ?> &copy; <?php echo date('Y'); ?> MK Leveling Systems</p>
            </div>
        </div>
    </footer>

    <!-- نافذة البوب للصور -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="expandedImg">
    </div>

    <script>
        // كود جافاسكريبت للوظائف التفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير التمرير السلس للروابط
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // إضافة وظيفة عرض الصورة بالحجم الكامل
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('expandedImg');
            const closeBtn = document.querySelector('.close-modal');

            // فتح النافذة المنبثقة عند النقر على صورة منتج
            document.querySelectorAll('.product-image img').forEach(img => {
                img.addEventListener('click', function() {
                    modal.classList.add('show');
                    modalImg.src = this.src;
                    document.body.style.overflow = 'hidden'; // منع التمرير عند فتح النافذة
                });
            });

            // إغلاق النافذة المنبثقة
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto'; // إعادة تفعيل التمرير
            });

            // إغلاق النافذة عند النقر خارج الصورة
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });

            // إغلاق النافذة بالزر ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // دالة تبديل اللغة
        function switchLanguage(lang) {
            // إضافة معلمة اللغة إلى الرابط الحالي
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
