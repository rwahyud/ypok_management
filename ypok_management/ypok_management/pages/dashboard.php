<?php
require_once __DIR__ . '/../config/database.php';

function dashboardBasePath(): string {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(dirname(dirname($scriptName)), '/');
    if ($basePath === '/' || $basePath === '.') {
        return '';
    }
    return $basePath;
}

if(!isset($_SESSION['user_id'])) {
    header('Location: ' . dashboardBasePath() . '/index.php');
    exit();
}

// Safe defaults so dashboard still renders even if one DB query fails on production.
$total_msh = 0;
$total_kohai = 0;
$total_lokasi = 0;
$total_pendapatan_bulan = 0;
$saldo_keuangan = 0;
$total_kegiatan = 0;
$total_legalitas = 0;
$keuangan_bulan = [];
$total_prestasi_msh = 0;
$total_prestasi_kohai = 0;
$prestasi_bulan = [];
$legalitas_status_raw = [];
$pembayaran_kategori_raw = [];
$gender_msh = [];
$gender_kohai = [];
$recent_transaksi = [];
$upcoming_kegiatan = [];
$chart_keuangan_labels = json_encode([]);
$chart_keuangan_pemasukan = json_encode([]);
$chart_keuangan_pengeluaran = json_encode([]);
$chart_prestasi_labels = json_encode([]);
$chart_prestasi_msh = json_encode([]);
$chart_prestasi_kohai = json_encode([]);
$chart_prestasi_pie_labels = json_encode(['MSH', 'Kohai']);
$chart_prestasi_pie_data = json_encode([0, 0]);
$chart_legalitas_labels = json_encode([]);
$chart_legalitas_data = json_encode([]);
$chart_pembayaran_labels = json_encode([]);
$chart_pembayaran_data = json_encode([]);
$laki_msh = 0;
$perempuan_msh = 0;
$laki_kohai = 0;
$perempuan_kohai = 0;

