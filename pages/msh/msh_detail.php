<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    echo '<p style="text-align:center;color:red;">Unauthorized</p>';
    exit();
}

$id = $_GET['id'] ?? null;

if(!$id) {
    echo '<p style="text-align:center;color:red;">ID tidak valid</p>';
    exit();
}

try {
    // Get MSH data
    $stmt = $pdo->prepare("SELECT * FROM majelis_sabuk_hitam WHERE id = ?");
    $stmt->execute([$id]);
    $msh = $stmt->fetch();
    
    if(!$msh) {
        echo '<p style="text-align:center;color:red;">Data tidak ditemukan</p>';
        exit();
    }
    
    // Get prestasi
    $stmt_prestasi = $pdo->prepare("SELECT * FROM prestasi_msh WHERE msh_id = ? ORDER BY created_at DESC");
    $stmt_prestasi->execute([$id]);
    $prestasi_list = $stmt_prestasi->fetchAll();
    
    // Get sertifikasi
    $stmt_sertifikasi = $pdo->prepare("SELECT * FROM sertifikasi_msh WHERE msh_id = ? ORDER BY tanggal_terbit DESC");
    $stmt_sertifikasi->execute([$id]);
    $sertifikasi_list = $stmt_sertifikasi->fetchAll();
    
?>
<div class="detail-container">
    <div class="detail-header">
        <?php if(!empty($msh['foto'])): ?>
            <img src="<?php echo htmlspecialchars($msh['foto']); ?>" alt="Foto" class="detail-photo">
        <?php else: ?>
            <div class="detail-photo-placeholder">👤</div>
        <?php endif; ?>
        <div>
            <h2><?php echo htmlspecialchars($msh['nama']); ?></h2>
            <p><strong>Kode MSH:</strong> <?php echo htmlspecialchars($msh['kode_msh'] ?? '-'); ?></p>
            <p><strong>Status:</strong> <span class="badge <?php echo strtolower($msh['status']); ?>"><?php echo ucfirst($msh['status']); ?></span></p>
        </div>
    </div>
    
    <div class="detail-section">
        <h3>📋 Informasi Pribadi</h3>
        <table class="detail-table">
            <tr>
                <td><strong>Tempat, Tanggal Lahir</strong></td>
                <td><?php echo htmlspecialchars($msh['tempat_lahir'] ?? '-'); ?>, <?php echo $msh['tanggal_lahir'] ? date('d/m/Y', strtotime($msh['tanggal_lahir'])) : '-'; ?></td>
            </tr>
            <tr>
                <td><strong>Jenis Kelamin</strong></td>
                <td><?php echo ucfirst($msh['jenis_kelamin'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td><?php echo htmlspecialchars($msh['alamat'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td><strong>No. Telepon</strong></td>
                <td><?php echo htmlspecialchars($msh['no_telp'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td><?php echo htmlspecialchars($msh['email'] ?? '-'); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="detail-section">
        <h3>🥋 Informasi Karate</h3>
        <table class="detail-table">
            <tr>
                <td><strong>Tingkat Dan</strong></td>
                <td><?php echo htmlspecialchars($msh['tingkat_dan'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td><strong>Dojo/Cabang</strong></td>
                <td><?php echo htmlspecialchars($msh['dojo_cabang'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Ujian</strong></td>
                <td><?php echo $msh['tanggal_ujian'] ? date('d/m/Y', strtotime($msh['tanggal_ujian'])) : '-'; ?></td>
            </tr>
        </table>
    </div>
    
    <?php if(count($prestasi_list) > 0): ?>
    <div class="detail-section">
        <h3>🏆 Prestasi</h3>
        <ul class="prestasi-list">
            <?php foreach($prestasi_list as $prestasi): ?>
                <li><?php echo htmlspecialchars($prestasi['nama_prestasi']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if(count($sertifikasi_list) > 0): ?>
    <div class="detail-section">
        <h3>📜 Sertifikasi</h3>
        <table class="detail-table">
            <tr>
                <th>Nama Sertifikasi</th>
                <th>Nomor</th>
                <th>Penerbit</th>
                <th>Tanggal Terbit</th>
                <th>Status</th>
            </tr>
            <?php foreach($sertifikasi_list as $sert): ?>
            <tr>
                <td><?php echo htmlspecialchars($sert['nama_sertifikasi']); ?></td>
                <td><?php echo htmlspecialchars($sert['nomor_sertifikat'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($sert['penerbit'] ?? '-'); ?></td>
                <td><?php echo $sert['tanggal_terbit'] ? date('d/m/Y', strtotime($sert['tanggal_terbit'])) : '-'; ?></td>
                <td><span class="badge <?php echo strtolower($sert['status']); ?>"><?php echo ucfirst($sert['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.detail-container { padding: 20px; }
.detail-header { display: flex; gap: 20px; margin-bottom: 30px; align-items: center; }
.detail-photo { width: 120px; height: 120px; border-radius: 12px; object-fit: cover; }
.detail-photo-placeholder { width: 120px; height: 120px; border-radius: 12px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 48px; }
.detail-section { margin-bottom: 25px; }
.detail-section h3 { margin-bottom: 15px; color: #00174b; }
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table td, .detail-table th { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
.detail-table tr:last-child td { border-bottom: none; }
.detail-table td:first-child { font-weight: 600; color: #6b7280; width: 200px; }
.prestasi-list { list-style: none; padding: 0; }
.prestasi-list li { padding: 10px; background: #f3f4f6; border-radius: 8px; margin-bottom: 8px; }
.badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.badge.aktif { background: #dcfce7; color: #166534; }
.badge.non-aktif { background: #fee2e2; color: #991b1b; }
.badge.valid { background: #dcfce7; color: #166534; }
.badge.kadaluarsa { background: #fee2e2; color: #991b1b; }
</style>

<?php
} catch(PDOException $e) {
    echo '<p style="text-align:center;color:red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
