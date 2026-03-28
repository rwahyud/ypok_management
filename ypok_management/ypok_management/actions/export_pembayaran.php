<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get form data
$format = $_GET['format'] ?? 'pdf';
$periode = $_GET['periode'] ?? 'month';
$dari_tanggal = $_GET['dari_tanggal'] ?? '';
$sampai_tanggal = $_GET['sampai_tanggal'] ?? '';
$ketua_ypok = $_GET['ketua_ypok'] ?? 'Ketua YPOK';
$admin_pembuat = $_GET['admin_pembuat'] ?? $_SESSION['nama_lengkap'];

// Build query based on periode
$where = "1=1";
$params = [];
$periode_text = "Semua Data";

if ($periode === 'month') {
    $where = "MONTH(tanggal_bayar) = MONTH(CURRENT_DATE) AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE)";
    $periode_text = "Bulan " . date('F Y');
} elseif ($periode === 'last_month') {
    $where = "MONTH(tanggal_bayar) = MONTH(CURRENT_DATE - INTERVAL '1 month') AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE - INTERVAL '1 month')";
    $periode_text = "Bulan " . date('F Y', strtotime('-1 month'));
} elseif ($periode === 'custom' && $dari_tanggal && $sampai_tanggal) {
    $where = "tanggal_bayar BETWEEN :dari_tanggal AND :sampai_tanggal";
    $params = ['dari_tanggal' => $dari_tanggal, 'sampai_tanggal' => $sampai_tanggal];
    $periode_text = date('d/m/Y', strtotime($dari_tanggal)) . " - " . date('d/m/Y', strtotime($sampai_tanggal));
}

// Get pembayaran data
$sql = "SELECT p.* FROM pembayaran p WHERE $where ORDER BY p.tanggal_bayar DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pembayaran_list = $stmt->fetchAll();

// Calculate totals per kategori
$sql_totals = "SELECT kategori, COALESCE(SUM(CASE WHEN status = 'Lunas' THEN jumlah ELSE 0 END), 0) as total_lunas
               FROM pembayaran WHERE $where GROUP BY kategori";
$stmt_totals = $pdo->prepare($sql_totals);
$stmt_totals->execute($params);
$totals_per_kategori = $stmt_totals->fetchAll(PDO::FETCH_KEY_PAIR);

// Calculate grand totals
$sql_grand = "SELECT 
    COALESCE(SUM(CASE WHEN status = 'Lunas' THEN jumlah ELSE 0 END), 0) as grand_total_lunas,
    COALESCE(SUM(jumlah), 0) as grand_total
FROM pembayaran WHERE $where";
$stmt_grand = $pdo->prepare($sql_grand);
$stmt_grand->execute($params);
$grand_totals = $stmt_grand->fetch();

