## 📋 TESTING CHECKLIST - YPOK ADMIN INTERFACE

**Instructions:**
1. Import dummy data first: http://localhost/ypok_management/ypok_management/database/import_dummy_data.php
2. Login as admin (username: admin, password: admin123)
3. Test each menu according to this checklist
4. Mark bugs found with [BUG], [WARNING], or [ISSUE]
5. Include severity level: CRITICAL | HIGH | MEDIUM | LOW

---

## 1️⃣ DASHBOARD
**URL:** /index.php (Redirect to pages/dashboard.php)
**Expected:** Central hub showing 8 stat cards and 6 chart visualizations

### Layout & Display
- [ ] Page title "📊 Dashboard" displays correctly
- [ ] 8 stat cards are visible (Total MSH, Total Kohai, Total Pembayaran, Total Kegiatan, etc.)
- [ ] All stat cards show correct data from database
- [ ] 6 charts render without JavaScript errors:
  - [ ] Chart 1: MSH per Dan
  - [ ] Chart 2: Kohai per Kyu
  - [ ] Chart 3: Pembayaran Status (Lunas/Belum Lunas)
  - [ ] Chart 4: Kegiatan per Kategori
  - [ ] Chart 5: Transaksi Pemasukan vs Pengeluaran
  - [ ] Chart 6: Top 5 Pembayaran Terbesar

### Data Accuracy
- [ ] Stat cards count matches actual database records
- [ ] Chart values are accurate (cross-verify with database)
- [ ] All currency values display with Rp format
- [ ] Dates display in Indonesian format (dd-mm-yyyy)

### Responsive Design
- [ ] Desktop (>1200px): 4 stat cards per row, charts full width
- [ ] Tablet (1024px): 2 stat cards per row
- [ ] Mobile (768px): 1 stat card per row
- [ ] Small Mobile (≤480px): Single column layout

### Browser Console
- [ ] No JavaScript errors
- [ ] No network errors
- [ ] All Chart.js libraries loaded successfully

---

## 2️⃣ DATA MSH (Master Sabuk Hitam)
**URL:** pages/msh.php
**Expected:** CRUD for Master Sabuk Hitam (Dan 1-9 levels)

### Table Display
- [ ] Page title "🥋 Data Master Sabuk Hitam" displays
- [ ] 5 MSH records visible in table
- [ ] Columns display: No, Nama, Tempat Lahir, Tanggal Lahir, Jenis Kelamin, Tingkat Dan, Email, No Telp, Aksi
- [ ] Table responsive on mobile (horizontal scroll)
- [ ] Pagination works (if >10 records)

### Search & Filter
- [ ] Search by name works correctly
- [ ] Filter by level (Dan) works
- [ ] Filter results clear correctly

### CRUD Operations
- [ ] **Add New:**
  - [ ] "Tambah MSH" button opens modal
  - [ ] Modal form fields: No MSH, Nama, Tempat Lahir, Tanggal Lahir, Jenis Kelamin, Tingkat Dan, Tanggal Lulus, Nomor Ijazah, Alamat, No Telp, Email, Dojo Cabang, Status
  - [ ] All required fields are validated
  - [ ] Form can be submitted without errors
  - [ ] New record appears in table
  - [ ] Modal closes after successful save

- [ ] **Edit:**
  - [ ] Click edit icon opens modal with existing data
  - [ ] Form fields prefilled correctly
  - [ ] Changes save successfully
  - [ ] Updated record reflects in table

- [ ] **Delete:**
  - [ ] Click delete icon shows confirmation
  - [ ] Record deleted successfully
  - [ ] Record removed from table

### Photo Upload (if feature exists)
- [ ] Upload button works
- [ ] File validation (PDF/JPG/PNG only)
- [ ] File size validation
- [ ] Photo displays in record

### Prestasi & Sertifikasi Tabs (if exists)
- [ ] Prestasi tab shows related achievements
- [ ] Sertifikasi tab shows related certifications
- [ ] Add/Edit/Delete works for related records

### Export (if feature exists)
- [ ] Export to PDF works
- [ ] Export to Excel works
- [ ] Export to CSV works
- [ ] Exported file contains all columns correctly

