<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Function untuk auto-update status berdasarkan tanggal
function updateLegalitasStatus($pdo) {
    $today = date('Y-m-d');
    $warning_days = 30; // 30 hari sebelum kadaluarsa
    $warning_date = date('Y-m-d', strtotime("+$warning_days days"));
    
    // Update ke Kadaluarsa (hanya untuk dokumen yang bukan permanent, bukan dalam proses, dan tanggal bukan 9999-12-31)
    $stmt = $pdo->prepare("UPDATE legalitas SET status = 'Kadaluarsa' WHERE tanggal_kadaluarsa < ? AND tanggal_kadaluarsa != '9999-12-31' AND is_permanent = false AND status != 'Dalam Proses'");
    $stmt->execute([$today]);
    
    // Update ke Akan Kadaluarsa (hanya untuk dokumen yang bukan permanent, bukan dalam proses, dan tanggal bukan 9999-12-31)
    $stmt = $pdo->prepare("UPDATE legalitas SET status = 'Akan Kadaluarsa' WHERE tanggal_kadaluarsa BETWEEN ? AND ? AND tanggal_kadaluarsa != '9999-12-31' AND is_permanent = false AND status != 'Dalam Proses'");
    $stmt->execute([$today, $warning_date]);
    
    // Update ke Aktif (jika masih jauh dari kadaluarsa, bukan permanent dan bukan dalam proses)
    $stmt = $pdo->prepare("UPDATE legalitas SET status = 'Aktif' WHERE tanggal_kadaluarsa > ? AND tanggal_kadaluarsa != '9999-12-31' AND is_permanent = false AND status != 'Dalam Proses'");
    $stmt->execute([$warning_date]);
    
    // Update dokumen permanent selalu aktif (kecuali dalam proses)
    $stmt = $pdo->prepare("UPDATE legalitas SET status = 'Aktif' WHERE is_permanent = true AND status != 'Dalam Proses'");
    $stmt->execute();
}

// Auto-update status
updateLegalitasStatus($pdo);

// Hitung statistik dokumen
$stmt_total = $pdo->query("SELECT COUNT(*) as total FROM legalitas");
$total_dokumen = $stmt_total->fetch()['total'];

$stmt_aktif = $pdo->query("SELECT COUNT(*) as total FROM legalitas WHERE status = 'Aktif'");
$dokumen_aktif = $stmt_aktif->fetch()['total'];

$stmt_kadaluarsa = $pdo->query("SELECT COUNT(*) as total FROM legalitas WHERE status = 'Akan Kadaluarsa'");
$akan_kadaluarsa = $stmt_kadaluarsa->fetch()['total'];

$stmt_proses = $pdo->query("SELECT COUNT(*) as total FROM legalitas WHERE status = 'Dalam Proses'");
$dalam_proses = $stmt_proses->fetch()['total'];

// Ambil informasi yayasan dengan error handling
$info_yayasan = null;
try {
    $stmt_yayasan = $pdo->query("SELECT * FROM informasi_yayasan LIMIT 1");
    $info_yayasan = $stmt_yayasan->fetch();
} catch (PDOException $e) {
    // Tabel belum ada, set default values
    $info_yayasan = [
        'nama_lengkap' => 'Yayasan Pemberdayaan Orang Kerdil',
        'nama_singkat' => 'YPOK',
        'tanggal_berdiri' => null,
        'status_hukum' => 'Terdaftar',
        'alamat' => '-',
        'email' => '-',
        'telepon' => '-',
        'website' => '-',
        'visi' => '-'
    ];
}

