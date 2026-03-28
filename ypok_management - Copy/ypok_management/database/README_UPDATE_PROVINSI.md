# Update Database Provinsi - Kolom Agregat

## 📋 Ringkasan

Update ini menambahkan kolom agregat ke tabel `provinsi` untuk menyimpan statistik dari semua dojo di setiap provinsi:

- **total_dojo**: Jumlah total dojo di provinsi tersebut
- **total_anggota**: Jumlah total seluruh anggota dari semua dojo
- **anggota_aktif**: Jumlah total anggota aktif dari semua dojo
- **anggota_non_aktif**: Jumlah total anggota non aktif dari semua dojo

## 🎯 Keuntungan

1. **Performa Lebih Cepat**: Data agregat sudah tersimpan di database, tidak perlu menghitung setiap kali ditampilkan
2. **Update Otomatis**: Trigger database memastikan data selalu sinkron ketika ada perubahan pada tabel dojo
3. **Mudah Digunakan**: Cukup query tabel provinsi, data sudah lengkap

## 🚀 Cara Menjalankan

### Opsi 1: Via Browser (Recommended)
1. Buka browser dan akses:
   ```
   http://localhost/ypok_management/ypok_management/database/run_update_provinsi_agregat.php
   ```
2. Script akan otomatis menjalankan semua update dan menampilkan hasilnya
3. Verifikasi data provinsi yang ditampilkan di akhir halaman

### Opsi 2: Via phpMyAdmin
1. Login ke phpMyAdmin
2. Pilih database `ypok_management`
3. Klik tab "SQL"
4. Copy paste isi file `update_provinsi_agregat.sql`
5. Klik "Go" untuk menjalankan

### Opsi 3: Via Command Line
```bash
mysql -u root -p ypok_management < update_provinsi_agregat.sql
```

## 📊 Yang Berubah

### 1. Struktur Tabel Provinsi
```sql
ALTER TABLE provinsi 
ADD COLUMN total_dojo INT DEFAULT 0,
ADD COLUMN total_anggota INT DEFAULT 0,
ADD COLUMN anggota_aktif INT DEFAULT 0,
ADD COLUMN anggota_non_aktif INT DEFAULT 0;
```

### 2. Stored Procedure
Dibuat procedure `update_provinsi_stats(provinsi_id)` untuk menghitung ulang agregat suatu provinsi.

### 3. Trigger Otomatis
- `after_dojo_insert`: Update agregat ketika dojo baru ditambahkan
- `after_dojo_update`: Update agregat ketika data dojo diubah
- `after_dojo_delete`: Update agregat ketika dojo dihapus

### 4. File PHP yang Berubah
- `lokasi.php`: Sekarang menggunakan data dari kolom `total_dojo`, `total_anggota`, dll langsung dari tabel provinsi, bukan menghitung dari dojo

## 🔄 Cara Kerja Trigger

### INSERT Dojo Baru
```
Tambah Dojo → Trigger after_dojo_insert → Update Agregat Provinsi
```

### UPDATE Dojo
```
Edit Dojo → Trigger after_dojo_update → Update Agregat Provinsi Lama & Baru
```

### DELETE Dojo
```
Hapus Dojo → Trigger after_dojo_delete → Update Agregat Provinsi
```

## 🧪 Testing

Setelah menjalankan update, lakukan testing:

1. **Cek data provinsi**:
   ```sql
   SELECT * FROM provinsi;
   ```
   Pastikan kolom agregat sudah terisi dengan benar.

2. **Test INSERT dojo**:
   - Tambah dojo baru via form
   - Cek apakah total_dojo di provinsi bertambah

3. **Test UPDATE dojo**:
   - Edit data anggota di dojo
   - Cek apakah total_anggota, anggota_aktif, anggota_non_aktif di provinsi ikut berubah

4. **Test DELETE dojo**:
   - Hapus dojo
   - Cek apakah agregat provinsi berkurang

## 🔍 Verifikasi Manual

Jika ingin memverifikasi data secara manual:

```sql
-- Hitung agregat secara manual untuk provinsi tertentu
SELECT 
    p.id,
    p.nama_provinsi,
    p.total_dojo AS total_dojo_tersimpan,
    COUNT(d.id) AS total_dojo_sebenarnya,
    p.total_anggota AS total_anggota_tersimpan,
    COALESCE(SUM(d.total_anggota), 0) AS total_anggota_sebenarnya,
    p.anggota_aktif AS anggota_aktif_tersimpan,
    COALESCE(SUM(d.anggota_aktif), 0) AS anggota_aktif_sebenarnya,
    p.anggota_non_aktif AS anggota_non_aktif_tersimpan,
    COALESCE(SUM(d.anggota_non_aktif), 0) AS anggota_non_aktif_sebenarnya
FROM provinsi p
LEFT JOIN dojo d ON d.provinsi_id = p.id
GROUP BY p.id;
```

Jika ada perbedaan, jalankan stored procedure untuk update:
```sql
CALL update_provinsi_stats(provinsi_id);
```

## 📝 Catatan Penting

1. **Backup Database**: Selalu backup database sebelum menjalankan update
2. **Jalankan Sekali Saja**: Script ini cukup dijalankan satu kali
3. **Data Otomatis Sinkron**: Setelah trigger dipasang, data akan otomatis terupdate
4. **Tidak Perlu Edit Manual**: Kolom agregat akan diupdate otomatis oleh trigger, tidak perlu edit manual

## 🐛 Troubleshooting

### Error: Column already exists
Artinya kolom sudah ditambahkan sebelumnya. Ini normal jika menjalankan script lebih dari sekali.

### Trigger Error
Jika terjadi error pada trigger, cek apakah tabel dojo memiliki kolom yang diperlukan:
- provinsi_id
- total_anggota
- anggota_aktif
- anggota_non_aktif

### Data tidak sinkron
Jalankan stored procedure untuk recalculate:
```sql
-- Update semua provinsi
CALL update_provinsi_stats(1);  -- ganti 1 dengan ID provinsi
```

## 📞 Support

Jika ada masalah atau pertanyaan, silakan hubungi tim developer.

---
**Tanggal Dibuat**: 21 Februari 2026  
**Version**: 1.0