### Data Validation
- [ ] Cannot save with empty nama field
- [ ] Cannot save with invalid email format
- [ ] Cannot save with duplicate nomor MSH

### Responsive Design
- [ ] Desktop: Normal table view
- [ ] Mobile: Horizontal scroll with min-width
- [ ] Modal responsive on small screens

---

## 3️⃣ DATA KOHAI (Students/Learners)
**URL:** pages/kohai.php
**Expected:** CRUD for Kohai (Kyu 1-10 levels)

### Table Display
- [ ] Page title "👥 Data Kohai" displays
- [ ] 7 Kohai records visible
- [ ] Columns: No, Kode, Nama, Sabuk, Tingkat Kyu, Dojo, Status, Aksi
- [ ] Color-coded sabuk levels (Putih, Kuning, Orange, etc.)

### CRUD Operations
- [ ] **Add New:** Form opens, fields populated, saves successfully
- [ ] **Edit:** Existing data loads, changes save
- [ ] **Delete:** Confirmation and successful deletion

### Guardian Information
- [ ] Guardian/wali data fields present (Nama Wali, No Telp Wali)
- [ ] Guardian info saves and displays correctly

### Status Management
- [ ] Status dropdown works (Aktif/Tidak Aktif)
- [ ] Filter by status works

### Export Functionality
- [ ] PDF export includes all kohai data
- [ ] Excel export formats correctly
- [ ] CSV export has proper encoding

### Data Validation
- [ ] Cannot save without kode_kohai
- [ ] Cannot save with duplicate kode_kohai
- [ ] Tanggal lahir must be reasonable

---

## 4️⃣ LOKASI
**URL:** pages/lokasi.php
**Expected:** CRUD for event locations/venues

### Display
- [ ] 5 lokasi records visible
- [ ] Columns: Nama, Alamat, Kota, Provinsi, Kapasitas, Fasilitas, Status

### CRUD Operations
- [ ] Add new lokasi: Form submits, record appears
- [ ] Edit lokasi: Data loads, changes save
- [ ] Delete lokasi: With confirmation

### Capacity Management
- [ ] Kapasitas field accepts numbers only
- [ ] Fasilitas text area works for comma-separated list

### Location Data
- [ ] Alamat field stores full address
- [ ] Kota/Provinsi fields populate correctly
- [ ] Status field (aktif/tidak aktif) works

---

## 5️⃣ PEMBAYARAN (Payments)
**URL:** pages/pembayaran.php
**Expected:** Payment record management with exports

### Display
- [ ] 12 pembayaran records visible
- [ ] Columns: Tanggal, Nama Kohai, Kategori, Jumlah, Metode, Status, Aksi
- [ ] All currency values show Rp format (Rp 100,000)
- [ ] Status color-coded (Lunas=green, Belum Lunas=red)

### Database Column Fix Verification
- [ ] tanggal_bayar column used (not tanggal) ✓ FIXED
- [ ] Sorting by tanggal_bayar works correctly
- [ ] Date formatting displays correctly

### CRUD Operations
- [ ] Add pembayaran: Form submits, record appears
- [ ] Edit: Data loads, changes save
- [ ] Delete: Confirmation and successful deletion

### Filtering
- [ ] Filter by kohai works
- [ ] Filter by status (Lunas/Belum Lunas) works
- [ ] Filter by kategori works
- [ ] Filter by date range works

### Export Operations
- [ ] **PDF Export:**
  - [ ] Modal shows format options
  - [ ] PDF generates without errors
  - [ ] PDF contains all selected records
  - [ ] Formatting looks professional
  - [ ] Tanggal_bayar displays correctly

- [ ] **Excel Export:**
  - [ ] Excel file generates
  - [ ] Column headers correct (uses tanggal_bayar not tanggal)
  - [ ] Data formatted correctly
  - [ ] Currency formatting preserved

- [ ] **CSV Export:**
  - [ ] CSV generates with UTF-8 encoding
  - [ ] Comma-separated values correct
  - [ ] Special characters handled properly

### Modal Form Fix Verification
- [ ] Export modal form field: formData.get('format') works ✓ FIXED
- [ ] Modal opens without JavaScript errors
- [ ] Form submits to correct export handler

---

