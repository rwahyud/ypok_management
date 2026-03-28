<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Family Location Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .step ol {
            padding-left: 20px;
        }
        .step li {
            margin: 8px 0;
            color: #555;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 10px 0;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🔧 Setup Database</h1>
        <p class="subtitle">Family Location Tracker Installation</p>
        
        <?php
        $message = '';
        $error = '';
        
        if (isset($_POST['setup'])) {
            try {
                // Database credentials
                $host = $_POST['db_host'] ?? 'localhost';
                $user = $_POST['db_user'] ?? 'root';
                $pass = $_POST['db_pass'] ?? '';
                $dbname = 'family_tracker';
                
                // Connect without database
                $conn = new mysqli($host, $user, $pass);
                
                if ($conn->connect_error) {
                    throw new Exception("Koneksi gagal: " . $conn->connect_error);
                }
                
                // Create database
                $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
                $conn->select_db($dbname);
                
                // Create tables
                $sql = file_get_contents('database.sql');
                
                // Split multiple queries
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        if (!$conn->query($query)) {
                            throw new Exception("Error executing query: " . $conn->error);
                        }
                    }
                }
                
                $conn->close();
                
                $message = "✅ Database berhasil dibuat! Silakan akses <a href='index.php' style='color: #667eea; font-weight: bold;'>index.php</a> untuk mulai menggunakan aplikasi.";
                
            } catch (Exception $e) {
                $error = "❌ Error: " . $e->getMessage();
            }
        }
        ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="step">
            <h3>📋 Langkah Setup:</h3>
            <ol>
                <li>Pastikan XAMPP (Apache + MySQL) sudah running</li>
                <li>Isi form kredensial database di bawah</li>
                <li>Klik tombol "Setup Database"</li>
                <li>Setelah berhasil, akses index.php untuk login</li>
            </ol>
        </div>
        
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Database Host:</label>
                <input type="text" name="db_host" value="localhost" 
                       style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Database User:</label>
                <input type="text" name="db_user" value="root" 
                       style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Database Password:</label>
                <input type="password" name="db_pass" value="" placeholder="Kosongkan jika tidak ada password"
                       style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <button type="submit" name="setup" class="btn">🚀 Setup Database</button>
        </form>
        
        <div class="step" style="margin-top: 20px;">
            <h3>⚠️ Setup Manual (Alternatif):</h3>
            <p style="margin: 10px 0;">Jika auto-setup gagal, Anda bisa setup manual:</p>
            <ol>
                <li>Buka phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                <li>Buat database baru: <strong>family_tracker</strong></li>
                <li>Import file: <strong>database.sql</strong></li>
                <li>Selesai!</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px; color: #666;">
            <small>💡 Setelah setup berhasil, hapus file setup.php untuk keamanan</small>
        </div>
    </div>
</body>
</html>
