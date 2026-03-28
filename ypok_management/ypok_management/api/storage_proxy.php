<?php
require_once '../config/storage.php';

$relativePath = $_GET['path'] ?? '';
$relativePath = ypok_normalize_path($relativePath);

if ($relativePath === '' || !str_starts_with($relativePath, 'uploads/')) {
    http_response_code(400);
    echo 'Invalid path';
    exit();
}

$localFile = dirname(__DIR__) . '/' . $relativePath;
if (file_exists($localFile)) {
    $mime = mime_content_type($localFile) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=3600');
    readfile($localFile);
    exit();
}

if (!ypok_storage_enabled()) {
    http_response_code(404);
    echo 'File not found';
    exit();
}

$bucket = rawurlencode(ypok_storage_bucket());
$segments = array_map('rawurlencode', explode('/', $relativePath));
$objectPath = implode('/', $segments);
$publicUrl = ypok_storage_url() . '/storage/v1/object/public/' . $bucket . '/' . $objectPath;

header('Location: ' . $publicUrl, true, 302);
exit();