## 6️⃣ LEGALITAS (Legal Documents)
**URL:** pages/legalitas.php
**Expected:** Legal document management

### Display
- [ ] 6 legalitas records visible
- [ ] Columns: Jenis Dokumen, Nomor, Tanggal Terbit, Tanggal Kadaluarsa, Instansi, Status, Aksi

### Status Indicators
- [ ] Status "Aktif" shows green
- [ ] Status "Akan Kadaluarsa" shows yellow/orange warning
- [ ] Expiration dates highlight documents needing renewal

### CRUD Operations
- [ ] Add legalitas: Form submits successfully
- [ ] Edit: Data loads and saves correctly
- [ ] Delete: With confirmation

### Document Upload (if applicable)
- [ ] Upload document button works
- [ ] Accepted file types validated
- [ ] Document stores in uploads folder

### Expiration Alert
- [ ] Documents expiring within 6 months flagged
- [ ] Dashboard shows count of documents needing renewal

### Data Validation
- [ ] Tanggal_kadaluarsa must be after tanggal_terbit
- [ ] Nomor dokumen field required

---

## 7️⃣ KELOLA TAMPILAN KEGIATAN (Manage Kegiatan Display)
**URL:** pages/kegiatan_display.php
**Expected:** Manage which kegiatan show on guest dashboard

### Setup Page
- [ ] Orange banner shows "Konfigurasi diperlukan"
- [ ] Auto-migration button works (if columns missing)
- [ ] Setup checks for tampil_di_berita and foto columns

### Migration System ✓ NEW FEATURE
- [ ] Auto-detection of missing columns works
- [ ] Migration button adds missing columns
- [ ] No errors after migration

### Kegiatan List
- [ ] All kegiatan records display
- [ ] Columns: Nama Kegiatan, Kategori, Tanggal, Tampil di Berita (Toggle)

### Toggle Functionality
- [ ] Toggle switches work (on/off)
- [ ] Changes save via AJAX
- [ ] Page updates without refresh
- [ ] Database updates reflect changes

### Filtering
- [ ] Filter by kategori works
- [ ] Show active/inactive toggles only works
- [ ] Search by nama kegiatan works

### Responsive Design
- [ ] Setup card responsive
- [ ] Table responsive on mobile
- [ ] Toggle switches touchable on mobile

---

## 8️⃣ LAPORAN KEGIATAN (Activity Report)
**URL:** pages/laporan_kegiatan.php
**Expected:** Event/activity reporting

### Table Display
- [ ] 5 kegiatan records visible
- [ ] Columns: Nama, Deskripsi, Tanggal, Waktu, Lokasi, Kategori, Peserta, Status

### Modal Form Fix Verification
- [ ] Export modal form fields all correctly mapped ✓ FIXED
- [ ] Export works without form submission errors

### Filtering
- [ ] Filter by kategori works
- [ ] Filter by status works
- [ ] Filter by date range works

### Export Operations
- [ ] PDF export generates correctly
- [ ] Excel export includes all fields
- [ ] CSV export has proper encoding

### Data Display
- [ ] Tanggal displays in Indonesian format
- [ ] Waktu displays in HH:MM format
- [ ] Jumlah peserta shows as number

### CRUD Operations
- [ ] Add kegiatan: Form submits
- [ ] Edit kegiatan: Data loads and saves
- [ ] Delete kegiatan: With confirmation

### Sorting
- [ ] Sort by tanggal works
- [ ] Sort by nama works
- [ ] Sort by peserta works

---

## 9️⃣ LAPORAN KEUANGAN (Financial Report)
**URL:** pages/laporan_keuangan.php
**Expected:** Financial statements and reporting

### Report Display
- [ ] Report title displays correctly
- [ ] Saldo calculation shows correct value ✓ FIXED (CRITICAL)

### Data Summary
- [ ] Total Pemasukan calculated correctly
- [ ] Total Pengeluaran calculated correctly
- [ ] Saldo Akhir = Total Pemasukan - Total Pengeluaran
- [ ] All currency values show Rp format

### Charts
- [ ] Pemasukan vs Pengeluaran chart renders
- [ ] Chart data accurate
- [ ] Legend displays correctly

