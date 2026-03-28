<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if database columns exist
$column_exists = false;
try {
    $columns = $pdo->query("SHOW COLUMNS FROM kegiatan LIKE 'tampil_di_berita'")->fetchAll();
    $column_exists = !empty($columns);
} catch(Exception $e) {
    // leave as false
}

// Handle toggle tampil_di_berita
if(isset($_POST['action']) && $_POST['action'] === 'toggle' && $column_exists) {
    try {
        $id = (int)$_POST['id'];
        $tampil = (int)$_POST['tampil'];
        
        $stmt = $pdo->prepare("UPDATE kegiatan SET tampil_di_berita = ? WHERE id = ?");
        $stmt->execute([$tampil, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
        exit();
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// Get all kegiatan if columns exist
$kegiatan_list = [];
$tampil_count = 0;

if($column_exists) {
    $kegiatan_list = $pdo->query("SELECT id, nama_kegiatan, tanggal_kegiatan, jenis_kegiatan, tampil_di_berita, status FROM kegiatan ORDER BY tanggal_kegiatan DESC")->fetchAll();
    $tampil_count = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE tampil_di_berita = true")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tampilan Kegiatan - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .setup-card {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 8px 24px rgba(217, 119, 6, 0.15);
        }

        .setup-card h2 {
            margin: 0 0 16px 0;
            color: white;
            font-size: 28px;
        }

        .setup-card p {
            margin: 0 0 12px 0;
            font-size: 16px;
            opacity: 0.95;
        }

        .setup-card p:last-of-type {
            opacity: 0.85;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .btn-setup {
            background: white;
            color: #d97706;
            border: none;
            padding: 14px 40px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-setup:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-setup:active {
            transform: translateY(-1px);
        }

        .btn-setup:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .migration-status {
            margin-top: 24px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 14px;
            line-height: 1.5;
        }
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 18px 30px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 26px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #6b7280;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-card h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            opacity: 0.95;
            font-weight: 600;
        }

        .info-card p {
            margin: 0;
            font-size: 13px;
            opacity: 0.85;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: center;
        }

        .stat-item {
            text-align: right;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-separator {
            font-size: 18px;
            font-weight: 300;
            opacity: 0.6;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }
            position: relative;
            display: inline-block;
            width: 56px;
            height: 32px;
            cursor: pointer;
        }

        .toggle-switch input {
            display: none;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 28px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #10b981;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(22px);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .date {
            color: #9ca3af;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .no-data svg {
            width: 60px;
            height: 60px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .toast-notification.success {
            border-left: 4px solid #10b981;
        }

        .toast-notification.error {
            border-left: 4px solid #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .info-card {
                flex-direction: column;
                gap: 15px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">📺 Kelola Tampilan Kegiatan</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>

        <div class="container">
            <?php if(!$column_exists): ?>
                <!-- Setup Page -->
                <div class="setup-card">
                    <h2>⚙️ Setup Diperlukan</h2>
                    <p>Database perlu diperbarui untuk menggunakan fitur ini.</p>
                    <p>Klik tombol di bawah untuk memulai migrasi database otomatis.</p>
                    <button class="btn-setup" onclick="runMigration()" id="migrationBtn">Jalankan Migrasi Database</button>
                    <div class="migration-status" id="migrationStatus" style="display: none;"></div>
                </div>
            <?php else: ?>
                <!-- Normal Page Content -->
                <div class="info-card">
                    <div>
                        <h3>Kegiatan Ditampilkan di Guest Dashboard</h3>
                        <p>Maksimal 3 kegiatan terbaru akan ditampilkan</p>
                    </div>
                    <div style="text-align: right;">
                        <div class="number"><?php echo $tampil_count; ?></div>
                        <p style="margin-top: 8px;">dari <?php echo count($kegiatan_list); ?> kegiatan</p>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Daftar Kegiatan</h2>
                    </div>

                    <?php if(empty($kegiatan_list)): ?>
                        <div class="no-data">
                            <p>📭 Tidak ada data kegiatan</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40px;">No</th>
                                <th>Nama Kegiatan</th>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th style="width: 120px;">Tampilkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kegiatan_list as $index => $k): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($k['nama_kegiatan']); ?></strong>
                                </td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($k['tanggal_kegiatan'])); ?></div>
                                    <div class="date"><?php echo date('l', strtotime($k['tanggal_kegiatan'])); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($k['jenis_kegiatan'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    $status_class = $k['status'] === 'Terlaksana' ? 'success' : 
                                                   ($k['status'] === 'Akan Datang' ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($k['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               <?php echo $k['tampil_di_berita'] ? 'checked' : ''; ?>
                                               onchange="toggleDisplay(<?php echo $k['id']; ?>, this.checked)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #1e40af;">💡 Informasi</h4>
                <p style="margin: 0; color: #0369a1; font-size: 14px;">
                    Aktifkan toggle di samping untuk menampilkan kegiatan di halaman utama guest dashboard. 
                    Maksimal 3 kegiatan terbaru dengan status "Terlaksana" yang lebih baru akan ditampilkan.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Migration function
        function runMigration() {
            const btn = document.getElementById('migrationBtn');
            const statusDiv = document.getElementById('migrationStatus');
            const messageDiv = document.getElementById('migrationMessage');
            
            btn.disabled = true;
            btn.style.opacity = '0.6';
            statusDiv.style.display = 'block';
            messageDiv.textContent = 'Sedang memproses...';
            
            fetch('../actions/migrate_kegiatan_display.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    messageDiv.textContent = '✅ ' + data.message;
                    messageDiv.style.color = '#d1fae5';
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    messageDiv.textContent = '❌ ' + data.message;
                    messageDiv.style.color = '#fee2e2';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            })
            .catch(error => {
                messageDiv.textContent = '❌ Terjadi kesalahan: ' + error.message;
                messageDiv.style.color = '#fee2e2';
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        }

        // Attach event listener to migration button if it exists
        document.addEventListener('DOMContentLoaded', function() {
            const migrationBtn = document.getElementById('migrationBtn');
            if(migrationBtn) {
                migrationBtn.addEventListener('click', runMigration);
            }
        });

        function toggleDisplay(id, checked) {
            const tampil = checked ? 1 : 0;
            
            fetch('../actions/toggle_kegiatan_display.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle&id=' + id + '&tampil=' + tampil
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showToast('success', '✅ Berhasil', 'Status kegiatan berhasil diperbarui');
                } else {
                    showToast('error', '❌ Gagal', data.message);
                }
            })
            .catch(error => {
                showToast('error', '❌ Kesalahan', error.message);
            });
        }

        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <div>
                    <strong>${title}</strong>
                    <div style="font-size: 12px; opacity: 0.8;">${message}</div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>

    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js"></script>
</body>
</html>
