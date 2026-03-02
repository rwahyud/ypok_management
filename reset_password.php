<?php
/**
 * GENERATE PASSWORD HASH & UPDATE USER ADMIN
 * Script untuk reset password admin ke "admin123"
 */

require_once 'config/supabase.php';

echo "<h2>🔐 Reset Password Admin</h2>";
echo "<hr>";

// Password baru yang diinginkan
$new_password = "admin123";
$username = "admin";

// Generate hash
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<strong>Password baru:</strong> $new_password<br>";
echo "<strong>Hash:</strong> $password_hash<br><br>";

try {
    // Update password di database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$password_hash, $username]);
    
    if($stmt->rowCount() > 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "✅ <strong>PASSWORD BERHASIL DIRESET!</strong><br><br>";
        echo "Login dengan:<br>";
        echo "- Username: <strong>$username</strong><br>";
        echo "- Password: <strong>$new_password</strong><br>";
        echo "</div>";
        
        // Verifikasi
        $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        echo "<h3>Verifikasi:</h3>";
        echo "Username: {$user['username']}<br>";
        echo "Hash tersimpan: " . substr($user['password'], 0, 30) . "...<br>";
        echo "Cocok: " . (password_verify($new_password, $user['password']) ? '✅ YA' : '❌ TIDAK') . "<br>";
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "❌ User '$username' tidak ditemukan!";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<a href='../index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>← Kembali ke Login</a>";
?>
