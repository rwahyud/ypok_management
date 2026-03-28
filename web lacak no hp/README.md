# 🏠 Family Location Tracker

Aplikasi pelacak lokasi keluarga berbasis web untuk memantau lokasi anak dan anggota keluarga dengan persetujuan mereka.

## 📋 Fitur

✅ **Real-time Location Tracking** - Pantau lokasi anggota keluarga secara real-time  
✅ **Peta Interaktif** - Visualisasi lokasi dengan marker dan accuracy circle  
✅ **Privacy Control** - Setiap user bisa on/off sharing lokasi  
✅ **Family Groups** - Sistem kode keluarga untuk mengelompokkan anggota  
✅ **Akurasi Tinggi** - Menggunakan GPS untuk lokasi akurat  
✅ **Battery Status** - Tampilkan level baterai perangkat  
✅ **Responsive Design** - Berfungsi di desktop dan mobile  

## 🚀 Cara Instalasi

### 1. Persiapan

Pastikan Anda sudah menginstall:
- XAMPP (Apache + MySQL + PHP)
- Browser modern (Chrome, Firefox, Edge)

### 2. Setup Database

1. Buka phpMyAdmin di `http://localhost/phpmyadmin`
2. Buat database baru bernama `family_tracker`
3. Import file `database.sql`:
   - Klik database `family_tracker`
   - Pilih tab "Import"
   - Pilih file `database.sql`
   - Klik "Go"

### 3. Konfigurasi

Edit file `config.php` jika perlu mengubah kredensial database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'family_tracker');
```

### 4. Jalankan Aplikasi

1. Pastikan XAMPP Apache dan MySQL sudah running
2. Buka browser dan akses: `http://localhost/web%20lacak%20no%20hp/`
3. Aplikasi siap digunakan!

## 👥 Cara Penggunaan

### Untuk Orang Tua (Membuat Keluarga Baru):

1. Klik tab **"Daftar"**
2. Isi form registrasi:
   - Nama lengkap
   - Email
   - Password
   - No. telepon (opsional)
   - Pilih role: **"Orang Tua (Buat Keluarga Baru)"**
3. Klik **"Daftar"**
4. **SIMPAN KODE KELUARGA** yang muncul (contoh: A1B2C3D4)
5. Bagikan kode ini ke anggota keluarga lain

### Untuk Anak/Anggota Keluarga:

1. Klik tab **"Daftar"**
2. Isi form registrasi:
   - Nama lengkap
   - Email
   - Password
   - No. telepon (opsional)
   - Pilih role: **"Anak"** atau **"Anggota Keluarga"**
   - **Masukkan KODE KELUARGA** yang diberikan orang tua
3. Klik **"Daftar"**

### Berbagi Lokasi:

1. Login ke dashboard
2. Browser akan meminta izin akses lokasi - **klik "Allow/Izinkan"**
3. Toggle "Berbagi Lokasi" akan aktif otomatis
4. Lokasi Anda akan muncul di peta dengan marker biru
5. Lokasi anggota keluarga lain akan muncul dengan marker merah

### Melihat Lokasi Keluarga:

1. Di dashboard, lihat peta untuk melihat semua lokasi
2. Klik marker untuk info detail (nama, akurasi, waktu update)
3. Klik tombol 📍 di samping nama anggota untuk zoom ke lokasi mereka
4. Peta akan auto-update setiap 5 detik

## 🔒 Keamanan & Privacy

- ✅ Password di-hash dengan algoritma bcrypt
- ✅ Session management untuk autentikasi
- ✅ Setiap user bisa mematikan sharing kapan saja
- ✅ Lokasi hanya dibagikan ke anggota keluarga dengan kode yang sama
- ✅ Data lokasi ter-enkripsi di database

## 📱 Tips Penggunaan

1. **Untuk Akurasi Terbaik:**
   - Aktifkan GPS di perangkat
   - Gunakan di luar ruangan atau dekat jendela
   - Pastikan browser memiliki izin lokasi

2. **Menghemat Baterai:**
   - Matikan sharing saat tidak diperlukan
   - Update interval: 5 detik (bisa diubah di `script.js`)

3. **Troubleshooting:**
   - Jika lokasi tidak muncul, refresh browser
   - Pastikan izin lokasi sudah diberikan
   - Cek koneksi internet

## 📂 Struktur File

```
web lacak no hp/
├── index.php           # Halaman login/register
├── dashboard.php       # Dashboard utama dengan peta
├── config.php          # Konfigurasi database & session
├── share-location.php  # API untuk update lokasi
├── get-locations.php   # API untuk ambil lokasi keluarga
├── logout.php          # Logout handler
├── style.css           # Styling
├── script.js           # JavaScript untuk peta & geolocation
├── database.sql        # SQL schema
└── README.md           # Dokumentasi ini
```

## 🛠️ Teknologi

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Maps:** Leaflet.js (OpenStreetMap)
- **Geolocation API:** HTML5 Navigator

## ⚠️ Catatan Legal

Aplikasi ini dibuat untuk tujuan keamanan keluarga yang sah dengan persetujuan semua pihak. 

**Penting:**
- Selalu dapatkan izin eksplisit dari semua anggota keluarga
- Gunakan hanya untuk tujuan keamanan keluarga
- Jangan gunakan untuk stalking atau harassment
- Patuhi undang-undang privasi dan perlindungan data di negara Anda

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi admin keluarga Anda.

---

**Dibuat dengan ❤️ untuk keamanan keluarga**
