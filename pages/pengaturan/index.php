<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if(!$user) {
    header('Location: ../../actions/logout.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - YPOK Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 20px auto;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }
        
        .settings-header h1 {
            margin: 0 0 10px;
            font-size: 28px;
            font-weight: 700;
        }
        
        .settings-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .settings-tabs {
            display: flex;
            background: white;
            border-bottom: 2px solid #e5e7eb;
            gap: 0;
        }
        
        .settings-tab {
            flex: 1;
            padding: 16px 24px;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .settings-tab:hover {
            background: #f9fafb;
            color: #4b5563;
        }
        
        .settings-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: #f9fafb;
        }
        
        .settings-content {
            background: white;
            padding: 30px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-section {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .profile-photo {
            flex-shrink: 0;
        }
        
        .photo-wrapper {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #e5e7eb;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            font-weight: 700;
            position: relative;
        }
        
        .photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-upload-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        
        .photo-upload-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .profile-info {
            flex: 1;
        }
        
        .info-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            width: 140px;
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }
        
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 14px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #10b981;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge.admin {
            background: #8b5cf6;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-row.full {
            grid-template-columns: 1fr;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group label span {
            color: #ef4444;
            margin-left: 2px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input[readonly] {
            background: #f3f4f6;
            cursor: not-allowed;
        }
        
        .password-input-wrapper {
            position: relative;
        }
        
        .password-input-wrapper input {
            padding-right: 50px;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #6b7280;
            transition: color 0.3s;
        }
        
        .toggle-password:hover {
            color: #374151;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        
        .password-strength-bar.weak {
            width: 33%;
            background: #ef4444;
        }
        
        .password-strength-bar.medium {
            width: 66%;
            background: #f59e0b;
        }
        
        .password-strength-bar.strong {
            width: 100%;
            background: #10b981;
        }
        
        .password-hint {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .btn-primary {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            padding: 12px 30px;
            background: #f3f4f6;
            color: #4b5563;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php include '../../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">⚙️ Pengaturan Akun</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
            </div>
        </div>
        
        <div class="settings-container">
            <!-- Alerts -->
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success" id="successAlert">
                    <span style="font-size: 20px;">✅</span>
                    <span>
                        <?php 
                        if($_GET['success'] == 'profile') {
                            echo 'Profil berhasil diperbarui!';
                        } elseif($_GET['success'] == 'password') {
                            echo 'Password berhasil diubah!';
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error" id="errorAlert">
                    <span style="font-size: 20px;">❌</span>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="settings-header">
                <h1>⚙️ Pengaturan Akun</h1>
                <p>Kelola informasi profil dan keamanan akun Anda</p>
            </div>
            
            <div class="settings-tabs">
                <button class="settings-tab active" onclick="switchTab('profile')">
                    👤 Profil Saya
                </button>
                <button class="settings-tab" onclick="switchTab('password')">
                    🔒 Keamanan
                </button>
            </div>
            
            <div class="settings-content">
                <!-- Tab Profil -->
                <div id="profile-panel" class="tab-panel active">
                    <div class="profile-section">
                        <div class="profile-photo">
                            <div class="photo-wrapper">
                                <?php if(!empty($user['foto_profil']) && file_exists('../../' . $user['foto_profil'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($user['foto_profil']); ?>" alt="Foto Profil" id="previewPhoto">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <form id="photoForm" action="../../actions/update_profile.php" method="POST" enctype="multipart/form-data" style="display: none;">
                                <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*" onchange="handlePhotoUpload()">
                            </form>
                            <button type="button" class="photo-upload-btn" onclick="document.getElementById('fotoProfilInput').click()">
                                📷 Ubah Foto
                            </button>
                        </div>
                        
                        <div class="profile-info">
                            <div class="info-card">
                                <div class="info-row">
                                    <div class="info-label">Username</div>
                                    <div class="info-value"><strong><?php echo htmlspecialchars($user['username']); ?></strong></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Role</div>
                                    <div class="info-value">
                                        <span class="badge <?php echo $user['role']; ?>">
                                            <?php echo strtoupper($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        <span class="badge" style="background: <?php echo $user['status'] == 'active' ? '#10b981' : '#ef4444'; ?>">
                                            <?php echo $user['status'] == 'active' ? 'AKTIF' : 'NON-AKTIF'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Terdaftar sejak</div>
                                    <div class="info-value">
                                        <?php 
                                        $date = new DateTime($user['created_at']);
                                        echo $date->format('d F Y, H:i');
                                        ?>
                                    </div>
                                </div>
                                <?php if($user['last_login']): ?>
                                <div class="info-row">
                                    <div class="info-label">Login terakhir</div>
                                    <div class="info-value">
                                        <?php 
                                        $lastLogin = new DateTime($user['last_login']);
                                        echo $lastLogin->format('d F Y, H:i');
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <form action="../../actions/update_profile.php" method="POST">
                        <div class="form-section">
                            <div class="section-title">📝 Informasi Pribadi</div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <small style="color: #6b7280; font-size: 12px;">Username tidak dapat diubah</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Nama Lengkap <span>*</span></label>
                                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row full">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="alamat@email.com">
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn-primary">💾 Simpan Perubahan</button>
                            <button type="button" class="btn-secondary" onclick="window.location.reload()">↺ Batalkan</button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab Password -->
                <div id="password-panel" class="tab-panel">
                    <div class="alert alert-info">
                        <span style="font-size: 20px;">ℹ️</span>
                        <span>Gunakan password yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan simbol. Minimal 8 karakter.</span>
                    </div>
                    
                    <form action="../../actions/change_password.php" method="POST" id="passwordForm">
                        <div class="form-section">
                            <div class="section-title">🔐 Ubah Password</div>
                            
                            <div class="form-group">
                                <label>Password Lama <span>*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="old_password" id="oldPassword" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('oldPassword')">👁️</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Password Baru <span>*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="new_password" id="newPassword" required minlength="8" oninput="checkPasswordStrength()">
                                    <button type="button" class="toggle-password" onclick="togglePassword('newPassword')">👁️</button>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <div class="password-hint" id="strengthText">Masukkan password minimal 8 karakter</div>
                            </div>
                            
                            <div class="form-group">
                                <label>Konfirmasi Password Baru <span>*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="confirm_password" id="confirmPassword" required minlength="8" oninput="checkPasswordMatch()">
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">👁️</button>
                                </div>
                                <div class="password-hint" id="matchText"></div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn-primary" id="submitPasswordBtn">🔒 Ubah Password</button>
                            <button type="button" class="btn-secondary" onclick="document.getElementById('passwordForm').reset(); checkPasswordStrength(); checkPasswordMatch();">↺ Reset Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/app.js"></script>
    <script>
        // Tab Switching
        function switchTab(tab) {
            // Update tabs
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update panels
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(tab + '-panel').classList.add('active');
        }
        
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const btn = event.target;
            
            if(input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
        
        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if(password.length === 0) {
                strengthBar.className = 'password-strength-bar';
                strengthText.textContent = 'Masukkan password minimal 8 karakter';
                strengthText.style.color = '#6b7280';
                return;
            }
            
            let strength = 0;
            
            // Length check
            if(password.length >= 8) strength++;
            if(password.length >= 12) strength++;
            
            // Character variety
            if(/[a-z]/.test(password)) strength++;
            if(/[A-Z]/.test(password)) strength++;
            if(/[0-9]/.test(password)) strength++;
            if(/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if(strength <= 2) {
                strengthBar.className = 'password-strength-bar weak';
                strengthText.textContent = '❌ Password lemah - tambahkan huruf besar, angka, dan simbol';
                strengthText.style.color = '#ef4444';
            } else if(strength <= 4) {
                strengthBar.className = 'password-strength-bar medium';
                strengthText.textContent = '⚠️ Password cukup kuat - pertimbangkan menambah panjang atau variasi';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.className = 'password-strength-bar strong';
                strengthText.textContent = '✅ Password sangat kuat!';
                strengthText.style.color = '#10b981';
            }
        }
        
        // Check password match
        function checkPasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matchText = document.getElementById('matchText');
            const submitBtn = document.getElementById('submitPasswordBtn');
            
            if(confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if(newPassword === confirmPassword) {
                matchText.textContent = '✅ Password cocok';
                matchText.style.color = '#10b981';
                submitBtn.disabled = false;
            } else {
                matchText.textContent = '❌ Password tidak cocok';
                matchText.style.color = '#ef4444';
                submitBtn.disabled = true;
            }
        }
        
        // Handle photo upload preview
        function handlePhotoUpload() {
            const input = document.getElementById('fotoProfilInput');
            const file = input.files[0];
            
            if(file) {
                // Validate file type
                if(!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar!');
                    input.value = '';
                    return;
                }
                
                // Validate file size (max 2MB)
                if(file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    input.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoWrapper = document.querySelector('.photo-wrapper');
                    photoWrapper.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="previewPhoto">';
                };
                reader.readAsDataURL(file);
                
                // Auto submit form
                if(confirm('Upload foto profil ini?')) {
                    document.getElementById('photoForm').submit();
                } else {
                    input.value = '';
                    location.reload();
                }
            }
        }
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            
            if(successAlert) {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
            
            if(errorAlert) {
                errorAlert.style.transition = 'opacity 0.5s';
                errorAlert.style.opacity = '0';
                setTimeout(() => errorAlert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>
