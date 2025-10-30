<?php
require_once __DIR__ . '/../includes/db.php';

// Disable foreign key checks temporarily
$mysqli->query('SET FOREIGN_KEY_CHECKS=0');

// Drop tables if they exist (be careful with this in production)
$tables = ['user_roles', 'role_permissions', 'users', 'roles', 'permissions'];
foreach ($tables as $table) {
    $mysqli->query("DROP TABLE IF EXISTS `$table`");
    echo "Dropped table $table if it existed<br>";
}

// Enable foreign key checks
$mysqli->query('SET FOREIGN_KEY_CHECKS=1');

// Read and execute the SQL file
$sql_file = dirname(__DIR__) . '/database/create_user_tables.sql';
if (!file_exists($sql_file)) {
    die("خطأ: ملف SQL غير موجود في المسار: $sql_file");
}
$sql = file_get_contents($sql_file);

if ($mysqli->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
        // If there are more results, continue
    } while ($mysqli->more_results() && $mysqli->next_result());
}

// Check for errors
if ($mysqli->error) {
    die("Error setting up database: " . $mysqli->error);
}

echo "Database setup completed successfully!<br>";

// Test the setup
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total users: " . $row['count'] . "<br>";
}

$result = $mysqli->query("SELECT u.username, r.name as role 
                         FROM users u 
                         JOIN user_roles ur ON u.id = ur.user_id 
                         JOIN roles r ON ur.role_id = r.id");

echo "<h3>Users and their roles:</h3>";
echo "<table border='1'>";
echo "<tr><th>Username</th><th>Role</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Add a link to go back to users page
echo "<p><a href='users/'>Go to Users Management</a></p>";
?>
