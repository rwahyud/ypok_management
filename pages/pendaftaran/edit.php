<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? '';

if(!$id || !in_array($type, ['msh', 'kohai'])) {
    header('Location: index.php?error=1&msg=Parameter tidak valid');
    exit();
}

// Ambil data
if($type == 'msh') {
    $stmt = $pdo->prepare("SELECT * FROM pendaftaran_msh WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM pendaftaran_kohai WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
}

if(!$data) {
    header('Location: index.php?tab='.$type.'&error=1&msg=Data tidak ditemukan');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pendaftaran <?php echo strtoupper($type); ?> - YPOK Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pendaftaran.css">
</head>
<body>
    <?php include '../../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">✏️ Edit Pendaftaran <?php echo strtoupper($type); ?></h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container" style="max-width: 800px; margin: 20px auto;">
            <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <div style="margin-bottom: 30px;">
                    <h2 style="color: #2d3748; margin-bottom: 10px;">📝 Edit Data Pendaftaran</h2>
                    <p style="color: #718096;">Update informasi pendaftaran <?php echo strtoupper($type); ?></p>
                    <a href="pendaftaran.php?tab=<?php echo $type; ?>" style="display: inline-block; margin-top: 10px; color: #4a5fc1; text-decoration: none;">
                        ← Kembali ke Daftar
                    </a>
                </div>
                
                <?php if($type == 'msh'): ?>
                <!-- MSH Edit Form -->
                <form action="actions/update_pendaftaran_msh.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                    
                    <div class="form-group">
                        <label>No MSH <span class="required">*</span></label>
                        <input type="text" name="kode_msh" value="<?php echo htmlspecialchars($data['kode_msh']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto MSH</label>
                        <?php if(!empty($data['foto'])): ?>
                        <div class="image-preview active" id="current-photo">
                            <img src="<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto MSH">
                            <p style="margin-top: 10px; color: #718096; font-size: 12px;">Foto saat ini</p>
                        </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input type="file" name="foto" id="foto" accept="image/*" onchange="previewImage(this, 'preview-msh')">
                            <label for="foto" class="file-upload-label">
                                <span class="file-icon">📷</span>
                                <span>Ubah Foto</span>
                            </label>
                        </div>
                        <div id="preview-msh" class="image-preview"></div>
                        <small class="form-hint">atau masukkan URL baru</small>
                        <input type="text" name="foto_url" placeholder="https://..." class="mt-5">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($data['foto'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tempat Lahir <span class="required">*</span></label>
                            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($data['tempat_lahir']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir <span class="required">*</span></label>
                            <input type="date" name="tanggal_lahir" value="<?php echo $data['tanggal_lahir']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <select name="jenis_kelamin" required>
                            <option value="L" <?php echo $data['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo $data['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tingkat Dan <span class="required">*</span></label>
                            <select name="tingkat_dan" required>
                                <?php for($i=1; $i<=7; $i++): ?>
                                <option value="Dan <?php echo $i; ?>" <?php echo $data['tingkat_dan'] == "Dan $i" ? 'selected' : ''; ?>>Dan <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dojo/Cabang <span class="required">*</span></label>
                            <input type="text" name="dojo_cabang" value="<?php echo htmlspecialchars($data['dojo_cabang']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. Telepon <span class="required">*</span></label>
                            <input type="tel" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat <span class="required">*</span></label>
                        <textarea name="alamat" rows="3" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn-submit">
                            💾 Update Data MSH
                        </button>
                        <a href="pendaftaran.php?tab=msh" class="btn-submit" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            ❌ Batal
                        </a>
                    </div>
                </form>
                
                <?php else: ?>
                <!-- Kohai Edit Form -->
                <form action="actions/update_pendaftaran_kohai.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                    
                    <div class="form-group">
                        <label>No Kohai <span class="required">*</span></label>
                        <input type="text" name="kode_kohai" value="<?php echo htmlspecialchars($data['kode_kohai']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto Kohai</label>
                        <?php if(!empty($data['foto'])): ?>
                        <div class="image-preview active" id="current-photo">
                            <img src="<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto Kohai">
                            <p style="margin-top: 10px; color: #718096; font-size: 12px;">Foto saat ini</p>
                        </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input type="file" name="foto" id="foto_kohai" accept="image/*" onchange="previewImage(this, 'preview-kohai')">
                            <label for="foto_kohai" class="file-upload-label">
                                <span class="file-icon">📷</span>
                                <span>Ubah Foto</span>
                            </label>
                        </div>
                        <div id="preview-kohai" class="image-preview"></div>
                        <small class="form-hint">atau masukkan URL baru</small>
                        <input type="text" name="foto_url" placeholder="https://..." class="mt-5">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($data['foto'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tempat Lahir <span class="required">*</span></label>
                            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($data['tempat_lahir']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir <span class="required">*</span></label>
                            <input type="date" name="tanggal_lahir" value="<?php echo $data['tanggal_lahir']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <select name="jenis_kelamin" required>
                            <option value="L" <?php echo $data['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo $data['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. Telepon <span class="required">*</span></label>
                            <input type="tel" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat <span class="required">*</span></label>
                        <textarea name="alamat" rows="3" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                    
                    <div class="form-divider">
                        <span>Data Wali</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Wali <span class="required">*</span></label>
                        <input type="text" name="nama_wali" value="<?php echo htmlspecialchars($data['nama_wali']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>No. Telepon Wali <span class="required">*</span></label>
                        <input type="tel" name="no_telp_wali" value="<?php echo htmlspecialchars($data['no_telp_wali']); ?>" required>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn-submit">
                            💾 Update Data Kohai
                        </button>
                        <a href="pendaftaran.php?tab=kohai" class="btn-submit" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            ❌ Batal
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/pendaftaran.js"></script>
</body>
</html>

