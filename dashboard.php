<?php
// =============================================
// SESSION CHECK - HARUS PALING ATAS!
// =============================================
require_once 'config/supabase.php';

// Redirect jika belum login
if(!isset($_SESSION['user_id'])) {
    header('Location: /ypok_management/ypok_management/index.php');
    exit();
}

// =============================================
// STATISTIK UTAMA
// =============================================

try {
    $total_msh       = $pdo->query("SELECT COUNT(*) FROM majelis_sabuk_hitam WHERE status='aktif'")->fetchColumn();
    $total_kohai     = $pdo->query("SELECT COUNT(*) FROM kohai WHERE status='aktif'")->fetchColumn();
    $total_lokasi    = $pdo->query("SELECT COUNT(*) FROM lokasi WHERE status='aktif'")->fetchColumn();
    $total_pendapatan_bulan = $pdo->query("
        SELECT
            COALESCE((SELECT SUM(jumlah) FROM pembayaran WHERE status='lunas' AND EXTRACT(MONTH FROM tanggal_bayar)=EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM tanggal_bayar)=EXTRACT(YEAR FROM CURRENT_DATE)), 0)
            + COALESCE((SELECT SUM(total_harga) FROM transaksi_toko WHERE EXTRACT(MONTH FROM tanggal)=EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM tanggal)=EXTRACT(YEAR FROM CURRENT_DATE)), 0)
            AS total
    ")->fetchColumn();

    $saldo_row = $pdo->query("SELECT COALESCE(SUM(CASE WHEN jenis='pemasukan' THEN jumlah ELSE 0 END),0) - COALESCE(SUM(CASE WHEN jenis='pengeluaran' THEN jumlah ELSE 0 END),0) as saldo FROM transaksi")->fetch();
    $saldo_keuangan = $saldo_row['saldo'] ?? 0;

    $total_kegiatan  = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status='akan_datang'")->fetchColumn();
    $total_legalitas = $pdo->query("SELECT COUNT(*) FROM legalitas WHERE status='aktif'")->fetchColumn();
    $total_pending   = $pdo->query("SELECT COUNT(*) FROM (SELECT id FROM pendaftaran_msh WHERE status='Pending' UNION SELECT id FROM pendaftaran_kohai WHERE status='Pending') AS pending_count")->fetchColumn();

// =============================================
// DATA CHART: KEUANGAN 6 BULAN TERAKHIR
// =============================================
$keuangan_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan_label = date('M Y', strtotime("-$i months"));
    $bulan_param = date('Y-m', strtotime("-$i months"));
    // Filter tanggal di dalam setiap subquery (lebih eksplisit & efisien)
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN jenis='pemasukan'   THEN jumlah ELSE 0 END), 0) AS pemasukan,
            COALESCE(SUM(CASE WHEN jenis='pengeluaran' THEN jumlah ELSE 0 END), 0) AS pengeluaran
        FROM (
            SELECT jenis, jumlah
                FROM transaksi
                WHERE TO_CHAR(tanggal,'YYYY-MM') = ?
            UNION ALL
            SELECT 'pemasukan', jumlah
                FROM pembayaran
                WHERE status='lunas' AND TO_CHAR(tanggal_bayar,'YYYY-MM') = ?
            UNION ALL
            SELECT 'pemasukan', total_harga
                FROM transaksi_toko
                WHERE TO_CHAR(tanggal,'YYYY-MM') = ?
        ) AS gabungan
    ");
    $stmt->execute([$bulan_param, $bulan_param, $bulan_param]);
    $row = $stmt->fetch();
    $keuangan_bulan[] = [
        'label'       => $bulan_label,
        'pemasukan'   => (float)$row['pemasukan'],
        'pengeluaran' => (float)$row['pengeluaran'],
    ];
}

// =============================================
// DATA CHART: TOTAL PRESTASI MSH & KOHAI
// =============================================
$total_prestasi_msh   = (int)$pdo->query("SELECT COUNT(*) FROM prestasi_msh")->fetchColumn();
$total_prestasi_kohai = (int)$pdo->query("SELECT COUNT(*) FROM prestasi_kohai")->fetchColumn();

