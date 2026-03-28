<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo '<p style="text-align:center;color:red;">Access denied</p>';
    exit();
}

$id = $_GET['id'];

// Get kohai data
$stmt = $pdo->prepare("SELECT * FROM kohai WHERE id = ?");
$stmt->execute([$id]);
$kohai = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$kohai) {
    echo '<p style="text-align:center;color:red;">Data not found</p>';
    exit();
}

// Get prestasi
$stmt = $pdo->prepare("SELECT * FROM prestasi_kohai WHERE kohai_id = ?");
$stmt->execute([$id]);
$prestasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sertifikasi
$stmt = $pdo->prepare("SELECT * FROM sertifikasi_kohai WHERE kohai_id = ?");
$stmt->execute([$id]);
$sertifikasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Profile Banner -->
<div class="profile-banner" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding: 30px; display: flex; align-items: center; gap: 25px; position: relative; overflow: hidden;">
    <div style="content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
    
    <div class="profile-photo" style="width: 110px; height: 110px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 5px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 24px rgba(0,0,0,0.2); flex-shrink: 0; position: relative; z-index: 1;">
        <?php if($kohai['foto']): ?>
            <img src="<?php echo htmlspecialchars($kohai['foto']); ?>" alt="Foto Kohai" style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
            <span style="font-size: 50px; color: #3b82f6;">👤</span>
        <?php endif; ?>
    </div>
    
    <div class="profile-info" style="flex: 1; position: relative; z-index: 1;">
        <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px 0; text-transform: uppercase; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <?php echo htmlspecialchars($kohai['nama']); ?>
        </h1>
        <div style="font-size: 15px; color: rgba(255, 255, 255, 0.9); font-weight: 500; background: rgba(255, 255, 255, 0.2); display: inline-block; padding: 6px 16px; border-radius: 20px; backdrop-filter: blur(10px);">
            <?php echo htmlspecialchars($kohai['kode_kohai']); ?>
        </div>
    </div>
</div>

<!-- Info Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; background: #fff;">
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Kode Kohai
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['kode_kohai']); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Nama Lengkap
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['nama']); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Tempat, Tanggal Lahir
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['tempat_lahir']) . ', ' . date('d F Y', strtotime($kohai['tanggal_lahir'])); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Jenis Kelamin
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo $kohai['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Tingkat Kyu
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <span style="color: #1e293b; border-radius: 6px; font-size: 13px; font-weight: 600;">
                <?php echo htmlspecialchars($kohai['tingkat_kyu']); ?>
            </span>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Sabuk
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php
                $sabuk_colors = [
                    'Putih' => ' color: #1e293b;',
                    'Kuning' => 'color: #1e293b',
                    'Orange' => ' color: #1e293b',
                    'Hijau' => ' color: #1e293b;',
                    'Biru' => ' color: #1e293b;',
                    'Coklat' => ' color: #1e293b;'
                ];
                $sabuk_style = $sabuk_colors[$kohai['sabuk']] ?? 'background: #64748b; color: #fff;';
            ?>
            <span style="<?php echo $sabuk_style; ?> border-radius: 6px; font-size: 13px; font-weight: 600;">
                <?php echo htmlspecialchars($kohai['sabuk']); ?>
            </span>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Dojo/Cabang
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['dojo_cabang']); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            No. Telepon
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['no_telp']); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
           Email
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['email']); ?>
        </div>
    </div>

    <?php if(!empty($kohai['nama_wali'])): ?>
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Nama Wali/Orang Tua
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['nama_wali']); ?>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            No. Telepon Wali
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['no_telp_wali'] ?? '-'); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; <?php echo empty($kohai['nama_wali']) ? 'border-right: 1px solid #f1f5f9;' : ''; ?> transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Status
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php
                $status_colors = [
                    'Aktif' => ' color: #1e293b;',
                    'Non-Aktif' => ' color: #1e293b;',
                    'Meninggal' => ' color: #1e293b;'
                ];
                $status_style = $status_colors[$kohai['status']] ?? 'background: #64748b; color: #fff;';
            ?>
            <span style="<?php echo $status_style; ?>  border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block;">
                <?php echo ucfirst($kohai['status']); ?>
            </span>
        </div>
    </div>
    
    <div style="padding: 18px 25px; border-bottom: 1px solid #f1f5f9; grid-column: 1 / -1; transition: all 0.3s;">
        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 3px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
            Alamat Lengkap
        </div>
        <div style="font-size: 14px; color: #1e293b; font-weight: 600; line-height: 1.5;">
            <?php echo htmlspecialchars($kohai['alamat']); ?>
        </div>
    </div>
</div>

