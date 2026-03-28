<?php
require_once __DIR__ . '/../config/database.php';

// Check if Dompdf is available
$use_dompdf = file_exists(__DIR__ . '/../vendor/autoload.php');

if ($use_dompdf) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$dompdfOptionsClass = '\\Dompdf\\Options';
$dompdfClass = '\\Dompdf\\Dompdf';
$dompdfAvailable = $use_dompdf && class_exists($dompdfOptionsClass) && class_exists($dompdfClass);

function getYpokLogoDataUri(): string {
    $logoCandidates = [
        __DIR__ . '/../assets/images/LOGO YPOK NO BACKGROUND.png',
        __DIR__ . '/../assets/images/logo ypok .jpg',
    ];

    foreach ($logoCandidates as $logoPath) {
        if (!file_exists($logoPath)) {
            continue;
        }

        $raw = @file_get_contents($logoPath);
        if ($raw === false || $raw === '') {
            continue;
        }

        $mime = @mime_content_type($logoPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    return '';
}

$logoDataUri = getYpokLogoDataUri();

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get parameters
$periode = $_GET['periode'] ?? 'month';
$format = $_GET['format'] ?? 'pdf';
$ketua = $_GET['ketua'] ?? 'Ketua YPOK';
$admin = $_GET['admin'] ?? $_SESSION['nama_lengkap'];

// Calculate date range based on periode
$start_date = '';
$end_date = '';
$periode_text = '';

switch($periode) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $periode_text = 'Hari Ini - ' . date('d F Y');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        $periode_text = 'Minggu Ini (' . date('d M', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)) . ')';
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $periode_text = 'Bulan ' . date('F Y');
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('first day of last month'));
        $end_date = date('Y-m-t', strtotime('last day of last month'));
        $periode_text = 'Bulan ' . date('F Y', strtotime('last month'));
        break;
    case 'custom':
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $periode_text = date('d F Y', strtotime($start_date)) . ' - ' . date('d F Y', strtotime($end_date));
        break;
}

// Query transaksi
$query = "SELECT t.*, p.nama_produk, p.kode_produk 
    FROM transaksi_toko t
    LEFT JOIN produk_toko p ON t.produk_id = p.id
    WHERE DATE(t.tanggal) BETWEEN :start_date AND :end_date
    ORDER BY t.tanggal DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$transaksi_list = $stmt->fetchAll();

// Calculate statistics
$total_transaksi = count($transaksi_list);
$total_pendapatan = array_sum(array_column($transaksi_list, 'total_harga'));
$total_produk = array_sum(array_column($transaksi_list, 'jumlah'));
$rata_rata = $total_transaksi > 0 ? $total_pendapatan / $total_transaksi : 0;

