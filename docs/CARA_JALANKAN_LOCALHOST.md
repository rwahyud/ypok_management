# 🌐 CARA MENJALANKAN LOCALHOST DENGAN SUPABASE

## 📌 Konsep

```
┌─────────────────────────────────────────┐
│  KOMPUTER ANDA (Localhost)              │
│  ┌───────────────────────────────┐      │
│  │   XAMPP Apache                │      │
│  │   (http://localhost)          │      │
│  │                               │      │
│  │   File PHP Anda               │      │
│  │   - dashboard.php             │      │
│  │   - msh.php                   │      │
│  │   - kohai.php                 │      │
│  │   - dll...                    │      │
│  └───────────────│───────────────┘      │
└──────────────────│─────────────────────-┘
                   │
                   │ Internet
                   │ (koneksi ke Supabase)
                   ↓
       ┌───────────────────────────┐
       │   SUPABASE (Cloud)        │
       │   ┌─────────────────┐     │
       │   │  PostgreSQL     │     │
       │   │  Database       │     │
       │   │  - 22 tabel     │     │
       │   └─────────────────┘     │
       └───────────────────────────┘
```

## ✅ Langkah-Langkah

### 1. Pastikan Apache XAMPP Berjalan

**Buka XAMPP Control Panel:**
```
C:\xampp\xampp-control.exe
```

**Start Apache:**
- ✅ Klik **Start** di Apache
- ❌ **JANGAN** start MySQL (tidak dipakai lagi!)

**Indikator Berhasil:**
- Apache berwarna **hijau**
- Port 80 aktif

---

### 2. Pastikan Koneksi Internet Aktif

Karena database di cloud, Anda **HARUS** terkoneksi internet:
- ✅ WiFi/LAN harus aktif
- ✅ Bisa akses internet

**Tanpa internet = Error koneksi database!**

---

### 3. Buka Aplikasi di Browser

**URL Localhost:**
```
http://localhost/ypok_management/
```

atau jika ada folder tambahan:
```
http://localhost/ypok_management/ypok_management/
```

---

### 4. Test Koneksi Database

**Sebelum login, test dulu koneksi:**
```
http://localhost/ypok_management/test_supabase_connection.php
```

atau:
```
http://localhost/ypok_management/ypok_management/test_supabase_connection.php
```

**Harus melihat:**
- ✅ Status: **Koneksi Berhasil**
- ✅ Database: postgres
- ✅ Total Tables: **22 tabel**
- ✅ Daftar semua tabel

**Jika Error:**
- ❌ Cek internet Anda
- ❌ Cek password di `config/supabase.php`
- ❌ Pastikan schema sudah di-import

---

### 5. Login ke Aplikasi

**URL Login:**
```
http://localhost/ypok_management/
```

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

**Setelah login berhasil:**
- Dashboard akan muncul
- Anda bisa CRUD data (MSH, Kohai, dll)
- Data tersimpan di Supabase cloud

---

## 🔧 Troubleshooting

### Problem 1: "Connection failed"

**Penyebab:**
- Internet mati
- Password salah
- Schema belum di-import

**Solusi:**
```bash
1. Cek koneksi internet
2. Edit config/supabase.php - cek password
3. Import schema di Supabase Dashboard
```

---

### Problem 2: "Apache tidak bisa start"

**Penyebab:**
- Port 80 dipakai aplikasi lain (Skype, IIS, dll)

**Solusi:**
```
1. Buka XAMPP Config (Apache)
2. Ganti port 80 ke 8080
3. Akses: http://localhost:8080/ypok_management/
```

---

### Problem 3: "Table doesn't exist"

**Penyebab:**
- Schema belum di-import ke Supabase

**Solusi:**
1. Buka Supabase Dashboard → SQL Editor
2. Import file: `database/supabase_schema_complete.sql`
3. Run query

---

## 📝 Perbedaan Localhost Sebelum vs Sesudah

| Aspek | Sebelum (MySQL) | Sesudah (Supabase) |
|-------|-----------------|-------------------|
| **XAMPP Service** | Apache + MySQL | **Apache saja** |
| **Database** | Lokal (phpmyadmin) | **Cloud (Supabase)** |
| **Internet** | Tidak wajib | **Wajib!** |
| **Lokasi Data** | Komputer Anda | **Cloud Singapore** |
| **Backup** | Manual | **Auto (Supabase)** |
| **Akses Data** | Lokal saja | **Dari mana saja** |

