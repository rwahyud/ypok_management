<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get user info
$stmt = $conn->prepare("SELECT name, email, role, family_code FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get family members
$family_members = [];
if ($user['family_code']) {
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.role, 
               l.latitude, l.longitude, l.accuracy, l.battery_level, 
               l.sharing_enabled, l.updated_at
        FROM users u
        LEFT JOIN locations l ON u.id = l.user_id
        WHERE u.family_code = ?
        ORDER BY u.role DESC, u.name ASC
    ");
    $stmt->bind_param("s", $user['family_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $family_members[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Family Location Tracker</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="dashboard">
        <nav class="navbar">
            <div class="nav-content">
                <h2>🏠 Family Tracker</h2>
                <div class="nav-right">
                    <span>Halo, <?php echo htmlspecialchars($user['name']); ?>!</span>
                    <a href="debug.php" class="btn btn-small" style="background: #6c757d; margin-right: 10px;">🔍 Debug</a>
                    <a href="logout.php" class="btn btn-small">Logout</a>
                </div>
            </div>
        </nav>
        
        <div class="dashboard-container">
            <aside class="sidebar">
                <div class="user-info">
                    <h3>Informasi Anda</h3>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
                    <p><strong>Kode Keluarga:</strong> <br><span class="family-code"><?php echo htmlspecialchars($user['family_code']); ?></span></p>
                </div>
                
                <div class="location-sharing">
                    <h3>Berbagi Lokasi GPS Real-Time</h3>
                    <div class="toggle-container">
                        <label class="switch">
                            <input type="checkbox" id="sharing-toggle" checked>
                            <span class="slider"></span>
                        </label>
                        <span id="sharing-status">Aktif</span>
                    </div>
                    <p class="location-info">
                        <small id="location-timestamp">⏳ Menunggu GPS...</small>
                    </p>
                    <p class="location-accuracy">
                        <small id="location-accuracy">Akurasi: Menghubungkan...</small>
                    </p>
                    <button onclick="refreshMyLocation()" class="btn-refresh" style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; margin-top: 10px; font-weight: 600;">
                        🔄 Refresh Lokasi Saya
                    </button>
                </div>
                
                <div class="family-members">
                    <h3>Anggota Keluarga</h3>
                    <div id="members-list">
                        <?php if (empty($family_members)): ?>
                            <p class="no-members">Belum ada anggota keluarga lain</p>
                        <?php else: ?>
                            <?php foreach ($family_members as $member): ?>
                                <div class="member-item" data-user-id="<?php echo $member['id']; ?>">
                                    <div class="member-icon">
                                        <?php 
                                        $icon = $member['role'] === 'parent' ? '👨‍👩‍👧‍👦' : 
                                               ($member['role'] === 'child' ? '👶' : '👤');
                                        echo $icon;
                                        ?>
                                    </div>
                                    <div class="member-info">
                                        <strong><?php echo htmlspecialchars($member['name']); ?></strong>
                                        <?php 
                                        $hasLocation = !empty($member['latitude']) && !empty($member['longitude']);
                                        $isSharing = isset($member['sharing_enabled']) && $member['sharing_enabled'] == 1;
                                        ?>
                                        <?php if ($isSharing && $hasLocation): ?>
                                            <small class="location-time">
                                                <?php 
                                                $time = strtotime($member['updated_at']);
                                                $diff = time() - $time;
                                                if ($diff < 60) echo 'Baru saja';
                                                elseif ($diff < 3600) echo floor($diff / 60) . ' menit lalu';
                                                else echo floor($diff / 3600) . ' jam lalu';
                                                ?>
                                            </small>
                                        <?php elseif (!$isSharing): ?>
                                            <small class="location-disabled">Sharing dimatikan</small>
                                        <?php else: ?>
                                            <small class="location-disabled">Menunggu lokasi...</small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isSharing && $hasLocation): ?>
                                        <button class="btn-locate" onclick="focusOnMember(<?php echo $member['id']; ?>)">📍</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="instructions">
                    <h4>💡 GPS Real-Time Tips:</h4>
                    <ol>
                        <li>✅ <strong>Izinkan akses lokasi</strong> di browser</li>
                        <li>📡 Gunakan di <strong>luar ruangan</strong> untuk GPS akurat</li>
                        <li>🔄 Update otomatis setiap <strong>3 detik</strong></li>
                        <li>🗺️ Marker biru = Lokasi Anda (GPS Real)</li>
                        <li>📍 Klik marker untuk detail koordinat</li>
                    </ol>
                    <p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; font-size: 0.9em;">
                        <strong>⚠️ Perhatian:</strong><br>
                        Ini adalah GPS <strong>REAL-TIME</strong> dari perangkat Anda, bukan data dummy!
                    </p>
                    <a href="gps-guide.html" target="_blank" style="display: block; width: 100%; padding: 10px; background: #17a2b8; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 10px;">
                        📖 Panduan Lengkap GPS
                    </a>
                </div>
            </aside>
            
            <main class="main-content">
                <div class="map-container">
                    <div id="map"></div>
                    <button onclick="centerToMyLocation()" style="position: absolute; top: 10px; right: 10px; z-index: 1000; background: white; border: 2px solid #667eea; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        📍 Ke Lokasi Saya
                    </button>
                </div>
                
                <div class="stats">
                    <div class="stat-card">
                        <h4>Total Anggota</h4>
                        <p class="stat-number"><?php echo count($family_members); ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Lokasi Aktif</h4>
                        <p class="stat-number" id="active-locations">0</p>
                    </div>
                    <div class="stat-card">
                        <h4>Update Terakhir</h4>
                        <p class="stat-text" id="last-update">-</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const familyCode = '<?php echo $user['family_code']; ?>';
    </script>
</body>
</html>
