# 🚀 YPOK MANAGEMENT - DUMMY DATA & TESTING GUIDE

## 📋 Overview

Ini adalah panduan lengkap untuk:
1. Import dummy data ke database YPOK
2. Menjalankan comprehensive testing pada semua menu admin
3. Mengidentifikasi dan mendokumentasikan bugs

**Status:** 100+ test cases ready
**Test Data:** 14 tabel dengan 80+ records
**Expected Duration:** 30-45 minutes per full test run

---

## 🔧 STEP 1: Persiapan

### 1.1 Pastikan XAMPP Berjalan
```
- Start Apache
- Start MySQL
```

### 1.2 Akses Admin Dashboard
```
URL: http://localhost/ypok_management/ypok_management/index.php
```

### 1.3 Login dengan Akun Default
```
Username: admin
Password: admin123
```

---

## 📊 STEP 2: Import Dummy Data

### 2.1 Buka Import Script
```
URL: http://localhost/ypok_management/ypok_management/database/import_dummy_data.php
```

### 2.2 Tunggu Proses Selesai
Anda akan melihat progress dengan check-mark ✓ untuk setiap langkah:
- ✓ Database Connection Successful
- ✓ Importing Users...
- ✓ Importing Provinsi...
- ✓ Importing Dojo...
- ✓ Importing Lokasi... (5 records)
- ✓ Importing Master Sabuk Hitam... (5 records)
- ✓ Importing Prestasi MSH... (5 records)
- ✓ Importing Kohai... (7 records)
- ✓ Importing Prestasi Kohai... (4 records)
- ✓ Importing Pengurus... (5 records)
- ✓ Importing Legalitas... (6 records)
- ✓ Importing Pembayaran... (12 records)
- ✓ Importing Kegiatan... (5 records)
- ✓ Importing Transaksi... (8 records)
- ✓ Importing Informasi Yayasan...

### 2.3 Verifikasi Import Berhasil
```
Expected: "✅ ALL DUMMY DATA IMPORTED SUCCESSFULLY!"
Total records imported: 100+
```

### 2.4 Klik "Go to Admin Dashboard"
Anda akan diarahkan ke dashboard dengan data lengkap.

---

## 📋 STEP 3: Jalankan Testing Checklist

### 3.1 Buka File Testing Checklist
```
File: TESTING_CHECKLIST.md
Location: ypok_management/TESTING_CHECKLIST.md
```

### 3.2 Testing Order (Recommended)

Test dalam urutan ini untuk hasil optimal:

#### **1. Dashboard** (5-7 menit)
- [ ] Verifikasi 8 stat cards menampilkan data
- [ ] Verifikasi 6 chart merender dengan benar
- [ ] Test responsive design (desktop/tablet/mobile)
- [ ] Cek browser console untuk errors

#### **2. Data MSH** (5-7 menit)
- [ ] Verifikasi 5 records tersedia
- [ ] Test Add MSH (create new record)
- [ ] Test Edit MSH (modify existing)
- [ ] Test Delete MSH (remove record)
- [ ] Test Search & Filter
- [ ] Test Export (PDF/Excel/CSV)

#### **3. Data Kohai** (5-7 menit)
- [ ] Verifikasi 7 records visible
- [ ] Test CRUD operations
- [ ] Test Guardian (wali) data
- [ ] Test Status filtering
- [ ] Test Export functionality

#### **4. Lokasi** (3-5 menit)
- [ ] Test CRUD operations
- [ ] Verify capacity validation
- [ ] Test search functionality

#### **5. Pembayaran** (7-10 menit) ⭐ IMPORTANT
- [ ] Verifikasi 12 records tampil
- [ ] Test tanggal_bayar column (FIXED ISSUE)
- [ ] Test currency formatting (Rp)
- [ ] Test status filtering
- [ ] Test all exports (PDF/Excel/CSV)
- [ ] Verify modal form export works

#### **6. Legalitas** (3-5 menit)
- [ ] Test document management
- [ ] Verify expiration warnings
- [ ] Test CRUD operations

#### **7. Kelola Tampilan Kegiatan** (5-7 menit) ⭐ NEW FEATURE
- [ ] Verify setup page displays
- [ ] Test auto-migration (if needed)
- [ ] Test toggle switches
- [ ] Verify changes save correctly

