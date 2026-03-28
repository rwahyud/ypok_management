<?php
require_once '../config/database.php';

echo "<h2>🔍 Diagnosa & Fix Login</h2>";

// Check database connection
try {
    $pdo->query("SELECT 1");
    echo "<p>✅ Database connection: OK</p>";
} catch(PDOException $e) {
    echo "<p>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
    exit();
}

// Check if users table exists
try {
    $pdo->query("SELECT * FROM users LIMIT 1");
    echo "<p>✅ Table 'users': EXISTS</p>";
} catch(PDOException $e) {
    echo "<p>❌ Table 'users': NOT FOUND</p>";
    echo "<p>Jalankan SQL berikut di phpMyAdmin:</p>";
    echo "<pre style='background:#f0f0f0;padding:10px;'>
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
</pre>";
    exit();
}

// Check users
$stmt = $pdo->query("SELECT id, username, nama_lengkap, role, LENGTH(password) as pwd_length FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($users) == 0) {
    echo "<p>⚠️ No users found. Creating default admin...</p>";
    
    $username = 'admin';
    $password = 'admin123';
    $nama_lengkap = 'Administrator';
    $role = 'admin';
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashed, $nama_lengkap, $role]);
    
    echo "<p>✅ Default user created!</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
} else {
    echo "<h3>📋 Users List:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Password Length</th><th>Action</th></tr>";
    
    foreach($users as $user) {
        $isHashed = $user['pwd_length'] > 50 ? 'Hashed' : 'Plain Text';
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['nama_lengkap']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['pwd_length']} chars ({$isHashed})</td>";
        echo "<td><a href='?reset_password={$user['id']}'>Reset Password</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Reset password if requested
if(isset($_GET['reset_password'])) {
    $id = (int)$_GET['reset_password'];
    $new_password = 'admin123';
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $id]);
    
    echo "<p style='color:green;'>✅ Password for user ID {$id} has been reset to: <strong>admin123</strong></p>";
    echo "<p><a href='fix_login.php'>Refresh</a></p>";
}

echo "<hr>";
echo "<h3>🧪 Test Login:</h3>";
echo "<form method='POST'>";
echo "<p><input type='text' name='test_user' placeholder='Username' value='admin' style='padding:8px;'></p>";
echo "<p><input type='password' name='test_pass' placeholder='Password' value='admin123' style='padding:8px;'></p>";
echo "<p><button type='submit' name='test_login' style='padding:8px 20px;background:#4CAF50;color:white;border:none;cursor:pointer;'>Test Login</button></p>";
echo "</form>";

if(isset($_POST['test_login'])) {
    $test_user = $_POST['test_user'];
    $test_pass = $_POST['test_pass'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$test_user]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        if(password_verify($test_pass, $user['password'])) {
            echo "<p style='color:green;'>✅ TEST PASSED: Login successful!</p>";
            echo "<p>You can now login at <a href='../index.php'>index.php</a></p>";
        } else {
            echo "<p style='color:red;'>❌ TEST FAILED: Wrong password</p>";
            echo "<p>Password in DB length: " . strlen($user['password']) . " chars</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ TEST FAILED: User not found</p>";
    }
}
?>
