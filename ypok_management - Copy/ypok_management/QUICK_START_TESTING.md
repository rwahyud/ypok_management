# 🚀 QUICK START - Dummy Data & Testing

## 📋 Ringkasan Singkat

Sudah siap untuk testing comprehensive semua menu admin YPOK? Ikuti 3 langkah mudah di bawah:

---

## ⚡ LANGKAH 1: Import Dummy Data (2 menit)

### URL:
```
http://localhost/ypok_management/ypok_management/database/import_dummy_data.php
```

### Klik URL di atas, tunggu sampai selesai
```
✅ Akan melihat: "✅ ALL DUMMY DATA IMPORTED SUCCESSFULLY!"
✅ Total records: 80+
✅ Tables imported: 14 tables
```

### Data yang di-import:
- ✓ 2 Users (admin, user1)
- ✓ 5 Provinsi
- ✓ 5 Dojo
- ✓ 5 Lokasi Kegiatan
- ✓ 5 Master Sabuk Hitam
- ✓ 7 Kohai (Students)
- ✓ 5 Pengurus (Staff)
- ✓ 6 Dokumen Legalitas
- ✓ 12 Pembayaran
- ✓ 5 Kegiatan
- ✓ 8 Transaksi Keuangan

---

## ⚡ LANGKAH 2: Login Admin

### URL:
```
http://localhost/ypok_management/ypok_management/index.php
```

### Credentials:
```
Username: admin
Password: admin123
```

### Verifikasi:
- [ ] Dashboard menampilkan data (8 stat cards + 6 charts)
- [ ] Sidebar menu lengkap (9 menu items)
- [ ] Tidak ada error di console (F12)

---

## ⚡ LANGKAH 3: Mulai Testing

### File Testing (Pilih salah satu):

#### 📋 Option A: Comprehensive Checklist (Detailed)
```
File: TESTING_CHECKLIST.md
Lokasi: ypok_management/TESTING_CHECKLIST.md
Use: Untuk testing mendetail dengan 120+ test cases
```

#### 📋 Option B: Quick Guide (Step-by-step)
```
File: TESTING_GUIDE.md
Lokasi: ypok_management/TESTING_GUIDE.md
Use: Untuk panduan langkah demi langkah
```

#### 📋 Option C: Progress Tracker (Log Results)
```
File: TESTING_TRACKER.md
Lokasi: ypok_management/TESTING_TRACKER.md
Use: Untuk mencatat hasil & bugs yang ditemukan
```

---

## 🎯 Quick Test (5 menit per menu)

### Suggested Testing Order:

| No. | Menu | URL | Time | Priority |
|-----|------|-----|------|----------|
| 1 | 📊 Dashboard | / | 5 min | HIGH |
| 2 | 👨‍🏫 Data MSH | pages/msh.php | 7 min | MEDIUM |
| 3 | 👥 Data Kohai | pages/kohai.php | 7 min | MEDIUM |
| 4 | 💳 Pembayaran | pages/pembayaran.php | 10 min | ⭐ CRITICAL |
| 5 | 📊 Laporan Keuangan | pages/laporan_keuangan.php | 10 min | ⭐ CRITICAL |
| 6 | 📍 Lokasi | pages/lokasi.php | 5 min | LOW |
| 7 | 📄 Legalitas | pages/legalitas.php | 5 min | MEDIUM |
| 8 | 📺 Kelola Tampilan Kegiatan | pages/kegiatan_display.php | 7 min | HIGH (NEW) |
| 9 | 📋 Laporan Kegiatan | pages/laporan_kegiatan.php | 7 min | MEDIUM |

**Total Time:** ~60 minutes full testing

---

## ✅ Testing Checklist Singkat (Per Page)

### ✓ Untuk setiap halaman, test:

```
1. DATA DISPLAY
   [ ] Semua records tampil
   [ ] Kolom lengkap
   [ ] Format benar (Rp untuk currency, dd-mm-yyyy untuk date)
   [ ] Tidak ada error JavaScript

2. CRUD OPERATIONS
   [ ] Tambah data: Form bisa diisi & simpan
   [ ] Edit data: Data lama load, bisa diubah, simpan
   [ ] Hapus data: Ada konfirmasi, data dihapus

3. FILTER & SEARCH (jika ada)
   [ ] Search works
   [ ] Filter works
   [ ] Results akurat

4. EXPORT (jika ada)
   [ ] Export PDF: File generate, buka dengan benar
   [ ] Export Excel: File generate, format ok
   [ ] Export CSV: File generate, encoding utf-8

5. RESPONSIVE DESIGN
   [ ] Desktop (>1200px): Full layout
   [ ] Tablet (768px): Single column
   [ ] Mobile (480px): Optimized

6. CONSOLE
   [ ] F12 Console: Tidak ada error merah
   [ ] Network: Semua status 200 atau 304
```

