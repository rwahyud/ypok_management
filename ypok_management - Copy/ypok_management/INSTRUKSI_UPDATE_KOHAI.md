# 📋 INSTRUKSI UPDATE DATABASE KOHAI

## ⚠️ PENTING - Baca Sebelum Menggunakan Fitur Kohai Baru!

Telah ditambahkan beberapa field baru pada tabel Data Kohai untuk mengikuti format standar:
1. **No. Registrasi Ijazah** - Nomor registrasi (bisa lebih dari satu, pisahkan dengan spasi)
2. **Tanggal Ujian** - Tanggal ujian terakhir
3. **Asal Sekolah/Ranting** - Cabang atau asal sekolah
4. **Asal Provinsi / Kab/ Kota** - Lokasi asal (Provinsi, Kabupaten, Kota)
5. **Keterangan** - Keterangan tambahan

---

## 🔧 Cara Update Database

### Langkah 1: Buka phpMyAdmin
```
http://localhost/phpmyadmin
```

### Langkah 2: Pilih Database
- Klik database: `ypok_management`

### Langkah 3: Import File SQL
1. Klik tab **Import**
2. Klik **Choose File**
3. Pilih file: `database/update_kohai_fields.sql`
4. Klik **Go**

### Langkah 4: Verifikasi
Setelah import berhasil, cek tabel `kohai`. Harus ada kolom:
- `no_registrasi_ijazah`
- `tanggal_ujian`
- `asal_sekolah`
- `asal_provinsi`
- `keterangan`

---

## 📊 Format Data Kohai Baru

### Tabel dengan kolom:
1. **NO** - Nomor urut
2. **NO. REGISTRASI IJAZAH** - Nomor registrasi (contoh: YPOK.H-I-23-0001 YPOK.B-II-23-0001)
3. **NAMA** - Nama lengkap
4. **TEMPAT, TGL LAHIR** - Format: JAKARTA,26-04-2012
5. **WARNA SABUK** - Format: 5 BIRU (Kyu + Warna)
6. **TANGGAL UJIAN, KYU, DAN WARNA SABUK** - Tanggal ujian terakhir
7. **CABANG/ ASAL SEKOLAH RANTING** - Asal sekolah/cabang
8. **ASAL PROVINSI / KAB/ KOTA** - Lokasi: JAKARTA PUSAT, DKI JAKARTA
9. **KETERANGAN** - Keterangan tambahan (opsional)
10. **STATUS ANGGOTA** - AKTIF / NON-AKTIF / MENINGGAL

### Contoh Data:
```
NO: 1
NO. REGISTRASI: YPOK.H-I-23-0001 YPOK.B-II-23-0001
NAMA: ARYA ILLIYANSYAH
TEMPAT, TGL LAHIR: JAKARTA,26-04-2012
WARNA SABUK: 5 BIRU
TANGGAL UJIAN: 24-09-2023
CABANG/ASAL SEKOLAH: SDN GONDANGDIA 01
ASAL PROVINSI: JAKARTA PUSAT, DKI JAKARTA
KETERANGAN: (kosong)
STATUS: AKTIF
```

---

## ✅ Setelah Update Database

### Test Fitur:
1. **Buka halaman Kohai:**
   ```
   http://localhost/ypok_management/pages/kohai.php
   ```

2. **Tambah Data Baru:**
   - Klik tombol "➕ Tambah Data"
   - Isi field baru:
     - No. Registrasi Ijazah (opsional, bisa lebih dari satu)
     - Tanggal Ujian
     - Asal Sekolah/Ranting
     - Asal Provinsi / Kab/ Kota
     - Keterangan (opsional)
   - Simpan

3. **Lihat Tabel:**
   - Pastikan kolom ditampilkan sesuai format baru
   - Format tempat tanggal lahir: JAKARTA,26-04-2012
   - Warna sabuk: 5 BIRU (Kyu + Warna)
   - Status ditampilkan dengan uppercase (AKTIF)

4. **Edit Data:**
   - Klik tombol Edit (✏️)
   - Field baru harus muncul
   - Update data dan simpan

---

## 🔍 Troubleshooting

### Error: "Unknown column 'no_registrasi_ijazah'"
**Solusi:** Database belum diupdate. Import file `update_kohai_fields.sql`

### Error: "Unknown column 'tanggal_ujian'"
**Solusi:** Database belum diupdate. Import file `update_kohai_fields.sql`

### Error: "Unknown column 'asal_sekolah'"
**Solusi:** Database belum diupdate. Import file `update_kohai_fields.sql`

### Error: "Unknown column 'asal_provinsi'"
**Solusi:** Database belum diupdate. Import file `update_kohai_fields.sql`

### Error: "Unknown column 'keterangan'"
**Solusi:** Database belum diupdate. Import file `update_kohai_fields.sql`

### Field tidak muncul di form
**Solusi:** Clear cache browser (Ctrl + Shift + R) dan reload halaman

### Data lama tidak tampil
**Solusi:** Data lama akan menggunakan field lama. Edit data lama untuk mengisi field yang baru.

---

## 📝 Field Mapping Database

| Field Database | Tipe Data | Default | Keterangan |
|----------------|-----------|---------|------------|
| `no_registrasi_ijazah` | VARCHAR(255) | NULL | Nomor registrasi ijazah (bisa multiple) |
| `tanggal_ujian` | DATE | NULL | Tanggal ujian terakhir |
| `asal_sekolah` | VARCHAR(255) | NULL | Cabang/Asal sekolah/Ranting |
| `asal_provinsi` | VARCHAR(255) | NULL | Asal provinsi/kab/kota |
| `keterangan` | TEXT | NULL | Keterangan tambahan |

---

## 🎯 Checklist Update

- [ ] Import `update_kohai_fields.sql` ke phpMyAdmin
- [ ] Refresh halaman kohai.php
- [ ] Test tambah data baru
- [ ] Test edit data lama
- [ ] Verifikasi tampilan tabel sesuai format:
  - NO (urut)
  - NO. REGISTRASI IJAZAH
  - NAMA
  - TEMPAT, TGL LAHIR (format: JAKARTA,26-04-2012)
  - WARNA SABUK (format: 5 BIRU)
  - TANGGAL UJIAN
  - CABANG/ ASAL SEKOLAH RANTING
  - ASAL PROVINSI / KAB/ KOTA
  - KETERANGAN
  - STATUS ANGGOTA (AKTIF/NON-AKTIF)
- [ ] Update data lama dengan informasi lengkap

---

## 💡 Tips Pengisian Data

### No. Registrasi Ijazah
Jika ada lebih dari satu nomor, pisahkan dengan spasi:
```
YPOK.H-I-23-0001 YPOK.B-II-23-0001
```

### Tempat, Tgl Lahir
Akan ditampilkan dalam format uppercase dengan format tanggal dd-mm-yyyy:
```
JAKARTA,26-04-2012
```

### Warna Sabuk
Ditampilkan dengan format: Kyu + Warna Sabuk
```
5 BIRU
Kyu 5 → 5 BIRU
Kyu 7 → 7 HIJAU
```

### Asal Provinsi / Kab/ Kota
Gunakan format:
```
JAKARTA PUSAT, DKI JAKARTA
BANDUNG, JAWA BARAT
```

---

**Dibuat:** 2026-03-04  
**File SQL:** `database/update_kohai_fields.sql`  
**Halaman:** `pages/kohai.php`
