<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    exit('<div style="text-align: center; padding: 40px; color: red;">Unauthorized</div>');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    exit('<div style="text-align: center; padding: 40px; color: red;">ID tidak valid</div>');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM provinsi WHERE id = ?");
    $stmt->execute([$id]);
    $provinsi = $stmt->fetch();

    if(!$provinsi) {
        exit('<div style="text-align: center; padding: 40px; color: red;">Provinsi tidak ditemukan</div>');
    }

    // Gunakan data agregat langsung dari tabel provinsi
    $stat = [
        'total_dojo' => $provinsi['total_dojo'] ?? 0,
        'total_anggota' => $provinsi['total_anggota'] ?? 0,
        'anggota_aktif' => $provinsi['anggota_aktif'] ?? 0,
        'anggota_non_aktif' => $provinsi['anggota_non_aktif'] ?? 0
    ];

    // Get all dojo
    $dojos = $pdo->prepare("SELECT * FROM dojo WHERE provinsi_id = ? ORDER BY created_at DESC");
    $dojos->execute([$id]);
    $dojo_list = $dojos->fetchAll();

} catch(Exception $e) {
    exit('<div style="text-align: center; padding: 40px; color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
}
?>

<div class="detail-provinsi-header">
    <div class="detail-header-content">
        <div class="detail-logo">
            <?php if($provinsi['logo_provinsi']): ?>
                <img src="<?php echo htmlspecialchars($provinsi['logo_provinsi']); ?>" alt="Logo">
            <?php else: ?>
                <div class="default-logo-large">🏛️</div>
            <?php endif; ?>
        </div>
        <div class="detail-info">
            <h1><?php echo strtoupper(htmlspecialchars($provinsi['nama_provinsi'])); ?></h1>
            <?php if($provinsi['ibu_kota']): ?>
                <p class="capital-info">
                    <span class="capital-icon">🏛️</span>
                    <span>Ibu Kota: <strong><?php echo htmlspecialchars($provinsi['ibu_kota']); ?></strong></span>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <div class="detail-header-decoration"></div>
</div>

<div class="detail-stats-grid">
    <div class="detail-stat-card stat-dojo">
        <img src="../assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK" class="stat-icon" style="width: 48px; height: 48px; object-fit: contain;">
        <div class="stat-content">
            <div class="stat-label">TOTAL DOJO</div>
            <div class="stat-value-large"><?php echo $stat['total_dojo']; ?></div>
        </div>
    </div>
    <div class="detail-stat-card stat-members">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <div class="stat-label">TOTAL ANGGOTA</div>
            <div class="stat-value-large"><?php echo $stat['total_anggota']; ?></div>
        </div>
    </div>
    <div class="detail-stat-card stat-active">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-label">ANGGOTA AKTIF</div>
            <div class="stat-value-large"><?php echo $stat['anggota_aktif']; ?></div>
        </div>
    </div>
    <div class="detail-stat-card stat-inactive">
        <div class="stat-icon">⏸️</div>
        <div class="stat-content">
            <div class="stat-label">ANGGOTA NON-AKTIF</div>
            <div class="stat-value-large"><?php echo $stat['anggota_non_aktif']; ?></div>
        </div>
    </div>
</div>

<div class="dojo-section">
    <div class="dojo-section-header">
        <h3>🥋 Daftar Dojo & Cabang</h3>
        <div class="dojo-actions">
            <input type="text" class="search-input-small" placeholder="🔍 Cari dojo (nama, ketua, telepon, alamat, status)..." id="searchDojo">
            <button class="btn-primary" onclick="openDojoModal(<?php echo $id; ?>)">➕ Tambah Dojo</button>
        </div>
    </div>

    <?php if(count($dojo_list) > 0): ?>
        <div class="dojo-cards-grid">
            <?php foreach($dojo_list as $dojo): ?>
                <div class="dojo-card">
                    <div class="dojo-card-header">
                        <div class="dojo-icon">🥋</div>
                        <div class="dojo-actions-btn">
                            <button class="btn-icon-small" onclick="editDojo(<?php echo $dojo['id']; ?>)" title="Edit">✏️</button>
                            <button class="btn-icon-small btn-danger" onclick="deleteDojo(<?php echo $dojo['id']; ?>, <?php echo $id; ?>)" title="Hapus">🗑️</button>
                        </div>
                    </div>
                    <div class="dojo-card-body">
                        <h4 class="dojo-name"><?php echo htmlspecialchars($dojo['nama_dojo']); ?></h4>
                        <p class="dojo-address">📍 <?php echo htmlspecialchars($dojo['alamat_lengkap']); ?></p>
                        <div class="dojo-info">
                            <p><strong>Ketua:</strong> <?php echo htmlspecialchars($dojo['nama_ketua']); ?></p>
                            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($dojo['no_telepon']); ?></p>
                        </div>
                        <div class="dojo-stats">
                            <div class="dojo-stat-item">
                                <span class="stat-num"><?php echo $dojo['total_anggota']; ?></span>
                                <span class="stat-text">Total</span>
                            </div>
                            <div class="dojo-stat-item active">
                                <span class="stat-num"><?php echo $dojo['anggota_aktif']; ?></span>
                                <span class="stat-text">Aktif</span>
                            </div>
                            <div class="dojo-stat-item inactive">
                                <span class="stat-num"><?php echo $dojo['anggota_non_aktif']; ?></span>
                                <span class="stat-text">Non-Aktif</span>
                            </div>
                        </div>
                        <div class="dojo-status">
                            <span class="status-badge <?php echo $dojo['status'] == 'Aktif' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $dojo['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state-small">
            <div class="empty-icon">🏪</div>
            <p>Belum ada dojo di provinsi ini</p>
            <button class="btn-primary" onclick="openDojoModal(<?php echo $id; ?>)">➕ Tambah Dojo Pertama</button>
        </div>
    <?php endif; ?>
</div>