<!-- Prestasi Section -->
<div style="padding: 25px 30px; background: #fff; border-top: 4px solid #fbbf24;">
    <div style="font-size: 18px; font-weight: 700; color: #78350f; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
        🏆 Prestasi & Penghargaan
    </div>
    
    <?php if(count($prestasi) > 0): ?>
    <div style="display: grid; gap: 12px;">
        <?php foreach($prestasi as $p): ?>
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%); padding: 16px 18px; border-radius: 10px; display: flex; align-items: center; gap: 14px; border-left: 5px solid #f59e0b; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1); transition: all 0.3s;">
            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);">
                🏆
            </div>
            <div style="font-size: 14px; color: #78350f; font-weight: 600;">
                <?php echo htmlspecialchars($p['nama_prestasi']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 10px;">🏆</div>
        <div style="font-size: 14px;">Belum ada prestasi tercatat</div>
    </div>
    <?php endif; ?>
</div>

<!-- Sertifikasi Section -->
<div style="padding: 25px 30px; background: #fff; border-top: 4px solid #3b82f6;">
    <div style="font-size: 18px; font-weight: 700; color: #1e40af; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
        📜 Rincian Sertifikasi
    </div>
    
    <?php if(count($sertifikasi) > 0): ?>
        <?php foreach($sertifikasi as $sert): ?>
        <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; padding: 20px; margin-bottom: 15px; border: 2px solid #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); transition: all 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 18px; padding-bottom: 15px; border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
                <div style="font-size: 16px; font-weight: 700; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                    📄 <?php echo htmlspecialchars($sert['nama_sertifikasi']); ?>
                </div>
                <?php
                    $cert_badge_style = '';
                    $cert_badge_text = '';
                    if($sert['status'] == 'permanent') {
                        $cert_badge_style = 'background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;';
                        $cert_badge_text = '✓ PERMANENT';
                    } elseif($sert['status'] == 'valid') {
                        $cert_badge_style = 'background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff;';
                        $cert_badge_text = '✓ VALID';
                    } else {
                        $cert_badge_style = 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff;';
                        $cert_badge_text = '✕ EXPIRED';
                    }
                ?>
                <span style="<?php echo $cert_badge_style; ?> padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);">
                    <?php echo $cert_badge_text; ?>
                </span>
            </div>
            
            <?php if(!empty($sert['nomor_sertifikat'])): ?>
            <div style="margin-bottom: 15px; padding: 10px 14px; background: rgba(255, 255, 255, 0.7); border-radius: 8px; font-size: 13px; color: #475569; font-weight: 600;">
                <strong>No. Sertifikat:</strong> <?php echo htmlspecialchars($sert['nomor_sertifikat']); ?>
            </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <?php if(!empty($sert['penerbit'])): ?>
                <div style="background: rgba(255, 255, 255, 0.5); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        Penerbit
                    </div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 700;">
                        <?php echo htmlspecialchars($sert['penerbit']); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($sert['tanggal_terbit'])): ?>
                <div style="background: rgba(255, 255, 255, 0.5); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        Tanggal Terbit
                    </div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 700;">
                        <?php echo date('d F Y', strtotime($sert['tanggal_terbit'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($sert['status'] != 'permanent' && !empty($sert['tanggal_kadaluarsa'])): ?>
                <div style="background: rgba(255, 255, 255, 0.5); padding: 12px; border-radius: 8px; grid-column: 1 / -1;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        Berlaku Sampai
                    </div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 700;">
                        <?php echo date('d F Y', strtotime($sert['tanggal_kadaluarsa'])); ?>
                    </div>
                </div>
                <?php elseif($sert['status'] == 'permanent'): ?>
                <div style="background: rgba(16, 185, 129, 0.1); padding: 12px; border-radius: 8px; grid-column: 1 / -1; border: 2px solid #10b981;">
                    <div style="font-size: 13px; color: #059669; font-weight: 700; text-align: center;">
                        ✓ Sertifikat Berlaku Seumur Hidup
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div style="text-align: center; padding: 40px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 10px;">📜</div>
        <div style="font-size: 14px;">Belum ada sertifikasi tercatat</div>
    </div>
    <?php endif; ?>
</div>

<!-- Footer Buttons -->
<div style="padding: 20px 25px; background: #f8fafc; border-top: 2px solid #e2e8f0; display: flex; gap: 12px; justify-content: flex-end;">
    <button style="background: #fff; color: #64748b; padding: 12px 24px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s;" onclick="closeDetail()">
        Tutup
    </button>
    <button style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" onclick="closeDetail(); editData(<?php echo $kohai['id']; ?>);">
        ✏️ Edit Data
    </button>
</div>
