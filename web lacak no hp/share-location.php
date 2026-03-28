<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;
$accuracy = $data['accuracy'] ?? null;
$battery_level = $data['battery_level'] ?? null;
$sharing_enabled = isset($data['sharing_enabled']) ? (int)$data['sharing_enabled'] : 1;

if ($latitude === null || $longitude === null) {
    echo json_encode(['success' => false, 'message' => 'Latitude dan longitude diperlukan']);
    exit;
}

$conn = getDBConnection();

// Cek apakah user sudah punya record lokasi
$stmt = $conn->prepare("SELECT id FROM locations WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing location
    $stmt = $conn->prepare("
        UPDATE locations 
        SET latitude = ?, longitude = ?, accuracy = ?, battery_level = ?, 
            sharing_enabled = ?, updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->bind_param("dddiii", $latitude, $longitude, $accuracy, $battery_level, $sharing_enabled, $_SESSION['user_id']);
} else {
    // Insert new location
    $stmt = $conn->prepare("
        INSERT INTO locations (user_id, latitude, longitude, accuracy, battery_level, sharing_enabled)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddii", $_SESSION['user_id'], $latitude, $longitude, $accuracy, $battery_level, $sharing_enabled);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Lokasi berhasil diupdate',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan lokasi']);
}

$stmt->close();
$conn->close();
?>