// =============================================
// DATA CHART: PRESTASI PER BULAN (6 BULAN)
// =============================================
$prestasi_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan_label = date('M Y', strtotime("-$i months"));
    $bulan_param = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM prestasi_msh   WHERE TO_CHAR(created_at,'YYYY-MM') = ?) as msh,
        (SELECT COUNT(*) FROM prestasi_kohai WHERE TO_CHAR(created_at,'YYYY-MM') = ?) as kohai");
    $stmt->execute([$bulan_param, $bulan_param]);
    $row = $stmt->fetch();
    $prestasi_bulan[] = [
        'label' => $bulan_label,
        'msh'   => (int)$row['msh'],
        'kohai' => (int)$row['kohai'],
    ];
}

// =============================================
// DATA CHART: STATUS LEGALITAS
// =============================================
$legalitas_status_raw = $pdo->query("SELECT status, COUNT(*) as jumlah FROM legalitas GROUP BY status")->fetchAll();

// =============================================
// DATA CHART: PEMBAYARAN PER KATEGORI
// =============================================
$pembayaran_kategori_raw = $pdo->query("SELECT jenis_pembayaran, COUNT(*) as jumlah, COALESCE(SUM(jumlah),0) as total FROM pembayaran WHERE jenis_pembayaran IS NOT NULL AND jenis_pembayaran != '' GROUP BY jenis_pembayaran ORDER BY total DESC LIMIT 8")->fetchAll();

// =============================================
// DATA CHART: GENDER MSH & KOHAI
// =============================================
$gender_msh = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM majelis_sabuk_hitam WHERE status='aktif' GROUP BY jenis_kelamin")->fetchAll();
$gender_kohai = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM kohai WHERE status='aktif' GROUP BY jenis_kelamin")->fetchAll();

// =============================================
// DATA CHART: PENDAFTARAN 6 BULAN
// =============================================
$pendaftaran_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan_label = date('M Y', strtotime("-$i months"));
    $bulan_param = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM pendaftaran_msh WHERE TO_CHAR(created_at,'YYYY-MM') = ?) as msh,
        (SELECT COUNT(*) FROM pendaftaran_kohai WHERE TO_CHAR(created_at,'YYYY-MM') = ?) as kohai");
    $stmt->execute([$bulan_param, $bulan_param]);
    $row = $stmt->fetch();
    $pendaftaran_bulan[] = [
        'label' => $bulan_label,
        'msh'   => (int)$row['msh'],
        'kohai' => (int)$row['kohai'],
    ];
}

// =============================================
// RECENT ACTIVITY: 5 TRANSAKSI TERBARU
// =============================================
$recent_transaksi = $pdo->query("SELECT * FROM transaksi ORDER BY created_at DESC LIMIT 5")->fetchAll();

// =============================================
// UPCOMING KEGIATAN
// =============================================
$upcoming_kegiatan = $pdo->query("SELECT k.*, l.nama_lokasi FROM kegiatan k LEFT JOIN lokasi l ON k.lokasi_id = l.id WHERE k.status='dijadwalkan' ORDER BY k.tanggal_kegiatan ASC LIMIT 5")->fetchAll();

// =============================================
// BERITA AKTIF (Kegiatan yang ditampilkan sebagai berita)
// =============================================
$berita_aktif = $pdo->query("SELECT k.*, l.nama_lokasi FROM kegiatan k LEFT JOIN lokasi l ON k.lokasi_id = l.id WHERE k.tampil_di_berita = true ORDER BY k.tanggal_kegiatan DESC LIMIT 6")->fetchAll();

// =============================================
// ENCODE JSON untuk Chart.js
// =============================================
$chart_keuangan_labels     = json_encode(array_column($keuangan_bulan, 'label'));
$chart_keuangan_pemasukan  = json_encode(array_column($keuangan_bulan, 'pemasukan'));
$chart_keuangan_pengeluaran= json_encode(array_column($keuangan_bulan, 'pengeluaran'));