#### **8. Laporan Kegiatan** (5-7 menit)
- [ ] Test filtering
- [ ] Test sorting
- [ ] Test exports (modal form FIXED)

#### **9. Laporan Keuangan** (7-10 menit) ⭐ HIGH PRIORITY
- [ ] Verify saldo calculation (FIXED)
- [ ] Test Pemasukan vs Pengeluaran calculations
- [ ] Verify PDF formatting (CSS FIXED)
- [ ] Verify PENDIDIKAN label (spelling FIXED)
- [ ] Test all export formats

#### **10. General Tests** (5-7 menit)
- [ ] Test sidebar collapse (responsive)
- [ ] Test logout
- [ ] Test session timeout
- [ ] Test form validation
- [ ] Test browser console (no errors)

### 3.3 Dokumentasi Bugs

Gunakan template ini untuk setiap bug yang ditemukan:

```
BUG #: [Number]
Page: [Menu Name]
Severity: CRITICAL | HIGH | MEDIUM | LOW

Steps to Reproduce:
1. [Step 1]
2. [Step 2]
3. [Step 3]

Expected: [What should happen]
Actual: [What actually happens]

Notes: [Any additional info]
```

---

## 🎯 Testing Tips & Best Practices

### 3.1 Tips untuk Setiap Test

**Dashboard Tests:**
- Buka DevTools (F12) dan cek Console tab
- Lihat Network tab untuk memastikan semua resources load
- Test zoom pada browser (Ctrl + Plus/Minus)

**CRUD Tests:**
- Refresh page (Ctrl+R) setelah setiap operasi
- Verifikasi data di database juga berubah
- Test dengan invalid data terlebih dahulu

**Export Tests:**
- Verifikasi file yang didownload sesuai format
- Cek apakah semua kolom present
- Verifikasi currency dan date formatting

**Responsive Tests:**
- Test pada breakpoints: 1920px, 1200px, 1024px, 768px, 480px, 375px
- Gunakan DevTools device emulation
- Test touch interactions pada mobile

### 3.2 Responsive Breakpoints untuk Test

| Device | Width | Sidebar | Columns | Layout |
|--------|-------|---------|---------|--------|
| Desktop | >1200px | 250px (expanded) | 4 col | Full |
| Tablet Large | 1024-1200px | 250px | 2 col | Adapted |
| Tablet | 768-1024px | 80px (collapsed) | 1-2 col | Compact |
| Mobile | 480-768px | 80px | 1 col | Stack |
| Mobile Small | ≤480px | 80px | 1 col | Ultra-compact |

### 3.3 Chrome DevTools Tips

```
1. Buka DevTools: F12
2. Test Responsive: Ctrl+Shift+M atau Elements > Device Toolbar
3. Clear Cache: Ctrl+Shift+Delete
4. Console Check: Lihat error messages (red)
5. Network Tab: Lihat loading times & status codes
6. Performance: Lihat FPS & loading performance
```

---

## 📊 Data Summary Setelah Import

| Table | Records | Purpose |
|-------|---------|---------|
| users | 2 | Authentication (admin, user1) |
| provinsi | 5 | Regions/States |
| dojo | 5 | Training centers |
| lokasi | 5 | Event venues |
| master_sabuk_hitam | 5 | Black belt instructors |
| kohai | 7 | Students/learners |
| prestasi_msh | 5 | MSH achievements |
| prestasi_kohai | 4 | Kohai achievements |
| pengurus | 5 | Organization staff |
| legalitas | 6 | Legal documents |
| pembayaran | 12 | Payment records |
| kegiatan | 5 | Activities/events |
| transaksi | 8 | Financial transactions |
| informasi_yayasan | 1 | Organization info |
| **TOTAL** | **80+** | |

---

## ✅ Pre-Testing Verification

Sebelum mulai testing, pastikan:

- [ ] Apache & MySQL running
- [ ] Can access: `http://localhost/ypok_management/ypok_management`
- [ ] Dummy data imported successfully
- [ ] Login berhasil dengan admin/admin123
- [ ] Dashboard menampilkan stat cards dengan data
- [ ] Sidebar menu komplet (tidak ada error)
- [ ] Console (DevTools) tidak ada error merah

