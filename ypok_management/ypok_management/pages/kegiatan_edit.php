<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
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

// Get data for dropdowns
$kategori_list = $pdo->query("SELECT DISTINCT jenis_kegiatan FROM kegiatan WHERE jenis_kegiatan IS NOT NULL ORDER BY jenis_kegiatan")->fetchAll();
$lokasi_list = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();
$msh_list = $pdo->query("SELECT id, nama, no_msh FROM master_sabuk_hitam ORDER BY nama")->fetchAll();
$kohai_list = $pdo->query("SELECT id, nama, kode_kohai FROM kohai ORDER BY nama")->fetchAll();

// Get existing peserta MSH
$peserta_msh = [];
if (!empty($kegiatan['peserta'])) {
    // Extract MSH IDs from peserta text
    preg_match_all('/- (.+?) \((.+?)\)/i', $kegiatan['peserta'], $matches);
    // This is simplified - you might want to store IDs in a separate table
}

// Map status
$status_map = [
    'terlaksana' => 'Selesai',
    'akan_datang' => 'Dijadwalkan',
    'dibatalkan' => 'Dibatalkan'
];

$status_norm = strtolower(trim((string)$kegiatan['status']));
$status_norm = str_replace(' ', '_', $status_norm);

if ($status_norm === 'selesai' || $status_norm === 'berlangsung') {
    $status_norm = 'terlaksana';
} elseif ($status_norm === 'dijadwalkan') {
    $status_norm = 'akan_datang';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kegiatan - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .edit-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 900px;
            margin: 20px 30px;
        }
        
        .edit-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .edit-header h1 {
            color: #1e40af;
            margin: 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group label span {
            color: red;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .add-item-btn {
            width: 100%;
            padding: 10px;
            background: #f3f4f6;
            border: 1px dashed #9ca3af;
            border-radius: 4px;
            cursor: pointer;
            color: #374151;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .peserta-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .peserta-item select {
            flex: 1;
        }
        
        .remove-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            text-align: center;
        }
        
        .btn-submit {
            flex: 2;
            padding: 12px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .kategori-helper {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Edit Kegiatan</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="edit-container">
                <div class="edit-header">
                    <h1>Edit Laporan Kegiatan</h1>
                </div>
                
                <form action="kegiatan_update.php" method="POST" id="formEdit">
                    <input type="hidden" name="id" value="<?php echo $kegiatan['id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Kegiatan <span>*</span></label>
                            <input type="text" name="nama_kegiatan" value="<?php echo htmlspecialchars($kegiatan['nama_kegiatan']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kategori <span>*</span></label>
                            <input type="text" name="kategori" list="kategori-list" value="<?php echo htmlspecialchars($kegiatan['jenis_kegiatan']); ?>" placeholder="Pilih atau ketik kategori baru..." required>
                            <datalist id="kategori-list">
                                <?php foreach($kategori_list as $kategori): ?>
                                    <option value="<?php echo htmlspecialchars($kategori['jenis_kegiatan']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="kategori-helper">💡 Pilih dari daftar atau ketik kategori baru</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal <span>*</span></label>
                            <input type="date" name="tanggal_kegiatan" value="<?php echo $kegiatan['tanggal_kegiatan']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" list="lokasi-list" value="<?php echo htmlspecialchars($kegiatan['nama_lokasi']); ?>" placeholder="Pilih atau ketik lokasi baru..." required>
                            <datalist id="lokasi-list">
                                <?php foreach($lokasi_list as $lokasi): ?>
                                    <option value="<?php echo htmlspecialchars($lokasi['nama_lokasi']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" placeholder="Alamat lengkap lokasi kegiatan..." rows="3"><?php echo htmlspecialchars($kegiatan['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>PIC / Penanggung Jawab <span>*</span></label>
                            <input type="text" name="pic" value="<?php echo htmlspecialchars($kegiatan['pic']); ?>" placeholder="Nama PIC" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Jumlah Peserta</label>
                            <input type="number" name="jumlah_peserta" min="0" value="<?php echo htmlspecialchars($kegiatan['jumlah_peserta'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="status" required>
                            <option value="Dijadwalkan" <?php echo $status_norm === 'akan_datang' ? 'selected' : ''; ?>>Dijadwalkan</option>
                            <option value="Berlangsung" <?php echo $status_norm === 'berlangsung' ? 'selected' : ''; ?>>Berlangsung</option>
                            <option value="Selesai" <?php echo $status_norm === 'terlaksana' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="Dibatalkan" <?php echo $status_norm === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Peserta MSH (Opsional)</label>
                        <div id="pesertaMSHContainer">
                            <div class="peserta-item">
                                <select name="peserta_msh[]">
                                    <option value="">Pilih MSH...</option>
                                    <?php foreach($msh_list as $msh): ?>
                                        <option value="<?php echo $msh['id']; ?>">
                                            <?php echo htmlspecialchars($msh['nama']); ?> 
                                            (<?php echo htmlspecialchars($msh['no_msh']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="tambahPesertaMSH()">+ Tambah Peserta MSH</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Peserta Pelatih (Opsional)</label>
                        <div id="pesertaPelatihContainer">
                            <div class="peserta-item">
                                <select name="peserta_pelatih[]">
                                    <option value="">Pilih Pelatih...</option>
                                    <?php foreach($kohai_list as $kohai): ?>
                                        <option value="<?php echo $kohai['id']; ?>">
                                            <?php echo htmlspecialchars($kohai['nama']); ?> 
                                            (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="tambahPesertaPelatih()">+ Tambah Peserta Pelatih</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi Kegiatan</label>
                        <textarea name="deskripsi" placeholder="Tuliskan deskripsi kegiatan..."><?php echo htmlspecialchars($kegiatan['keterangan'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="laporan_kegiatan.php" class="btn-cancel">Batal</a>
                        <button type="submit" class="btn-submit">Update Data</button>
                    </div>
                </form>
            </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        const mshOptions = `
            <option value="">Pilih MSH...</option>
            <?php foreach($msh_list as $msh): ?>
                <option value="<?php echo $msh['id']; ?>">
                    <?php echo addslashes(htmlspecialchars($msh['nama'])); ?> 
                    (<?php echo htmlspecialchars($msh['no_msh']); ?>)
                </option>
            <?php endforeach; ?>
        `;
        
        const kohaiOptions = `
            <option value="">Pilih Pelatih...</option>
            <?php foreach($kohai_list as $kohai): ?>
                <option value="<?php echo $kohai['id']; ?>">
                    <?php echo addslashes(htmlspecialchars($kohai['nama'])); ?> 
                    (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                </option>
            <?php endforeach; ?>
        `;
        
        function tambahPesertaMSH() {
            const container = document.getElementById('pesertaMSHContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_msh[]">${mshOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function tambahPesertaPelatih() {
            const container = document.getElementById('pesertaPelatihContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_pelatih[]">${kohaiOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
