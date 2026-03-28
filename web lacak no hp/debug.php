<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's location
$stmt = $conn->prepare("SELECT * FROM locations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_location = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get all family members
$stmt = $conn->prepare("
    SELECT u.*, l.* 
    FROM users u
    LEFT JOIN locations l ON u.id = l.user_id
    WHERE u.family_code = ?
");
$stmt->bind_param("s", $user['family_code']);
$stmt->execute();
$all_members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Info</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #4ec9b0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #252526;
        }
        th, td {
            border: 1px solid #3e3e42;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2d2d30;
            color: #4ec9b0;
        }
        .section {
            background: #252526;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #3e3e42;
        }
        .back-btn {
            display: inline-block;
            background: #0e639c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background: #1177bb;
        }
        .status-active {
            color: #4ec9b0;
        }
        .status-inactive {
            color: #f48771;
        }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>
    
    <h1>🔍 Debug Information</h1>
    <p>Halaman ini menampilkan data mentah dari database untuk debugging.</p>
    
    <div class="section">
        <h2>👤 Informasi Anda</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>User ID</td>
                <td><?php echo $user['id']; ?></td>
            </tr>
            <tr>
                <td>Name</td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
            <tr>
                <td>Role</td>
                <td><?php echo $user['role']; ?></td>
            </tr>
            <tr>
                <td>Family Code</td>
                <td><?php echo $user['family_code']; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>📍 Lokasi Anda di Database</h2>
        <?php if ($my_location): ?>
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Location ID</td>
                    <td><?php echo $my_location['id']; ?></td>
                </tr>
                <tr>
                    <td>Latitude</td>
                    <td><?php echo $my_location['latitude']; ?></td>
                </tr>
                <tr>
                    <td>Longitude</td>
                    <td><?php echo $my_location['longitude']; ?></td>
                </tr>
                <tr>
                    <td>Accuracy</td>
                    <td><?php echo $my_location['accuracy']; ?> meter</td>
                </tr>
                <tr>
                    <td>Battery Level</td>
                    <td><?php echo $my_location['battery_level'] ?? 'null'; ?>%</td>
                </tr>
                <tr>
                    <td>Sharing Enabled</td>
                    <td class="<?php echo $my_location['sharing_enabled'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $my_location['sharing_enabled'] ? '✓ Aktif (1)' : '✗ Nonaktif (0)'; ?>
                    </td>
                </tr>
                <tr>
                    <td>Updated At</td>
                    <td><?php echo $my_location['updated_at']; ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p class="status-inactive">⚠️ Belum ada data lokasi di database</p>
            <p>Pastikan:</p>
            <ul>
                <li>Browser memiliki izin akses lokasi</li>
                <li>Toggle "Berbagi Lokasi" aktif</li>
                <li>Tunggu beberapa detik untuk GPS mendapatkan lokasi</li>
                <li>Cek Console browser (F12) untuk error</li>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>👨‍👩‍👧‍👦 Semua Anggota Keluarga</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Akurasi (m)</th>
                    <th>Sharing</th>
                    <th>Update Terakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_members as $member): ?>
                    <tr style="<?php echo $member['id'] == $user_id ? 'background: #2d4f3e;' : ''; ?>">
                        <td><?php echo $member['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($member['name']); ?>
                            <?php if ($member['id'] == $user_id): ?>
                                <strong>(Anda)</strong>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $member['role']; ?></td>
                        <td><?php echo $member['latitude'] ?? '-'; ?></td>
                        <td><?php echo $member['longitude'] ?? '-'; ?></td>
                        <td><?php echo $member['accuracy'] ? round($member['accuracy']) : '-'; ?></td>
                        <td class="<?php echo isset($member['sharing_enabled']) && $member['sharing_enabled'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php 
                            if (!isset($member['sharing_enabled'])) {
                                echo '- (Belum ada data)';
                            } elseif ($member['sharing_enabled']) {
                                echo '✓ Aktif';
                            } else {
                                echo '✗ Nonaktif';
                            }
                            ?>
                        </td>
                        <td><?php echo $member['updated_at'] ?? '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>💡 Tips Troubleshooting</h2>
        <ul>
            <li><strong>Jika lokasi tidak muncul:</strong>
                <ul>
                    <li>Pastikan browser memiliki izin akses lokasi (klik ikon gembok di address bar)</li>
                    <li>Aktifkan GPS/Location Services di perangkat</li>
                    <li>Refresh halaman dan izinkan akses lokasi</li>
                    <li>Cek Console browser (tekan F12) untuk melihat error</li>
                </ul>
            </li>
            <li><strong>Jika sharing_enabled = 0:</strong>
                <ul>
                    <li>Periksa toggle "Berbagi Lokasi" di dashboard</li>
                    <li>Pastikan toggle dalam keadaan aktif (hijau)</li>
                </ul>
            </li>
            <li><strong>Jika akurasi rendah (>100m):</strong>
                <ul>
                    <li>Gunakan di luar ruangan atau dekat jendela</li>
                    <li>Tunggu beberapa saat hingga GPS lock lebih baik</li>
                </ul>
            </li>
        </ul>
    </div>
    
    <div class="section">
        <h2>🔄 Auto-Refresh</h2>
        <p>Halaman akan refresh otomatis setiap 10 detik untuk melihat update terbaru.</p>
        <button onclick="location.reload()" style="background: #0e639c; color: white; border: none; padding: 10px 20px; border-radius: 3px; cursor: pointer;">
            Refresh Sekarang
        </button>
    </div>
    
    <script>
        // Auto refresh every 10 seconds
        setTimeout(function() {
            location.reload();
        }, 10000);
        
        console.log('Debug page loaded. Auto-refresh in 10 seconds.');
    </script>
</body>
</html>
