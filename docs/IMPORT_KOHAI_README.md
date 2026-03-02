# UPDATE KOHAI TABLE & IMPORT CSV

## 📋 Yang Sudah Dibuat

### 1. **Update Schema Database** ✅
File: `database/supabase_schema_complete.sql`
- ✅ Ditambahkan field `tanggal_ujian DATE`
- ✅ Ditambahkan field `nomor_ijazah TEXT`
- ✅ Ditambahkan field `keterangan TEXT`

### 2. **SQL Update Script** ✅
File: `database/update_kohai_table.sql`

Script untuk ALTER existing table (jika table sudah ada):
```sql
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS tanggal_ujian DATE;
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS nomor_ijazah TEXT;
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS keterangan TEXT;
```

### 3. **Tool Import CSV** ✅
File: `import_kohai_csv.php`

Tool lengkap untuk import data Kohai dari CSV dengan fitur:
- ✅ Auto-parse tanggal Indonesia (01-MEI-2023 → 2023-05-01)
- ✅ Auto-detect gender dari nama
- ✅ Extract kode kohai pertama dari multiple codes
- ✅ Parse sabuk dan tingkat kyu otomatis
- ✅ Skip data duplikat berdasarkan kode_kohai
- ✅ Transaction safety (rollback jika error)
- ✅ Standardisasi status (AKTIF → Aktif)

## 🔄 Mapping CSV → Database

| Kolom CSV | Field Database | Proses |
|-----------|---------------|---------|
| NO. REGISTRASI IJAZAH | kode_kohai + nomor_ijazah | Kode pertama → kode_kohai, semua → nomor_ijazah |
| NAMA | nama + jenis_kelamin | Auto-detect gender |
| TEMPAT, TGL LAHIR | tempat_lahir + tanggal_lahir | Split & parse |
| WARNA SABUK | sabuk + tingkat_kyu | Extract angka & warna |
| TANGGAL UJIAN, KYU, DAN WARNA SABUK | tanggal_ujian | Extract tanggal |
| CABANG/ASAL SEKOLAH | dojo_cabang | Direct |
| ASAL PROVINSI/KAB/KOTA | alamat | Direct |
| KETERANGAN | keterangan | Direct |
| STATUS ANGGOTA | status | Standardize |
| FOTO | foto | Direct |

## 🚀 Cara Penggunaan

### Step 1: Update Database Schema (WAJIB!)

**Jika table sudah ada**, run SQL update:
```bash
# Di Supabase SQL Editor, run:
database/update_kohai_table.sql
```

**Jika fresh install**, schema sudah otomatis include field baru.

### Step 2: Import Data CSV

1. Buka: `http://localhost/ypok_management/import_kohai_csv.php`
2. Lihat mapping table dan info
3. Klik tombol **"🚀 Mulai Import Data Kohai"**
4. Tunggu proses selesai
5. Lihat hasil: berapa data sukses, skip, error

### Step 3: Verifikasi Data

1. Buka: `http://localhost/ypok_management/kohai.php`
2. Cek data yang sudah di-import
3. Pastikan field baru terisi (nomor_ijazah, tanggal_ujian, keterangan)

## 📊 Data CSV

**File**: `googlesheet/NO.REGISTRASI IJAZAH KYU YPOK - NO.REG IJAZAH.csv`

**Total Baris**: ~1,852 rows
**Format**: 
- Baris 1-3: Empty (di-skip)
- Baris 4: Header
- Baris 5: "RANTING" (di-skip)
- Baris 6+: Data aktual

**Contoh Data**:
```
NO. REGISTRASI IJAZAH: YPOK.H-I-23-0001 YPOK.B-II-23-0001
NAMA: ARYA ILLIYANSYAH
TEMPAT, TGL LAHIR: JAKARTA,26-04-2012
WARNA SABUK: 5 BIRU
TANGGAL UJIAN: 24-09-2023
CABANG: SDN GONDANGDIA 01
PROVINSI: JAKARTA PUSAT, DKI JAKARTA
STATUS: AKTIF
```

## ⚠️ Catatan Penting

1. **Duplikasi**: Data dengan `kode_kohai` sama akan di-skip
2. **Gender Detection**: Berdasarkan keyword nama (Dewi, Putri, dll → P, sisanya L)
3. **Parsing Tanggal**: Support berbagai format (DD-MM-YYYY, DD/MM/YYYY, DD-MMM-YYYY)
4. **Transaction**: Jika ada error di tengah, semua rollback (data consistency)
5. **Empty Fields**: Field kosong di-set NULL, kecuali field required

## 🐛 Troubleshooting

**Error: "column does not exist"**
→ Jalankan `database/update_kohai_table.sql` dulu

**Error: "duplicate key value"**
→ Data dengan kode_kohai sama sudah ada, akan di-skip otomatis

**Tanggal tidak ter-parse**
→ Tool support format: DD-MM-YYYY, DD/MM/YYYY, DD MMM YYYY, DD-MMM-YYYY

**Gender salah terdeteksi**
→ Edit manual di kohai.php setelah import

## ✅ Checklist

- [ ] Run `database/update_kohai_table.sql` di Supabase
- [ ] Buka `import_kohai_csv.php`
- [ ] Klik "Mulai Import"
- [ ] Cek hasil di `kohai.php`
- [ ] Verifikasi data sample
- [ ] Update manual jika perlu

---

**Dibuat**: 2 Maret 2026
**Status**: Ready to Use ✅
