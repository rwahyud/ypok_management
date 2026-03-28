<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get payment ID from URL
if(!isset($_GET['id'])) {
    header('Location: ../pages/pembayaran.php');
    exit();
}

$id = $_GET['id'];

// Get payment data
$stmt = $pdo->prepare("SELECT p.* FROM pembayaran p WHERE p.id = ?");
$stmt->execute([$id]);
$pembayaran = $stmt->fetch();

if(!$pembayaran) {
    header('Location: ../pages/pembayaran.php?error=1&msg=Data tidak ditemukan');
    exit();
}

// Generate invoice number
$invoice_no = 'INV/' . date('Y') . '/' . date('m') . '/' . str_pad($pembayaran['id'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran - <?php echo $invoice_no; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
        }

        .invoice-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .invoice-number {
            font-size: 24px;
            font-weight: 700;
            margin-top: 5px;
        }

        .invoice-body {
            padding: 40px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-section h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .info-section p {
            font-size: 14px;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        .info-section .highlight {
            font-weight: 600;
            color: #1e3a8a;
        }

        .payment-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
            text-align: right;
        }

        .amount-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            border-radius: 8px;
            padding: 20px 25px;
            margin-bottom: 30px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .amount-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .amount-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .amount-value {
            font-size: 16px;
            font-weight: 600;
        }

        .total-label {
            font-size: 16px;
            font-weight: 700;
        }

        .total-value {
            font-size: 28px;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-lunas {
            background: #d1fae5;
            color: #065f46;
        }

        .status-sebagian {
            background: #fef3c7;
            color: #92400e;
        }

        .status-belum {
            background: #fee2e2;
            color: #991b1b;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }

        .signature-box {
            text-align: center;
        }

        .signature-title {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 60px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .signature-name {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
            padding-top: 10px;
            border-top: 2px solid #1f2937;
        }

        .invoice-footer {
            background: #f9fafb;
            padding: 20px 40px;
            border-top: 3px solid #1e3a8a;
            text-align: center;
        }

        .footer-text {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .footer-note {
            font-size: 11px;
            color: #9ca3af;
            font-style: italic;
        }

        .action-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
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
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-print {
            background: #1e3a8a;
            color: white;
        }

        .btn-print:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.4);
        }

        .btn-close {
            background: #6b7280;
            color: white;
        }

        .btn-close:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            z-index: 0;
            pointer-events: none;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                max-width: 100%;
            }

            .action-buttons {
                display: none !important;
            }

            .invoice-body {
                position: relative;
            }

            .signatures {
                page-break-inside: avoid;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="watermark">PAID</div>

        <div class="invoice-header">
            <div class="header-content">
                <div class="company-name">🥋 YPOK MANAGEMENT</div>
                <div class="company-tagline">Yayasan Pencak Silat Organisasi Karate Indonesia</div>
                <div class="invoice-title">BUKTI PEMBAYARAN</div>
                <div class="invoice-number"><?php echo $invoice_no; ?></div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="invoice-info">
                <div class="info-section">
                    <h3>Informasi Pembayaran</h3>
                    <p><span class="highlight">Tanggal:</span> <?php echo date('d F Y', strtotime($pembayaran['tanggal'])); ?></p>
                    <p><span class="highlight">Kategori:</span> <?php echo htmlspecialchars($pembayaran['kategori']); ?></p>
                    <p><span class="highlight">Metode:</span> <?php echo htmlspecialchars($pembayaran['metode_pembayaran']); ?></p>
                </div>
                <div class="info-section">
                    <h3>Data Penerima</h3>
                    <p><span class="highlight">Nama:</span> <?php echo htmlspecialchars($pembayaran['nama_kohai'] ?? '-'); ?></p>
                    <p><span class="highlight">Keterangan:</span> <?php echo htmlspecialchars($pembayaran['keterangan']); ?></p>
                    <p><span class="highlight">Dicetak:</span> <span id="tanggalCetak"></span></p>
                </div>
            </div>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Status Pembayaran</span>
                    <span class="detail-value">
                        <span class="status-badge status-<?php
                            echo $pembayaran['status'] == 'Lunas' ? 'lunas' :
                                ($pembayaran['status'] == 'Sebagian' ? 'sebagian' : 'belum');
                        ?>">
                            <?php echo htmlspecialchars($pembayaran['status']); ?>
                        </span>
                    </span>
                </div>

                <?php if($pembayaran['status'] == 'Sebagian'): ?>
                <div class="detail-row">
                    <span class="detail-label">Total Tagihan</span>
                    <span class="detail-value">Rp <?php echo number_format($pembayaran['total_tagihan'], 0, ',', '.'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nominal Dibayar</span>
                    <span class="detail-value">Rp <?php echo number_format($pembayaran['nominal_dibayar'], 0, ',', '.'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Sisa Pembayaran</span>
                    <span class="detail-value" style="color: #dc2626;">Rp <?php echo number_format($pembayaran['sisa'], 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="amount-section">
                <?php if($pembayaran['status'] == 'Sebagian'): ?>
                <div class="amount-row">
                    <span class="amount-label">Dibayar Saat Ini</span>
                    <span class="amount-value">Rp <?php echo number_format($pembayaran['nominal_dibayar'], 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="amount-row">
                    <span class="total-label">TOTAL <?php echo $pembayaran['status'] == 'Sebagian' ? 'TAGIHAN' : 'PEMBAYARAN'; ?></span>
                    <span class="total-value">Rp <?php echo number_format($pembayaran['status'] == 'Sebagian' ? $pembayaran['total_tagihan'] : $pembayaran['jumlah'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-title">Penerima</div>
                    <div class="signature-name">Admin YPOK</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Yang Menyerahkan</div>
                    <div class="signature-name"><?php echo htmlspecialchars($pembayaran['nama_kohai'] ?? '-'); ?></div>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p class="footer-text">Terima kasih atas pembayaran Anda</p>
            <p class="footer-note">Dokumen ini adalah bukti pembayaran yang sah dan dicetak secara otomatis oleh sistem</p>
        </div>
    </div>

    <div class="action-buttons">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ Print / Save PDF
        </button>
        <button class="btn btn-close" onclick="window.close()">
            ✖️ Tutup
        </button>
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

        // Auto print dialog (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 500);
        // });

        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P for print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // ESC to close
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
