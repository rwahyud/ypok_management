<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM pengurus WHERE id = ?");
$stmt->execute([$id]);

header('Location: legalitas.php?deleted=1');
exit();
?>
