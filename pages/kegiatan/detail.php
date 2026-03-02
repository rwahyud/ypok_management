<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: laporan_kegiatan.php');
    exit();
}

$id = $_GET['id'];

// Get kegiatan detail
$stmt = $pdo->prepare("SELECT k.*, l.nama_lokasi FROM kegiatan k LEFT JOIN lokasi l ON k.lokasi_id = l.id WHERE k.id = ?");
$stmt->execute([$id]);
$kegiatan = $stmt->fetch();

if(!$kegiatan) {
    header('Location: laporan_kegiatan.php?error=Data tidak ditemukan');
    exit();
}

// Map status
$status_display = [
    'terlaksana' => 'Selesai',
    'akan_datang' => 'Dijadwalkan',
    'dibatalkan' => 'Dibatalkan'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kegiatan - YPOK Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .detail-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 900px;
            margin: 20px auto;
        }
        
        .detail-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .detail-header h1 {
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4b5563;
        }
        
        .detail-value {
            color: #1f2937;
        }
        
        .peserta-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .peserta-section h3 {
            color: #1e40af;
            margin-bottom: 15px;
        }
        
        .peserta-list {
            white-space: pre-line;
            line-height: 1.8;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .btn-back {
            padding: 12px 24px;
            background: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
        
        .btn-edit {
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
        
        .btn-delete {
            padding: 12px 24px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include '../../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Detail Kegiatan</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="detail-container">
                <div class="detail-header">
                    <h1><?php echo htmlspecialchars($kegiatan['nama_kegiatan']); ?></h1>
                    <span class="status-badge status-<?php echo $kegiatan['status']; ?>">
                        <?php echo $status_display[$kegiatan['status']] ?? $kegiatan['status']; ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Kategori</div>
                    <div class="detail-value"><?php echo htmlspecialchars($kegiatan['jenis_kegiatan']); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Tanggal Kegiatan</div>
                    <div class="detail-value"><?php echo date('d F Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Lokasi</div>
                    <div class="detail-value"><?php echo htmlspecialchars($kegiatan['nama_lokasi']); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Penanggung Jawab (PIC)</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($kegiatan['pic'] ?? '-'); ?>
                    </div>
                </div>
                
                <?php if (!empty($kegiatan['biaya'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Biaya</div>
                    <div class="detail-value">Rp <?php echo number_format($kegiatan['biaya'], 0, ',', '.'); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($kegiatan['keterangan'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-value">
                        <?php echo nl2br(htmlspecialchars($kegiatan['keterangan'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($kegiatan['peserta'])): ?>
                <div class="peserta-section">
                    <h3>📋 Daftar Peserta</h3>
                    <div class="peserta-list">
                        <?php echo htmlspecialchars($kegiatan['peserta']); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="laporan_kegiatan.php" class="btn-back">← Kembali</a>
                    <a href="kegiatan_edit.php?id=<?php echo $kegiatan['id']; ?>" class="btn-edit">✏️ Edit</a>
                    <a href="/ypok_management/ypok_management/pages/kegiatan/delete.php?id=<?php echo $kegiatan['id']; ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">🗑️ Hapus</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/app.js"></script>
</body>
</html>