---

## 💡 Keuntungan Pakai Supabase di Localhost

### 1. **Development Realistis**
- Data sama dengan production
- Testing lebih akurat
- Kolaborasi tim lebih mudah

### 2. **Data Aman**
- Auto backup setiap hari
- Tidak hilang jika komputer rusak
- Bisa restore kapan saja

### 3. **Akses Fleksibel**
- Bisa akses data dari HP
- Bisa akses dari komputer lain
- Bisa share dengan tim

### 4. **Performa Lebih Baik**
- Server database dedicated
- Auto scaling
- Monitoring real-time

---

## 🌐 Akses dari Perangkat Lain

### Dari Komputer Lain (Same Network)

**Cari IP komputer Anda:**
```cmd
ipconfig
```

**Akses dari komputer lain:**
```
http://192.168.x.x/ypok_management/
```
*(ganti dengan IP Anda)*

**Data tetap sama** karena di Supabase!

---

### Dari HP/Tablet (Same WiFi)

**Pastikan 1 WiFi dengan komputer:**
```
http://192.168.x.x/ypok_management/
```

**Data real-time sync!**

---

## ⚙️ Konfigurasi Optimal

### File: config/supabase.php

**Sudah di-set:**
```php
$supabase_host = 'db.vpqjbpkizdnvzpattiop.supabase.co';
$supabase_port = '5432';
$supabase_dbname = 'postgres';
$supabase_username = 'postgres';
$supabase_password = 'Ciooren123@';
```

✅ Sudah benar, tidak perlu diubah!

---

## 📊 Monitoring Database

### Dashboard Supabase

**URL:**
```
https://supabase.com/dashboard/project/vpqjbpkizdnvzpattiop
```

**Bisa monitoring:**
- 📈 Database usage
- 📊 Query performance
- 👥 Active connections
- 📝 Logs real-time
- 💾 Storage usage

---

## 🔄 Workflow Development

```
1. Buka XAMPP → Start Apache
   ↓
2. Cek internet aktif
   ↓
3. Test koneksi: test_supabase_connection.php
   ↓
4. Jika ✅ hijau → Lanjut develop
   ↓
5. Buka aplikasi: http://localhost/ypok_management/
   ↓
6. Login & mulai kerja
   ↓
7. Semua perubahan data → Langsung ke cloud!
```

---

## ❓ FAQ

### Q: Apakah bisa offline?
**A:** Tidak. Harus ada internet untuk akses database Supabase.

### Q: Berapa kecepatan yang dibutuhkan?
**A:** Minimal 1 Mbps. Makin cepat makin baik.

### Q: Apakah gratis?
**A:** Ya! Free tier Supabase:
- 500 MB database
- 1 GB file storage
- 2 GB bandwidth/bulan
- Unlimited API requests

### Q: Bagaimana jika internet lambat?
**A:** Query tetap jalan, tapi lebih lambat. Caching bisa membantu.

### Q: Bisa pakai di produksi?
**A:** Ya! Tinggal deploy file PHP ke hosting, database sudah siap.

---

## 🚀 Deploy ke Production (Bonus)

Saat siap production:

1. **Upload file PHP** ke hosting (Heroku, Railway, dll)
2. **Database sudah ready** (Supabase)
3. **Ganti URL** dari localhost ke domain
4. **Done!** 🎉

Tidak perlu setup database lagi!

---

## 📞 Support

**Jika ada masalah:**

1. Test koneksi: `test_supabase_connection.php`
2. Lihat error di browser console (F12)
3. Check Supabase Logs di dashboard
4. Baca dokumentasi: `MIGRASI_SUPABASE.md`

---

## ✅ Checklist Sebelum Mulai

- [ ] XAMPP Apache running (hijau)
- [ ] Internet aktif & stabil
- [ ] Test connection berhasil (22 tabel)
- [ ] Login berhasil (admin/admin123)
- [ ] Dashboard muncul
- [ ] Bisa CRUD data

**Jika semua ✅ = READY TO USE!** 🎉

---

**Last Updated:** 27 Februari 2026  
**Version:** 1.0  
**Database:** Supabase PostgreSQL (Cloud)  
**Aplikasi:** Localhost XAMPP
