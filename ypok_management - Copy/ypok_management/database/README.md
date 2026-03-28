# 📁 Database YPOK Management System

## 📌 File Database Utama

### ⭐ ypok_database.sql
**FILE UTAMA - GUNAKAN FILE INI UNTUK INSTALASI**

File database lengkap yang berisi semua tabel untuk sistem YPOK Management:
- ✅ 25 tabel lengkap (users, MSH, kohai, produk, transaksi, dll)
- ✅ Foreign key constraints dengan CASCADE
- ✅ Stored procedures untuk statistik provinsi
- ✅ Triggers otomatis
- ✅ Data default (admin user, kategori produk, sample data)

**Cara Instalasi:**
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik tab **Import**
3. Pilih file `ypok_database.sql`
4. Klik **Go**

**Login Default:**
- Username: `admin`
- Password: `admin123`

---

## 🔧 File Utility & Migration

### update_provinsi_agregat.sql
File SQL untuk menambahkan fitur agregat statistik provinsi.
- Digunakan oleh `run_update_provinsi_agregat.php`
- Berisi stored procedure `update_provinsi_stats()`
- Berisi triggers untuk auto-update statistik

**Cara Menggunakan:**
1. Akses via browser: `http://localhost/ypok_management/ypok_management/database/run_update_provinsi_agregat.php`
2. Atau import manual melalui phpMyAdmin

### update_pembayaran_struktur.sql
File migration untuk update struktur tabel pembayaran.
- Digunakan jika perlu update struktur tabel pembayaran
- Import manual via phpMyAdmin jika diperlukan

### run_update_provinsi_agregat.php
Script PHP untuk menjalankan `update_provinsi_agregat.sql` secara otomatis.
- Akses: `http://localhost/ypok_management/ypok_management/database/run_update_provinsi_agregat.php`

---

## 📚 File Dokumentasi

### README_UPDATE_PROVINSI.md
Dokumentasi lengkap tentang fitur agregat provinsi.
- Penjelasan stored procedure
- Cara troubleshooting
- Contoh penggunaan

### README.md (file ini)
Panduan penggunaan file-file database.

---

## 👨‍💻 File Development

### create_default_user.php
Script untuk membuat user admin default jika diperlukan.

### fix_login.php
Script utility untuk fix masalah login.

---

## 🗑️ File yang Sudah Dihapus

File-file duplikat berikut telah dihapus:
- ❌ ypok_final_complete.sql
- ❌ ypok_complete_final.sql
- ❌ ypok_database_complete.sql
- ❌ ypok_database_final.sql
- ❌ database.sql
- ❌ database_update.sql
- ❌ schema.sql
- ❌ toko_tables.sql
- ❌ legalitas_schema.sql
- ❌ create_variasi_table.sql

Semua tabel dari file-file di atas sudah digabungkan ke dalam **ypok_database.sql**

---

## 📋 Daftar Tabel dalam Database

1. **users** - Autentikasi dan manajemen user
2. **provinsi** - Data provinsi/wilayah
3. **dojo** - Data dojo/cabang per provinsi
4. **lokasi** - Lokasi kegiatan
5. **informasi_yayasan** - Identitas yayasan
6. **pengurus** - Data pengurus yayasan
7. **legalitas** - Dokumen legal yayasan
8. **master_sabuk_hitam** - Data MSH (Dan 1-9)
9. **prestasi_msh** - Prestasi MSH
10. **sertifikasi_msh** - Sertifikasi MSH
11. **kohai** - Data siswa/kohai (Kyu 1-10)
12. **prestasi_kohai** - Prestasi kohai
13. **sertifikasi_kohai** - Sertifikasi kohai
14. **pendaftaran_msh** - Pendaftaran MSH baru
15. **pendaftaran_kohai** - Pendaftaran kohai baru
16. **pembayaran** - Transaksi pembayaran
17. **transaksi** - Laporan keuangan yayasan
18. **kegiatan** - Data kegiatan/event
19. **kategori_produk** - Kategori produk toko
20. **produk_toko** - Produk toko
21. **produk_variasi** - Variasi produk (ukuran, warna)
22. **transaksi_toko** - Transaksi penjualan

---

## ⚠️ Catatan Penting

1. **Backup Database Secara Berkala**
   - Gunakan fitur Export di phpMyAdmin
   - Simpan backup di tempat yang aman

2. **Ganti Password Admin**
   - Setelah instalasi, segera ganti password default
   - Password default: `admin123`

3. **Jangan Hapus User Admin (id=1)**
   - User admin diperlukan untuk akses sistem

4. **CASCADE DELETE Sudah Aktif**
   - Hapus MSH = otomatis hapus prestasi & sertifikasi MSH
   - Hapus Kohai = otomatis hapus prestasi & sertifikasi Kohai
   - Hapus Produk = otomatis hapus variasi & transaksi terkait

5. **File Upload Locations**
   - MSH: `uploads/msh/`
   - Kohai: `uploads/kohai/`
   - Pengurus: `uploads/pengurus/`
   - Dokumen: `uploads/dokumen/`
   - Produk: `uploads/produk/`
   - Provinsi: `uploads/provinsi/`

---

## 🆘 Troubleshooting

### Error: Cannot delete or update a parent row
**Solusi:** Database sudah menggunakan CASCADE DELETE, pastikan import `ypok_database.sql` yang baru.

### Error: Column not found
**Solusi:** Pastikan semua tabel sudah dibuat dengan benar. Jalankan ulang `ypok_database.sql`.

### Error: Duplicate entry
**Solusi:** Gunakan nomor/kode yang berbeda untuk setiap data atau gunakan clean install (DROP semua tabel lalu import ulang).

### Provinsi Stats Tidak Update
**Solusi:** Jalankan `run_update_provinsi_agregat.php` atau import manual `update_provinsi_agregat.sql`.

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.

**Versi Database:** 3.0 COMPLETE  
**Last Updated:** 2026-03-04
