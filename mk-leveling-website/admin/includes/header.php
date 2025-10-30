<?php
// تعريف متغير $page_title إذا لم يكن معرّفاً
if (!isset($page_title)) {
    $page_title = 'الرئيسية';
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /mk-leveling-website/admin/login.php');
    exit;
}
?>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Bootstrap RTL CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<!-- Custom Header CSS -->
<link rel="stylesheet" href="/mk-leveling-website/admin/css/header.css">
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <!-- Toggle Sidebar Button (Mobile) -->
        <button class="btn btn-link d-lg-none me-2" type="button" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <!-- Right Navigation -->
        <div class="d-flex align-items-center ms-auto">
            <!-- Notifications Dropdown -->
            <div class="dropdown notification-dropdown me-3 d-none d-lg-block">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                        <span class="visually-hidden">إشعارات غير مقروءة</span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="notification-header">
                        <h6 class="mb-0">الإشعارات</h6>
                        <a href="#" class="text-primary small">تعيين الكل كمقروء</a>
                    </div>
                    <div class="notification-item unread">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>طلب جديد</strong>
                            <small class="notification-time">منذ 5 دقائق</small>
                        </div>
                        <p class="mb-0 small">تم استلام طلب جديد من أحمد محمد</p>
                    </div>
                    <div class="notification-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>تحديث النظام</strong>
                            <small class="notification-time">منذ ساعة</small>
                        </div>
                        <p class="mb-0 small">يتوفر تحديث جديد للنظام</p>
                    </div>
                    <div class="text-center p-2">
                        <a href="#" class="text-primary">عرض جميع الإشعارات</a>
                    </div>
                </div>
            </div>
            
            <!-- User Dropdown -->
            <div class="dropdown user-dropdown">
                <a class="dropdown-toggle d-flex align-items-center p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: none !important; background: none !important;">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=<?php echo isset($_SESSION['admin_name']) ? urlencode($_SESSION['admin_name']) : 'Admin'; ?>&background=4361ee&color=fff" alt="صورة المستخدم" class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e3e6f0;">
                        <div class="me-2 text-end" style="line-height: 1.2;">
                            <div class="fw-bold" style="font-size: 0.85rem; color: #2c3e50;"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'المشرف'; ?></div>
                            <div style="font-size: 0.7rem; color: #6c757d;">مدير النظام</div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #6c757d;"></i>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