---

## 🔍 Known Fixed Issues (Verification)

Verifikasi fixes berikut sudah diterapkan:

### ✅ 1. PDF Export - Saldo Calculation
**File:** pages/laporan_keuangan.php
**Status:** FIXED - Saldo sekarang dihitung: Pemasukan - Pengeluaran

### ✅ 2. Export Column Names - Pembayaran
**File:** export/export_laporan_pdf.php
**Status:** FIXED - Menggunakan 'tanggal_bayar' (bukan 'tanggal')

### ✅ 3. Form Field Names - Export Modal
**Files:** pages/pembayaran.php, pages/laporan_kegiatan.php
**Status:** FIXED - Modal form fields sekarang mapped dengan benar

### ✅ 4. Spelling - YPOK
**Files:** export files
**Status:** FIXED - "Pendidikan" (bukan "Perguruan")

### ✅ 5. UI Sizing Normalization
**Files:** assets/css/style.css, all pages
**Status:** FIXED - Semua sizing konsisten

### ✅ 6. Responsive Design
**Files:** assets/css/style.css
**Status:** VERIFIED - 5 breakpoints working

### ✅ 7. Kegiatan Display Management
**Files:** pages/kegiatan_display.php (NEW), actions/migrate_kegiatan_display.php (NEW)
**Status:** NEW FEATURE - Auto-migration untuk missing columns

---

## 📈 Expected Test Results

Setelah testing lengkap, output yang diharapkan:

```
✅ 120+ test cases executed
✅ 95%+ passing rate
✅ UI consistent across all pages
✅ Responsive design working on all breakpoints
✅ All exports functioning (PDF/Excel/CSV)
✅ All CRUD operations working
✅ Data accurate and complete
✅ No critical bugs
✅ Performance acceptable (<3s load time)
```

---

## 🐛 Bug Severity Guidelines

| Level | Impact | Priority | Example |
|-------|--------|----------|---------|
| CRITICAL | Application broken, data loss | Fix immediately | Export fails, data not saving |
| HIGH | Major functionality broken | Fix this session | Button not working, chart not rendering |
| MEDIUM | Functionality impaired | Fix in next iteration | Wrong calculation, UI layout off |
| LOW | Minor cosmetic issue | Fix when convenient | Spacing off by 1px, typo |

---

## 📞 Support & Troubleshooting

### Import Script Error?
1. Pastikan MySQL sudah berjalan
2. Pastikan connection ke database berhasil
3. Clear existing data dulu kalau perlu reset

### Testing Gagal di Halaman X?
1. Refresh page (Ctrl+R)
2. Check browser console (F12)
3. Lihat Network tab untuk HTTP errors

### Export Tidak Bekerja?
1. Pastikan DOMPDF library terpasang
2. Check file permissions di folder upload
3. Check PHP error log

### Responsive Design Tidak Tepat?
1. Clear browser cache (Ctrl+Shift+Delete)
2. Test di DevTools (F12 + Ctrl+Shift+M)
3. Test di multiple browsers

---

## ✨ Next Steps Setelah Testing

1. **Dokumentasi Bugs**: Catat semua bugs ditemukan
2. **Prioritas Fixes**: Urutkan fixes by severity
3. **Fix Bugs**: Perbaiki issues identified
4. **Regression Testing**: Test ulang setelah fixes
5. **Production Ready**: Deploy jika semua clear

---

## 📝 Test Session Template

```
DATE: [Date]
TESTER: [Name]
START TIME: [Time]
END TIME: [Time]
TOTAL DURATION: [Time]

TESTS EXECUTED: [Number]
TESTS PASSED: [Number]
TESTS FAILED: [Number]

BUGS FOUND:
- [Bug 1]: [Severity] - [Description]
- [Bug 2]: [Severity] - [Description]
- ...

CRITICAL ISSUES: [Number]
BLOCKERS: [Yes/No]

NOTES:
[Any additional notes or observations]
```

---

## 🎉 You're Ready!

Silahkan mulai testing. Gunakan TESTING_CHECKLIST.md untuk panduan detail setiap halaman.

Good luck! 🚀

