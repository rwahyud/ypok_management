<?php
/**
 * Public API endpoint untuk mendapatkan data Master Sabuk Hitam (MSH)
 * Digunakan oleh guest dashboard untuk pencarian MSH
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);

error_log("=== MSH Public API Called ===");
error_log("GET params: " . json_encode($_GET));

require_once __DIR__ . '/../config/database.php';

try {
    error_log("Database connection successful");
    
    // Get search parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    error_log("Search params - search: '$search', limit: $limit, offset: $offset");

    // Validate limit
    if ($limit < 1 || $limit > 100) {
        $limit = 12;
    }

    // Build WHERE clause for search
    $whereConditions = [];
    $params = [];

    if (!empty($search)) {
        $searchPattern = '%' . $search . '%';
        $whereConditions[] = "(
            nama LIKE ? OR
            no_msh LIKE ? OR
            tingkat_dan LIKE ? OR
            no_telp LIKE ? OR
            dojo_cabang LIKE ? OR
            alamat LIKE ? OR
            status LIKE ? OR
            email LIKE ? OR
            tempat_lahir LIKE ? OR
            nomor_ijazah LIKE ? OR
            DATE_FORMAT(tanggal_lahir, '%d/%m/%Y') LIKE ? OR
            DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?
        )";
        $params = array_fill(0, 12, $searchPattern);
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    error_log("WhereClause: $whereClause");
    error_log("Params: " . json_encode($params));

    // Get total count
    $countSql = "SELECT COUNT(*) as total 
                 FROM master_sabuk_hitam 
                 $whereClause";
    
    error_log("Count SQL: $countSql");
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    error_log("Total records found: $totalRecords");

    // Get MSH data
    $sql = "SELECT 
                id,
                no_msh as kode_msh,
                nama,
                tingkat_dan,
                foto,
                dojo_cabang
            FROM master_sabuk_hitam
            $whereClause
            ORDER BY nama ASC
            LIMIT ? OFFSET ?";
    
    error_log("Main SQL: $sql");

    $stmt = $pdo->prepare($sql);

    foreach ($params as $index => $value) {
        $stmt->bindValue($index + 1, $value, PDO::PARAM_STR);
    }

    // MariaDB membutuhkan LIMIT/OFFSET sebagai integer, bukan string ter-quote.
    $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

    error_log("Executing query with search params: " . json_encode($params) . ", limit: $limit, offset: $offset");

    $stmt->execute();
    $mshData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Fetched " . count($mshData) . " records");

    // Format foto URLs if they exist
    foreach ($mshData as &$msh) {
        if (!empty($msh['foto'])) {
            // Check if foto is already a full path
            if (strpos($msh['foto'], 'uploads/') === false) {
                $msh['foto'] = 'uploads/msh/' . $msh['foto'];
            }
            $msh['foto'] = ypok_public_asset_url($msh['foto']);
        } else {
            $msh['foto'] = null;
        }
    }

    
    echo json_encode([
        'success' => true,
        'data' => $mshData,
        'total' => $totalRecords,
        'limit' => $limit,
        'offset' => $offset,
        'hasMore' => ($offset + $limit) < $totalRecords
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => 'Terjadi kesalahan saat mengambil data',
        'debug' => $e->getMessage(),
        'sql' => $sql ?? 'N/A'
    ]);
    error_log("Get MSH Public Error: " . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'System error',
        'message' => 'Terjadi kesalahan sistem',
        'debug' => $e->getMessage()
    ]);
    error_log("Get MSH Public System Error: " . $e->getMessage());
}
