# 🎯 QUICK START GUIDE - SUPABASE MIGRATION

## ⚡ Langkah Cepat (5 Menit)

### 1. Dapatkan Password Supabase
```
1. Buka: https://supabase.com/dashboard/project/vpqjbpkizdnvzpattiop
2. Settings → Database
3. Copy password dari Connection String
```

### 2. Update Konfigurasi
Edit: `config/supabase.php`
```php
$supabase_password = 'PASTE_PASSWORD_DISINI';
```

### 3. Import Schema Database
```
1. Buka Supabase Dashboard → SQL Editor
2. New Query
3. Copy isi file: database/supabase_schema_complete.sql
4. Run Query
```

### 4. Test Koneksi
Buka browser:
```
http://localhost/ypok_management/test_supabase_connection.php
```

Harus muncul: ✅ **Koneksi Berhasil** + 22 tabel

### 5. Migrasi Data (Jika Ada Data Lama)
```
http://localhost/ypok_management/migrate_data.php
```

### 6. Update File PHP
Ganti di semua file:
```php
// Dari:
require_once 'config/database.php';

// Menjadi:
require_once 'config/supabase.php';
```

### 7. Test Aplikasi
```
http://localhost/ypok_management/
Login: admin / admin123
```

## ✅ Selesai!

Database Anda sekarang:
- ☁️ Di cloud (Supabase)
- 🔒 Auto backup
- 🚀 Scalable
- 🌍 Bisa diakses dari mana saja

## 📚 Dokumentasi Lengkap

Baca: **MIGRASI_SUPABASE.md**

## 🆘 Butuh Bantuan?

Error? Lihat bagian Troubleshooting di MIGRASI_SUPABASE.md
