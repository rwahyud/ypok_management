<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$periode = isset($_GET['periode']) ? $_GET['periode'] : '';

// Build query with filters
$query = "SELECT k.*, l.nama_lokasi
          FROM kegiatan k 
          LEFT JOIN lokasi l ON k.lokasi_id = l.id 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (k.nama_kegiatan LIKE :search OR l.nama_lokasi LIKE :search OR k.tanggal_kegiatan LIKE :search)";
}

if (!empty($periode) && $periode != 'semua') {
    $query .= " AND EXTRACT(MONTH FROM k.tanggal_kegiatan) = :periode AND EXTRACT(YEAR FROM k.tanggal_kegiatan) = EXTRACT(YEAR FROM CURRENT_DATE)";
}

$query .= " ORDER BY k.tanggal_kegiatan DESC";

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if (!empty($periode) && $periode != 'semua') {
    $stmt->bindValue(':periode', $periode);
}

$stmt->execute();
$kegiatan_list = $stmt->fetchAll();

// Get statistics
$stats_query = "SELECT
    COUNT(*) as total_kegiatan,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status = 'berlangsung' THEN 1 ELSE 0 END) as berlangsung,
    SUM(CASE WHEN status = 'dijadwalkan' THEN 1 ELSE 0 END) as dijadwalkan,
    SUM(CASE WHEN tampil_di_berita = true THEN 1 ELSE 0 END) as berita_aktif
    FROM kegiatan";
$stats = $pdo->query($stats_query)->fetch();

// Set default value for total_peserta and berita_aktif
$stats['total_peserta'] = 0;
$stats['berita_aktif'] = $stats['berita_aktif'] ?? 0;

// Get data for form dropdowns
$kategori_list = $pdo->query("SELECT DISTINCT jenis_kegiatan FROM kegiatan WHERE jenis_kegiatan IS NOT NULL ORDER BY jenis_kegiatan")->fetchAll();
$lokasi_list = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();

