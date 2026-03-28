<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$year   = isset($_GET['year'])   ? (int)$_GET['year']   : (int)date('Y');
$period = isset($_GET['period']) ? (int)$_GET['period'] : 6;

// Clamp values
$currentYear = (int)date('Y');
if ($year < 2000 || $year > $currentYear) $year = $currentYear;
if (!in_array($period, [6, 12])) $period = 6;

// ── Build month slots ──────────────────────────────────────────
$months = [];
if ($period === 12) {
    // Full year: Jan–Dec
    for ($m = 1; $m <= 12; $m++) {
        $months[] = [
            'label' => date('M Y', mktime(0, 0, 0, $m, 1, $year)),
            'param' => sprintf('%04d-%02d', $year, $m),
        ];
    }
} else {
    // 6 months ending at Dec of selected year (or current month if current year)
    $endMonth = ($year === $currentYear) ? (int)date('n') : 12;
    for ($i = 5; $i >= 0; $i--) {
        $ts = mktime(0, 0, 0, $endMonth - $i, 1, $year);
        $months[] = [
            'label' => date('M Y', $ts),
            'param' => date('Y-m', $ts),
        ];
    }
}

// ── Keuangan ──────────────────────────────────────────────────
$keuangan = ['labels' => [], 'pemasukan' => [], 'pengeluaran' => []];
foreach ($months as $m) {
    $keuangan['labels'][] = $m['label'];

    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(CASE WHEN tipe='pemasukan'   THEN jumlah ELSE 0 END),0) as pemasukan,
        COALESCE(SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END),0) as pengeluaran
        FROM transaksi WHERE DATE_FORMAT(created_at,'%Y-%m') = ? AND status='lunas'");
    $stmt->execute([$m['param']]);
    $row = $stmt->fetch();
    $keuangan['pemasukan'][]   = (float)$row['pemasukan'];
    $keuangan['pengeluaran'][] = (float)$row['pengeluaran'];
}

// ── Prestasi ──────────────────────────────────────────────────
$prestasi = ['labels' => [], 'msh' => [], 'kohai' => []];
foreach ($months as $m) {
    $prestasi['labels'][] = $m['label'];

    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM prestasi_msh   WHERE DATE_FORMAT(created_at,'%Y-%m') = ?) as msh,
        (SELECT COUNT(*) FROM prestasi_kohai WHERE DATE_FORMAT(created_at,'%Y-%m') = ?) as kohai");
    $stmt->execute([$m['param'], $m['param']]);
    $row = $stmt->fetch();
    $prestasi['msh'][]   = (int)$row['msh'];
    $prestasi['kohai'][] = (int)$row['kohai'];
}

// ── Prestasi pie (total all-time within year) ─────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM prestasi_msh   WHERE YEAR(created_at) = ?");
$stmt->execute([$year]);
$pieMSH = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM prestasi_kohai WHERE YEAR(created_at) = ?");
$stmt->execute([$year]);
$pieKohai = (int)$stmt->fetchColumn();

echo json_encode([
    'year'        => $year,
    'period'      => $period,
    'keuangan'    => $keuangan,
    'prestasi'    => $prestasi,
    'prestasi_pie'=> ['labels' => ['MSH','Kohai'], 'data' => [$pieMSH, $pieKohai]],
]);
