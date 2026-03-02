# 📂 Dokumentasi Struktur Project (Modular)

Tanggal Reorganisasi: 2 Maret 2026

## 🎯 Tujuan Reorganisasi

Project YPOK Management telah direstrukturisasi menjadi arsitektur yang lebih modular dan profesional dengan memisahkan halaman-halaman ke dalam folder berdasarkan modul fungsionalitas.

## 📁 Struktur Baru

### **Pages (Halaman Aplikasi)**

Semua halaman aplikasi sekarang berada di folder `pages/` dengan struktur modular:

```
pages/
├── msh/              # Manajemen MSH (Member/Sensei/Head)
│   ├── index.php    # List MSH
│   ├── add.php      # Tambah MSH
│   ├── edit.php     # Edit MSH
│   ├── detail.php   # Detail MSH
│   └── import.php   # Import CSV
│
├── kohai/           # Manajemen Kohai (Anggota)
│   ├── index.php
│   ├── detail.php
│   ├── import.php
│   └── import_batch.php
│
├── kegiatan/        # Manajemen Kegiatan
│   ├── add.php
│   ├── edit.php
│   ├── detail.php
│   ├── delete.php
│   ├── save.php
│   └── update.php
│
├── lokasi/          # Manajemen Lokasi & Provinsi
│   └── index.php
│
├── pembayaran/      # Pembayaran & Invoice
│   ├── index.php
│   └── invoice.php
│
├── pendaftaran/     # Pendaftaran Anggota
│   ├── index.php
│   └── edit.php
│
├── legalitas/       # Dokumen Legalitas
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   ├── update.php
│   └── delete.php
│
├── toko/            # Toko Merchandise
│   └── index.php
│
├── laporan/         # Laporan & Export
│   ├── kegiatan.php
│   ├── keuangan.php
│   ├── export_keuangan.php
│   └── export_pdf.php
│
├── pengurus/        # Manajemen Pengurus
│   ├── add.php
│   ├── edit.php
│   ├── update.php
│   └── delete.php
│
└── surat/           # Template Surat
    ├── form.php
    └── generate.php
```

### **API (Endpoints)**

API endpoints sekarang terpisah di folder `api/`:

```
api/
├── msh.php          # API Get MSH data
├── kohai.php        # API Get Kohai data
├── kegiatan.php     # API Get Kegiatan details
├── kategori.php     # API Kategori (AJAX)
├── transaksi.php    # API Proses Transaksi
└── berita.php       # API Toggle Berita
```

## 🔗 URL Mapping

### **Sebelum Reorganisasi:**
```
/msh.php
/msh_add.php
/msh_edit.php
/kohai.php
/kegiatan_add.php
```

### **Setelah Reorganisasi:**
```
/pages/msh/                 (index.php)
/pages/msh/add.php
/pages/msh/edit.php
/pages/kohai/               (index.php)
/pages/kegiatan/add.php
```

## 🔧 Perubahan File

### **Navbar Component**

File: `components/navbar.php`

Semua link diupdate menggunakan path baru:
```php
// Sebelum
<a href="msh.php">Data MSH</a>

// Sesudah
<a href="/pages/msh/">Data MSH</a>
```

### **Vercel Configuration**

File: `vercel.json`

Routing rules ditambahkan untuk mendukung struktur folder:
```json
{
  "routes": [
    {
      "src": "/pages/(msh|kohai|kegiatan|...)/",
      "dest": "/pages/$1/index.php"
    }
  ]
}
```

## ✅ Keuntungan Struktur Baru

1. **Modular & Scalable**
   - Setiap modul terpisah dalam foldernya sendiri
   - Mudah menambah fitur baru tanpa mencemari root directory

2. **Maintainable**
   - File terorganisir berdasarkan fungsionalitas
   - Developer baru lebih mudah memahami struktur

3. **Clean URLs**
   - `/pages/msh/` lebih bersih dari `/msh.php`
   - SEO-friendly dengan struktur hierarki

4. **Separation of Concerns**
   - Halaman UI di `/pages/`
   - API endpoints di `/api/`
   - Actions handlers di `/actions/`

5. **Professional**
   - Mengikuti best practice modern PHP development
   - Mirip dengan framework populer (Laravel, CodeIgniter)

## 📊 Statistik Reorganisasi

- **Folder baru dibuat:** 12
- **File dipindahkan:** 45 files
- **File diupdate:** 3 files (navbar.php, vercel.json, README.md)
- **Commit:** 1 major reorganization commit
- **Pushed to GitHub:** ✅

## 🚀 Testing

Setelah reorganisasi, test semua halaman:

1. **Dashboard** - `http://localhost/dashboard.php`
2. **Data MSH** - `http://localhost/pages/msh/`
3. **Data Kohai** - `http://localhost/pages/kohai/`
4. **Kegiatan** - `http://localhost/pages/kegiatan/add.php`
5. **Lokasi** - `http://localhost/pages/lokasi/`
6. **Pembayaran** - `http://localhost/pages/pembayaran/`
7. **Dan seterusnya...**

## 📝 Catatan Penting

- Root files tetap ada: `index.php`, `dashboard.php`, `guest_dashboard.php`
- Folder lain tidak berubah: `actions/`, `auth/`, `components/`, `config/`, `assets/`
- Environment files tetap di root: `.env`, `.env.example`, `.gitignore`
- PWA files tetap di root: `manifest.json`, `sw.js`

## 🔄 Update Future

Jika ada perubahan di file pages, ikuti workflow ini:

```bash
# Edit file
nano pages/msh/index.php

# Commit
git add .
git commit -m "Update halaman MSH"
git push origin main
```

---

**Project YPOK Management sekarang lebih rapih, modular, dan siap untuk scale! 🎉**
