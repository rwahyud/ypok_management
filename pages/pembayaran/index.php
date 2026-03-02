<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mendapatkan data pembayaran dengan filter search
$sql = "SELECT p.*, 
        k.nama as nama_kohai,
        m.nama as nama_msh
        FROM pembayaran p
        LEFT JOIN kohai k ON p.kohai_id = k.id
        LEFT JOIN majelis_sabuk_hitam m ON p.msh_id = m.id
        WHERE COALESCE(k.nama, '') LIKE :search
        OR COALESCE(m.nama, '') LIKE :search
        OR COALESCE(p.keterangan, '') LIKE :search
        OR COALESCE(p.jenis_pembayaran, '') LIKE :search
        OR COALESCE(p.metode_pembayaran, '') LIKE :search
        OR COALESCE(p.status, '') LIKE :search
        OR TO_CHAR(p.tanggal_bayar, 'DD/MM/YYYY') LIKE :search
        ORDER BY p.tanggal_bayar DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$pembayaran_list = $stmt->fetchAll();

// Hitung total per kategori
$stmt_ujian = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM pembayaran WHERE jenis_pembayaran = 'Ujian' AND status = 'lunas'");
$total_ujian = $stmt_ujian->fetch()['total'];

$stmt_kyu = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM pembayaran WHERE jenis_pembayaran = 'Kyu' AND status = 'lunas'");
$total_kyu = $stmt_kyu->fetch()['total'];

$stmt_rakernas = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM pembayaran WHERE jenis_pembayaran = 'Rakernas' AND status = 'lunas'");
$total_rakernas = $stmt_rakernas->fetch()['total'];

// Get existing categories for dropdown
$stmt_kategori = $pdo->query("SELECT DISTINCT jenis_pembayaran FROM pembayaran ORDER BY jenis_pembayaran");
$existing_categories = $stmt_kategori->fetchAll(PDO::FETCH_COLUMN);

// Get kohai list for dropdown
$stmt_kohai = $pdo->query("SELECT id, nama, kode_kohai FROM kohai WHERE status='aktif' ORDER BY nama");
$kohai_list = $stmt_kohai->fetchAll();