// Handle CSV Export
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Transaksi_Toko_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header Laporan
    fputcsv($output, ['LAPORAN TRANSAKSI TOKO YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Periode', $periode_text], ',');
    fputcsv($output, ['Tanggal Cetak', date('d F Y, H:i') . ' WIB'], ',');
    fputcsv($output, ['Dicetak oleh', $admin], ',');
    fputcsv($output, [''], ',');

    // Statistik
    fputcsv($output, ['STATISTIK'], ',');
    fputcsv($output, ['Total Transaksi', number_format($total_transaksi) . ' transaksi'], ',');
    fputcsv($output, ['Total Pendapatan', 'Rp ' . number_format($total_pendapatan, 0, ',', '.')], ',');
    fputcsv($output, ['Produk Terjual', number_format($total_produk) . ' unit'], ',');
    fputcsv($output, ['Rata-rata per Transaksi', 'Rp ' . number_format($rata_rata, 0, ',', '.')], ',');
    fputcsv($output, [''], ',');

    // Table header
    fputcsv($output, [
        'No',
        'Tanggal',
        'ID Transaksi',
        'Produk',
        'Kode Produk',
        'Variasi',
        'Pembeli',
        'Lokasi',
        'Qty',
        'Total (Rp)',
        'Metode Pembayaran'
    ], ',');

    // Data rows
    if (!empty($transaksi_list)) {
        $no = 1;
        foreach($transaksi_list as $trans) {
            fputcsv($output, [
                $no++,
                date('d/m/Y', strtotime($trans['tanggal'])),
                $trans['id_transaksi'],
                $trans['nama_produk'],
                $trans['kode_produk'],
                $trans['variasi_info'] ?? '-',
                $trans['pembeli'],
                $trans['lokasi'] . ' - ' . $trans['alamat'],
                $trans['jumlah'],
                number_format($trans['total_harga'], 0, ',', '.'),
                $trans['metode_pembayaran']
            ], ',');
        }
    } else {
        fputcsv($output, ['', '', '', 'Tidak ada data transaksi', '', '', '', '', '', '', ''], ',');
    }

    // Summary
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['RINGKASAN'], ',');
    fputcsv($output, ['Total Keseluruhan (Qty)', number_format($total_produk) . ' unit'], ',');
    fputcsv($output, ['Total Keseluruhan (Pendapatan)', 'Rp ' . number_format($total_pendapatan, 0, ',', '.')], ',');

    fclose($output);
    exit();

} elseif ($format === 'excel') {
    // Excel Export (simple HTML table that Excel can open)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Transaksi_Toko_' . date('Ymd_His') . '.xls"');

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr><td colspan="11" style="text-align:center; font-weight:bold; font-size:16px;">LAPORAN TRANSAKSI TOKO YPOK</td></tr>';
    echo '<tr><td colspan="11" style="text-align:center;">Periode: ' . $periode_text . '</td></tr>';
    echo '<tr><td colspan="11" style="text-align:center;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</td></tr>';
    echo '<tr><td colspan="11"></td></tr>';

    // Statistik
    echo '<tr style="background-color:#f0f9ff; font-weight:bold;"><td colspan="11">STATISTIK</td></tr>';
    echo '<tr><td colspan="4">Total Transaksi</td><td colspan="7">' . number_format($total_transaksi) . ' transaksi</td></tr>';
    echo '<tr><td colspan="4">Total Pendapatan</td><td colspan="7" style="text-align:right;">Rp ' . number_format($total_pendapatan, 0, ',', '.') . '</td></tr>';
    echo '<tr><td colspan="4">Produk Terjual</td><td colspan="7">' . number_format($total_produk) . ' unit</td></tr>';
    echo '<tr><td colspan="4">Rata-rata per Transaksi</td><td colspan="7" style="text-align:right;">Rp ' . number_format($rata_rata, 0, ',', '.') . '</td></tr>';
    echo '<tr><td colspan="11"></td></tr>';

    // Table header
    echo '<tr style="background-color:#667eea; color:white; font-weight:bold;">';
    echo '<td>No</td><td>Tanggal</td><td>ID Transaksi</td><td>Produk</td><td>Kode</td><td>Variasi</td><td>Pembeli</td><td>Lokasi</td><td>Qty</td><td>Total</td><td>Metode</td>';
    echo '</tr>';

    $no = 1;
    foreach($transaksi_list as $trans) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($trans['tanggal'])) . '</td>';
        echo '<td>' . htmlspecialchars($trans['id_transaksi']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['nama_produk']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['kode_produk']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['variasi_info'] ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($trans['pembeli']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['lokasi']) . '</td>';
        echo '<td style="text-align:center;">' . $trans['jumlah'] . 'x</td>';
        echo '<td style="text-align:right;">Rp ' . number_format($trans['total_harga'], 0, ',', '.') . '</td>';
        echo '<td>' . htmlspecialchars($trans['metode_pembayaran']) . '</td>';
        echo '</tr>';
    }

    echo '<tr><td colspan="11"></td></tr>';
    echo '<tr style="background-color:#dbeafe; font-weight:bold;">';
    echo '<td colspan="8">TOTAL KESELURUHAN</td>';
    echo '<td style="text-align:center;">' . number_format($total_produk) . 'x</td>';
    echo '<td colspan="2" style="text-align:right; color:#667eea; font-size:14px;">Rp ' . number_format($total_pendapatan, 0, ',', '.') . '</td>';
    echo '</tr>';

    echo '</table>';
    echo '</body></html>';
    exit();
}

