# 🐛 TROUBLESHOOTING GUIDE - YPOK Management System

## 📋 Masalah yang Dilaporkan & Solusi

### 1️⃣ Berita Tidak Tampil di Guest Dashboard

#### ✅ SOLUSI LENGKAP:

**A. Pastikan Checkbox Berita Dicentang**
1. Buka menu **📰 Kelola Berita**
2. Klik **+ Tambah Kegiatan**
3. Isi semua data kegiatan
4. **PENTING**: Upload foto kegiatan (JPG/PNG, max 2MB)
5. **CENTANG checkbox** "📰 Tampilkan sebagai Berita di Halaman Utama"
6. Klik **Simpan**

**B. Jika Data Sudah Ada, Aktifkan dengan Toggle**
1. Buka menu **📰 Kelola Berita**
2. Cari kegiatan yang ingin ditampilkan sebagai berita
3. Klik **toggle switch** di kolom "📰 Berita" → akan berubah **hijau**
4. Lihat toast notification "✅ Berita berhasil diaktifkan"
5. Buka **Guest Dashboard** → berita akan tampil

**C. Debug Berita**
- Akses: `http://localhost/ypok_management/ypok_management/test_berita_debug.php`
- File ini akan menampilkan:
  - ✅ Semua kegiatan dan status tampil_di_berita
  - ✅ Foto yang sudah diupload
  - ✅ Berita yang seharusnya tampil di guest dashboard
  - ❌ Error jika ada masalah

**D. Penyebab Umum Berita Tidak Tampil:**
- ❌ Checkbox "Tampilkan sebagai Berita" tidak dicentang
- ❌ Foto tidak diupload
- ❌ Foto gagal upload (ukuran > 2MB atau format salah)
- ❌ Folder uploads/kegiatan/ tidak ada atau tidak writable

---

### 2️⃣ Data Hilang Saat Refresh Halaman

#### ✅ PENJELASAN:
**Data TIDAK hilang!** Ini adalah UX normal dari sistem POST-Redirect-GET:

**Yang Terjadi:**
1. User submit form (POST data)
2. Server save data ke database ✅
3. Server **redirect** ke halaman list dengan **success notification** ✅
4. Toast notification muncul di kanan atas ✅
5. Data sudah ada di tabel ✅

**Mengapa Seperti Ini?**
- Ini mencegah **duplicate data** saat user refresh browser
- Jika tidak ada redirect, refresh = submit data lagi = data dobel

**Cara Verifikasi Data Berhasil Disimpan:**
1. **Lihat Toast Notification** (hijau, kanan atas): "✅ Data berhasil disimpan"
2. **Lihat di tabel**: Data baru sudah muncul di list
3. **Refresh halaman**: Data tetap ada, tidak hilang

**Contoh di Semua Menu:**
- ✅ MSH: msh_add.php → redirect ke msh.php?success=1 → Toast hijau muncul
- ✅ Kohai: kohai_add.php → redirect ke kohai.php?success=1 → Toast hijau muncul
- ✅ Lokasi/Dojo: dojo_action.php → redirect ke lokasi.php?success=1 → Toast hijau muncul
- ✅ Pengurus: pengurus_add.php → redirect ke legalitas.php?success=1 → Toast hijau muncul

**Screenshots Toast Notification:**
```
┌─────────────────────────────────┐
│ ✓ Berhasil!                     │
│ Data berhasil disimpan          │
└─────────────────────────────────┘
```

---

### 3️⃣ Upload Foto Gagal

#### ✅ SOLUSI:

**A. Pastikan Format & Ukuran Benar:**
- ✅ Format: JPG, JPEG, PNG
- ✅ Ukuran maksimal: 2MB
- ❌ Jangan upload: GIF, BMP, WEBP, PDF

**B. Pastikan Folder Upload Ada:**
```bash
# Buat folder jika belum ada
mkdir uploads/kegiatan/
mkdir uploads/msh/
mkdir uploads/pengurus/

# Set permission (Windows via PowerShell)
icacls "uploads" /grant Everyone:F /T
```

**C. Cek Permission Folder:**
- Folder `uploads/` harus **writable**
- Gunakan **test_system_check.php** untuk cek permission

**D. Error Umum:**
- "Ukuran file terlalu besar" → Resize foto < 2MB
- "Format file tidak didukung" → Convert ke JPG/PNG
- "Gagal mengupload foto" → Cek folder permission

---

## 🔍 Tools untuk Debugging

### 1. System Checker
**File**: `test_system_check.php`  
**URL**: `http://localhost/ypok_management/ypok_management/test_system_check.php`

**Fungsi:**
- ✅ Cek koneksi database
- ✅ Cek struktur folder
- ✅ Cek file penting
- ✅ Cek database tables
- ✅ Cek sistem berita
- ✅ Cek file permissions

