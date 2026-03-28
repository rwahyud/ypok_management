<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();

// Get user's family code
$stmt = $conn->prepare("SELECT family_code FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user['family_code']) {
    echo json_encode(['success' => false, 'message' => 'Anda belum tergabung dalam keluarga']);
    exit;
}

// Get all family members' locations
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.role, 
           l.latitude, l.longitude, l.accuracy, l.battery_level, 
           l.sharing_enabled, l.updated_at
    FROM users u
    LEFT JOIN locations l ON u.id = l.user_id
    WHERE u.family_code = ?
");
$stmt->bind_param("s", $user['family_code']);
$stmt->execute();
$result = $stmt->get_result();

$locations = [];
while ($row = $result->fetch_assoc()) {
    // Hanya tampilkan jika sharing enabled dan ada koordinat
    if ($row['sharing_enabled'] && $row['latitude'] && $row['longitude']) {
        $locations[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'role' => $row['role'],
            'latitude' => floatval($row['latitude']),
            'longitude' => floatval($row['longitude']),
            'accuracy' => floatval($row['accuracy']),
            'battery_level' => intval($row['battery_level']),
            'updated_at' => $row['updated_at'],
            'is_me' => ($row['id'] == $_SESSION['user_id'])
        ];
    }
}

echo json_encode([
    'success' => true,
    'locations' => $locations,
    'timestamp' => date('Y-m-d H:i:s')
]);

$stmt->close();
$conn->close();
?>