// Generate HTML for PDF/Print
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Toko YPOK</title>
    <style>
        @page { 
            margin: 20mm; 
            size: A4 landscape;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .logo {
            margin-bottom: 10px;
        }
        .logo img {
            max-height: 68px;
            width: auto;
            object-fit: contain;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 5px 0;
        }
        .report-period {
            font-size: 12px;
            color: #666;
        }
        .stats-container {
            display: table;
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #1e3a8a;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
            display: inline-block;
            min-width: 200px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-transfer {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-tunai {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-ewallet {
            background: #e0e7ff;
            color: #4338ca;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 140px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            z-index: 1000;
        }
        .print-button:hover {
            background: #5568d3;
        }
        .close-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            z-index: 1000;
        }
        .close-button:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <button class="close-button no-print" onclick="window.close()">❌ Close</button>
    <button class="print-button no-print" onclick="window.print()">🖨️ Cetak / Download PDF</button>
    
    
    <div class="header">
        ' . ($logoDataUri !== '' ? '<div class="logo"><img src="' . $logoDataUri . '" alt="Logo YPOK"></div>' : '<div class="logo">YPOK</div>') . '
        <div class="company-name">YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</div>
        <div style="font-size: 11px; color: #666; margin-top: 5px;">
            Toko Perlengkapan Karate Resmi<br>
            Jl. Karate No. 123, Jakarta | Tel: (021) 12345678
        </div>
        <div class="report-title">LAPORAN TRANSAKSI TOKO</div>
        <div class="report-period">Periode: ' . $periode_text . '</div>
        <div style="font-size: 10px; color: #999; margin-top: 5px;">
            Dicetak pada: ' . date('d F Y, H:i') . ' WIB
        </div>
    </div>
    
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value">' . number_format($total_transaksi) . '</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value">Rp ' . number_format($total_pendapatan, 0, ',', '.') . '</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Produk Terjual</div>
            <div class="stat-value">' . number_format($total_produk) . '</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Rata-rata</div>
            <div class="stat-value">Rp ' . number_format($rata_rata, 0, ',', '.') . '</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 12%;">TANGGAL</th>
                <th style="width: 15%;">ID TRANSAKSI</th>
                <th style="width: 20%;">PRODUK</th>
                <th style="width: 15%;">PEMBELI</th>
                <th style="width: 8%;" class="text-center">QTY</th>
                <th style="width: 15%;" class="text-right">TOTAL</th>
                <th style="width: 10%;" class="text-center">METODE</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
foreach($transaksi_list as $trans) {
    $metode_class = 'badge-' . strtolower($trans['metode_pembayaran']);
    $html .= '
            <tr>
                <td class="text-center">' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($trans['tanggal'])) . '</td>
                <td>' . htmlspecialchars($trans['id_transaksi']) . '</td>
                <td>
                    <strong>' . htmlspecialchars($trans['nama_produk']) . '</strong><br>
                    <span style="color: #666; font-size: 9px;">' . htmlspecialchars($trans['kode_produk']) . '</span>
                    ' . (!empty($trans['variasi_info']) ? '<br><span style="color: #6366f1; font-size: 9px;">📦 ' . htmlspecialchars($trans['variasi_info']) . '</span>' : '') . '
                </td>
                <td>' . htmlspecialchars($trans['pembeli']) . '<br><span style="color: #666; font-size: 9px;">' . htmlspecialchars($trans['lokasi']) . '</span></td>
                <td class="text-center"><strong>' . $trans['jumlah'] . 'x</strong></td>
                <td class="text-right"><strong>Rp ' . number_format($trans['total_harga'], 0, ',', '.') . '</strong></td>
                <td class="text-center"><span class="badge ' . $metode_class . '">' . htmlspecialchars($trans['metode_pembayaran']) . '</span></td>
            </tr>';
}

$html .= '
        </tbody>
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="5" class="text-right" style="padding: 12px 8px;">TOTAL KESELURUHAN:</td>
                <td class="text-center" style="padding: 12px 8px;">' . number_format($total_produk) . 'x</td>
                <td class="text-right" style="padding: 12px 8px; color: #667eea; font-size: 12px;">Rp ' . number_format($total_pendapatan, 0, ',', '.') . '</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="signature-section">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div style="font-weight: bold; margin-top: 5px;">Ketua YPOK</div>
            <div class="signature-line">' . htmlspecialchars($ketua) . '</div>
        </div>
        <div class="signature-box">
            <div>Dibuat oleh,</div>
            <div style="font-weight: bold; margin-top: 5px;">Administrator</div>
            <div class="signature-line">' . htmlspecialchars($admin) . '</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem YPOK Management</p>
        <p>© ' . date('Y') . ' YPOK - Yayasan Pendidikan Olahraga Karate</p>
    </div>
</body>
</html>';

// If Dompdf is available, use it. Otherwise, display HTML with print button
if ($dompdfAvailable && $format === 'pdf') {
    $options = new $dompdfOptionsClass();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new $dompdfClass($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $filename = 'Laporan_Transaksi_Toko_' . date('Ymd_His') . '.pdf';
    $dompdf->stream($filename, ['Attachment' => false]);
} else {
    // Display HTML that can be printed to PDF
    echo $html;
}
?>
