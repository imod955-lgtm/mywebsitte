<?php
$current_page = basename($_SERVER['PHP_SELF']);
// جلب عدد الرسائل غير المقروءة
$unread_count = 0;
try {
    require_once __DIR__ . '/../../includes/db.php';
    if (isset($mysqli)) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
        if ($result) {
            $row = $result->fetch_assoc();
            $unread_count = $row['count'];
        }
    }
} catch (Exception $e) {
    // تجاهل الخطأ
}
?>
<aside class="sidebar">
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <img src="https://ui-avatars.com/api/?name=<?php echo isset($_SESSION['admin_name']) ? urlencode($_SESSION['admin_name']) : 'Admin'; ?>&background=ffffff&color=1e3a8a" alt="صورة المشرف">
        </div>
        <div class="profile-info">
            <h4><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'المشرف'; ?></h4>
            <span>مدير النظام</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="menu-list">
            <li class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="/mk-leveling-website/admin/dashboard.php">
                    <i class="menu-icon fas fa-home"></i>
                    <span class="menu-text">الرئيسية</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo in_array($current_page, ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : ''; ?>">
                <a href="/mk-leveling-website/admin/products.php">
                    <i class="menu-icon fas fa-box"></i>
                    <span class="menu-text">المنتجات</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
                <a href="/mk-leveling-website/admin/messages.php">
                    <i class="menu-icon fas fa-envelope"></i>
                    <span class="menu-text">الرسائل</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="menu-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page == 'change_password.php' ? 'active' : ''; ?>">
                <a href="/mk-leveling-website/admin/change_password.php">
                    <i class="menu-icon fas fa-key"></i>
                    <span class="menu-text">تغيير كلمة المرور</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo in_array($current_page, ['users/index.php', 'users/add.php', 'users/edit.php']) ? 'active' : ''; ?>">
                <a href="/mk-leveling-website/admin/users/">
                    <i class="menu-icon fas fa-users"></i>
                    <span class="menu-text">إدارة المستخدمين</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="/mk-leveling-website/admin/logout.php" style="color: #ff6b6b;">
                    <i class="menu-icon fas fa-sign-out-alt"></i>
                    <span class="menu-text">تسجيل الخروج</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<style>
/* تنسيقات القائمة الجانبية */
:root {
    --sidebar-width: 250px;
    --primary-color: #1e40af !important;  /* أزرق داكن */
    --primary-light: #1e3a8a !important;  /* لون أغمق قليلاً */
    --text-color: #ffffff !important;     /* نص أبيض */
    --hover-bg: rgba(255, 255, 255, 0.1) !important;
    --active-bg: rgba(255, 255, 255, 0.2) !important;
    --border-color: rgba(255, 255, 255, 0.1) !important;
}

/* الخط */
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap');

/* التنسيق الأساسي */
.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    right: 0;
    top: 0;
    background: var(--primary-color) !important;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    font-family: 'Tajawal', sans-serif;
    color: var(--text-color);
}

/* ملف التعريف */
.sidebar-profile {
    padding: 20px;
    text-align: center;
    background: rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    margin-bottom: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.profile-info h4 {
    margin: 5px 0 0;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
}

.profile-info span {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.8rem;
    background: rgba(255, 255, 255, 0.15);
    padding: 2px 10px;
    border-radius: 10px;
    display: inline-block;
    margin-top: 5px;
}

/* القائمة */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    margin: 2px 0;
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    font-weight: 500;
    position: relative;
}

.menu-item a:hover {
    background: var(--hover-bg);
    color: #fff;
    padding-right: 25px;
}

.menu-item.active > a {
    background: var(--active-bg);
    color: #fff;
    font-weight: 600;
    border-right: 3px solid #fff;
}

.menu-icon {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 12px;
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.2s;
}

.menu-text {
    flex: 1;
}

.menu-badge {
    background: #ef4444;
    color: #fff;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 10px;
    margin-right: 8px;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

/* إخفاء شريط التمرير */
.sidebar-nav::-webkit-scrollbar {
    width: 0;
    background: transparent;
}

/* تأثيرات الحركة */
.menu-item a {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* تحسينات للهواتف */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(100%);
        z-index: 1050;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
}
.sidebar-footer {
    padding: 15px 20px;
    border-top: 1px solid #f0f0f0;
    background: #f9fafc;
}
.theme-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 0;
}

.theme-icon {
    font-size: 1rem;
    color: #666;
}

.theme-text {
    font-size: 0.85rem;
    color: #555;
}

.version {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: #888;
    padding-top: 10px;
    border-top: 1px solid #eee;
    margin-top: 10px;
}

.version i {
    margin-left: 5px;
    font-size: 0.7rem;
}

/* تأثيرات الحركة */
.sidebar-nav li a {
    transition: all 0.2s ease-in-out;
}

/* التجاوب مع الشاشات الصغيرة */
@media (max-width: 992px) {
    .sidebar {
        right: -260px;
    }
    
    .sidebar.show {
        right: 0;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
}
</style>

<script>
// تفعيل/تعطيل القائمة الجانبية على الأجهزة المحمولة
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.toggle-sidebar');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !e.target.classList.contains('toggle-sidebar')) {
            sidebar.classList.remove('show');
        }
    });
    
    // تفعيل/تعطيل الوضع المظلم
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (darkModeSwitch) {
        darkModeSwitch.addEventListener('change', function() {
            document.body.classList.toggle('dark-mode');
            // يمكنك حفظ تفضيل المستخدم هنا
        });
    }
});
</script>