try {

// =============================================
// STATISTIK UTAMA
// =============================================

$total_msh       = $pdo->query("SELECT COUNT(*) FROM master_sabuk_hitam WHERE status='Aktif'")->fetchColumn();
$total_kohai     = $pdo->query("SELECT COUNT(*) FROM kohai WHERE status='Aktif'")->fetchColumn();
$total_lokasi    = $pdo->query("SELECT COUNT(*) FROM lokasi WHERE status='aktif'")->fetchColumn();
$total_pendapatan_bulan = $pdo->query("
    SELECT
        COALESCE((SELECT SUM(jumlah) FROM pembayaran WHERE status='Lunas' AND MONTH(tanggal_bayar)=MONTH(CURDATE()) AND YEAR(tanggal_bayar)=YEAR(CURDATE())), 0)
        + COALESCE((SELECT SUM(total_harga) FROM transaksi_toko WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())), 0)
        AS total
")->fetchColumn();

$saldo_row = $pdo->query("SELECT COALESCE(SUM(CASE WHEN jenis='pemasukan' THEN jumlah ELSE 0 END),0) - COALESCE(SUM(CASE WHEN jenis='pengeluaran' THEN jumlah ELSE 0 END),0) as saldo FROM transaksi")->fetch();
$saldo_keuangan = $saldo_row['saldo'] ?? 0;

$total_kegiatan  = $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE LOWER(REPLACE(status, ' ', '_')) IN ('akan_datang', 'dijadwalkan')")->fetchColumn();
$total_legalitas = $pdo->query("SELECT COUNT(*) FROM legalitas WHERE status='Aktif'")->fetchColumn();

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
                WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?
            UNION ALL
            SELECT 'pemasukan', jumlah
                FROM pembayaran
                WHERE status='Lunas' AND DATE_FORMAT(tanggal_bayar,'%Y-%m') = ?
            UNION ALL
            SELECT 'pemasukan', total_harga
                FROM transaksi_toko
                WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?
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
        (SELECT COUNT(*) FROM prestasi_msh   WHERE DATE_FORMAT(created_at,'%Y-%m') = ?) as msh,
        (SELECT COUNT(*) FROM prestasi_kohai WHERE DATE_FORMAT(created_at,'%Y-%m') = ?) as kohai");
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
$pembayaran_kategori_raw = $pdo->query("SELECT kategori, COUNT(*) as jumlah, COALESCE(SUM(jumlah),0) as total FROM pembayaran WHERE kategori IS NOT NULL AND kategori != '' GROUP BY kategori ORDER BY total DESC LIMIT 8")->fetchAll();

// =============================================
// DATA CHART: GENDER MSH & KOHAI
// =============================================
$gender_msh = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM master_sabuk_hitam WHERE status='aktif' GROUP BY jenis_kelamin")->fetchAll();
$gender_kohai = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM kohai WHERE status='aktif' GROUP BY jenis_kelamin")->fetchAll();

// =============================================
// RECENT ACTIVITY: 5 TRANSAKSI TERBARU
// =============================================
$recent_transaksi = $pdo->query("SELECT * FROM transaksi ORDER BY created_at DESC LIMIT 5")->fetchAll();

// =============================================
// UPCOMING KEGIATAN
// =============================================
$upcoming_kegiatan = $pdo->query("SELECT k.*, l.nama_lokasi FROM kegiatan k LEFT JOIN lokasi l ON k.lokasi_id = l.id WHERE LOWER(REPLACE(k.status, ' ', '_')) IN ('akan_datang', 'dijadwalkan') ORDER BY k.tanggal_kegiatan ASC LIMIT 5")->fetchAll();

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
} catch (Throwable $e) {
    error_log('Dashboard data load error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e3a8a">
    <title>Dashboard - YPOK Management</title>
    <link rel="manifest" href="../manifest.json">
    <link rel="apple-touch-icon" href="../assets/images/logo ypok .jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ===== DASHBOARD STYLES ===== */
        .dashboard-container {
            padding: 20px 30px 20px 15px;
            margin: 0;
        }

        /* --- Welcome Banner --- */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 0 16px 16px 0;
            padding: 28px 32px 28px 15px;
            margin-bottom: 28px;
            margin-left: 0;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 24px rgba(30,58,138,0.25);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::after {
            content: '🥋';
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 80px;
            opacity: 0.18;
            pointer-events: none;
        }

        .welcome-banner h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .welcome-banner p {
            font-size: 14px;
            opacity: 0.85;
            margin: 0;
        }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border-left: 5px solid transparent;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
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
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .stat-value.small-text {
            font-size: 16px;
        }

        .stat-sub {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* --- Charts Grid --- */
        .charts-row {
            display: grid;
            gap: 20px;
            margin-bottom: 24px;
        }

        .charts-row.col-2  { grid-template-columns: 1fr 1fr; }
        .charts-row.col-3  { grid-template-columns: repeat(3, 1fr); }
        .charts-row.col-32 { grid-template-columns: 3fr 2fr; }
        .charts-row.col-23 { grid-template-columns: 2fr 3fr; }

        /* Section Divider */
        .section-divider {
            margin: 32px 0 24px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .chart-title {
            font-size: 15px;
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
            margin-bottom: 30px;
        }

        .activity-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
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
        }

        .activity-link:hover { text-decoration: underline; }

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

        .mini-table tr:hover td { background: #f8fafc; }

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

        /* Large Tablet (769px - 1200px) */
        @media (min-width: 769px) and (max-width: 1200px) {
            .charts-row.col-3 { grid-template-columns: repeat(2, 1fr); }
        }

        /* Tablet (≤1200px) */
        @media (max-width: 1200px) {
            .dashboard-container { padding: 18px 20px 18px 10px; margin: 0; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row.col-32,
            .charts-row.col-23,
            .charts-row.col-2 { grid-template-columns: 1fr; }
            .activity-row { grid-template-columns: 1fr; }
            .welcome-banner { padding: 22px 24px 22px 10px; }
            .welcome-banner h2 { font-size: 19px; }
        }

        /* Mobile (≤768px) */
        @media (max-width: 768px) {
            .dashboard-container { padding: 14px 12px; margin: 0; }
            .stats-grid { gap: 12px; margin-bottom: 16px; }
            .charts-row.col-3 { grid-template-columns: 1fr; }
            .stat-card { padding: 14px 12px; gap: 10px; }
            .stat-icon-box { width: 42px; height: 42px; font-size: 20px; }
            .stat-value { font-size: 20px; }
            .stat-value.small-text { font-size: 13px; }
            .stat-label { font-size: 11px; }
            .welcome-banner {
                padding: 18px 18px;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
            .welcome-banner h2 { font-size: 17px; }
            .welcome-banner p { font-size: 13px; }
            .welcome-banner::after { font-size: 54px; right: 18px; }
            .charts-row { gap: 14px; margin-bottom: 14px; }
            .chart-card { padding: 16px 12px; }
            .activity-card { padding: 16px 14px; }
            .chart-title { font-size: 13px; }
            .chart-subtitle { font-size: 11px; }
            .chart-body canvas { max-height: 170px; }
            .chart-body.small canvas { max-height: 140px; }
            .activity-row { gap: 14px; margin-bottom: 14px; }
            .mini-table { font-size: 12px; }
            .mini-table th { padding: 6px 8px; font-size: 10px; }
            .mini-table td { padding: 8px 8px; }
            .section-divider { margin: 24px 0 18px; }
            .section-title { font-size: 14px; }
        }

        /* Small Mobile (≤480px) */
        @media (max-width: 480px) {
            .dashboard-container { padding: 10px 8px; margin: 0; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 12px 10px; gap: 8px; }
            .stat-icon-box { width: 36px; height: 36px; font-size: 17px; border-radius: 8px; }
            .stat-value { font-size: 17px; }
            .stat-value.small-text { font-size: 12px; }
            .welcome-banner { padding: 14px 14px; }
            .welcome-banner h2 { font-size: 15px; }
            .welcome-banner::after { display: none; }
            .chart-card { padding: 12px 10px; }
            .activity-card { padding: 14px 12px; }
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                margin-bottom: 12px;
            }
            .chart-body canvas { max-height: 150px; }
            .chart-body.small canvas { max-height: 120px; }
        }

        /* Extra Small Mobile - iPhone SE (≤375px) */
        @media (max-width: 375px) {
            .dashboard-container { padding: 8px 6px; margin: 0; }
            .stats-grid { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
            .stat-card { padding: 10px 8px; gap: 6px; }
            .stat-icon-box { width: 32px; height: 32px; font-size: 15px; border-radius: 6px; }
            .stat-value { font-size: 15px; }
            .stat-value.small-text { font-size: 11px; }
            .stat-label { font-size: 10px; }
            .stat-sublabel { font-size: 9px; opacity: 0.8; }
            .welcome-banner { padding: 12px 10px; margin-bottom: 12px; border-radius: 0 12px 12px 0; }
            .welcome-banner h2 { font-size: 13px; margin-bottom: 4px; }
            .welcome-banner p { font-size: 11px; }
            .welcome-banner::after { display: none; }
            .charts-row { gap: 10px; margin-bottom: 10px; }
            .chart-card { padding: 10px 8px; }
            .activity-card { padding: 10px 8px; }
            .chart-title { font-size: 12px; font-weight: 600; }
            .chart-subtitle { font-size: 10px; }
            .section-title { font-size: 13px; }
            .section-divider { margin: 16px 0 12px; }
            .chart-header { margin-bottom: 10px; }
            .chart-body canvas { max-height: 140px; }
            .chart-body.small canvas { max-height: 110px; }
            .mini-table { font-size: 11px; }
            .mini-table th { padding: 4px 6px; font-size: 9px; }
            .mini-table td { padding: 6px 6px; }
            .chart-filter-bar { padding: 10px 8px; gap: 8px; margin-bottom: 12px; }
            .filter-label { font-size: 12px; }
            .chart-filter-controls { gap: 10px; }
            .filter-select { padding: 4px 8px; font-size: 12px; }
            .filter-btn { padding: 4px 10px; font-size: 12px; }
        }

        /* ── Chart Filter Bar ──────────────────────────────── */
        .chart-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .filter-label {
            font-size: 14px;
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
            transition: border-color .2s;
        }
        .filter-select:focus { border-color: #3b82f6; }
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
            transition: all .2s;
        }
        .filter-btn:hover { border-color: #3b82f6; color: #3b82f6; }
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
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <?php include '../components/navbar.php'; ?>

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

            <!-- Stats Row 1: Data Utama -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon-box">🥋</div>
                    <div class="stat-info">
                        <div class="stat-label">Total MSH Aktif</div>
                        <div class="stat-value"><?php echo number_format($total_msh); ?></div>
                        <div class="stat-sub">Master Sabuk Hitam</div>
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

                <div class="stat-card teal">
                    <div class="stat-icon-box">📄</div>
                    <div class="stat-info">
                        <div class="stat-label">Legalitas Aktif</div>
                        <div class="stat-value"><?php echo number_format($total_legalitas); ?></div>
                        <div class="stat-sub">Dokumen legal valid</div>
                    </div>
                </div>
            </div>

            <!-- Stats Row 2: Keuangan & Kegiatan -->
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

                <div class="stat-card amber">
                    <div class="stat-icon-box">🎖️</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Prestasi</div>
                        <div class="stat-value"><?php echo number_format($total_prestasi_msh + $total_prestasi_kohai); ?></div>
                        <div class="stat-sub">MSH + Kohai keseluruhan</div>
                    </div>
                </div>
            </div>

            <!-- Section: Analisis & Grafik -->
            <div class="section-divider">
                <h3 class="section-title">📊 Analisis & Grafik</h3>
            </div>

            <!-- Chart Row 1: Keuangan (Full Width) -->
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
                        <div class="chart-title">📈 Prestasi per Bulan</div>
                        <span class="chart-subtitle">MSH vs Kohai 6 bulan terakhir</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartMSH"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Row 2: Prestasi & Pembayaran -->
            <div class="charts-row col-2">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">🏆 Prestasi MSH vs Kohai</div>
                        <span class="chart-subtitle">Total prestasi keseluruhan</span>
                    </div>
                    <div class="chart-body small">
                        <canvas id="chartKohai"></canvas>
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

            <!-- Chart Row 3: Legalitas & Gender -->
            <div class="charts-row col-3">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">📄 Status Legalitas</div>
                        <span class="chart-subtitle">Dokumen hukum organisasi</span>
                    </div>
                    <div class="chart-body small">
                        <canvas id="chartLegalitas"></canvas>
                    </div>
                </div>

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

            <!-- Section: Aktivitas Terkini -->
            <div class="section-divider">
                <h3 class="section-title">📋 Aktivitas Terkini</h3>
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

    <script src="../assets/js/app.js"></script>
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
            animation: false,
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

    // ---- 2. Prestasi MSH vs Kohai (Doughnut) ----
    const chartKohai = new Chart(document.getElementById('chartKohai'), {
        type: 'doughnut',
        data: {
            labels: <?php echo $chart_prestasi_pie_labels; ?>,
            datasets: [{
                data: <?php echo $chart_prestasi_pie_data; ?>,
                backgroundColor: ['#3b82f6', '#10b981'],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            animation: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} prestasi`
                    }
                }
            }
        }
    });

    // ---- 3. Prestasi per Bulan (Line) ----
    const chartMSH = new Chart(document.getElementById('chartMSH'), {
        type: 'line',
        data: {
            labels: <?php echo $chart_prestasi_labels; ?>,
            datasets: [
                {
                    label: 'MSH',
                    data: <?php echo $chart_prestasi_msh; ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 5,
                },
                {
                    label: 'Kohai',
                    data: <?php echo $chart_prestasi_kohai; ?>,
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
            animation: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} prestasi` }
                }
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

    // ---- 4. Status Legalitas ----
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
            animation: false,
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
            animation: false,
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
            animation: false,
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
            animation: false,
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
            const appBasePath = window.location.pathname
                                .replace(/\/pages\/[^\/]*$/, '');
            navigator.serviceWorker.register((appBasePath || '') + '/sw.js')
                .catch(() => {});
    });
  }
  
</script>
</body>
</html>
