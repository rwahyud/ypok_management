# 📝 Template Surat Resmi YPOK

Template surat resmi dengan kop surat lengkap (logo YPOK & KORMI), nomor surat otomatis, dan konten dinamis.

## 🎯 Fitur

✅ Kop surat profesional dengan 2 logo  
✅ Nomor surat otomatis  
✅ Format sesuai standar surat resmi Indonesia  
✅ Support untuk undangan kegiatan otomatis  
✅ Print-friendly (siap cetak PDF)  
✅ Konten dinamis via URL parameter  

---

## 📖 Cara Penggunaan

### 1️⃣ **Surat Manual (Custom Content)**

Buka URL dengan parameter:

```
http://localhost/ypok_management/generate_surat.php?
hal=Undangan Rapat&
tujuan=Ketua Dojo se-Jakarta&
isi=<p>Dengan hormat,</p><p>Kami mengundang...</p>&
ttd_nama=Aldina Olii&
ttd_jabatan=Ketua Umum YPOK
```

**Parameter yang tersedia:**

| Parameter | Deskripsi | Default | Contoh |
|-----------|-----------|---------|--------|
| `nomor` | Nomor surat | Auto (01/YPOK-PP/VI/2026) | `021/YPOK-PP/VI/2025` |
| `lampiran` | Lampiran | `-` | `1 Berkas`, `2 Lembar` |
| `hal` | Perihal/Subjek | `Surat Kegiatan` | `Undangan Rapat` |
| `tujuan` | Penerima surat | `Ketua/Pengurus Panitia` | `Ketua Dojo Jakarta` |
| `organisasi` | Nama organisasi | - | `FORNAS VIII NTB` |
| `tanggal` | Tanggal surat | Hari ini | `30 Juni 2025` |
| `isi` | Konten surat (HTML) | - | `<p>Dengan hormat...</p>` |
| `ttd_nama` | Nama penandatangan | `Ketua Umum YPOK` | `Aldina Olii` |
| `ttd_jabatan` | Jabatan TTD | `Ketua Umum` | `Ketua Umum YPOK` |

---

### 2️⃣ **Surat Undangan Kegiatan Otomatis**

Gunakan parameter `kegiatan_id` untuk generate undangan dari data kegiatan:

```
http://localhost/ypok_management/generate_surat.php?kegiatan_id=1
```

Sistem akan otomatis:
- ✅ Mengambil data kegiatan dari database
- ✅ Generate isi undangan lengkap (hari, tanggal, waktu, tempat)
- ✅ Set perihal menjadi "Undangan [Jenis Kegiatan]"

---

## 💡 Contoh Penggunaan

### **Contoh 1: Surat Undangan Rapat**

```
generate_surat.php?
nomor=015/YPOK-PP/III/2026&
hal=Undangan Rapat Koordinasi&
tujuan=Ketua Dojo se-Jabodetabek&
tanggal=15 Maret 2026&
isi=<p>Dengan hormat,</p><p>Sehubungan dengan akan dilaksanakannya Rapat Koordinasi...</p>&
ttd_nama=Aldina Olii&
ttd_jabatan=Ketua Umum YPOK
```

### **Contoh 2: Surat Pemberitahuan**

```
generate_surat.php?
nomor=020/YPOK-PP/III/2026&
lampiran=1 Berkas&
hal=Pemberitahuan Perubahan Jadwal&
tujuan=Seluruh Anggota YPOK&
isi=<p>Dengan hormat,</p><p>Bersama ini kami sampaikan perubahan jadwal...</p>
```

### **Contoh 3: Undangan dari Data Kegiatan**

```
generate_surat.php?kegiatan_id=5&ttd_nama=Aldina Olii
```

---

## 🖨️ Cara Print/Export PDF

1. Buka surat di browser
2. Klik tombol **"🖨️ Print / Simpan PDF"**
3. Pada dialog Print:
   - **Destination**: Save as PDF (Chrome) / Microsoft Print to PDF (Edge)
   - **Layout**: Portrait
   - **Paper size**: A4
   - **Margins**: Default
4. Klik **Save**

Atau tambahkan `&print=1` di URL untuk auto-print:
```
generate_surat.php?kegiatan_id=1&print=1
```

