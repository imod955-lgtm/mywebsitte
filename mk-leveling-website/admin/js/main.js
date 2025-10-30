document.addEventListener('DOMContentLoaded', function() {
    // تبديل القائمة الجانبية
    const toggleSidebar = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const adminContainer = document.querySelector('.admin-container');
    
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            adminContainer.classList.toggle('sidebar-collapsed');
            
            // حفظ حالة القائمة في localStorage
            if (sidebar.classList.contains('show')) {
                localStorage.setItem('sidebarState', 'expanded');
            } else {
                localStorage.setItem('sidebarState', 'collapsed');
            }
        });
    }
    
    // استرجاع حالة القائمة من localStorage
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        sidebar.classList.remove('show');
        adminContainer.classList.add('sidebar-collapsed');
    }
    
    // تفعيل dropdown القوائم
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            
            // إغلاق جميع القوائم المفتوحة
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdownMenu) {
                    menu.classList.remove('show');
                }
            });
            
            // تبديل القائمة الحالية
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            }
        });
    });
    
    // إغلاق القوائم عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    
    // تأكيد الحذف
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع عن هذه العملية.')) {
                e.preventDefault();
            }
        });
    });
    
    // عرض معاينة الصورة قبل الرفع
    const imageInputs = document.querySelectorAll('.image-preview-upload');
    
    imageInputs.forEach(input => {
        const previewId = input.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        
        if (preview) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    // تفعيل tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
