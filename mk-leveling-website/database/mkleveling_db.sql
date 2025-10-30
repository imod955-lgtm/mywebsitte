-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS `mkleveling_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mkleveling_db`;

-- جدول الإعدادات
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- إدراج الإعدادات الافتراضية
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name_ar', 'متجر MK'),
('site_name_en', 'MK Store'),
('site_description_ar', 'وصف الموقع بالعربية'),
('site_description_en', 'Site description in English'),
('contact_email', 'info@example.com'),
('phone', '+966501234567'),
('address_ar', 'الرياض، المملكة العربية السعودية'),
('address_en', 'Riyadh, Saudi Arabia'),
('logo_url', 'assets/images/logo.png'),
('favicon_url', 'assets/images/favicon.ico'),
('facebook_url', 'https://facebook.com'),
('twitter_url', 'https://twitter.com'),
('instagram_url', 'https://instagram.com'),
('youtube_url', 'https://youtube.com'),
('meta_title_ar', 'عنوان الموقع بالعربية'),
('meta_title_en', 'Site Title in English'),
('meta_description_ar', 'وصف الموقع بالعربية'),
('meta_description_en', 'Site description in English'),
('meta_keywords_ar', 'متجر, تسوق, إلكترونيات'),
('meta_keywords_en', 'store, shop, electronics');

-- جدول المستخدمين (للمشرفين)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- إدراج مستخدم افتراضي (كلمة المرور: admin123)
INSERT INTO `admin_users` (`username`, `password_hash`, `full_name`, `email`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin@example.com', 1);

-- جدول المنتجات
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description_ar` text,
  `description_en` text,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- إدراج بيانات اختبارية للمنتجات
INSERT INTO `products` (`name_ar`, `name_en`, `description_ar`, `description_en`, `price`, `image_url`, `is_active`) VALUES
('هاتف ذكي', 'Smartphone', 'هاتف ذكي بمواصفات عالية', 'High-end smartphone with latest features', 1999.99, 'assets/images/products/phone.jpg', 1),
('حاسوب محمول', 'Laptop', 'حاسوب محمول قوي للأعمال', 'Powerful laptop for business', 3499.99, 'assets/images/products/laptop.jpg', 1),
('سماعات لاسلكية', 'Wireless Headphones', 'سماعات لاسلكية بجودة صوت عالية', 'High-quality wireless headphones', 499.99, 'assets/images/products/headphones.jpg', 1);

-- جدول رسائل الاتصال
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول العملاء
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول الطلبات
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `shipping_address` text,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول تفاصيل الطلب
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
