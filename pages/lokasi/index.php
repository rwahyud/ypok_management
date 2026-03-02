<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if($search) {
    $where = "WHERE nama_provinsi LIKE :search
              OR ibu_kota LIKE :search
              OR TO_CHAR(created_at, 'DD/MM/YYYY') LIKE :search";
}

$stmt = $pdo->prepare("SELECT * FROM provinsi $where ORDER BY created_at DESC");
if($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$provinsi_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lokasi - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/lokasi.css">
    <!-- Remove PWA manifest if not needed -->
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
            <h2 class="page-title">Data Lokasi</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="content-header">
                <h1>Data Provinsi</h1>
                
                <div class="search-bar">
                    <form method="GET" style="display: flex; gap: 15px; flex: 1;" id="searchForm">
                        <input type="text" name="search" id="searchInput" placeholder="🔍 Cari provinsi, ibu kota, atau tanggal..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                    </form>
                    <button class="btn-primary" onclick="openProvinsiModal()">
                        📍 Tambah Provinsi
                    </button>
                </div>
            </div>

            <!-- Provinsi Cards Grid -->
            <div class="provinsi-grid">
                <?php if(count($provinsi_list) > 0): ?>
                    <?php foreach($provinsi_list as $provinsi): ?>
                        <?php
                        // Gunakan data agregat langsung dari tabel provinsi
                        $stat = [
                            'total_dojo' => $provinsi['total_dojo'] ?? 0,
                            'total_anggota' => $provinsi['total_anggota'] ?? 0,
                            'anggota_aktif' => $provinsi['anggota_aktif'] ?? 0,
                            'anggota_non_aktif' => $provinsi['anggota_non_aktif'] ?? 0
                        ];
                        ?>
                        <div class="provinsi-card" onclick="viewProvinsiDetail(<?php echo $provinsi['id']; ?>)">
                            <div class="provinsi-card-inner">
                                <div class="provinsi-logo-wrapper">
                                    <?php if(isset($provinsi['logo_provinsi']) && !empty($provinsi['logo_provinsi'])): ?>
                                        <img src="<?php echo htmlspecialchars($provinsi['logo_provinsi']); ?>" alt="<?php echo htmlspecialchars($provinsi['nama_provinsi']); ?>" class="provinsi-logo-img">
                                    <?php else: ?>
                                        <div class="provinsi-logo-default">🏛️</div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="provinsi-title"><?php echo strtoupper(htmlspecialchars($provinsi['nama_provinsi'])); ?></h3>
                            </div>
                            
                            <div class="provinsi-actions-hover">
                                <button class="btn-icon-hover" onclick="event.stopPropagation(); editProvinsi(<?php echo $provinsi['id']; ?>)" title="Edit">
                                    ✏️
                                </button>
                                <button class="btn-icon-hover btn-danger" onclick="event.stopPropagation(); deleteProvinsi(<?php echo $provinsi['id']; ?>)" title="Hapus">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📍</div>
                        <h3>Belum ada data provinsi</h3>
                        <p>Klik tombol "Tambah Provinsi" untuk menambahkan data</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Provinsi -->
    <div id="provinsiModal" class="modal">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h2 id="provinsiModalTitle">📍 Tambah Provinsi</h2>
                <button class="close-btn" onclick="closeProvinsiModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="provinsiForm" action="actions/provinsi_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="provinsi_id">
                    <input type="hidden" name="action" id="provinsi_action" value="create">
                    
                    <div class="form-group">
                        <label for="nama_provinsi">Nama Provinsi *</label>
                        <input type="text" id="nama_provinsi" name="nama_provinsi" placeholder="Masukkan nama provinsi" required>
                    </div>

                    <div class="form-group">
                        <label for="ibu_kota">Ibu Kota</label>
                        <input type="text" id="ibu_kota" name="ibu_kota" placeholder="Masukkan ibu kota">
                    </div>

                    <div class="form-group">
                        <label for="logo_provinsi">Logo Provinsi</label>
                        <input type="file" id="logo_provinsi" name="logo_provinsi" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="url_logo_eksternal">URL Logo Eksternal</label>
                        <input type="url" id="url_logo_eksternal" name="url_logo_eksternal" placeholder="https://...">
                        <small>Jika tidak upload file, bisa gunakan URL eksternal</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeProvinsiModal()">Batal</button>
                        <button type="submit" class="btn-primary">💾 Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Provinsi -->
    <div id="detailProvinsiModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>📍 Detail Provinsi</h2>
                <button class="close-btn" onclick="closeDetailProvinsiModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="detailProvinsiContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Dojo -->
    <div id="dojoModal" class="modal">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h2 id="dojoModalTitle">🥋 Tambah Dojo</h2>
                <button class="close-btn" onclick="closeDojoModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="dojoForm" action="actions/dojo_action.php" method="POST">
                    <input type="hidden" name="id" id="dojo_id">
                    <input type="hidden" name="provinsi_id" id="dojo_provinsi_id">
                    <input type="hidden" name="action" id="dojo_action" value="create">
                    
                    <div class="form-group">
                        <label for="nama_dojo">Nama Dojo *</label>
                        <input type="text" id="nama_dojo" name="nama_dojo" placeholder="contoh: Dojo Jakarta Pusat" required>
                    </div>

                    <div class="form-group">
                        <label for="alamat_lengkap">Alamat Lengkap *</label>
                        <textarea id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Jl. ..." required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_ketua">Nama Ketua *</label>
                            <input type="text" id="nama_ketua" name="nama_ketua" placeholder="contoh: Sensei Ahmad" required>
                        </div>

                        <div class="form-group">
                            <label for="no_telepon">No. Telepon *</label>
                            <input type="tel" id="no_telepon" name="no_telepon" placeholder="021-xxxxx" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_anggota">Total Anggota *</label>
                            <input type="number" id="total_anggota" name="total_anggota" value="0" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="anggota_aktif">Anggota Aktif *</label>
                            <input type="number" id="anggota_aktif" name="anggota_aktif" value="0" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="anggota_non_aktif">Anggota Non-Aktif *</label>
                            <input type="number" id="anggota_non_aktif" name="anggota_non_aktif" value="0" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeDojoModal()">Batal</button>
                        <button type="submit" class="btn-primary">💾 Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    <script src="assets/js/lokasi.js"></script>
    <script>
        // Clear URL parameters after showing toast
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // If there's a success/error parameter, show toast then clean URL
            if(urlParams.has('success') || urlParams.has('updated') || urlParams.has('deleted') || urlParams.has('error')) {
                // Wait for toast to be visible
                setTimeout(function() {
                    // Clean URL without reloading
                    const cleanUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 3000); // 3 seconds to read the message
            }
        });
    </script>
</body>
</html>
