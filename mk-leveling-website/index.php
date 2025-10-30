<?php
// بدء الجلسة
session_start();

// الاتصال بقاعدة البيانات
require_once __DIR__ . '/includes/db.php';

// جلب الإعدادات من قاعدة البيانات
$settings = [];
$site_title = "MK Leveling Systems - أنظمة تسوية البلاط الاحترافية";
$contact_phone = "+962796051510";
$contact_email = "mklevelingsys@gmail.com";
$company_address = "عمان- الاردن";

if (isset($mysqli)) {
    $result = $mysqli->query("SELECT * FROM settings LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $settings = $result->fetch_assoc();
        $site_title = $settings['site_title'] ?? $site_title;
        $contact_phone = $settings['contact_phone'] ?? $contact_phone;
        $contact_email = $settings['contact_email'] ?? $contact_email;
        $company_address = $settings['company_address'] ?? $company_address;
    }
}

// تحديد اللغة الحالية
$current_language = isset($_SESSION['language']) ? $_SESSION['language'] : 'ar';

// دوال الترجمة
function t($ar_text, $en_text) {
    global $current_language;
    return $current_language === 'ar' ? $ar_text : $en_text;
}

// تبديل اللغة
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
    $current_language = $_SESSION['language'];
    header('Location: ' . str_replace('?lang=' . $_GET['lang'], '', $_SERVER['REQUEST_URI']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- لوحة التحكم -->
    <div class="admin-panel">
        <a href="/mk-leveling-website/admin/dashboard.php"><i class="fas fa-cog"></i> <?php echo t('لوحة التحكم', 'Admin Panel'); ?></a>
    </div>
    
    <!-- Header -->
    <header id="home">
        <div class="header-bg" style="background-image: url('/mk-leveling-website/images/header.jpg'); background-size: cover; background-position: center; height: 80vh; width: 100%;">
            <img src="/mk-leveling-website/images/header.jpg" alt="MK Leveling Systems Header" style="display: none; width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="header-overlay"></div>
        
        <!-- Navigation -->
        <nav class="navbar">
            <div class="container">
                <div class="nav-content">
                    <div class="logo">
                        <img src="/mk-leveling-website/images/logo.png" alt="MK Leveling Systems Logo" class="header-logo">
                        <div class="logo-text">
                            <div class="top">MK LEVELING</div>
                            <div class="bottom">SYSTEMS</div>
                        </div>
                    </div>
                    
                    <nav>
                        <ul>
                            <li><a href="index.php" class="active"><?php echo t('الرئيسية', 'Home'); ?></a></li>
                            <li><a href="about.php"><?php echo t('عن الشركة', 'About'); ?></a></li>
                            <li><a href="products.php"><?php echo t('المنتجات', 'Products'); ?></a></li>
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
        
        <!-- Header Content -->
        <div class="header-content">
            <div class="container">
                <div class="header-text">
                    <h1><?php echo t('أنظمة تسوية البلاط الاحترافية', 'Professional Tile Leveling Systems'); ?></h1>
                    <p><?php echo t('حلول مبتكرة لتركيب البلاط باحترافية ودقة عالية', 'Innovative solutions for professional and precise tile installation'); ?></p>
                    <div class="header-buttons">
                        <a href="products.php" class="btn"><?php echo t('تصفح المنتجات', 'Browse Products'); ?> <i class="fas fa-arrow-left"></i></a>
                        <a href="contact.php" class="btn btn-outline"><?php echo t('اتصل بنا', 'Contact Us'); ?> <i class="fas fa-phone"></i></a>
                    </div>
                </div>
                <div class="header-image">
                    <!-- تمت إزالة الصورة غير المرغوب فيها -->
                </div>
            </div>
        </div>
    </header>
    
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2><?php echo t('لماذا تختار MK Leveling Systems؟', 'Why Choose MK Leveling Systems?'); ?></h2>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3><?php echo t('جودة عالية', 'High Quality'); ?></h3>
                    <p><?php echo t('منتجاتنا مصنوعة من أفضل المواد لضمان المتانة والأداء المتميز', 'Our products are made from the best materials to ensure durability and excellent performance'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3><?php echo t('سهولة الاستخدام', 'Easy to Use'); ?></h3>
                    <p><?php echo t('تصميم مبتكر يسهل عملية التركيب والتسوية حتى للمبتدئين', 'Innovative design facilitates the installation and leveling process even for beginners'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3><?php echo t('ضمان الجودة', 'Quality Guarantee'); ?></h3>
                    <p><?php echo t('نقدم ضماناً على جميع منتجاتنا ضد عيوب التصنيع', 'We offer a warranty on all our products against manufacturing defects'); ?></p>
                </div>
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
    
    <script>
    // دالة تبديل اللغة
    function switchLanguage(lang) {
        window.location.href = '?lang=' + lang;
    }
    </script>
    
    <script src="js/main.js"></script>
</body>
</html>
