# 🥋 Integrasi MSH: Dashboard Admin ↔️ Guest Dashboard

## 📋 Ringkasan Sistem

Sistem ini mengintegrasikan data **Master Sabuk Hitam (MSH)** antara:
1. **Dashboard Admin** (memerlukan login) - untuk CRUD data
2. **Guest Dashboard** (publik, tanpa login) - untuk melihat dan mencari data

---

## 🔄 Cara Kerja Integrasi

### 1. Dashboard Admin (`msh.php`)
- ✅ Menampilkan **SEMUA** data MSH (aktif & non-aktif)
- ✅ Fitur: Tambah, Edit, Hapus, Search
- ✅ Akses: Memerlukan login
- ✅ Query: Mengambil dari tabel `master_sabuk_hitam` tanpa filter status

### 2. Guest Dashboard (`guest_dashboard.php`)
- ✅ Menampilkan **HANYA** data MSH dengan `status = 'aktif'`
- ✅ Fitur: Lihat dan Search (read-only)
- ✅ Akses: Publik, tidak perlu login
- ✅ API: Menggunakan `actions/get_msh_public.php`

---

## 🎯 Syarat Data Muncul di Guest Dashboard

Agar data MSH yang ada di Dashboard Admin bisa dicari dan muncul di Guest Dashboard, pastikan:

### ✅ Checklist:
1. [ ] Data MSH sudah ditambahkan melalui Dashboard Admin
2. [ ] **Status** diset ke **'aktif'** (PENTING!)
3. [ ] Data sudah tersimpan di database

---

## 🚀 Langkah-langkah Setup

### Step 1: Verifikasi Data MSH
Buka: `http://localhost/ypok_management/ypok_management/verify_msh_data.php`

File ini akan menampilkan:
- ✅ Status koneksi database
- ✅ Struktur tabel
- ✅ Jumlah data MSH (total & aktif)
- ✅ Daftar semua data MSH
- ✅ Test pencarian "faiz"

**Yang Harus Dicek:**
- Apakah data MSH yang dicari **ada** di database?
- Apakah **status**nya **'aktif'**?
- Jika statusnya bukan 'aktif' → data TIDAK akan muncul di Guest Dashboard

---

### Step 2: Update Status MSH (Jika Diperlukan)
Buka: `http://localhost/ypok_management/ypok_management/update_msh_status.php`

Gunakan file ini untuk:
- ✅ Melihat data MSH yang statusnya bukan 'aktif'
- ✅ Update status menjadi 'aktif' (semua atau pilih tertentu)
- ✅ Otomatis update dengan 1 klik

**Atau Manual via Dashboard Admin:**
1. Login ke Dashboard Admin
2. Buka menu **MSH**
3. Klik **Edit** pada data yang ingin ditampilkan
4. Ubah **Status** → **'aktif'**
5. Klik **Simpan**

---

### Step 3: Test API
Buka: `http://localhost/ypok_management/ypok_management/test_search_simple.html`

Test:
- ✅ Koneksi API
- ✅ Search MSH (contoh: "faiz")
- ✅ Load semua data MSH

---

### Step 4: Test di Guest Dashboard
Buka: `http://localhost/ypok_management/ypok_management/guest_dashboard.php#master`

1. Scroll ke section **"Master Sabuk Hitam"**
2. Gunakan **Search Box** untuk mencari nama MSH
3. Data akan otomatis dimuat

**Debug di Browser:**
- Tekan `F12` untuk buka Console
- Lihat log: "API Response: ..."
- Jika ada error, akan muncul detail error

---

## 📊 Struktur Data

### Tabel: `master_sabuk_hitam`

Kolom yang digunakan di Guest Dashboard:
```sql
- id              : ID unik
- no_msh          : Nomor MSH
- nama            : Nama lengkap (untuk search)
- tingkat_dan     : Tingkat Dan (untuk search)
- dojo_cabang     : Nama Dojo (untuk search)
- foto            : Path foto
- tanggal_lahir   : Untuk tahun bergabung
- status          : 'aktif' atau 'non-aktif' (FILTER UTAMA!)
```

---

## 🔍 Fitur Search

### Di Guest Dashboard:
- Bisa search berdasarkan:
  - ✅ Nama MSH
  - ✅ Nomor MSH
  - ✅ Dojo/Cabang
  - ✅ Tingkat Dan (contoh: "Dan 5")

- Fitur tambahan:
  - ✅ Real-time search (otomatis saat mengetik)
  - ✅ Pagination (tombol "Muat Lebih Banyak")
  - ✅ Menampilkan jumlah hasil

---

## 🐛 Troubleshooting

### ❌ Problem: "Data tidak ditemukan" padahal ada di database

**Penyebab:**
- Status data bukan 'aktif'
- Ejaan nama tidak cocok
- Data sudah dihapus

**Solusi:**
1. Buka `verify_msh_data.php` → cek apakah data ada
2. Periksa kolom **Status**
3. Jika bukan 'aktif' → gunakan `update_msh_status.php` atau edit manual
4. Refresh Guest Dashboard

---

### ❌ Problem: "Terjadi kesalahan saat memuat data"

**Penyebab:**
- Error di API `get_msh_public.php`
- Kolom database tidak sesuai
- Koneksi database bermasalah

**Solusi:**
1. Buka Console Browser (F12) → lihat error detail
2. Test API langsung: `actions/get_msh_public.php?search=faiz`
3. Cek `verify_msh_data.php` untuk detail error

---

### ❌ Problem: Data muncul di Admin tapi tidak di Guest

**Penyebab:**
- Status bukan 'aktif'

**Solusi:**
1. Edit data di Dashboard Admin
2. Ubah Status → 'aktif'
3. Simpan

---

## 📁 File-file Penting

### Core Files:
- `guest_dashboard.php` - Halaman tamu dengan fitur search MSH
- `actions/get_msh_public.php` - API publik untuk data MSH
- `msh.php` - Dashboard admin untuk kelola MSH

### Helper Files (untuk debugging):
- `verify_msh_data.php` - Verifikasi data di database
- `update_msh_status.php` - Update status MSH
- `test_search_simple.html` - Test interface search
- `test_msh_api.php` - Test API detail

---

## ✅ Checklist Testing

Agar yakin integrasi bekerja:

1. [ ] Tambah data MSH di Dashboard Admin dengan status 'aktif'
2. [ ] Buka `verify_msh_data.php` → pastikan data muncul
3. [ ] Test search di `test_search_simple.html`
4. [ ] Buka Guest Dashboard → search nama MSH
5. [ ] Data harus muncul dengan informasi lengkap

---

## 🎨 Tampilan di Guest Dashboard

Setiap card MSH menampilkan:
- 📸 Foto (jika ada)
- 👤 Nama
- 📛 Nomor MSH
- 🥋 Tingkat Dan
- 📍 Dojo/Cabang
- 📅 Tahun Bergabung

---

## 📞 Support

Jika masih ada masalah:
1. Buka file helper di atas untuk debugging
2. Cek Console browser (F12) untuk error detail
3. Periksa log database

---

**Update Terakhir:** 26 Februari 2026
**Versi:** 2.0
