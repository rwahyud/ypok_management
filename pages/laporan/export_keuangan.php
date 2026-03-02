<?php
require_once '../../config/supabase.php';

// Check if Dompdf is available
$use_dompdf = file_exists('vendor/autoload.php');

if ($use_dompdf) {
    require_once 'vendor/autoload.php';
}

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
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
$query = "SELECT * FROM transaksi
    WHERE DATE(tanggal) BETWEEN :start_date AND :end_date
    ORDER BY tanggal ASC";

$stmt = $pdo->prepare($query);
$stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$transaksi_list = $stmt->fetchAll();

// Calculate totals
$total_pemasukan = 0;
$total_pengeluaran = 0;
$running_saldo = 0;

foreach($transaksi_list as &$trans) {
    if($trans['jenis'] === 'pemasukan') {
        $total_pemasukan += $trans['jumlah'];
        $running_saldo += $trans['jumlah'];
    } else {
        $total_pengeluaran += $trans['jumlah'];
        $running_saldo -= $trans['jumlah'];
    }
    $trans['saldo_running'] = $running_saldo;
}
unset($trans);

$saldo_bersih = $total_pemasukan - $total_pengeluaran;

// Handle CSV Export
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Keuangan_YPOK_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header Laporan
    fputcsv($output, ['LAPORAN KEUANGAN YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Periode', $periode_text], ',');
    fputcsv($output, ['Tanggal Cetak', date('d F Y, H:i') . ' WIB'], ',');
    fputcsv($output, ['Dicetak oleh', $admin], ',');
    fputcsv($output, [''], ',');

    // Statistik
    fputcsv($output, ['RINGKASAN KEUANGAN'], ',');
    fputcsv($output, ['Total Pemasukan', 'Rp ' . number_format($total_pemasukan, 0, ',', '.')], ',');
    fputcsv($output, ['Total Pengeluaran', 'Rp ' . number_format($total_pengeluaran, 0, ',', '.')], ',');
    fputcsv($output, ['Saldo Bersih', 'Rp ' . number_format($saldo_bersih, 0, ',', '.')], ',');
    fputcsv($output, [''], ',');

    // Table header
    fputcsv($output, [
        'No',
        'Tanggal',
        'Keterangan',
        'Kategori',
        'Jenis',
        'Pemasukan (Rp)',
        'Pengeluaran (Rp)',
        'Saldo (Rp)'
    ], ',');

    // Data rows
    if (!empty($transaksi_list)) {
        $no = 1;
        foreach($transaksi_list as $trans) {
            fputcsv($output, [
                $no++,
                date('d/m/Y', strtotime($trans['tanggal'])),
                $trans['keterangan'],
                $trans['kategori'] ?? '-',
                ucfirst($trans['jenis']),
                $trans['jenis'] === 'pemasukan' ? number_format($trans['jumlah'], 0, ',', '.') : '-',
                $trans['jenis'] === 'pengeluaran' ? number_format($trans['jumlah'], 0, ',', '.') : '-',
                number_format($trans['saldo_running'], 0, ',', '.')
            ], ',');
        }
    } else {
        fputcsv($output, ['', '', 'Tidak ada data transaksi', '', '', '', '', ''], ',');
    }

    // Summary
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['TOTAL'], ',');
    fputcsv($output, ['Total Pemasukan', 'Rp ' . number_format($total_pemasukan, 0, ',', '.')], ',');
    fputcsv($output, ['Total Pengeluaran', 'Rp ' . number_format($total_pengeluaran, 0, ',', '.')], ',');
    fputcsv($output, ['Saldo Bersih', 'Rp ' . number_format($saldo_bersih, 0, ',', '.')], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Mengetahui,', '', '', '', '', '', 'Dibuat oleh,'], ',');
    fputcsv($output, ['Ketua YPOK', '', '', '', '', '', 'Administrator'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, [$ketua, '', '', '', '', '', $admin], ',');

    fclose($output);
    exit();

} elseif ($format === 'excel') {
    // Excel Export (simple HTML table that Excel can open)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Keuangan_YPOK_' . date('Ymd_His') . '.xls"');

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr><td colspan="8" style="text-align:center; font-weight:bold; font-size:16px;">LAPORAN KEUANGAN YPOK</td></tr>';
    echo '<tr><td colspan="8" style="text-align:center;">Periode: ' . $periode_text . '</td></tr>';
    echo '<tr><td colspan="8" style="text-align:center;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</td></tr>';
    echo '<tr><td colspan="8"></td></tr>';

    // Statistik
    echo '<tr style="background-color:#f0f9ff; font-weight:bold;"><td colspan="8">RINGKASAN KEUANGAN</td></tr>';
    echo '<tr><td colspan="3">Total Pemasukan</td><td colspan="5" style="text-align:right; color:#10b981; font-weight:bold;">Rp ' . number_format($total_pemasukan, 0, ',', '.') . '</td></tr>';
    echo '<tr><td colspan="3">Total Pengeluaran</td><td colspan="5" style="text-align:right; color:#ef4444; font-weight:bold;">Rp ' . number_format($total_pengeluaran, 0, ',', '.') . '</td></tr>';
    echo '<tr><td colspan="3">Saldo Bersih</td><td colspan="5" style="text-align:right; color:#3b82f6; font-weight:bold;">Rp ' . number_format($saldo_bersih, 0, ',', '.') . '</td></tr>';
    echo '<tr><td colspan="8"></td></tr>';

    // Table header
    echo '<tr style="background-color:#3b82f6; color:white; font-weight:bold;">';
    echo '<td>No</td><td>Tanggal</td><td>Keterangan</td><td>Kategori</td><td>Jenis</td><td>Pemasukan</td><td>Pengeluaran</td><td>Saldo</td>';
    echo '</tr>';

    $no = 1;
    foreach($transaksi_list as $trans) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($trans['tanggal'])) . '</td>';
        echo '<td>' . htmlspecialchars($trans['keterangan']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['kategori'] ?? '-') . '</td>';
        echo '<td>' . ucfirst($trans['jenis']) . '</td>';
        echo '<td style="text-align:right; color:#10b981; font-weight:bold;">' . ($trans['jenis'] === 'pemasukan' ? 'Rp ' . number_format($trans['jumlah'], 0, ',', '.') : '-') . '</td>';
        echo '<td style="text-align:right; color:#ef4444; font-weight:bold;">' . ($trans['jenis'] === 'pengeluaran' ? 'Rp ' . number_format($trans['jumlah'], 0, ',', '.') : '-') . '</td>';
        echo '<td style="text-align:right;">Rp ' . number_format($trans['saldo_running'], 0, ',', '.') . '</td>';
        echo '</tr>';
    }

    echo '<tr><td colspan="8"></td></tr>';
    echo '<tr style="background-color:#dbeafe; font-weight:bold;">';
    echo '<td colspan="5">TOTAL KESELURUHAN</td>';
    echo '<td style="text-align:right; color:#10b981;">Rp ' . number_format($total_pemasukan, 0, ',', '.') . '</td>';
    echo '<td style="text-align:right; color:#ef4444;">Rp ' . number_format($total_pengeluaran, 0, ',', '.') . '</td>';
    echo '<td style="text-align:right; color:#3b82f6; font-size:14px;">Rp ' . number_format($saldo_bersih, 0, ',', '.') . '</td>';
    echo '</tr>';

    echo '<tr><td colspan="8"></td></tr>';
    echo '<tr><td colspan="8"></td></tr>';
    echo '<tr><td colspan="4" style="text-align:center;">Mengetahui,<br>Ketua YPOK<br><br><br><br>' . htmlspecialchars($ketua) . '</td>';
    echo '<td colspan="4" style="text-align:center;">Dibuat oleh,<br>Administrator<br><br><br><br>' . htmlspecialchars($admin) . '</td></tr>';

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
    <title>Laporan Keuangan YPOK</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
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
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 48px;
            margin-bottom: 10px;
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
            width: 33.33%;
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
        }
        .income { color: #10b981; }
        .expense { color: #ef4444; }
        .balance { color: #3b82f6; }
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
            text-align: left;
        }
        .text-center {
            text-align: left;
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
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-pemasukan {
            background: #d1fae5;
            color: #059669;
        }
        .badge-pengeluaran {
            background: #fee2e2;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <button class="close-button no-print" onclick="window.close()">❌ Close</button>
    <button class="print-button no-print" onclick="window.print()">🖨️ Cetak / Download PDF</button>


    <div class="header">
        <img src="../../assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo" class="logo" style="width: 60px; height: 60px; object-fit: contain; margin: 0 auto;">
        <div class="company-name">YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</div>
        <div style="font-size: 11px; color: #666; margin-top: 5px;">
            Laporan Keuangan Resmi
        </div>
        <div class="report-title">LAPORAN KEUANGAN</div>
        <div class="report-period">Periode: ' . $periode_text . '</div>
        <div style="font-size: 10px; color: #999; margin-top: 5px;">
            Dicetak pada: ' . date('d F Y, H:i') . ' WIB
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-label">Total Pemasukan</div>
            <div class="stat-value income">Rp ' . number_format($total_pemasukan, 0, ',', '.') . '</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Pengeluaran</div>
            <div class="stat-value expense">Rp ' . number_format($total_pengeluaran, 0, ',', '.') . '</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Saldo Bersih</div>
            <div class="stat-value balance">Rp ' . number_format($saldo_bersih, 0, ',', '.') . '</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 12%;">TANGGAL</th>
                <th style="width: 10%;">KETERANGAN</th>
                <th style="width: 10%;">KATEGORI</th>
                <th style="width: 8%;" class="text-left">JENIS</th>
                <th style="width: 10%;" class="text-left">PEMASUKAN</th>
                <th style="width: 10%;" class="text-left">PENGELUARAN</th>
                <th style="width: 10%;" class="text-left">SALDO</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
foreach($transaksi_list as $trans) {
    $html .= '
            <tr>
                <td class="text-center">' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($trans['tanggal'])) . '</td>
                <td>' . htmlspecialchars($trans['keterangan']) . '</td>
                <td>' . htmlspecialchars($trans['kategori'] ?? '-') . '</td>
                <td class="text-center"><span class="badge badge-' . $trans['jenis'] . '">' . ucfirst($trans['jenis']) . '</span></td>
                <td class="text-right income"><strong>' . ($trans['jenis'] === 'pemasukan' ? 'Rp ' . number_format($trans['jumlah'], 0, ',', '.') : '-') . '</strong></td>
                <td class="text-right expense"><strong>' . ($trans['jenis'] === 'pengeluaran' ? 'Rp ' . number_format($trans['jumlah'], 0, ',', '.') : '-') . '</strong></td>
                <td class="text-right">Rp ' . number_format($trans['saldo_running'], 0, ',', '.') . '</td>
            </tr>';
}

$html .= '
        </tbody>
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="5" class="text-right" style="padding: 12px 8px;">TOTAL KESELURUHAN:</td>
                <td class="text-right income" style="padding: 12px 8px; font-size: 12px;">Rp ' . number_format($total_pemasukan, 0, ',', '.') . '</td>
                <td class="text-right expense" style="padding: 12px 8px; font-size: 12px;">Rp ' . number_format($total_pengeluaran, 0, ',', '.') . '</td>
                <td class="text-right balance" style="padding: 12px 8px; font-size: 12px;">Rp ' . number_format($saldo_bersih, 0, ',', '.') . '</td>
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
if ($use_dompdf && $format === 'pdf') {
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = 'Laporan_Keuangan_YPOK_' . date('Ymd_His') . '.pdf';
    $dompdf->stream($filename, ['Attachment' => false]);
} else {
    // Display HTML that can be printed to PDF
    echo $html;
}
?>
