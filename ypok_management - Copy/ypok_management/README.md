# 📁 YPOK Management System - Struktur Folder

## 🏗️ Struktur Direktori

```
ypok_management/
├── 📄 index.php                # Login page (entry point)
├── 📄 register.php             # Registration page
├── 📄 manifest.json            # PWA manifest
├── 📄 sw.js                    # Service worker
├── 📄 .htaccess               # Apache configuration
│
├── 📁 pages/                   # Semua halaman aplikasi
│   ├── dashboard.php           # Dashboard utama
│   ├── kohai.php               # Data Kohai
│   ├── kohai_detail.php        # Detail Kohai
│   ├── msh.php                 # Data Master Sabuk Hitam
│   ├── msh_detail.php          # Detail MSH
│   ├── legalitas.php           # Dokumen Legalitas
│   ├── legalitas_add.php       # Tambah Legalitas
│   ├── legalitas_edit.php      # Edit Legalitas
│   ├── legalitas_update.php    # Update Legalitas
│   ├── legalitas_delete.php    # Hapus Legalitas
│   ├── pembayaran.php          # Data Pembayaran
│   ├── pengurus_add.php        # Tambah Pengurus
│   ├── pengurus_edit.php       # Edit Pengurus
│   ├── pengurus_update.php     # Update Pengurus
│   ├── pengurus_delete.php     # Hapus Pengurus
│   ├── kegiatan_add.php        # Tambah Kegiatan
│   ├── kegiatan_edit.php       # Edit Kegiatan
│   ├── kegiatan_update.php     # Update Kegiatan
│   ├── kegiatan_save.php       # Simpan Kegiatan
│   ├── kegiatan_delete.php     # Hapus Kegiatan
│   ├── kegiatan_detail.php     # Detail Kegiatan
│   ├── laporan_kegiatan.php    # Laporan Kegiatan
│   ├── laporan_keuangan.php    # Laporan Keuangan
│   ├── lokasi.php              # Data Lokasi/Provinsi/Dojo
│   ├── toko.php                # Toko Produk
│   └── proses_transaksi.php    # Proses Transaksi Keuangan
│
├── 📁 api/                     # API & Ajax endpoints
│   ├── dashboard_chart_data.php    # Data chart dashboard
│   ├── kohai_get.php               # Get data Kohai
│   ├── msh_get.php                 # Get data MSH
│   ├── kegiatan_get_detail.php     # Get detail kegiatan
│   └── kategori_add_ajax.php       # Tambah kategori (Ajax)
│
├── 📁 actions/                 # Backend action handlers
│   ├── login.php               # Proses login
│   ├── logout.php              # Proses logout
│   ├── register_action.php     # Proses registrasi
│   ├── pembayaran_action.php           # CRUD pembayaran
│   ├── dojo_action.php                 # CRUD dojo
│   ├── provinsi_action.php             # CRUD provinsi
│   ├── add_produk.php                  # Tambah produk
│   ├── edit_produk.php                 # Edit produk
│   ├── delete_produk.php               # Hapus produk
│   ├── add_kategori.php                # Tambah kategori
│   ├── delete_kategori.php             # Hapus kategori
│   ├── add_transaksi.php               # Tambah transaksi
│   ├── delete_transaksi.php            # Hapus transaksi
│   ├── get_dojo.php                    # Get data dojo
│   ├── get_produk.php                  # Get data produk
│   ├── get_provinsi.php                # Get data provinsi
│   ├── get_provinsi_detail.php         # Get detail provinsi
│   └── get_transaksi.php               # Get data transaksi
│
├── 📁 export/                  # Export & Print files
│   ├── export_laporan_keuangan.php     # Export laporan keuangan
│   ├── export_laporan_pdf.php          # Export laporan PDF
│   ├── invoice_pembayaran.php          # Invoice pembayaran
│   ├── export_kegiatan.php             # Export kegiatan
│   ├── export_pembayaran.php           # Export pembayaran
│   └── export_transaksi_laporan.php    # Export transaksi
│
│   ├── login.php               # Login handler
│   ├── logout.php              # Logout handler
│   └── register.php            # Registration handler
│
├── 📁 components/              # Reusable components
│   └── navbar.php              # Navigation bar
│
├── 📁 config/                  # Configuration files
│   └── database.php            # Database connection
│
├── 📁 database/                # Database files
│   ├── ypok_database.sql       # 🌟 DATABASE UTAMA (GUNAKAN INI)
│   ├── update_provinsi_agregat.sql     # Update agregat provinsi
│   ├── update_pembayaran_struktur.sql  # Update struktur pembayaran
│   ├── run_update_provinsi_agregat.php # Script update provinsi
│   ├── create_default_user.php         # Buat user default
│   ├── fix_login.php                   # Fix login
│   ├── README.md                       # Dokumentasi database
│   └── README_UPDATE_PROVINSI.md       # Dokumentasi update provinsi
│
├── 📁 docs/                    # Documentation
│   └── INSTRUKSI_UPDATE_PROVINSI.txt   # Instruksi update provinsi
│
├── 📁 assets/                  # Static assets
│   ├── css/                    # Stylesheet files
│   │   ├── style.css
│   │   ├── login.css
│   │   ├── pendaftaran.css
│   │   ├── pembayaran.css
│   │   └── lokasi.css
│   ├── js/                     # JavaScript files
│   │   ├── app.js
│   │   ├── pendaftaran.js
│   │   ├── pembayaran.js
│   │   └── lokasi.js
│   └── icons/                  # Icon files
│       ├── icon-192x192.svg
│       └── icon-512x512.svg
│
└── 📁 uploads/                 # Upload directory
    ├── kohai/                  # Foto Kohai
    ├── msh/                    # Foto MSH
    ├── pengurus/               # Foto Pengurus
    ├── dokumen/                # Dokumen Legalitas
    ├── produk/                 # Gambar Produk
    └── provinsi/               # Logo Provinsi
```

