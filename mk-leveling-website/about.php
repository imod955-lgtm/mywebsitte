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
    <title><?php echo htmlspecialchars($site_title); ?> - <?php echo t('عن الشركة', 'About Us'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- لوحة التحكم -->
    <div class="admin-panel">
        <a href="admin/dashboard.php"><i class="fas fa-cog"></i> <?php echo t('لوحة التحكم', 'Admin Panel'); ?></a>
    </div>
    
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
                            <li><a href="about.php" class="active"><?php echo t('عن الشركة', 'About'); ?></a></li>
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
        
        <!-- Page Title -->
        <div class="page-title" style="text-align: center; margin-top: 20px;">
            <div class="container">
                <h1 style="margin: 0; padding: 10px 0;"><?php echo t('عن الشركة', 'About Us'); ?></h1>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="images/about us.jpg" alt="<?php echo t('عن الشركة', 'About Us'); ?>" style="max-width: 100%; height: auto; border-radius: 8px;">
                </div>
                <div class="about-text">
                    <h2><?php echo t('من نحن', 'Who We Are'); ?></h2>
                    <p><?php echo t('تأسست شركة MK Leveling Systems بهدف توفير حلول مبتكرة ومتقدمة في مجال تسوية بلاط الأرضيات والجدران. نحن نفتخر بتقديم منتجات عالية الجودة تلبي احتياجات العملاء المحترفين والهواة على حد سواء.', 'MK Leveling Systems was established to provide innovative and advanced solutions in the field of floor and wall tile leveling. We take pride in offering high-quality products that meet the needs of both professional and amateur customers.'); ?></p>
                    <p><?php echo t('منتجاتنا مصممة بدقة لتضمن نتائج مثالية في كل مشروع، مع التركيز على سهولة الاستخدام والمتانة والكفاءة. فريقنا من الخبراء يعمل باستمرار على تطوير وتحسين منتجاتنا لتواكب أحدث التقنيات في الصناعة.', 'Our products are precisely designed to ensure perfect results in every project, with a focus on ease of use, durability, and efficiency. Our team of experts continuously works on developing and improving our products to keep up with the latest technologies in the industry.'); ?></p>
                    <p><?php echo t('نؤمن بأن الجودة والدقة هما أساس النجاح في أي مشروع بناء، لذلك نحرص على تقديم منتجات تساعدك في تحقيق أفضل النتائج بأقل جهد ووقت ممكن.', 'We believe that quality and precision are the foundation of success in any construction project, which is why we are committed to providing products that help you achieve the best results with minimal effort and time.'); ?></p>
                </div>
                
                <!-- Vision and Mission Section Below Image -->
                <div class="vision-mission" style="display: flex; gap: 30px; margin: 60px 0; flex-wrap: wrap; justify-content: space-between;">
                    <div style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden; border-right: 4px solid #3498db;">
                        <div style="font-size: 2.5rem; color: #3498db; margin-bottom: 20px;">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 1.5rem; position: relative; padding-bottom: 10px;">
                            <?php echo t('رؤيتنا', 'Our Vision'); ?>
                            <span style="position: absolute; bottom: 0; right: 0; width: 50px; height: 3px; background: #3498db;"></span>
                        </h3>
                        <p style="color: #555; line-height: 1.7; margin: 0;">
                            <?php echo t('أن نكون الخيار الأول للعملاء في مجال أنظمة تسوية البلاط من خلال تقديم منتجات عالية الجودة وخدمة عملاء متميزة.', 'To be the first choice for customers in tile leveling systems by providing high-quality products and excellent customer service.'); ?>
                        </p>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden; border-right: 4px solid #e74c3c;">
                        <div style="font-size: 2.5rem; color: #e74c3c; margin-bottom: 20px;">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 1.5rem; position: relative; padding-bottom: 10px;">
                            <?php echo t('رسالتنا', 'Our Mission'); ?>
                            <span style="position: absolute; bottom: 0; right: 0; width: 50px; height: 3px; background: #e74c3c;"></span>
                        </h3>
                        <p style="color: #555; line-height: 1.7; margin: 0;">
                            <?php echo t('توفير أحدث التقنيات والحلول المبتكرة في مجال تسوية البلاط لضمان أعلى مستويات الجودة والدقة في كل مشروع.', 'Providing the latest technologies and innovative solutions in tile leveling to ensure the highest levels of quality and precision in every project.'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3><?php echo t('جودة عالية', 'High Quality'); ?></h3>
                    <p><?php echo t('منتجاتنا مصنوعة من أفضل المواد الخام لضمان المتانة والأداء المتميز.', 'Our products are made from the finest raw materials to ensure durability and outstanding performance.'); ?></p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3><?php echo t('ابتكار', 'Innovation'); ?></h3>
                    <p><?php echo t('نعمل باستمرار على تطوير منتجاتنا لتلبية أحدث متطلبات السوق.', 'We continuously work on developing our products to meet the latest market requirements.'); ?></p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3><?php echo t('دعم فني', 'Technical Support'); ?></h3>
                    <p><?php echo t('فريق دعم فني متكامل لمساعدتك في أي استفسارات أو مشاكل تواجهك.', 'Integrated technical support team to assist you with any inquiries or issues you may face.'); ?></p>
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
        function switchLanguage(lang) {
            window.location.href = window.location.pathname + '?lang=' + lang;
        }
    </script>
</body>
</html>
