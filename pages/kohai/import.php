<?php
/**
 * IMPORT KOHAI DARI CSV
 * =====================
 * Tool untuk import data Kohai dari file CSV Google Sheets
 * 
 * File CSV: googlesheet/NO.REGISTRASI IJAZAH KYU YPOK - NO.REG IJAZAH.csv
 * 
 * Mapping kolom:
 * - NO. REGISTRASI IJAZAH → kode_kohai (ambil yang pertama) + nomor_ijazah (semua)
 * - NAMA → nama
 * - TEMPAT, TGL LAHIR → tempat_lahir + tanggal_lahir
 * - WARNA SABUK → sabuk + tingkat_kyu
 * - TANGGAL UJIAN, KYU, DAN WARNA SABUK → tanggal_ujian
 * - CABANG/ASAL SEKOLAH → dojo_cabang
 * - ASAL PROVINSI/KAB/KOTA → alamat
 * - KETERANGAN → keterangan
 * - STATUS ANGGOTA → status
 * - FOTO → foto
 */

require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tingkatkan execution time dan memory limit untuk import besar
set_time_limit(600); // 10 menit
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);

$csvFile = __DIR__ . '/googlesheet/NO.REGISTRASI IJAZAH KYU YPOK - NO.REG IJAZAH.csv';

// Function untuk parse tanggal Indonesia
function parseTanggalIndo($str) {
    if(empty($str)) return null;
    
    $str = trim($str);
    $bulan_map = [
        'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
        'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
        'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
        'jun' => '06', 'jul' => '07', 'agu' => '08', 'sep' => '09',
        'okt' => '10', 'nov' => '11', 'des' => '12'
    ];
    
    foreach($bulan_map as $indo => $num) {
        $str = str_ireplace($indo, $num, $str);
    }
    
    // Try different formats
    $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd m Y', 'd-m-y', 'j-n-Y', 'j/n/Y'];
    
    foreach($formats as $format) {
        try {
            $date = DateTime::createFromFormat($format, $str);
            if($date) {
                return $date->format('Y-m-d');
            }
        } catch(Exception $e) {
            continue;
        }
    }
    
    return null;
}

// Function untuk parse tempat & tanggal lahir
function parseTempatlahir($str) {
    if(empty($str)) return ['', null];
    
    $str = trim($str);
    
    // Split by comma
    $parts = explode(',', $str);
    
    if(count($parts) >= 2) {
        $tempat = trim($parts[0]);
        $tanggal_str = trim(implode(',', array_slice($parts, 1)));
        $tanggal = parseTanggalIndo($tanggal_str);
        return [$tempat, $tanggal];
    }
    
    // Jika tidak ada koma, coba detect tanggal di string
    if(preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $str, $matches)) {
        $tanggal = parseTanggalIndo($matches[1]);
        $tempat = trim(str_replace($matches[1], '', $str));
        return [$tempat, $tanggal];
    }
    
    return [$str, null];
}

// Function untuk extract kode kohai pertama
function extractKodeKohai($str) {
    if(empty($str)) return '';
    
    $str = trim($str);
    
    // Split by spaces, ambil yang pertama
    $parts = preg_split('/\s+/', $str);
    return trim($parts[0]);
}

// Function untuk parse sabuk & kyu
function parseSabukKyu($str) {
    if(empty($str)) return ['', ''];
    
    $str = trim($str);
    
    // Extract number (kyu level)
    preg_match('/(\d+)/', $str, $matches);
    $kyu = isset($matches[1]) ? $matches[1] : '';
    
    // Extract color name
    $warna = '';
    $warna_map = [
        'putih' => 'Putih',
        'kuning' => 'Kuning', 
        'orange' => 'Orange',
        'hijau' => 'Hijau',
        'biru' => 'Biru',
        'coklat' => 'Coklat'
    ];
    
    foreach($warna_map as $key => $value) {
        if(stripos($str, $key) !== false) {
            $warna = $value;
            break;
        }
    }
    
    $sabuk = $warna;
    $tingkat_kyu = $kyu ? "Kyu $kyu" : '';
    
    return [$sabuk, $tingkat_kyu];
}

// Function untuk parse tanggal ujian dari string kompleks
function parseTanggalUjian($str) {
    if(empty($str)) return null;
    
    $str = trim($str);
    
    // Extract dates (DD-MM-YYYY or DD/MM/YYYY)
    if(preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $str, $matches)) {
        return parseTanggalIndo($matches[1]);
    }
    
    return null;
}

