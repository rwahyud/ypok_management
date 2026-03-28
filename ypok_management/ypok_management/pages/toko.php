<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Query untuk produk
$stmt = $pdo->query("SELECT * FROM produk_toko ORDER BY created_at DESC");
$produk_list = $stmt->fetchAll();

// Query untuk kategori dari tabel kategori_produk
$kategori_stmt = $pdo->query("SELECT * FROM kategori_produk ORDER BY nama_kategori");
$kategori_list = $kategori_stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk transaksi dengan statistik
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Statistik Transaksi - Using prepared statements to prevent SQL injection
try {
    $stats_query = "SELECT 
        COUNT(*) as total_transaksi,
        SUM(total_harga) as total_pendapatan,
        SUM(jumlah) as total_produk,
        AVG(total_harga) as rata_rata
    FROM transaksi_toko 
    WHERE 1=1";
    $stats_params = [];
    
    if($date_from && $date_to) {
        // Validate date format
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
            $stats_query .= " AND DATE(tanggal) BETWEEN ? AND ?";
            $stats_params[] = $date_from;
            $stats_params[] = $date_to;
        }
    }
    
    $stmt_stats = $pdo->prepare($stats_query);
    $stmt_stats->execute($stats_params);
    $stats = $stmt_stats->fetch();
    
    // Query transaksi - Using prepared statements
    $transaksi_query = "SELECT t.*, p.nama_produk, p.kode_produk 
        FROM transaksi_toko t
        LEFT JOIN produk_toko p ON t.produk_id = p.id
        WHERE 1=1";
    $transaksi_params = [];
    
    if($search && !empty(trim($search))) {
        $search_term = '%' . trim($search) . '%';
        $transaksi_query .= " AND (p.nama_produk LIKE ? OR t.pembeli LIKE ? OR t.id_transaksi LIKE ?)";
        $transaksi_params[] = $search_term;
        $transaksi_params[] = $search_term;
        $transaksi_params[] = $search_term;
    }
    
    if($date_from && $date_to) {
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
            $transaksi_query .= " AND DATE(t.tanggal) BETWEEN ? AND ?";
            $transaksi_params[] = $date_from;
            $transaksi_params[] = $date_to;
        }
    }
    
    $transaksi_query .= " ORDER BY t.tanggal DESC";
    $stmt_transaksi = $pdo->prepare($transaksi_query);
    $stmt_transaksi->execute($transaksi_params);
    $transaksi_list = $stmt_transaksi->fetchAll();
} catch(PDOException $e) {
    error_log("Database error in toko.php: " . $e->getMessage());
    $stats = ['total_transaksi' => 0, 'total_pendapatan' => 0, 'total_produk' => 0, 'rata_rata' => 0];
    $transaksi_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-button.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            padding: 24px;
            border-radius: 12px;
            color: white;
        }
        
        .stat-card.purple {
            --gradient-start: #8b5cf6;
            --gradient-end: #6366f1;
        }
        
        .stat-card.green {
            --gradient-start: #10b981;
            --gradient-end: #059669;
        }
        
        .stat-card.orange {
            --gradient-start: #f59e0b;
            --gradient-end: #d97706;
        }
        
        .stat-card.blue {
            --gradient-start: #3b82f6;
            --gradient-end: #2563eb;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-subtitle {
            font-size: 13px;
            opacity: 0.8;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-available {
            background: #dcfce7;
            color: #15803d;
        }
        
        .badge-limited {
            background: #fef3c7;
            color: #92400e;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .product-code {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 8px;
        }
        
        .product-stock {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .btn-buy {
            flex: 1;
            background: #10b981;
            color: white;
        }
        
        .btn-buy:hover {
            background: #059669;
        }
        
        .btn-view {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .date-filter input[type="date"] {
            border: none;
            padding: 4px;
            font-size: 14px;
        }
        
        .transaction-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .transaction-table table {
            width: 100%;
        }
        
        .transaction-table th {
            background: #2563eb;
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 500;
        }
        
        .transaction-table td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .quantity-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 24px;
            padding: 0 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .method-badge {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .method-transfer {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .method-tunai {
            background: #fef3c7;
            color: #92400e;
        }
        
        .method-ewallet {
            background: #e0e7ff;
            color: #4338ca;
        }
        
        /* Filter info */
        .filter-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 4px solid #3b82f6;
        }
        
        .filter-info-icon {
            font-size: 24px;
        }
        
        .filter-info-text {
            flex: 1;
        }
        
        .filter-info-text strong {
            color: #1e40af;
            font-size: 14px;
        }
        
        .filter-info-text p {
            margin: 5px 0 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        
        .filter-badge {
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #3b82f6;
            margin-right: 8px;
        }
        
        /* Search bar enhancement */
        .search-bar input {
            padding-left: 16px;
        }
        
        /* Struk Print Styles */
        .struk-container {
            max-width: 400px;
            margin: 0 auto;
            font-family: 'Courier New', monospace;
            background: white;
        }
        
        .struk-header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px dashed #000;
        }
        
        .struk-logo {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .struk-title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .struk-subtitle {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        
        .struk-info {
            padding: 15px 20px;
            border-bottom: 2px dashed #000;
        }
        
        .struk-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 13px;
        }
        
        .struk-items {
            padding: 15px 20px;
            border-bottom: 2px dashed #000;
        }
        
        .struk-item {
            margin: 10px 0;
        }
        
        .struk-item-name {
            font-weight: bold;
            font-size: 14px;
        }
        
        .struk-item-detail {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }
        
        .struk-item-price {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }
        
        .struk-total {
            padding: 15px 20px;
            border-bottom: 2px dashed #000;
        }
        
        .struk-total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .struk-grand-total {
            font-size: 18px;
            font-weight: bold;
            padding-top: 10px;
            border-top: 1px solid #000;
        }
        
        .struk-footer {
            padding: 15px 20px;
            text-align: center;
            font-size: 12px;
        }
        
        .struk-footer p {
            margin: 5px 0;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        @media print {
            /* Set page size */
            @page {
                size: auto;
                margin: 10mm;
            }

            /* Hide body margin/padding */
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide everything */
            body * {
                visibility: hidden;
            }

            /* Show only struk container and its children */
            #viewTransaksiContent,
            #viewTransaksiContent * {
                visibility: visible;
            }

            /* Position struk at top of page */
            #viewTransaksiContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide modal chrome */
            .modal {
                background: none !important;
            }

            .modal-header,
            .modal-footer,
            .btn-print,
            button {
                display: none !important;
            }

            /* Ensure single page print - prevent page breaks */
            .struk-container {
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                page-break-before: auto !important;
            }
        }
        
        /* Enhanced Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
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
        
        .modal-header {
            padding: 24px 28px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .close {
            color: white;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(255,255,255,0.2);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            transition: all 0.3s;
        }
        
        .close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 28px;
           
            overflow-y: auto;
        }
        
        .modal-footer {
            padding: 20px 28px;
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #e5e7eb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .form-group label::before {
            content: "•";
            color: #3b82f6;
            font-size: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-control:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .input-group {
            display: flex;
            gap: 8px;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .btn-add-kategori {
            padding: 12px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .btn-add-kategori:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .kategori-option {
            padding: 4px 0;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 600;
        }
        
        .checkbox-description {
            font-size: 13px;
            color: #6b7280;
            margin-top: 8px;
            font-style: italic;
        }
        
        .variasi-container {
            display: none;
            margin-top: 16px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 2px dashed #d1d5db;
        }
        
        .variasi-container.active {
            display: block;
        }
        
        .variasi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .variasi-header h4 {
            margin: 0;
            color: #374151;
            font-size: 16px;
        }
        
        .btn-add-variasi {
            padding: 8px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-add-variasi:hover {
            transform: scale(1.05);
        }
        
        .variasi-item {
            background: white;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .variasi-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .variasi-title {
            font-weight: 700;
            color: #5b21b6;
            font-size: 14px;
        }
        
        .btn-remove-variasi {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-remove-variasi:hover {
            background: #fecaca;
        }
        
        .variasi-fields {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 12px;
        }
        
        .variasi-fields .form-group {
            margin-bottom: 0;
        }
        
        .variasi-empty {
            text-align: center;
            padding: 32px;
            color: #9ca3af;
        }
        
        .variasi-empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        .file-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9fafb;
        }
        
        .file-upload-area:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .file-upload-area.dragover {
            border-color: #3b82f6;
            background: #dbeafe;
        }
        
        .upload-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.6;
        }
        
        .upload-text {
            color: #6b7280;
            font-size: 14px;
        }
        
        .upload-text strong {
            color: #3b82f6;
            font-weight: 600;
        }
        
        .file-preview {
            margin-top: 12px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            display: none;
        }
        
        .file-preview.active {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .file-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .status-info {
            padding: 12px 16px;
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-top: 12px;
        }
        
        .status-info.warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        
        .status-info.danger {
            background: #fee2e2;
            border-left-color: #ef4444;
        }
        
        .status-text {
            font-size: 13px;
            font-weight: 600;
            margin: 0;
        }
        
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .variasi-fields {
                grid-template-columns: 1fr;
            }
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
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil ditambahkan'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil diupdate'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Data berhasil dihapus'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">⚠️</div>
            <div class="toast-content">
                <div class="toast-title">Error!</div>
                <div class="toast-message"><?php echo htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan'); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">💰 Toko </h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <button class="tab-button active" onclick="switchTab('katalog')">
                    🏪 Katalog Produk
                </button>
                <button class="tab-button" onclick="switchTab('riwayat')">
                    📋 Riwayat Transaksi
                </button>
            </div>

            <!-- Tab: Katalog Produk -->
            <div id="tab-katalog" class="tab-content active">
                <div class="content-header">
                    <div>
                        <h1>🏪 Katalog Produk YPOK</h1>
                        <p style="color: #6b7280; margin-top: 8px;">Perlengkapan & Merchandise Resmi YPOK</p>
                    </div>
                    
                    <div class="header-actions">
                        <div class="search-bar">
                            <input type="text" id="searchProduk" placeholder="Cari produk..." autocomplete="off">
                        </div>
                        <select class="form-control" id="filterKategori" style="width: 200px;" onchange="filterByKategori()">
                            <option value="">🔍 Semua Kategori</option>
                            <?php foreach($kategori_list as $kat): ?>
                            <option value="<?php echo htmlspecialchars($kat['nama_kategori']); ?>">
                                <?php echo htmlspecialchars($kat['icon'] . ' ' . $kat['nama_kategori']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn-secondary" onclick="openManageKategoriModal()">
                            🏷️ Kelola Kategori
                        </button>
                        <button class="btn-primary" onclick="openTambahProdukModal()">
                            ➕ Tambah Produk
                        </button>
                    </div>
                </div>

                <div class="products-grid">
                    <?php foreach($produk_list as $produk): ?>
                    <div class="product-card" data-kategori="<?php echo htmlspecialchars($produk['kategori'] ?? ''); ?>">
                        <div class="product-image">📦</div>
                        <div class="product-info">
                            <div class="product-badges">
                                <?php 
                                $status = $produk['status'] ?? 'Tersedia';
                                $stok = $produk['stok'];
                                
                                if ($status === 'Habis' || $stok <= 0): ?>
                                    <span class="badge" style="background: #fee2e2; color: #dc2626;">HABIS</span>
                                <?php elseif ($status === 'Pre Order'): ?>
                                    <span class="badge" style="background: #fef3c7; color: #92400e;">PRE ORDER</span>
                                <?php elseif ($stok > 10): ?>
                                    <span class="badge badge-available">TERSEDIA</span>
                                <?php else: ?>
                                    <span class="badge badge-limited">STOK TERBATAS</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-name"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                            <div class="product-code"><?php echo htmlspecialchars($produk['kode_produk']); ?></div>
                            <div class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                            <div class="product-stock">
                                📦 Stok: <strong><?php echo $produk['stok']; ?></strong> unit
                                <?php if($status === 'Pre Order'): ?>
                                <span style="font-size: 11px; color: #f59e0b;">• PO</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <button class="btn-icon btn-buy" onclick="openBeliProdukModal(<?php echo $produk['id']; ?>)" <?php echo ($status === 'Habis' || $stok <= 0) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                    🛒 Beli
                                </button>
                                <button class="btn-icon btn-view" onclick="viewProduk(<?php echo $produk['id']; ?>)">👁️</button>
                                <button class="btn-icon btn-edit" onclick="editProduk(<?php echo $produk['id']; ?>)">✏️</button>
                                <button class="btn-icon btn-delete" onclick="deleteProduk(<?php echo $produk['id']; ?>)">🗑️</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab: Riwayat Transaksi -->
            <div id="tab-riwayat" class="tab-content">
                <div class="content-header">
                    <div>
                        <h1>📋 Riwayat Transaksi Toko</h1>
                        <p style="color: #6b7280; margin-top: 8px;">Daftar semua transaksi pembelian produk</p>
                    </div>
                    
                    <div class="header-actions">
                        <div class="date-filter">
                            <span>Periode:</span>
                            <input type="date" id="dateFrom" value="<?php echo $date_from; ?>">
                            <span>s/d</span>
                            <input type="date" id="dateTo" value="<?php echo $date_to; ?>">
                            <button class="btn-secondary" onclick="resetTanggalToko()" title="Reset rentang tanggal" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                                🔄 Reset Tanggal
                            </button>
                        </div>
                        <div class="search-bar">
                            <input type="text" id="searchTransaksi" placeholder="🔍 Cari pembeli, produk, ID transaksi..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                        </div>
                        <button class="btn-secondary" onclick="openExportModalTransaksi()" title="Export laporan transaksi">
                            📄 Export PDF
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card purple">
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value"><?php echo $stats['total_transaksi'] ?? 0; ?></div>
                        <div class="stat-subtitle">transaksi</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-value">Rp <?php echo number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="stat-subtitle">dari penjualan</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-label">Produk Terjual</div>
                        <div class="stat-value"><?php echo $stats['total_produk'] ?? 0; ?></div>
                        <div class="stat-subtitle">unit</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-label">Rata-rata Transaksi</div>
                        <div class="stat-value">Rp <?php echo number_format($stats['rata_rata'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="stat-subtitle">per transaksi</div>
                    </div>
                </div>

                <?php if($date_from && $date_to || $search): ?>
                <!-- Filter Info -->
                <div class="filter-info">
                    <div class="filter-info-icon">🔍</div>
                    <div class="filter-info-text">
                        <strong>Filter Aktif:</strong>
                        <p>
                            <?php if($date_from && $date_to): ?>
                                <span class="filter-badge">📅 <?php echo date('d M Y', strtotime($date_from)) . ' - ' . date('d M Y', strtotime($date_to)); ?></span>
                            <?php endif; ?>
                            <?php if($search): ?>
                                <span class="filter-badge">🔎 "<?php echo htmlspecialchars($search); ?>"</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <button class="btn-secondary" onclick="resetFilter()" style="background: #ef4444; color: white;">
                        🔄 Reset Filter
                    </button>
                </div>
                <?php endif; ?>

                <!-- Transaction Table -->
                <div class="transaction-table">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>ID Transaksi</th>
                                <th>Produk</th>
                                <th>Variasi</th>
                                <th>Pembeli</th>
                                <th>Lokasi</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($transaksi_list as $trans): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d M Y', strtotime($trans['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($trans['id_transaksi']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($trans['nama_produk']); ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($trans['kode_produk']); ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($trans['variasi_info'])): ?>
                                        <span style="font-size: 12px; color: #6366f1; background: #e0e7ff; padding: 2px 8px; border-radius: 4px;">
                                            <?php echo htmlspecialchars($trans['variasi_info']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($trans['pembeli']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($trans['lokasi']); ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($trans['alamat']); ?></small>
                                </td>
                                <td>
                                    <span class="quantity-badge" style="background: <?php 
                                        echo $trans['jumlah'] >= 3 ? '#8b5cf6' : 
                                            ($trans['jumlah'] == 2 ? '#3b82f6' : '#10b981'); 
                                    ?>">
                                        <?php echo $trans['jumlah']; ?>x
                                    </span>
                                </td>
                                <td><strong style="color: #10b981;">Rp <?php echo number_format($trans['total_harga'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="method-badge method-<?php echo strtolower($trans['metode_pembayaran']); ?>">
                                        <?php echo htmlspecialchars($trans['metode_pembayaran']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-icon btn-view" onclick="viewTransaksi(<?php echo $trans['id']; ?>)" title="Lihat Struk">👁️</button>
                                    <button class="btn-icon btn-delete" onclick="deleteTransaksi(<?php echo $trans['id']; ?>)" title="Hapus">🗑️</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Export Transaksi Toko -->
     <div class="export-modal-overlay" id="exportModalTransaksi">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3>
                    <span>📊</span>
                    <span>Export Laporan Keuangan</span>
                </h3>
                <button class="export-modal-close" onclick="closeExportModalTransaksi()">×</button>
            </div>

            <form id="formExportTransaksi" onsubmit="handleExportTransaksiSubmit(event)">
                 <div class="export-modal-body">
                    <div class="export-form-group">
                        <label>Format Export</label>
                        <select name="format_export" id="formatExport" required>
                            <option value="">Pilih Format...</option>
                            <option value="pdf" selected>📄 PDF Document (.pdf)</option>
                            <option value="excel">📊 Excel Spreadsheet (.xlsx)</option>
                            <option value="csv">📋 CSV File (.csv)</option>
                        </select>
                    </div>

                    <div class="export-form-group">
                        <label>Pilih Periode</label>
                        <select name="periode" id="exportPeriodeTransaksi" required onchange="toggleCustomDateTransaksi(this.value)">
                            <option value="month">Bulan Ini</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="custom">Pilih Tanggal</option>
                        </select>
                    </div>

                    <div class="export-form-group" id="customDateRangeTransaksi" style="display: none;">
                        <label>Range Tanggal</label>
                        <div class="export-signature-row">
                            <input type="date" name="start_date" id="startDateTransaksi" class="export-signature-input" placeholder="Dari Tanggal">
                            <input type="date" name="end_date" id="endDateTransaksi" class="export-signature-input" placeholder="Sampai Tanggal">
                        </div>
                    </div>

                    <div class="export-signature-section">
                        <div class="export-signature-title">Tanda Tangan Digital</div>
                        <div class="export-signature-row">
                            <input type="text" name="ketua" id="exportKetuaTransaksi" class="export-signature-input" placeholder="Ketua YPOK" value="Ketua YPOK" required>
                            <input type="text" name="admin" id="exportAdminTransaksi" class="export-signature-input" placeholder="Pembuat Laporan" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly required style="background: #f0f0f0; cursor: not-allowed;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeExportModalTransaksi()">Batal</button>
                    <button type="submit" class="btn-primary">📄 Generate & Export</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let currentHarga = 0;
        let variasiCount = 0;

        // ========== EXPORT TRANSAKSI MODAL FUNCTIONS ==========
        function openExportModalTransaksi() {
            document.getElementById('exportModalTransaksi').style.display = 'flex';
        }

        function closeExportModalTransaksi() {
            document.getElementById('exportModalTransaksi').style.display = 'none';
            document.getElementById('formExportTransaksi').reset();
            document.getElementById('customDateRangeTransaksi').style.display = 'none';
        }

        function toggleCustomDateTransaksi(value) {
            const customDateRange = document.getElementById('customDateRangeTransaksi');
            const startDate = document.getElementById('startDateTransaksi');
            const endDate = document.getElementById('endDateTransaksi');

            if (value === 'custom') {
                customDateRange.style.display = 'block';
                startDate.required = true;
                endDate.required = true;
            } else {
                customDateRange.style.display = 'none';
                startDate.required = false;
                endDate.required = false;
            }
        }

        function handleExportTransaksiSubmit(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            // Build URL parameters
            const params = new URLSearchParams();
            params.append('format', formData.get('format_export')); // Perbaiki dari 'format' ke 'format_export'
            params.append('periode', formData.get('periode'));
            params.append('ketua', formData.get('ketua'));
            params.append('admin', formData.get('admin'));

            if (formData.get('periode') === 'custom') {
                const startDate = formData.get('start_date');
                const endDate = formData.get('end_date');

                if (!startDate || !endDate) {
                    alert('Silakan pilih tanggal mulai dan tanggal akhir');
                    return;
                }

                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }

            // Open export in new window
            window.open('../actions/export_transaksi_laporan.php?' + params.toString(), '_blank');

            // Close modal
            closeExportModalTransaksi();

            // Show success toast (optional)
            showToast('success', 'Export sedang diproses...', 'Laporan akan terbuka di tab baru');
        }

        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <div class="toast-icon">${type === 'success' ? '✓' : '⚠️'}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // ========== OTHER FUNCTIONS ==========
        // Toggle Variasi Container
        function toggleVariasi() {
            const container = document.getElementById('variasiContainer');
            const checkbox = document.getElementById('hasVariasi');
            
            if (checkbox.checked) {
                container.classList.add('active');
                if (variasiCount === 0) {
                    addVariasi();
                }
            } else {
                container.classList.remove('active');
                if (confirm('Hapus semua variasi yang sudah ditambahkan?')) {
                    document.getElementById('variasiList').innerHTML = `
                        <div class="variasi-empty">
                            <div class="variasi-empty-icon">📦</div>
                            <p>Belum ada variasi ditambahkan</p>
                        </div>
                    `;
                    variasiCount = 0;
                } else {
                    checkbox.checked = true;
                }
            }
        }
        
        // Add Variasi Item
        function addVariasi() {
            variasiCount++;
            const variasiList = document.getElementById('variasiList');
            const emptyState = variasiList.querySelector('.variasi-empty');
            if (emptyState) emptyState.remove();
            
            const variasiItem = document.createElement('div');
            variasiItem.className = 'variasi-item';
            variasiItem.id = 'variasi-' + variasiCount;
            variasiItem.innerHTML = `
                <div class="variasi-item-header">
                    <span class="variasi-title">Variasi #${variasiCount}</span>
                    <button type="button" class="btn-remove-variasi" onclick="removeVariasi(${variasiCount})">🗑️ Hapus</button>
                </div>
                <div class="variasi-fields">
                    <div class="form-group">
                        <label>Nama Variasi <span class="required">*</span></label>
                        <input type="text" name="variasi_nama[]" class="form-control" placeholder="Contoh: Ukuran" required>
                    </div>
                    <div class="form-group">
                        <label>Nilai <span class="required">*</span></label>
                        <input type="text" name="variasi_nilai[]" class="form-control" placeholder="Contoh: S, M, L, XL" required>
                    </div>
                    <div class="form-group">
                        <label>Stok <span class="required">*</span></label>
                        <input type="number" name="variasi_stok[]" class="form-control" placeholder="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Tambahan Harga</label>
                        <input type="number" name="variasi_harga[]" class="form-control" placeholder="0" min="0" step="1000">
                    </div>
                </div>
            `;
            variasiList.appendChild(variasiItem);
        }
        
        // Remove Variasi Item
        function removeVariasi(id) {
            const item = document.getElementById('variasi-' + id);
            if (item) {
                item.remove();
                variasiCount--;
                const variasiList = document.getElementById('variasiList');
                if (variasiList.children.length === 0) {
                    variasiList.innerHTML = `
                        <div class="variasi-empty">
                            <div class="variasi-empty-icon">📦</div>
                            <p>Belum ada variasi ditambahkan</p>
                        </div>
                    `;
                }
            }
        }
        
        // Update Status Info
        function updateStatusInfo() {
            const stok = parseInt(document.getElementById('stokProduk').value) || 0;
            const statusSelect = document.getElementById('statusProduk');
            const statusValue = statusSelect.value;
            const statusInfo = document.getElementById('statusInfo');
            
            let statusClass = 'danger', statusText = 'Habis', statusIcon = '❌';
            
            if (statusValue === 'Tersedia') {
                if (stok > 10) { statusClass = ''; statusText = 'Tersedia'; statusIcon = '✅'; }
                else if (stok > 0) { statusClass = 'warning'; statusText = 'Stok Terbatas'; statusIcon = '⚠️'; }
            } else if (statusValue === 'Pre Order') {
                statusClass = 'warning'; statusText = 'Pre Order'; statusIcon = '⏳';
            }
            
            statusInfo.className = 'status-info ' + statusClass;
            statusInfo.innerHTML = `<p class="status-text">${statusIcon} Status: <strong>${statusText}</strong> (Stok: ${stok} unit)</p>`;
        }
        
        // Update Status Info untuk Edit Modal
        function updateEditStatusInfo() {
            const stok = parseInt(document.getElementById('edit_stok')?.value) || 0;
            const statusSelect = document.getElementById('edit_status');
            const statusValue = statusSelect ? statusSelect.value : '';
            const statusInfo = document.getElementById('edit_statusInfo');
            
            if (!statusInfo) return;
            
            let statusClass = 'danger', statusText = 'Habis', statusIcon = '❌';
            
            if (statusValue === 'Tersedia') {
                if (stok > 10) { statusClass = ''; statusText = 'Tersedia'; statusIcon = '✅'; }
                else if (stok > 0) { statusClass = 'warning'; statusText = 'Stok Terbatas'; statusIcon = '⚠️'; }
            } else if (statusValue === 'Pre Order') {
                statusClass = 'warning'; statusText = 'Pre Order'; statusIcon = '⏳';
            }
            
            statusInfo.className = 'status-info ' + statusClass;
            statusInfo.innerHTML = `<p class="status-text">${statusIcon} Status: <strong>${statusText}</strong> (Stok: ${stok} unit)</p>`;
        }
        
        // Open Modal Functions
        function openTambahProdukModal() {
            document.getElementById('modalTambahProduk').style.display = 'flex';
        }
        
        function openTambahKategoriModal() {
            document.getElementById('modalTambahKategori').style.display = 'flex';
        }
        
        function openManageKategoriModal() {
            loadKategoriList();
            document.getElementById('modalManageKategori').style.display = 'flex';
        }
        
        // Close Modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Tab Switching
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');

            // Find and activate the correct button
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tab)) {
                    btn.classList.add('active');
                }
            });
        }

        // Check hash on page load to open correct tab
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1); // Remove #
            if (hash === 'riwayat') {
                switchTab('riwayat');
            }
        });
        
        // Load kategori list
        function loadKategoriList() {
            fetch('../actions/get_kategori_list.php')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.error) {
                        html = '<p style="text-align: center; color: #ef4444; padding: 20px;">Error: ' + data.error + '</p>';
                    } else if (data.length === 0) {
                        html = '<p style="text-align: center; color: #6b7280; padding: 20px;">Belum ada kategori</p>';
                    } else {
                        html = '<div style="max-height: 400px; overflow-y: auto;">';
                        data.forEach(kat => {
                            html += `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: white; border-radius: 8px; margin-bottom: 8px; border: 1px solid #e5e7eb;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 24px;">${kat.icon}</span>
                                            <div>
                                                <p style="margin: 0; font-weight: 600;">${kat.nama_kategori}</p>
                                                ${kat.deskripsi ? `<p style="margin: 0; font-size: 13px; color: #6b7280;">${kat.deskripsi}</p>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-icon btn-delete" onclick="deleteKategori(${kat.id}, '${kat.nama_kategori.replace(/'/g, "\\'")}')" title="Hapus">🗑️</button>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }
                    document.getElementById('kategoriListContent').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('kategoriListContent').innerHTML = '<p style="text-align: center; color: #ef4444; padding: 20px;">Gagal memuat kategori</p>';
                });
        }
        
        // Delete kategori
        function deleteKategori(id, nama) {
            if(confirm(`Yakin ingin menghapus kategori "${nama}"?\n\nKategori yang masih digunakan tidak dapat dihapus.`)) {
                window.location.href = `../actions/delete_kategori.php?id=${id}`;
            }
        }
        
        // Preview image
        function previewImage(input) {
            const preview = document.getElementById('filePreview');
            const previewImg = document.getElementById('previewImg');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fileName.textContent = file.name;
                    fileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
                    preview.classList.add('active');
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Filter Functions
        function resetFilter() {
            window.location.href = 'toko.php';
        }

        function resetTanggalToko() {
            // Clear date inputs
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';

            // Reload page but stay on riwayat tab
            const url = new URL(window.location.href);
            url.search = ''; // Clear all query parameters
            url.hash = 'riwayat'; // Set hash to riwayat tab
            window.location.href = url.toString();
        }

        function filterByKategori() {
            const selectedKategori = document.getElementById('filterKategori').value;
            const searchValue = document.getElementById('searchProduk').value.toLowerCase();
            
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.querySelector('.product-name').textContent.toLowerCase();
                const code = card.querySelector('.product-code').textContent.toLowerCase();
                const kategori = card.getAttribute('data-kategori') || '';
                
                const matchSearch = name.includes(searchValue) || code.includes(searchValue);
                const matchKategori = !selectedKategori || kategori === selectedKategori;
                
                card.style.display = (matchSearch && matchKategori) ? 'block' : 'none';
            });
            
            const visibleCards = document.querySelectorAll('.product-card[style*="display: block"], .product-card:not([style*="display: none"])').length;
            updateProductCounter(visibleCards);
        }
        
        function updateProductCounter(count) {
            let counterElement = document.getElementById('productCounter');
            if (!counterElement) {
                counterElement = document.createElement('p');
                counterElement.id = 'productCounter';
                counterElement.style.cssText = 'color: #6b7280; margin-top: 8px; font-size: 14px;';
                document.querySelector('.content-header > div').appendChild(counterElement);
            }
            const totalProducts = document.querySelectorAll('.product-card').length;
            counterElement.textContent = `Menampilkan ${count} dari ${totalProducts} produk`;
        }
        
        // Live search transaksi
        function liveSearchTransaksi() {
            const searchValue = document.getElementById('searchTransaksi').value.toLowerCase();
            const rows = document.querySelectorAll('.transaction-table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const idTransaksi = row.cells[2].textContent.toLowerCase();
                const produk = row.cells[3].textContent.toLowerCase();
                const pembeli = row.cells[5].textContent.toLowerCase();
                const lokasi = row.cells[6].textContent.toLowerCase();
                
                const match = idTransaksi.includes(searchValue) || produk.includes(searchValue) || 
                             pembeli.includes(searchValue) || lokasi.includes(searchValue);
                
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            
            updateTransaksiCounter(visibleCount);
        }
        
        function updateTransaksiCounter(count) {
            let counterElement = document.getElementById('transaksiCounter');
            if (!counterElement) {
                counterElement = document.createElement('p');
                counterElement.id = 'transaksiCounter';
                counterElement.style.cssText = 'color: #6b7280; margin: 15px 0; font-size: 14px;';
                const tableContainer = document.querySelector('.transaction-table');
                tableContainer.parentNode.insertBefore(counterElement, tableContainer);
            }
            const totalRows = document.querySelectorAll('.transaction-table tbody tr').length;
            counterElement.innerHTML = `<strong>Menampilkan ${count} dari ${totalRows} transaksi</strong>`;
            if (count === 0) {
                counterElement.innerHTML += ' <span style="color: #ef4444;">- Tidak ada hasil yang ditemukan</span>';
            }
        }
        
        function filterByDateRange() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const searchValue = document.getElementById('searchTransaksi').value.toLowerCase();
            const rows = document.querySelectorAll('.transaction-table tbody tr');
            
            if (!dateFrom || !dateTo) return;
            
            const startDate = new Date(dateFrom);
            const endDate = new Date(dateTo);
            let visibleCount = 0;
            
            rows.forEach(row => {
                const tanggalText = row.cells[1].textContent;
                const rowDate = parseIndonesianDate(tanggalText);
                const dateMatch = rowDate >= startDate && rowDate <= endDate;
                
                const idTransaksi = row.cells[2].textContent.toLowerCase();
                const produk = row.cells[3].textContent.toLowerCase();
                const pembeli = row.cells[5].textContent.toLowerCase();
                const lokasi = row.cells[6].textContent.toLowerCase();
                
                const searchMatch = !searchValue || idTransaksi.includes(searchValue) || 
                                   produk.includes(searchValue) || pembeli.includes(searchValue) || 
                                   lokasi.includes(searchValue);
                
                if (dateMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateTransaksiCounter(visibleCount);
        }
        
        function parseIndonesianDate(dateStr) {
            const months = {
                'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'Mei': 4, 'Jun': 5,
                'Jul': 6, 'Agt': 7, 'Sep': 8, 'Okt': 9, 'Nov': 10, 'Des': 11
            };
            const parts = dateStr.split(' ');
            if (parts.length === 3) {
                const day = parseInt(parts[0]);
                const month = months[parts[1]];
                const year = parseInt(parts[2]);
                return new Date(year, month, day);
            }
            return new Date();
        }

        // Produk Functions
        function openBeliProdukModal(id) {
            fetch(`../actions/get_produk.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Set produk ID
                    const produkIdInput = document.getElementById('beli_produk_id');
                    if (produkIdInput) produkIdInput.value = data.id;
                    
                    // Set nama produk
                    const namaProdukEl = document.getElementById('beli_nama_produk');
                    if (namaProdukEl) namaProdukEl.textContent = data.nama_produk || '-';
                    
                    // Set kode produk
                    const kodeProdukEl = document.getElementById('beli_kode_produk');
                    if (kodeProdukEl) kodeProdukEl.textContent = data.kode_produk || '-';
                    
                    // Set status badge
                    const statusBadge = document.getElementById('beli_status_badge');
                    const statusEl = document.getElementById('beli_status');
                    const status = data.status || 'Tersedia';
                    
                    if (statusBadge && statusEl) {
                        if (status === 'Tersedia') {
                            statusBadge.style.background = '#dcfce7';
                            statusBadge.style.color = '#15803d';
                            statusEl.textContent = 'Tersedia';
                            statusBadge.innerHTML = '✅ <span id="beli_status">Tersedia</span>';
                        } else if (status === 'Pre Order') {
                            statusBadge.style.background = '#fef3c7';
                            statusBadge.style.color = '#92400e';
                            statusEl.textContent = 'Pre Order';
                            statusBadge.innerHTML = '⏳ <span id="beli_status">Pre Order</span>';
                        } else {
                            statusBadge.style.background = '#fee2e2';
                            statusBadge.style.color = '#dc2626';
                            statusEl.textContent = 'Habis';
                            statusBadge.innerHTML = '❌ <span id="beli_status">Habis</span>';
                        }
                    }
                    
                    // Set harga
                    currentHarga = parseFloat(data.harga) || 0;
                    const hargaEl = document.getElementById('beli_harga');
                    if (hargaEl) {
                        hargaEl.textContent = 'Rp ' + parseInt(currentHarga).toLocaleString('id-ID');
                    }
                    
                    // Handle variasi
                    const variasiContainer = document.getElementById('beli_variasi_container');
                    const jumlahInput = document.getElementById('beli_jumlah');
                    
                    if (variasiContainer) {
                        if (data.has_variasi && data.variasi && data.variasi.length > 0) {
                            let variasiHTML = '<div class="form-group"><label>Pilih Variasi <span class="required">*</span></label><select name="variasi_id" id="beli_variasi" class="form-control" required onchange="updateHargaVariasi()">';
                            variasiHTML += '<option value="">Pilih variasi...</option>';
                            data.variasi.forEach(v => {
                                const hargaTotal = parseFloat(data.harga) + parseFloat(v.harga_tambahan || 0);
                                const stokInfo = v.stok > 0 ? `(Stok: ${v.stok})` : '(Habis)';
                                variasiHTML += `<option value="${v.id}" data-stok="${v.stok}" data-harga="${hargaTotal}" ${v.stok <= 0 ? 'disabled' : ''}>${v.nama_variasi}: ${v.nilai_variasi} - Rp ${parseInt(hargaTotal).toLocaleString('id-ID')} ${stokInfo}</option>`;
                            });
                            variasiHTML += '</select></div>';
                            variasiContainer.innerHTML = variasiHTML;
                            variasiContainer.style.display = 'block';
                        } else {
                            variasiContainer.innerHTML = '';
                            variasiContainer.style.display = 'none';
                            if (jumlahInput) jumlahInput.max = data.stok || 0;
                        }
                    }
                    
                    // Reset dan hitung total
                    if (jumlahInput) jumlahInput.value = 1;
                    hitungTotal();
                    
                    // Show modal
                    const modal = document.getElementById('modalBeliProduk');
                    if (modal) modal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading product:', error);
                    alert('Gagal memuat data produk. Silakan coba lagi.');
                });
        }
        
        function updateHargaVariasi() {
            const variasiSelect = document.getElementById('beli_variasi');
            if (!variasiSelect) return;
            
            const selectedOption = variasiSelect.options[variasiSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                const stok = parseInt(selectedOption.getAttribute('data-stok')) || 0;
                currentHarga = harga;
                
                const hargaEl = document.getElementById('beli_harga');
                if (hargaEl) {
                    hargaEl.textContent = 'Rp ' + parseInt(harga).toLocaleString('id-ID');
                }
                
                const jumlahInput = document.getElementById('beli_jumlah');
                if (jumlahInput) {
                    jumlahInput.max = stok;
                    jumlahInput.value = 1;
                }
                
                hitungTotal();
            }
        }
        
        function hitungTotal() {
            const jumlahInput = document.getElementById('beli_jumlah');
            const totalEl = document.getElementById('total_bayar');
            
            if (!jumlahInput || !totalEl) return;
            
            const jumlah = parseInt(jumlahInput.value) || 0;
            const total = currentHarga * jumlah;
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        // View Produk
        function viewProduk(id) {
            fetch(`../actions/get_produk.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    let variasiHTML = '';
                    if (data.has_variasi && data.variasi && data.variasi.length > 0) {
                        variasiHTML = `<div style="margin-top: 20px;"><h4 style="margin: 0 0 15px 0; color: #374151;">📦 Variasi Produk</h4>`;
                        data.variasi.forEach(v => {
                            variasiHTML += `<div style="background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 8px; border: 1px solid #e5e7eb;">
                                <p style="margin: 0; font-weight: 600;">${v.nama_variasi}: <span style="color: #3b82f6;">${v.nilai_variasi}</span></p>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">Stok: ${v.stok} unit${v.harga_tambahan > 0 ? ' • +Rp ' + parseInt(v.harga_tambahan).toLocaleString('id-ID') : ''}</p>
                            </div>`;
                        });
                        variasiHTML += '</div>';
                    }
                    
                    const content = `<div style="text-align: center; padding: 20px;">
                        <div style="font-size: 100px; margin-bottom: 20px;">📦</div>
                        <h2 style="margin: 0 0 5px 0;">${data.nama_produk}</h2>
                        <p style="color: #6b7280; margin: 0 0 20px 0;">${data.kode_produk}</p>
                        ${data.status ? `<div style="display: inline-block; padding: 6px 16px; border-radius: 20px; margin-bottom: 15px; background: ${data.status === 'Tersedia' ? '#dcfce7' : data.status === 'Pre Order' ? '#fef3c7' : '#fee2e2'}; color: ${data.status === 'Tersedia' ? '#15803d' : data.status === 'Pre Order' ? '#92400e' : '#dc2626'}; font-weight: 600;">
                            ${data.status === 'Tersedia' ? '✅' : data.status === 'Pre Order' ? '⏳' : '❌'} ${data.status}
                        </div>` : ''}
                        <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left;">
                                <div><p style="color: #6b7280; margin: 0; font-size: 13px;">Kategori</p><p style="margin: 5px 0 0 0; font-weight: 600;">${data.kategori || '-'}</p></div>
                                <div><p style="color: #6b7280; margin: 0; font-size: 13px;">Stok Total</p><p style="margin: 5px 0 0 0; font-weight: 600;">${data.stok} unit</p></div>
                            </div>
                        </div>
                        <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <p style="color: #15803d; margin: 0; font-size: 13px;">Harga ${data.has_variasi ? 'Dasar' : ''}</p>
                            <p style="margin: 5px 0 0 0; font-size: 28px; font-weight: 700; color: #15803d;">Rp ${parseInt(data.harga).toLocaleString('id-ID')}</p>
                        </div>
                        ${variasiHTML}
                        ${data.deskripsi ? `<div style="text-align: left; margin-top: 15px;"><p style="color: #6b7280; margin: 0 0 5px 0; font-size: 13px;">Deskripsi</p><p style="margin: 0;">${data.deskripsi}</p></div>` : ''}
                        ${data.spesifikasi ? `<div style="text-align: left; margin-top: 15px;"><p style="color: #6b7280; margin: 0 0 5px 0; font-size: 13px;">Spesifikasi</p><p style="margin: 0; white-space: pre-line;">${data.spesifikasi}</p></div>` : ''}
                    </div>`;
                    
                    document.getElementById('viewProdukContent').innerHTML = content;
                    document.getElementById('modalViewProduk').style.display = 'flex';
                });
        }
        
        function editProduk(id) {
            fetch(`../actions/get_produk.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    fetch('../actions/get_kategori_list.php')
                        .then(response => response.json())
                        .then(kategoriList => {
                            let kategoriOptions = '<option value="">Pilih Kategori...</option>';
                            kategoriList.forEach(kat => {
                                const selected = data.kategori === kat.nama_kategori ? 'selected' : '';
                                kategoriOptions += `<option value="${kat.nama_kategori}" ${selected}>${kat.icon} ${kat.nama_kategori}</option>`;
                            });
                            
                            const content = `
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Kode Produk <span class="required">*</span></label>
                                        <input type="text" name="kode_produk" class="form-control" value="${data.kode_produk}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Produk <span class="required">*</span></label>
                                        <input type="text" name="nama_produk" class="form-control" value="${data.nama_produk}" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Kategori <span class="required">*</span></label>
                                    <select name="kategori" class="form-control" required>${kategoriOptions}</select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Harga <span class="required">*</span></label>
                                        <input type="number" name="harga" id="edit_harga" class="form-control" value="${data.harga}" required min="0" step="1000">
                                    </div>
                                    <div class="form-group">
                                        <label>Stok <span class="required">*</span></label>
                                        <input type="number" name="stok" id="edit_stok" class="form-control" value="${data.stok}" required min="0" onchange="updateEditStatusInfo()">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Status Produk <span class="required">*</span></label>
                                    <select name="status" id="edit_status" class="form-control" required onchange="updateEditStatusInfo()">
                                        <option value="">Pilih Status...</option>
                                        <option value="Tersedia" ${data.status === 'Tersedia' ? 'selected' : ''}>✅ Tersedia - Siap dikirim</option>
                                        <option value="Pre Order" ${data.status === 'Pre Order' ? 'selected' : ''}>⏳ Pre Order - Perlu waktu pengadaan</option>
                                        <option value="Habis" ${data.status === 'Habis' ? 'selected' : ''}>❌ Habis - Stok kosong</option>
                                    </select>
                                </div>
                                <div id="edit_statusInfo" class="status-info ${data.stok > 10 ? '' : data.stok > 0 ? 'warning' : 'danger'}">
                                    <p class="status-text">${data.status === 'Tersedia' ? '✅' : data.status === 'Pre Order' ? '⏳' : '❌'} Status: <strong>${data.status || 'Tersedia'}</strong> (Stok: ${data.stok} unit)</p>
                                </div>
                                ${data.has_variasi && data.variasi && data.variasi.length > 0 ? `
                                    <div style="background: #eff6ff; padding: 16px; border-radius: 10px; border-left: 4px solid #3b82f6; margin: 20px 0;">
                                        <h4 style="margin: 0 0 12px 0; color: #1e40af; font-size: 14px;">📦 Variasi Produk Saat Ini</h4>
                                        ${data.variasi.map(v => `
                                            <div style="background: white; padding: 10px; border-radius: 6px; margin-bottom: 8px;">
                                                <p style="margin: 0; font-size: 13px;"><strong>${v.nama_variasi}:</strong> ${v.nilai_variasi}</p>
                                                <p style="margin: 4px 0 0 0; font-size: 12px; color: #6b7280;">Stok: ${v.stok} unit ${v.harga_tambahan > 0 ? '• +Rp ' + parseInt(v.harga_tambahan).toLocaleString('id-ID') : ''}</p>
                                            </div>
                                        `).join('')}
                                        <p style="margin: 12px 0 0 0; font-size: 12px; color: #6b7280; font-style: italic;">💡 Untuk mengubah variasi, hapus produk dan buat ulang dengan variasi baru.</p>
                                    </div>
                                ` : ''}
                                <div class="form-group">
                                    <label>Deskripsi <span class="required">*</span></label>
                                    <textarea name="deskripsi" class="form-control" rows="3" required>${data.deskripsi || ''}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Spesifikasi</label>
                                    <textarea name="spesifikasi" class="form-control" rows="2" placeholder="Contoh: Bahan: 100% Cotton"></textarea>
                                    <button type="button" class="btn-add-variasi" style="margin-top: 8px; width: 100%;" onclick="alert('Fitur tambah spesifikasi')">+ Tambah Spesifikasi</button>
                                </div>
                                <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                    <p style="margin: 0; font-size: 13px; color: #92400e;"><strong>⚠️ Perhatian:</strong> Stok total produk adalah ${data.stok} unit. Pastikan data yang diubah sudah benar.</p>
                                </div>
                            `;
                            document.getElementById('editProdukContent').innerHTML = content;
                            document.getElementById('modalEditProduk').style.display = 'flex';
                        });
                });
        }
        
        function deleteProduk(id) {
            if(confirm('Yakin ingin menghapus produk ini?')) {
                window.location.href = `../actions/delete_produk.php?id=${id}`;
            }
        }
        
        function viewTransaksi(id) {
            fetch(`../actions/get_transaksi.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    const hargaSatuan = data.total_harga / data.jumlah;
                    const content = `
                        <div class="struk-container">
                            <div class="struk-header">
                                <div class="struk-logo">🥋</div>
                                <div class="struk-title">YPOK</div>
                                <div class="struk-subtitle">TOKO YPOK</div>
                                <div class="struk-subtitle">PERLENGKAPAN KARATE RESMI</div>
                            </div>
                            <div class="struk-info">
                                <div class="struk-row"><span>No. Transaksi:</span><strong>${data.id_transaksi}</strong></div>
                                <div class="struk-row"><span>Tanggal:</span><span>${new Date(data.tanggal).toLocaleString('id-ID', {day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span></div>
                                <div class="struk-row"><span>Kasir:</span><span>Admin YPOK</span></div>
                            </div>
                            <div class="struk-info">
                                <div style="font-weight: bold; margin-bottom: 8px;">Pembeli:</div>
                                <div class="struk-row"><span style="width: 100%;">${data.pembeli}</span></div>
                                <div class="struk-row"><span style="width: 100%; color: #666;">${data.lokasi}</span></div>
                                ${data.alamat ? `<div class="struk-row"><span style="width: 100%; color: #666; font-size: 11px;">${data.alamat}</span></div>` : ''}
                            </div>
                            <div class="struk-items">
                                <div class="struk-item">
                                    <div class="struk-item-name">${data.nama_produk}</div>
                                    <div class="struk-item-detail">${data.kode_produk}</div>
                                    ${data.variasi_info ? `<div class="struk-item-detail" style="color: #6366f1;">📦 ${data.variasi_info}</div>` : ''}
                                    ${data.spesifikasi ? `<div class="struk-item-detail">${data.spesifikasi}</div>` : ''}
                                    <div class="struk-item-price">
                                        <span>${data.jumlah} x Rp ${parseInt(hargaSatuan).toLocaleString('id-ID')}</span>
                                        <strong>Rp ${parseInt(data.total_harga).toLocaleString('id-ID')}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="struk-total">
                                <div class="struk-total-row"><span>Subtotal:</span><span>Rp ${parseInt(data.total_harga).toLocaleString('id-ID')}</span></div>
                                <div class="struk-total-row"><span>Diskon:</span><span>Rp 0</span></div>
                                <div class="struk-total-row struk-grand-total"><span>TOTAL:</span><span>Rp ${parseInt(data.total_harga).toLocaleString('id-ID')}</span></div>
                            </div>
                            <div class="struk-info">
                                <div class="struk-row"><span>Metode Bayar:</span><strong>${data.metode_pembayaran}</strong></div>
                                <div class="struk-row"><span>Total Bayar:</span><strong>Rp ${parseInt(data.total_harga).toLocaleString('id-ID')}</strong></div>
                                <div class="struk-row"><span>Kembalian:</span><span>Rp 0</span></div>
                            </div>
                            ${data.catatan ? `<div class="struk-info"><div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div><div style="font-size: 12px; color: #666;">${data.catatan}</div></div>` : ''}
                            <div class="struk-footer">
                                <p style="font-weight: bold; margin-top: 10px;">*** TERIMA KASIH ***</p>
                                <p>Barang yang sudah dibeli tidak dapat ditukar</p>
                                <p>Simpan struk ini sebagai bukti pembelian</p>
                                <p style="margin-top: 10px; font-size: 10px;">www.ypok.com | Instagram: @ypok_official</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('viewTransaksiContent').innerHTML = content;
                    document.getElementById('modalViewTransaksi').style.display = 'flex';
                });
        }
        
        function printStruk() {
            // Clone the struk content
            const strukContent = document.getElementById('viewTransaksiContent').cloneNode(true);

            // Create a new window for printing
            const printWindow = window.open('', '', 'height=600,width=800');

            printWindow.document.write('<html><head><title>Cetak Struk</title>');
            printWindow.document.write('<style>');
            printWindow.document.write(`
                @page {
                    size: auto;
                    margin: 10mm;
                }
                body {
                    margin: 0;
                    padding: 0;
                    font-family: 'Courier New', monospace;
                }
                .struk-container {
                    max-width: 400px;
                    margin: 0 auto;
                    background: white;
                    padding: 10px;
                }
                .struk-header {
                    text-align: center;
                    border-bottom: 2px dashed #333;
                    padding-bottom: 15px;
                    margin-bottom: 15px;
                }
                .struk-logo {
                    font-size: 48px;
                    margin-bottom: 5px;
                }
                .struk-title {
                    font-size: 24px;
                    font-weight: bold;
                    margin: 5px 0;
                }
                .struk-subtitle {
                    font-size: 12px;
                    color: #666;
                }
                .struk-info {
                    margin-bottom: 15px;
                    padding-bottom: 10px;
                    border-bottom: 1px dashed #ddd;
                }
                .struk-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 5px 0;
                    font-size: 13px;
                }
                .struk-items {
                    margin-bottom: 15px;
                }
                .struk-item {
                    margin-bottom: 10px;
                }
                .struk-item-name {
                    font-weight: bold;
                    font-size: 14px;
                }
                .struk-item-detail {
                    font-size: 11px;
                    color: #666;
                    margin: 2px 0;
                }
                .struk-item-price {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 5px;
                    font-size: 13px;
                }
                .struk-total {
                    border-top: 2px dashed #333;
                    padding-top: 15px;
                    margin-bottom: 15px;
                }
                .struk-total-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 8px 0;
                    font-size: 13px;
                }
                .struk-grand-total {
                    font-size: 16px;
                    font-weight: bold;
                    border-top: 1px solid #333;
                    padding-top: 8px;
                    margin-top: 8px;
                }
                .struk-footer {
                    text-align: center;
                    font-size: 11px;
                    color: #666;
                    border-top: 2px dashed #333;
                    padding-top: 15px;
                    margin-top: 15px;
                }
                .struk-footer p {
                    margin: 5px 0;
                }
            `);
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(strukContent.innerHTML);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.focus();

            // Wait for content to load then print
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
        
        function deleteTransaksi(id) {
            if(confirm('Yakin ingin menghapus transaksi ini?')) {
                window.location.href = `../actions/delete_transaksi.php?id=${id}`;
            }
        }
        
        function exportPDF() {
            window.open('../actions/export_transaksi_laporan.php', '_blank');
        }
        
       
        
        // Event Listeners
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        document.getElementById('searchProduk')?.addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const selectedKategori = document.getElementById('filterKategori')?.value || '';
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.querySelector('.product-name').textContent.toLowerCase();
                const code = card.querySelector('.product-code').textContent.toLowerCase();
                const kategori = card.getAttribute('data-kategori') || '';
                const matchSearch = name.includes(search) || code.includes(search);
                const matchKategori = !selectedKategori || kategori === selectedKategori;
                card.style.display = (matchSearch && matchKategori) ? 'block' : 'none';
            });
        });
        
        document.getElementById('searchTransaksi')?.addEventListener('input', function(e) {
            liveSearchTransaksi();
        });
        
        document.getElementById('dateFrom')?.addEventListener('change', function(e) {
            const dateTo = document.getElementById('dateTo').value;
            if (this.value && dateTo) filterByDateRange();
        });
        
        document.getElementById('dateTo')?.addEventListener('change', function(e) {
            const dateFrom = document.getElementById('dateFrom').value;
            if (this.value && dateFrom) filterByDateRange();
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const totalRows = document.querySelectorAll('.transaction-table tbody tr').length;
            if (totalRows > 0) {
                updateTransaksiCounter(totalRows);
            }
        });
    </script>
    
    <script src="../assets/js/app.js"></script>
    
    <!-- Modal Tambah Produk -->
    <div class="modal" id="modalTambahProduk">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>➕ Tambah Produk Baru</h3>
                <span class="close" onclick="closeModal('modalTambahProduk')">&times;</span>
            </div>
            <form action="../actions/add_produk.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
     <!-- Kode & Nama Produk -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kode Produk <span class="required">*</span></label>
                            <input type="text" name="kode_produk" class="form-control" required placeholder="PRD-001">
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Produk <span class="required">*</span></label>
                            <input type="text" name="nama_produk" class="form-control" required placeholder="Karate Gi Premium">
                        </div>
                    </div>
                    
                    <!-- Foto Produk -->
                    <div class="form-group">
                        <label>Foto Produk</label>
                        <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('fileInput').click()">
                            <div class="upload-icon">📦</div>
                            <div class="upload-text">
                                <strong>Pilih Foto</strong> atau masukkan URL<br>
                                <small>Format: JPG, PNG (Max: 2MB)</small>
                            </div>
                        </div>
                        <input type="file" id="fileInput" name="gambar" class="form-control" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        <div style="margin-top: 12px;">
                            <input type="text" name="gambar_url" class="form-control" placeholder="https://..." style="font-size: 13px;">
                        </div>
                        <div class="file-preview" id="filePreview">
                            <img id="previewImg" src="" alt="Preview">
                            <div>
                                <p style="margin: 0; font-weight: 600;" id="fileName"></p>
                                <p style="margin: 0; font-size: 12px; color: #6b7280;" id="fileSize"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kategori -->
                    <div class="form-group">
                        <label>Kategori <span class="required">*</span></label>
                        <div class="input-group">
                            <select name="kategori" id="kategoriSelect" class="form-control" required>
                                <option value="">Pilih Kategori...</option>
                                <?php foreach($kategori_list as $kat): ?>
                                <option value="<?php echo htmlspecialchars($kat['nama_kategori']); ?>">
                                    <?php echo htmlspecialchars($kat['icon'] . ' ' . $kat['nama_kategori']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-add-kategori" onclick="openTambahKategoriModal()">+ Tambah Kategori</button>
                        </div>
                    </div>
                    
                    <!-- Harga & Stok -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Harga <span class="required">*</span></label>
                            <input type="number" name="harga" id="hargaProduk" class="form-control" required min="0" step="1000" placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Stok <span class="required">*</span></label>
                            <input type="number" name="stok" id="stokProduk" class="form-control" required min="0" value="0" onchange="updateStatusInfo()">
                        </div>
                    </div>
                    
                    <!-- Status Produk -->
                    <div class="form-group">
                        <label>Status Produk <span class="required">*</span></label>
                        <select name="status" id="statusProduk" class="form-control" required onchange="updateStatusInfo()">
                            <option value="">Pilih Status...</option>
                            <option value="Tersedia" ${data.status === 'Tersedia' ? 'selected' : ''}>✅ Tersedia - Siap dikirim</option>
                            <option value="Pre Order" ${data.status === 'Pre Order' ? 'selected' : ''}>⏳ Pre Order - Perlu waktu pengadaan</option>
                            <option value="Habis" ${data.status === 'Habis' ? 'selected' : ''}>❌ Habis - Stok kosong</option>
                        </select>
                    </div>
                    
                    <!-- Status Info -->
                    <div id="statusInfo" class="status-info danger">
                        <p class="status-text">Status: <strong>Habis</strong> (Stok: 0 unit)</p>
                    </div>
                    
                    <!-- Checkbox Variasi Produk -->
                    <div class="checkbox-group">
                        <input type="checkbox" id="hasVariasi" name="has_variasi" onchange="toggleVariasi()">
                        <label for="hasVariasi">Produk memiliki variasi (ukuran, warna, dll)</label>
                    </div>
                    <p class="checkbox-description">Centang jika produk memiliki variasi seperti ukuran, warna, tinggi, dll.</p>
                    
                    <!-- Container Variasi -->
                    <div class="variasi-container" id="variasiContainer">
                        <div class="variasi-header">
                            <h4>➕ Tambah Variasi Produk</h4>
                            <button type="button" class="btn-add-variasi" onclick="addVariasi()">+ Tambah Variasi</button>
                        </div>
                        
                        <div id="variasiList">
                            <div class="variasi-empty">
                                <div class="variasi-empty-icon">📦</div>
                                <p>Belum ada variasi ditambahkan</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Deskripsi & Spesifikasi -->
                    <div class="form-group">
                        <label>Deskripsi <span class="required">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Deskripsi produk..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Spesifikasi</label>
                        <textarea name="spesifikasi" class="form-control" rows="2" placeholder="Contoh: Bahan: 100% Cotton"></textarea>
                        <button type="button" class="btn-add-variasi" style="margin-top: 8px; width: 100%;" onclick="alert('Fitur tambah spesifikasi')">+ Tambah Spesifikasi</button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalTambahProduk')">Batal</button>
                    <button type="submit" class="btn-primary">💾 Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Beli Produk -->
    <div class="modal" id="modalBeliProduk">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>🛒 Form Pembelian Produk</h3>
                <span class="close" onclick="closeModal('modalBeliProduk')">&times;</span>
            </div>
            <form action="../actions/add_transaksi.php" method="POST" id="formBeliProduk">
                <div class="modal-body">
                    <input type="hidden" name="produk_id" id="beli_produk_id">
                    
                    <!-- Produk Info -->
                    <div style="background: #f9fafb; padding: 16px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                        <h4 style="margin: 0 0 10px 0; color: #374151;">📦 Produk yang Dibeli</h4>
                        <p style="margin: 0; font-weight: 600; font-size: 16px;" id="beli_nama_produk">-</p>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #6b7280;" id="beli_kode_produk">-</p>
                        <div id="beli_status_badge" style="display: inline-block; padding: 6px 12px; border-radius: 20px; margin-top: 10px; font-size: 12px; font-weight: 600;">
                            ✅ <span id="beli_status">Tersedia</span>
                        </div>
                        <p style="margin: 15px 0 0 0; font-size: 20px; font-weight: 700; color: #10b981;" id="beli_harga">Rp 0</p>
                    </div>
                    
                    <!-- Variasi Container -->
                    <div id="beli_variasi_container" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label>Nama Pembeli <span class="required">*</span></label>
                        <input type="text" name="pembeli" class="form-control" required placeholder="Nama lengkap pembeli">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Lokasi <span class="required">*</span></label>
                            <input type="text" name="lokasi" class="form-control" required placeholder="Kota">
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat detail">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah <span class="required">*</span></label>
                        <input type="number" name="jumlah" id="beli_jumlah" class="form-control" required min="1" value="1" onchange="hitungTotal()">
                    </div>
                    
                    <div class="form-group">
                        <label>Metode Pembayaran <span class="required">*</span></label>
                        <select name="metode_pembayaran" class="form-control" required>
                            <option value="">Pilih Metode</option>
                            <option value="Transfer">💳 Transfer Bank</option>
                            <option value="Tunai">💵 Tunai</option>
                            <option value="E-Wallet">📱 E-Wallet (OVO, GoPay, Dana)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                    
                    <!-- Total -->
                    <div style="background: #dcfce7; padding: 16px; border-radius: 10px; margin-top: 20px; border: 2px solid #10b981;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 16px; font-weight: 600; color: #15803d;">Total Bayar:</span>
                            <span style="font-size: 24px; font-weight: 700; color: #15803d;" id="total_bayar">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalBeliProduk')">Batal</button>
                    <button type="submit" class="btn-primary">🛒 Proses Pembelian</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal View Produk -->
    <div class="modal" id="modalViewProduk">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>👁️ Detail Produk</h3>
                <span class="close" onclick="closeModal('modalViewProduk')">&times;</span>
            </div>
            <div class="modal-body" id="viewProdukContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalViewProduk')">Tutup</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Produk -->
    <div class="modal" id="modalEditProduk">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>✏️ Edit Produk</h3>
                <span class="close" onclick="closeModal('modalEditProduk')">&times;</span>
            </div>
            <form action="../actions/edit_produk.php" method="POST" enctype="multipart/form-data" id="formEditProduk">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body" id="editProdukContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalEditProduk')">Batal</button>
                    <button type="submit" class="btn-primary">💾 Update Produk</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal View Transaksi -->
    <div class="modal" id="modalViewTransaksi">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>📋 Struk Pembelian</h3>
                <span class="close" onclick="closeModal('modalViewTransaksi')">&times;</span>
            </div>
            <div class="modal-body" id="viewTransaksiContent" style="padding: 0;">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-print" onclick="printStruk()">
                    🖨️ Cetak Struk
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('modalViewTransaksi')">Tutup</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Kategori -->
    <div class="modal" id="modalTambahKategori">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>🏷️ Tambah Kategori Baru</h3>
                <span class="close" onclick="closeModal('modalTambahKategori')">&times;</span>
            </div>
            <form action="../actions/add_kategori.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="required">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" required placeholder="Contoh: Seragam Premium">
                    </div>
                    
                    <div class="form-group">
                        <label>Icon Emoji</label>
                        <input type="text" name="icon" class="form-control" placeholder="📦" maxlength="10">
                        <small style="color: #6b7280; font-size: 12px;">Copy emoji dari: <a href="https://emojipedia.org" target="_blank" style="color: #3b82f6;">Emojipedia</a></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
                    </div>
                    
                    <div style="background: #eff6ff; padding: 12px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                        <p style="margin: 0; font-size: 13px; color: #1e40af;">
                            <strong>💡 Tips:</strong> Kategori akan muncul di dropdown saat menambah produk baru.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalTambahKategori')">Batal</button>
                    <button type="submit" class="btn-primary">💾 Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Manage Kategori -->
    <div class="modal" id="modalManageKategori">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>🏷️ Kelola Kategori Produk</h3>
                <span class="close" onclick="closeModal('modalManageKategori')">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="margin: 0;">Daftar Kategori</h4>
                    <button type="button" class="btn-primary" onclick="closeModal('modalManageKategori'); openTambahKategoriModal();">
                        ➕ Tambah Kategori
                    </button>
                </div>
                
                <div id="kategoriListContent">
                    <p style="text-align: center; color: #6b7280; padding: 20px;">Memuat...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalManageKategori')">Tutup</button>
            </div>
        </div>
    </div>
</body>
</html>

