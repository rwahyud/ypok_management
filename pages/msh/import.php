<?php
/**
 * IMPORT DATA MSH DARI CSV
 * ========================
 * Tool untuk import data MSH dari file "NO.MSH YPOK - NO.MSH.csv"
 * ke tabel majelis_sabuk_hitam dengan mapping otomatis
 */

require_once '../../config/supabase.php';

// Hanya admin yang bisa import
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Hanya admin yang dapat mengimport data.");
}

$csv_file = 'googlesheet/NO.MSH YPOK - NO.MSH.csv';

// Function untuk parse "TEMPAT, TGL LAHIR" → [tempat_lahir, tanggal_lahir]
function parseTempatlahir($str) {
    if(empty($str)) return [null, null];
    
    // Format: "Manado, 28-08-1965" atau "Manado,28-08-1965"
    $parts = array_map('trim', explode(',', $str));
    
    if(count($parts) < 2) return [$str, null];
    
    $tempat = $parts[0];
    $tanggal_str = $parts[1];
    
    // Parse tanggal berbagai format
    $tanggal = parseTanggal($tanggal_str);
    
    return [$tempat, $tanggal];
}

// Function untuk parse tanggal Indonesia → Y-m-d
function parseTanggal($str) {
    if(empty($str)) return null;
    
    // Remove extra spaces
    $str = trim($str);
    
    // Format: "11-Maret-2023", "28-08-1965", "23-09-2003", "31 Oktober 1975"
    $bulan_map = [
        'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
        'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
        'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
        'mei' => '05', 'jun' => '06', 'jul' => '07', 'agu' => '08',
        'sep' => '09', 'okt' => '10', 'nov' => '11', 'des' => '12'
    ];
    
    // Format: "11-Maret-2023" atau "31 Oktober 1975"
    foreach($bulan_map as $indo => $num) {
        $str = str_ireplace($indo, $num, $str);
    }
    
    // Try different date formats
    $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd m Y', 'd-m-y'];
    
    foreach($formats as $format) {
        $date = DateTime::createFromFormat($format, $str);
        if($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    return null;
}

// Function untuk parse "NOMOR IJAZAH, TINGKATAN DAN" → [nomor_ijazah, tingkat_dan]
function parseIjazahDan($str) {
    if(empty($str)) return [null, null];
    
    // Format: "DAN X, 0001/X/2023" atau "DAN IX" atau "DAN VIII "
    $str = trim($str);
    
    // Extract tingkat Dan (DAN I, DAN II, ... DAN X)
    $tingkat = null;
    if(preg_match('/DAN\s+(X|IX|VIII|VII|VI|V|IV|III|II|I)\b/i', $str, $matches)) {
        $tingkat = strtoupper($matches[1]);
    }
    
    // Extract nomor ijazah (format: 0001/X/2023)
    $nomor = null;
    if(preg_match('/(\d{4}\/[IVX]+\/\d{4})/', $str, $matches)) {
        $nomor = $matches[1];
    }
    
    return [$nomor, $tingkat];
}

// Function untuk convert status
function convertStatus($ket) {
    $ket = strtoupper(trim($ket));
    if($ket == 'AKTIF') return 'aktif';
    if($ket == 'ALMARHUM') return 'meninggal';
    if($ket == 'NON-AKTIF') return 'non-aktif';
    return 'aktif'; // default
}

// Function untuk detect jenis kelamin dari nama (simple heuristic)
function detectGender($nama) {
    $nama_lower = strtolower($nama);
    
    // Female indicators
    $female_keywords = ['dewi', 'putri', 'aisyah', 'nelly', 'fahimah', 'aldina', 'aisy', 
                        'ghea', 'rafila', 'nadira', 'alinsyra', 'henna', 'raisa', 'astrid'];
    
    foreach($female_keywords as $keyword) {
        if(strpos($nama_lower, $keyword) !== false) {
            return 'P';
        }
    }
    
    return 'L'; // Default laki-laki
}

// PROSES IMPORT
if(isset($_POST['action']) && $_POST['action'] == 'import') {
    try {
        if(!file_exists($csv_file)) {
            throw new Exception("File CSV tidak ditemukan: $csv_file");
        }
        
        // Baca CSV
        $handle = fopen($csv_file, 'r');
        if(!$handle) {
            throw new Exception("Tidak dapat membuka file CSV");
        }
        
        // Skip header rows (baris 1-4 adalah header yayasan dan judul)
        for($i = 0; $i < 5; $i++) {
            fgets($handle);
        }
        
        $pdo->beginTransaction();
        
        $success = 0;
        $skipped = 0;
        $errors = [];
        $line_number = 6;
        
        while(($data = fgetcsv($handle, 1000, ',')) !== false) {
            $line_number++;
            
            // Skip empty rows
            if(empty($data[0]) && empty($data[1])) continue;
            
            // Parse data CSV
            $no = trim($data[0] ?? '');
            $kode_msh = trim($data[1] ?? '');
            $nama = trim($data[2] ?? '');
            $tempat_tgl_lahir = trim($data[3] ?? '');
            $ijazah_dan = trim($data[4] ?? '');
            $tanggal_ujian = trim($data[5] ?? '');
            $provinsi = trim($data[6] ?? '');
            $alamat = trim($data[7] ?? '');
            $jenis_dan = trim($data[8] ?? '');
            $ket = trim($data[9] ?? '');
            
            // Skip if no nama
            if(empty($nama)) continue;
            
            // Check duplicate berdasarkan kode_msh
            if(!empty($kode_msh)) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM majelis_sabuk_hitam WHERE kode_msh = ?");
                $check->execute([$kode_msh]);
                if($check->fetchColumn() > 0) {
                    $skipped++;
                    continue; // Skip duplikat
                }
            }
            
            // Parse tempat dan tanggal lahir
            list($tempat_lahir, $tanggal_lahir) = parseTempatlahir($tempat_tgl_lahir);
            
            // Parse ijazah dan tingkat Dan
            list($nomor_ijazah, $tingkat_dan) = parseIjazahDan($ijazah_dan);
            
            // Parse tanggal ujian (dari kolom tanggal_ujian)
            $tanggal_ujian_parsed = parseTanggal($tanggal_ujian);
            
            // Parse tanggal ujian/lulus (untuk backward compatibility)
            $tanggal_lulus = parseTanggal($tanggal_ujian);
            
            // Convert status
            $status = convertStatus($ket);
            
            // Detect jenis kelamin
            $jenis_kelamin = detectGender($nama);
            
            // Format tingkat_sabuk (sama dengan tingkat_dan untuk compatibility)
            $tingkat_sabuk = $tingkat_dan;
            
            // Insert ke database
            $sql = "INSERT INTO majelis_sabuk_hitam (
                kode_msh, nama, tempat_lahir, tanggal_lahir, jenis_kelamin,
                tingkat_sabuk, tingkat_dan, tanggal_lulus, tanggal_ujian, nomor_ijazah,
                dojo_cabang, alamat, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";;
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $kode_msh ?: null,
                $nama,
                $tempat_lahir,
                $tanggal_lahir,
                $jenis_kelamin,
                $tingkat_sabuk,
                $tingkat_dan,
                $tanggal_lulus,
                $tanggal_ujian_parsed,
                $nomor_ijazah,
                $provinsi,
                $alamat,
                $status
            ]);
            
            if($result) {
                $success++;
            } else {
                $errors[] = "Baris $line_number: " . implode(', ', $stmt->errorInfo());
            }
        }
        
        fclose($handle);
        
        $pdo->commit();
        
        $result_message = [
            'success' => true,
            'imported' => $success,
            'skipped' => $skipped,
            'errors' => $errors
        ];
        
    } catch(Exception $e) {
        if(isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $result_message = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data MSH dari CSV - YPOK</title>
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
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-box h3 {
            color: #1e40af;
            margin-bottom: 15px;
        }
        
        .info-box ul {
            margin-left: 20px;
            line-height: 1.8;
        }
        
        .mapping-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .result-box {
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .result-success {
            background: #d4edda;
            border-left: 5px solid #28a745;
            color: #155724;
        }
        
        .result-error {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            color: #721c24;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .stat-label {
            color: #64748b;
            margin-top: 5px;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #856404;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        code {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Import Data MSH dari CSV</h1>
            <p>Upload data Majelis Sabuk Hitam dari Google Sheet</p>
        </div>
        
        <div class="content">
            <?php if(isset($result_message)): ?>
                <?php if($result_message['success']): ?>
                    <div class="result-box result-success">
                        <h3>✅ Import Berhasil!</h3>
                        <div class="stats">
                            <div class="stat-card">
                                <div class="stat-number"><?= $result_message['imported'] ?></div>
                                <div class="stat-label">Data Berhasil</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?= $result_message['skipped'] ?></div>
                                <div class="stat-label">Data Dilewati (Duplikat)</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?= count($result_message['errors']) ?></div>
                                <div class="stat-label">Error</div>
                            </div>
                        </div>
                        
                        <?php if(count($result_message['errors']) > 0): ?>
                            <h4 style="margin-top: 20px; color: #dc2626;">Error Details:</h4>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <?php foreach($result_message['errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <div class="actions">
                            <a href="index.php" class="btn btn-primary">Lihat Data MSH</a>
                            <a href="import.php" class="btn btn-secondary">Import Lagi</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="result-box result-error">
                        <h3>❌ Import Gagal</h3>
                        <p><?= htmlspecialchars($result_message['message']) ?></p>
                        <div class="actions">
                            <a href="import.php" class="btn btn-secondary">Coba Lagi</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="info-box">
                    <h3>📋 Informasi Import</h3>
                    <ul>
                        <li>File CSV: <code><?= $csv_file ?></code></li>
                        <li>Target Database: <code>majelis_sabuk_hitam</code></li>
                        <li>Mode: Insert dengan deteksi duplikat otomatis</li>
                        <li>Duplikat akan di-skip berdasarkan <code>kode_msh</code></li>
                    </ul>
                </div>
                
                <h3 style="margin-bottom: 15px;">🔀 Mapping Kolom CSV → Database</h3>
                <table class="mapping-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Kolom CSV</th>
                            <th style="width: 35%;">Kolom Database</th>
                            <th>Proses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>NO.MSH</code></td>
                            <td><code>kode_msh</code></td>
                            <td>Langsung (contoh: 0001)</td>
                        </tr>
                        <tr>
                            <td><code>NAMA</code></td>
                            <td><code>nama</code></td>
                            <td>Langsung</td>
                        </tr>
                        <tr>
                            <td><code>TEMPAT, TGL LAHIR</code></td>
                            <td><code>tempat_lahir</code></td>
                            <td>Extract bagian tempat</td>
                        </tr>
                        <tr>
                            <td><code>TEMPAT, TGL LAHIR</code></td>
                            <td><code>tanggal_lahir</code></td>
                            <td>Extract & convert tanggal</td>
                        </tr>
                        <tr>
                            <td>-</td>
                            <td><code>jenis_kelamin</code></td>
                            <td>Auto-detect dari nama</td>
                        </tr>
                        <tr>
                            <td><code>NOMOR IJAZAH, TINGKATAN DAN</code></td>
                            <td><code>tingkat_dan</code></td>
                            <td>Extract DAN (I-X)</td>
                        </tr>
                        <tr>
                            <td><code>NOMOR IJAZAH, TINGKATAN DAN</code></td>
                            <td><code>nomor_ijazah</code></td>
                            <td>Extract nomor (0001/X/2023)</td>
                        </tr>
                        <tr>
                            <td><code>TANGGAL UJIAN</code></td>
                            <td><code>tanggal_lulus</code></td>
                            <td>Parse tanggal Indonesia</td>
                        </tr>
                        <tr>
                            <td><code>TANGGAL UJIAN</code></td>
                            <td><code>tanggal_ujian</code></td>
                            <td>Parse tanggal Indonesia (otomatis) ✨</td>
                        </tr>
                        <tr>
                            <td><code></code>ASAL PROVINSI</code></td>
                            <td><code>dojo_cabang</code></td>
                            <td>Langsung</td>
                        </tr>
                        <tr>
                            <td><code>ALAMAT</code></td>
                            <td><code>alamat</code></td>
                            <td>Langsung</td>
                        </tr>
                        <tr>
                            <td><code>KET</code></td>
                            <td><code>status</code></td>
                            <td>Convert (AKTIF→aktif, ALMARHUM→meninggal)</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="warning">
                    <strong>⚠️ Perhatian:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Proses ini akan menggunakan transaksi database (rollback otomatis jika error)</li>
                        <li>Data duplikat (berdasarkan kode_msh) akan di-skip</li>
                        <li>File CSV harus berada di: <code><?= $csv_file ?></code></li>
                    </ul>
                </div>
                
                <form method="POST" onsubmit="return confirm('Yakin ingin import data MSH dari CSV?\n\nData duplikat akan di-skip otomatis.');">
                    <input type="hidden" name="action" value="import">
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">🚀 Mulai Import</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
