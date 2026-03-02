# 🔗 URL Structure Guide - YPOK Management (XAMPP + Supabase)

**Terakhir diupdate:** 2 Maret 2026

## 📍 Base URL

### Localhost (XAMPP)
```
http://localhost/ypok_management/ypok_management/
```

### Production (Vercel)
```
https://ypok-management.vercel.app/
```

---

## 🏠 URL Mapping - Struktur Baru

### **Dashboard & Auth**
| Page | URL |
|------|-----|
| Login | `http://localhost/ypok_management/ypok_management/` |
| Dashboard | `http://localhost/ypok_management/ypok_management/dashboard.php` |
| Guest Dashboard | `http://localhost/ypok_management/ypok_management/guest_dashboard.php` |
| Logout | `http://localhost/ypok_management/ypok_management/actions/logout.php` |

### **Master Data**
| Module | List Page | Add | Edit | Detail |
|--------|-----------|-----|------|--------|
| **MSH** | `/pages/msh/` | `/pages/msh/add.php` | `/pages/msh/edit.php?id=X` | `/pages/msh/detail.php?id=X` |
| **Kohai** | `/pages/kohai/` | (inline modal) | (inline modal) | `/pages/kohai/detail.php?id=X` |
| **Lokasi** | `/pages/lokasi/` | (inline form) | - | - |
| **Pembayaran** | `/pages/pembayaran/` | (inline form) | - | `/pages/pembayaran/invoice.php?id=X` |
| **Pendaftaran** | `/pages/pendaftaran/` | (inline form) | `/pages/pendaftaran/edit.php?id=X` | - |
| **Legalitas** | `/pages/legalitas/` | (inline form) | `/pages/legalitas/edit.php?id=X` | - |
| **Toko** | `/pages/toko/` | (inline form) | - | - |

### **Laporan**
| Laporan | URL |
|---------|-----|
| Kelola Berita | `/pages/laporan/kegiatan.php` |
| Laporan Keuangan | `/pages/laporan/keuangan.php` |
| Export Keuangan (Excel) | `/pages/laporan/export_keuangan.php` |
| Export PDF | `/pages/laporan/export_pdf.php` |

### **Import Data**
| Import | URL |
|--------|-----|
| Import MSH CSV | `/pages/msh/import.php` |
| Import Kohai CSV | `/pages/kohai/import.php` |
| Import Kohai Batch | `/pages/kohai/import_batch.php` |

### **Actions (API Endpoints)**
| Action | URL |
|--------|-----|
| Get MSH Data | `/api/msh.php?id=X` |
| Get Kohai Data | `/api/kohai.php?id=X` |
| Get Kegiatan Detail | `/api/kegiatan.php?id=X` |
| Add Kategori (AJAX) | `/api/kategori.php` |
| Proses Transaksi | `/api/transaksi.php` |
| Toggle Berita | `/api/berita.php?id=X` |

---

## ⚠️ Path yang TIDAK VALID Lagi

Setelah reorganisasi, path lama sudah tidak berlaku:

❌ **Path Lama (Tidak Valid):**
```
/msh.php
/kohai.php
/lokasi.php
/pembayaran.php
/legalitas.php
/pendaftaran.php
/toko.php
/laporan_kegiatan.php
/laporan_keuangan.php
```

✅ **Path Baru (Valid):**
```
/pages/msh/
/pages/kohai/
/pages/lokasi/
/pages/pembayaran/
/pages/legalitas/
/pages/pendaftaran/
/pages/toko/
/pages/laporan/kegiatan.php
/pages/laporan/keuangan.php
```

---

## 🔧 Cara Mengakses di Browser

### **1. Login Pertama Kali**
```
http://localhost/ypok_management/ypok_management/
```

### **2. Setelah Login → Dashboard**
Otomatis redirect ke:
```
http://localhost/ypok_management/ypok_management/dashboard.php
```

### **3. Navigasi Pakai Sidebar**
Klik menu di sidebar (sudah menggunakan absolute path yang benar):
- 📊 Dashboard → `/ypok_management/ypok_management/dashboard.php`
- 🥋 Data MSH → `/ypok_management/ypok_management/pages/msh/`
- 👥 Data Kohai → `/ypok_management/ypok_management/pages/kohai/`
- Dan seterusnya...

### **4. Check Data Kohai**
Khusus untuk debugging:
```
http://localhost/ypok_management/ypok_management/check_kohai_data.php
```

---

## 🗺️ File Structure Reference

```
ypok_management/
├── index.php                    (Login page)
├── dashboard.php                (Main dashboard)
├── guest_dashboard.php          (Public view)
├── check_kohai_data.php         (Debug tool)
│
├── pages/
│   ├── msh/
│   │   ├── index.php           (List MSH)
│   │   ├── add.php             (Add MSH)
│   │   ├── edit.php            (Edit MSH)
│   │   ├── detail.php          (Detail MSH)
│   │   └── import.php          (Import CSV)
│   │
│   ├── kohai/
│   │   ├── index.php           (List Kohai)
│   │   ├── detail.php          (Detail Kohai)
│   │   ├── import.php          (Import CSV)
│   │   └── import_batch.php    (Batch Import)
│   │
│   ├── lokasi/
│   ├── pembayaran/
│   ├── pendaftaran/
│   ├── legalitas/
│   ├── toko/
│   ├── laporan/
│   └── ...
│
├── api/
│   ├── msh.php
│   ├── kohai.php
│   ├── kegiatan.php
│   └── ...
│
├── actions/
│   ├── logout.php
│   └── ...
│
├── config/
│   └── supabase.php            (Database connection)
│
└── assets/
    ├── css/
    ├── js/
    └── images/
```

---

## 🎯 Tips Navigasi

1. **Gunakan Sidebar Menu**
   - Semua menu di sidebar sudah menggunakan absolute path
   - Klik langsung tanpa khawatir path ganda

2. **Jangan Bookmark Path Lama**
   - Update bookmark browser Anda dengan path baru
   - Gunakan struktur `/pages/module/`

3. **Refresh Cache Browser**
   - Jika ada masalah tampilan, tekan `Ctrl + F5`
   - Clear browser cache jika perlu

4. **Import Data**
   - Pastikan file CSV ada di `googlesheet/`
   - Path sudah otomatis fix ke `../../googlesheet/`

---

## 🚀 Deployment ke Vercel

Ketika di-deploy ke Vercel, path akan otomatis tanpa `/ypok_management/ypok_management/`:

```
Production:
https://ypok-management.vercel.app/
https://ypok-management.vercel.app/dashboard.php
https://ypok-management.vercel.app/pages/msh/
https://ypok-management.vercel.app/pages/kohai/
```

File `vercel.json` sudah dikonfigurasi untuk handle routing dengan benar.

---

## 📞 Troubleshooting

### Masalah: "404 Not Found"
**Solusi:** Pastikan menggunakan path baru dengan `/pages/module/`

### Masalah: "Tampilan rusak (CSS tidak load)"
**Solusi:** 
1. Refresh browser dengan `Ctrl + F5`
2. Check path CSS sudah `../../assets/css/style.css`

### Masalah: "Data kohai hilang"
**Solusi:**
1. Buka `check_kohai_data.php` untuk cek database
2. Import ulang jika perlu (path CSV sudah benar)

### Masalah: "Menu tidak berfungsi"
**Solusi:**
1. Clear browser cache
2. Check console browser (F12) untuk error
3. Pastikan menggunakan versi terbaru dari GitHub

---

**Happy Coding! 🥋**

*YPOK Management System - Powered by Supabase*
