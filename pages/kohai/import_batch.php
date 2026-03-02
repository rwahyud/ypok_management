<?php
/**
 * IMPORT KOHAI BATCH PROCESSING
 * ==============================
 * Versi batch processing untuk menghindari timeout
 * Import data dalam chunk kecil menggunakan AJAX
 */

require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Set limits
set_time_limit(300);
ini_set('memory_limit', '512M');

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

function parseTempatlahir($str) {
    if(empty($str)) return ['', null];
    
    $str = trim($str);
    $parts = explode(',', $str);
    
    if(count($parts) >= 2) {
        $tempat = trim($parts[0]);
        $tanggal_str = trim(implode(',', array_slice($parts, 1)));
        $tanggal = parseTanggalIndo($tanggal_str);
        return [$tempat, $tanggal];
    }
    
    if(preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $str, $matches)) {
        $tanggal = parseTanggalIndo($matches[1]);
        $tempat = trim(str_replace($matches[1], '', $str));
        return [$tempat, $tanggal];
    }
    
    return [$str, null];
}

function extractKodeKohai($str) {
    if(empty($str)) return '';
    $str = trim($str);
    $parts = preg_split('/\s+/', $str);
    return trim($parts[0]);
}

function parseSabukKyu($str) {
    if(empty($str)) return ['', ''];
    
    $str = trim($str);
    
    preg_match('/(\d+)/', $str, $matches);
    $kyu = isset($matches[1]) ? $matches[1] : '';
    
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

function parseTanggalUjian($str) {
    if(empty($str)) return null;
    $str = trim($str);
    
    if(preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $str, $matches)) {
        return parseTanggalIndo($matches[1]);
    }
    
    return null;
}

function detectGender($nama) {
    $nama_lower = strtolower($nama);
    
    $female_keywords = ['dewi', 'putri', 'siti', 'aisyah', 'ayu', 'fitri', 'rani', 'wati', 'ningsih', 
                        'ratna', 'sri', 'indah', 'lestari', 'gina', 'nurul', 'fatma', 'retno',
                        'verani', 'natasya', 'naura', 'khanza', 'keisya', 'kayla', 'humaira'];
    
    foreach($female_keywords as $keyword) {
        if(stripos($nama_lower, $keyword) !== false) {
            return 'P';
        }
    }
    
    return 'L';
}

function standardizeStatus($str) {
    $str = strtoupper(trim($str));
    
    if(in_array($str, ['AKTIF', 'ACTIVE'])) {
        return 'Aktif';
    } elseif(in_array($str, ['NON-AKTIF', 'NONAKTIF', 'NON AKTIF', 'INACTIVE'])) {
        return 'Non-Aktif';
    } elseif(in_array($str, ['MENINGGAL', 'ALMARHUM', 'ALMARHUMAH'])) {
        return 'Meninggal';
    }
    
    return 'Aktif';
}

