<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'msh';
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
$edit_data = null;

// Jika ada parameter edit, ambil data untuk diedit
if($edit_id) {
    if($tab == 'msh') {
        $stmt = $pdo->prepare("SELECT * FROM pendaftaran_msh WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pendaftaran_kohai WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch();
    }
}

// Query untuk MSH
if($tab == 'msh') {
    $sql = "SELECT * FROM pendaftaran_msh WHERE 1=1";
    if($search) {
        $sql .= " AND (nama LIKE :search
                  OR kode_msh LIKE :search
                  OR no_telp LIKE :search
                  OR email LIKE :search
                  OR tempat_lahir LIKE :search
                  OR tingkat_dan LIKE :search
                  OR dojo_cabang LIKE :search
                  OR alamat LIKE :search
                  OR status LIKE :search
                  OR TO_CHAR(tanggal_lahir, 'DD/MM/YYYY') LIKE :search
                  OR TO_CHAR(created_at, 'DD/MM/YYYY') LIKE :search)";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    if($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $pendaftaran_list = $stmt->fetchAll();
} else {
    // Query untuk Kohai
    $sql = "SELECT * FROM pendaftaran_kohai WHERE 1=1";
    if($search) {
        $sql .= " AND (nama LIKE :search
                  OR kode_kohai LIKE :search
                  OR no_telp LIKE :search
                  OR email LIKE :search
                  OR tempat_lahir LIKE :search
                  OR alamat LIKE :search
                  OR nama_wali LIKE :search
                  OR no_telp_wali LIKE :search
                  OR status LIKE :search
                  OR TO_CHAR(tanggal_lahir, 'DD/MM/YYYY') LIKE :search
                  OR TO_CHAR(created_at, 'DD/MM/YYYY') LIKE :search)";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    if($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $pendaftaran_list = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftaran - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pendaftaran.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil ditambahkan'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
            <div class="toast-progress"></div>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil diupdate'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
            <div class="toast-progress"></div>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil dihapus'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
            <div class="toast-progress"></div>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">⚠️</div>
            <div class="toast-content">
                <div class="toast-title">Error!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
            <div class="toast-progress"></div>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">📋 Pendaftaran</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="pendaftaran-layout">
            <!-- Sidebar Form -->
            <div class="sidebar-form <?php echo $edit_data ? 'edit-mode' : ''; ?>">
                <div class="form-header">
                    <h3><?php echo $edit_data ? '✏️ Edit' : '📝 Form'; ?> Pendaftaran</h3>
                    <p><?php echo $edit_data ? 'Update data yang sudah ada' : 'Isi data dengan lengkap'; ?></p>
                    <?php if($edit_data): ?>
                    <a href="pendaftaran.php?tab=<?php echo $tab; ?>" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background: #f1f1f1; border-radius: 6px; text-decoration: none; color: #666; font-size: 13px;">
                        ❌ Batal Edit
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Form Tabs -->
                <div class="form-tabs">
                    <button class="form-tab-btn <?php echo $tab == 'msh' ? 'active' : ''; ?>" onclick="switchFormTab('msh')">
                        👨‍🎓 MSH
                    </button>
                    <button class="form-tab-btn <?php echo $tab == 'kohai' ? 'active' : ''; ?>" onclick="switchFormTab('kohai')">
                        👧 Kohai
                    </button>
                </div>
                
                <!-- MSH Form -->
                <div id="form-msh" class="form-content" style="display: <?php echo $tab == 'msh' ? 'block' : 'none'; ?>;">
                    <form action="<?php echo $edit_data && $tab == 'msh' ? 'actions/update_pendaftaran_msh.php' : 'actions/save_pendaftaran_msh.php'; ?>" method="POST" enctype="multipart/form-data">
                        <?php if($edit_data && $tab == 'msh'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($edit_data['foto'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>No MSH <span class="required">*</span></label>
                            <input type="text" name="kode_msh" placeholder="MSH001" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['kode_msh']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama" placeholder="Nama" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['nama']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Foto MSH</label>
                            <?php if($edit_data && $tab == 'msh' && !empty($edit_data['foto'])): ?>
                            <div class="image-preview active" style="margin-bottom: 10px;">
                                <img src="<?php echo htmlspecialchars($edit_data['foto']); ?>" alt="Foto saat ini" style="max-width: 150px;">
                                <p style="margin-top: 5px; color: #718096; font-size: 12px;">Foto saat ini</p>
                            </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="foto" id="foto_msh" accept="image/*" onchange="previewImage(this, 'preview-msh')">
                                <label for="foto_msh" class="file-upload-label">
                                    <span class="file-icon">📷</span>
                                    <span><?php echo $edit_data && $tab == 'msh' ? 'Ubah Foto' : 'Pilih Foto'; ?></span>
                                </label>
                            </div>
                            <div id="preview-msh" class="image-preview"></div>
                            <small class="form-hint">atau masukkan URL</small>
                            <input type="text" name="foto_url" placeholder="https://..." class="mt-5">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span class="required">*</span></label>
                                <input type="text" name="tempat_lahir" placeholder="Kota" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['tempat_lahir']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="required">*</span></label>
                                <input type="date" name="tanggal_lahir" value="<?php echo $edit_data && $tab == 'msh' ? $edit_data['tanggal_lahir'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Jenis Kelamin <span class="required">*</span></label>
                            <select name="jenis_kelamin" required>
                                <option value="">Pilih...</option>
                                <option value="L" <?php echo ($edit_data && $tab == 'msh' && $edit_data['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo ($edit_data && $tab == 'msh' && $edit_data['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tingkat Dan <span class="required">*</span></label>
                                <select name="tingkat_dan" required>
                                    <option value="">Pilih...</option>
                                    <?php for($i=1; $i<=7; $i++): ?>
                                    <option value="Dan <?php echo $i; ?>" <?php echo ($edit_data && $tab == 'msh' && $edit_data['tingkat_dan'] == "Dan $i") ? 'selected' : ''; ?>>Dan <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Dojo/Cabang <span class="required">*</span></label>
                                <input type="text" name="dojo_cabang" placeholder="Nama dojo" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['dojo_cabang']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. Telepon <span class="required">*</span></label>
                                <input type="tel" name="no_telp" placeholder="08xx-xxxx-xxxx" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['no_telp']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="email@example.com" value="<?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat <span class="required">*</span></label>
                            <textarea name="alamat" rows="3" placeholder="Alamat lengkap" required><?php echo $edit_data && $tab == 'msh' ? htmlspecialchars($edit_data['alamat']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_data && $tab == 'msh' ? '💾 Update' : '💾 Simpan'; ?> Pendaftaran MSH
                        </button>
                    </form>
                </div>
                
                <!-- Kohai Form -->
                <div id="form-kohai" class="form-content" style="display: <?php echo $tab == 'kohai' ? 'block' : 'none'; ?>;">
                    <form action="<?php echo $edit_data && $tab == 'kohai' ? 'actions/update_pendaftaran_kohai.php' : 'actions/save_pendaftaran_kohai.php'; ?>" method="POST" enctype="multipart/form-data">
                        <?php if($edit_data && $tab == 'kohai'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($edit_data['foto'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>No Kohai <span class="required">*</span></label>
                            <input type="text" name="kode_kohai" placeholder="KOH001" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['kode_kohai']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama" placeholder="Nama" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['nama']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Foto Kohai</label>
                            <?php if($edit_data && $tab == 'kohai' && !empty($edit_data['foto'])): ?>
                            <div class="image-preview active" style="margin-bottom: 10px;">
                                <img src="<?php echo htmlspecialchars($edit_data['foto']); ?>" alt="Foto saat ini" style="max-width: 150px;">
                                <p style="margin-top: 5px; color: #718096; font-size: 12px;">Foto saat ini</p>
                            </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="foto" id="foto_kohai" accept="image/*" onchange="previewImage(this, 'preview-kohai')">
                                <label for="foto_kohai" class="file-upload-label">
                                    <span class="file-icon">📷</span>
                                    <span><?php echo $edit_data && $tab == 'kohai' ? 'Ubah Foto' : 'Pilih Foto'; ?></span>
                                </label>
                            </div>
                            <div id="preview-kohai" class="image-preview"></div>
                            <small class="form-hint">atau masukkan URL</small>
                            <input type="text" name="foto_url" placeholder="https://..." class="mt-5">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span class="required">*</span></label>
                                <input type="text" name="tempat_lahir" placeholder="Kota" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['tempat_lahir']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="required">*</span></label>
                                <input type="date" name="tanggal_lahir" value="<?php echo $edit_data && $tab == 'kohai' ? $edit_data['tanggal_lahir'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Jenis Kelamin <span class="required">*</span></label>
                            <select name="jenis_kelamin" required>
                                <option value="">Pilih...</option>
                                <option value="L" <?php echo ($edit_data && $tab == 'kohai' && $edit_data['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo ($edit_data && $tab == 'kohai' && $edit_data['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. Telepon <span class="required">*</span></label>
                                <input type="tel" name="no_telp" placeholder="08xx-xxxx-xxxx" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['no_telp']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="email@example.com" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat <span class="required">*</span></label>
                            <textarea name="alamat" rows="3" placeholder="Alamat lengkap" required><?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['alamat']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-divider">
                            <span>Data Wali</span>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Wali <span class="required">*</span></label>
                            <input type="text" name="nama_wali" placeholder="Nama wali/orang tua" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['nama_wali']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>No. Telepon Wali <span class="required">*</span></label>
                            <input type="tel" name="no_telp_wali" placeholder="08xx-xxxx-xxxx" value="<?php echo $edit_data && $tab == 'kohai' ? htmlspecialchars($edit_data['no_telp_wali']) : ''; ?>" required>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_data && $tab == 'kohai' ? '💾 Update' : '💾 Simpan'; ?> Pendaftaran Kohai
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-table-content">
                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <button class="tab-btn <?php echo $tab == 'msh' ? 'active' : ''; ?>" onclick="switchTab('msh')">
                        👨‍🎓 Daftar MSH
                    </button>
                    <button class="tab-btn <?php echo $tab == 'kohai' ? 'active' : ''; ?>" onclick="switchTab('kohai')">
                        👧 Daftar Kohai
                    </button>
                </div>
                
                <div class="content-header">
                    <div>
                        <h1>📋 Ringkasan Data <?php echo $tab == 'msh' ? 'MSH' : 'Kohai'; ?> Terdaftar</h1>
                        <p class="hint-text">💡 Tips: Cari berdasarkan nama, nomor, dojo, tingkat, status, atau tanggal</p>
                    </div>
                    
                    <div class="header-actions">
                        <div class="search-bar">
                            <form method="GET" style="display: flex; gap: 15px; flex: 1;" id="searchForm">
                                <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                                <input type="text" name="search" id="searchInput" placeholder="Cari <?php echo strtoupper($tab); ?> (Nama, No, Tanggal, dll)..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                            </form>
                        </div>
                        <button class="btn-secondary" onclick="openExportModal()">
                            📄 Export PDF/Excel
                        </button>
                    </div>
                </div>
                
                <!-- Table Content -->
                <?php if($tab == 'msh'): ?>
                <!-- MSH Table -->
                <?php if(count($pendaftaran_list) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NO MSH</th>
                            <th>NAMA LENGKAP</th>
                            <th>TEMPAT LAHIR</th>
                            <th>TINGKAT DAN</th>
                            <th>TGL DAFTAR</th>
                            <th>STATUS</th>
                            <th>EXPORT</th>
                            <th>AKSI EXPORT</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pendaftaran_list as $daftar): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($daftar['kode_msh']); ?></td>
                            <td><?php echo htmlspecialchars($daftar['nama']); ?></td>
                            <td><?php echo htmlspecialchars($daftar['tempat_lahir']); ?></td>
                            <td><span class="badge badge-purple"><?php echo htmlspecialchars($daftar['tingkat_dan']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($daftar['created_at'])); ?></td>
                            <td>
                                <?php if($daftar['status'] == 'Pending'): ?>
                                    <span class="badge badge-warning">⏳ Belum</span>
                                <?php elseif($daftar['status'] == 'Aktif'): ?>
                                    <span class="badge badge-success">✅ Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">❌ Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-icon btn-export" onclick="exportToPDF(<?php echo $daftar['id']; ?>, 'majelis_sabuk_hitam')" title="Export PDF">
                                    📄
                                </button>
                            </td>
                            <td>
                                <?php if($daftar['status'] == 'Pending'): ?>
                                <button class="btn-icon btn-success" onclick="confirmExport(<?php echo $daftar['id']; ?>, 'majelis_sabuk_hitam')" title="Export ke Data MSH">
                                    📤
                                </button>
                                <?php else: ?>
                                <span class="badge badge-info">Sudah di Export</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-icon btn-edit" onclick="editPendaftaran(<?php echo $daftar['id']; ?>, 'msh')" title="Edit">✏️</button>
                                <button class="btn-icon btn-delete" onclick="confirmDelete(<?php echo $daftar['id']; ?>, 'msh')" title="Hapus">🗑️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3>Belum ada data terdaftar</h3>
                    <p>Mulai daftarkan MSH menggunakan form di sebelah kiri</p>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <!-- Kohai Table -->
                <?php if(count($pendaftaran_list) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NO KOHAI</th>
                            <th>NAMA LENGKAP</th>
                            <th>TEMPAT LAHIR</th>
                            <th>NAMA WALI</th>
                            <th>TGL DAFTAR</th>
                            <th>STATUS</th>
                            <th>EXPORT</th>
                            <th>AKSI EXPORT</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pendaftaran_list as $daftar): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($daftar['kode_kohai'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($daftar['nama'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($daftar['tempat_lahir'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($daftar['nama_wali'] ?? '-'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($daftar['created_at'])); ?></td>
                            <td>
                                <?php if($daftar['status'] == 'Pending'): ?>
                                    <span class="badge badge-warning">⏳ Belum</span>
                                <?php elseif($daftar['status'] == 'Aktif'): ?>
                                    <span class="badge badge-success">✅ Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">❌ Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-icon btn-export" onclick="exportToPDF(<?php echo $daftar['id']; ?>, 'kohai')" title="Export PDF">
                                    📄
                                </button>
                            </td>
                            <td>
                                <?php if($daftar['status'] == 'Pending'): ?>
                                <button class="btn-icon btn-success" onclick="confirmExport(<?php echo $daftar['id']; ?>, 'kohai')" title="Export ke Data Kohai">
                                    📤
                                </button>
                                <?php else: ?>
                                <span class="badge badge-info">Sudah di Export</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-icon btn-edit" onclick="editPendaftaran(<?php echo $daftar['id']; ?>, 'kohai')" title="Edit">✏️</button>
                                <button class="btn-icon btn-delete" onclick="confirmDelete(<?php echo $daftar['id']; ?>, 'kohai')" title="Hapus">🗑️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3>Belum ada data terdaftar</h3>
                    <p>Mulai daftarkan Kohai menggunakan form di sebelah kiri</p>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Export Modal -->
    <div class="export-modal-overlay" id="exportModal">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3>
                    <span>📊</span>
                    <span>Export Laporan Pendaftaran</span>
                </h3>
                <button class="export-modal-close" onclick="closeExportModal()">×</button>
            </div>
            
            <form id="exportForm" onsubmit="handleExportSubmit(event)">
                <div class="export-modal-body">
                    <div class="export-form-group">
                        <label>Format Export</label>
                        <select name="format" id="exportFormat" required>
                             <option value="">Pilih Format...</option>
                            <option value="pdf">📄 PDF Document (.pdf)</option>
                            <option value="excel">📊 Excel Spreadsheet (.xlsx)</option>
                            <option value="csv">📋 CSV File (.csv)</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group">
                        <label>Pilih Periode</label>
                        <select name="periode" id="exportPeriode" required onchange="toggleCustomDate(this.value)">
                            <option value="month">Bulan Ini</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="custom">Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group" id="customDateRange" style="display: none;">
                        <label>Range Tanggal</label>
                        <div class="export-signature-row">
                            <input type="date" name="start_date" id="startDate" class="export-signature-input" placeholder="Dari Tanggal">
                            <input type="date" name="end_date" id="endDate" class="export-signature-input" placeholder="Sampai Tanggal">
                        </div>
                    </div>
                    
                    <div class="export-signature-section">
                        <div class="export-signature-title">Tanda Tangan Digital</div>
                        <div class="export-signature-row">
                            <input type="text" name="ketua" id="exportKetua" class="export-signature-input" placeholder="Ketua YPOK" value="Ketua YPOK" required>
                            <input type="text" name="admin" id="exportAdmin" class="export-signature-input" placeholder="Pembuat Laporan" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly required style="background: #f0f0f0; cursor: not-allowed;">
                        </div>
                    </div>
                    
                    <input type="hidden" name="type" id="exportType" value="<?php echo $tab; ?>">
                </div>
                
                <div class="export-modal-footer">
                    <button type="button" class="export-btn export-btn-cancel" onclick="closeExportModal()">
                        <span>❌</span>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="export-btn export-btn-submit">
                        <span>📄</span>
                        <span>Generate & Export</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script src="assets/js/pendaftaran.js"></script>
    <?php if($edit_data): ?>
    <script>
        // Scroll to form when editing
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.sidebar-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
    <?php endif; ?>

    <!-- Auto-submit search enhancement -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto hide toast after 3 seconds
            const toast = document.getElementById('toast');
            if (toast) {
                setTimeout(() => {
                    toast.style.animation = 'slideOutRight 0.4s ease';
                    setTimeout(() => {
                        toast.remove();
                    }, 400);
                }, 3000);
            }

            // Auto-submit search form on input
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');

            if (searchInput && searchForm) {
                let searchTimeout;

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    // Visual feedback saat mengetik
                    searchInput.style.borderColor = '#fbbf24';
                    searchInput.style.background = '#fffbeb';

                    // Debounce untuk menghindari terlalu banyak request
                    searchTimeout = setTimeout(() => {
                        // Show loading indicator
                        searchInput.style.borderColor = '#3b82f6';
                        searchInput.style.background = '#eff6ff';
                        searchForm.submit();
                    }, 500); // Submit setelah 500ms user berhenti mengetik
                });

                // Clear search dengan ESC
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchInput.value = '';
                        searchForm.submit();
                    }
                });

                // Reset style on focus
                searchInput.addEventListener('focus', function() {
                    if (!this.value) {
                        searchInput.style.borderColor = '#667eea';
                        searchInput.style.background = 'white';
                    }
                });
            }
        });

        function closeToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(() => {
                    toast.remove();
                }, 400);
            }
        }
    </script>
    
    <!-- Export Modal Functions -->
    <script>
        function toggleCustomDate(value) {
            const customDateDiv = document.getElementById('customDateRange');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            
            if(value === 'custom') {
                customDateDiv.style.display = 'block';
                startDate.required = true;
                endDate.required = true;
            } else {
                customDateDiv.style.display = 'none';
                startDate.required = false;
                endDate.required = false;
            }
        }
        
        function handleExportSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Build query string
            const params = new URLSearchParams();
            params.append('format', formData.get('format'));
            params.append('periode', formData.get('periode'));
            params.append('type', formData.get('type'));
            params.append('ketua', formData.get('ketua'));
            params.append('admin', formData.get('admin'));
            
            if (formData.get('periode') === 'custom') {
                const startDate = formData.get('start_date');
                const endDate = formData.get('end_date');
                
                if (!startDate || !endDate) {
                    alert('Silakan pilih tanggal mulai dan tanggal akhir');
                    return;
                }
                
                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }
            
            // Open export page in new tab
            const url = `actions/export_pendaftaran.php?${params.toString()}`;
            window.open(url, '_blank');
            
            // Close modal
            closeExportModal();
        }
    </script>
</body>
</html>

