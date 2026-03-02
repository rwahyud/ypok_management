<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get parameters
$format = $_GET['format'] ?? 'pdf';
$periode = $_GET['periode'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$ketua = $_GET['ketua'] ?? 'Ketua YPOK';
$admin = $_GET['admin'] ?? $_SESSION['nama_lengkap'] ?? 'Administrator';

// Ensure admin is not null
if (empty($admin) || $admin === 'null') {
    $admin = 'Administrator';
}

// Calculate date range based on periode
$where = "1=1";
$params = [];
$periode_text = "Semua Data";

if ($periode === 'month') {
    $where = "EXTRACT(MONTH FROM tanggal_kegiatan) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM tanggal_kegiatan) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $periode_text = "Bulan " . date('F Y');
} elseif ($periode === 'last_month') {
    $where = "MONTH(tanggal_kegiatan) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(tanggal_kegiatan) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
    $periode_text = "Bulan " . date('F Y', strtotime('-1 month'));
} elseif ($periode === 'custom' && $start_date && $end_date) {
    $where = "DATE(tanggal_kegiatan) BETWEEN :start_date AND :end_date";
    $params = ['start_date' => $start_date, 'end_date' => $end_date];
    $periode_text = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
}

// Query kegiatan
$query = "SELECT k.*, l.nama_lokasi, l.alamat
    FROM kegiatan k
    LEFT JOIN lokasi l ON k.lokasi_id = l.id
    WHERE $where
    ORDER BY k.tanggal_kegiatan DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$kegiatan_list = $stmt->fetchAll();

// Calculate statistics
$total_kegiatan = count($kegiatan_list);
$selesai = count(array_filter($kegiatan_list, fn($k) => $k['status'] === 'selesai'));
$dijadwalkan = count(array_filter($kegiatan_list, fn($k) => $k['status'] === 'dijadwalkan'));
$dibatalkan = count(array_filter($kegiatan_list, fn($k) => $k['status'] === 'dibatalkan'));

// Handle CSV Export
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Kegiatan_YPOK_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Header
    fputcsv($output, ['LAPORAN KEGIATAN YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)']);
    fputcsv($output, ['']);
    fputcsv($output, ['Periode', $periode_text]);
    fputcsv($output, ['Tanggal Cetak', date('d F Y, H:i') . ' WIB']);
    fputcsv($output, ['Dicetak oleh', $admin]);
    fputcsv($output, ['Total Kegiatan', $total_kegiatan]);
    fputcsv($output, ['']);
    
    // Table header
    fputcsv($output, ['No', 'Tanggal', 'Nama Kegiatan', 'Kategori', 'Lokasi', 'PIC', 'Status']);
    
    // Data rows
    $no = 1;
    foreach($kegiatan_list as $k) {
        $status_text = $k['status'] === 'selesai' ? 'Selesai' : 
                      ($k['status'] === 'dijadwalkan' ? 'Dijadwalkan' : 
                      ($k['status'] === 'berlangsung' ? 'Berlangsung' : 'Dibatalkan'));
        fputcsv($output, [
            $no++,
            date('d/m/Y', strtotime($k['tanggal_kegiatan'])),
            $k['nama_kegiatan'],
            $k['jenis_kegiatan'],
            $k['nama_lokasi'] ?? '-',
            $k['pic'] ?? '-',
            $status_text
        ]);
    }
    
    // Summary
    fputcsv($output, ['']);
    fputcsv($output, ['RINGKASAN']);
    fputcsv($output, ['Total Kegiatan', $total_kegiatan]);
    fputcsv($output, ['Selesai', $selesai]);
    fputcsv($output, ['Dijadwalkan', $dijadwalkan]);
    fputcsv($output, ['Dibatalkan', $dibatalkan]);
    
    fputcsv($output, ['']);
    fputcsv($output, ['Mengetahui Ketua YPOK', '', 'Pembuat Laporan']);
    fputcsv($output, ['']);
    fputcsv($output, [$ketua, '', $admin]);
    
    fclose($output);
    exit();
    
} elseif ($format === 'excel') {
    // Excel Export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Kegiatan_YPOK_' . date('Ymd_His') . '.xls"');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr><td colspan="7" style="text-align:center; font-weight:bold; font-size:16px;">LAPORAN KEGIATAN YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</td></tr>';
    echo '<tr><td colspan="7" style="text-align:center;">Periode: ' . $periode_text . '</td></tr>';
    echo '<tr><td colspan="7" style="text-align:center;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</td></tr>';
    echo '<tr><td colspan="7"></td></tr>';
    echo '<tr style="background-color:#667eea; color:white; font-weight:bold;">';
    echo '<td>No</td><td>Tanggal</td><td>Nama Kegiatan</td><td>Kategori</td><td>Lokasi</td><td>PIC</td><td>Status</td>';
    echo '</tr>';
    
    $no = 1;
    foreach($kegiatan_list as $k) {
        $status_text = $k['status'] === 'selesai' ? 'Selesai' : 
                      ($k['status'] === 'dijadwalkan' ? 'Dijadwalkan' : 
                      ($k['status'] === 'berlangsung' ? 'Berlangsung' : 'Dibatalkan'));
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($k['tanggal_kegiatan'])) . '</td>';
        echo '<td>' . htmlspecialchars($k['nama_kegiatan']) . '</td>';
        echo '<td>' . htmlspecialchars($k['jenis_kegiatan']) . '</td>';
        echo '<td>' . htmlspecialchars($k['nama_lokasi'] ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($k['pic'] ?? '-') . '</td>';
        echo '<td>' . $status_text . '</td>';
        echo '</tr>';
    }
    
    echo '<tr><td colspan="7"></td></tr>';
    echo '<tr style="background-color:#f0f9ff; font-weight:bold;">';
    echo '<td colspan="2">RINGKASAN</td>';
    echo '<td colspan="5">Total: ' . $total_kegiatan . ' | Selesai: ' . $selesai . ' | Dijadwalkan: ' . $dijadwalkan . ' | Dibatalkan: ' . $dibatalkan . '</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="7"></td></tr>';
    echo '<tr><td colspan="3" style="text-align:center;">Mengetahui Ketua YPOK</td><td></td><td colspan="3" style="text-align:center;">Pembuat Laporan</td></tr>';
    echo '<tr><td colspan="3" style="height:60px;"></td><td></td><td colspan="3"></td></tr>';
    echo '<tr><td colspan="3" style="text-align:center; font-weight:bold;">' . htmlspecialchars($ketua) . '</td><td></td><td colspan="3" style="text-align:center; font-weight:bold;">' . htmlspecialchars($admin) . '</td></tr>';
    
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
    <title>Laporan Kegiatan YPOK</title>
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
            position: relative;
        }
        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }
        .header-logo img {
            height: 80px;
            width: auto;
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
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
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
    <div class="action-buttons no-print">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ Print / Simpan PDF
        </button>
        <button class="btn btn-cancel" onclick="window.close()">
            ✖️ Tutup
        </button>
    </div>

    <div class="header">
        <div class="header-logo">
            <img src="../assets/images/logo ypok .jpg" alt="Logo YPOK">
            <div>
                <h1>YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</h1>
                <h2>LAPORAN KEGIATAN</h2>
            </div>
        </div>
        <p>Periode: <?php echo $periode_text; ?></p>
        <p>Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Total Kegiatan:</span>
            <span><?php echo $total_kegiatan; ?> kegiatan</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dicetak oleh:</span>
            <span><?php echo htmlspecialchars($admin); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Selesai:</span>
            <span><?php echo $selesai; ?> kegiatan</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dijadwalkan:</span>
            <span><?php echo $dijadwalkan; ?> kegiatan</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Kegiatan</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>PIC</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($kegiatan_list)): ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data kegiatan pada periode ini</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach($kegiatan_list as $k): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($k['tanggal_kegiatan'])); ?></td>
                    <td><?php echo htmlspecialchars($k['nama_kegiatan']); ?></td>
                    <td><?php echo htmlspecialchars($k['jenis_kegiatan']); ?></td>
                    <td><?php echo htmlspecialchars($k['nama_lokasi'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($k['pic'] ?? '-'); ?></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $k['status'] === 'selesai' ? 'success' : 
                                ($k['status'] === 'dijadwalkan' ? 'warning' : 
                                ($k['status'] === 'berlangsung' ? 'warning' : 'danger')); 
                        ?>">
                            <?php 
                            echo $k['status'] === 'selesai' ? 'Selesai' : 
                                ($k['status'] === 'dijadwalkan' ? 'Dijadwalkan' : 
                                ($k['status'] === 'berlangsung' ? 'Berlangsung' : 'Dibatalkan'));
                            ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <h3 style="margin-top: 0;">Ringkasan Kegiatan</h3>
        <div class="summary-row">
            <span>Total Kegiatan:</span>
            <span style="font-weight: bold;"><?php echo $total_kegiatan; ?> kegiatan</span>
        </div>
        <div class="summary-row">
            <span>Selesai:</span>
            <span style="font-weight: bold; color: #059669;"><?php echo $selesai; ?> kegiatan</span>
        </div>
        <div class="summary-row">
            <span>Dijadwalkan:</span>
            <span style="font-weight: bold; color: #d97706;"><?php echo $dijadwalkan; ?> kegiatan</span>
        </div>
        <div class="summary-row">
            <span>Dibatalkan:</span>
            <span style="font-weight: bold; color: #dc2626;"><?php echo $dibatalkan; ?> kegiatan</span>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>Mengetahui,<br><strong>Ketua YPOK</strong></p>
            <div class="signature-name">
                <?php echo htmlspecialchars($ketua); ?>
            </div>
        </div>
        <div class="signature-box">
            <p>Dibuat oleh,<br><strong>Administrator</strong></p>
            <div class="signature-name">
                <?php echo htmlspecialchars($admin); ?>
            </div>
        </div>
    </div>
</body>
</html>
