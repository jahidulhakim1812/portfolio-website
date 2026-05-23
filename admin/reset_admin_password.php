<?php
require_once 'config.php';

// New password you want
$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update or insert admin user
$stmt = $pdo->prepare("DELETE FROM admin_users WHERE username = 'admin'");
$stmt->execute();

$stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
if ($stmt->execute(['admin', $hash])) {
    echo "✅ Admin user created/updated successfully!<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    echo '<a href="admin/login.php">Go to Admin Login</a>';
} else {
    echo "❌ Failed to insert. Check your database connection.";
}
?>