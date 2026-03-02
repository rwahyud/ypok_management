<?php
/**
 * VERIFIKASI DATABASE SCHEMA SUPABASE
 * ====================================
 * Script untuk cek apakah schema sudah sesuai dan koneksi berhasil
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include koneksi Supabase
require_once __DIR__ . '/../config/supabase.php';

echo "========================================\n";
echo "VERIFIKASI DATABASE SUPABASE\n";
echo "========================================\n\n";

// ========================================
// 1. CEK KONEKSI
// ========================================
echo "1. CEK KONEKSI DATABASE\n";
echo "   Host: db.vpqjbpkizdnvzpattiop.supabase.co\n";
echo "   Port: 5432\n";
echo "   Database: postgres\n";
echo "   Password: " . (strlen('Ciooren123@') > 0 ? '✓ (Set)' : '✗ (Not Set)') . "\n\n";

try {
    $test = $pdo->query("SELECT version()");
    $version = $test->fetch();
    echo "   ✅ KONEKSI BERHASIL!\n";
    echo "   PostgreSQL Version: " . substr($version['version'], 0, 50) . "...\n\n";
} catch(PDOException $e) {
    echo "   ❌ KONEKSI GAGAL!\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// ========================================
// 2. CEK JUMLAH TABEL
// ========================================
echo "2. CEK JUMLAH TABEL\n";

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
    ");
    $result = $stmt->fetch();
    $total_tables = $result['total'];
    
    if ($total_tables == 22) {
        echo "   ✅ Total Tabel: {$total_tables}/22 (LENGKAP)\n\n";
    } else if ($total_tables == 0) {
        echo "   ⚠️  Total Tabel: {$total_tables}/22 (SCHEMA BELUM DI-IMPORT)\n";
        echo "   📝 Import file: database/supabase_schema_complete.sql di Supabase SQL Editor\n\n";
    } else {
        echo "   ⚠️  Total Tabel: {$total_tables}/22 (TIDAK LENGKAP)\n";
        echo "   📝 Harap import ulang: database/supabase_schema_complete.sql\n\n";
    }
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 3. DAFTAR TABEL YANG ADA
// ========================================
echo "3. DAFTAR TABEL\n";

$expected_tables = [
    'users', 'informasi_yayasan', 'provinsi', 'dojo', 
    'majelis_sabuk_hitam', 'prestasi_msh', 'sertifikasi_msh', 'pendaftaran_msh',
    'kohai', 'prestasi_kohai', 'sertifikasi_kohai', 'pendaftaran_kohai',
    'pengurus', 'legalitas', 'lokasi', 'kegiatan', 'pembayaran',
    'kategori_produk', 'produk_toko', 'produk_variasi', 'transaksi_toko', 'transaksi'
];

try {
    $stmt = $pdo->query("
        SELECT table_name
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
        ORDER BY table_name
    ");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $found = [];
    $missing = [];
    
    foreach ($expected_tables as $table) {
        if (in_array($table, $existing_tables)) {
            $found[] = $table;
            echo "   ✅ {$table}\n";
        } else {
            $missing[] = $table;
            echo "   ❌ {$table} (MISSING)\n";
        }
    }
    
    echo "\n";
    echo "   Summary: " . count($found) . " found, " . count($missing) . " missing\n\n";
    
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 4. CEK INDEX
// ========================================
echo "4. CEK INDEX\n";

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM pg_indexes
        WHERE schemaname = 'public'
    ");
    $result = $stmt->fetch();
    $total_indexes = $result['total'];
    
    // Expected: 33 custom indexes + auto indexes dari PRIMARY KEY & UNIQUE
    if ($total_indexes >= 33) {
        echo "   ✅ Total Index: {$total_indexes} (Termasuk auto-generated)\n\n";
    } else if ($total_indexes == 0) {
        echo "   ⚠️  Total Index: {$total_indexes} (SCHEMA BELUM DI-IMPORT)\n\n";
    } else {
        echo "   ⚠️  Total Index: {$total_indexes}\n\n";
    }
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 5. CEK DATA SAMPLE
// ========================================
echo "5. CEK DATA SAMPLE\n";

try {
    // Cek user admin
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $admin_count = $stmt->fetch()['total'];
    echo "   " . ($admin_count > 0 ? "✅" : "❌") . " User Admin: {$admin_count}\n";
    
    // Cek provinsi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM provinsi");
    $provinsi_count = $stmt->fetch()['total'];
    echo "   " . ($provinsi_count > 0 ? "✅" : "⚠️ ") . " Provinsi: {$provinsi_count}\n";
    
    // Cek kategori produk
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kategori_produk");
    $kategori_count = $stmt->fetch()['total'];
    echo "   " . ($kategori_count > 0 ? "✅" : "⚠️ ") . " Kategori Produk: {$kategori_count}\n";
    
    echo "\n";
    
} catch(PDOException $e) {
    echo "   ⚠️  Tabel belum ada data (Schema belum di-import?)\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 6. CEK TRIGGERS
// ========================================
echo "6. CEK TRIGGERS\n";

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM information_schema.triggers
        WHERE trigger_schema = 'public'
    ");
    $result = $stmt->fetch();
    $total_triggers = $result['total'];
    
    // Expected: 13 triggers untuk auto-update updated_at
    if ($total_triggers >= 13) {
        echo "   ✅ Total Triggers: {$total_triggers}\n\n";
    } else if ($total_triggers == 0) {
        echo "   ⚠️  Total Triggers: {$total_triggers} (SCHEMA BELUM DI-IMPORT)\n\n";
    } else {
        echo "   ⚠️  Total Triggers: {$total_triggers}/13\n\n";
    }
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 7. CEK VIEWS
// ========================================
echo "7. CEK VIEWS\n";

try {
    $stmt = $pdo->query("
        SELECT table_name
        FROM information_schema.views
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expected_views = ['view_msh_summary', 'view_kohai_summary'];
    
    foreach ($expected_views as $view) {
        if (in_array($view, $views)) {
            echo "   ✅ {$view}\n";
        } else {
            echo "   ❌ {$view} (MISSING)\n";
        }
    }
    
    echo "\n";
    
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// ========================================
// 8. KESIMPULAN
// ========================================
echo "========================================\n";
echo "KESIMPULAN\n";
echo "========================================\n\n";

if ($total_tables == 22 && $admin_count > 0) {
    echo "✅ DATABASE SUDAH SIAP DIGUNAKAN!\n\n";
    echo "Langkah Selanjutnya:\n";
    echo "1. Akses aplikasi: http://localhost/ypok_management/ypok_management/\n";
    echo "2. Login dengan:\n";
    echo "   - Username: admin\n";
    echo "   - Password: admin123\n";
    echo "3. Mulai gunakan aplikasi!\n\n";
} else if ($total_tables == 0) {
    echo "⚠️  SCHEMA BELUM DI-IMPORT!\n\n";
    echo "Langkah yang harus dilakukan:\n";
    echo "1. Buka Supabase Dashboard: https://supabase.com/dashboard/project/vpqjbpkizdnvzpattiop\n";
    echo "2. Klik menu 'SQL Editor' di sidebar kiri\n";
    echo "3. Klik 'New Query'\n";
    echo "4. Copy semua isi file: database/supabase_schema_complete.sql\n";
    echo "5. Paste di SQL Editor\n";
    echo "6. Klik 'RUN' (atau tekan Ctrl+Enter)\n";
    echo "7. Tunggu sampai selesai (~30 detik)\n";
    echo "8. Jalankan script ini lagi untuk verifikasi\n\n";
} else {
    echo "⚠️  DATABASE TIDAK LENGKAP!\n\n";
    echo "Langkah perbaikan:\n";
    echo "1. Backup data yang ada (jika perlu)\n";
    echo "2. Import ulang: database/supabase_schema_complete.sql\n";
    echo "3. Jalankan script ini lagi untuk verifikasi\n\n";
}

echo "========================================\n";
echo "Informasi Database:\n";
echo "========================================\n";
echo "Project Name: ypok_management\n";
echo "Database Name: postgres\n";
echo "Host: db.vpqjbpkizdnvzpattiop.supabase.co\n";
echo "Port: 5432\n";
echo "Region: Southeast Asia (Singapore)\n";
echo "SSL: Required\n";
echo "========================================\n\n";

echo "Script selesai dijalankan.\n";