---

## 🐛 Kalau Ketemu Bug

### Format Laporan:
```
PAGE: [Menu name]
BUG: [Apa yang salah]
SEVERITY: CRITICAL | HIGH | MEDIUM | LOW

Steps:
1. ...
2. ...
3. ...

Expected: [Seharusnya apa]
Actual: [Tapi yang terjadi apa]
```

### Contoh Bug Report:
```
PAGE: Pembayaran
BUG: Export button tidak working
SEVERITY: HIGH

Steps:
1. Pergi ke Pembayaran
2. Klik Export PDF
3. Modal muncul

Expected: Modal submit dan PDF download
Actual: Error di console "format is undefined"

Note: Sudah di-fix di version terbaru
```

---

## 🧪 Known Fixes yang Sudah Diterapkan

### ✅ 1. Saldo Calculation (Laporan Keuangan)
**Status:** FIXED ✓
- Formula: Pemasukan - Pengeluaran
- Test: Verifikasi nilai saldo akurat

### ✅ 2. Pembayaran Column (tanggal_bayar)
**Status:** FIXED ✓
- Column: tanggal_bayar (bukan tanggal)
- Test: Verifikasi sorting & display benar

### ✅ 3. Export Form Fields
**Status:** FIXED ✓
- Modal form: formData.get('format')
- Test: Export button works tanpa error

### ✅ 4. UI Sizing Normalization
**Status:** FIXED ✓
- Semua sidebar padding, icon size, button size, table padding sama
- Test: Visual consistency across pages

### ✅ 5. Responsive Design
**Status:** VERIFIED ✓
- 5 breakpoints implemented
- Test: Mobile/tablet/desktop layout bekerja

### ✅ 6. Kegiatan Display Management
**Status:** NEW FEATURE ✓
- Auto-migration untuk missing columns
- Test: Toggle switches & display settings work

---

## 📞 Troubleshooting

### Import gagal?
```
1. Pastikan MySQL running
2. Database sudah dibuat
3. Coba refresh browser
4. Lihat error message di halaman
```

### Login tidak bisa?
```
1. Username: admin
2. Password: admin123
3. Pastikan admin user sudah di-import
4. Clear browser cache (Ctrl+Shift+Delete)
```

### Export tidak works?
```
1. Check browser console (F12)
2. Pastikan DOMPDF library loaded
3. Cek file permissions di /uploads folder
4. Lihat PHP error log
```

### Data tidak tampil?
```
1. Pastikan dummy data sudah di-import
2. Refresh page (Ctrl+R)
3. Check database connection
4. Lihat browser console
```

---

## 📊 Dashboard Data Check

Kalau login berhasil, lihat Dashboard dan verifikasi:

```
✓ Total MSH: 5
✓ Total Kohai: 7
✓ Total Pembayaran: 12
✓ Total Kegiatan: 5
✓ Total Transaksi: 8
✓ 6 Charts rendering

Kalau semua ✓, import berhasil!
```

---

## 🎬 Next Steps After Testing

1. **Catat semua bugs** ditemukan
2. **Prioritas fixes** (CRITICAL dulu)
3. **Fix bugs** satu per satu
4. **Regression test** setelah fix
5. **Go live** kalau semua clear

---

## 📚 Files Reference

| File | Purpose | Use When |
|------|---------|----------|
| import_dummy_data.php | Import script | Awal testing |
| TESTING_CHECKLIST.md | 120+ test cases | Testing mendetail |
| TESTING_GUIDE.md | Step-by-step guide | Need guidance |
| TESTING_TRACKER.md | Progress log | Record results |

---

## 🎉 Ready to Start?

1. ✓ Buka: `http://localhost/ypok_management/ypok_management/database/import_dummy_data.php`
2. ✓ Login: admin / admin123
3. ✓ Open: TESTING_GUIDE.md atau TESTING_CHECKLIST.md
4. ✓ Start testing!

**Good luck! Let's find those bugs! 🚀**

