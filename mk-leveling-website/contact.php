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

// معالجة نموذج الاتصال
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // التحقق من صحة البيانات
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = t('الرجاء ملء جميع الحقول المطلوبة', 'Please fill in all required fields');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = t('البريد الإلكتروني غير صالح', 'Invalid email address');
    } else {
        // هنا يمكنك إضافة كود إرسال البريد الإلكتروني أو حفظ الرسالة في قاعدة البيانات
        // على سبيل المثال:
        /*
        $to = $contact_email;
        $email_subject = "رسالة جديدة من نموذج الاتصال: $subject";
        $email_body = "
            <h2>رسالة جديدة من $name</h2>
            <p><strong>الاسم:</strong> $name</p>
            <p><strong>البريد الإلكتروني:</strong> $email</p>
            <p><strong>الهاتف:</strong> $phone</p>
            <p><strong>الموضوع:</strong> $subject</p>
            <p><strong>الرسالة:</strong></p>
            <p>$message</p>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $email . "\r\n";
        
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success_message = t('شكراً لتواصلكم معنا. سنرد عليكم في أقرب وقت ممكن.', 'Thank you for contacting us. We will get back to you as soon as possible.');
        } else {
            $error_message = t('حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى لاحقاً.', 'An error occurred while sending the message. Please try again later.');
        }
        */
        
        // في الوقت الحالي، سنعرض رسالة نجاح افتراضية
        $success_message = t('شكراً لتواصلكم معنا. سنرد عليكم في أقرب وقت ممكن.', 'Thank you for contacting us. We will get back to you as soon as possible.');
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?> - <?php echo t('اتصل بنا', 'Contact Us'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Contact form styling */
        .contact-form {
            background: #e8f2ff !important;  /* Force light blue background */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            border: 1px solid #d0e3ff !important;
            height: 100%;
        }
        
        /* Contact icons styling */
        .contact-item .contact-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: #e8f2ff;  /* Matching form background */
            color: #0066cc;      /* Blue icon color */
            border: 2px solid #d0e3ff;
            border-radius: 50%;
            margin-left: 15px;
            font-size: 20px;
            position: relative;
        }
        
        .contact-item .contact-icon i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            padding: 0;
            line-height: 1;
            width: 20px;
            text-align: center;
        }
        
        /* Ensure form elements have proper background */
        .contact-form input,
        .contact-form textarea {
            background: #f8fbff !important;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .contact-text h3 {
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .contact-text p {
            color: #666;
            margin: 0;
        }
        
        /* Update map container */
        .map-container iframe {
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 50px 0;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
            background: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .contact-form {
            flex: 2;
            min-width: 300px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            flex-shrink: 0;
        }
        
        .contact-text h3 {
            margin: 0 0 5px;
            color: var(--dark-text);
        }
        
        .contact-text p {
            margin: 0;
            color: var(--light-text);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #2980b9;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .map-container {
            margin-top: 50px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }
            
            .contact-info, .contact-form {
                width: 100%;
            }
        }
    </style>
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
                    <div class="logo" style="display: flex; align-items: center; justify-content: center; flex-direction: row-reverse; gap: 15px;">
                        <a href="index.php" style="display: flex; align-items: center; text-decoration: none;">
                            <img src="images/logo.png" alt="MK Leveling Systems Logo" class="header-logo" style="max-width: 80px; height: auto;">
                            <div class="logo-text" style="text-align: right; margin-right: 10px;">
                                <div class="top" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50;">MK LEVELING</div>
                                <div class="bottom" style="font-size: 1.2rem; color: #7f8c8d;">SYSTEMS</div>
                            </div>
                        </a>
                    </div>
                    
                    <nav>
                        <ul>
                            <li><a href="index.php"><?php echo t('الرئيسية', 'Home'); ?></a></li>
                            <li><a href="about.php"><?php echo t('عن الشركة', 'About'); ?></a></li>
                            <li><a href="products.php"><?php echo t('المنتجات', 'Products'); ?></a></li>
                            <li><a href="contact.php" class="active"><?php echo t('اتصل بنا', 'Contact'); ?></a></li>
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
        <div class="page-title" style="text-align: center; margin-top: 60px; margin-bottom: 30px;">
            <div class="container">
                <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0; padding: 20px 0; position: relative; display: inline-block;">
                    <?php echo t('اتصل بنا', 'Contact Us'); ?>
                    <span style="position: absolute; bottom: 10px; right: 0; width: 80px; height: 3px; background: #3498db; left: 50%; transform: translateX(-50%);"></span>
                </h1>
            </div>
        </div>
    </header>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="contact-container">
                <div class="contact-info">
                    <h2><?php echo t('معلومات الاتصال', 'Contact Information'); ?></h2>
                    <p><?php echo t('نحن هنا لمساعدتك والرد على استفساراتك. لا تتردد في التواصل معنا عبر أي من القنوات التالية:', 'We are here to help and answer any questions you might have. Feel free to reach out to us through any of the following channels:'); ?></p>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h3><?php echo t('العنوان', 'Address'); ?></h3>
                            <p><?php echo htmlspecialchars($company_address); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h3><?php echo t('الهاتف', 'Phone'); ?></h3>
                            <p><?php echo htmlspecialchars($contact_phone); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h3><?php echo t('البريد الإلكتروني', 'Email'); ?></h3>
                            <p><?php echo htmlspecialchars($contact_email); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h3><?php echo t('ساعات العمل', 'Working Hours'); ?></h3>
                            <p><?php echo t('السبت - الخميس: 8 صباحاً - 5 مساءً', 'Saturday - Thursday: 8 AM - 5 PM'); ?><br>
                            <?php echo t('الجمعة: مغلق', 'Friday: Closed'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2><?php echo t('أرسل لنا رسالة', 'Send Us a Message'); ?></h2>
                    <p><?php echo t('سنكون سعداء بالرد على استفساراتك في أسرع وقت ممكن. يرجى ملء النموذج أدناه وسنعاود الاتصال بك قريباً.', 'We would be happy to answer your questions as soon as possible. Please fill out the form below and we will get back to you soon.'); ?></p>
                    
                    <form action="contact.php" method="POST">
                        <div class="form-group">
                            <label for="name"><?php echo t('الاسم الكامل', 'Full Name'); ?> *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><?php echo t('البريد الإلكتروني', 'Email Address'); ?> *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><?php echo t('رقم الهاتف', 'Phone Number'); ?></label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject"><?php echo t('الموضوع', 'Subject'); ?></label>
                            <input type="text" id="subject" name="subject" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="message"><?php echo t('الرسالة', 'Message'); ?> *</label>
                            <textarea id="message" name="message" class="form-control" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit"><?php echo t('إرسال الرسالة', 'Send Message'); ?></button>
                    </form>
                </div>
            </div>
            
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3386.280427042478!2d35.84803031516158!3d31.95394953457007!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x151b5f8e8c582f4d%3A0x3d9f3a9b4a1e1b9c!2sAmman%20West%2C%20Jordan!5e0!3m2!1sen!2sjo!4v1620000000000!5m2!1sen!2sjo" allowfullscreen="" loading="lazy"></iframe>
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
</html>
