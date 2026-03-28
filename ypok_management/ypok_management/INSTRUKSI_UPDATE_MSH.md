# 📋 INSTRUKSI UPDATE DATABASE MSH

## ⚠️ PENTING - Baca Sebelum Menggunakan Fitur MSH Baru!

Telah ditambahkan 2 field baru pada tabel Data MSH:
1. **Tanggal Ujian** - Tanggal ujian kenaikan dan
2. **Jenis Keanggotaan** - Reguler, Khusus, atau Kehormatan

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
3. Pilih file: `database/update_msh_fields.sql`
4. Klik **Go**

### Langkah 4: Verifikasi
Setelah import berhasil, cek tabel `master_sabuk_hitam`:
- Harus ada kolom: `tanggal_ujian`
- Harus ada kolom: `jenis_keanggotaan`

---

## 📊 Format Data MSH Baru

### Tabel dengan kolom:
1. **NO** - Nomor urut
2. **NO.MSH** - Nomor MSH (contoh: 0001/X/2023)
3. **NAMA** - Nama lengkap
4. **TEMPAT, TGL LAHIR** - Jakarta, 28-08-1965
5. **NOMOR IJAZAH, TINGKATAN DAN TANGGAL UJIAN** - DAN X, 0001/X/2023
6. **ASAL PROVINSI / KAB/ KOTA** - DKI Jakarta
7. **ALAMAT** - Alamat lengkap
8. **JENIS DAN** - Reguler / Khusus / Kehormatan
9. **KET** - AKTIF / NON-AKTIF / MENINGGAL

### Contoh Data:
```
NO: 1
NO.MSH: 0001/X/2023
NAMA: IDRIS OLII
TEMPAT, TGL LAHIR: Manado, 28-08-1965
IJAZAH & TINGKATAN: DAN X, 0001/X/2023, 11-Maret-2023
ASAL: DKI Jakarta
ALAMAT: Jl. Proklamasi No.31 pegangsaan menteng, jakarta pusat
JENIS: Reguler
KET: AKTIF
```

---

## ✅ Setelah Update Database

### Test Fitur:
1. **Buka halaman MSH:**
   ```
   http://localhost/ypok_management/pages/msh.php
   ```

2. **Tambah Data Baru:**
   - Klik tombol "➕ Tambah Data"
   - Isi semua field termasuk:
     - Tanggal Ujian
     - Jenis Keanggotaan (pilih: Reguler/Khusus/Kehormatan)
   - Simpan

3. **Lihat Tabel:**
   - Pastikan kolom ditampilkan sesuai format baru
   - Data "JENIS DAN" muncul dengan badge biru

4. **Edit Data:**
   - Klik tombol Edit (✏️)
   - Field Tanggal Ujian dan Jenis Keanggotaan harus muncul
   - Update data dan simpan

---

## 🔍 Troubleshooting

### Error: "Unknown column 'tanggal_ujian'"
**Solusi:** Database belum diupdate. Import file `update_msh_fields.sql`

### Error: "Unknown column 'jenis_keanggotaan'"
**Solusi:** Database belum diupdate. Import file `update_msh_fields.sql`

### Field tidak muncul di form
**Solusi:** Clear cache browser (Ctrl + Shift + R) dan reload halaman

### Data lama tidak tampil
**Solusi:** Data lama akan menggunakan default value:
- Tanggal Ujian: NULL (kosong)
- Jenis Keanggotaan: "Reguler"

Edit data lama untuk mengisi field yang baru.

---

## 📝 Field Mapping Database

| Field Database | Tipe Data | Default | Keterangan |
|----------------|-----------|---------|------------|
| `tanggal_ujian` | DATE | NULL | Tanggal ujian kenaikan dan |
| `jenis_keanggotaan` | VARCHAR(50) | 'Reguler' | Jenis: Reguler, Khusus, Kehormatan |

---

## 🎯 Checklist Update

- [ ] Import `update_msh_fields.sql` ke phpMyAdmin
- [ ] Refresh halaman msh.php
- [ ] Test tambah data baru
- [ ] Test edit data lama
- [ ] Verifikasi tampilan tabel sesuai format
- [ ] Update data lama dengan informasi lengkap

---

**Dibuat:** 2026-03-04  
**File SQL:** `database/update_msh_fields.sql`  
**Halaman:** `pages/msh.php`
