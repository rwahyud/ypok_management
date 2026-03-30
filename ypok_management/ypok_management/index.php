<?php
session_start();

// Cek apakah file config ada
if(!file_exists(__DIR__ . '/config/database.php')) {
    die('Error: File config/database.php tidak ditemukan. Silakan buat folder "config" dan file "database.php" terlebih dahulu.');
}

require_once __DIR__ . '/config/database.php';

// Build app base path dynamically so redirects/forms work on both root and subfolder deployments.
$appBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($appBasePath === '/' || $appBasePath === '.') {
    $appBasePath = '';
}

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header('Location: ' . $appBasePath . '/pages/dashboard.php');
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
    <title>Login - YPOK Management System</title>
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
    <!-- Gunakan file minified untuk performa lebih baik -->
    <link rel="stylesheet" href="assets/css/login.min.css">
    <?php include __DIR__ . '/components/analytics.php'; ?>
</head> 
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-box">
                <div class="logo-section">
                    <div class="logo">
                        <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo" loading="lazy">
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
                
                <form action="<?php echo htmlspecialchars(($appBasePath ?: '') . '/actions/login.php'); ?>" method="POST" class="login-form">
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
    
    <!-- Gunakan file minified untuk performa lebih baik -->
    <script src="assets/js/app.min.js"></script>
            <!--
            Untuk cache-control asset statis, tambahkan aturan berikut di .htaccess (jika pakai Apache):
            <IfModule mod_expires.c>
                ExpiresActive On
                ExpiresByType image/png "access plus 1 year"
                ExpiresByType image/jpeg "access plus 1 year"
                ExpiresByType image/svg+xml "access plus 1 year"
                ExpiresByType text/css "access plus 1 month"
                ExpiresByType application/javascript "access plus 1 month"
            </IfModule>
    
            Untuk performa lebih baik, gunakan CDN untuk file di folder assets/ (misal jsDelivr, Cloudflare, atau BunnyCDN).
            -->
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

        // Fallback login flow for serverless environments: set signed auth cookie from browser.
        (function () {
            const form = document.querySelector('form.login-form');
            if (!form || !window.fetch) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const submitLabel = submitBtn ? submitBtn.querySelector('span:first-child') : null;

                if (submitBtn) submitBtn.disabled = true;
                if (submitLabel) submitLabel.textContent = 'Memproses...';

                try {
                    const response = await fetch(form.action + '?ajax=1', {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data && data.success) {
                        if (data.token) {
                            let cookie = 'ypok_auth=' + encodeURIComponent(data.token) + '; path=/; max-age=1800; SameSite=Lax';
                            if (location.protocol === 'https:') cookie += '; Secure';
                            document.cookie = cookie;
                        }
                        window.location.href = data.redirect || '<?php echo htmlspecialchars(($appBasePath ?: '') . '/pages/dashboard.php', ENT_QUOTES, 'UTF-8'); ?>';
                        return;
                    }

                    const err = (data && data.error) ? data.error : 'wrong';
                    window.location.href = '<?php echo htmlspecialchars(($appBasePath ?: '') . '/index.php', ENT_QUOTES, 'UTF-8'); ?>?error=' + encodeURIComponent(err);
                } catch (error) {
                    // Let the browser do regular submit if fetch/login API fails.
                    form.submit();
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitLabel) submitLabel.textContent = 'Login';
                }
            });
        })();
        
        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