### Transaction Details Table
- [ ] All 8 transaksi records visible
- [ ] Columns: Jenis, Jumlah, Keterangan, Kategori, Status
- [ ] Color coding: Pemasukan=green, Pengeluaran=red

### Export Operations
- [ ] **PDF Export:**
  - [ ] All data includes saldo calculation
  - [ ] Formatting looks professional ✓ FIXED (CSS alignment)
  - [ ] Text alignment correct (currency right-aligned, amounts justified)
  - [ ] Page breaks handle large datasets

- [ ] **Excel Export:**
  - [ ] All columns present
  - [ ] Currency formatting preserved
  - [ ] Saldo row prominent

- [ ] **CSV Export:**
  - [ ] Proper UTF-8 encoding
  - [ ] PENDIDIKAN label used (not PERGURUAN) ✓ FIXED

### Spelling Verification
- [ ] "YPOK" displays as organizational name
- [ ] "Yayasan Pendidikan Olahraga Karate" used (not Perguruan) ✓ FIXED
- [ ] Indonesian terms spelled correctly

### Responsive Display
- [ ] Desktop: Table full width
- [ ] Mobile: Horizontal scroll with min-width
- [ ] Chart responsive

---

## ⚙️ GENERAL FUNCTIONALITY TESTS

### Navigation
- [ ] Sidebar menu collapses at 768px (mobile view)
- [ ] All menu items accessible from navbar
- [ ] Logout works correctly
- [ ] Session timeout works (30 minutes)

### Styling & Consistency
- [ ] All pages follow same design pattern
- [ ] Sidebar padding normalized (14px) ✓ FIXED
- [ ] Menu icons standardized (18px) ✓ FIXED
- [ ] Page titles standardized (26px) ✓ FIXED
- [ ] Table padding consistent (14px 16px) ✓ FIXED
- [ ] Button sizes consistent (40-56px) ✓ FIXED
- [ ] Card padding unified (24px) ✓ FIXED

### Form Validation
- [ ] Required fields validated
- [ ] Invalid email rejected
- [ ] Duplicate records prevented
- [ ] Number fields accept only numbers
- [ ] Date fields accept only dates

### Database Operations
- [ ] No SQL injection vulnerabilities
- [ ] Prepared statements used
- [ ] Foreign key relationships working
- [ ] Cascading deletes work correctly
- [ ] Transaction integrity maintained

### Export System
- [ ] PDF generation works on all pages
- [ ] Excel generation works
- [ ] CSV generation works
- [ ] File naming consistent
- [ ] File downloads correctly

### Error Handling
- [ ] Database errors handled gracefully
- [ ] User-friendly error messages displayed
- [ ] No stack traces shown to users
- [ ] Try-catch blocks implemented

### Performance
- [ ] Pages load in <3 seconds
- [ ] No N+1 database queries
- [ ] Charts render smoothly
- [ ] Export completes in reasonable time

### Browser Compatibility
- [ ] Chrome: All features work
- [ ] Firefox: All features work
- [ ] Safari: All features work
- [ ] Mobile browsers: Responsive layout works

### Mobile Responsiveness (Breakpoints)
- [ ] Desktop (>1200px): Full layout
- [ ] Large Tablet (1024-1200px): Adapted layout
- [ ] Tablet (768-1024px): Collapsed sidebar, single column
- [ ] Mobile (480-768px): Full mobile layout
- [ ] Small Mobile (≤480px): Optimized mobile layout

---

## 🐛 BUG REPORT TEMPLATE

**Bug #:** [Number]
**Page:** [Menu name]
**Severity:** CRITICAL | HIGH | MEDIUM | LOW
**Title:** [Short description]

**Steps to Reproduce:**
1. ...
2. ...
3. ...

**Expected Result:**
...

**Actual Result:**
...

**Screenshots/Details:**
...

---

## ✅ SUMMARY

**Total Tests:** 120+
**Passed:** ___ / 120
**Failed:** ___ / 120
**Bugs Found:** ___
**Critical:** ___
**High:** ___
**Medium:** ___
**Low:** ___

**Test Date:** 
**Tested By:** 
**Status:** [ ] PASSED [ ] FAILED

---

## 📝 NOTES & OBSERVATIONS

[Add any observations, improvements, or future recommendations here]

