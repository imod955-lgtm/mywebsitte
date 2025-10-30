<?php
// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db.php';

// Handle message deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    if ($message_id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success_message'] = 'تم حذف الرسالة بنجاح';
        header('Location: messages.php');
        exit;
    }
}

// Handle marking message as read/unread
if (isset($_GET['action']) && isset($_GET['id']) && ($_GET['action'] === 'read' || $_GET['action'] === 'unread')) {
    $message_id = (int)$_GET['id'];
    $is_read = ($_GET['action'] === 'read') ? 1 : 0;
    
    if ($message_id > 0) {
        $stmt = $mysqli->prepare("UPDATE contact_messages SET is_read = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_read, $message_id);
        $stmt->execute();
        $stmt->close();
        
        header('Location: messages.php');
        exit;
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the query
$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
$types = "";

if ($status_filter === 'read') {
    $query .= " AND is_read = 1";
} elseif ($status_filter === 'unread') {
    $query .= " AND is_read = 0";
}

if (!empty($search_query)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute the query
$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();

// Get unread messages count
$unread_count = 0;
$unread_result = $mysqli->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
if ($unread_result) {
    $unread_count = $unread_result->fetch_assoc()['count'];
    $unread_result->free();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرسائل - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .message-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-right: 4px solid #3498db;
            transition: all 0.3s ease;
        }
        
        .message-card.unread {
            border-right-color: #e74c3c;
            background-color: #f9f9f9;
        }
        
        .message-card .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        
        .message-card .card-body {
            padding: 20px;
            display: none;
            border-top: 1px solid #eee;
        }
        
        .message-card.expanded .card-body {
            display: block;
        }
        
        .message-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }
        
        .message-meta i {
            margin-left: 5px;
            color: #777;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-unread {
            background-color: #fde8e8;
            color: #e74c3c;
        }
        
        .status-read {
            background-color: #e8f4fd;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1>الرسائل الواردة</h1>
                    <div class="header-actions">
                        <span class="badge <?php echo $unread_count > 0 ? 'danger' : 'success'; ?>" style="margin-left: 10px;">
                            <?php echo $unread_count; ?> رسالة غير مقروءة
                        </span>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <div class="filters">
                            <form action="" method="GET" class="filter-form">
                                <div class="form-group" style="margin-bottom: 0; margin-left: 15px;">
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>الكل</option>
                                        <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>غير مقروءة</option>
                                        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>مقروءة</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        
                        <form action="" method="GET" class="search-form">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="ابحث في الرسائل..." value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (!empty($search_query)): ?>
                                    <a href="messages.php" class="btn btn-outline" style="margin-right: 5px;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message-card <?php echo $message['is_read'] ? '' : 'unread'; ?>" id="message-<?php echo $message['id']; ?>">
                                    <div class="card-header" onclick="toggleMessage(<?php echo $message['id']; ?>)">
                                        <div class="message-info">
                                            <h4 style="margin: 0 0 5px 0; font-size: 16px;">
                                                <?php if (!$message['is_read']): ?>
                                                    <span class="badge danger" style="margin-left: 10px;">جديد</span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($message['subject']); ?>
                                            </h4>
                                            <div class="message-meta">
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?></span>
                                                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                                                <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="message-actions">
                                            <?php if ($message['is_read']): ?>
                                                <a href="?action=unread&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline" title="وضع علامة كمقروءة">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=read&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline" title="وضع علامة كمقروءة">
                                                    <i class="fas fa-envelope-open-text"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-sm btn-outline" title="الرد">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?php echo $message['id']; ?>)" class="btn btn-sm btn-outline danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="message-content">
                                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                        </div>
                                        <div class="message-footer" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                                <i class="fas fa-reply"></i> رد
                                            </a>
                                            <span class="text-muted" style="margin-right: 15px;">
                                                <?php if ($message['is_read']): ?>
                                                    <i class="fas fa-check-circle"></i> مقروءة
                                                <?php else: ?>
                                                    <i class="far fa-envelope"></i> غير مقروءة
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>لا توجد رسائل لعرضها</p>
                                <?php if (!empty($search_query) || $status_filter !== 'all'): ?>
                                    <a href="messages.php" class="btn btn-outline">
                                        <i class="fas fa-undo"></i> عرض كل الرسائل
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($messages) && count($messages) > 10): ?>
                        <div class="card-footer" style="padding: 15px 20px; border-top: 1px solid #eee; text-align: center;">
                            <div class="pagination">
                                <a href="#" class="btn btn-outline" disabled><i class="fas fa-chevron-right"></i> السابق</a>
                                <span class="pagination-info">الصفحة 1 من 5</span>
                                <a href="#" class="btn btn-outline">التالي <i class="fas fa-chevron-left"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
    
    <script>
    // Toggle sidebar
    document.querySelector('.toggle-sidebar').addEventListener('click', function() {
        document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
    });
    
    // Toggle message expansion
    function toggleMessage(id) {
        const message = document.getElementById('message-' + id);
        message.classList.toggle('expanded');
        
        // Mark as read when expanded
        if (message.classList.contains('expanded') && message.classList.contains('unread')) {
            // Send AJAX request to mark as read
            fetch(`messages.php?action=read&id=${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.ok) {
                    message.classList.remove('unread');
                    // Update unread count
                    const unreadBadge = document.querySelector('.badge.danger');
                    if (unreadBadge) {
                        const count = parseInt(unreadBadge.textContent) - 1;
                        if (count > 0) {
                            unreadBadge.textContent = count + ' رسالة غير مقروءة';
                        } else {
                            unreadBadge.textContent = '0 رسالة غير مقروءة';
                            unreadBadge.classList.remove('danger');
                            unreadBadge.classList.add('success');
                        }
                    }
                }
            });
        }
    }
    
    // Confirm message deletion
    function confirmDelete(id) {
        if (confirm('هل أنت متأكد من حذف هذه الرسالة؟ لا يمكن التراجع عن هذا الإجراء.')) {
            window.location.href = 'messages.php?action=delete&id=' + id;
        }
        return false;
    }
    </script>
</body>
</html>