$chart_prestasi_labels = json_encode(array_column($prestasi_bulan, 'label'));
$chart_prestasi_msh    = json_encode(array_column($prestasi_bulan, 'msh'));
$chart_prestasi_kohai  = json_encode(array_column($prestasi_bulan, 'kohai'));

$chart_prestasi_pie_labels = json_encode(['MSH', 'Kohai']);
$chart_prestasi_pie_data   = json_encode([$total_prestasi_msh, $total_prestasi_kohai]);

$chart_legalitas_labels = json_encode(array_column($legalitas_status_raw, 'status'));
$chart_legalitas_data   = json_encode(array_map('intval', array_column($legalitas_status_raw, 'jumlah')));

$chart_pembayaran_labels = json_encode(array_column($pembayaran_kategori_raw, 'kategori'));
$chart_pembayaran_data   = json_encode(array_map('floatval', array_column($pembayaran_kategori_raw, 'total')));

$chart_pendaftaran_labels = json_encode(array_column($pendaftaran_bulan, 'label'));
$chart_pendaftaran_msh    = json_encode(array_column($pendaftaran_bulan, 'msh'));
$chart_pendaftaran_kohai  = json_encode(array_column($pendaftaran_bulan, 'kohai'));

// Gender data
$laki_msh = 0; $perempuan_msh = 0;
foreach ($gender_msh as $g) {
    if ($g['jenis_kelamin'] === 'L') $laki_msh = (int)$g['jumlah'];
    else $perempuan_msh = (int)$g['jumlah'];
}
$laki_kohai = 0; $perempuan_kohai = 0;
foreach ($gender_kohai as $g) {
    if ($g['jenis_kelamin'] === 'L') $laki_kohai = (int)$g['jumlah'];
    else $perempuan_kohai = (int)$g['jumlah'];
}

} catch(PDOException $e) {
    // Log error untuk debugging
    error_log("Dashboard Error: " . $e->getMessage());
    
    // Redirect ke login dengan error message
    header('Location: ' . BASE_PATH . '/index.php?error=db_dashboard');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e3a8a">
    <title>Dashboard - YPOK Management</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/jpeg" href="assets/icons/icon-192x192.jpg">
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ===== DASHBOARD STYLES ===== */
        * {
            box-sizing: border-box;
        }
        
        .dashboard-container {
            padding: 28px 32px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* --- Welcome Banner --- */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 12px;
            padding: 28px 32px;
            margin-bottom: 28px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(30,58,138,0.2);
            position: relative;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }

        .welcome-banner::after {
            content: '🥋';
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 80px;
            opacity: 0.18;
        }

        .welcome-banner h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px;
            line-height: 1.2;
        }

        .welcome-banner p {
            font-size: 15px;
            opacity: 0.9;
            margin: 0;
            line-height: 1.4;
        }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
            width: 100%;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid transparent;
            width: 100%;
            box-sizing: border-box;
            min-height: 120px;
        }

        .stat-card.blue   { border-left-color: #3b82f6; }
        .stat-card.green  { border-left-color: #10b981; }
        .stat-card.orange { border-left-color: #f59e0b; }
        .stat-card.emerald{ border-left-color: #059669; }
        .stat-card.purple { border-left-color: #8b5cf6; }
        .stat-card.teal   { border-left-color: #14b8a6; }
        .stat-card.red    { border-left-color: #ef4444; }
        .stat-card.indigo { border-left-color: #6366f1; }
        .stat-card.yellow { border-left-color: #eab308; }
        .stat-card.amber  { border-left-color: #d97706; }

        .stat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }

        .stat-card.blue   .stat-icon-box { background: #dbeafe; }
        .stat-card.green  .stat-icon-box { background: #d1fae5; }
        .stat-card.orange .stat-icon-box { background: #fef3c7; }
        .stat-card.emerald .stat-icon-box{ background: #d1fae5; }
        .stat-card.purple .stat-icon-box { background: #ede9fe; }
        .stat-card.teal   .stat-icon-box { background: #ccfbf1; }
        .stat-card.red    .stat-icon-box { background: #fee2e2; }
        .stat-card.indigo .stat-icon-box { background: #e0e7ff; }
        .stat-card.yellow .stat-icon-box { background: #fef9c3; }
        .stat-card.amber  .stat-icon-box { background: #fde68a; }

        .stat-info { flex: 1; }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .stat-value.small-text {
            font-size: 18px;
        }

        .stat-sub {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* --- Charts Grid --- */
        .charts-row {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .charts-row.col-2  { grid-template-columns: 1fr 1fr; }
        .charts-row.col-32 { grid-template-columns: 3fr 2fr; }
        .charts-row.col-23 { grid-template-columns: 2fr 3fr; }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            width: 100%;
            box-sizing: border-box;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-subtitle {
            font-size: 12px;
            color: #9ca3af;
        }

        .chart-body {
            position: relative;
        }

        .chart-body canvas {
            max-height: 280px;
        }

        .chart-body.small canvas {
            max-height: 220px;
        }

        /* --- Recent Activity --- */
        .activity-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .activity-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            width: 100%;
            box-sizing: border-box;
        }

        .activity-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .activity-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .activity-link {
            font-size: 12px;
            color: #3b82f6;
            text-decoration: none;
            pointer-events: none;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .mini-table th {
            background: #f8fafc;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .mini-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #374151;
            vertical-align: middle;
        }

        .mini-table tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-pemasukan  { background: #d1fae5; color: #059669; }
        .badge-pengeluaran{ background: #fee2e2; color: #dc2626; }
        .badge-akan_datang{ background: #dbeafe; color: #2563eb; }
        .badge-terlaksana { background: #d1fae5; color: #059669; }
        .badge-dibatalkan { background: #fee2e2; color: #dc2626; }
        .badge-aktif      { background: #d1fae5; color: #059669; }
        .badge-kadaluarsa { background: #fee2e2; color: #dc2626; }
        .badge-proses     { background: #fef3c7; color: #d97706; }
        .badge-pending    { background: #fef3c7; color: #d97706; }

        .empty-state {
            text-align: center;
            padding: 32px 20px;
            color: #9ca3af;
        }

        .empty-state .empty-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }

        /* ── Responsive ───────────────────────────────── */

        /* Tablet (≤1200px) */
        @media (max-width: 1200px) {
            .dashboard-container { padding: 20px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .charts-row.col-32,
            .charts-row.col-23,
            .charts-row.col-2 { grid-template-columns: 1fr; }
            .activity-row { grid-template-columns: 1fr; }
            .welcome-banner { padding: 24px; }
            .welcome-banner h2 { font-size: 20px; }
        }

        /* Mobile (≤768px) */
        @media (max-width: 768px) {
            .dashboard-container { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr; gap: 14px; margin-bottom: 18px; }
            .stat-card { padding: 20px; gap: 14px; min-height: 100px; }
            .stat-icon-box { width: 48px; height: 48px; font-size: 24px; }
            .stat-value { font-size: 22px; }
            .stat-value.small-text { font-size: 14px; }
            .stat-label { font-size: 12px; }
            .welcome-banner {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .welcome-banner h2 { font-size: 18px; }
            .welcome-banner p { font-size: 13px; }
            .welcome-banner::after { font-size: 60px; right: 20px; }
            .charts-row { gap: 16px; margin-bottom: 16px; }
            .chart-card,
            .activity-card { padding: 20px; }
            .chart-title { font-size: 14px; }
            .chart-subtitle { font-size: 12px; }
            .chart-body canvas { max-height: 240px; }
            .chart-body.small canvas { max-height: 200px; }
            .activity-row { gap: 16px; margin-bottom: 16px; }
            .mini-table { font-size: 13px; }
            .mini-table th { padding: 8px 10px; font-size: 11px; }
            .mini-table td { padding: 10px; }
        }

        /* Small Mobile (≤480px) */
        @media (max-width: 480px) {
            .dashboard-container { padding: 12px; }
            .stats-grid { grid-template-columns: 1fr; gap: 12px; }
            .stat-card { padding: 18px; gap: 12px; min-height: 90px; }
            .stat-icon-box { width: 44px; height: 44px; font-size: 22px; border-radius: 10px; }
            .stat-value { font-size: 20px; }
            .stat-value.small-text { font-size: 13px; }
            .welcome-banner { padding: 16px; }
            .welcome-banner h2 { font-size: 16px; }
            .welcome-banner::after { display: none; }
            .chart-card,
            .activity-card { padding: 16px; }
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                margin-bottom: 14px;
            }
        }

        /* ── Chart Filter Bar ──────────────────────────────── */
        .chart-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .filter-label {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .chart-filter-controls {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-group-label {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
        }
        .filter-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            color: #1e293b;
            background: #f8fafc;
            cursor: pointer;
            outline: none;
        }
        .filter-btn-group {
            display: flex;
            gap: 4px;
        }
        .filter-btn {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
            font-weight: 600;
        }
        .filter-status { display: flex; align-items: center; gap: 6px; }
        .filter-status-text { font-size: 12px; color: #94a3b8; font-style: italic; }
        .filter-spinner {
            font-size: 16px;
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Dashboard</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>
        </div>

        <div class="dashboard-container">

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div>
                    <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h2>
                    <p>Berikut adalah ringkasan data dan analisis sistem YPOK Management — <?php echo date('l, d F Y'); ?></p>
                </div>
            </div>

            <!-- Stats Row 1 -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon-box">🥋</div>
                    <div class="stat-info">
                        <div class="stat-label">Total MSH Aktif</div>
                        <div class="stat-value"><?php echo number_format($total_msh); ?></div>
                        <div class="stat-sub">Majelis Sabuk Hitam</div>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon-box">👥</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Kohai Aktif</div>
                        <div class="stat-value"><?php echo number_format($total_kohai); ?></div>
                        <div class="stat-sub">Siswa/Peserta aktif</div>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon-box">📍</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Lokasi</div>
                        <div class="stat-value"><?php echo number_format($total_lokasi); ?></div>
                        <div class="stat-sub">Dojo/Cabang aktif</div>
                    </div>
                </div>
            </div>

            <!-- Stats Row 2 -->
            <div class="stats-grid">
                <div class="stat-card emerald">
                    <div class="stat-icon-box">💰</div>
                    <div class="stat-info">
                        <div class="stat-label">Pendapatan Bulan Ini</div>
                        <div class="stat-value small-text">Rp <?php echo number_format($total_pendapatan_bulan, 0, ',', '.'); ?></div>
                        <div class="stat-sub">Pembayaran lunas</div>
                    </div>
                </div>

                <div class="stat-card indigo">
                    <div class="stat-icon-box">💵</div>
                    <div class="stat-info">
                        <div class="stat-label">Saldo Keuangan</div>
                        <div class="stat-value small-text">Rp <?php echo number_format($saldo_keuangan, 0, ',', '.'); ?></div>
                        <div class="stat-sub">Total saldo bersih</div>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon-box">📅</div>
                    <div class="stat-info">
                        <div class="stat-label">Kegiatan Akan Datang</div>
                        <div class="stat-value"><?php echo number_format($total_kegiatan); ?></div>
                        <div class="stat-sub">Jadwal kegiatan</div>
                    </div>
                </div>
            </div>

            <!-- Stats Row 3 -->
            <div class="stats-grid">
                <div class="stat-card teal">
                    <div class="stat-icon-box">📄</div>
                    <div class="stat-info">
                        <div class="stat-label">Legalitas Aktif</div>
                        <div class="stat-value"><?php echo number_format($total_legalitas); ?></div>
                        <div class="stat-sub">Dokumen legal valid</div>
                    </div>
                </div>
            </div>

            <!-- Chart Row 1: Keuangan & Pendaftaran -->
            <div class="charts-row col-2">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">📊 Arus Keuangan 6 Bulan Terakhir</div>
                        <span class="chart-subtitle">Pemasukan vs Pengeluaran</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartKeuangan"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">📋 Pendaftaran 6 Bulan Terakhir</div>
                        <span class="chart-subtitle">MSH vs Kohai</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartPendaftaran"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Row 2: Pembayaran Kategori + Legalitas -->
            <div class="charts-row col-23">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">💳 Status Legalitas</div>
                        <span class="chart-subtitle">Dokumen hukum organisasi</span>
                    </div>
                    <div class="chart-body small">
                        <canvas id="chartLegalitas"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">💰 Total Pembayaran per Kategori</div>
                        <span class="chart-subtitle">Nominal terbayar (Rp)</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartPembayaran"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Row 3: Gender MSH + Gender Kohai -->
            <div class="charts-row col-2">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">⚧ Gender MSH Aktif</div>
                        <span class="chart-subtitle">Laki-laki vs Perempuan</span>
                    </div>
                    <div class="chart-body small">
                        <canvas id="chartGenderMSH"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">⚧ Gender Kohai Aktif</div>
                        <span class="chart-subtitle">Laki-laki vs Perempuan</span>
                    </div>
                    <div class="chart-body small">
                        <canvas id="chartGenderKohai"></canvas>
                    </div>
                </div>
            </div>

            <!-- Berita Aktif Section -->
            <div class="activity-row" style="grid-template-columns: 1fr; margin-bottom: 20px;">
                <div class="activity-card">
                    <div class="activity-header">
                        <div class="activity-title">📰 Berita Aktif di Guest Dashboard</div>
                        <a href="laporan_kegiatan.php" class="activity-link" style="pointer-events: auto; color: #3b82f6; text-decoration: none; font-weight: 600;">Kelola Berita →</a>
                    </div>
                    <?php if (count($berita_aktif) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 16px;">
                        <?php foreach ($berita_aktif as $b): ?>
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 10px; padding: 16px; border: 2px solid #bae6fd;">
                            <?php if (!empty($b['foto'])): ?>
                            <div style="width: 100%; height: 140px; border-radius: 8px; overflow: hidden; margin-bottom: 12px; background: #d1d5db;">
                                <img src="<?php echo htmlspecialchars($b['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($b['nama_kegiatan']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:#6b7280;font-size:12px;\'>📷 No Image</div>'">
                            </div>
                            <?php endif; ?>
                            <div style="font-size: 11px; color: #2563eb; font-weight: 600; margin-bottom: 6px;">
                                <?php echo htmlspecialchars($b['jenis_kegiatan']); ?> · <?php echo date('d M Y', strtotime($b['tanggal_kegiatan'])); ?>
                            </div>
                            <div style="font-size: 14px; font-weight: 600; color: #1e3a8a; margin-bottom: 8px; line-height: 1.4;">
                                <?php echo htmlspecialchars(mb_strimwidth($b['nama_kegiatan'], 0, 60, '...')); ?>
                            </div>
                            <div style="font-size: 12px; color: #64748b; line-height: 1.5;">
                                📍 <?php echo htmlspecialchars($b['nama_lokasi'] ?? 'Lokasi TBA'); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📰</div>
                        <div>Belum ada berita aktif</div>
                        <div style="font-size: 12px; color: #9ca3af; margin-top: 8px;">Aktifkan kegiatan sebagai berita di halaman Kelola Berita</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-row">
                <!-- Transaksi Terbaru -->
                <div class="activity-card">
                    <div class="activity-header">
                        <div class="activity-title">💸 Transaksi Terbaru</div>
                        <a href="laporan_keuangan.php" class="activity-link">Lihat Semua →</a>
                    </div>
                    <?php if (count($recent_transaksi) > 0): ?>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transaksi as $t): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($t['keterangan'], 0, 30, '...')); ?></td>
                                <td><span class="badge badge-<?php echo $t['jenis']; ?>"><?php echo ucfirst($t['jenis']); ?></span></td>
                                <td style="font-weight:600;color:<?php echo $t['jenis']==='pemasukan'?'#059669':'#dc2626'; ?>">
                                    Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div>Belum ada transaksi</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Kegiatan -->
                <div class="activity-card">
                    <div class="activity-header">
                        <div class="activity-title">📅 Kegiatan Akan Datang</div>
                        <a href="laporan_kegiatan.php" class="activity-link">Lihat Semua →</a>
                    </div>
                    <?php if (count($upcoming_kegiatan) > 0): ?>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Kegiatan</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_kegiatan as $k): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($k['tanggal_kegiatan'])); ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($k['nama_kegiatan'], 0, 28, '...')); ?></td>
                                <td><?php echo htmlspecialchars($k['nama_lokasi'] ?? '-'); ?></td>
                                <td><span class="badge badge-<?php echo $k['status']; ?>"><?php echo str_replace('_', ' ', ucfirst($k['status'])); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div>Tidak ada kegiatan mendatang</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /dashboard-container -->
    </div><!-- /main-content -->

    <script src="assets/js/app.js"></script>
    <script>
    // =============================================
    // CHART.JS CONFIGURATIONS
    // =============================================

    const COLORS = {
        blue:   '#3b82f6',
        green:  '#10b981',
        red:    '#ef4444',
        orange: '#f59e0b',
        purple: '#8b5cf6',
        teal:   '#14b8a6',
        indigo: '#6366f1',
        pink:   '#ec4899',
        yellow: '#eab308',
        cyan:   '#06b6d4',
    };

    const PALETTE = Object.values(COLORS);

    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.plugins.legend.labels.boxWidth = 14;
    Chart.defaults.plugins.legend.labels.padding = 16;

    // ---- 1. Arus Keuangan ----
    const chartKeuangan = new Chart(document.getElementById('chartKeuangan'), {
        type: 'bar',
        data: {
            labels: <?php echo $chart_keuangan_labels; ?>,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?php echo $chart_keuangan_pemasukan; ?>,
                    backgroundColor: 'rgba(16,185,129,0.8)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6,
                },
                {
                    label: 'Pengeluaran',
                    data: <?php echo $chart_keuangan_pengeluaran; ?>,
                    backgroundColor: 'rgba(239,68,68,0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => 'Rp ' + val.toLocaleString('id-ID')
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // ---- 4. Pendaftaran 6 Bulan ----
    const chartPendaftaran = new Chart(document.getElementById('chartPendaftaran'), {
        type: 'line',
        data: {
            labels: <?php echo $chart_pendaftaran_labels; ?>,
            datasets: [
                {
                    label: 'MSH',
                    data: <?php echo $chart_pendaftaran_msh; ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 5,
                },
                {
                    label: 'Kohai',
                    data: <?php echo $chart_pendaftaran_kohai; ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // ---- 5. Status Legalitas ----
    const legalitasColorMap = { 'aktif': '#10b981', 'kadaluarsa': '#ef4444', 'proses': '#f59e0b' };
    const legalitasLabels = <?php echo $chart_legalitas_labels; ?>;
    new Chart(document.getElementById('chartLegalitas'), {
        type: 'pie',
        data: {
            labels: legalitasLabels,
            datasets: [{
                data: <?php echo $chart_legalitas_data; ?>,
                backgroundColor: legalitasLabels.map(l => legalitasColorMap[l] || '#d1d5db'),
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} dokumen` }
                }
            }
        }
    });

    // ---- 6. Pembayaran per Kategori ----
    new Chart(document.getElementById('chartPembayaran'), {
        type: 'bar',
        data: {
            labels: <?php echo $chart_pembayaran_labels; ?>,
            datasets: [{
                label: 'Total Pembayaran',
                data: <?php echo $chart_pembayaran_data; ?>,
                backgroundColor: PALETTE,
                borderColor: PALETTE,
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 24,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: val => 'Rp ' + (val/1000).toLocaleString('id-ID') + 'k' },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // ---- 7. Gender MSH ----
    new Chart(document.getElementById('chartGenderMSH'), {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [<?php echo $laki_msh; ?>, <?php echo $perempuan_msh; ?>],
                backgroundColor: ['#3b82f6', '#ec4899'],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} orang` }
                }
            }
        }
    });

    // ---- 8. Gender Kohai ----
    new Chart(document.getElementById('chartGenderKohai'), {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [<?php echo $laki_kohai; ?>, <?php echo $perempuan_kohai; ?>],
                backgroundColor: ['#3b82f6', '#ec4899'],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} orang` }
                }
            }
        }
    });

    </script>

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/ypok_management/ypok_management/sw.js')
        .then(reg => console.log('SW registered:', reg.scope))
        .catch(err => console.log('SW error:', err));
    });
  }
</script>
</body>
</html>
