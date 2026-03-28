<?php
session_start();
// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="YPOK">
    <title>Registrasi - YPOK Management System</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/images/LOGO YPOK NO BACKGROUND.png">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-750x1334.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-828x1792.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-1125x2436.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-1170x2532.png" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-1179x2556.png" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-1242x2688.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="assets/splash/apple-splash-1290x2796.png" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container register-container">
            <div class="login-box">
                <div class="logo-section">
                    <div class="logo">
                        <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo">
                    </div>
                    <h2>Daftar Akun Baru</h2>
                    <p class="subtitle">Lengkapi form untuk membuat akun</p>
                </div>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">⚠️</span>
                        <span class="alert-text">
                            <?php 
                            switch($_GET['error']) {
                                case 'empty':
                                    echo 'Semua field harus diisi';
                                    break;
                                case 'username_exists':
                                    echo 'Username sudah digunakan';
                                    break;
                                case 'password_mismatch':
                                    echo 'Password dan konfirmasi password tidak sama';
                                    break;
                                case 'password_short':
                                    echo 'Password minimal 6 karakter';
                                    break;
                                default:
                                    echo 'Terjadi kesalahan, silakan coba lagi';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <form action="actions/register_action.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📝</span>
                            <input type="text" id="username" name="username" placeholder="Pilih username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                            <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Konfirmasi Password *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔐</span>
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                            <span class="toggle-password" onclick="togglePassword('password_confirm')">👁️</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <span>Daftar</span>
                        <span class="btn-arrow">→</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span>sudah punya akun?</span>
                </div>
                
                <a href="index.php" class="btn-register">
                    <span>←</span>
                    <span>Kembali ke Login</span>
                </a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script>
        function togglePassword(id) {
            const passwordField = document.getElementById(id);
            const toggleBtn = event.target;
            
            if(passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
        
        // Validate password match
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if(password !== confirm) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
            }
        });
    </script>
</body>
</html>
