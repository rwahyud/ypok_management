<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$periode = $_GET['periode'] ?? 'all';

// Get transactions
$query = "SELECT * FROM transaksi WHERE 1=1";
$params = [];

if($periode !== 'all') {
    $query .= " AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
    $params[] = $periode;
}

$query .= " ORDER BY tanggal ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transaksi_list = $stmt->fetchAll();

// Calculate running saldo and totals
$running_saldo = 0;
foreach($transaksi_list as &$trans) {
    if($trans['jenis'] === 'pemasukan') {
        $running_saldo += $trans['jumlah'];
    } else {
        $running_saldo -= $trans['jumlah'];
    }
    $trans['saldo_running'] = $running_saldo;
}
unset($trans);

$total_query = "SELECT 
    SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan,
    SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran
    FROM transaksi WHERE 1=1";
$total_params = [];

if($periode !== 'all') {
    $total_query .= " AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
    $total_params[] = $periode;
}

$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute($total_params);
$totals = $total_stmt->fetch();

$pemasukan = $totals['total_pemasukan'] ?? 0;
$pengeluaran = $totals['total_pengeluaran'] ?? 0;
$saldo = $pemasukan - $pengeluaran;

$periode_label = $periode === 'all' ? 'Semua Periode' : date('F Y', strtotime($periode . '-01'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan YPOK - <?php echo $periode_label; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-item strong { display: block; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .income { color: green; font-weight: bold; text-align: right; }
        .expense { color: red; font-weight: bold; text-align: right; }
        .jenis-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .jenis-pemasukan { background: #d1fae5; color: #059669; }
        .jenis-pengeluaran { background: #fee2e2; color: #dc2626; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #666; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer;">🖨️ Cetak</button>
    
    <div class="header">
        <h1>LAPORAN KEUANGAN YPOK</h1>
        <p>Periode: <?php echo $periode_label; ?></p>
        <p>Tanggal Cetak: <?php echo date('d F Y H:i'); ?></p>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <small>Total Pemasukan</small>
            <strong class="income">Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?></strong>
        </div>
        <div class="summary-item">
            <small>Total Pengeluaran</small>
            <strong class="expense">Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?></strong>
        </div>
        <div class="summary-item">
            <small>Saldo Bersih</small>
            <strong style="color: #2196F3;">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></strong>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th>Jenis</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($transaksi_list as $index => $transaksi): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo date('d/m/Y', strtotime($transaksi['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($transaksi['keterangan']); ?></td>
                <td><?php echo htmlspecialchars($transaksi['kategori'] ?? '-'); ?></td>
                <td><span class="jenis-badge jenis-<?php echo $transaksi['jenis']; ?>"><?php echo ucfirst($transaksi['jenis']); ?></span></td>
                <td class="income">
                    <?php echo $transaksi['jenis'] === 'pemasukan' ? 'Rp ' . number_format($transaksi['jumlah'], 0, ',', '.') : '-'; ?>
                </td>
                <td class="expense">
                    <?php echo $transaksi['jenis'] === 'pengeluaran' ? 'Rp ' . number_format($transaksi['jumlah'], 0, ',', '.') : '-'; ?>
                </td>
                <td style="text-align: right;">Rp <?php echo number_format($transaksi['saldo_running'] ?? 0, 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak oleh: <?php echo $_SESSION['nama_lengkap']; ?></p>
        <p>© <?php echo date('Y'); ?> YPOK Management System</p>
    </div>
</body>
</html>
