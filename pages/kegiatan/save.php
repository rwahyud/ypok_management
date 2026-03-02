<?php
// Tambahkan di baris paling atas untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $nama_kegiatan = trim($_POST['nama_kegiatan']);
        $kategori = trim($_POST['kategori']);
        $tanggal_kegiatan = $_POST['tanggal_kegiatan'];
        $lokasi = trim($_POST['lokasi']);
        $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
        $pic = trim($_POST['pic']);
        $status_form = $_POST['status'];
        $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
        $tampil_di_berita = isset($_POST['tampil_di_berita']) && $_POST['tampil_di_berita'] == '1' ? true : false;
        
        // Map status dari form ke database ENUM
        $status_map = [
            'Selesai' => 'selesai',
            'Berlangsung' => 'berlangsung',
            'Dijadwalkan' => 'dijadwalkan'
        ];
        $status = $status_map[$status_form] ?? 'dijadwalkan';
        
        // Handle foto upload
        $foto_filename = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/kegiatan/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Validate file
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            $file_type = $_FILES['foto']['type'];
            $file_size = $_FILES['foto']['size'];
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.");
            }
            
            if ($file_size > $max_size) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
            }
            
            // Generate unique filename
            $foto_filename = 'kegiatan_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $foto_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                throw new Exception("Gagal mengupload foto.");
            }
        }
        
        // Check if lokasi exists, if not create it
        $stmt = $pdo->prepare("SELECT id FROM lokasi WHERE nama_lokasi = ?");
        $stmt->execute([$lokasi]);
        $lokasi_data = $stmt->fetch();
        
        if (!$lokasi_data) {
            $stmt = $pdo->prepare("INSERT INTO lokasi (nama_lokasi) VALUES (?)");
            $stmt->execute([$lokasi]);
            $lokasi_id = $pdo->lastInsertId();
        } else {
            $lokasi_id = $lokasi_data['id'];
        }
        
        // Prepare peserta text (hanya daftar peserta, tanpa PIC)
        $peserta_text = "";
        $jumlah_peserta_total = 0;
        
        // Add MSH participants
        if (!empty($_POST['peserta_msh'])) {
            $msh_count = 0;
            $msh_names = [];
            foreach ($_POST['peserta_msh'] as $msh_id) {
                if (!empty($msh_id)) {
                    $stmt = $pdo->prepare("SELECT nama, kode_msh FROM majelis_sabuk_hitam WHERE id = ?");
                    $stmt->execute([$msh_id]);
                    $msh = $stmt->fetch();
                    if ($msh) {
                        $msh_names[] = "{$msh['nama']} ({$msh['kode_msh']})";
                        $msh_count++;
                    }
                }
            }
            if ($msh_count > 0) {
                $peserta_text .= "MSH [$msh_count orang]:\n";
                foreach ($msh_names as $name) {
                    $peserta_text .= "- $name\n";
                }
                $jumlah_peserta_total += $msh_count;
            }
        }
        
        // Add Pelatih participants
        if (!empty($_POST['peserta_pelatih'])) {
            $pelatih_count = 0;
            $pelatih_names = [];
            foreach ($_POST['peserta_pelatih'] as $kohai_id) {
                if (!empty($kohai_id)) {
                    $stmt = $pdo->prepare("SELECT nama, kode_kohai FROM kohai WHERE id = ?");
                    $stmt->execute([$kohai_id]);
                    $kohai = $stmt->fetch();
                    if ($kohai) {
                        $pelatih_names[] = "{$kohai['nama']} ({$kohai['kode_kohai']})";
                        $pelatih_count++;
                    }
                }
            }
            if ($pelatih_count > 0) {
                if (!empty($peserta_text)) $peserta_text .= "\n";
                $peserta_text .= "Pelatih [$pelatih_count orang]:\n";
                foreach ($pelatih_names as $name) {
                    $peserta_text .= "- $name\n";
                }
                $jumlah_peserta_total += $pelatih_count;
            }
        }
        
        // If no specific participants, use general count
        if (empty($peserta_text) && !empty($_POST['jumlah_peserta'])) {
            $jumlah_peserta_total = (int)$_POST['jumlah_peserta'];
            $peserta_text = "Total peserta: $jumlah_peserta_total orang";
        }
        
        // Insert kegiatan dengan kolom pic, alamat, foto, dan tampil_di_berita
        $sql = "INSERT INTO kegiatan (
            nama_kegiatan, 
            jenis_kegiatan, 
            tanggal_kegiatan, 
            lokasi_id,
            alamat,
            pic,
            peserta,
            keterangan,
            status,
            foto,
            tampil_di_berita
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $data = [
            $nama_kegiatan,
            $kategori,
            $tanggal_kegiatan,
            $lokasi_id,
            $alamat,
            $pic,
            $peserta_text,
            $deskripsi,
            $status,
            $foto_filename,
            $tampil_di_berita
        ];
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($data);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . $errorInfo[2]);
            throw new Exception("Gagal menyimpan data kegiatan: " . $errorInfo[2]);
        }
        
        $kegiatan_id = $pdo->lastInsertId();
        
        if (!$kegiatan_id) {
            throw new Exception("Gagal mendapatkan ID kegiatan");
        }
        
        header('Location: laporan_kegiatan.php?success=1');
        exit();
        
    } catch(PDOException $e) {
        error_log("Error saving kegiatan: " . $e->getMessage());
        header('Location: laporan_kegiatan.php?error=' . urlencode($e->getMessage()));
        exit();
    } catch(Exception $e) {
        error_log("Error: " . $e->getMessage());
        header('Location: laporan_kegiatan.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: laporan_kegiatan.php');
    exit();
}
?>

