<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit('Unauthorized');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM majelis_sabuk_hitam WHERE id = ?");
$stmt->execute([$id]);
$msh = $stmt->fetch();

if(!$msh) {
    echo '<p style="text-align:center;color:red;">Data tidak ditemukan</p>';
    exit;
}

// Get Prestasi
$stmt_prestasi = $pdo->prepare("SELECT * FROM prestasi_msh WHERE msh_id = ?");
$stmt_prestasi->execute([$id]);
$prestasi_list = $stmt_prestasi->fetchAll();

// Get Sertifikasi
$stmt_sertifikasi = $pdo->prepare("SELECT * FROM sertifikasi_msh WHERE msh_id = ?");
$stmt_sertifikasi->execute([$id]);
$sertifikasi_list = $stmt_sertifikasi->fetchAll();
?>

<!-- Profile Banner -->
<div class="profile-banner">
    <div class="profile-photo">
        <?php if($msh['foto']): ?>
            <img src="<?php echo $msh['foto']; ?>" alt="Foto MSH">
        <?php else: ?>
            <span class="icon">👤</span>
        <?php endif; ?>
    </div>
    <div class="profile-info">
        <h1 class="profile-name"><?php echo htmlspecialchars($msh['nama']); ?></h1>
        <div class="profile-id"><?php echo $msh['nomor_sertifikat'] ?? 'MSH-' . str_pad($msh['id'], 3, '0', STR_PAD_LEFT); ?></div>
    </div>
</div>

<!-- Info Grid -->
<div class="info-grid">
    <div class="info-box">
        <div class="info-label">No. MSH</div>
        <div class="info-value"><?php echo $msh['nomor_sertifikat'] ?? 'MSH-' . str_pad($msh['id'], 3, '0', STR_PAD_LEFT); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Nama Lengkap</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['nama']); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Tempat, Tanggal Lahir</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['tempat_lahir'] ?? 'Jakarta') . ', ' . date('d F Y', strtotime($msh['tanggal_lahir'] ?? '2000-01-01')); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Jenis Kelamin</div>
        <div class="info-value"><?php echo $msh['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Tingkat Dan</div>
        <div class="info-value"><?php echo $msh['tingkat_dan'] ?? 'Dan 1'; ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Dojo/Cabang</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['dojo_cabang'] ?? 'Jakarta'); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">No. Telepon</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['no_telp']); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Email</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['email'] ?? '-'); ?></div>
    </div>
    
    <div class="info-box">
        <div class="info-label">Status</div>
        <div class="info-value">
            <span class="status-badge status-<?php echo $msh['status']; ?>">
                <?php echo ucfirst($msh['status']); ?>
            </span>
        </div>
    </div>
    
    <div class="info-box full">
        <div class="info-label">Alamat Lengkap</div>
        <div class="info-value"><?php echo htmlspecialchars($msh['alamat'] ?? 'Jl. Hilir Raya No.39'); ?></div>
    </div>
</div>

<!-- Prestasi Section -->
<div class="prestasi-section">
    <div class="prestasi-title">
        🏆 Prestasi & Penghargaan
    </div>
    <div class="prestasi-list">
        <?php if(count($prestasi_list) > 0): ?>
            <?php foreach($prestasi_list as $prestasi): ?>
                <div class="prestasi-item">
                    <div class="prestasi-icon">🥇</div>
                    <div class="prestasi-text"><?php echo htmlspecialchars($prestasi['nama_prestasi']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="prestasi-item">
                <div class="prestasi-icon">🥇</div>
                <div class="prestasi-text">Belum ada prestasi tercatat</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sertifikasi Section -->
<div class="sertifikasi-section">
    <div class="sertifikasi-title">
        📜 Rincian Sertifikasi
    </div>
    
    <?php if(count($sertifikasi_list) > 0): ?>
        <?php foreach($sertifikasi_list as $sert): ?>
            <?php
                $badgeClass = 'permanent';
                $badgeText = '✓ PERMANENT';
                $kadaluarsaText = 'Seumur Hidup';
                
                if($sert['status'] == 'valid') {
                    $badgeClass = 'valid';
                    $badgeText = '✓ VALID';
                    $kadaluarsaText = $sert['tanggal_kadaluarsa'] ? date('d F Y', strtotime($sert['tanggal_kadaluarsa'])) : '-';
                } elseif($sert['status'] == 'expired') {
                    $badgeClass = 'expired';
                    $badgeText = '✕ EXPIRED';
                    $kadaluarsaText = $sert['tanggal_kadaluarsa'] ? date('d F Y', strtotime($sert['tanggal_kadaluarsa'])) : '-';
                }
            ?>
            <div class="cert-card">
                <div class="cert-header">
                    <div class="cert-name">
                        📄 <?php echo htmlspecialchars($sert['nama_sertifikasi']); ?>
                    </div>
                    <span class="cert-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                </div>
                
                <div class="cert-number">
                    <strong>No:</strong> <?php echo htmlspecialchars($sert['nomor_sertifikat'] ?? '-'); ?>
                </div>
                
                <div class="cert-details">
                    <div class="cert-detail-item">
                        <div class="cert-detail-label">Penerbit</div>
                        <div class="cert-detail-value"><?php echo htmlspecialchars($sert['penerbit'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="cert-detail-item">
                        <div class="cert-detail-label">Level</div>
                        <div class="cert-detail-value"><?php echo htmlspecialchars($sert['level'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="cert-detail-item">
                        <div class="cert-detail-label">Tanggal Terbit</div>
                        <div class="cert-detail-value"><?php echo $sert['tanggal_terbit'] ? date('d F Y', strtotime($sert['tanggal_terbit'])) : '-'; ?></div>
                    </div>
                    
                    <div class="cert-detail-item">
                        <div class="cert-detail-label">Berlaku S/D</div>
                        <div class="cert-detail-value"><?php echo $kadaluarsaText; ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="cert-card">
            <p style="text-align: center; color: #64748b;">Belum ada sertifikasi tercatat</p>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<div class="detail-footer">
    <button class="btn-detail-close" onclick="closeDetail()">Tutup</button>
    <button class="btn-detail-edit" onclick="editData(<?php echo $msh['id']; ?>); closeDetail();">
        ✏️ Edit Data
    </button>
</div>
