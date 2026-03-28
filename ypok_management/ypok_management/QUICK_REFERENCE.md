# 🔧 Quick Reference - Troubleshooting Guide

## 📁 Struktur Folder

```
Root (7 files)
├── pages/       (30 files) - Semua halaman aplikasi
├── api/         (5 files)  - AJAX endpoints
├── actions/     (29 files) - Backend handlers
├── export/      (6 files)  - Export & print
├── config/      (1 file)   - Database config
├── database/    (8 files)  - SQL files
├── components/  (1 file)   - Reusable components
├── docs/        (1 file)   - Documentation
├── uploads/     (folders)  - Upload directory
└── assets/      (folders)  - CSS, JS, Icons
```

---

## 🚨 Common Errors & Solutions

### 1. Error: "config/database.php not found"

**Penyebab:** Path relatif tidak sesuai dengan lokasi file

**Solusi:**
```php
// ❌ SALAH (dari pages/)
require_once 'config/database.php';

// ✅ BENAR (dari pages/)
require_once '../config/database.php';
```

**Quick Fix:**
- Dari **root**: `config/database.php`
- Dari **pages/**: `../config/database.php`
- Dari **api/**: `../config/database.php`
- Dari **actions/**: `../config/database.php`
- Dari **export/**: `../config/database.php`

---

### 2. Error: Redirect tidak berfungsi / "Page not found"

**Penyebab:** Path redirect salah setelah reorganisasi

**Solusi:**
```php
// ❌ SALAH (dari actions/)
header("Location: dashboard.php");

// ✅ BENAR (dari actions/)
header("Location: ../pages/dashboard.php");
```

**Quick Fix Pattern:**
| Dari Folder | Ke Dashboard | Ke Login |
|------------|--------------|----------|
| **actions/** | `../pages/dashboard.php` | `../index.php` |
| **pages/** | `dashboard.php` | `../index.php` |
| **api/** | `../pages/dashboard.php` | `../index.php` |

---

### 3. Error: CSS/JS tidak load

**Penyebab:** Path assets tidak sesuai

**Solusi:**
```html
<!-- ❌ SALAH (dari pages/) -->
<link href="assets/css/style.css">
<script src="assets/js/app.js"></script>

<!-- ✅ BENAR (dari pages/) -->
<link href="../assets/css/style.css">
<script src="../assets/js/app.js"></script>
```

**Quick Fix:**
- Dari **root**: `assets/css/style.css`
- Dari **pages/**: `../assets/css/style.css`
- Dari **export/**: `../assets/css/style.css`

---

### 4. Error: Form submit tidak berfungsi

**Penyebab:** Action path form salah

**Solusi:**
```html
<!-- ❌ SALAH (dari pages/) -->
<form action="actions/login.php">

<!-- ✅ BENAR (dari pages/) -->
<form action="../actions/login.php">
```

**Quick Fix:**
```html
<!-- Dari pages/ -->
<form action="../actions/login.php">
<form action="../actions/save_pendaftaran_msh.php">
<form action="../actions/pembayaran_action.php">
```

---

### 5. Error: Ajax/Fetch tidak jalan

**Penyebab:** URL endpoint salah

**Solusi:**
```javascript
// ❌ SALAH (dari pages/)
fetch('api/kohai_get.php')

// ✅ BENAR (dari pages/)
fetch('../api/kohai_get.php')
```

**Quick Fix:**
```javascript
// Dari pages/
fetch('../api/kohai_get.php')
fetch('../api/dashboard_chart_data.php')
fetch('../actions/get_dojo.php')
```

---

### 6. Error: Upload file gagal

**Penyebab:** Permission folder uploads atau path salah

**Solusi 1 - Cek Permission:**
```powershell
# Windows: Pastikan folder uploads bisa ditulis
icacls "c:\xampp\htdocs\ypok_management\ypok_management\uploads" /grant Users:F
```

**Solusi 2 - Cek Path:**
```php
// ❌ SALAH (dari pages/)
$uploadDir = "uploads/kohai/";

// ✅ BENAR (dari pages/)
$uploadDir = "../uploads/kohai/";
```

---

### 7. Error: Database connection failed

**Penyebab:** Database belum diimport atau config salah

**Solusi:**
1. Import database terlebih dahulu:
   - Buka: `http://localhost/phpmyadmin`
   - Import file: `database/ypok_database.sql`

2. Cek config di `config/database.php`:
```php
$host = "localhost";
$username = "root";
$password = "";  // Default XAMPP kosong
$database = "ypok_management";
```

3. Pastikan XAMPP MySQL sudah running

---

### 8. Error: Navbar tidak muncul atau broken

**Penyebab:** Include path ke navbar salah

**Solusi:**
```php
// ❌ SALAH (dari pages/)
include 'components/navbar.php';

// ✅ BENAR (dari pages/)
include '../components/navbar.php';
```

---

### 9. Error: Export/Print PDF tidak jalan

**Penyebab:** Path ke file export salah

**Solusi:**
```html
<!-- ❌ SALAH (dari pages/) -->
<a href="export/pendaftaran_pdf.php">

<!-- ✅ BENAR (dari pages/) -->
<a href="../export/pendaftaran_pdf.php">
```

---

### 10. Error: JavaScript redirect error

**Penyebab:** Path dalam JavaScript salah

**Solusi:**
```javascript
// ❌ SALAH (dari pages/)
window.location.href = 'actions/logout.php';
location.href = "index.php";

// ✅ BENAR (dari pages/)
window.location.href = '../actions/logout.php';
location.href = "../index.php";
```

---

## 🎯 Path Cheat Sheet

### Dari Root (index.php, register.php)
```php
require_once 'config/database.php';
header('Location: pages/dashboard.php');
```
```html
<link href="assets/css/style.css">
<form action="actions/login.php">
```

### Dari Pages (dashboard.php, msh.php, dll)
```php
require_once '../config/database.php';
include '../components/navbar.php';
header('Location: dashboard.php');        // Antar pages
header('Location: ../index.php');         // Ke root
```
```html
<link href="../assets/css/style.css">
<form action="../actions/login.php">
<a href="../export/pendaftaran_pdf.php">
```
```javascript
window.location.href = '../actions/logout.php';
fetch('../api/kohai_get.php');
```

### Dari Actions (login.php, save_*.php, dll)
```php
require_once '../config/database.php';
header('Location: ../pages/dashboard.php');
header('Location: ../index.php');
```

### Dari API (kohai_get.php, dll)
```php
require_once '../config/database.php';
```

### Dari Export (pendaftaran_pdf.php, dll)
```php
require_once '../config/database.php';
```
```html
<link href="../assets/css/style.css">
```

---

## 🧪 Testing Checklist

### Login Test
```
1. Buka: http://localhost/ypok_management/index.php
2. Login: admin / admin123
3. Harus redirect ke: http://localhost/ypok_management/pages/dashboard.php
```

### Navigation Test
```
Klik setiap menu di navbar:
✓ Dashboard → pages/dashboard.php
✓ Data MSH → pages/msh.php
✓ Data Kohai → pages/kohai.php
✓ Lokasi → pages/lokasi.php
✓ Pembayaran → pages/pembayaran.php
✓ Legalitas → pages/legalitas.php
✓ Kelola Tampilan Kegiatan → pages/kegiatan_display.php
✓ Laporan Kegiatan → pages/laporan_kegiatan.php
✓ Laporan Keuangan → pages/laporan_keuangan.php
```

### CRUD Test
```
1. Test Tambah Data (Create)
2. Test Lihat/Detail Data (Read)
3. Test Edit Data (Update)
4. Test Hapus Data (Delete)
```

---

## 📞 Quick Command Reference

### Cek File di Folder
```powershell
Get-ChildItem "c:\xampp\htdocs\ypok_management\ypok_management\pages" | Select-Object Name
```

### Search Pattern di File
```powershell
Get-ChildItem "c:\xampp\htdocs\ypok_management\ypok_management\pages\*.php" | Select-String "require_once"
```

### Replace Pattern Batch
```powershell
Get-ChildItem "pages\*.php" | ForEach-Object {
    (Get-Content $_.FullName -Raw) -replace "OLD", "NEW" | Set-Content $_.FullName -NoNewline
}
```

---

## 🆘 Emergency Rollback

Jika terjadi masalah serius dan perlu rollback:

1. **Backup ada di:** (sesuaikan jika ada backup)
2. **Copy kembali ke:** `c:\xampp\htdocs\ypok_management\ypok_management`
3. **Restart XAMPP**

---

## 📋 Versi Info

**Version:** 1.0  
**Last Updated:** 2026-03-04  
**Status:** ✅ Production Ready  
**Total Files:** 84 files terorganisir

---

**💡 Tip:** Simpan file ini untuk referensi cepat saat troubleshooting!