// Calculate total per kategori (all status)
$sql_total_all = "SELECT kategori, COALESCE(SUM(jumlah), 0) as total FROM pembayaran WHERE $where GROUP BY kategori";
$stmt_total_all = $pdo->prepare($sql_total_all);
$stmt_total_all->execute($params);
$totals_all_per_kategori = $stmt_total_all->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle different export formats
if ($format === 'csv') {
    // CSV Export dengan format yang lebih baik
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Pembayaran_YPOK_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM untuk Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header Laporan
    fputcsv($output, ['LAPORAN PEMBAYARAN YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Periode', $periode_text], ',');
    fputcsv($output, ['Tanggal Cetak', date('d F Y, H:i') . ' WIB'], ',');
    fputcsv($output, ['Dicetak oleh', $admin_pembuat], ',');
    fputcsv($output, ['Total Transaksi', count($pembayaran_list) . ' transaksi'], ',');
    fputcsv($output, [''], ',');
    
    // Table header
    fputcsv($output, [
        'No',
        'Tanggal',
        'Kategori',
        'Nama Kohai',
        'Keterangan',
        'Jumlah (Rp)',
        'Metode Pembayaran',
        'Status'
    ], ',');
    
    // Data rows
    if (!empty($pembayaran_list)) {
        $no = 1;
        foreach($pembayaran_list as $p) {
            fputcsv($output, [
                $no++,
                date('d/m/Y', strtotime($p['tanggal_bayar'])),
                $p['kategori'],
                $p['nama_kohai'] ?? '-',
                $p['keterangan'],
                number_format($p['jumlah'], 0, ',', '.'),
                $p['metode_pembayaran'],
                $p['status']
            ], ',');
        }
    } else {
        fputcsv($output, ['', '', '', 'Tidak ada data pembayaran', '', '', '', ''], ',');
    }
    
    // Separator
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    
    // Summary - Total per Kategori (Lunas)
    fputcsv($output, ['RINGKASAN PEMBAYARAN'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Kategori', 'Total Lunas (Rp)'], ',');
    
    if (!empty($totals_per_kategori)) {
        foreach($totals_per_kategori as $kategori => $total_lunas) {
            fputcsv($output, [
                $kategori,
                number_format($total_lunas, 0, ',', '.')
            ], ',');
        }
    }
    
    fputcsv($output, [''], ',');
    
    // Total per Kategori (Semua Status)
    fputcsv($output, ['TOTAL SEMUA KATEGORI (Semua Status)'], ',');
    fputcsv($output, ['Kategori', 'Total (Rp)'], ',');
    
    if (!empty($totals_all_per_kategori)) {
        foreach($totals_all_per_kategori as $kategori => $total) {
            fputcsv($output, [
                $kategori,
                number_format($total, 0, ',', '.')
            ], ',');
        }
    }
    
    fputcsv($output, [''], ',');
    
    // Grand Total
    fputcsv($output, ['GRAND TOTAL (Semua Status)', 'Rp ' . number_format($grand_totals['grand_total'], 0, ',', '.')], ',');
    fputcsv($output, ['TOTAL LUNAS', 'Rp ' . number_format($grand_totals['grand_total_lunas'], 0, ',', '.')], ',');
    
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    
 
    
    fclose($output);
    exit();
    
} elseif ($format === 'excel') {
    // Excel Export (simple HTML table that Excel can open)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Pembayaran_' . date('Ymd_His') . '.xls"');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr><td colspan="8" style="text-align:center; font-weight:bold; font-size:16px;">LAPORAN PEMBAYARAN YPOK</td></tr>';
    echo '<tr><td colspan="8" style="text-align:center;">Periode: ' . $periode_text . '</td></tr>';
    echo '<tr><td colspan="8" style="text-align:center;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</td></tr>';
    echo '<tr><td colspan="8"></td></tr>';
    echo '<tr style="background-color:#667eea; color:white; font-weight:bold;">';
    echo '<td>No</td><td>Tanggal</td><td>Kategori</td><td>Nama Kohai</td><td>Keterangan</td><td>Jumlah</td><td>Metode</td><td>Status</td>';
    echo '</tr>';
    
    $no = 1;
    foreach($pembayaran_list as $p) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($p['tanggal_bayar'])) . '</td>';
        echo '<td>' . htmlspecialchars($p['kategori']) . '</td>';
        echo '<td>' . htmlspecialchars($p['nama_kohai'] ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($p['keterangan']) . '</td>';
        echo '<td style="text-align:right;">Rp ' . number_format($p['jumlah'], 0, ',', '.') . '</td>';
        echo '<td>' . htmlspecialchars($p['metode_pembayaran']) . '</td>';
        echo '<td>' . htmlspecialchars($p['status']) . '</td>';
        echo '</tr>';
    }
    
    echo '<tr><td colspan="8"></td></tr>';
    echo '<tr style="background-color:#f0f9ff; font-weight:bold;"><td colspan="8">RINGKASAN</td></tr>';
    foreach($totals_per_kategori as $kategori => $total) {
        echo '<tr><td colspan="5">Total ' . $kategori . ' (Lunas)</td><td colspan="3" style="text-align:right;">Rp ' . number_format($total, 0, ',', '.') . '</td></tr>';
    }
    echo '<tr style="background-color:#dbeafe; font-weight:bold;"><td colspan="5">GRAND TOTAL</td><td colspan="3" style="text-align:right;">Rp ' . number_format($grand_totals['grand_total'], 0, ',', '.') . '</td></tr>';
    echo '<tr style="background-color:#d1fae5; font-weight:bold;"><td colspan="5">TOTAL LUNAS</td><td colspan="3" style="text-align:right;">Rp ' . number_format($grand_totals['grand_total_lunas'], 0, ',', '.') . '</td></tr>';
    
    echo '</table>';
    echo '</body></html>';
    exit();
}

// Default: PDF/HTML format
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran - YPOK Management</title>
    <style>
        @page { margin: 20mm; size: A4 portrait; }
        @media print {
            body { margin: 0; padding: 0; }
            .action-buttons, .no-print { display: none !important; }
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 5px 0;
            color: #1f2937;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 12px;
        }
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background: #1e3a8a;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .summary-box {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0f2fe;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-name {
            margin-top: 80px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-info { background: #e0e7ff; color: #4338ca; }
        
        /* Button Styles */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .btn-print {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
        }
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-cancel {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }
    </style>
</head>
<body>
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ Print / Simpan PDF
        </button>
        <button class="btn btn-cancel" onclick="window.close()">
            ✖️ Close
        </button>
    </div>

    <div class="header">
        <h1>YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</h1>
        <h2>LAPORAN PEMBAYARAN</h2>
        <p>Periode: <?php echo $periode_text; ?></p>
        <p>Tanggal Cetak: <span id="tanggalCetak"></span></p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Total Data:</span>
            <span><?php echo count($pembayaran_list); ?> transaksi</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dicetak oleh:</span>
            <span><?php echo htmlspecialchars($admin_pembuat); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Nama Kohai</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($pembayaran_list)): ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pembayaran pada periode ini</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach($pembayaran_list as $p): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($p['tanggal_bayar'])); ?></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $p['kategori'] == 'Ujian' ? 'primary' : 
                                ($p['kategori'] == 'Kyu' ? 'info' : 
                                ($p['kategori'] == 'Rakernas' ? 'warning' : 'primary')); 
                        ?>">
                            <?php echo htmlspecialchars($p['kategori']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($p['nama_kohai'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['keterangan']); ?></td>
                    <td class="text-right" style="font-weight: bold;">Rp <?php echo number_format($p['jumlah'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($p['metode_pembayaran']); ?></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $p['status'] == 'Lunas' ? 'success' : 
                                ($p['status'] == 'Sebagian' ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo $p['status']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <h3 style="margin-top: 0;">Ringkasan Pembayaran</h3>
        <?php if(!empty($totals_per_kategori)): ?>
            <?php foreach($totals_per_kategori as $kategori => $total_lunas): ?>
            <div class="summary-row">
                <span>Total <?php echo $kategori; ?> (Lunas):</span>
                <span style="font-weight: bold;">Rp <?php echo number_format($total_lunas, 0, ',', '.'); ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if(!empty($totals_all_per_kategori)): ?>
            <div class="summary-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #0ea5e9;">
                <span><strong>TOTAL SEMUA KATEGORI (Semua Status):</strong></span>
                <span></span>
            </div>
            <?php foreach($totals_all_per_kategori as $kategori => $total): ?>
            <div class="summary-row">
                <span>• <?php echo $kategori; ?>:</span>
                <span style="font-weight: bold;">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="summary-row" style="background: #dbeafe; margin: 10px -15px 0; padding: 12px 15px;">
            <span><strong>GRAND TOTAL (Semua Status):</strong></span>
            <span style="color: #1e40af; font-weight: bold; font-size: 14px;">Rp <?php echo number_format($grand_totals['grand_total'], 0, ',', '.'); ?></span>
        </div>
        <div class="summary-row" style="background: #d1fae5; margin: 5px -15px -15px; padding: 12px 15px;">
            <span><strong>TOTAL LUNAS:</strong></span>
            <span style="color: #065f46; font-weight: bold; font-size: 14px;">Rp <?php echo number_format($grand_totals['grand_total_lunas'], 0, ',', '.'); ?></span>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>Mengetahui,<br><strong>Ketua YPOK</strong></p>
            <div class="signature-name">
                <?php echo htmlspecialchars($ketua_ypok); ?>
            </div>
        </div>
        <div class="signature-box">
            <p>Dibuat oleh,<br><strong>Administrator</strong></p>
            <div class="signature-name">
                <?php echo htmlspecialchars($admin_pembuat); ?>
            </div>
        </div>
    </div>

    <script>
        // Real-time clock function
        const bulanIndo = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        function updateClock() {
            const now = new Date();
            const tanggal = now.getDate();
            const bulan = bulanIndo[now.getMonth()];
            const tahun = now.getFullYear();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');

            const formatWaktu = `${tanggal} ${bulan} ${tahun}, ${jam}:${menit}:${detik} WIB`;
            document.getElementById('tanggalCetak').textContent = formatWaktu;
        }

        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Auto print on load if requested
        if (window.location.search.includes('auto_print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>