---

## 🚀 Cara Akses

### Login Page (Entry Point)
```
http://localhost/ypok_management/index.php
```

### Halaman Utama
```
http://localhost/ypok_management/pages/dashboard.php
http://localhost/ypok_management/pages/kohai.php
http://localhost/ypok_management/pages/msh.php
http://localhost/ypok_management/pages/legalitas.php
http://localhost/ypok_management/pages/pembayaran.php
http://localhost/ypok_management/pages/toko.php
http://localhost/ypok_management/pages/lokasi.php
http://localhost/ypok_management/pages/laporan_kegiatan.php
http://localhost/ypok_management/pages/laporan_keuangan.php
```

---

## 📝 Aturan Path

### 1. File di Root (`/`)
```php
// ✅ Benar
require_once 'config/database.php';
header('Location: pages/dashboard.php');
```

### 2. File di Pages (`/pages/`)
```php
// ✅ Benar
require_once '../config/database.php';
include '../components/navbar.php';
header('Location: dashboard.php');           // Halaman lain di pages
header('Location: ../index.php');            // Kembali ke root
```

```html
<!-- ✅ HTML/JavaScript -->
<link href="../assets/css/style.css">
<script src="../assets/js/app.js"></script>
<form action="../actions/login.php">
<a href="../export/pendaftaran_pdf.php">
```

### 3. File di API (`/api/`)
```php
// ✅ Benar
require_once '../config/database.php';
```

### 4. File di Actions (`/actions/`)
```php
// ✅ Benar
require_once '../config/database.php';
header('Location: ../pages/dashboard.php');
header('Location: ../pages/toko.php');
header('Location: ../index.php');
```

### 5. File di Export (`/export/`)
```php
// ✅ Benar
require_once '../config/database.php';
```

---

## 🎯 Perubahan dari Struktur Lama

### ❌ Path Lama (TIDAK VALID LAGI)
```php
// Jangan gunakan lagi!
require_once 'config/database.php';           // Dari pages/
header('Location: dashboard.php');             // Dari actions/
href="assets/css/style.css"                    // Dari pages/
action="actions/login.php"                     // Dari pages/
```

### ✅ Path Baru (GUNAKAN INI)
```php
// Gunakan yang ini!
require_once '../config/database.php';         // Dari pages/
header('Location: ../pages/dashboard.php');    // Dari actions/
href="../assets/css/style.css"                 // Dari pages/
action="../actions/login.php"                  // Dari pages/
```

---

## 📦 Instalasi Database

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik tab **Import**
3. Pilih file: `database/ypok_database.sql`
4. Klik **Go**

**Login Default:**
- Username: `admin`
- Password: `admin123`

---

## 🔒 Keamanan

- File `.htaccess` sudah dikonfigurasi untuk keamanan
- Pastikan folder `uploads/` memiliki permission yang tepat
- Ganti password admin setelah instalasi
- Jangan commit file `config/database.php` ke repository

---

## 🆘 Troubleshooting

### Error: "config/database.php not found"
**Solusi:** Pastikan Anda menggunakan path yang benar sesuai lokasi file
- Dari pages: `../config/database.php`
- Dari root: `config/database.php`

### Error: "cannot redirect to dashboard"
**Solusi:** Pastikan redirect menggunakan `../pages/dashboard.php` dari actions

### Error: CSS/JS tidak load
**Solusi:** Pastikan path assets menggunakan `../assets/` dari pages

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.

**Versi:** 1.0  
**Last Updated:** 2026-03-04