### 2. Berita Debugger
**File**: `test_berita_debug.php`  
**URL**: `http://localhost/ypok_management/ypok_management/test_berita_debug.php`

**Fungsi:**
- ✅ Tampilkan semua kegiatan + status berita
- ✅ Cek foto yang sudah diupload
- ✅ Verifikasi berita yang seharusnya tampil
- ✅ Cek folder uploads

---

## 📊 Checklist Sistem Berjalan Normal

### ✅ Database
- [ ] Database terkoneksi
- [ ] Semua table ada (kegiatan, msh, kohai, lokasi, dll)
- [ ] Column tampil_di_berita ada di table kegiatan
- [ ] Column foto ada di table kegiatan

### ✅ File Structure
- [ ] Folder uploads/ ada
- [ ] Folder uploads/kegiatan/ ada dan writable
- [ ] Folder uploads/msh/ ada dan writable
- [ ] Folder uploads/pengurus/ ada dan writable
- [ ] File components/navbar.php ada
- [ ] File toggle_berita.php ada

### ✅ Berita System
- [ ] Menu "📰 Kelola Berita" muncul di sidebar
- [ ] Page laporan_kegiatan.php bisa diakses
- [ ] Modal tambah kegiatan muncul
- [ ] Upload foto berfungsi
- [ ] Checkbox "Tampilkan sebagai Berita" ada
- [ ] Toggle switch berita berfungsi
- [ ] Dashboard admin menampilkan section "Berita Aktif"
- [ ] Guest dashboard menampilkan berita dengan foto

### ✅ Notification System
- [ ] Toast notification muncul setelah save data
- [ ] Toast notification berwarna hijau (success)
- [ ] Pesan "Data berhasil disimpan" tampil
- [ ] Toast auto-close setelah 3 detik

---

## 🚀 Workflow Menambah Berita yang Benar

### Step-by-Step:

1. **Login sebagai Admin**
   ```
   URL: http://localhost/ypok_management/ypok_management/
   ```

2. **Buka Menu Kelola Berita**
   - Klik sidebar: "📰 Kelola Berita"

3. **Tambah Kegiatan Baru**
   - Klik tombol "+ Tambah Kegiatan"
   - Isi form:
     - Nama Kegiatan ✅
     - Kategori ✅
     - Tanggal ✅
     - Lokasi ✅
     - PIC ✅
     - Jumlah Peserta ✅
     - Status ✅
     - **Upload Foto** ✅ (PENTING!)
     - Deskripsi (optional)
     - **CENTANG "📰 Tampilkan sebagai Berita"** ✅ (PENTING!)
   
4. **Klik Simpan**
   - Data akan tersimpan
   - Redirect ke laporan_kegiatan.php
   - Toast hijau muncul: "✅ Data berhasil disimpan"

5. **Verifikasi**
   - Lihat tabel: data baru muncul
   - Lihat kolom "📰 Berita": toggle switch **hijau** (aktif)
   - Buka Dashboard Admin: berita muncul di section "Berita Aktif"
   - Logout → Buka Guest Dashboard: berita tampil dengan foto

---

## ❌ Error Umum & Solusi

### Error 1: "Column tampil_di_berita does not exist"
**Solusi:**
```sql
-- Jalankan di Supabase SQL Editor
ALTER TABLE kegiatan ADD COLUMN IF NOT EXISTS tampil_di_berita BOOLEAN DEFAULT false;
CREATE INDEX IF NOT EXISTS idx_kegiatan_berita ON kegiatan(tampil_di_berita) WHERE tampil_di_berita = true;
```

### Error 2: "Column foto does not exist"
**Solusi:**
```sql
-- Jalankan di Supabase SQL Editor
ALTER TABLE kegiatan ADD COLUMN IF NOT EXISTS foto VARCHAR(255);
```

### Error 3: "Failed to upload file"
**Solusi:**
```bash
# Windows PowerShell
New-Item -ItemType Directory -Path "uploads/kegiatan" -Force
icacls "uploads" /grant Everyone:F /T
```

### Error 4: "Berita tidak muncul di guest dashboard"
**Solusi:**
1. Cek `test_berita_debug.php`
2. Pastikan ada berita dengan `tampil_di_berita = TRUE`
3. Pastikan foto ada di `uploads/kegiatan/`
4. Clear browser cache: Ctrl + Shift + R

---

## 📞 Kontak Tim Support

Jika masih ada masalah setelah mengikuti panduan ini:

1. **Jalankan System Check:**
   - Buka `test_system_check.php`
   - Screenshot hasilnya

2. **Jalankan Berita Debug:**
   - Buka `test_berita_debug.php`
   - Screenshot hasilnya

3. **Kirim ke Tim:**
   - Screenshot error
   - Screenshot test results
   - Deskripsi masalah

---

**Last Updated:** March 1, 2026  
**Version:** 2.0
