<?php
require_once '../config/supabase.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_kategori'])) {
    try {
        $nama_kategori = trim($_POST['nama_kategori']);
        
        // Check if kategori already exists
        $stmt = $pdo->prepare("SELECT id FROM kategori_kegiatan WHERE nama_kategori = ?");
        $stmt->execute([$nama_kategori]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Kategori sudah ada']);
            exit();
        }
        
        // Insert new kategori
        $stmt = $pdo->prepare("INSERT INTO kategori_kegiatan (nama_kategori) VALUES (?)");
        $stmt->execute([$nama_kategori]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
// File ini tidak diperlukan - kategori diinput langsung via form