// Get MSH and Kohai data for dropdowns
$msh_list = $pdo->query("SELECT id, nama, kode_msh FROM majelis_sabuk_hitam ORDER BY nama")->fetchAll();
$kohai_list = $pdo->query("SELECT id, nama, kode_kohai FROM kohai ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita YPOK - YPOK Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(2px);
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        /* Modal Content */
     
        
        .detail-modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
          padding: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
         .modal-content,
         .edit-modal-content{
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Modal Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .modal-header h2 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .close-modal {
            background: #f3f4f6;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .close-modal:hover {
            background: #e5e7eb;
            color: #374151;
            transform: rotate(90deg);
        }
        
        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        }
        
        .form-group label span {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .kategori-input-group {
            position: relative;
        }
        
        .kategori-helper {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Peserta Items */
        .peserta-item {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .peserta-item select {
            flex: 1;
        }
        
        .remove-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.2s;
            min-width: 44px;
        }
        
        .remove-btn:hover {
            background: #dc2626;
            transform: scale(1.05);
        }
        
        .add-item-btn {
            width: 100%;
            padding: 12px;
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            margin-top: 10px;
            transition: all 0.2s;
        }
        
        .add-item-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #374151;
        }
        
        /* Modal Actions */
        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .btn-cancel {
            flex: 1;
            padding: 14px 24px;
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .btn-submit {
            flex: 2;
            padding: 14px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        /* Detail Modal Styles */
        .detail-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .detail-header h1 {
            color: #1e40af;
            margin: 0 0 12px 0;
            font-size: 28px;
            font-weight: 700;
            flex: 1;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        
        .detail-value {
            color: #1f2937;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .peserta-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 24px;
            border-radius: 12px;
            margin-top: 24px;
            border: 2px solid #bae6fd;
        }
        
        .peserta-section h3 {
            color: #1e40af;
            margin: 0 0 16px 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .peserta-list {
            white-space: pre-line;
            line-height: 1.8;
            color: #374151;
            font-size: 14px;
        }
        
        .detail-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .detail-actions .btn-edit,
        .detail-actions .btn-delete {
            flex: 1;
            padding: 14px 24px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
        }
        
        .detail-actions .btn-edit {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .detail-actions .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
        
        .detail-actions .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .detail-actions .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }
        
        /* Table Action Buttons */
        .btn-view,
        .btn-edit,
        .btn-delete {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
          jjustify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
            border: none;
      
        }
        
        .btn-view {
            background: #3b82f6;
            color: white;
        }
        
        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-edit {
            background: #10b981;
            color: white;
        }
        
        .btn-edit:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .status-badge.status-selesai {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.status-dijadwalkan {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.status-berlangsung {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Scrollbar Styling */
        .modal-content::-webkit-scrollbar,
        .edit-modal-content::-webkit-scrollbar,
        .detail-modal-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-content::-webkit-scrollbar-track,
        .edit-modal-content::-webkit-scrollbar-track,
        .detail-modal-content::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb,
        .edit-modal-content::-webkit-scrollbar-thumb,
        .detail-modal-content::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb:hover,
        .edit-modal-content::-webkit-scrollbar-thumb:hover,
        .detail-modal-content::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .detail-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .modal-header h2 {
                font-size: 20px;
            }
            
            .detail-header h1 {
                font-size: 22px;
            }
        }
        
        /* Search Section Styling */
        .content-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .content-header h1 {
            color: #1e40af;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .search-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex: 1;
            min-width: 300px;
        }
        
        .search-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 250px;
        }
        
        .search-input-wrapper .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #9ca3af;
            pointer-events: none;
        }
        
        .search-input-wrapper input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: white;
        }
        
        .search-input-wrapper input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-input-wrapper input::placeholder {
            color: #9ca3af;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }
        
        .filter-group select {
            padding: 8px 32px 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 160px;
        }
        
        .filter-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .btn-export {
         padding: 12px 20px;
    background: #e2e8f0;
    color: #2d3748;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(66, 107, 220, 0.3);
    white-space: nowrap;
        }
        
        .btn-export:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            padding: 12px 24px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Toggle Switch for Berita Status */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            cursor: pointer;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: all 0.3s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: all 0.3s;
            border-radius: 50%;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .toggle-switch input:disabled + .toggle-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .toggle-switch:hover .toggle-slider {
            box-shadow: 0 0 6px rgba(59, 130, 246, 0.4);
        }
        
        /* Responsive Search Actions */
        @media (max-width: 1200px) {
            .search-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form {
                width: 100%;
            }
            
            .filter-group {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-export,
            .btn-primary {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-input-wrapper,
            .filter-group {
                width: 100%;
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
        
        /* ============================================ */
        /* RESPONSIVE DESIGN - ALL DEVICES */
        /* ============================================ */
        
        /* Tablet Large & Below (max-width: 1024px) */
        @media (max-width: 1024px) {
            .content-header {
                padding: 20px;
            }
            
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                max-width: 700px;
            }
        }
        
        /* Tablet (max-width: 768px) */
        @media (max-width: 768px) {
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                width: 94%;
                max-width: 100%;
                padding: 20px 15px;
                max-height: 92vh;
            }
            
            .modal-header {
                margin-bottom: 20px;
                padding-bottom: 12px;
            }
            
            .modal-header h2 {
                font-size: 18px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-group label {
                font-size: 13px;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 11px 14px;
                font-size: 15px;
            }
            
            .modal-actions {
                flex-direction: column;
                gap: 10px;
                margin-top: 20px;
                padding-top: 15px;
            }
            
            .btn-cancel,
            .btn-submit {
                padding: 13px 20px;
                font-size: 15px;
            }
            
            .peserta-item {
                flex-direction: column;
                gap: 8px;
            }
            
            .remove-btn {
                width: 100%;
                padding: 10px;
            }
            
            /* Detail Modal Responsive */
            .detail-row {
                grid-template-columns: 1fr;
                gap: 6px;
                padding: 12px 0;
            }
            
            .detail-label {
                font-weight: 600;
                margin-bottom: 4px;
            }
            
            .detail-header h1 {
                font-size: 20px;
            }
            
            /* Export Modal */
            .export-modal {
                width: 92%;
                max-width: 100%;
            }
            
            .export-modal-header h3 {
                font-size: 16px;
            }
            
            .export-modal-footer {
                flex-direction: column;
            }
            
            .export-btn {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
            }
        }
        
        /* Mobile (max-width: 640px) */
        @media (max-width: 640px) {
            .content-header h1 {
                font-size: 18px;
                margin-bottom: 15px;
            }
            
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                width: 96%;
                padding: 18px 12px;
                border-radius: 8px;
            }
            
            .modal-header h2 {
                font-size: 17px;
            }
            
            .close-modal {
                width: 32px;
                height: 32px;
                font-size: 22px;
            }
            
            .form-group {
                margin-bottom: 16px;
            }
            
            .form-group label {
                font-size: 13px;
                margin-bottom: 6px;
            }
            
            .form-group input,
            .form-group select {
                padding: 10px 12px;
                font-size: 15px;
            }
            
            .form-group textarea {
                padding: 10px 12px;
                font-size: 15px;
                min-height: 80px;
            }
            
            .kategori-helper {
                font-size: 11px;
            }
            
            .btn-cancel,
            .btn-submit {
                padding: 12px 18px;
                font-size: 14px;
            }
            
            /* Search Actions Mobile */
            .search-input-wrapper input {
                padding: 11px 12px 11px 40px;
                font-size: 14px;
            }
            
            .filter-group {
                padding: 6px 12px;
            }
            
            .filter-group label {
                font-size: 13px;
            }
            
            .filter-group select {
                padding: 7px 28px 7px 10px;
                font-size: 13px;
                min-width: 140px;
            }
            
            .btn-export,
            .btn-primary {
                padding: 11px 18px;
                font-size: 13px;
            }
        }
        
        /* Mobile Small (max-width: 480px) */
        @media (max-width: 480px) {
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                width: 98%;
                padding: 15px 10px;
                border-radius: 6px;
                max-height: 94vh;
            }
            
            .modal-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            
            .modal-header h2 {
                font-size: 16px;
            }
            
            .close-modal {
                width: 30px;
                height: 30px;
                font-size: 20px;
            }
            
            .form-group {
                margin-bottom: 14px;
            }
            
            .form-group label {
                font-size: 12px;
                margin-bottom: 5px;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 9px 11px;
                font-size: 14px;
            }
            
            .form-group textarea {
                min-height: 70px;
            }
            
            .kategori-helper {
                font-size: 10px;
            }
            
            .modal-actions {
                margin-top: 15px;
                padding-top: 12px;
            }
            
            .btn-cancel,
            .btn-submit {
                padding: 11px 16px;
                font-size: 14px;
            }
            
            .peserta-item select {
                font-size: 14px;
            }
            
            .add-item-btn {
                padding: 10px;
                font-size: 13px;
            }
            
            /* Header */
            .content-header {
                padding: 15px;
            }
            
            .content-header h1 {
                font-size: 17px;
                margin-bottom: 12px;
            }
            
            /* Search responsive */
            .search-input-wrapper {
                min-width: 100%;
            }
            
            .search-input-wrapper input {
                padding: 10px 10px 10px 38px;
                font-size: 14px;
            }
            
            .search-input-wrapper .search-icon {
                left: 12px;
                font-size: 16px;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                padding: 10px;
            }
            
            .filter-group label {
                font-size: 12px;
            }
            
            .filter-group select {
                width: 100%;
                min-width: 100%;
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .btn-export,
            .btn-primary {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            /* Detail Modal */
            .detail-header {
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            
            .detail-header h1 {
                font-size: 18px;
            }
            
            .detail-row {
                padding: 10px 0;
            }
            
            /* Export Modal Small Mobile */
            .export-modal {
                width: 96%;
            }
            
            .export-modal-header,
            .export-modal-body,
            .export-modal-footer {
                padding: 15px;
            }
            
            .export-modal-header h3 {
                font-size: 15px;
            }
            
            .export-btn {
                padding: 11px 18px;
                font-size: 13px;
            }
        }
        
        /* Ultra Small Devices (max-width: 360px) */
        @media (max-width: 360px) {
            .modal-header h2 {
                font-size: 15px;
            }
            
            .form-group label {
                font-size: 11px;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 13px;
                padding: 8px 10px;
            }
            
            .btn-cancel,
            .btn-submit {
                font-size: 13px;
                padding: 10px 14px;
            }
            
            .content-header h1 {
                font-size: 16px;
            }
        }
        
        /* Landscape Orientation for Mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            .modal-content,
            .edit-modal-content,
            .detail-modal-content {
                max-height: 96vh;
                padding: 12px;
            }
            
            .modal-header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }
            
            .form-group {
                margin-bottom: 10px;
            }
            
            .modal-actions {
                margin-top: 12px;
                padding-top: 10px;
            }
        }
        
        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            .form-group input,
            .form-group select,
            .form-group textarea {
                min-height: 44px; /* iOS recommended touch target */
            }
            
            .btn-cancel,
            .btn-submit,
            .btn-primary,
            .btn-export {
                min-height: 44px;
            }
            
            .close-modal {
                min-width: 44px;
                min-height: 44px;
            }
            
            .remove-btn {
                min-height: 44px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data kegiatan berhasil ditambahkan</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data kegiatan berhasil diupdate</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data kegiatan berhasil dihapus</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">❌</div>
            <div class="toast-content">
                <div class="toast-title">Gagal!</div>
                <div class="toast-message"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">📰 Kelola Berita YPOK</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-blue">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_kegiatan']; ?></h3>
                        <p>Total Kegiatan</p>
                    </div>
                </div>
                
                <div class="stat-card stat-green">
                    <div class="stat-icon">📰</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['berita_aktif']; ?></h3>
                        <p>Berita Aktif</p>
                    </div>
                </div>
                
                <div class="stat-card stat-orange">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['selesai']; ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>
                
                <div class="stat-card stat-orange">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['berlangsung']; ?></h3>
                        <p>Berlangsung</p>
                    </div>
                </div>

                   <div class="stat-card stat-teal">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['dijadwalkan']; ?></h3>
                        <p>Dijadwalkan</p>
                    </div>
                </div>
            </div>
            
            <!-- Search and Actions -->
            <div class="content-header">
                <h1>Search</h1>
                
                <div class="search-actions">
                    <form method="GET" class="search-form" id="searchForm">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" name="search" id="searchInput" 
                                   placeholder="Cari kegiatan, lokasi, tanggal..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   autocomplete="off"
                           oninput="handleSearch()">
                        </div>
                        
                        <div class="filter-group">
                            <label>Periode:</label>
                            <select name="periode" id="periodeSelect" onchange="this.form.submit()">
                                <option value="semua" <?php echo $periode == 'semua' ? 'selected' : ''; ?>>Semua Periode</option>
                                <option value="1" <?php echo $periode == '1' ? 'selected' : ''; ?>>Januari</option>
                                <option value="2" <?php echo $periode == '2' ? 'selected' : ''; ?>>Februari</option>
                                <option value="3" <?php echo $periode == '3' ? 'selected' : ''; ?>>Maret</option>
                                <option value="4" <?php echo $periode == '4' ? 'selected' : ''; ?>>April</option>
                                <option value="5" <?php echo $periode == '5' ? 'selected' : ''; ?>>Mei</option>
                                <option value="6" <?php echo $periode == '6' ? 'selected' : ''; ?>>Juni</option>
                                <option value="7" <?php echo $periode == '7' ? 'selected' : ''; ?>>Juli</option>
                                <option value="8" <?php echo $periode == '8' ? 'selected' : ''; ?>>Agustus</option>
                                <option value="9" <?php echo $periode == '9' ? 'selected' : ''; ?>>September</option>
                                <option value="10" <?php echo $periode == '10' ? 'selected' : ''; ?>>Oktober</option>
                                <option value="11" <?php echo $periode == '11' ? 'selected' : ''; ?>>November</option>
                                <option value="12" <?php echo $periode == '12' ? 'selected' : ''; ?>>Desember</option>
                            </select>
                        </div>
                    </form>
                    
                    <button class="btn-export" onclick="openExportModal()">
                        📄 Export Kegiatan
                    </button>
                    
                    <button class="btn-primary" onclick="openModal()">
                        ➕ Tambah Laporan
                    </button>
                </div>
            </div>
            
            <!-- Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Kegiatan</th>
                        <th>Lokasi</th>
                        <th>Kategori</th>
                        <th>PIC</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>📰 Berita</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($kegiatan_list) > 0): ?>
                        <?php foreach($kegiatan_list as $index => $kegiatan): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($kegiatan['tanggal_kegiatan'])); ?></td>
                            <td><?php echo htmlspecialchars($kegiatan['nama_kegiatan']); ?></td>
                            <td><?php echo htmlspecialchars($kegiatan['nama_lokasi'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($kegiatan['jenis_kegiatan']); ?></td>
                            <td><?php echo htmlspecialchars($kegiatan['pic'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                // Count participants
                                if (!empty($kegiatan['peserta'])) {
                                    // Extract numbers from [X orang] pattern
                                    preg_match_all('/\[(\d+)\s+orang\]/', $kegiatan['peserta'], $matches);
                                    $total = array_sum($matches[1]);
                                    echo $total > 0 ? $total : substr_count($kegiatan['peserta'], '- ');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $kegiatan['status']; ?>">
                                    <?php 
                                    $status_display = [
                                        'selesai' => 'Selesai',
                                        'dijadwalkan' => 'Dijadwalkan',
                                        'berlangsung' => 'Berlangsung'
                                    ];
                                    echo $status_display[$kegiatan['status']] ?? $kegiatan['status'];
                                    ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <label class="toggle-switch" title="<?php echo (!empty($kegiatan['tampil_di_berita']) && $kegiatan['tampil_di_berita'] == true) ? 'Berita Aktif - Klik untuk nonaktifkan' : 'Berita Nonaktif - Klik untuk aktifkan'; ?>">
                                    <input type="checkbox" 
                                           class="toggle-berita" 
                                           data-id="<?php echo $kegiatan['id']; ?>"
                                           <?php echo (!empty($kegiatan['tampil_di_berita']) && $kegiatan['tampil_di_berita'] == true) ? 'checked' : ''; ?>
                                           onchange="toggleBerita(this)">
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <a href="javascript:void(0)" onclick="viewDetail(<?php echo $kegiatan['id']; ?>)" class="btn-view" title="Lihat Detail">👁️</a>
                                <a href="javascript:void(0)" onclick="editKegiatan(<?php echo $kegiatan['id']; ?>)" class="btn-edit" title="Edit">✏️</a>
                                <a href="kegiatan_delete.php?id=<?php echo $kegiatan['id']; ?>" 
                                   class="btn-delete" 
                                   title="Hapus"
                                   onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px;">
                                Tidak ada data kegiatan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination Info -->
            <div class="pagination-info">
                Menampilkan <?php echo count($kegiatan_list); ?> sampai <?php echo count($kegiatan_list); ?> dari <?php echo count($kegiatan_list); ?> kegiatan
            </div>
        </div>
    </div>
    
    <!-- Modal Form Tambah Kegiatan -->
    <div class="modal-overlay" id="modalKegiatan">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Berita / Kegiatan</h2>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            
            <form action="kegiatan_save.php" method="POST" id="formKegiatan" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Kegiatan <span>*</span></label>
                        <input type="text" name="nama_kegiatan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori <span>*</span></label>
                        <input type="text" name="kategori" id="kategoriInput" list="kategori-list" placeholder="Pilih atau ketik kategori baru..." required>
                        <datalist id="kategori-list">
                            <?php foreach($kategori_list as $kategori): ?>
                                <option value="<?php echo htmlspecialchars($kategori['jenis_kegiatan']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <div class="kategori-helper">💡 Pilih dari daftar atau ketik kategori baru</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal <span>*</span></label>
                        <input type="date" name="tanggal_kegiatan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi <span>*</span></label>
                        <input type="text" name="lokasi" list="lokasi-list" placeholder="Pilih atau ketik lokasi baru..." required>
                        <datalist id="lokasi-list">
                            <?php foreach($lokasi_list as $lokasi): ?>
                                <option value="<?php echo htmlspecialchars($lokasi['nama_lokasi']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" placeholder="Alamat lengkap lokasi kegiatan..." rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>PIC / Penanggung Jawab <span>*</span></label>
                        <input type="text" name="pic" placeholder="Nama PIC" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Peserta <span>*</span></label>
                        <input type="number" name="jumlah_peserta" min="0" value="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status <span>*</span></label>
                    <select name="status" required>
                        <option value="Dijadwalkan">Dijadwalkan</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Peserta MSH (Opsional)</label>
                    <div id="pesertaMSHContainer">
                        <div class="peserta-item">
                            <select name="peserta_msh[]">
                                <option value="">Pilih MSH...</option>
                                <?php foreach($msh_list as $msh): ?>
                                    <option value="<?php echo $msh['id']; ?>">
                                        <?php echo htmlspecialchars($msh['nama']); ?> 
                                        (<?php echo htmlspecialchars($msh['kode_msh']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="add-item-btn" onclick="tambahPesertaMSH()">+ Tambah Peserta MSH</button>
                </div>
                
                <div class="form-group">
                    <label>Peserta Pelatih (Opsional)</label>
                    <div id="pesertaPelatihContainer">
                        <div class="peserta-item">
                            <select name="peserta_pelatih[]">
                                <option value="">Pilih Pelatih...</option>
                                <?php foreach($kohai_list as $kohai): ?>
                                    <option value="<?php echo $kohai['id']; ?>">
                                        <?php echo htmlspecialchars($kohai['nama']); ?> 
                                        (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="add-item-btn" onclick="tambahPesertaPelatih()">+ Tambah Peserta Pelatih</button>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" placeholder="Tuliskan deskripsi kegiatan..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>📷 Foto Kegiatan (untuk ditampilkan sebagai berita)</label>
                    <input type="file" name="foto" id="fotoInputModal" accept="image/jpeg,image/jpg,image/png" onchange="previewFotoModal(this)">
                    <div class="kategori-helper">📷 Format: JPG, JPEG, PNG. Maksimal 2MB</div>
                    <div id="fotoPreviewModal" style="margin-top: 10px; display: none;">
                        <img id="previewImageModal" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                </div>
                
                <div class="form-group" style="background: #f0f9ff; padding: 20px; border-radius: 6px; border-left: 4px solid #1e40af;">
                    <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="tampil_di_berita" id="tampilDiBeritaModal" value="1" style="width: auto; margin-right: 10px; cursor: pointer;">
                        <span style="font-weight: 600; color: #1e40af;">📰 Tampilkan sebagai Berita di Halaman Utama</span>
                    </label>
                    <div class="kategori-helper" style="margin-left: 30px; margin-top: 8px;">
                        Centang jika kegiatan ini ingin ditampilkan di section Berita pada halaman utama (Guest Dashboard). Pastikan foto sudah diupload agar tampilan berita optimal.
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Detail Kegiatan -->
    <div class="modal-overlay" id="modalDetail">
        <div class="detail-modal-content">
            <div class="detail-header">
                <h1 id="detailNamaKegiatan"></h1>
                <button class="close-modal" onclick="closeDetailModal()">×</button>
            </div>
            
            <span class="status-badge" id="detailStatusBadge"></span>
            
            <div class="detail-row">
                <div class="detail-label">Kategori</div>
                <div class="detail-value" id="detailKategori"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Tanggal Kegiatan</div>
                <div class="detail-value" id="detailTanggal"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Lokasi</div>
                <div class="detail-value" id="detailLokasi"></div>
            </div>
            
            <div class="detail-row" id="detailAlamatRow" style="display: none;">
                <div class="detail-label">Alamat</div>
                <div class="detail-value" id="detailAlamat"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Penanggung Jawab (PIC)</div>
                <div class="detail-value" id="detailPIC"></div>
            </div>
            
            <div class="detail-row" id="detailBiayaRow" style="display: none;">
                <div class="detail-label">Biaya</div>
                <div class="detail-value" id="detailBiaya"></div>
            </div>
            
            <div class="detail-row" id="detailKeteranganRow" style="display: none;">
                <div class="detail-label">Keterangan</div>
                <div class="detail-value" id="detailKeterangan"></div>
            </div>
            
            <div class="peserta-section" id="detailPesertaSection" style="display: none;">
                <h3>📋 Daftar Peserta</h3>
                <div class="peserta-list" id="detailPeserta"></div>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Kegiatan -->
    <div class="modal-overlay" id="modalEdit">
        <div class="edit-modal-content">
            <div class="modal-header">
                <h2>Edit Berita / Kegiatan</h2>
                <button class="close-modal" onclick="closeEditModal()">×</button>
            </div>
            
            <form action="kegiatan_update.php" method="POST" id="formEdit" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="foto_lama" id="editFotoLama">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Kegiatan <span>*</span></label>
                        <input type="text" name="nama_kegiatan" id="editNamaKegiatan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori <span>*</span></label>
                        <input type="text" name="kategori" id="editKategori" list="kategori-list" placeholder="Pilih atau ketik kategori baru..." required>
                        <div class="kategori-helper">💡 Pilih dari daftar atau ketik kategori baru</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal <span>*</span></label>
                        <input type="date" name="tanggal_kegiatan" id="editTanggal" required>
                    </div>
                     
                    <div class="form-group">
                        <label>Lokasi <span>*</span></label>
                        <input type="text" name="lokasi" id="editLokasi" list="lokasi-list" placeholder="Pilih atau ketik lokasi baru..." required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" id="editAlamat" placeholder="Alamat lengkap lokasi kegiatan..." rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>PIC / Penanggung Jawab <span>*</span></label>
                        <input type="text" name="pic" id="editPIC" placeholder="Nama PIC" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Peserta</label>
                        <input type="number" name="jumlah_peserta" id="editJumlahPeserta" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status <span>*</span></label>
                    <select name="status" id="editStatus" required>
                        <option value="Dijadwalkan">Dijadwalkan</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Peserta MSH (Opsional)</label>
                    <div id="editPesertaMSHContainer">
                        <div class="peserta-item">
                            <select name="peserta_msh[]">
                                <option value="">Pilih MSH...</option>
                                <?php foreach($msh_list as $msh): ?>
                                    <option value="<?php echo $msh['id']; ?>">
                                        <?php echo htmlspecialchars($msh['nama']); ?> 
                                        (<?php echo htmlspecialchars($msh['kode_msh']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="add-item-btn" onclick="tambahEditPesertaMSH()">+ Tambah Peserta MSH</button>
                </div>
                
                <div class="form-group">
                    <label>Peserta Pelatih (Opsional)</label>
                    <div id="editPesertaPelatihContainer">
                        <div class="peserta-item">
                            <select name="peserta_pelatih[]">
                                <option value="">Pilih Pelatih...</option>
                                <?php foreach($kohai_list as $kohai): ?>
                                    <option value="<?php echo $kohai['id']; ?>">
                                        <?php echo htmlspecialchars($kohai['nama']); ?> 
                                        (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="add-item-btn" onclick="tambahEditPesertaPelatih()">+ Tambah Peserta Pelatih</button>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" id="editDeskripsi" placeholder="Tuliskan deskripsi kegiatan..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>📷 Foto Kegiatan (untuk ditampilkan sebagai berita)</label>
                    <div id="editCurrentFoto" style="margin-bottom: 10px; display: none;">
                        <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Foto saat ini:</div>
                        <img id="editCurrentFotoImg" src="" alt="Foto saat ini" style="max-width: 200px; max-height: 150px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                    <input type="file" name="foto" id="editFotoInput" accept="image/jpeg,image/jpg,image/png" onchange="previewEditFoto(this)">
                    <div class="kategori-helper">📷 Format: JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.</div>
                    <div id="editFotoPreview" style="margin-top: 10px; display: none;">
                        <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Preview foto baru:</div>
                        <img id="editPreviewImage" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                </div>
                
                <div class="form-group" style="background: #f0f9ff; padding: 20px; border-radius: 6px; border-left: 4px solid #1e40af;">
                    <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="tampil_di_berita" id="editTampilDiBerita" value="1" style="width: auto; margin-right: 10px; cursor: pointer;">
                        <span style="font-weight: 600; color: #1e40af;">📰 Tampilkan sebagai Berita di Halaman Utama</span>
                    </label>
                    <div class="kategori-helper" style="margin-left: 30px; margin-top: 8px;">
                        Centang jika kegiatan ini ingin ditampilkan di section Berita pada halaman utama (Guest Dashboard). Pastikan foto sudah diupload agar tampilan berita optimal.
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-submit">Update Data</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Export -->
    <div class="modal-overlay export-modal-overlay" id="modalExport">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3>
                    <span>📊</span>
                    <span>Export Laporan Kegiatan</span>
                </h3>
                <button class="export-modal-close" onclick="closeExportModal()"type="button">×</button>
            </div>
            
            
                
                <form id="formExport" onsubmit="handleExport(event)">
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
                        <select name="periode_export" id="periodeExport" required onchange="toggleCustomDate(this.value)">
                            <option value="semua">🗓️ Semua Data</option>
                            <option value="month">📅 Bulan Ini</option>
                            <option value="last_month">📆 Bulan Lalu</option>
                            <option value="custom">🔧 Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div class="export-form-group" id="customDateRange" style="display: none;">
                        <label>Range Tanggal</label>
                        <div class="export-signature-row">
                            <input type="date" name="dari_tanggal" id="startDate" class="export-signature-input" placeholder="Dari Tanggal">
                            <input type="date" name="sampai_tanggal" id="endDate" class="export-signature-input" placeholder="Sampai Tanggal">
                        </div>
                    </div>
                    
                    <div class="export-signature-section">
                        <div class="export-signature-title">Tanda Tangan Digital</div>
                        <div class="export-signature-row">
                            <input type="text" name="ketua_ypok" id="exportKetua" class="export-signature-input" placeholder="Ketua YPOK" value="Ketua YPOK" required>
                            <input type="text" name="admin_pembuat" id="exportAdmin" class="export-signature-input" placeholder="Pembuat Laporan" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly required style="background: #f0f0f0; cursor: not-allowed;">
                        </div>
                    </div>
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
    
    
    <script src="../../assets/js/app.js"></script>
    <script>
        // MSH options for dynamic addition
        const mshOptions = `
            <option value="">Pilih MSH...</option>
            <?php foreach($msh_list as $msh): ?>
                <option value="<?php echo $msh['id']; ?>">
                    <?php echo addslashes(htmlspecialchars($msh['nama'])); ?> 
                    (<?php echo htmlspecialchars($msh['kode_msh']); ?>)
                </option>
            <?php endforeach; ?>
        `;
        
        // Kohai options for dynamic addition
        const kohaiOptions = `
            <option value="">Pilih Pelatih...</option>
            <?php foreach($kohai_list as $kohai): ?>
                <option value="<?php echo $kohai['id']; ?>">
                    <?php echo addslashes(htmlspecialchars($kohai['nama'])); ?> 
                    (<?php echo htmlspecialchars($kohai['kode_kohai']); ?>)
                </option>
            <?php endforeach; ?>
        `;
        
        function exportData() {
            openExportModal();
        }
        
        function openModal() {
            document.getElementById('modalKegiatan').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('modalKegiatan').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('formKegiatan').reset();
            
            // Reset foto preview
            const fotoPreview = document.getElementById('fotoPreviewModal');
            if (fotoPreview) {
                fotoPreview.style.display = 'none';
            }
            
            // Reset to one item each
            document.getElementById('pesertaMSHContainer').innerHTML = `
                <div class="peserta-item">
                    <select name="peserta_msh[]">${mshOptions}</select>
                </div>
            `;
            document.getElementById('pesertaPelatihContainer').innerHTML = `
                <div class="peserta-item">
                    <select name="peserta_pelatih[]">${kohaiOptions}</select>
                </div>
            `;
        }
        
        function tambahPesertaMSH() {
            const container = document.getElementById('pesertaMSHContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_msh[]">${mshOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function tambahPesertaPelatih() {
            const container = document.getElementById('pesertaPelatihContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_pelatih[]">${kohaiOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function previewFotoModal(input) {
            const preview = document.getElementById('fotoPreviewModal');
            const previewImage = document.getElementById('previewImageModal');
            
            if (input.files && input.files[0]) {
                // Validate file size (2MB)
                if (input.files[0].size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(input.files[0].type)) {
                    alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        function viewDetail(id) {
            // Fetch detail data via AJAX
            fetch('kegiatan_get_detail.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const k = data.data;
                        
                        // Set header
                        document.getElementById('detailNamaKegiatan').textContent = k.nama_kegiatan;
                        
                        // Set status badge
                        const statusBadge = document.getElementById('detailStatusBadge');
                        statusBadge.className = 'status-badge status-' + k.status;
                        statusBadge.textContent = k.status_display;
                        
                        // Set detail values
                        document.getElementById('detailKategori').textContent = k.jenis_kegiatan;
                        document.getElementById('detailTanggal').textContent = k.tanggal_formatted;
                        document.getElementById('detailLokasi').textContent = k.nama_lokasi;
                        document.getElementById('detailPIC').textContent = k.pic || '-';
                        
                        // Alamat
                        if (k.alamat) {
                            document.getElementById('detailAlamat').textContent = k.alamat;
                            document.getElementById('detailAlamatRow').style.display = 'grid';
                        } else {
                            document.getElementById('detailAlamatRow').style.display = 'none';
                        }
                        
                        // Biaya
                        if (k.biaya && k.biaya > 0) {
                            document.getElementById('detailBiaya').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(k.biaya);
                            document.getElementById('detailBiayaRow').style.display = 'grid';
                        } else {
                            document.getElementById('detailBiayaRow').style.display = 'none';
                        }
                        
                        // Keterangan
                        if (k.keterangan) {
                            document.getElementById('detailKeterangan').innerHTML = k.keterangan.replace(/\n/g, '<br>');
                            document.getElementById('detailKeteranganRow').style.display = 'grid';
                        } else {
                            document.getElementById('detailKeteranganRow').style.display = 'none';
                        }
                        
                        // Peserta
                        if (k.peserta) {
                            document.getElementById('detailPeserta').textContent = k.peserta;
                            document.getElementById('detailPesertaSection').style.display = 'block';
                        } else {
                            document.getElementById('detailPesertaSection').style.display = 'none';
                        }

                        // Show modal
                        document.getElementById('modalDetail').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        alert('Gagal memuat detail: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
        }
        
        function closeDetailModal() {
            document.getElementById('modalDetail').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close detail modal when clicking outside
        document.getElementById('modalDetail').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailModal();
            }
        });
        
        // Search functionality
        let searchTimeout;
        function handleSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.getElementById('searchForm').submit();
            }, 500); // Wait 500ms after user stops typing
        }
        
        // Close modal when clicking outside
        document.getElementById('modalKegiatan').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Edit functions
        function editKegiatan(id) {
            // Fetch detail data via AJAX
            fetch('kegiatan_get_detail.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const k = data.data;
                        
                        // Set form values
                        document.getElementById('editId').value = k.id;
                        document.getElementById('editNamaKegiatan').value = k.nama_kegiatan;
                        document.getElementById('editKategori').value = k.jenis_kegiatan;
                        document.getElementById('editTanggal').value = k.tanggal_kegiatan;
                        document.getElementById('editLokasi').value = k.nama_lokasi;
                        document.getElementById('editAlamat').value = k.alamat || '';
                        document.getElementById('editPIC').value = k.pic || '';
                        document.getElementById('editJumlahPeserta').value = 0;
                        document.getElementById('editDeskripsi').value = k.keterangan || '';
                        
                        // Set foto lama dan tampilkan preview jika ada
                        document.getElementById('editFotoLama').value = k.foto || '';
                        if (k.foto) {
                            document.getElementById('editCurrentFoto').style.display = 'block';
                            document.getElementById('editCurrentFotoImg').src = 'uploads/kegiatan/' + k.foto;
                        } else {
                            document.getElementById('editCurrentFoto').style.display = 'none';
                        }
                        // Reset preview foto baru
                        document.getElementById('editFotoPreview').style.display = 'none';
                        document.getElementById('editFotoInput').value = '';
                        
                        // Set checkbox tampil di berita
                        document.getElementById('editTampilDiBerita').checked = k.tampil_di_berita ? true : false;
                        
                        // Set status
                        const statusMap = {
                            'selesai': 'Selesai',
                            'dijadwalkan': 'Dijadwalkan',
                            'berlangsung': 'Berlangsung'
                        };
                        document.getElementById('editStatus').value = statusMap[k.status] || 'Dijadwalkan';
                        
                        // Reset peserta containers
                        document.getElementById('editPesertaMSHContainer').innerHTML = `
                            <div class="peserta-item">
                                <select name="peserta_msh[]">
                                    <option value="">Pilih MSH...</option>
                                    ${mshOptions}
                                </select>
                            </div>
                        `;
                        document.getElementById('editPesertaPelatihContainer').innerHTML = `
                            <div class="peserta-item">
                                <select name="peserta_pelatih[]">
                                    <option value="">Pilih Pelatih...</option>
                                    ${kohaiOptions}
                                </select>
                            </div>
                        `;
                        
                        // Show modal
                        document.getElementById('modalEdit').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        alert('Gagal memuat data: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
        }
        
        function closeEditModal() {
            document.getElementById('modalEdit').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('formEdit').reset();
            // Reset preview
            document.getElementById('editFotoPreview').style.display = 'none';
            document.getElementById('editCurrentFoto').style.display = 'none';
        }
        
        function previewEditFoto(input) {
            const preview = document.getElementById('editFotoPreview');
            const previewImg = document.getElementById('editPreviewImage');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validasi tipe file
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
        
        function tambahEditPesertaMSH() {
            const container = document.getElementById('editPesertaMSHContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_msh[]">${mshOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        function tambahEditPesertaPelatih() {
            const container = document.getElementById('editPesertaPelatihContainer');
            const div = document.createElement('div');
            div.className = 'peserta-item';
            div.innerHTML = `
                <select name="peserta_pelatih[]">${kohaiOptions}</select>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
        
        // Close edit modal when clicking outside
        document.getElementById('modalEdit').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Export Modal Functions
        function openExportModal() {
            document.getElementById('modalExport').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        // Toggle Berita Function
        function toggleBerita(checkbox) {
            const id = checkbox.getAttribute('data-id');
            const status = checkbox.checked;
            
            // Disable checkbox while processing
            checkbox.disabled = true;
            
            // Send AJAX request
            fetch('toggle_berita.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id + '&status=' + (status ? '1' : '0')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast('success', data.message);
                    
                    // Update tooltip
                    checkbox.parentElement.title = status ? 
                        'Berita Aktif - Klik untuk nonaktifkan' : 
                        'Berita Nonaktif - Klik untuk aktifkan';
                } else {
                    // Revert checkbox status on error
                    checkbox.checked = !status;
                    showToast('error', data.message || 'Gagal mengubah status berita');
                }
            })
            .catch(error => {
                // Revert checkbox status on error
                checkbox.checked = !status;
                showToast('error', 'Terjadi kesalahan: ' + error);
            })
            .finally(() => {
                // Re-enable checkbox
                checkbox.disabled = false;
            });
        }
        
        // Show Toast Notification
        function showToast(type, message) {
            const existingToast = document.getElementById('dynamicToast');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.id = 'dynamicToast';
            toast.className = 'toast-notification toast-' + type;
            toast.innerHTML = `
                <div class="toast-icon">${type === 'success' ? '✓' : '✕'}</div>
                <div class="toast-content">
                    <div class="toast-title">${type === 'success' ? 'Berhasil!' : 'Gagal!'}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 3000);
        }
        
        function closeExportModal() {
            document.getElementById('modalExport').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('formExport').reset();
            document.getElementById('customDateRange').style.display = 'none';
        }
        
        function toggleCustomDate(value) {
            const customDateRange = document.getElementById('customDateRange');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

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
        
        function handleExport(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            // Build URL parameters
            const params = new URLSearchParams();
            params.append('format', formData.get('format_export'));
            params.append('periode', formData.get('periode_export'));
            params.append('ketua', formData.get('ketua_ypok'));
            params.append('admin', formData.get('administrator'));

            if (formData.get('periode_export') === 'custom') {
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
            window.open('actions/export_kegiatan.php?' + params.toString(), '_blank');

            // Close modal
            closeExportModal();
        }
        
        // Close export modal when clicking outside
        document.getElementById('modalExport').addEventListener('click', function(e) {
            if (e.target === this) {
                closeExportModal();
            }
        });
    </script>
</body>
</html>

