<?php
/**
 * Script untuk menjalankan update database provinsi dengan kolom agregat
 * Jalankan file ini di browser: http://localhost/ypok_management/ypok_management/database/run_update_provinsi_agregat.php
 */

require_once '../config/database.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Update Database - Provinsi Agregat</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
    .step { margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
    .step-number { display: inline-block; background: #4CAF50; color: white; width: 30px; height: 30px; border-radius: 50%; text-align: center; line-height: 30px; margin-right: 10px; font-weight: bold; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    .btn { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 0; }
    .btn:hover { background: #45a049; }
</style>";
echo "</head><body>";
echo "<div class='container'>";
echo "<h1>🔄 Update Database - Kolom Agregat Provinsi</h1>";

try {
    // Baca file SQL
    $sql_file = __DIR__ . '/update_provinsi_agregat.sql';
    
    if(!file_exists($sql_file)) {
        throw new Exception("File update_provinsi_agregat.sql tidak ditemukan!");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Pisahkan query berdasarkan delimiter
    $queries = [];
    $current_query = '';
    $in_delimiter = false;
    
    $lines = explode("\n", $sql_content);
    
    foreach($lines as $line) {
        $line = trim($line);
        
        // Skip komentar dan baris kosong
        if(empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        // Handle DELIMITER
        if(stripos($line, 'DELIMITER') === 0) {
            $in_delimiter = !$in_delimiter;
            continue;
        }
        
        $current_query .= $line . ' ';
        
        // Jika tidak dalam delimiter block dan ada semicolon, itu akhir query
        if(!$in_delimiter && substr($line, -1) === ';') {
            $queries[] = trim($current_query);
            $current_query = '';
        }
        // Jika dalam delimiter block dan ada $$, itu akhir query
        elseif($in_delimiter && substr($line, -2) === '$$') {
            $queries[] = trim($current_query);
            $current_query = '';
        }
    }
    
    echo "<div class='info'><strong>📋 Informasi:</strong> Ditemukan " . count($queries) . " query untuk dijalankan.</div>";
    
    // Jalankan setiap query
    $success_count = 0;
    $error_count = 0;
    
    foreach($queries as $index => $query) {
        if(empty(trim($query))) continue;
        
        echo "<div class='step'>";
        echo "<span class='step-number'>" . ($index + 1) . "</span>";
        
        try {
            $pdo->exec($query);
            echo "<strong style='color: #28a745;'>✓ Berhasil</strong><br>";
            
            // Show summary of what was done
            if(stripos($query, 'ALTER TABLE provinsi') !== false) {
                echo "<em>Menambahkan kolom agregat ke tabel provinsi</em>";
            } elseif(stripos($query, 'UPDATE provinsi') !== false) {
                echo "<em>Mengisi data agregat dari tabel dojo</em>";
            } elseif(stripos($query, 'CREATE PROCEDURE') !== false) {
                echo "<em>Membuat stored procedure update_provinsi_stats</em>";
            } elseif(stripos($query, 'CREATE TRIGGER') !== false) {
                if(stripos($query, 'after_dojo_insert') !== false) {
                    echo "<em>Membuat trigger untuk INSERT dojo</em>";
                } elseif(stripos($query, 'after_dojo_update') !== false) {
                    echo "<em>Membuat trigger untuk UPDATE dojo</em>";
                } elseif(stripos($query, 'after_dojo_delete') !== false) {
                    echo "<em>Membuat trigger untuk DELETE dojo</em>";
                }
            }
            
            $success_count++;
        } catch(PDOException $e) {
            echo "<strong style='color: #dc3545;'>✗ Error:</strong> " . htmlspecialchars($e->getMessage());
            $error_count++;
        }
        
        echo "</div>";
    }
    
    echo "<hr>";
    
    if($error_count === 0) {
        echo "<div class='success'>";
        echo "<h2>✅ Update Berhasil!</h2>";
        echo "<p>Semua query berhasil dijalankan ($success_count query).</p>";
        echo "<h3>Perubahan yang dilakukan:</h3>";
        echo "<ul>";
        echo "<li>✓ Kolom <code>total_dojo</code> ditambahkan ke tabel provinsi</li>";
        echo "<li>✓ Kolom <code>total_anggota</code> ditambahkan ke tabel provinsi</li>";
        echo "<li>✓ Kolom <code>anggota_aktif</code> ditambahkan ke tabel provinsi</li>";
        echo "<li>✓ Kolom <code>anggota_non_aktif</code> ditambahkan ke tabel provinsi</li>";
        echo "<li>✓ Data agregat sudah dihitung dan disimpan</li>";
        echo "<li>✓ Trigger otomatis sudah dibuat untuk menjaga sinkronisasi data</li>";
        echo "</ul>";
        echo "<p><strong>Data akan otomatis terupdate</strong> setiap kali ada perubahan pada tabel dojo (INSERT, UPDATE, DELETE).</p>";
        echo "</div>";
        
        // Tampilkan data untuk verifikasi
        echo "<h3>📊 Data Provinsi Setelah Update:</h3>";
        $stmt = $pdo->query("SELECT id, nama_provinsi, total_dojo, total_anggota, anggota_aktif, anggota_non_aktif FROM provinsi ORDER BY nama_provinsi");
        $provinsi_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($provinsi_data) > 0) {
            echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
            echo "<thead>";
            echo "<tr style='background: #f8f9fa; border-bottom: 2px solid #dee2e6;'>";
            echo "<th style='padding: 12px; text-align: left;'>Provinsi</th>";
            echo "<th style='padding: 12px; text-align: center;'>Total Dojo</th>";
            echo "<th style='padding: 12px; text-align: center;'>Total Anggota</th>";
            echo "<th style='padding: 12px; text-align: center;'>Anggota Aktif</th>";
            echo "<th style='padding: 12px; text-align: center;'>Anggota Non Aktif</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach($provinsi_data as $prov) {
                echo "<tr style='border-bottom: 1px solid #dee2e6;'>";
                echo "<td style='padding: 12px;'>" . htmlspecialchars($prov['nama_provinsi']) . "</td>";
                echo "<td style='padding: 12px; text-align: center;'>" . $prov['total_dojo'] . "</td>";
                echo "<td style='padding: 12px; text-align: center;'>" . $prov['total_anggota'] . "</td>";
                echo "<td style='padding: 12px; text-align: center;'>" . $prov['anggota_aktif'] . "</td>";
                echo "<td style='padding: 12px; text-align: center;'>" . $prov['anggota_non_aktif'] . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<div class='info'>Belum ada data provinsi.</div>";
        }
        
    } else {
        echo "<div class='error'>";
        echo "<h2>⚠️ Update Selesai dengan Beberapa Error</h2>";
        echo "<p>Berhasil: $success_count query | Error: $error_count query</p>";
        echo "<p>Silakan periksa error di atas dan coba lagi jika diperlukan.</p>";
        echo "</div>";
    }
    
    echo "<br><a href='../lokasi.php' class='btn'>← Kembali ke Halaman Lokasi</a>";
    
} catch(Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>