// Get MSH list for dropdown
$stmt_msh = $pdo->query("SELECT id, nama, kode_msh FROM majelis_sabuk_hitam WHERE status='aktif' ORDER BY nama");
$msh_list = $stmt_msh->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembayaran - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pembayaran.css">
</head>
<body>
   <?php include '../../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil ditambahkan'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
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
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">💰 Pembayaran</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="content-header">
                <div>
                    <h1>💰 Data Pembayaran</h1>
                </div>
                
                <div class="header-actions">
                    <div class="search-bar">
                        <form method="GET" style="display: flex; gap: 15px; flex: 1; position: relative;" id="searchForm">
                            <input type="text" name="search" id="searchInput" placeholder="🔍 Cari pembayaran (nama, kategori, tanggal, metode, status)..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" style="padding-right: 40px;">
                            <?php if($search): ?>
                        
                            <?php endif; ?>
                        </form>
                    </div>
                    <button class="btn-secondary" onclick="openExportModal()">
                        📄 Export PDF
                    </button>
                    <button class="btn-primary" onclick="openPembayaranModal()">
                        ➕ Tambah Pembayaran
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 30px; gap: 20px;">
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-info">
                        <h3>PEMASUKAN UJIAN</h3>
                        <div class="stat-value">Rp <?php echo number_format($total_ujian, 0, ',', '.'); ?></div>
                        <div class="stat-label">DAN 1-9</div>
                    </div>
                </div>
                
                <div class="stat-card stat-card-green">
                    <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK" class="stat-icon" style="width: 48px; height: 48px; object-fit: contain;">
                    <div class="stat-info">
                        <h3>PEMASUKAN KYU</h3>
                        <div class="stat-value">Rp <?php echo number_format($total_kyu, 0, ',', '.'); ?></div>
                        <div class="stat-label">KYU 1-9</div>
                    </div>
                </div>
                
                <div class="stat-card stat-card-orange">
                    <div class="stat-icon">🏛️</div>
                    <div class="stat-info">
                        <h3>PEMASUKAN RAKERNAS</h3>
                        <div class="stat-value">Rp <?php echo number_format($total_rakernas, 0, ',', '.'); ?></div>
                        <div class="stat-label">NASIONAL</div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>TANGGAL</th>
                            <th>KATEGORI</th>
                            <th>NAMA</th>
                            <th>KETERANGAN</th>
                            <th>JUMLAH</th>
                            <th>METODE</th>
                            <th>STATUS</th>
                            <th>SISA</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($pembayaran_list)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px;">
                                    Tidak ada data pembayaran
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($pembayaran_list as $pembayaran): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pembayaran['tanggal_bayar'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                        echo $pembayaran['jenis_pembayaran'] == 'Ujian' ? 'primary' :
                                            ($pembayaran['jenis_pembayaran'] == 'Kyu' ? 'info' :
                                            ($pembayaran['jenis_pembayaran'] == 'Rakernas' ? 'warning' : 'secondary'));
                                    ?>">
                                        <?php echo htmlspecialchars($pembayaran['jenis_pembayaran']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($pembayaran['nama_kohai'])) {
                                        echo '🥋 ' . htmlspecialchars($pembayaran['nama_kohai']);
                                    } elseif (!empty($pembayaran['nama_msh'])) {
                                        echo '👨‍🎓 ' . htmlspecialchars($pembayaran['nama_msh']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($pembayaran['keterangan']); ?></td>
                                <td class="text-success" style="font-weight: bold;">
                                    Rp <?php echo number_format($pembayaran['jumlah'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($pembayaran['metode_pembayaran'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                        echo $pembayaran['status'] == 'lunas' ? 'success' :
                                            ($pembayaran['status'] == 'pending' ? 'warning' : 'danger');
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pembayaran['status']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #a0aec0;">-</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-info" onclick="viewPembayaran(<?php echo $pembayaran['id']; ?>)" title="Lihat Detail">
                                            👁️
                                        </button>
                                        <button class="btn-icon btn-success" onclick="printInvoice(<?php echo $pembayaran['id']; ?>)" title="Invoice / Print">
                                            🧾
                                        </button>
                                        <button class="btn-icon btn-warning" onclick="editPembayaran(<?php echo $pembayaran['id']; ?>)" title="Edit">
                                            ✏️
                                        </button>
                                        <button class="btn-icon btn-danger" onclick="deletePembayaran(<?php echo $pembayaran['id']; ?>)" title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah/Edit Pembayaran -->
    <div id="pembayaranModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Pembayaran</h2>
                <span class="close" onclick="closePembayaranModal()">&times;</span>
            </div>
            <form id="pembayaranForm" method="POST" action="actions/pembayaran_action.php">
                <input type="hidden" name="action" id="formAction" value="tambah">
                <input type="hidden" name="id" id="pembayaranId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal">📅 Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_select">📂 Kategori *</label>
                        <select id="kategori_select" name="kategori_select" onchange="toggleKategoriInput()" required>
                            <option value="">Pilih Kategori...</option>
                            <option value="Ujian">🎓 Ujian</option>
                            <option value="Kyu">🥋 Kyu</option>
                            <option value="Rakernas">🏛️ Rakernas</option>
                            <option value="Iuran Bulanan">💳 Iuran Bulanan</option>
                            <option value="Seragam">👕 Seragam</option>
                            <option value="Sabuk">🎗️ Sabuk</option>
                            <?php foreach($existing_categories as $cat): ?>
                                <?php if(!in_array($cat, ['Ujian', 'Kyu', 'Rakernas', 'Iuran Bulanan', 'Seragam', 'Sabuk'])): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">📌 <?php echo htmlspecialchars($cat); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="lainnya">➕ Kategori Lainnya (Tulis Sendiri)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="kategori_custom_group" style="display: none;">
                    <label for="kategori_custom">✍️ Tulis Kategori Baru *</label>
                    <input type="text" id="kategori_custom" name="kategori_custom" placeholder="Masukkan kategori baru...">
                </div>
                <input type="hidden" id="kategori" name="kategori">

                <div class="form-group">
                    <label>👥 Tipe Anggota *</label>
                    <div style="display: flex; gap: 20px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="tipe_anggota" value="kohai" onchange="toggleAnggotaDropdown()" checked>
                            <span style="margin-left: 8px;">🥋 Kohai</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="tipe_anggota" value="msh" onchange="toggleAnggotaDropdown()">
                            <span style="margin-left: 8px;">👨‍🎓 Majelis Sabuk Hitam</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="kohai_select_group">
                    <label for="kohai_id">👤 Pilih Kohai *</label>
                    <select id="kohai_id" name="kohai_id">
                        <option value="">Pilih Kohai...</option>
                        <?php foreach($kohai_list as $kohai): ?>
                            <option value="<?php echo $kohai['id']; ?>">
                                <?php echo htmlspecialchars($kohai['nama']); ?> 
                                (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="msh_select_group" style="display: none;">
                    <label for="msh_id">👤 Pilih Majelis Sabuk Hitam *</label>
                    <select id="msh_id" name="msh_id">
                        <option value="">Pilih MSH...</option>
                        <?php foreach($msh_list as $msh): ?>
                            <option value="<?php echo $msh['id']; ?>">
                                <?php echo htmlspecialchars($msh['nama']); ?> 
                                (<?php echo htmlspecialchars($msh['kode_msh']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">📝 Keterangan *</label>
                    <textarea id="keterangan" name="keterangan" rows="3" placeholder="Deskripsi detail pembayaran..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah">💰 Jumlah Pembayaran *</label>
                        <div class="input-with-icon">
                            <input type="number" id="jumlah" name="jumlah" placeholder="0" required min="0" step="1000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="metode_pembayaran">💳 Metode Pembayaran *</label>
                        <select id="metode_pembayaran" name="metode_pembayaran" required>
                            <option value="">Pilih Metode...</option>
                            <option value="Tunai">💵 Tunai</option>
                            <option value="Transfer">🏦 Transfer Bank</option>
                            <option value="E-Wallet">📱 E-Wallet</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">✅ Status Pembayaran *</label>
                    <select id="status" name="status" required>
                        <option value="lunas">✅ Lunas</option>
                        <option value="belum_bayar">❌ Belum Bayar</option>
                        <option value="pending">⏳ Pending</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closePembayaranModal()">
                        ✖️ Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        💾 Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal View Detail -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔍 Detail Pembayaran</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="viewContent" class="detail-view">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeViewModal()">
                    ✖️ Tutup
                </button>
            </div>
        </div>
    </div>

     <!-- Modal Export -->
    <div class="modal-overlay export-modal-overlay" id="modalExport">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3>
                    <span>📊</span>
                    <span>Export Laporan Pembayaran</span>
                </h3>
                <button class="export-modal-close" onclick="closeExportModal()" type="button">×</button>
            </div>
            
            <form id="formExport" onsubmit="handleExportSubmit(event)">
                <div class="export-modal-body">
                    <div class="export-form-group">
                        <label>Format Export</label>
                        <select name="format" id="formatExport" required>
                            <option value="">Pilih Format...</option>
                            <option value="pdf">📄 PDF Document (.pdf)</option>
                            <option value="excel">📊 Excel Spreadsheet (.xlsx)</option>
                            <option value="csv">📋 CSV File (.csv)</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group">
                        <label>Pilih Periode</label>
                        <select name="periode" id="exportPeriode" required onchange="toggleCustomDate()">
                            <option value="month">Bulan Ini</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="custom">Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group" id="customDateRange" style="display: none;">
                        <label>Range Tanggal</label>
                        <div class="export-signature-row">
                            <input type="date" name="dari_tanggal" id="startDate" class="export-signature-input" placeholder="Dari Tanggal">
                            <input type="date" name="sampai_tanggal" id="endDate" class="export-signature-input" placeholder="Sampai Tanggal">
                        </div>
                    </div>
                    
                    <div class="export-signature-section">
                        <div class="export-signature-title">Tanda Tangan Digital</div>
                        <div class="export-signature-row">
                            <input type="text" name="ketua_ypok" id="exportKetua" class="export-signature-input" placeholder="Ketua YPOK" value="Ketua YPOK" required>
                            <input type="text" name="admin_pembuat" id="exportAdmin" class="export-signature-input" placeholder="Pembuat Laporan" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly required style="background: #f0f0f0; cursor: not-allowed;">
                        </div>
                    </div>
                </div>
                
                <div class="export-modal-footer">
                    <button type="button" class="export-btn" onclick="resetTanggalExport()" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                        <span>🔄</span>
                        <span>Reset Tanggal</span>
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
    <script src="assets/js/pembayaran.js"></script>
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

        // Clear search function
        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            if (searchInput && searchForm) {
                searchInput.value = '';
                searchForm.submit();
            }
        }

        function openExportModal() {
            document.getElementById('modalExport').style.display = 'flex';
            // Reset form
            document.getElementById('formExport').reset();
            document.getElementById('customDateRange').style.display = 'none';
            document.getElementById('exportAdmin').value = '<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>';
            document.getElementById('exportKetua').value = 'Ketua YPOK';
        }
        
        function closeExportModal() {
            document.getElementById('modalExport').style.display = 'none';
        }
        
        function toggleCustomDate() {
            const periode = document.getElementById('exportPeriode').value;
            const customDateRange = document.getElementById('customDateRange');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            if (periode === 'custom') {
                customDateRange.style.display = 'block';
                startDate.required = true;
                endDate.required = true;
            } else {
                customDateRange.style.display = 'none';
                startDate.required = false;
                endDate.required = false;
            }
        }

        function resetTanggalExport() {
            // Reset periode to default (Bulan Ini)
            document.getElementById('exportPeriode').value = 'month';

            // Hide and clear custom date range
            document.getElementById('customDateRange').style.display = 'none';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('startDate').required = false;
            document.getElementById('endDate').required = false;

            // Show success notification
            alert('✅ Filter tanggal telah di-reset ke "Bulan Ini"');
        }
        
        function handleExportSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Validasi format
            const format = formData.get('format_export');
            if (!format) {
                alert('⚠️ Mohon pilih format export terlebih dahulu');
                return;
            }
            
            // Validasi periode
            const periode = formData.get('periode');
            if (!periode) {
                alert('⚠️ Mohon pilih periode terlebih dahulu');
                return;
            }
            
            // Validasi custom date
            if (periode === 'custom') {
                const dariTanggal = formData.get('dari_tanggal');
                const sampaiTanggal = formData.get('sampai_tanggal');
                
                if (!dariTanggal || !sampaiTanggal) {
                    alert('⚠️ Mohon lengkapi tanggal mulai dan tanggal akhir');
                    return;
                }
                
                if (new Date(dariTanggal) > new Date(sampaiTanggal)) {
                    alert('⚠️ Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                    return;
                }
            }
            
            // Validasi tanda tangan
            const ketuaYpok = formData.get('ketua_ypok');
            if (!ketuaYpok || ketuaYpok.trim() === '') {
                alert('⚠️ Mohon isi nama Ketua YPOK');
                return;
            }
            
            // Build query string
            const params = new URLSearchParams();
            params.append('format', format);
            params.append('periode', periode);
            params.append('ketua_ypok', ketuaYpok);
            params.append('admin_pembuat', formData.get('admin_pembuat'));
            
            if (periode === 'custom') {
                params.append('dari_tanggal', formData.get('dari_tanggal'));
                params.append('sampai_tanggal', formData.get('sampai_tanggal'));
            }
            
            // Open export page in new tab
            const url = `actions/export_pembayaran.php?${params.toString()}`;
            window.open(url, '_blank');
            
            // Close modal
            closeExportModal();
        }
        
        // Close modal when clicking outside
        document.getElementById('modalExport')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeExportModal();
            }
        });
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('modalExport').style.display === 'flex') {
                closeExportModal();
            }
        });

        // Toggle dropdown kohai/MSH berdasarkan radio button selection
        function toggleAnggotaDropdown() {
            const tipeAnggota = document.querySelector('input[name="tipe_anggota"]:checked').value;
            const kohaiGroup = document.getElementById('kohai_select_group');
            const mshGroup = document.getElementById('msh_select_group');
            const kohaiSelect = document.getElementById('kohai_id');
            const mshSelect = document.getElementById('msh_id');

            if (tipeAnggota === 'kohai') {
                kohaiGroup.style.display = 'block';
                mshGroup.style.display = 'none';
                kohaiSelect.required = true;
                mshSelect.required = false;
                mshSelect.value = ''; // Clear MSH selection
            } else {
                kohaiGroup.style.display = 'none';
                mshGroup.style.display = 'block';
                kohaiSelect.required = false;
                mshSelect.required = true;
                kohaiSelect.value = ''; // Clear kohai selection
            }
        }
    </script>
</body>
</html>
