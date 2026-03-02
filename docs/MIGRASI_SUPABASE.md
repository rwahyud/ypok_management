# 🚀 MIGRASI KE SUPABASE - DOKUMENTASI LENGKAP

## 📋 Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Persiapan](#persiapan)
3. [Setup Supabase](#setup-supabase)
4. [Import Schema](#import-schema)
5. [Konfigurasi PHP](#konfigurasi-php)
6. [Migrasi Data](#migrasi-data)
7. [Testing](#testing)
8. [Troubleshooting](#troubleshooting)

---

## 📖 Pendahuluan

Dokumentasi ini berisi panduan lengkap untuk migrasi database YPOK Management dari MySQL (localhost) ke **Supabase PostgreSQL**.

### Keuntungan Menggunakan Supabase:
- ✅ Database PostgreSQL yang powerful
- ✅ Hosting gratis dengan fitur lengkap
- ✅ Auto-backup dan disaster recovery
- ✅ Real-time subscriptions
- ✅ Built-in authentication
- ✅ REST API otomatis
- ✅ Dashboard yang user-friendly
- ✅ Scalable dan reliable

---

## 🛠️ Persiapan

### 1. Informasi Project Supabase Anda

```
Project Name: ypok_management
Project URL: https://vpqjbpkizdnvzpattiop.supabase.co
Region: Southeast Asia (Singapore)
Database Host: db.vpqjbpkizdnvzpattiop.supabase.co
Database Port: 5432
Database Name: postgres
Database User: postgres
```

### 2. File-file yang Sudah Dibuat

```
ypok_management/
├── config/
│   ├── database.php          (MySQL - lama)
│   └── supabase.php          (PostgreSQL - baru) ✅
├── database/
│   └── supabase_schema_complete.sql  ✅
└── test_supabase_connection.php      ✅
```

---

## 🔧 Setup Supabase

### Step 1: Dapatkan Database Password

1. Buka [Supabase Dashboard](https://supabase.com/dashboard)
2. Pilih project **ypok_management**
3. Klik **Settings** → **Database**
4. Scroll ke bagian **Connection String**
5. Klik **Show** untuk melihat password
6. Copy password tersebut

### Step 2: Update File Konfigurasi

Edit file: `config/supabase.php`

```php
$supabase_password = 'PASTE_PASSWORD_ANDA_DISINI';
```

**PENTING:** Jangan share password ini ke siapapun!

---

## 📊 Import Schema

### Metode 1: Via Supabase Dashboard (Recommended)

1. Buka Supabase Dashboard
2. Klik **SQL Editor** di sidebar kiri
3. Klik **New Query**
4. Buka file: `database/supabase_schema_complete.sql`
5. Copy seluruh isi file
6. Paste ke SQL Editor
7. Klik **Run** atau tekan `Ctrl + Enter`
8. Tunggu hingga selesai (sekitar 10-30 detik)

### Metode 2: Via pgAdmin (Advanced)

1. Install [pgAdmin](https://www.pgadmin.org/)
2. Connect ke Supabase dengan credentials
3. Right-click database → **Query Tool**
4. Buka file `supabase_schema_complete.sql`
5. Execute

### Verifikasi Import

Setelah import, cek di **Table Editor**. Anda harus melihat **22 tabel**:

Core Tables:
- ✅ users
- ✅ master_sabuk_hitam
- ✅ kohai
- ✅ prestasi_msh
- ✅ sertifikasi_msh
- ✅ prestasi_kohai
- ✅ sertifikasi_kohai

Registration Tables:
- ✅ pendaftaran_msh
- ✅ pendaftaran_kohai

Organization Tables:
- ✅ informasi_yayasan
- ✅ pengurus
- ✅ legalitas
- ✅ dojo
- ✅ provinsi

Activity Tables:
- ✅ kegiatan
- ✅ lokasi
- ✅ pembayaran

Store Tables:
- ✅ kategori_produk
- ✅ produk_toko
- ✅ produk_variasi
- ✅ transaksi_toko

Financial Tables:
- ✅ transaksi

---

## ⚙️ Konfigurasi PHP

### Update Semua File PHP

Ganti semua baris ini:
```php
require_once 'config/database.php';
```

Menjadi:
```php
require_once 'config/supabase.php';
```

### Cara Cepat (Find & Replace)

Di VS Code:
1. Tekan `Ctrl + Shift + H`
2. Find: `require_once 'config/database.php';`
3. Replace: `require_once 'config/supabase.php';`
4. Klik **Replace All**

### File-file yang Perlu Diupdate

```
✅ dashboard.php
✅ msh.php
✅ msh_add.php
✅ msh_edit.php
✅ msh_detail.php
✅ kohai.php
✅ kohai_detail.php
✅ pendaftaran.php
✅ pembayaran.php
✅ laporan_keuangan.php
✅ laporan_kegiatan.php
✅ legalitas.php
✅ toko.php
✅ lokasi.php
... dan semua file lainnya yang menggunakan database
```

### Perbedaan Sintaks MySQL vs PostgreSQL

Jika ada query yang error, sesuaikan:

| MySQL | PostgreSQL |
|-------|------------|
| `LIMIT 10, 20` | `LIMIT 20 OFFSET 10` |
| `NOW()` | `CURRENT_TIMESTAMP` |
| `CONCAT()` | `\|\|` atau `CONCAT()` |
| Backtick \` | Double quotes " (untuk reserved keywords) |

---

## 🔄 Migrasi Data

### Jika Anda Memiliki Data di MySQL

#### Option 1: Export/Import Manual

1. **Export dari MySQL**
```bash
# Dari phpMyAdmin atau MySQL Workbench
# Export setiap tabel sebagai CSV
```

2. **Import ke Supabase**
```sql
-- Via SQL Editor di Supabase
COPY users(username, password, nama_lengkap, email, role)
FROM '/path/to/users.csv'
DELIMITER ','
CSV HEADER;
```

#### Option 2: Script PHP Migration (Akan dibuat)

Jalankan script migrasi yang akan:
- Connect ke MySQL dan Supabase
- Transfer data tabel per tabel
- Validasi data

---

## 🧪 Testing

### 1. Test Koneksi Database

Buka di browser:
```
http://localhost/ypok_management/test_supabase_connection.php
```

Anda harus melihat:
- ✅ Status: Koneksi Berhasil
- ✅ Database Info
- ✅ Daftar 22 tabel

### 2. Test Login

1. Buka aplikasi
2. Login dengan:
   - Username: `admin`
   - Password: `admin123`
3. Pastikan dashboard muncul

### 3. Test CRUD Operations

Test setiap modul:
- ✅ Tambah data MSH
- ✅ Edit data MSH
- ✅ Hapus data MSH
- ✅ Tambah data Kohai
- ✅ Tambah transaksi
- ✅ Upload foto
- ✅ Generate laporan

---

## 🐛 Troubleshooting

### Error: "Connection failed"

**Solusi:**
1. Cek password di `config/supabase.php`
2. Pastikan koneksi internet stabil
3. Cek status Supabase di dashboard
4. Verifikasi host: `db.vpqjbpkizdnvzpattiop.supabase.co`

### Error: "Table doesn't exist"

**Solusi:**
1. Schema belum di-import
2. Import file `supabase_schema_complete.sql`
3. Refresh halaman test connection

### Error: "SQLSTATE[42703]: Undefined column"

**Solusi:**
- Nama kolom case-sensitive di PostgreSQL
- Gunakan lowercase untuk semua nama kolom
- Atau quote dengan double quotes: `"NamaKolom"`

### Error: "LIMIT syntax error"

**Solusi:**
Ganti query:
```php
// MySQL
$query = "SELECT * FROM users LIMIT 10, 20";

// PostgreSQL
$query = "SELECT * FROM users LIMIT 20 OFFSET 10";
```

### Error: "ENUM type not exists"

**Solusi:**
- PostgreSQL menggunakan CHECK constraints bukan ENUM
- Schema sudah disesuaikan
- Gunakan VARCHAR dengan validation

### Upload File Tidak Berfungsi

**Solusi:**
- Upload file tetap di server lokal (`uploads/` folder)
- Untuk produksi, gunakan Supabase Storage:
  1. Aktifkan di Dashboard → Storage
  2. Buat bucket untuk uploads
  3. Update code untuk upload ke Supabase Storage

---

## 📝 Post-Migration Checklist

- [ ] Password database sudah diupdate
- [ ] Schema sudah di-import (22 tabel)
- [ ] Koneksi berhasil (green status)
- [ ] Semua file PHP sudah diupdate
- [ ] Login berhasil dengan user admin
- [ ] CRUD MSH berfungsi
- [ ] CRUD Kohai berfungsi
- [ ] Upload foto berfungsi
- [ ] Laporan bisa di-generate
- [ ] Data sample sudah ada

---

## 🚀 Keuntungan Migrasi

| Fitur | MySQL (Localhost) | Supabase |
|-------|------------------|----------|
| Hosting | Manual | ✅ Auto |
| Backup | Manual | ✅ Auto |
| Scaling | Limited | ✅ Auto |
| Monitoring | Limited | ✅ Built-in |
| API | Manual | ✅ Auto REST API |
| Auth | Manual | ✅ Built-in Auth |
| Realtime | No | ✅ Yes |
| Cost | Server cost | ✅ Free tier |

---

## 📞 Support

Jika ada masalah:
1. Cek error di browser console (F12)
2. Cek error log di Supabase Dashboard → Logs
3. Review dokumentasi ini kembali
4. Check Supabase Documentation: https://supabase.com/docs

---

## ✅ Kesimpulan

Setelah migrasi selesai, aplikasi YPOK Management akan:
- Menggunakan database PostgreSQL di Supabase
- Lebih scalable dan reliable
- Auto-backup setiap hari
- Bisa diakses dari mana saja
- Lebih aman dan modern

**Selamat! Database Anda sudah siap di cloud! 🎉**

---

**Last Updated:** 27 Februari 2026
**Version:** 1.0
**Author:** GitHub Copilot