---

## 🎨 Kustomisasi

### **Mengubah Logo**

Edit file `generate_surat.php` baris 164 & 177:

```php
<!-- Logo Kiri (YPOK) -->
<img src="assets/images/logo ypok .jpg" alt="Logo YPOK">

<!-- Logo Kanan (KORMI) -->
<img src="uploads/msh/1772373554_ypok kormi .jpg" alt="Logo KORMI">
```

### **Mengubah Alamat Kantor**

Edit baris 172-176 di `generate_surat.php`:

```php
<div class="letterhead-address">
    Menara Cakrawala - Sky Building Lt 12, Unit 05A<br>
    Jl. M.H. Thamrin No.9, Jakarta Pusat 10340<br>
    email@ypok.com | 0851-7313-2266
</div>
```

---

## 🔗 Integrasi dengan Sistem

### **Tombol "Cetak Undangan" di Laporan Kegiatan**

Tambahkan button di `laporan_kegiatan.php`:

```php
<a href="generate_surat.php?kegiatan_id=<?php echo $kegiatan['id']; ?>" 
   target="_blank" 
   class="btn btn-primary">
    📄 Cetak Undangan
</a>
```

### **Generate via JavaScript**

```javascript
function cetakUndangan(kegiatanId) {
    const url = `generate_surat.php?kegiatan_id=${kegiatanId}&print=1`;
    window.open(url, '_blank', 'width=800,height=600');
}
```

---

## 📋 Format Isi Surat (HTML)

Gunakan HTML sederhana untuk styling:

```html
<p>Dengan hormat,</p>

<p>Sehubungan dengan akan dilaksanakannya kegiatan ujian kenaikan tingkat, 
maka kami mengundang Bapak/Ibu untuk hadir pada:</p>

<table style='margin-left: 40px; margin-top: 20px;'>
    <tr>
        <td style='width: 150px;'>Hari/Tanggal</td>
        <td>:</td>
        <td>Kamis, 03 April 2026</td>
    </tr>
    <tr>
        <td>Waktu</td>
        <td>:</td>
        <td>09:00 WIB - Selesai</td>
    </tr>
    <tr>
        <td>Tempat</td>
        <td>:</td>
        <td>Gedung Serbaguna Medan</td>
    </tr>
</table>

<p>Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadiran 
Bapak/Ibu, kami ucapkan terima kasih.</p>
```

---

## ⚠️ Tips

- ✅ Gunakan `kegiatan_id` untuk undangan kegiatan (otomatis)
- ✅ Encode URL parameters dengan `urlencode()` di PHP
- ✅ Untuk isi surat panjang, simpan template di file terpisah
- ✅ Preview dulu sebelum print untuk cek format
- ✅ Pastikan logo tersedia di folder yang benar

---

## 🎉 Hasil

Output surat akan memiliki:

```
┌─────────────────────────────────────────────┐
│  [LOGO YPOK]  YAYASAN PENDIDIKAN    [KORMI] │
│            OLAHRAGA KARATE                   │
│           PENGURUS PUSAT                     │
│        [Alamat lengkap, email, HP]          │
├─────────────────────────────────────────────┤
│ Nomor      : 021/YPOK-PP/VI/2025            │
│ Lampiran   : -                               │
│ Hal        : Surat Mandate Wasit             │
│                                              │
│                        Jakarta, 30 Juni 2025 │
│                                              │
│ Kepada Yth,                                  │
│ Ketua/Pengurus Panitia Pelaksana            │
│ FORNAS VIII NTB                             │
│                                              │
│ Yang bertanda tangan di bawah ini:          │
│ Nama: Aldina Olii                           │
│ Jabatan: Ketua Umum YPOK                    │
│                                              │
│ [Isi surat...]                              │
│                                              │
│                        Ketua Umum YPOK,     │
│                                              │
│                        [TTD Space]          │
│                                              │
│                        Aldina Olii          │
│                        Ketua Umum YPOK      │
└─────────────────────────────────────────────┘
```

---

## 📞 Support

Jika ada pertanyaan atau butuh kustomisasi lebih lanjut, silakan hubungi developer! 🚀
