<?php
// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

require_once '../config/database.php';

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');

// Clear any output
ob_clean();

// Check authentication
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM provinsi WHERE id = ?");
    $stmt->execute([$id]);
    $provinsi = $stmt->fetch(PDO::FETCH_ASSOC);

    if($provinsi) {
        echo json_encode($provinsi);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

exit();
?>