// AJAX Handler untuk batch processing
if(isset($_POST['action']) && $_POST['action'] == 'import_batch') {
    header('Content-Type: application/json');
    
    $start = intval($_POST['start'] ?? 0);
    $batchSize = 50; // Process 50 rows per batch
    
    $imported = 0;
    $skipped = 0;
    $errors = [];
    
    try {
        // Get existing codes for duplicate check
        $existing_codes = [];
        $stmt = $pdo->query("SELECT kode_kohai FROM kohai");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existing_codes[$row['kode_kohai']] = true;
        }
        
        $file = fopen($csvFile, 'r');
        if(!$file) {
            throw new Exception("Tidak dapat membuka file CSV");
        }
        
        $row_num = 0;
        $processed = 0;
        
        while(($row = fgetcsv($file, 10000, ',')) !== false) {
            $row_num++;
            
            // Skip header rows (3 empty + 1 header + 1 RANTING)
            if($row_num <= 5) continue;
            
            // Skip rows before start position
            if($row_num <= $start + 5) continue;
            
            // Stop after batch size
            if($processed >= $batchSize) break;
            
            // Skip empty rows
            if(empty($row[1]) || empty($row[2])) {
                $skipped++;
                $processed++;
                continue;
            }
            
            try {
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
                
                if(empty($no_reg_ijazah) || empty($nama)) {
                    $skipped++;
                    $processed++;
                    continue;
                }
                
                $kode_kohai = extractKodeKohai($no_reg_ijazah);
                
                if(empty($kode_kohai)) {
                    $skipped++;
                    $processed++;
                    continue;
                }
                
                // Check duplicate
                if(isset($existing_codes[$kode_kohai])) {
                    $skipped++;
                    $processed++;
                    continue;
                }
                
                list($tempat_lahir, $tanggal_lahir) = parseTempatlahir($tempat_tgl_lahir);
                list($sabuk, $tingkat_kyu) = parseSabukKyu($warna_sabuk);
                $tanggal_ujian = parseTanggalUjian($tanggal_ujian_str);
                $jenis_kelamin = detectGender($nama);
                $status = standardizeStatus($status_anggota);
                $alamat = $provinsi;
                
                $sql = "INSERT INTO kohai (
                    kode_kohai, nama, tempat_lahir, tanggal_lahir, jenis_kelamin,
                    tingkat_kyu, sabuk, dojo_cabang, alamat, status,
                    nomor_ijazah, tanggal_ujian, keterangan, foto
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $kode_kohai, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin,
                    $tingkat_kyu, $sabuk, $cabang, $alamat, $status,
                    $no_reg_ijazah, $tanggal_ujian, $keterangan, $foto
                ]);
                
                $existing_codes[$kode_kohai] = true;
                $imported++;
                
            } catch(Exception $e) {
                $errors[] = "Row $row_num: " . $e->getMessage();
                $skipped++;
            }
            
            $processed++;
        }
        
        fclose($file);
        
        // Check if done
        $nextStart = $start + $batchSize;
        $totalRows = 1852; // Known from file
        $isDone = ($row_num >= $totalRows || $processed < $batchSize);
        
        echo json_encode([
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'nextStart' => $nextStart,
            'isDone' => $isDone,
            'progress' => min(100, round(($nextStart / $totalRows) * 100)),
            'errors' => $errors
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    exit();
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
    <title>Import Kohai Batch - YPOK</title>
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
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        button {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .progress-container {
            margin-top: 30px;
            display: none;
        }
        
        .progress-bar {
            background: #e5e7eb;
            height: 40px;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #10b981, #059669);
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success-msg {
            background: #d1fae5;
            border-left-color: #10b981;
            color: #065f46;
        }
        
        .error-msg {
            background: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
            margin-top: 20px;
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
            <h1>📥 Import Kohai (Batch Processing)</h1>
            <p>Import dengan batch untuk menghindari timeout</p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <h3 style="margin-bottom: 10px;">⚡ Keunggulan Batch Processing</h3>
                <ul style="padding-left: 20px; color: #374151;">
                    <li>✅ Tidak akan timeout (proses per 50 data)</li>
                    <li>✅ Progress bar real-time</li>
                    <li>✅ Bisa di-pause dan resume</li>
                    <li>✅ Lebih stabil untuk data besar (1800+ rows)</li>
                </ul>
                <p style="margin-top: 15px;"><strong>Total Kohai Saat Ini:</strong> <span id="totalKohai"><?= $total_kohai ?></span> data</p>
            </div>
            
            <button id="startBtn" onclick="startImport()">🚀 Mulai Import Batch</button>
            
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill">0%</div>
                </div>
                <p id="statusText" style="text-align: center; color: #6b7280; margin-bottom: 15px;">Memulai import...</p>
                
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number" id="importedCount">0</div>
                        <div class="stat-label">Imported</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="skippedCount">0</div>
                        <div class="stat-label">Skipped</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="progressPercent">0%</div>
                        <div class="stat-label">Progress</div>
                    </div>
                </div>
            </div>
            
            <div id="resultMessage"></div>
            
            <a href="index.php" class="back-link">← Kembali ke Data Kohai</a>
        </div>
    </div>
    
    <script>
        let totalImported = 0;
        let totalSkipped = 0;
        let allErrors = [];
        
        async function startImport() {
            document.getElementById('startBtn').disabled = true;
            document.getElementById('progressContainer').style.display = 'block';
            
            await importBatch(0);
        }
        
        async function importBatch(start) {
            try {
                const formData = new FormData();
                formData.append('action', 'import_batch');
                formData.append('start', start);
                
                const response = await fetch('import_kohai_batch.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if(!result.success) {
                    throw new Error(result.error);
                }
                
                totalImported += result.imported;
                totalSkipped += result.skipped;
                allErrors = allErrors.concat(result.errors);
                
                document.getElementById('importedCount').textContent = totalImported;
                document.getElementById('skippedCount').textContent = totalSkipped;
                document.getElementById('progressPercent').textContent = result.progress + '%';
                document.getElementById('progressFill').style.width = result.progress + '%';
                document.getElementById('progressFill').textContent = result.progress + '%';
                document.getElementById('statusText').textContent = 
                    `Processing... Imported: ${totalImported}, Skipped: ${totalSkipped}`;
                
                if(result.isDone) {
                    // Import selesai
                    document.getElementById('progressFill').style.width = '100%';
                    document.getElementById('progressFill').textContent = '100%';
                    document.getElementById('statusText').textContent = '✅ Import selesai!';
                    
                    let message = `
                        <div class="info-box success-msg">
                            <h3>✅ Import Berhasil!</h3>
                            <p><strong>${totalImported}</strong> data berhasil di-import</p>
                            <p><strong>${totalSkipped}</strong> data di-skip (duplikat/kosong)</p>
                        </div>
                    `;
                    
                    if(allErrors.length > 0) {
                        message += `
                            <div class="error-msg">
                                <h4>⚠️ Beberapa Error:</h4>
                                <ul style="margin-top: 10px; padding-left: 20px;">
                                    ${allErrors.slice(0, 10).map(err => `<li>${err}</li>`).join('')}
                                    ${allErrors.length > 10 ? `<li><em>... dan ${allErrors.length - 10} error lainnya</em></li>` : ''}
                                </ul>
                            </div>
                        `;
                    }
                    
                    document.getElementById('resultMessage').innerHTML = message;
                    
                } else {
                    // Lanjut ke batch berikutnya
                    await new Promise(resolve => setTimeout(resolve, 100)); // Small delay
                    await importBatch(result.nextStart);
                }
                
            } catch(error) {
                document.getElementById('resultMessage').innerHTML = `
                    <div class="error-msg">
                        <h4>❌ Error:</h4>
                        <p>${error.message}</p>
                    </div>
                `;
                document.getElementById('startBtn').disabled = false;
            }
        }
    </script>
</body>
</html>
