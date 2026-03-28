<?php
function ypok_storage_bucket(): string {
    return getenv('SUPABASE_STORAGE_BUCKET') ?: 'ypok-files';
}

function ypok_storage_url(): string {
    return rtrim((string)(getenv('SUPABASE_URL') ?: ''), '/');
}

function ypok_storage_service_key(): string {
    return (string)(getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '');
}

function ypok_storage_enabled(): bool {
    return ypok_storage_url() !== '' && ypok_storage_service_key() !== '';
}

function ypok_normalize_path(string $path): string {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $path = str_replace(['../', './'], '', $path);
    return $path;
}

function ypok_upload_file(string $tmpFile, string $relativePath, string $contentType = 'application/octet-stream'): bool {
    $relativePath = ypok_normalize_path($relativePath);

    if (ypok_storage_enabled()) {
        $segments = array_map('rawurlencode', explode('/', $relativePath));
        $objectPath = implode('/', $segments);
        $bucket = rawurlencode(ypok_storage_bucket());
        $url = ypok_storage_url() . '/storage/v1/object/' . $bucket . '/' . $objectPath;

        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        $payload = file_get_contents($tmpFile);
        if ($payload === false) {
            curl_close($ch);
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . ypok_storage_service_key(),
                'Authorization: Bearer ' . ypok_storage_service_key(),
                'Content-Type: ' . $contentType,
                'x-upsert: true'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    $local = dirname(__DIR__) . '/' . $relativePath;
    $dir = dirname($local);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (is_uploaded_file($tmpFile)) {
        return move_uploaded_file($tmpFile, $local);
    }

    return copy($tmpFile, $local);
}

function ypok_delete_file(string $relativePath): bool {
    $relativePath = ypok_normalize_path($relativePath);
    if ($relativePath === '' || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
        return true;
    }

    if (ypok_storage_enabled()) {
        $segments = array_map('rawurlencode', explode('/', $relativePath));
        $objectPath = implode('/', $segments);
        $bucket = rawurlencode(ypok_storage_bucket());
        $url = ypok_storage_url() . '/storage/v1/object/' . $bucket . '/' . $objectPath;

        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'apikey: ' . ypok_storage_service_key(),
                'Authorization: Bearer ' . ypok_storage_service_key(),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    $local = dirname(__DIR__) . '/' . $relativePath;
    return !file_exists($local) || @unlink($local);
}

function ypok_file_exists_compat(?string $path): bool {
    if (empty($path)) {
        return false;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return true;
    }
    if (ypok_storage_enabled() && str_starts_with($path, 'uploads/')) {
        return true;
    }
    return file_exists(dirname(__DIR__) . '/' . ltrim($path, '/'));
}

function ypok_public_asset_url(?string $path): ?string {
    if (empty($path)) {
        return null;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $path = ypok_normalize_path($path);
    return '/' . $path;
}
