# Script untuk memperbaiki path relatif di semua file pages/
# Mengganti 'config/supabase.php' dengan '../../config/supabase.php'
# Mengganti 'components/navbar.php' dengan '../../components/navbar.php'

Write-Host "Memperbaiki path relatif di folder pages/..." -ForegroundColor Cyan

$files = Get-ChildItem -Path "pages" -Filter "*.php" -Recurse

$countConfig = 0
$countComponent = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Fix config path
    if ($content -match "require_once 'config/supabase\.php'") {
        $content = $content -replace "require_once 'config/supabase\.php'", "require_once '../../config/supabase.php'"
        $countConfig++
        $modified = $true
    }
    
    # Fix components path for navbar
    if ($content -match "include 'components/navbar\.php'") {
        $content = $content -replace "include 'components/navbar\.php'", "include '../../components/navbar.php'"
        $countComponent++
        $modified = $true
    }
    
    # Fix auth path
    if ($content -match "require_once 'auth/") {
        $content = $content -replace "require_once 'auth/", "require_once '../../auth/"
        $modified = $true
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "  ✓ Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "`nSelesai!" -ForegroundColor Green
Write-Host "  - Config paths fixed: $countConfig files" -ForegroundColor Yellow
Write-Host "  - Component paths fixed: $countComponent files" -ForegroundColor Yellow
Write-Host "`nSilakan refresh browser dan coba login lagi." -ForegroundColor Cyan
