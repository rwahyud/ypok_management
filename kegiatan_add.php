<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get existing categories from kegiatan table
$kategori_list = $pdo->query("SELECT DISTINCT jenis_kegiatan FROM kegiatan WHERE jenis_kegiatan IS NOT NULL ORDER BY jenis_kegiatan")->fetchAll();
$lokasi_list = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita / Kegiatan - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-header h2 {
            color: #1e40af;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
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
        
        .add-item-btn:hover {
            background: #e5e7eb;
        }
        
        .peserta-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .peserta-item input {
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
        
        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #e5e7eb;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-submit {
            flex: 2;
            padding: 12px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .kategori-input-group {
            position: relative;
        }
        
        .kategori-input-group input {
            width: 100%;
        }
        
        .kategori-helper {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">📰 Tambah Berita / Kegiatan</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>📰 Tambah Berita / Kegiatan</h2>
                    <button class="close-modal" onclick="window.location.href='laporan_kegiatan.php'">×</button>
                </div>
                
                <form action="kegiatan_save.php" method="POST" id="formKegiatan" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Kegiatan <span>*</span></label>
                            <input type="text" name="nama_kegiatan" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kategori <span>*</span></label>
                            <div class="kategori-input-group">
                                <input type="text" name="kategori" id="kategoriInput" list="kategori-list" placeholder="Pilih atau ketik kategori baru..." required>
                                <datalist id="kategori-list">
                                    <?php foreach($kategori_list as $kategori): ?>
                                        <option value="<?php echo htmlspecialchars($kategori['jenis_kegiatan']); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="kategori-helper">💡 Pilih dari daftar atau ketik kategori baru</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal <span>*</span></label>
                            <input type="date" name="tanggal_kegiatan" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" list="lokasi-list" placeholder="Pilih atau ketik lokasi baru..." required>
                            <datalist id="lokasi-list">
                                <?php foreach($lokasi_list as $lokasi): ?>
                                    <option value="<?php echo htmlspecialchars($lokasi['nama_lokasi']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>PIC / Penanggung Jawab <span>*</span></label>
                            <input type="text" name="pic" placeholder="Nama PIC" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Jumlah Peserta <span>*</span></label>
                            <input type="number" name="jumlah_peserta" min="0" value="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="status" required>
                            <option value="Dijadwalkan">Dijadwalkan</option>
                            <option value="Berlangsung">Berlangsung</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Peserta MSH (Opsional)</label>
                        <div id="pesertaMSHContainer">
                            <div class="peserta-item">
                                <input type="text" name="peserta_msh[]" placeholder="Nama MSH atau No. MSH">
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="tambahPesertaMSH()">+ Tambah Peserta MSH</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Peserta Pelatih (Opsional)</label>
                        <div id="pesertaPelatihContainer">
                            <div class="peserta-item">
                                <input type="text" name="peserta_pelatih[]" placeholder="Nama Pelatih atau Kode Pelatih">
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="tambahPesertaPelatih()">+ Tambah Peserta Pelatih</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi Kegiatan</label>
                        <textarea name="deskripsi" placeholder="Tuliskan deskripsi kegiatan..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto Kegiatan (akan tampil sebagai berita di halaman utama)</label>
                        <input type="file" name="foto" id="fotoInput" accept="image/jpeg,image/jpg,image/png" onchange="previewFoto(this)">
                        <div class="kategori-helper">📷 Format: JPG, JPEG, PNG. Maksimal 2MB</div>
                        <div id="fotoPreview" style="margin-top: 10px; display: none;">
                            <img id="previewImage" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    
                    <div class="form-group" style="background: #f0f9ff; padding: 20px; border-radius: 6px; border-left: 4px solid #1e40af;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="tampil_di_berita" id="tampilDiBerita" value="1" style="width: auto; margin-right: 10px; cursor: pointer;">
                            <span style="font-weight: 600; color: #1e40af;">📰 Tampilkan sebagai Berita di Halaman Utama</span>
                        </label>
                        <div class="kategori-helper" style="margin-left: 30px; margin-top: 8px;">
                            Centang jika kegiatan ini ingin ditampilkan di section Berita pada halaman utama (Guest Dashboard). Pastikan foto sudah diupload agar tampilan berita optimal.
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='laporan_kegiatan.php'">Batal</button>
                        <button type="submit" class="btn-submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function tambahPesertaMSH() {
            const container = document.getElementById('pesertaMSHContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <input type="text" name="peserta_msh[]" placeholder="Nama MSH atau No. MSH">
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function tambahPesertaPelatih() {
            const container = document.getElementById('pesertaPelatihContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <input type="text" name="peserta_pelatih[]" placeholder="Nama Pelatih atau Kode Pelatih">
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function previewFoto(input) {
            const preview = document.getElementById('fotoPreview');
            const previewImage = document.getElementById('previewImage');
            
            if (input.files && input.files[0]) {
                // Validate file size (2MB)
                if (input.files[0].size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(input.files[0].type)) {
                    alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
