<?php
session_start();

// Cek apakah file config ada
if(!file_exists(__DIR__ . '/config/database.php')) {
    die('Error: File config/database.php tidak ditemukan. Silakan buat folder "config" dan file "database.php" terlebih dahulu.');
}

require_once __DIR__ . '/config/database.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e3a8a">
    <title>Login - YPOK Management System</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/images/LOGO YPOK NO BACKGROUND.png">
    <link rel="stylesheet" href="assets/css/login.css">
</head> 
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-box">
                <div class="logo-section">
                    <div class="logo">
                        <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo">
                    </div>
                    <h2>YPOK Management</h2>
                    <p class="subtitle">Silakan login untuk melanjutkan</p>
                </div>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">⚠️</span>
                        <span class="alert-text">
                            <?php 
                            switch($_GET['error']) {
                                case 'empty':
                                    echo 'Username dan password harus diisi';
                                    break;
                                case 'wrong':
                                    echo 'Password yang Anda masukkan salah';
                                    break;
                                case 'notfound':
                                    echo 'Username tidak ditemukan';
                                    break;
                                case 'db':
                                    echo 'Terjadi kesalahan database';
                                    break;
                                default:
                                    echo 'Username atau password salah';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['logout'])): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon">✓</span>
                        <span class="alert-text">Anda telah berhasil logout</span>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon">✓</span>
                        <span class="alert-text">Registrasi berhasil! Silakan login</span>
                    </div>
                <?php endif; ?>
                
                <form action="/actions/login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                            <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <span>Login</span>
                        <span class="btn-arrow">→</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span>atau</span>
                </div>
                
                <a href="register.php" class="btn-register">
                    <span>➕</span>
                    <span>Daftar Akun Baru</span>
                </a>
                
                <a href="guest_dashboard.php" class="btn-guest">
                    <span>👤</span>
                    <span>Login sebagai Tamu</span>
                </a>
                
                <div class="login-footer">
                    <p class="default-info">Login Default:</p>
                    <div class="default-credentials">
                        <span><strong>Username:</strong> admin</span>
                        <span><strong>Password:</strong> admin123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
        
        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
                        navigator.serviceWorker.register('/sw.js')
                .catch(() => {});
    });
  }
</script>
</body>
</html>
