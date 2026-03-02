<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get parameters dari URL atau POST
$nomor_surat = $_GET['nomor'] ?? date('d') . '/YPOK-PP/VI/' . date('Y');
$lampiran = $_GET['lampiran'] ?? '-';
$hal = $_GET['hal'] ?? 'Surat Kegiatan';
$tujuan = $_GET['tujuan'] ?? 'Ketua/Pengurus Panitia Pelaksana';
$organisasi = $_GET['organisasi'] ?? '';
$isi_surat = $_GET['isi'] ?? '';
$penandatangan_nama = $_GET['ttd_nama'] ?? 'Ketua Umum YPOK';
$penandatangan_jabatan = $_GET['ttd_jabatan'] ?? 'Ketua Umum';
$tanggal_surat = $_GET['tanggal'] ?? date('d F Y');

// Jika ada ID kegiatan, ambil data kegiatan
$kegiatan_id = $_GET['kegiatan_id'] ?? null;
if ($kegiatan_id) {
    $stmt = $pdo->prepare("
        SELECT k.*, l.nama_lokasi, l.alamat as alamat_lokasi
        FROM kegiatan k
        LEFT JOIN lokasi l ON k.lokasi_id = l.id
        WHERE k.id = ?
    ");
    $stmt->execute([$kegiatan_id]);
    $kegiatan = $stmt->fetch();
    
    if ($kegiatan) {
        // Generate content berdasarkan kegiatan
        $hal = 'Undangan ' . $kegiatan['jenis_kegiatan'];
        $isi_surat = "
        <p>Dengan hormat,</p>
        
        <p>Sehubungan dengan akan dilaksanakannya kegiatan <strong>{$kegiatan['nama_kegiatan']}</strong>, 
        maka kami mengundang Bapak/Ibu untuk hadir pada:</p>
        
        <table style='margin-left: 40px; margin-top: 20px; margin-bottom: 20px;'>
            <tr>
                <td style='width: 150px; padding: 5px 0;'>Hari/Tanggal</td>
                <td style='width: 20px;'>:</td>
                <td>" . date('l, d F Y', strtotime($kegiatan['tanggal_kegiatan'])) . "</td>
            </tr>
            <tr>
                <td style='padding: 5px 0;'>Waktu</td>
                <td>:</td>
                <td>" . ($kegiatan['waktu_mulai'] ? date('H:i', strtotime($kegiatan['waktu_mulai'])) . ' WIB - Selesai' : 'Akan ditentukan kemudian') . "</td>
            </tr>
            <tr>
                <td style='padding: 5px 0;'>Tempat</td>
                <td>:</td>
                <td>" . ($kegiatan['nama_lokasi'] ?? $kegiatan['alamat'] ?? 'Akan ditentukan kemudian') . "</td>
            </tr>
            <tr>
                <td style='padding: 5px 0;'>Acara</td>
                <td>:</td>
                <td>{$kegiatan['nama_kegiatan']}</td>
            </tr>
        </table>
        
        <p>Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadiran Bapak/Ibu, 
        kami ucapkan terima kasih.</p>
        ";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Resmi YPOK - <?php echo htmlspecialchars($hal); ?></title>
    <style>
        @page { 
            margin: 15mm 20mm; 
            size: A4 portrait; 
        }
        
        @media print {
            body { 
                margin: 0; 
                padding: 0; 
            }
            .action-buttons, .no-print { 
                display: none !important; 
            }
            .page-break {
                page-break-after: always;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: white;
            padding: 20px;
        }
        
        .page-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 0;
        }
        
        /* Header/Kop Surat */
        .letterhead {
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .letterhead-inner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
        }
        
        .letterhead-logo {
            flex-shrink: 0;
        }
        
        .letterhead-logo img {
            height: 85px;
            width: auto;
        }
        
        .letterhead-content {
            flex: 1;
            text-align: center;
        }
        
        .letterhead-title {
            font-size: 18pt;
            font-weight: bold;
            color: #5b6b9e;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        
        .letterhead-subtitle {
            font-size: 14pt;
            font-weight: bold;
            color: #5b6b9e;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .letterhead-address {
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
        }
        
        .letterhead-logo-right {
            flex-shrink: 0;
        }
        
        .letterhead-logo-right img {
            height: 85px;
            width: auto;
        }
        
        /* Nomor Surat Section */
        .letter-info {
            margin: 25px 0;
            font-size: 12pt;
        }
        
        .letter-info-row {
            display: flex;
            margin-bottom: 3px;
        }
        
        .letter-info-label {
            width: 100px;
            flex-shrink: 0;
        }
        
        .letter-info-separator {
            width: 20px;
            text-align: center;
        }
        
        .letter-info-value {
            flex: 1;
        }
        
        .letter-date {
            text-align: right;
            margin-bottom: 25px;
            font-size: 12pt;
        }
        
        /* Recipient */
        .letter-recipient {
            margin-bottom: 25px;
            font-size: 12pt;
        }
        
        /* Content */
        .letter-content {
            text-align: justify;
            font-size: 12pt;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .letter-content p {
            margin-bottom: 15px;
            text-indent: 40px;
        }
        
        .letter-content p:first-child {
            text-indent: 0;
        }
        
        .letter-content table {
            margin: 15px 0;
            border-collapse: collapse;
        }
        
        .letter-content strong {
            font-weight: bold;
        }
        
        /* Signature */
        .letter-signature {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 250px;
        }
        
        .signature-box p {
            margin: 5px 0;
        }
        
        .signature-space {
            height: 80px;
        }
        
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }
        
        .signature-title {
            font-size: 11pt;
        }
        
        /* Action Buttons */
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
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
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

    <div class="page-container">
        <!-- Kop Surat -->
        <div class="letterhead">
            <div class="letterhead-inner">
                <div class="letterhead-logo">
                    <img src="../../assets/images/logo ypok .jpg" alt="Logo YPOK">
                </div>
                
                <div class="letterhead-content">
                    <div class="letterhead-title">YAYASAN PENDIDIKAN OLAHRAGA KARATE</div>
                    <div class="letterhead-subtitle">PENGURUS PUSAT</div>
                    <div class="letterhead-address">
                        Menara Cakrawala - Sky Building Lt 12, Unit 05A (Infiniti Office)<br>
                        Jl. M.H. Thamrin No.9, Rt 002/001, Kel Kebon Sirih, Kec. Menteng, Jakarta Pusat, Kode Pos 10340<br>
                        yayasanpendorkarate@gmail.com | 0851-7313-2266
                    </div>
                </div>
                
                <div class="letterhead-logo-right">
                    <img src="uploads/msh/1772373554_ypok kormi .jpg" alt="Logo KORMI">
                </div>
            </div>
        </div>
        
        <!-- Nomor Surat -->
        <div class="letter-info">
            <div class="letter-info-row">
                <div class="letter-info-label">Nomor</div>
                <div class="letter-info-separator">:</div>
                <div class="letter-info-value"><?php echo htmlspecialchars($nomor_surat); ?></div>
            </div>
            <div class="letter-info-row">
                <div class="letter-info-label">Lampiran</div>
                <div class="letter-info-separator">:</div>
                <div class="letter-info-value"><?php echo htmlspecialchars($lampiran); ?></div>
            </div>
            <div class="letter-info-row">
                <div class="letter-info-label">Hal</div>
                <div class="letter-info-separator">:</div>
                <div class="letter-info-value"><strong><?php echo htmlspecialchars($hal); ?></strong></div>
            </div>
        </div>
        
        <!-- Tanggal & Tempat -->
        <div class="letter-date">
            Jakarta, <?php echo $tanggal_surat; ?>
        </div>
        
        <!-- Kepada Yth -->
        <div class="letter-recipient">
            Kepada Yth,<br>
            <strong><?php echo htmlspecialchars($tujuan); ?></strong>
            <?php if($organisasi): ?>
            <br><?php echo htmlspecialchars($organisasi); ?>
            <?php endif; ?>
        </div>
        
        <!-- Isi Surat -->
        <div class="letter-content">
            <?php echo $isi_surat; ?>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="letter-signature">
            <div class="signature-box">
                <p><?php echo htmlspecialchars($penandatangan_jabatan); ?>,</p>
                <div class="signature-space"></div>
                <p class="signature-name"><?php echo htmlspecialchars($penandatangan_nama); ?></p>
                <p class="signature-title"><?php echo htmlspecialchars($penandatangan_jabatan); ?></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-adjust content untuk print
        window.onload = function() {
            // Check if print preview
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>
</html>
