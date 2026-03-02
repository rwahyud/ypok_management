<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

// Get file path before delete
$stmt_file = $pdo->prepare("SELECT file_dokumen FROM legalitas WHERE id = ?");
$stmt_file->execute([$id]);
$file_data = $stmt_file->fetch();

// Delete file if exists
if($file_data && $file_data['file_dokumen'] && file_exists($file_data['file_dokumen'])) {
    unlink($file_data['file_dokumen']);
}

// Delete record
$stmt = $pdo->prepare("DELETE FROM legalitas WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?deleted=1#dokumenSection');
exit();
?>