// Filter dokumen legalitas
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Semua';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM legalitas WHERE 1=1";
if ($filter != 'Semua') {
    $query .= " AND status = :filter";
}
if ($search) {
    $query .= " AND (jenis_dokumen LIKE :search OR nomor_dokumen LIKE :search)";
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
if ($filter != 'Semua') {
    $stmt->bindValue(':filter', $filter);
}
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->execute();
$legalitas_list = $stmt->fetchAll();

// Ambil data pengurus dengan error handling
$pengurus_list = [];
try {
    $stmt_pengurus = $pdo->query("SELECT * FROM pengurus ORDER BY created_at DESC");
    $pengurus_list = $stmt_pengurus->fetchAll();
} catch (PDOException $e) {
    // Tabel belum ada
    $pengurus_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Legalitas - YPOK Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil disimpan</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil diupdate</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil dihapus</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Legalitas</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <!-- Statistik Card -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_dokumen; ?></div>
                        <div class="stat-label">Total Dokumen</div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">✓</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $dokumen_aktif; ?></div>
                        <div class="stat-label">Dokumen Aktif</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $akan_kadaluarsa; ?></div>
                        <div class="stat-label">Akan Kadaluarsa</div>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $dalam_proses; ?></div>
                        <div class="stat-label">Dalam Proses</div>
                    </div>
                </div>
            </div>

            <!-- Informasi Yayasan -->
            <div class="info-section">
                <h2 class="section-title">ℹ️ Informasi Yayasan</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">NAMA LENGKAP:</div>
                        <div class="info-value"><?php echo $info_yayasan['nama_lengkap'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NAMA SINGKAT:</div>
                        <div class="info-value"><?php echo $info_yayasan['nama_singkat'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">TANGGAL BERDIRI:</div>
                        <div class="info-value"><?php echo $info_yayasan['tanggal_berdiri'] ? date('d F Y', strtotime($info_yayasan['tanggal_berdiri'])) : '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">STATUS HUKUM:</div>
                        <div class="info-value">
                            <span class="status-badge status-active"><?php echo $info_yayasan['status_hukum'] ?? '-'; ?></span>
                        </div>
                    </div>
                    <div class="info-item full-width">
                        <div class="info-label">ALAMAT KANTOR PUSAT:</div>
                        <div class="info-value"><?php echo $info_yayasan['alamat'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">EMAIL:</div>
                        <div class="info-value"><?php echo $info_yayasan['email'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">TELEPON:</div>
                        <div class="info-value"><?php echo $info_yayasan['telepon'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item full-width">
                        <div class="info-label">WEBSITE:</div>
                        <div class="info-value"><?php echo $info_yayasan['website'] ?? '-'; ?></div>
                    </div>
                    <div class="info-item full-width">
                        <div class="info-label">VISI:</div>
                        <div class="info-value"><?php echo $info_yayasan['visi'] ?? '-'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Legalitas -->
            <div class="content-header">
                <h1>📋 Dokumen Legalitas</h1>
                
                <div class="search-bar">
                    <form method="GET" style="display: flex; gap: 15px; flex: 1;">
                        <input type="text" name="search" id="searchInput" placeholder="Cari dokumen..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                    </form>
                    <button class="btn-primary" onclick="openModal()">
                        ➕ Tambah Dokumen
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs" id="dokumenSection">
                <a href="?filter=Semua#dokumenSection" class="filter-tab <?php echo $filter == 'Semua' ? 'active' : ''; ?>">Semua</a>
                <a href="?filter=Aktif#dokumenSection" class="filter-tab <?php echo $filter == 'Aktif' ? 'active' : ''; ?>">Aktif</a>
                <a href="?filter=Akan Kadaluarsa#dokumenSection" class="filter-tab <?php echo $filter == 'Akan Kadaluarsa' ? 'active' : ''; ?>">Akan Habis</a>
                <a href="?filter=Kadaluarsa#dokumenSection" class="filter-tab <?php echo $filter == 'Kadaluarsa' ? 'active' : ''; ?>">Habis</a>
                <a href="?filter=Dalam Proses#dokumenSection" class="filter-tab <?php echo $filter == 'Dalam Proses' ? 'active' : ''; ?>">Proses</a>
            </div>

            <!-- Document Cards -->
            <div class="document-grid">
                <?php foreach($legalitas_list as $legal): ?>
                <div class="document-card">
                    <div class="document-icon">📄</div>
                    <div class="document-header">
                        <h3><?php echo htmlspecialchars($legal['jenis_dokumen']); ?></h3>
                        <div class="document-actions">
                            <a href="#" class="action-icon" onclick="viewDokumen(<?php echo htmlspecialchars(json_encode($legal)); ?>); return false;" title="Lihat Detail">👁️</a>
                            <a href="#" class="action-icon" onclick="editDokumen(<?php echo htmlspecialchars(json_encode($legal)); ?>); return false;" title="Edit">✏️</a>
                            <a href="/ypok_management/ypok_management/pages/legalitas/delete.php?id=<?php echo $legal['id']; ?>" class="action-icon" onclick="return confirm('Yakin hapus dokumen ini?')" title="Hapus">🗑️</a>
                        </div>
                    </div>
                    <div class="document-number"><?php echo htmlspecialchars($legal['nomor_dokumen']); ?></div>
                    <div class="document-meta">
                        <div class="meta-item">
                            <span class="meta-icon">📅</span>
                            <span>Terbit: <?php echo date('d F Y', strtotime($legal['tanggal_terbit'])); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">⏰</span>
                            <?php if($legal['is_permanent'] == 1): ?>
                                <span>Permanen</span>
                            <?php elseif($legal['status'] == 'Dalam Proses' && $legal['tanggal_kadaluarsa'] == '9999-12-31'): ?>
                                <span>Belum Ditentukan</span>
                            <?php else: ?>
                                <span>Kadaluarsa: <?php echo date('d F Y', strtotime($legal['tanggal_kadaluarsa'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">🏢</span>
                            <span><?php echo htmlspecialchars($legal['instansi_penerbit']); ?></span>
                        </div>
                    </div>
                    <div class="document-status">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $legal['status'])); ?>">
                            <?php echo $legal['status']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pengurus Yayasan -->
            <div class="content-header" style="margin-top: 40px;">
                <h1>👥 Pengurus Yayasan</h1>
                <div class="search-bar">
                    <input type="text" id="searchPengurus" placeholder="Cari pengurus..." autocomplete="off">
                    <button class="btn-primary" onclick="openPengurusModal()">
                        ➕ Tambah Pengurus
                    </button>
                </div>
            </div>

            <?php if(empty($pengurus_list)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>Belum ada data pengurus</h3>
                <p>Klik tombol "Tambah Pengurus" untuk menambahkan data</p>
            </div>
            <?php else: ?>
            <div class="pengurus-grid">
                <?php foreach($pengurus_list as $pengurus): ?>
                <div class="pengurus-card">
                    <div class="pengurus-avatar">
                        <?php if(!empty($pengurus['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($pengurus['foto']); ?>" alt="<?php echo htmlspecialchars($pengurus['nama']); ?>">
                        <?php elseif(!empty($pengurus['foto_url'])): ?>
                            <img src="<?php echo htmlspecialchars($pengurus['foto_url']); ?>" alt="<?php echo htmlspecialchars($pengurus['nama']); ?>">
                        <?php else: ?>
                            👤
                        <?php endif; ?>
                    </div>
                    <div class="pengurus-info">
                        <h3><?php echo htmlspecialchars($pengurus['nama']); ?></h3>
                        <p class="pengurus-jabatan"><?php echo htmlspecialchars($pengurus['jabatan']); ?></p>
                        <p class="pengurus-periode">Periode: <?php echo htmlspecialchars($pengurus['periode']); ?></p>
                    </div>
                    <div class="pengurus-actions">
                        <a href="#" class="btn-view-small" onclick="viewPengurus(<?php echo htmlspecialchars(json_encode($pengurus)); ?>); return false;" title="Lihat Detail">👁️</a>
                        <a href="#" class="btn-edit-small" onclick="editPengurus(<?php echo htmlspecialchars(json_encode($pengurus)); ?>); return false;" title="Edit">✏️</a>
                        <a href="/ypok_management/ypok_management/pages/pengurus/delete.php?id=<?php echo $pengurus['id']; ?>" class="btn-delete-small" onclick="return confirm('Yakin hapus pengurus ini?')" title="Hapus">🗑️</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Tambah Dokumen -->
    <div id="modalDokumen" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalDokumenTitle">➕ Tambah Dokumen Legalitas</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="formDokumen" action="add.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="dokumen_id">
                <div class="form-group">
                    <label>Jenis Dokumen *</label>
                    <input type="text" name="jenis_dokumen" id="dokumen_jenis" required>
                </div>
                <div class="form-group">
                    <label>Nomor Dokumen *</label>
                    <input type="text" name="nomor_dokumen" id="dokumen_nomor" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Terbit *</label>
                        <input type="date" name="tanggal_terbit" id="dokumen_terbit" required>
                    </div>
                    <div class="form-group">
                        <label id="label_kadaluarsa">Tanggal Kadaluarsa *</label>
                        <input type="date" name="tanggal_kadaluarsa" id="dokumen_kadaluarsa" required>
                        <div class="checkbox-group" style="margin-top: 10px;">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_permanent" id="dokumen_permanent" value="1" onchange="toggleKadaluarsa()">
                                <span>Dokumen Permanen (Tidak Ada Kadaluarsa)</span>
                            </label>
                        </div>
                        <small class="form-hint" id="status_info" style="display: none; margin-top: 8px;"></small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Instansi Penerbit *</label>
                    <input type="text" name="instansi_penerbit" id="dokumen_instansi" required>
                </div>
                <div class="form-group">
                    <label>Status Proses</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="dalam_proses" id="dokumen_dalam_proses" value="1" onchange="toggleDalamProses()">
                            <span>Dokumen Sedang Dalam Proses Pengurusan</span>
                        </label>
                    </div>
                    <small class="form-hint">
                        Status dokumen akan otomatis dihitung berdasarkan tanggal kadaluarsa:<br>
                        • <strong>Aktif</strong>: Lebih dari 30 hari sebelum kadaluarsa<br>
                        • <strong>Akan Kadaluarsa</strong>: 30 hari atau kurang sebelum kadaluarsa<br>
                        • <strong>Kadaluarsa</strong>: Sudah melewati tanggal kadaluarsa<br>
                        • <strong>Dalam Proses</strong>: Jika dokumen sedang diurus (tanggal kadaluarsa opsional)
                    </small>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" id="dokumen_keterangan" rows="3" placeholder="Tambahkan keterangan dokumen..."></textarea>
                </div>
                <div class="form-group">
                    <label>File Dokumen</label>
                    <input type="file" name="file_dokumen" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar">
                    <small class="form-hint">Format yang didukung: PDF, Word (DOC/DOCX), Gambar (JPG/PNG), ZIP/RAR. Maksimal 10MB. Kosongkan jika tidak ingin mengubah file</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal View Dokumen -->
    <div id="modalViewDokumen" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>📄 Detail Dokumen</h2>
                <span class="close" onclick="closeViewDokumen()">&times;</span>
            </div>
            <div class="detail-container">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Jenis Dokumen</div>
                        <div class="detail-value" id="view_jenis"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Nomor Dokumen</div>
                        <div class="detail-value" id="view_nomor"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Terbit</div>
                        <div class="detail-value" id="view_terbit"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Kadaluarsa</div>
                        <div class="detail-value" id="view_kadaluarsa"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sisa Waktu</div>
                        <div class="detail-value" id="view_sisa_waktu"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value" id="view_status"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Instansi Penerbit</div>
                        <div class="detail-value" id="view_instansi"></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Keterangan</div>
                        <div class="detail-value" id="view_keterangan"></div>
                    </div>
                </div>

                <!-- PDF Preview Section -->
                <div id="pdfPreviewSection" style="display: none; margin-top: 30px;">
                    <div class="pdf-preview-header">
                        <h3 class="form-section-title">📄 Preview Dokumen</h3>
                        <div class="pdf-actions">
                            <button type="button" class="btn-secondary btn-small" onclick="downloadPDF()">
                                📥 Download
                            </button>
                            <button type="button" class="btn-secondary btn-small" onclick="openPDFNewTab()">
                                🔗 Buka di Tab Baru
                            </button>
                        </div>
                    </div>
                    <div class="pdf-preview-container">
                        <iframe id="pdfViewer" width="100%" height="600px" style="border: none; border-radius: 8px;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Pengurus -->
    <div id="modalPengurus" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalPengurusTitle">👥 Tambah Pengurus</h2>
                <span class="close" onclick="closePengurusModal()">&times;</span>
            </div>
            <form id="formPengurus" action="../pengurus/add.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="pengurus_id">
                <div class="form-section">
                    <h3 class="form-section-title">📷 Foto Pengurus</h3>
                    <div class="form-group">
                        <div class="foto-upload-container">
                            <div class="foto-preview" id="fotoPreview">
                                <span class="foto-icon">👤</span>
                            </div>
                            <div class="foto-upload-options">
                                <label class="btn-upload">
                                    📤 Pilih Foto
                                    <input type="file" name="foto" id="fotoInput" accept="image/*" style="display: none;">
                                </label>
                                <span class="text-divider">atau masukkan URL</span>
                                <input type="text" name="foto_url" id="pengurus_foto_url" placeholder="https://..." class="input-url">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">📋 Data Pribadi</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>NIK *</label>
                            <input type="text" name="nik" id="pengurus_nik" placeholder="3201xxxxxxxxxx" maxlength="16" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama" id="pengurus_nama" placeholder="Dr. H. ..." required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tempat Lahir *</label>
                            <input type="text" name="tempat_lahir" id="pengurus_tempat_lahir" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir *</label>
                            <input type="date" name="tanggal_lahir" id="pengurus_tanggal_lahir" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">💼 Jabatan & Periode</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Jabatan *</label>
                            <select name="jabatan" id="jabatanSelect" required>
                                <option value="">Pilih...</option>
                                <option value="Ketua Umum">Ketua Umum</option>
                                <option value="Wakil Ketua">Wakil Ketua</option>
                                <option value="Sekretaris Jenderal">Sekretaris Jenderal</option>
                                <option value="Bendahara">Bendahara</option>
                                <option value="Ketua Bidang Teknik">Ketua Bidang Teknik</option>
                                <option value="Ketua Bidang Pembinaan">Ketua Bidang Pembinaan</option>
                                <option value="Ketua Bidang Humas">Ketua Bidang Humas</option>
                                <option value="Anggota">Anggota</option>
                                <option value="custom">+ Tambah Jabatan Baru</option>
                            </select>
                            <input type="text" name="jabatan_custom" id="jabatanCustom" placeholder="Masukkan jabatan baru" style="display: none; margin-top: 10px;">
                        </div>
                        <div class="form-group">
                            <label>Periode *</label>
                            <input type="text" name="periode" id="pengurus_periode" placeholder="2020-2025" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. SK Pengangkatan *</label>
                            <input type="text" name="no_sk" id="pengurus_no_sk" placeholder="SK/YPOK/001/2020" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal SK *</label>
                            <input type="date" name="tanggal_sk" id="pengurus_tanggal_sk" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">📧 Kontak</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" id="pengurus_email" placeholder="email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon *</label>
                            <input type="text" name="telepon" id="pengurus_telepon" placeholder="08xx-xxxx-xxxx" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Lengkap *</label>
                        <textarea name="alamat" id="pengurus_alamat" rows="3" required></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">🎓 Pendidikan & Status</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pendidikan Terakhir *</label>
                            <input type="text" name="pendidikan_terakhir" id="pengurus_pendidikan" placeholder="S2 Manajemen Olahraga" required>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" id="pengurus_status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closePengurusModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal View Pengurus -->
    <div id="modalViewPengurus" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>👤 Detail Pengurus</h2>
                <span class="close" onclick="closeViewPengurus()">&times;</span>
            </div>
            <div class="detail-container">
                <div class="pengurus-detail-header">
                    <div class="pengurus-detail-foto" id="detail_foto">
                        <span class="foto-icon">👤</span>
                    </div>
                    <div class="pengurus-detail-info">
                        <h3 id="detail_nama"></h3>
                        <p class="detail-jabatan" id="detail_jabatan"></p>
                        <p class="detail-periode" id="detail_periode"></p>
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">NIK</div>
                        <div class="detail-value" id="detail_nik"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tempat, Tanggal Lahir</div>
                        <div class="detail-value" id="detail_ttl"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">No. SK</div>
                        <div class="detail-value" id="detail_no_sk"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal SK</div>
                        <div class="detail-value" id="detail_tanggal_sk"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value" id="detail_email"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Telepon</div>
                        <div class="detail-value" id="detail_telepon"></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Alamat</div>
                        <div class="detail-value" id="detail_alamat"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Pendidikan Terakhir</div>
                        <div class="detail-value" id="detail_pendidikan"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value" id="detail_status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/app.js"></script>
    <script>
        let currentPDFUrl = '';

        // Calculate status based on date
        function calculateStatus(kadaluarsaDate, isPermanent, dalamProses) {
            if (dalamProses) {
                return 'Dalam Proses';
            }
            
            if (isPermanent) {
                return 'Aktif';
            }
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const kadaluarsa = new Date(kadaluarsaDate);
            kadaluarsa.setHours(0, 0, 0, 0);
            
            const diffTime = kadaluarsa - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) {
                return 'Kadaluarsa';
            } else if (diffDays <= 30) {
                return 'Akan Kadaluarsa';
            } else {
                return 'Aktif';
            }
        }

        // Calculate remaining time
        function calculateRemainingTime(kadaluarsaDate, isPermanent) {
            if (isPermanent) {
                return '<span class="permanent-badge">Permanen</span>';
            }
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const kadaluarsa = new Date(kadaluarsaDate);
            kadaluarsa.setHours(0, 0, 0, 0);
            
            const diffTime = kadaluarsa - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) {
                return '<span class="expired-time">Telah kadaluarsa ' + Math.abs(diffDays) + ' hari</span>';
            } else if (diffDays === 0) {
                return '<span class="warning-time">Kadaluarsa hari ini</span>';
            } else if (diffDays <= 30) {
                return '<span class="warning-time">' + diffDays + ' hari lagi</span>';
            } else {
                const months = Math.floor(diffDays / 30);
                const days = diffDays % 30;
                return '<span class="safe-time">' + (months > 0 ? months + ' bulan ' : '') + (days > 0 ? days + ' hari' : '') + '</span>';
            }
        }

        // Show status preview when date changes
        function updateStatusPreview() {
            const kadaluarsaInput = document.getElementById('dokumen_kadaluarsa');
            const permanentCheck = document.getElementById('dokumen_permanent');
            const dalamProsesCheck = document.getElementById('dokumen_dalam_proses');
            const statusInfo = document.getElementById('status_info');
            
            if (!kadaluarsaInput.value || permanentCheck.checked || dalamProsesCheck.checked) {
                statusInfo.style.display = 'none';
                return;
            }
            
            const status = calculateStatus(kadaluarsaInput.value, permanentCheck.checked, dalamProsesCheck.checked);
            const remainingTime = calculateRemainingTime(kadaluarsaInput.value, permanentCheck.checked);
            
            statusInfo.style.display = 'block';
            statusInfo.innerHTML = '📊 Status akan menjadi: <strong>' + status + '</strong> (' + remainingTime + ')';
            
            // Change color based on status
            if (status === 'Kadaluarsa') {
                statusInfo.style.color = '#e53e3e';
            } else if (status === 'Akan Kadaluarsa') {
                statusInfo.style.color = '#d69e2e';
            } else if (status === 'Dalam Proses') {
                statusInfo.style.color = '#3182ce';
            } else {
                statusInfo.style.color = '#38a169';
            }
        }

        // Toggle Dalam Proses
        function toggleDalamProses() {
            const dalamProses = document.getElementById('dokumen_dalam_proses').checked;
            const kadaluarsaInput = document.getElementById('dokumen_kadaluarsa');
            const permanentCheck = document.getElementById('dokumen_permanent');
            const labelKadaluarsa = document.getElementById('label_kadaluarsa');
            
            if(dalamProses) {
                kadaluarsaInput.required = false;
                labelKadaluarsa.textContent = 'Tanggal Kadaluarsa (Opsional)';
                permanentCheck.disabled = true;
                permanentCheck.checked = false;
            } else {
                if(!permanentCheck.checked) {
                    kadaluarsaInput.required = true;
                    labelKadaluarsa.textContent = 'Tanggal Kadaluarsa *';
                }
                permanentCheck.disabled = false;
            }
            updateStatusPreview();
        }

        // Toggle Kadaluarsa Field
        function toggleKadaluarsa() {
            const isPermanent = document.getElementById('dokumen_permanent').checked;
            const kadaluarsaInput = document.getElementById('dokumen_kadaluarsa');
            const dalamProsesCheck = document.getElementById('dokumen_dalam_proses');
            const labelKadaluarsa = document.getElementById('label_kadaluarsa');
            
            if(isPermanent) {
                kadaluarsaInput.disabled = true;
                kadaluarsaInput.required = false;
                kadaluarsaInput.value = '9999-12-31';
                labelKadaluarsa.textContent = 'Tanggal Kadaluarsa (Permanen)';
                dalamProsesCheck.disabled = true;
                dalamProsesCheck.checked = false;
            } else {
                kadaluarsaInput.disabled = false;
                const dalamProses = dalamProsesCheck.checked;
                kadaluarsaInput.required = !dalamProses;
                kadaluarsaInput.value = '';
                labelKadaluarsa.textContent = dalamProses ? 'Tanggal Kadaluarsa (Opsional)' : 'Tanggal Kadaluarsa *';
                dalamProsesCheck.disabled = false;
            }
            updateStatusPreview();
        }

        // Modal Dokumen Functions
        function openModal() {
            document.getElementById('modalDokumenTitle').textContent = '➕ Tambah Dokumen Legalitas';
            document.getElementById('formDokumen').action = 'add.php';
            document.getElementById('formDokumen').reset();
            document.getElementById('dokumen_id').value = '';
            document.getElementById('dokumen_kadaluarsa').disabled = false;
            document.getElementById('dokumen_kadaluarsa').required = true;
            document.getElementById('dokumen_dalam_proses').disabled = false;
            document.getElementById('dokumen_permanent').disabled = false;
            document.getElementById('label_kadaluarsa').textContent = 'Tanggal Kadaluarsa *';
            document.getElementById('status_info').style.display = 'none';
            document.getElementById('modalDokumen').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('modalDokumen').style.display = 'none';
        }

        function editDokumen(data) {
            document.getElementById('modalDokumenTitle').textContent = '✏️ Edit Dokumen Legalitas';
            document.getElementById('formDokumen').action = 'update.php';
            document.getElementById('dokumen_id').value = data.id;
            document.getElementById('dokumen_jenis').value = data.jenis_dokumen;
            document.getElementById('dokumen_nomor').value = data.nomor_dokumen;
            document.getElementById('dokumen_terbit').value = data.tanggal_terbit;
            
            // Handle permanent document
            const isPermanent = data.is_permanent == 1;
            document.getElementById('dokumen_permanent').checked = isPermanent;
            
            // Handle dalam proses
            const dalamProses = data.status === 'Dalam Proses';
            document.getElementById('dokumen_dalam_proses').checked = dalamProses;
            
            if(isPermanent) {
                document.getElementById('dokumen_kadaluarsa').value = '';
                document.getElementById('dokumen_kadaluarsa').disabled = true;
                document.getElementById('dokumen_kadaluarsa').required = false;
                document.getElementById('dokumen_dalam_proses').disabled = true;
                document.getElementById('label_kadaluarsa').textContent = 'Tanggal Kadaluarsa (Permanen)';
            } else if(dalamProses) {
                if(data.tanggal_kadaluarsa && data.tanggal_kadaluarsa !== '9999-12-31') {
                    document.getElementById('dokumen_kadaluarsa').value = data.tanggal_kadaluarsa;
                } else {
                    document.getElementById('dokumen_kadaluarsa').value = '';
                }
                document.getElementById('dokumen_kadaluarsa').disabled = false;
                document.getElementById('dokumen_kadaluarsa').required = false;
                document.getElementById('dokumen_permanent').disabled = true;
                document.getElementById('label_kadaluarsa').textContent = 'Tanggal Kadaluarsa (Opsional)';
            } else {
                document.getElementById('dokumen_kadaluarsa').value = data.tanggal_kadaluarsa;
                document.getElementById('dokumen_kadaluarsa').disabled = false;
                document.getElementById('dokumen_kadaluarsa').required = true;
                document.getElementById('dokumen_dalam_proses').disabled = false;
                document.getElementById('dokumen_permanent').disabled = false;
                document.getElementById('label_kadaluarsa').textContent = 'Tanggal Kadaluarsa *';
            }
            
            document.getElementById('dokumen_instansi').value = data.instansi_penerbit;
            document.getElementById('dokumen_keterangan').value = data.keterangan || '';
            updateStatusPreview();
            document.getElementById('modalDokumen').style.display = 'block';
        }

        function viewDokumen(data) {
            document.getElementById('view_jenis').textContent = data.jenis_dokumen;
            document.getElementById('view_nomor').textContent = data.nomor_dokumen;
            document.getElementById('view_terbit').textContent = formatDate(data.tanggal_terbit);
            
            // Handle permanent document display
            if(data.is_permanent == 1) {
                document.getElementById('view_kadaluarsa').innerHTML = '<span class="permanent-badge">Permanen</span>';
                document.getElementById('view_sisa_waktu').innerHTML = '<span class="permanent-badge">Tidak Ada Batas Waktu</span>';
            } else if(data.status === 'Dalam Proses' && (!data.tanggal_kadaluarsa || data.tanggal_kadaluarsa === '9999-12-31')) {
                document.getElementById('view_kadaluarsa').innerHTML = '<span class="process-badge">Belum Ditentukan</span>';
                document.getElementById('view_sisa_waktu').innerHTML = '<span class="process-badge">Dalam Proses</span>';
            } else {
                document.getElementById('view_kadaluarsa').textContent = formatDate(data.tanggal_kadaluarsa);
                document.getElementById('view_sisa_waktu').innerHTML = calculateRemainingTime(data.tanggal_kadaluarsa, false);
            }
            
            document.getElementById('view_instansi').textContent = data.instansi_penerbit;
            document.getElementById('view_status').innerHTML = '<span class="status-badge status-' + data.status.toLowerCase().replace(/ /g, '-') + '">' + data.status + '</span>';
            document.getElementById('view_keterangan').textContent = data.keterangan || '-';
            
            // Handle File Preview based on type
            const pdfSection = document.getElementById('pdfPreviewSection');
            const pdfViewer = document.getElementById('pdfViewer');
            const pdfHeader = pdfSection.querySelector('.form-section-title');
            
            if(data.file_dokumen) {
                currentPDFUrl = data.file_dokumen;
                const fileExt = data.file_dokumen.split('.').pop().toLowerCase();
                
                pdfSection.style.display = 'block';
                
                // Check file type and show appropriate preview
                if(fileExt === 'pdf') {
                    pdfHeader.textContent = '📄 Preview Dokumen PDF';
                    pdfViewer.style.display = 'block';
                    pdfViewer.src = data.file_dokumen;
                } else if(['jpg', 'jpeg', 'png'].includes(fileExt)) {
                    pdfHeader.textContent = '🖼️ Preview Gambar';
                    pdfViewer.style.display = 'block';
                    pdfViewer.innerHTML = '<img src="' + data.file_dokumen + '" style="width: 100%; max-width: 800px; height: auto; border-radius: 8px;">';
                } else if(['doc', 'docx'].includes(fileExt)) {
                    pdfHeader.textContent = '📝 Dokumen Word';
                    pdfViewer.style.display = 'none';
                } else if(['zip', 'rar'].includes(fileExt)) {
                    pdfHeader.textContent = '📦 File Archive';
                    pdfViewer.style.display = 'none';
                } else {
                    pdfHeader.textContent = '📄 File Dokumen';
                    pdfViewer.style.display = 'none';
                }
            } else {
                pdfSection.style.display = 'none';
                currentPDFUrl = '';
            }
            
            document.getElementById('modalViewDokumen').style.display = 'block';
        }

        function closeViewDokumen() {
            document.getElementById('modalViewDokumen').style.display = 'none';
            const pdfViewer = document.getElementById('pdfViewer');
            pdfViewer.src = '';
            pdfViewer.innerHTML = '';
            currentPDFUrl = '';
        }

        function downloadPDF() {
            if(currentPDFUrl) {
                const link = document.createElement('a');
                link.href = currentPDFUrl;
                link.download = currentPDFUrl.split('/').pop();
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        function openPDFNewTab() {
            if(currentPDFUrl) {
                window.open(currentPDFUrl, '_blank');
            }
        }

        // Modal Pengurus Functions
        function openPengurusModal() {
            document.getElementById('modalPengurusTitle').textContent = '👥 Tambah Pengurus';
            document.getElementById('formPengurus').action = '../pengurus/add.php';
            document.getElementById('formPengurus').reset();
            document.getElementById('pengurus_id').value = '';
            document.getElementById('fotoPreview').innerHTML = '<span class="foto-icon">👤</span>';
            document.getElementById('jabatanCustom').style.display = 'none';
            document.getElementById('modalPengurus').style.display = 'block';
        }
        
        function closePengurusModal() {
            document.getElementById('modalPengurus').style.display = 'none';
        }

        function editPengurus(data) {
            document.getElementById('modalPengurusTitle').textContent = '✏️ Edit Pengurus';
            document.getElementById('formPengurus').action = '../pengurus/update.php';
            document.getElementById('pengurus_id').value = data.id;
            document.getElementById('pengurus_nik').value = data.nik || '';
            document.getElementById('pengurus_nama').value = data.nama;
            document.getElementById('pengurus_tempat_lahir').value = data.tempat_lahir || '';
            document.getElementById('pengurus_tanggal_lahir').value = data.tanggal_lahir || '';
            
            // Set jabatan
            const jabatanSelect = document.getElementById('jabatanSelect');
            const jabatanOptions = Array.from(jabatanSelect.options).map(opt => opt.value);
            if(jabatanOptions.includes(data.jabatan)) {
                jabatanSelect.value = data.jabatan;
            } else {
                jabatanSelect.value = 'custom';
                document.getElementById('jabatanCustom').value = data.jabatan;
                document.getElementById('jabatanCustom').style.display = 'block';
            }
            
            document.getElementById('pengurus_periode').value = data.periode || '';
            document.getElementById('pengurus_no_sk').value = data.no_sk || '';
            document.getElementById('pengurus_tanggal_sk').value = data.tanggal_sk || '';
            document.getElementById('pengurus_email').value = data.email || '';
            document.getElementById('pengurus_telepon').value = data.telepon || '';
            document.getElementById('pengurus_alamat').value = data.alamat || '';
            document.getElementById('pengurus_pendidikan').value = data.pendidikan_terakhir || '';
            document.getElementById('pengurus_status').value = data.status || 'Aktif';
            document.getElementById('pengurus_foto_url').value = data.foto_url || '';
            
            // Preview foto
            if(data.foto) {
                document.getElementById('fotoPreview').innerHTML = '<img src="' + data.foto + '" alt="Foto">';
            } else if(data.foto_url) {
                document.getElementById('fotoPreview').innerHTML = '<img src="' + data.foto_url + '" alt="Foto">';
            } else {
                document.getElementById('fotoPreview').innerHTML = '<span class="foto-icon">👤</span>';
            }
            
            document.getElementById('modalPengurus').style.display = 'block';
        }

        function viewPengurus(data) {
            document.getElementById('detail_nama').textContent = data.nama;
            document.getElementById('detail_jabatan').textContent = data.jabatan;
            document.getElementById('detail_periode').textContent = 'Periode: ' + (data.periode || '-');
            document.getElementById('detail_nik').textContent = data.nik || '-';
            document.getElementById('detail_ttl').textContent = (data.tempat_lahir || '-') + ', ' + (data.tanggal_lahir ? formatDate(data.tanggal_lahir) : '-');
            document.getElementById('detail_no_sk').textContent = data.no_sk || '-';
            document.getElementById('detail_tanggal_sk').textContent = data.tanggal_sk ? formatDate(data.tanggal_sk) : '-';
            document.getElementById('detail_email').textContent = data.email || '-';
            document.getElementById('detail_telepon').textContent = data.telepon || '-';
            document.getElementById('detail_alamat').textContent = data.alamat || '-';
            document.getElementById('detail_pendidikan').textContent = data.pendidikan_terakhir || '-';
            document.getElementById('detail_status').innerHTML = '<span class="status-badge status-' + (data.status === 'Aktif' ? 'aktif' : 'tidak-aktif') + '">' + data.status + '</span>';
            
            // Foto
            const fotoDetail = document.getElementById('detail_foto');
            if(data.foto) {
                fotoDetail.innerHTML = '<img src="' + data.foto + '" alt="' + data.nama + '">';
            } else if(data.foto_url) {
                fotoDetail.innerHTML = '<img src="' + data.foto_url + '" alt="' + data.nama + '">';
            } else {
                fotoDetail.innerHTML = '<span class="foto-icon">👤</span>';
            }
            
            document.getElementById('modalViewPengurus').style.display = 'block';
        }

        function closeViewPengurus() {
            document.getElementById('modalViewPengurus').style.display = 'none';
        }

        // Utility Functions
        function formatDate(dateString) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const date = new Date(dateString);
            return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Handle jabatan custom
        document.getElementById('jabatanSelect').addEventListener('change', function() {
            const customInput = document.getElementById('jabatanCustom');
            if (this.value === 'custom') {
                customInput.style.display = 'block';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
            }
        });

        // Preview foto
        document.getElementById('fotoInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Auto close toast
        setTimeout(function() {
            const toast = document.getElementById('toast');
            if(toast) {
                toast.style.display = 'none';
            }
        }, 5000);

        // Live Search untuk Dokumen
        const searchInput = document.getElementById('searchInput');
        if(searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    const searchValue = e.target.value.toLowerCase();
                    const documentCards = document.querySelectorAll('.document-card');
                    
                    documentCards.forEach(function(card) {
                        const jenisDokumen = card.querySelector('h3').textContent.toLowerCase();
                        const nomorDokumen = card.querySelector('.document-number').textContent.toLowerCase();
                        
                        if(jenisDokumen.includes(searchValue) || nomorDokumen.includes(searchValue)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }, 300);
            });
        }

        // Live Search untuk Pengurus
        const searchPengurus = document.getElementById('searchPengurus');
        if(searchPengurus) {
            let pengurusSearchTimeout;
            searchPengurus.addEventListener('input', function(e) {
                clearTimeout(pengurusSearchTimeout);
                pengurusSearchTimeout = setTimeout(function() {
                    const searchValue = e.target.value.toLowerCase();
                    const pengurusCards = document.querySelectorAll('.pengurus-card');
                    
                    pengurusCards.forEach(function(card) {
                        const nama = card.querySelector('.pengurus-info h3').textContent.toLowerCase();
                        const jabatan = card.querySelector('.pengurus-jabatan').textContent.toLowerCase();
                        const periode = card.querySelector('.pengurus-periode').textContent.toLowerCase();
                        
                        if(nama.includes(searchValue) || jabatan.includes(searchValue) || periode.includes(searchValue)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }, 300);
            });
        }

        // Add event listeners for date changes
        document.addEventListener('DOMContentLoaded', function() {
            const kadaluarsaInput = document.getElementById('dokumen_kadaluarsa');
            const permanentCheck = document.getElementById('dokumen_permanent');
            const dalamProsesCheck = document.getElementById('dokumen_dalam_proses');
            
            if (kadaluarsaInput) {
                kadaluarsaInput.addEventListener('change', updateStatusPreview);
            }
            
            if (permanentCheck) {
                permanentCheck.addEventListener('change', updateStatusPreview);
            }
            
            if (dalamProsesCheck) {
                dalamProsesCheck.addEventListener('change', updateStatusPreview);
            }
        });
    </script>
</body>
</html>
