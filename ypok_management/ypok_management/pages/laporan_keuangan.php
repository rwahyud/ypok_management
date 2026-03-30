<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$search = $_GET['search'] ?? '';
$periode = $_GET['periode'] ?? 'all';

// Build query based on filters
$query = "SELECT * FROM transaksi WHERE 1=1";
$params = [];

if(!empty($search)) {
    $query .= " AND (keterangan LIKE ? OR kategori LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($periode !== 'all') {
    $query .= " AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
    $params[] = $periode;
}

$query .= " ORDER BY tanggal DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transaksi_list = $stmt->fetchAll();

// Calculate totals
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

// Count by type
$count_pemasukan = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE jenis='pemasukan'" . ($periode !== 'all' ? " AND DATE_FORMAT(tanggal, '%Y-%m') = ?" : ""));
if($periode !== 'all') {
    $count_pemasukan->execute([$periode]);
} else {
    $count_pemasukan->execute();
}
$jml_pemasukan = $count_pemasukan->fetchColumn();

$count_pengeluaran = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE jenis='pengeluaran'" . ($periode !== 'all' ? " AND DATE_FORMAT(tanggal, '%Y-%m') = ?" : ""));
if($periode !== 'all') {
    $count_pengeluaran->execute([$periode]);
} else {
    $count_pengeluaran->execute();
}
$jml_pengeluaran = $count_pengeluaran->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan YPOK - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .stat-card.income::before { background: linear-gradient(90deg, #10b981, #059669); }
        .stat-card.expense::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .stat-card.balance::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        
        .stat-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-card.income .stat-icon { background: #d1fae5; }
        .stat-card.expense .stat-icon { background: #fee2e2; }
        .stat-card.balance .stat-icon { background: #dbeafe; }
        
        .stat-info h3 {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .stat-info p {
            font-size: 11px;
            color: #9ca3af;
            margin: 2px 0 0 0;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card.income .stat-value { color: #10b981; }
        .stat-card.expense .stat-value { color: #ef4444; }
        .stat-card.balance .stat-value { color: #3b82f6; }
        
        .stat-subtitle {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 5px;
        }
        
        .filter-section {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .filter-section select {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }
        
        .transaction-summary {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .summary-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .summary-badges {
            display: flex;
            gap: 15px;
        }
        
        .summary-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .badge-income {
            background: #d1fae5;
            color: #059669;
        }
        
        .badge-expense {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .badge-icon {
            font-size: 16px;
        }
        
        .table-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit {
            background: #fbbf24;
            color: white;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .kategori-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .kategori-pemasukan {
            background: #d1fae5;
            color: #059669;
        }
        
        .kategori-pengeluaran {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Export Modal Styles */
        .export-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .export-modal-overlay.active {
            display: flex;
        }
        
        .export-modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .export-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .export-modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .export-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .export-modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .export-modal-body {
            padding: 25px;
        }
        
        .export-form-group {
            margin-bottom: 20px;
        }
        
        .export-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .export-form-group select,
        .export-form-group input[type="date"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .export-form-group select:focus,
        .export-form-group input[type="date"]:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .export-signature-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .export-signature-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .export-signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .export-signature-input {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .export-signature-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .export-modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .export-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .export-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }
        
        .export-btn-cancel:hover {
            background: #e5e7eb;
        }
        
        .export-btn-submit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .export-btn-submit:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
    </style>
    <?php include __DIR__ . '/../components/analytics.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil ditambahkan</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil diupdate</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data berhasil dihapus</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Laporan Keuangan YPOK</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <!-- Search & Filter Section -->
            <div class="content-header" style="margin-bottom: 25px;">
                <div class="search-bar" style="flex: 1;">
                    <form method="GET" style="display: flex; gap: 15px; align-items: center;" id="searchForm">
                        <input type="text" name="search" id="searchInput" placeholder="🔍 Cari transaksi, keterangan, tanggal..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" style="flex: 1;">
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label style="font-size: 14px; color: #6b7280; white-space: nowrap;">Periode:</label>
                            <select name="periode" onchange="this.form.submit()" style="padding: 10px 15px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                <option value="all" <?php echo $periode === 'all' ? 'selected' : ''; ?>>Semua Periode</option>
                                <?php
                                // Generate last 12 months
                                for($i = 0; $i < 12; $i++) {
                                    $month = date('Y-m', strtotime("-$i months"));
                                    $label = date('F Y', strtotime("-$i months"));
                                    echo "<option value='$month' " . ($periode === $month ? 'selected' : '') . ">$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="button" class="btn-primary" onclick="openModal()">
                            ➕ Tambah Transaksi
                        </button>
                        
                        <button type="button" class="btn-secondary" onclick="exportPDF()" style="background: #e2e8f0;
    color: #2d3748;">
                            📄 Export PDF
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card income">
                    <div class="stat-header">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3>TOTAL PEMASUKAN</h3>
                            <p>Periode yang dipilih</p>
                        </div>
                    </div>
                    <h2 class="stat-value">Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?></h2>
                    <p class="stat-subtitle">Pemasukan - Pengeluaran</p>
                </div>
                
                <div class="stat-card expense">
                    <div class="stat-header">
                        <div class="stat-icon">💸</div>
                        <div class="stat-info">
                            <h3>TOTAL PENGELUARAN</h3>
                            <p>Periode yang dipilih</p>
                        </div>
                    </div>
                    <h2 class="stat-value">Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?></h2>
                    <p class="stat-subtitle">Periode yang dipilih</p>
                </div>
                
                <div class="stat-card balance">
                    <div class="stat-header">
                        <div class="stat-icon">💵</div>
                        <div class="stat-info">
                            <h3>SALDO BERSIH</h3>
                            <p>Pemasukan - Pengeluaran</p>
                        </div>
                    </div>
                    <h2 class="stat-value">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h2>
                    <p class="stat-subtitle">Pemasukan - Pengeluaran</p>
                </div>
            </div>
            
            <!-- Transaction Summary -->
            <div class="transaction-summary">
                <div class="summary-header">
                    <h3 style="margin: 0; font-size: 18px; color: #1f2937;">📊 Riwayat Transaksi</h3>
                </div>
                <div class="summary-badges">
                    <div class="summary-badge badge-income">
                        <span class="badge-icon">↗️</span>
                        <span><?php echo $jml_pemasukan; ?> Pemasukan</span>
                    </div>
                    <div class="summary-badge badge-expense">
                        <span class="badge-icon">↘️</span>
                        <span><?php echo $jml_pengeluaran; ?> Pengeluaran</span>
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Kategori</th>
                        <th>Pemasukan</th>
                        <th>Pengeluaran</th>
                        <th>Saldo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($transaksi_list) > 0): ?>
                        <?php foreach($transaksi_list as $index => $transaksi): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d F Y', strtotime($transaksi['tanggal'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($transaksi['keterangan']); ?></strong>
                                <?php if(!empty($transaksi['kategori'])): ?>
                                    <br><small style="color: #6b7280;">🏷️ <?php echo htmlspecialchars($transaksi['kategori']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="kategori-badge kategori-<?php echo $transaksi['jenis']; ?>">
                                    <?php echo ucfirst($transaksi['jenis']); ?>
                                </span>
                            </td>
                            <td style="color: #10b981; font-weight: 600;">
                                <?php echo $transaksi['jenis'] === 'pemasukan' ? 'Rp ' . number_format($transaksi['jumlah'], 0, ',', '.') : '-'; ?>
                            </td>
                            <td style="color: #ef4444; font-weight: 600;">
                                <?php echo $transaksi['jenis'] === 'pengeluaran' ? 'Rp ' . number_format($transaksi['jumlah'], 0, ',', '.') : '-'; ?>
                            </td>
                            <td style="color: #3b82f6; font-weight: 600;">
                                Rp <?php echo number_format($transaksi['saldo'] ?? 0, 0, ',', '.'); ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn-edit" onclick="editTransaksi(<?php echo htmlspecialchars(json_encode($transaksi)); ?>)">
                                        ✏️
                                    </button>
                                    <button class="btn-delete" onclick="deleteTransaksi(<?php echo $transaksi['id']; ?>)">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
                                <div style="font-size: 48px; margin-bottom: 10px;">📭</div>
                                <strong>Tidak ada data transaksi</strong>
                                <p style="margin: 5px 0 0 0; font-size: 14px;">Silakan tambah transaksi baru</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Form -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Transaksi</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="transaksiForm" method="POST" action="proses_transaksi.php">
                <input type="hidden" name="id" id="transaksi_id">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="date" name="tanggal" id="tanggal" required>
                </div>
                
                <div class="form-group">
                    <label>Jenis Transaksi *</label>
                    <select name="jenis" id="jenis" required>
                        <option value="">Pilih Jenis</option>
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" id="kategori" placeholder="Misal: Lainnya, Donasi, dll">
                </div>
                
                <div class="form-group">
                    <label>Keterangan *</label>
                    <textarea name="keterangan" id="keterangan" rows="3" required placeholder="Deskripsi transaksi"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="number" name="jumlah" id="jumlah" required placeholder="Masukkan jumlah">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
     <!-- Export Modal -->
    <div class="export-modal-overlay" id="exportModal">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3>
                    <span>📊</span>
                    <span>Export Laporan Keuangan</span>
                </h3>
                <button class="export-modal-close" onclick="closeExportModal()">×</button>
            </div>
            
            <form id="exportForm" onsubmit="handleExportSubmit(event)">
                <div class="export-modal-body">
                    <div class="export-form-group">
                        <label>Format Export</label>
                        <select name="format" id="formatExport" required>
                            <option value="">Pilih Format...</option>
                            <option value="pdf" selected>📄 PDF Document (.pdf)</option>
                            <option value="excel">📊 Excel Spreadsheet (.xlsx)</option>
                            <option value="csv">📋 CSV File (.csv)</option>
                        </select>
                    </div>
                    
                    
                    <div class="export-form-group">
                        <label>Pilih Periode</label>
                        <select name="periode" id="exportPeriode" required onchange="toggleCustomDate(this.value)">
                            <option value="month">Bulan Ini</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="custom">Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group" id="customDateRange" style="display: none;">
                        <label>Range Tanggal</label>
                        <div class="export-signature-row">
                            <input type="date" name="start_date" id="startDate" class="export-signature-input" placeholder="Dari Tanggal">
                            <input type="date" name="end_date" id="endDate" class="export-signature-input" placeholder="Sampai Tanggal">
                        </div>
                    </div>
                    
                    <div class="export-signature-section">
                        <div class="export-signature-title">Tanda Tangan Digital</div>
                        <div class="export-signature-row">
                            <input type="text" name="ketua" id="exportKetua" class="export-signature-input" placeholder="Ketua YPOK" value="Ketua YPOK" required>
                            <input type="text" name="admin" id="exportAdmin" class="export-signature-input" placeholder="Pembuat Laporan" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly required style="background: #f0f0f0; cursor: not-allowed;">
                        </div>
                    </div>
                    
                    <input type="hidden" name="type" id="exportType" value="<?php echo $tab; ?>">
                </div>
                
                <div class="export-modal-footer">
                    <button type="button" class="export-btn export-btn-cancel" onclick="closeExportModal()">
                        <span>❌</span>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="export-btn export-btn-submit">
                        <span>📄</span>
                        <span>Generate & Export</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/app.js"></script>
    <script>
        // Auto-submit search with delay
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('searchForm').submit();
            }, 500);
        });
        
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Transaksi';
            document.getElementById('formAction').value = 'add';
            document.getElementById('transaksiForm').reset();
            document.getElementById('transaksi_id').value = '';
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('modal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
        
        function editTransaksi(data) {
            document.getElementById('modalTitle').textContent = 'Edit Transaksi';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('transaksi_id').value = data.id;
            document.getElementById('tanggal').value = data.tanggal;
            document.getElementById('jenis').value = data.jenis;
            document.getElementById('kategori').value = data.kategori || '';
            document.getElementById('keterangan').value = data.keterangan;
            document.getElementById('jumlah').value = data.jumlah;
            document.getElementById('modal').style.display = 'block';
        }
        
        function deleteTransaksi(id) {
            if(confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
                window.location.href = 'proses_transaksi.php?action=delete&id=' + id;
            }
        }
        
        // Export Modal Functions
        function exportPDF() {
            document.getElementById('exportModal').classList.add('active');
        }
        
        function closeExportModal() {
            document.getElementById('exportModal').classList.remove('active');
        }
        
        function toggleCustomDate(value) {
            const customDateRange = document.getElementById('customDateRange');
            if (value === 'custom') {
                customDateRange.style.display = 'block';
                document.getElementById('startDate').required = true;
                document.getElementById('endDate').required = true;
            } else {
                customDateRange.style.display = 'none';
                document.getElementById('startDate').required = false;
                document.getElementById('endDate').required = false;
            }
        }
        
        function handleExportSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const format = formData.get('format');
            const periode = formData.get('periode');
            
            let url = '../export/export_laporan_keuangan.php?';
            url += 'format=' + format;
            url += '&periode=' + periode;
            
            if (periode === 'custom') {
                url += '&start_date=' + formData.get('start_date');
                url += '&end_date=' + formData.get('end_date');
            }
            
            url += '&ketua=' + encodeURIComponent(formData.get('ketua'));
            url += '&admin=' + encodeURIComponent(formData.get('admin'));
            
            window.open(url, '_blank');
            closeExportModal();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            const exportModal = document.getElementById('exportModal');
            
            if (event.target == modal) {
                closeModal();
            }
            
            if (event.target == exportModal) {
                closeExportModal();
            }
        }
    </script>
</body>
</html>