// Function untuk detect gender from name
function detectGender($nama) {
    $nama_lower = strtolower($nama);
    
    // Female keywords
    $female_keywords = ['dewi', 'putri', 'siti', 'aisyah', 'ayu', 'fitri', 'rani', 'wati', 'ningsih', 
                        'ratna', 'sri', 'indah', 'lestari', 'gina', 'nurul', 'fatma', 'retno',
                        'verani', 'natasya', 'naura', 'khanza', 'keisya', 'kayla', 'humaira'];
    
    foreach($female_keywords as $keyword) {
        if(stripos($nama_lower, $keyword) !== false) {
            return 'P';
        }
    }
    
    return 'L'; // Default laki-laki
}

// Function untuk standardize status
function standardizeStatus($str) {
    $str = strtoupper(trim($str));
    
    if(in_array($str, ['AKTIF', 'ACTIVE'])) {
        return 'Aktif';
    } elseif(in_array($str, ['NON-AKTIF', 'NONAKTIF', 'NON AKTIF', 'INACTIVE'])) {
        return 'Non-Aktif';
    } elseif(in_array($str, ['MENINGGAL', 'ALMARHUM', 'ALMARHUMAH'])) {
        return 'Meninggal';
    }
    
    return 'Aktif'; // Default
}

$imported = 0;
$skipped = 0;
$errors = [];
$processed = false;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {
    $processed = true;
    
    if(!file_exists($csvFile)) {
        $errors[] = "File CSV tidak ditemukan: $csvFile";
    } else {
        try {
            // Disable buffering untuk real-time output
            if(ob_get_level()) ob_end_flush();
            
            $pdo->beginTransaction();
            
            // Set locale untuk parsing tanggal
            setlocale(LC_TIME, 'id_ID', 'Indonesian');
            
            // Cache existing kode_kohai untuk optimasi duplicate check
            $existing_codes = [];
            $stmt = $pdo->query("SELECT kode_kohai FROM kohai");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existing_codes[$row['kode_kohai']] = true;
            }
            
            $file = fopen($csvFile, 'r');
            if(!$file) {
                throw new Exception("Tidak dapat membuka file CSV");
            }
            
            $header = null;
            $row_num = 0;
            
            while(($row = fgetcsv($file, 10000, ',')) !== false) {
                $row_num++;
                
                // Skip 3 baris pertama (empty rows)
                if($row_num <= 3) continue;
                
                // Baris 4 adalah header
                if($row_num == 4) {
                    $header = $row;
                    continue;
                }
                
                // Baris 5 adalah RANTING, skip
                if($row_num == 5) continue;
                
                // Skip empty rows
                if(empty($row[1]) || empty($row[2])) continue;
                
                try {
                    // Parse data dengan null coalescing
                    $no_reg_ijazah = isset($row[1]) ? trim($row[1]) : '';
                    $nama = isset($row[2]) ? trim($row[2]) : '';
                    $tempat_tgl_lahir = isset($row[3]) ? trim($row[3]) : '';
                    $warna_sabuk = isset($row[4]) ? trim($row[4]) : '';
                    $tanggal_ujian_str = isset($row[5]) ? trim($row[5]) : '';
                    $cabang = isset($row[6]) ? trim($row[6]) : '';
                    $provinsi = isset($row[7]) ? trim($row[7]) : '';
                    $keterangan = isset($row[8]) ? trim($row[8]) : '';
                    $status_anggota = isset($row[9]) ? trim($row[9]) : 'AKTIF';
                    $foto = isset($row[10]) ? trim($row[10]) : '';
                    
                    // Validation
                    if(empty($no_reg_ijazah) || empty($nama)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Extract kode kohai (first registration number)
                    $kode_kohai = extractKodeKohai($no_reg_ijazah);
                    
                    // Validasi kode kohai tidak boleh kosong
                    if(empty($kode_kohai)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Check duplicate menggunakan cache (lebih cepat)
                    if(isset($existing_codes[$kode_kohai])) {
                        $skipped++;
                        continue;
                    }
                    
                    // Tambahkan ke cache untuk row berikutnya
                    $existing_codes[$kode_kohai] = true;
                    
                    // Parse tempat & tanggal lahir
                    list($tempat_lahir, $tanggal_lahir) = parseTempatlahir($tempat_tgl_lahir);
                    
                    // Parse sabuk & kyu
                    list($sabuk, $tingkat_kyu) = parseSabukKyu($warna_sabuk);
                    
                    // Parse tanggal ujian
                    $tanggal_ujian = parseTanggalUjian($tanggal_ujian_str);
                    
                    // Detect gender
                    $jenis_kelamin = detectGender($nama);
                    
                    // Standardize status
                    $status = standardizeStatus($status_anggota);
                    
                    // Alamat dari provinsi
                    $alamat = $provinsi;
                    
                    // Insert data
                    $sql = "INSERT INTO kohai (
                        kode_kohai, nama, tempat_lahir, tanggal_lahir, jenis_kelamin,
                        tingkat_kyu, sabuk, dojo_cabang, alamat, status,
                        nomor_ijazah, tanggal_ujian, keterangan, foto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $kode_kohai,
                        $nama,
                        $tempat_lahir,
                        $tanggal_lahir,
                        $jenis_kelamin,
                        $tingkat_kyu,
                        $sabuk,
                        $cabang,
                        $alamat,
                        $status,
                        $no_reg_ijazah, // Full nomor ijazah
                        $tanggal_ujian,
                        $keterangan,
                        $foto
                    ]);
                    
                    $imported++;
                    
                    // Progress indicator setiap 50 records
                    if($imported % 50 == 0) {
                        echo "<script>console.log('Progress: {$imported} records imported...');</script>";
                        flush();
                    }
                    
                } catch(PDOException $e) {
                    $nama_display = isset($nama) ? $nama : 'Unknown';
                    $errors[] = "Baris $row_num ($nama_display): " . $e->getMessage();
                    $skipped++;
                } catch(Exception $e) {
                    $nama_display = isset($nama) ? $nama : 'Unknown';
                    $errors[] = "Baris $row_num ($nama_display): " . $e->getMessage();
                    $skipped++;
                }
            }
            
            fclose($file);
            $pdo->commit();
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// Get current count
try {
    $total_kohai = $pdo->query("SELECT COUNT(*) FROM kohai")->fetchColumn();
} catch(PDOException $e) {
    $total_kohai = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Kohai CSV - YPOK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 5px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-box h3 {
            color: #1e40af;
            margin-bottom: 15px;
        }
        
        .mapping-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .mapping-table th,
        .mapping-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .mapping-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        
        .mapping-table tr:hover {
            background: #f9fafb;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        button {
            padding: 15px 40px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            max-width: 300px;
            margin: 20px auto;
            display: block;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }
        
        .error-list {
            background: #fee2e2;
            border-left: 5px solid #ef4444;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .error-list h4 {
            color: #991b1b;
            margin-bottom: 10px;
        }
        
        .error-list ul {
            list-style: none;
            padding-left: 0;
        }
        
        .error-list li {
            color: #7f1d1d;
            padding: 5px 0;
            font-size: 14px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📥 Import Data Kohai dari CSV</h1>
            <p>Import data dari Google Sheets: NO.REGISTRASI IJAZAH KYU YPOK</p>
        </div>
        
        <div class="content">
            <?php if($processed): ?>
                <div class="stats">
                    <div class="stat-card success">
                        <div class="stat-number">✅ <?= $imported ?></div>
                        <div class="stat-label">Data Berhasil Di-import</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number">⏭️ <?= $skipped ?></div>
                        <div class="stat-label">Data Di-skip (Duplikat/Kosong)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">📊 <?= $total_kohai ?></div>
                        <div class="stat-label">Total Data Kohai</div>
                    </div>
                </div>
                
                <?php if(!empty($errors)): ?>
                    <div class="error-list">
                        <h4>⚠️ Error yang Terjadi:</h4>
                        <ul>
                            <?php foreach(array_slice($errors, 0, 20) as $error): ?>
                                <li>• <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                            <?php if(count($errors) > 20): ?>
                                <li><em>... dan <?= count($errors) - 20 ?> error lainnya</em></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <a href="kohai.php" class="back-link">← Kembali ke Data Kohai</a>
                
            <?php else: ?>
                <div class="info-box">
                    <h3>📋 Informasi Import</h3>
                    <p><strong>File CSV:</strong> <?= basename($csvFile) ?></p>
                    <p><strong>Status File:</strong> 
                        <?php if(file_exists($csvFile)): ?>
                            <span style="color: #10b981; font-weight: 600;">✅ Ditemukan</span>
                            (<?= number_format(filesize($csvFile) / 1024, 2) ?> KB)
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: 600;">❌ Tidak Ditemukan</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Total Kohai Saat Ini:</strong> <?= $total_kohai ?> data</p>
                    <p style="margin-top: 15px;">Tool ini akan mengimport data Kohai dari file CSV Google Sheets dengan mapping otomatis.</p>
                </div>
                
                <div class="info-box">
                    <h3>🔄 Mapping Kolom CSV → Database</h3>
                    <table class="mapping-table">
                        <tr>
                            <th>Kolom CSV</th>
                            <th>Field Database</th>
                            <th>Keterangan</th>
                        </tr>
                        <tr>
                            <td>NO. REGISTRASI IJAZAH</td>
                            <td>kode_kohai + nomor_ijazah</td>
                            <td>Kode pertama → kode_kohai, semua → nomor_ijazah</td>
                        </tr>
                        <tr>
                            <td>NAMA</td>
                            <td>nama + jenis_kelamin</td>
                            <td>Auto-detect gender dari nama</td>
                        </tr>
                        <tr>
                            <td>TEMPAT, TGL LAHIR</td>
                            <td>tempat_lahir + tanggal_lahir</td>
                            <td>Split by comma, parse tanggal Indonesia</td>
                        </tr>
                        <tr>
                            <td>WARNA SABUK</td>
                            <td>sabuk + tingkat_kyu</td>
                            <td>Extract angka (kyu) dan warna</td>
                        </tr>
                        <tr>
                            <td>TANGGAL UJIAN, KYU, DAN WARNA SABUK</td>
                            <td>tanggal_ujian</td>
                            <td>Extract tanggal dari string kompleks</td>
                        </tr>
                        <tr>
                            <td>CABANG/ASAL SEKOLAH</td>
                            <td>dojo_cabang</td>
                            <td>Direct mapping</td>
                        </tr>
                        <tr>
                            <td>ASAL PROVINSI/KAB/KOTA</td>
                            <td>alamat</td>
                            <td>Masuk ke field alamat</td>
                        </tr>
                        <tr>
                            <td>KETERANGAN</td>
                            <td>keterangan</td>
                            <td>Direct mapping</td>
                        </tr>
                        <tr>
                            <td>STATUS ANGGOTA</td>
                            <td>status</td>
                            <td>AKTIF → Aktif, standardization</td>
                        </tr>
                        <tr>
                            <td>FOTO</td>
                            <td>foto</td>
                            <td>Direct mapping (URL/path)</td>
                        </tr>
                    </table>
                </div>
                
                <div class="info-box">
                    <h3>⚙️ Fitur Import</h3>
                    <ul style="padding-left: 20px; color: #374151;">
                        <li>✅ Auto-parse tanggal Indonesia (01-MEI-2023 → 2023-05-01)</li>
                        <li>✅ Auto-detect gender dari nama</li>
                        <li>✅ Extract kode kohai pertama dari multiple codes</li>
                        <li>✅ Parse sabuk dan tingkat kyu otomatis</li>
                        <li>✅ Skip data duplikat berdasarkan kode_kohai</li>
                        <li>✅ Transaction safety (rollback jika error)</li>
                        <li>✅ Standardisasi status (AKTIF → Aktif)</li>
                    </ul>
                </div>
                
                <?php if(file_exists($csvFile)): ?>
                    <form method="POST" id="importForm">
                        <button type="submit" name="import" id="importBtn">🚀 Mulai Import Data Kohai</button>
                    </form>
                    
                    <div id="progressContainer" style="display:none; margin-top: 30px;">
                        <h3>⏳ Import Sedang Berjalan...</h3>
                        <div style="background: #e5e7eb; border-radius: 10px; height: 30px; overflow: hidden; margin: 20px 0;">
                            <div id="progressBar" style="background: linear-gradient(90deg, #10b981, #059669); height: 100%; width: 0%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"></div>
                        </div>
                        <p id="progressText" style="text-align: center; color: #6b7280;">Memulai import...</p>
                    </div>
                    
                    <script>
                    document.getElementById('importForm').addEventListener('submit', function() {
                        document.getElementById('importBtn').disabled = true;
                        document.getElementById('importBtn').textContent = '⏳ Importing...';
                        document.getElementById('progressContainer').style.display = 'block';
                        
                        // Simulate progress (since we can't get real-time progress easily with PHP)
                        let progress = 0;
                        const interval = setInterval(function() {
                            progress += Math.random() * 5;
                            if(progress > 95) progress = 95;
                            document.getElementById('progressBar').style.width = progress + '%';
                            document.getElementById('progressBar').textContent = Math.round(progress) + '%';
                            document.getElementById('progressText').textContent = 'Memproses data... ' + Math.round(progress) + '%';
                        }, 500);
                        
                        // Stop simulation when form actually submits
                        setTimeout(function() {
                            clearInterval(interval);
                        }, 120000); // 2 minutes timeout
                    });
                    </script>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px;">
                        <p style="color: #ef4444; font-weight: 600; font-size: 16px;">
                            ⚠️ File CSV tidak ditemukan. Pastikan file sudah ada di folder googlesheet/
                        </p>
                        <button type="button" disabled style="opacity: 0.5; cursor: not-allowed;">
                            🚀 Mulai Import Data Kohai (File Tidak Ditemukan)
                        </button>
                    </div>
                <?php endif; ?>
                
                <a href="kohai.php" class="back-link">← Kembali ke Data Kohai</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
