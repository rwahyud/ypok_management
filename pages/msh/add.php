<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_msh = $_POST['nomor_msh'];
    $nama = $_POST['nama'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tingkat_dan = $_POST['tingkat_dan'];
    $dojo_cabang = $_POST['dojo_cabang'];
    $no_telp = $_POST['no_telp'];
    $nomor_ijazah = $_POST['nomor_ijazah'];
    $status = $_POST['status'];
    $alamat = $_POST['alamat'];
    $prestasi = $_POST['prestasi'];
    
    // Handle foto upload
    $foto = '';
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/msh/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $foto = $target_dir . time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
    }
    
    $stmt = $pdo->prepare("INSERT INTO majelis_sabuk_hitam (nama, nomor_sertifikat, tingkat_sabuk, tanggal_lulus, foto, alamat, no_telp, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if($stmt->execute([$nama, $nomor_msh, $tingkat_dan, $tanggal_lahir, $foto, $alamat, $no_telp, $dojo_cabang, $status])) {
        header('Location: msh.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data MSH - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-content {
            background: #fff;
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.4s ease;
        }
        
        .modal-header {
            padding: 20px 25px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            color: #fff;
            font-size: 20px;
            margin: 0;
            font-weight: 600;
        }
        
        .btn-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #fff;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .btn-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 25px;
            max-height: calc(85vh - 130px);
            overflow-y: auto;
        }
        
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row.full {
            grid-template-columns: 1fr;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #334155;
            font-size: 13px;
        }
        
        .form-group label span {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .foto-upload {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s;
        }
        
        .foto-upload:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .foto-preview {
            width: 100px;
            height: 100px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .foto-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .foto-preview .icon {
            font-size: 40px;
            color: #94a3b8;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .url-helper {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }
        
        .url-helper input {
            margin-top: 6px;
            font-size: 12px;
            padding: 8px 10px;
        }
        
        .prestasi-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
        }
        
        .btn-add-prestasi {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .btn-add-prestasi:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .sertifikasi-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1.5px solid #e2e8f0;
            position: relative;
            transition: all 0.3s;
        }
        
        .sertifikasi-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
        }
        
        .sertifikasi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .sertifikasi-title {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-remove {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .btn-remove:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        .btn-add-sertifikasi {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            margin-top: 12px;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-add-sertifikasi:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .modal-footer {
            padding: 20px 25px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }
        
        .btn-cancel {
            flex: 1;
            background: #fff;
            color: #64748b;
            padding: 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        
        .btn-submit {
            flex: 2;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
        }
        
        .info-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Tambah Data MSH</h2>
                <button class="btn-close" onclick="window.location.href='msh.php'">×</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formMSH">
                <div class="modal-body">
                    <!-- Data Utama -->
                    <div class="form-section">
                        <div class="section-title">
                            📝 Data Utama
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. MSH <span>*</span></label>
                                <input type="text" name="nomor_msh" placeholder="MSH-001" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Foto MSH</label>
                                <div class="foto-upload">
                                    <div class="foto-preview" id="fotoPreview">
                                        <span class="icon">👤</span>
                                    </div>
                                    <input type="file" name="foto" id="fotoInput" accept="image/*" style="display: none;" onchange="previewFoto(event)">
                                    <button type="button" class="btn-upload" onclick="document.getElementById('fotoInput').click()">
                                        📷 Pilih Foto
                                    </button>
                                    <div class="url-helper">
                                        atau URL: <input type="text" name="foto_url" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span>*</span></label>
                                <input type="text" name="tempat_lahir" placeholder="Jakarta" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span>*</span></label>
                                <input type="date" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin <span>*</span></label>
                                <select name="jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Dan <span>*</span></label>
                                <select name="tingkat_dan" required>
                                    <option value="">Pilih...</option>
                                    <option value="Dan 1">Dan 1</option>
                                    <option value="Dan 2">Dan 2</option>
                                    <option value="Dan 3">Dan 3</option>
                                    <option value="Dan 4">Dan 4</option>
                                    <option value="Dan 5">Dan 5</option>
                                    <option value="Dan 6">Dan 6</option>
                                    <option value="Dan 7">Dan 7</option>
                                    <option value="Dan 8">Dan 8</option>
                                    <option value="Dan 9">Dan 9</option>
                                    <option value="Dan 10">Dan 10</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Dojo/Cabang <span>*</span></label>
                                <input type="text" name="dojo_cabang" placeholder="Jakarta Pusat" required>
                            </div>
                            <div class="form-group">
                                <label>No. Telepon <span>*</span></label>
                                <input type="tel" name="no_telp" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nomor Ijazah</label>
                                <input type="text" name="nomor_ijazah" placeholder="IJ-2025-001">
                            </div>
                            <div class="form-group">
                                <label>Status <span>*</span></label>
                                <select name="status" required>
                                    <option value="">Pilih...</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="non-aktif">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Alamat Lengkap <span>*</span></label>
                                <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap..." required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prestasi -->
                    <div class="form-section">
                        <div class="section-title">
                            🏆 Prestasi
                        </div>
                        <div class="prestasi-box">
                            <div class="form-group">
                                <input type="text" name="prestasi" placeholder="Contoh: Juara 1 Kata Regional 2023">
                            </div>
                            <button type="button" class="btn-add-prestasi">
                                ➕ Tambah Prestasi
                            </button>
                        </div>
                    </div>
                    
                    <!-- Sertifikasi Detail -->
                    <div class="form-section">
                        <div class="section-title">
                            📜 Sertifikasi Detail
                        </div>
                        <p class="info-text">Tambahkan sertifikasi resmi (opsional)</p>
                        
                        <div id="sertifikasiContainer">
                            <div class="sertifikasi-card">
                                <div class="sertifikasi-header">
                                    <div class="sertifikasi-title">📄 Sertifikasi #1</div>
                                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕ Hapus</button>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nama Sertifikasi</label>
                                        <input type="text" name="sertifikasi_nama[]" placeholder="Sabuk Hitam Dan 1">
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor Sertifikat</label>
                                        <input type="text" name="sertifikasi_nomor[]" placeholder="MSH-2023-XXX">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Penerbit</label>
                                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI">
                                    </div>
                                    <div class="form-group">
                                        <label>Level</label>
                                        <input type="text" name="sertifikasi_level[]" placeholder="Dan 1">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Tanggal Terbit</label>
                                        <input type="date" name="sertifikasi_tanggal[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Kadaluarsa</label>
                                        <input type="date" name="sertifikasi_kadaluarsa[]">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn-add-sertifikasi" onclick="tambahSertifikasi()">
                            ➕ Tambah Sertifikasi
                        </button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="window.location.href='msh.php'">
                        Batal
                    </button>
                    <button type="submit" class="btn-submit">
                        💾 Simpan Data MSH
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let sertifikasiCount = 1;
        
        function previewFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').innerHTML = '<img src="' + e.target.result + '">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        function tambahSertifikasi() {
            sertifikasiCount++;
            const container = document.getElementById('sertifikasiContainer');
            const newCard = document.createElement('div');
            newCard.className = 'sertifikasi-card';
            newCard.innerHTML = `
                <div class="sertifikasi-header">
                    <div class="sertifikasi-title">📄 Sertifikasi #${sertifikasiCount}</div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕ Hapus</button>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Sertifikasi</label>
                        <input type="text" name="sertifikasi_nama[]" placeholder="Sabuk Hitam Dan 1">
                    </div>
                    <div class="form-group">
                        <label>Nomor Sertifikat</label>
                        <input type="text" name="sertifikasi_nomor[]" placeholder="MSH-2023-XXX">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI">
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="text" name="sertifikasi_level[]" placeholder="Dan 1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Terbit</label>
                        <input type="date" name="sertifikasi_tanggal[]">
                    </div>
                    <div class="form-group">
                        <label>Kadaluarsa</label>
                        <input type="date" name="sertifikasi_kadaluarsa[]">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        }
    </script>
</body>
</html>
