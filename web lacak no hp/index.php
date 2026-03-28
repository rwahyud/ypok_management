<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Proses Login
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Email atau password salah!';
    }
    $stmt->close();
    $conn->close();
}

// Proses Register
if (isset($_POST['register'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'member';
    $family_code = $_POST['family_code'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $conn = getDBConnection();
        
        // Generate family code untuk parent
        if ($role === 'parent' && empty($family_code)) {
            $family_code = generateFamilyCode();
        }
        
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert user dulu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, family_code) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hashed_password, $phone, $role, $family_code);
            
            if ($stmt->execute()) {
                // Dapatkan user ID yang baru dibuat
                $new_user_id = $conn->insert_id;
                
                // Jika parent, buat family group setelah user dibuat
                if ($role === 'parent') {
                    $family_name = $name . "'s Family";
                    $stmt2 = $conn->prepare("INSERT INTO family_groups (family_code, family_name, created_by) VALUES (?, ?, ?)");
                    $stmt2->bind_param("ssi", $family_code, $family_name, $new_user_id);
                    $stmt2->execute();
                    $stmt2->close();
                }
                
                $success = 'Registrasi berhasil! Silakan login.';
                if ($role === 'parent') {
                    $success .= ' Kode Keluarga Anda: <strong>' . $family_code . '</strong> (simpan untuk anggota keluarga lain)';
                }
            } else {
                $error = 'Registrasi gagal! Coba lagi.';
            }
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Location Tracker - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>🏠 Family Location Tracker</h1>
            <p class="subtitle">Pantau lokasi keluarga Anda dengan aman</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('login')">Login</button>
                <button class="tab-btn" onclick="showTab('register')">Daftar</button>
            </div>
            
            <!-- Form Login -->
            <form id="login-form" method="POST" class="auth-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" name="login" class="btn btn-primary">Masuk</button>
            </form>
            
            <!-- Form Register -->
            <form id="register-form" method="POST" class="auth-form" style="display: none;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama Anda">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>No. Telepon (Opsional)</label>
                    <input type="tel" name="phone" placeholder="08123456789">
                </div>
                <div class="form-group">
                    <label>Peran</label>
                    <select name="role" id="role-select" onchange="toggleFamilyCode()">
                        <option value="member">Anggota Keluarga</option>
                        <option value="parent">Orang Tua (Buat Keluarga Baru)</option>
                        <option value="child">Anak</option>
                    </select>
                </div>
                <div class="form-group" id="family-code-group">
                    <label>Kode Keluarga</label>
                    <input type="text" name="family_code" id="family-code" placeholder="Masukkan kode keluarga">
                    <small>Minta kode ini dari orang tua/admin keluarga</small>
                </div>
                <button type="submit" name="register" class="btn btn-primary">Daftar</button>
            </form>
            
            <div class="info-box">
                <h3>📍 Fitur Aplikasi:</h3>
                <ul>
                    <li>✅ Lacak lokasi anggota keluarga secara real-time</li>
                    <li>✅ Peta interaktif dengan marker lokasi</li>
                    <li>✅ Privacy control - nyalakan/matikan sharing</li>
                    <li>✅ Riwayat lokasi dan waktu terakhir update</li>
                    <li>✅ Aman dan terenkripsi</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tab) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const tabs = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(t => t.classList.remove('active'));
            
            if (tab === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                tabs[0].classList.add('active');
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                tabs[1].classList.add('active');
            }
        }
        
        function toggleFamilyCode() {
            const role = document.getElementById('role-select').value;
            const familyCodeGroup = document.getElementById('family-code-group');
            const familyCodeInput = document.getElementById('family-code');
            
            if (role === 'parent') {
                familyCodeGroup.style.display = 'none';
                familyCodeInput.required = false;
            } else {
                familyCodeGroup.style.display = 'block';
                familyCodeInput.required = true;
            }
        }
        
        // Initialize
        toggleFamilyCode();
    </script>
</body>
</html>
