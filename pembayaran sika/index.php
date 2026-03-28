<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biaya Kuliah - SIKA UNINDRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Header */
        .header {
            background-color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-icon {
            font-size: 24px;
            color: #666;
            cursor: pointer;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 50px;
            height: 50px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            font-size: 20px;
            color: #fff;
            background: #1e88e5;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #e0e0e0;
        }

        .user-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .user-details p {
            font-size: 12px;
            color: #666;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            width: 250px;
            height: calc(100vh - 80px);
            background: #fff;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            overflow-y: auto;
            padding-top: 20px;
        }

        .university-info {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .university-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }

        .university-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #666;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: #f5f7fa;
            color: #1e88e5;
        }

        .menu-item.active {
            background: #1e88e5;
            color: #fff;
            border-left-color: #1565c0;
        }

        .menu-item i {
            font-size: 18px;
            width: 20px;
        }

        .menu-item.has-dropdown::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 80px;
            padding: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab {
            padding: 12px 30px;
            background: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #666;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .tab:hover {
            background: #f5f7fa;
        }

        .tab.active {
            background: #1e88e5;
            color: #fff;
        }

        .tab i {
            font-size: 16px;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .card:nth-child(1)::before {
            background: #2196F3;
        }

        .card:nth-child(2)::before {
            background: #FF9800;
        }

        .card:nth-child(3)::before {
            background: #9C27B0;
        }

        .card:nth-child(4)::before {
            background: #2196F3;
        }

        .card-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: #FFA726;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        /* Detail Section */
        .detail-section {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .detail-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .detail-table {
            width: 100%;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
            padding-top: 20px;
            margin-top: 10px;
            border-top: 2px solid #e0e0e0;
            font-weight: 600;
        }

        .detail-label {
            color: #666;
            font-size: 15px;
        }

        .detail-value {
            color: #333;
            font-size: 15px;
            font-weight: 500;
        }

        .detail-row:last-child .detail-label,
        .detail-row:last-child .detail-value {
            font-size: 18px;
            color: #1e88e5;
        }

        .badge-new {
            background: #ff5252;
            color: #fff;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 5px;
        }

        .saldo-box {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: #fff;
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: absolute;
            right: 30px;
            top: 30px;
        }

        .saldo-box i {
            font-size: 24px;
        }

        .saldo-label {
            font-size: 12px;
            opacity: 0.9;
        }

        .saldo-value {
            font-size: 18px;
            font-weight: 600;
        }

        /* Notification Panel */
        .notification-panel {
            position: fixed;
            top: 80px;
            right: -400px;
            width: 400px;
            height: calc(100vh - 80px);
            background: #fff;
            box-shadow: -2px 0 10px rgba(0,0,0,0.15);
            transition: right 0.3s ease;
            z-index: 999;
            overflow-y: auto;
        }

        .notification-panel.active {
            right: 0;
        }

        .notification-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .notification-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .notification-list {
            padding: 0;
        }

        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 15px;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #e3f2fd;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.payment {
            background: #fff3e0;
        }

        .notification-icon.schedule {
            background: #e8f5e9;
        }

        .notification-icon.validation {
            background: #fce4ec;
        }

        .notification-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            color: #1e88e5;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            text-decoration: none;
        }

        .notification-title:hover {
            text-decoration: underline;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 13px;
            color: #666;
        }

        .notification-meta i {
            font-size: 12px;
        }

        .notification-footer {
            padding: 15px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .notification-footer a {
            color: #1e88e5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .notification-footer a:hover {
            text-decoration: underline;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff5252;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 998;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        .header-icon {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars menu-icon"></i>
            <div class="logo-container">
                <svg class="logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="50,10 90,90 10,90" fill="#1e88e5"/>
                    <circle cx="50" cy="50" r="15" fill="#fff"/>
                </svg>
            </div>
        </div>
        <div class="header-right">
            <div class="header-icon" id="notificationBtn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </div>
            <div class="header-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="user-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23ccc'/%3E%3Ccircle cx='50' cy='35' r='15' fill='%23999'/%3E%3Cpath d='M20,80 Q20,60 50,60 Q80,60 80,80' fill='%23999'/%3E%3C/svg%3E" class="user-avatar" alt="User">
                <div class="user-details">
                    <h4>RIZKI TRI WAHYUDI</h4>
                    <p>20224352606</p>
                </div>
                <i class="fas fa-chevron-down" style="color: #999;"></i>
            </div>
        </div>
    </div>

    <!-- Notification Panel -->
    <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <h3>Pemberitahuan</h3>
        </div>
        <div class="notification-list">
            <!-- Notification 1 - Payment Reminder -->
            <div class="notification-item unread">
                <div class="notification-icon payment">
                    <i class="fas fa-exclamation-circle" style="color: #FF9800; font-size: 24px;"></i>
                </div>
                <div class="notification-content">
                    <a href="#" class="notification-title">Batas Waktu Pembayaran Terakhir Tanggal 10 Maret 2026</a>
                    <div class="notification-meta">
                        <span><i class="fas fa-calendar"></i> 5 Maret 2026</span>
                        <span><i class="fas fa-user"></i> Admin SIKA</span>
                    </div>
                </div>
            </div>

            <!-- Notification 2 - Schedule Info -->
            <div class="notification-item">
                <div class="notification-icon schedule">
                    <svg width="50" height="50" viewBox="0 0 50 50">
                        <rect width="50" height="50" fill="#e8f5e9" rx="8"/>
                        <path d="M15,20 L35,20 M15,25 L30,25 M15,30 L35,30" stroke="#4CAF50" stroke-width="2"/>
                    </svg>
                </div>
                <div class="notification-content">
                    <a href="#" class="notification-title">Jadwal Pengisian KRS Genap 2025/2026</a>
                    <div class="notification-meta">
                        <span><i class="fas fa-calendar"></i> 11 Februari 2026</span>
                        <span><i class="fas fa-user"></i> Admin</span>
                    </div>
                </div>
            </div>

            <!-- Notification 3 - QR Code -->
            <div class="notification-item">
                <div class="notification-icon payment">
                    <svg width="50" height="50" viewBox="0 0 50 50">
                        <rect width="50" height="50" fill="#fff3e0" rx="8"/>
                        <rect x="10" y="10" width="12" height="12" fill="#FF9800"/>
                        <rect x="28" y="10" width="12" height="12" fill="#FF9800"/>
                        <rect x="10" y="28" width="12" height="12" fill="#FF9800"/>
                        <rect x="28" y="28" width="12" height="12" fill="#FF9800"/>
                    </svg>
                </div>
                <div class="notification-content">
                    <a href="#" class="notification-title">Ijazah yang Sudah Bisa Diambil</a>
                    <div class="notification-meta">
                        <span><i class="fas fa-calendar"></i> 10 Februari 2026</span>
                        <span><i class="fas fa-user"></i> Admin</span>
                    </div>
                </div>
            </div>

            <!-- Notification 4 - Validation -->
            <div class="notification-item">
                <div class="notification-icon validation">
                    <svg width="50" height="50" viewBox="0 0 50 50">
                        <rect width="50" height="50" fill="#fce4ec" rx="8"/>
                        <circle cx="25" cy="20" r="8" fill="#E91E63"/>
                        <path d="M15,35 Q25,30 35,35" fill="#E91E63"/>
                    </svg>
                </div>
                <div class="notification-content">
                    <a href="#" class="notification-title">Validasi Data Mahasiswa (BIODATA)</a>
                    <div class="notification-meta">
                        <span><i class="fas fa-calendar"></i> 11 Oktober 2025</span>
                        <span><i class="fas fa-user"></i> Admin</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="notification-footer">
            <a href="#">Lihat Semua</a>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="university-info">
            <svg class="university-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="#1e88e5" stroke="#1565c0" stroke-width="2"/>
                <text x="50" y="45" font-size="16" fill="#fff" text-anchor="middle" font-weight="bold">PGRI</text>
                <text x="50" y="65" font-size="10" fill="#fff" text-anchor="middle">UNINDRA</text>
            </svg>
            <div class="university-name">Universitas Indraprasta PGRI</div>
        </div>
        <a href="#" class="menu-item">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-user"></i>
            <span>Biodata</span>
        </a>
        <a href="#" class="menu-item has-dropdown">
            <i class="fas fa-book"></i>
            <span>KRS</span>
        </a>
        <a href="#" class="menu-item active">
            <i class="fas fa-credit-card"></i>
            <span>Biaya Kuliah</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-bookmark"></i>
            <span>Bahan & Tugas</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-calendar"></i>
            <span>Jadwal & Presensi</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-laptop"></i>
            <span>PA Online</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-edit"></i>
            <span>Kuesioner</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-star"></i>
            <span>Nilai</span>
        </a>
        <a href="#" class="menu-item has-dropdown">
            <i class="fas fa-file-alt"></i>
            <span>Pengajuan</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="saldo-box">
            <i class="fas fa-wallet"></i>
            <div>
                <div class="saldo-label">Saldo Anda</div>
                <div class="saldo-value">-</div>
            </div>
        </div>

        <h1 class="page-title">Biaya Kuliah</h1>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active">
                <i class="fas fa-receipt"></i>
                Tagihan
            </button>
            <button class="tab">
                <i class="fas fa-history"></i>
                Riwayat Pembayaran
            </button>
            <button class="tab">
                <i class="fas fa-book"></i>
                Rekap Pembayaran
            </button>
        </div>

        <!-- Cards -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-title">Total Tunggakan</div>
                <div class="card-value">Rp 1.800.000</div>
            </div>
            <div class="card">
                <div class="card-title">
                    Syarat KRS
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="card-value">-</div>
            </div>
            <div class="card">
                <div class="card-title">
                    Syarat UTS
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="card-value">-</div>
            </div>
            <div class="card">
                <div class="card-title">
                    Syarat UAS
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="card-value">-</div>
            </div>
        </div>

        <!-- Detail Section -->
        <div class="detail-section">
            <h2 class="detail-title">Rincian Tunggakan Pembayaran</h2>
            <div class="detail-table">
                <div class="detail-row">
                    <div class="detail-label">1. Biaya Pengajuan Tugas Akhir</div>
                    <div class="detail-value">Rp 750.000</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">2. Biaya Perkuliahan 9 SKS</div>
                    <div class="detail-value">Rp 650.000</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">3. Biaya Bebas Administrasi KKP</div>
                    <div class="detail-value">Rp 400.000</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total Tunggakan</div>
                    <div class="detail-value">Rp 1.800.000</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Notification Panel Toggle
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationPanel = document.getElementById('notificationPanel');
        const overlay = document.getElementById('overlay');

        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationPanel.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            notificationPanel.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Close notification panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationPanel.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationPanel.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Menu functionality
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!this.classList.contains('has-dropdown')) {
                    document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
