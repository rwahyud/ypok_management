<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get form data
$format = $_GET['format'] ?? 'pdf';
$type = $_GET['type'] ?? 'kohai'; // msh or kohai
$periode = $_GET['periode'] ?? 'month';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$ketua = $_GET['ketua'] ?? 'Ketua YPOK';
$admin = $_GET['admin'] ?? $_SESSION['nama_lengkap'];

// Build query based on periode
$where = "1=1";
$params = [];
$periode_text = "Semua Data";

if ($periode === 'month') {
    $where = "EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $periode_text = "Bulan " . date('F Y');
} elseif ($periode === 'last_month') {
    $where = "EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month')";
    $periode_text = "Bulan " . date('F Y', strtotime('-1 month'));
} elseif ($periode === 'custom' && $start_date && $end_date) {
    $where = "DATE(created_at) BETWEEN :start_date AND :end_date";
    $params = ['start_date' => $start_date, 'end_date' => $end_date];
    $periode_text = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
}

// Get data based on type
if ($type === 'msh') {
    $sql = "SELECT * FROM pendaftaran_msh WHERE $where ORDER BY created_at DESC";
    $table_name = "Majelis Sabuk Hitam (MSH)";
    $emoji = "👨‍🎓";
} else {
    $sql = "SELECT * FROM pendaftaran_kohai WHERE $where ORDER BY created_at DESC";
    $table_name = "Kohai";
    $emoji = "👧";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data_list = $stmt->fetchAll();

// Calculate statistics
$total_data = count($data_list);
$status_counts = array_count_values(array_column($data_list, 'status'));

// Handle CSV Export
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Pendaftaran_' . strtoupper($type) . '_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Header Laporan
    fputcsv($output, ['LAPORAN PENDAFTARAN ' . strtoupper($table_name) . ' - YPOK'], ',');
    fputcsv($output, [''], ',');
    fputcsv($output, ['Periode', $periode_text], ',');
    fputcsv($output, ['Tanggal Cetak', date('d F Y, H:i') . ' WIB'], ',');
    fputcsv($output, ['Dicetak oleh', $admin], ',');
    fputcsv($output, ['Total Data', $total_data . ' pendaftaran'], ',');
    fputcsv($output, [''], ',');
    
    // Table header
    if ($type === 'msh') {
        fputcsv($output, ['No', 'No MSH', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'JK', 'Tingkat Dan', 'Dojo/Cabang', 'No. Telepon', 'Email', 'Alamat', 'Status', 'Tanggal Daftar'], ',');
        
        $no = 1;
        foreach($data_list as $d) {
            fputcsv($output, [
                $no++,
                $d['no_msh'],
                $d['nama'],
                $d['tempat_lahir'],
                date('d/m/Y', strtotime($d['tanggal_lahir'])),
                $d['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
                $d['tingkat_dan'],
                $d['dojo_cabang'],
                $d['no_telp'],
                $d['email'] ?? '-',
                $d['alamat'],
                $d['status'],
                date('d/m/Y H:i', strtotime($d['created_at']))
            ], ',');
        }
    } else {
        fputcsv($output, ['No', 'No Kohai', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'JK', 'No. Telepon', 'Email', 'Alamat', 'Nama Wali', 'No. Telepon Wali', 'Status', 'Tanggal Daftar'], ',');
        
        $no = 1;
        foreach($data_list as $d) {
            fputcsv($output, [
                $no++,
                $d['kode_kohai'],
                $d['nama'],
                $d['tempat_lahir'],
                date('d/m/Y', strtotime($d['tanggal_lahir'])),
                $d['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
                $d['no_telp'],
                $d['email'] ?? '-',
                $d['alamat'],
                $d['nama_wali'],
                $d['no_telp_wali'],
                $d['status'],
                date('d/m/Y H:i', strtotime($d['created_at']))
            ], ',');
        }
    }
    
    // Summary
    fputcsv($output, [''], ',');
    fputcsv($output, ['RINGKASAN'], ',');
    fputcsv($output, ['Total Pendaftaran', $total_data . ' orang'], ',');
    foreach($status_counts as $status => $count) {
        fputcsv($output, ['Status ' . $status, $count . ' orang'], ',');
    }
    

    
    fclose($output);
    exit();
    
} elseif ($format === 'excel') {
    // Excel Export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Pendaftaran_' . strtoupper($type) . '_' . date('Ymd_His') . '.xls"');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<tr><td colspan="13" style="text-align:center; font-weight:bold; font-size:16px;">LAPORAN PENDAFTARAN ' . strtoupper($table_name) . ' - YPOK</td></tr>';
    echo '<tr><td colspan="13" style="text-align:center;">Periode: ' . $periode_text . '</td></tr>';
    echo '<tr><td colspan="13" style="text-align:center;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</td></tr>';
    echo '<tr><td colspan="13"></td></tr>';
    
    if ($type === 'msh') {
        echo '<tr style="background-color:#667eea; color:white; font-weight:bold;">';
        echo '<td>No</td><td>No MSH</td><td>Nama</td><td>Tempat Lahir</td><td>Tanggal Lahir</td>';
        echo '<td>JK</td><td>Tingkat Dan</td><td>Dojo</td><td>Telepon</td><td>Email</td>';
        echo '<td>Alamat</td><td>Status</td><td>Tanggal Daftar</td></tr>';
        
        $no = 1;
        foreach($data_list as $d) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . htmlspecialchars($d['no_msh']) . '</td>';
            echo '<td>' . htmlspecialchars($d['nama']) . '</td>';
            echo '<td>' . htmlspecialchars($d['tempat_lahir']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['tanggal_lahir'])) . '</td>';
            echo '<td>' . ($d['jenis_kelamin'] == 'L' ? 'L' : 'P') . '</td>';
            echo '<td>' . htmlspecialchars($d['tingkat_dan']) . '</td>';
            echo '<td>' . htmlspecialchars($d['dojo_cabang']) . '</td>';
            echo '<td>' . htmlspecialchars($d['no_telp']) . '</td>';
            echo '<td>' . htmlspecialchars($d['email'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($d['alamat']) . '</td>';
            echo '<td>' . htmlspecialchars($d['status']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['created_at'])) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr style="background-color:#667eea; color:white; font-weight:bold;">';
        echo '<td>No</td><td>No Kohai</td><td>Nama</td><td>Tempat Lahir</td><td>Tanggal Lahir</td>';
        echo '<td>JK</td><td>Telepon</td><td>Email</td><td>Alamat</td>';
        echo '<td>Nama Wali</td><td>Telepon Wali</td><td>Status</td><td>Tanggal Daftar</td></tr>';
        
        $no = 1;
        foreach($data_list as $d) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . htmlspecialchars($d['kode_kohai']) . '</td>';
            echo '<td>' . htmlspecialchars($d['nama']) . '</td>';
            echo '<td>' . htmlspecialchars($d['tempat_lahir']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['tanggal_lahir'])) . '</td>';
            echo '<td>' . ($d['jenis_kelamin'] == 'L' ? 'L' : 'P') . '</td>';
            echo '<td>' . htmlspecialchars($d['no_telp']) . '</td>';
            echo '<td>' . htmlspecialchars($d['email'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($d['alamat']) . '</td>';
            echo '<td>' . htmlspecialchars($d['nama_wali']) . '</td>';
            echo '<td>' . htmlspecialchars($d['no_telp_wali']) . '</td>';
            echo '<td>' . htmlspecialchars($d['status']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['created_at'])) . '</td>';
            echo '</tr>';
        }
    }
    
    echo '<tr><td colspan="13"></td></tr>';
    echo '<tr style="background-color:#f0f9ff; font-weight:bold;">';
    echo '<td colspan="13">RINGKASAN: Total ' . $total_data . ' pendaftaran</td></tr>';
    
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
    <title>Laporan Pendaftaran <?php echo $table_name; ?> - YPOK</title>
    <style>
        @page { margin: 20mm; size: A4 <?php echo $type === 'msh' ? 'landscape' : 'portrait'; ?>; }
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
            margin: 5px 0;;
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
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background:#1e3a8a;
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
            font-size: 9px;
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
            ✖️ Close
        </button>
    </div>

    <div class="header">
        <h1>YAYASAN PENDIDIKAN OLAHRAGA KARATE (YPOK)</h1>
        <h2><?php echo $emoji; ?> LAPORAN PENDAFTARAN <?php echo strtoupper($table_name); ?></h2>
        <p>Periode: <?php echo $periode_text; ?></p>
        <p>Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Total Data:</span>
            <span><?php echo $total_data; ?> pendaftaran</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dicetak oleh:</span>
            <span><?php echo htmlspecialchars($admin); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <?php if ($type === 'msh'): ?>
                    <th style="width: 30px;">No</th>
                    <th>No MSH</th>
                    <th>Nama Lengkap</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>JK</th>
                    <th>Tingkat Dan</th>
                    <th>Dojo/Cabang</th>
                    <th>No. Telepon</th>
                    <th>Status</th>
                    <th>Tgl Daftar</th>
                <?php else: ?>
                    <th style="width: 30px;">No</th>
                    <th>No Kohai</th>
                    <th>Nama Lengkap</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>JK</th>
                    <th>No. Telepon</th>
                    <th>Nama Wali</th>
                    <th>Telp Wali</th>
                    <th>Status</th>
                    <th>Tgl Daftar</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data_list)): ?>
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data pendaftaran pada periode ini</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach($data_list as $d): ?>
                <tr>
                    <?php if ($type === 'msh'): ?>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($d['no_msh']); ?></td>
                        <td><?php echo htmlspecialchars($d['nama']); ?></td>
                        <td><?php echo htmlspecialchars($d['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($d['tanggal_lahir'])); ?></td>
                        <td class="text-center"><?php echo $d['jenis_kelamin']; ?></td>
                        <td><?php echo htmlspecialchars($d['tingkat_dan']); ?></td>
                        <td><?php echo htmlspecialchars($d['dojo_cabang']); ?></td>
                        <td><?php echo htmlspecialchars($d['no_telp']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $d['status'] == 'Aktif' ? 'success' : 
                                    ($d['status'] == 'Pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $d['status']; ?>
                            </span>
                        </td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></td>
                    <?php else: ?>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($d['kode_kohai']); ?></td>
                        <td><?php echo htmlspecialchars($d['nama']); ?></td>
                        <td><?php echo htmlspecialchars($d['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($d['tanggal_lahir'])); ?></td>
                        <td class="text-center"><?php echo $d['jenis_kelamin']; ?></td>
                        <td><?php echo htmlspecialchars($d['no_telp']); ?></td>
                        <td><?php echo htmlspecialchars($d['nama_wali']); ?></td>
                        <td><?php echo htmlspecialchars($d['no_telp_wali']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $d['status'] == 'Aktif' ? 'success' : 
                                    ($d['status'] == 'Pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $d['status']; ?>
                            </span>
                        </td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <h3 style="margin-top: 0;">Ringkasan Pendaftaran</h3>
        <div class="summary-row">
            <span>Total Pendaftaran:</span>
            <span style="font-weight: bold;"><?php echo $total_data; ?> orang</span>
        </div>
        <?php foreach($status_counts as $status => $count): ?>
        <div class="summary-row">
            <span>Status <?php echo $status; ?>:</span>
            <span style="font-weight: bold;"><?php echo $count; ?> orang</span>
        </div>
        <?php endforeach; ?>
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
