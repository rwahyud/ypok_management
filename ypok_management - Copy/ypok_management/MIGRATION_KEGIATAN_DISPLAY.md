# Panduan Migrasi: Hapus Menu Toko & Tambah Kelola Tampilan Kegiatan

## 📋 Ringkasan Perubahan

- ❌ **Menu Toko** dihapus dari navbar
- ✅ **Menu Baru**: "Kelola Tampilan Kegiatan" ditambahkan untuk admin
- 📊 **Database**: Kolom `tampil_di_berita` dan `foto` ditambahkan ke tabel `kegiatan`

---

## 🔧 Langkah Migrasi untuk Instalasi Existing

### 1️⃣ Update Database (PENTING)

Jalankan SQL query berikut di MySQL/MariaDB:

```sql
-- Tambahkan kolom ke tabel kegiatan
ALTER TABLE `kegiatan` 
ADD COLUMN `tampil_di_berita` BOOLEAN DEFAULT FALSE AFTER `keterangan`,
ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `tampil_di_berita`,
ADD KEY `idx_tampil_di_berita` (`tampil_di_berita`);
```

**Atau**: Import file `database/add_kegiatan_display_column.sql`:
```bash
mysql -u username -p ypok_management < database/add_kegiatan_display_column.sql
```

---

### 2️⃣ File Baru yang Ditambahkan

```
✅ pages/kegiatan_display.php
   └─ Admin interface untuk kelola tampilan kegiatan di guest dashboard

✅ actions/toggle_kegiatan_display.php
   └─ API endpoint untuk toggle tampil_di_berita
```

---

### 3️⃣ File yang Diperbarui

```
✅ components/navbar.php
   └─ Hapus: Menu "Toko" 
   └─ Tambah: Menu "Kelola Tampilan Kegiatan"

✅ database/ypok_database.sql
   └─ Tambah kolom tampil_di_berita dan foto ke kegiatan table

✅ QUICK_REFERENCE.md
   └─ Update referensi menu
```

---

## 📱 Fitur Baru: Kelola Tampilan Kegiatan

### Akses Menu
- Login sebagai **admin**
- Buka menu **"Kelola Tampilan Kegiatan"** di sidebar

### Fungsi
- ✅ Lihat daftar semua kegiatan
- ✅ Toggle tampilan kegiatan di guest dashboard (on/off)
- ✅ Tampilkan maksimal 3 kegiatan terbaru
- 📊 Lihat statistik kegiatan yang ditampilkan

### Cara Kerja
1. Admin mengaktifkan toggle untuk kegiatan yang ingin ditampilkan
2. System menyimpan setting ke database (`tampil_di_berita = TRUE`)
3. Guest dashboard menampilkan kegiatan dengan `tampil_di_berita = TRUE`
4. Hanya 3 kegiatan terbaru yang ditampilkan di guest dashboard

---

## 🧹 Hapus Menu Toko

Menu toko telah dihapus dari sidebar. Jika masih ada file terkait toko yang ingin dihapus:

```
Optional - Hapus file toko:
- pages/toko.php
- actions/add_produk.php
- actions/delete_produk.php
- actions/edit_produk.php
- actions/add_kategori.php
- actions/delete_kategori.php
- actions/get_transaksi.php
```

⚠️ Catatan: Table `produk_toko`, `kategori_produk`, `transaksi_toko` masih ada di database untuk referensi

---

## ✅ Testing Checklist

- [ ] Database sudah di-update dengan kolom baru
- [ ] Menu "Toko" sudah hilang dari sidebar
- [ ] Menu "Kelola Tampilan Kegiatan" muncul di sidebar
- [ ] Bisa login ke halaman kegiatan_display.php
- [ ] Toggle tampil_di_berita berfungsi
- [ ] Guest dashboard menampilkan kegiatan yang di-toggle
- [ ] Export tetap berfungsi normal

---

## 🆘 Troubleshooting

### Error: "Unknown column 'tampil_di_berita'"
**Solusi**: Jalankan migration SQL di section "Update Database"

### Menu kegiatan_display tidak muncul
**Solusi**: Clear browser cache (Ctrl+Shift+Del or Cmd+Shift+Del)

### Toggle tidak bekerja
**Solusi**: Check browser console untuk error, pastikan database sudah di-update

---

## 📝 Catatan

- Guest dashboard akan menampilkan kegiatan dengan `tampil_di_berita = TRUE`
- Urutan: **3 kegiatan terbaru** (by tanggal_kegiatan DESC) yang memiliki status **"Terlaksana"**
- Admin dapat toggle kegiatan kapan saja tanpa update data kegiatan

---

**Selesai! 🎉 Sistem siap digunakan dengan menu baru.**
