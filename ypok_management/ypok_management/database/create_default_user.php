<?php
require_once '../config/database.php';

// Check if users table exists
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch(PDOException $e) {
    echo "Tabel users belum ada. Jalankan SQL untuk membuat tabel terlebih dahulu.<br>";
    exit();
}

// Check if admin user exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute(['admin']);
$exists = $stmt->fetchColumn();

if($exists > 0) {
    echo "User admin sudah ada.<br>";
    echo "<a href='../index.php'>Login Sekarang</a>";
    exit();
}

// Create default admin user
$username = 'admin';
$password = 'admin123'; // Password default
$nama_lengkap = 'Administrator';
$role = 'admin';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $hashed_password, $nama_lengkap, $role]);
    
    echo "<h2>✅ User Default Berhasil Dibuat!</h2>";
    echo "<p><strong>Username:</strong> $username</p>";
    echo "<p><strong>Password:</strong> $password</p>";
    echo "<p><strong>Role:</strong> $role</p>";
    echo "<br>";
    echo "<a href='../index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Sekarang</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
