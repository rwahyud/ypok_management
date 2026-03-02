<?php
/**
 * Public API untuk mendapatkan data MSH
 * Tidak memerlukan login - untuk guest dashboard
 */
require_once '../config/supabase.php';

header('Content-Type: application/json');

try {
    // Get search parameter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Validate and sanitize pagination parameters
    $limit = max(1, min($limit, 100)); // Between 1 and 100
    $offset = max(0, $offset);
    
    // Check if created_at column exists
    $columns = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'majelis_sabuk_hitam'")->fetchAll(PDO::FETCH_COLUMN);
    $hasCreatedAt = in_array('created_at', $columns);
    $orderBy = $hasCreatedAt ? 'created_at' : 'id';
    
    // Build query - conditional tahun_bergabung
    $tahunBergabungCol = $hasCreatedAt ? 'EXTRACT(YEAR FROM created_at) as tahun_bergabung' : 'NULL as tahun_bergabung';
    
    $sql = "SELECT 
                id,
                kode_msh,
                nama,
                tempat_lahir,
                tanggal_lahir,
                jenis_kelamin,
                tingkat_dan,
                dojo_cabang,
                foto,
                no_telp,
                email,
                alamat,
                nomor_ijazah,
                {$tahunBergabungCol},
                status
            FROM majelis_sabuk_hitam 
            WHERE status = 'aktif'";
    
    $params = [];
    
    // Add search condition
    if (!empty($search)) {
        $sql .= " AND (
            nama ILIKE ? OR 
            kode_msh ILIKE ? OR 
            dojo_cabang ILIKE ? OR 
            tingkat_dan ILIKE ? OR
            alamat ILIKE ? OR
            tempat_lahir ILIKE ?
        )";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM majelis_sabuk_hitam WHERE status = 'aktif'";
    if (!empty($search)) {
        $countSql .= " AND (
            nama ILIKE ? OR 
            kode_msh ILIKE ? OR 
            dojo_cabang ILIKE ? OR 
            tingkat_dan ILIKE ? OR
            alamat ILIKE ? OR
            tempat_lahir ILIKE ?
        )";
    }
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Add order and pagination - use integer values directly (already validated as int)
    $sql .= " ORDER BY {$orderBy} DESC LIMIT {$limit} OFFSET {$offset}";
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data - ensure foto path is correct
    $data = array_map(function($row) {
        // Ensure foto path is accessible
        if (!empty($row['foto'])) {
            // If foto starts with uploads/, it's correct
            // If not, it might need fixing
            if (strpos($row['foto'], 'uploads/') !== 0 && strpos($row['foto'], 'http') !== 0) {
                // Fix path if needed
                $row['foto'] = 'uploads/msh/' . basename($row['foto']);
            }
        }
        return $row;
    }, $rawData);
    
    // Send response
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => (int)$total,
        'limit' => $limit,
        'offset' => $offset,
        'search' => $search
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>
