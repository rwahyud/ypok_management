<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM kegiatan WHERE id = ?");
$stmt->execute([$id]);

header('Location: laporan_kegiatan.php?deleted=1');
exit();
?>
