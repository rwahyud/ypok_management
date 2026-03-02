<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Surat YPOK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #1e3a8a;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        label .required {
            color: #ef4444;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            min-height: 200px;
            resize: vertical;
            font-family: inherit;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            justify-content: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .help-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .examples {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .examples h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
        }
        
        .example-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 6px;
            margin: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        
        .example-btn:hover {
            background: #c7d2fe;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Generator Surat YPOK</h1>
        <p class="subtitle">Template Surat Resmi dengan Kop Surat Lengkap</p>
        
        <div class="alert alert-info">
            💡 <strong>Tips:</strong> Isi form di bawah ini, lalu klik "Generate Surat". Atau pilih salah satu contoh cepat untuk melihat preview.
        </div>
        
        <form id="suratForm" action="generate_surat.php" method="GET" target="_blank">
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Surat</label>
                    <input type="text" name="nomor" placeholder="Otomatis jika kosong" value="">
                    <div class="help-text">Contoh: 021/YPOK-PP/VI/2025</div>
                </div>
                
                <div class="form-group">
                    <label>Lampiran</label>
                    <input type="text" name="lampiran" placeholder="-" value="-">
                    <div class="help-text">Contoh: 1 Berkas, 2 Lembar</div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Perihal / Hal <span class="required">*</span></label>
                <input type="text" name="hal" placeholder="Surat Mandate Wasit FORNAS VIII NTB" required>
                <div class="help-text">Subjek/judul surat</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Kepada Yth <span class="required">*</span></label>
                    <input type="text" name="tujuan" placeholder="Ketua/Pengurus Panitia Pelaksana" required>
                </div>
                
                <div class="form-group">
                    <label>Organisasi/Instansi</label>
                    <input type="text" name="organisasi" placeholder="FORNAS VIII NTB">
                </div>
            </div>
            
            <div class="form-group">
                <label>Tanggal Surat</label>
                <input type="text" name="tanggal" placeholder="Otomatis: hari ini" value="">
                <div class="help-text">Format: 30 Juni 2025</div>
            </div>
            
            <div class="form-group">
                <label>Isi Surat <span class="required">*</span></label>
                <textarea name="isi" placeholder="Ketik isi surat di sini..." required></textarea>
                <div class="help-text">Gunakan HTML sederhana. Contoh: &lt;p&gt;Paragraf&lt;/p&gt;</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Penandatangan</label>
                    <input type="text" name="ttd_nama" placeholder="Aldina Olii" value="Aldina Olii">
                </div>
                
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="ttd_jabatan" placeholder="Ketua Umum YPOK" value="Ketua Umum YPOK">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                📄 Generate Surat
            </button>
        </form>
        
        <div class="examples">
            <h3>⚡ Contoh Cepat</h3>
            <span class="example-btn" onclick="fillSuratMandate()">📋 Surat Mandate Wasit</span>
            <span class="example-btn" onclick="fillUndanganRapat()">📧 Undangan Rapat</span>
            <span class="example-btn" onclick="fillPemberitahuan()">📢 Pemberitahuan</span>
            <span class="example-btn" onclick="fillUcapanSelamat()">🎉 Ucapan Selamat</span>
        </div>
    </div>
    
    <script>
        function fillSuratMandate() {
            document.querySelector('[name="nomor"]').value = '021/YPOK-PP/VI/2025';
            document.querySelector('[name="lampiran"]').value = '-';
            document.querySelector('[name="hal"]').value = 'Surat Mandate Wasit FORNAS VIII NTB';
            document.querySelector('[name="tujuan"]').value = 'Ketua/Pengurus Panitia Pelaksana';
            document.querySelector('[name="organisasi"]').value = 'FORNAS VIII NTB';
            document.querySelector('[name="tanggal"]').value = '30 Juni 2025';
            document.querySelector('[name="isi"]').value = `<p>Yang bertanda tangan di bawah ini:</p>

<p><strong>Nama:</strong> Aldina Olii<br>
<strong>Jabatan:</strong> Ketua Umum Yayasan Pendidikan Olahraga Karate (YPOK)</p>

<p>Dengan ini menerangkan bahwa:</p>

<table style='margin-left: 40px; margin-top: 15px; margin-bottom: 15px;'>
    <tr>
        <td style='width: 150px; padding: 5px 0;'>Nama</td>
        <td style='width: 20px;'>:</td>
        <td>Muhammad Rizki</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Jabatan</td>
        <td>:</td>
        <td>Wasit Karate YPOK</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Lisensi</td>
        <td>:</td>
        <td>Wasit Nasional Kelas A</td>
    </tr>
</table>

<p>Adalah benar wasit resmi dari YPOK dan telah ditugaskan untuk melaksanakan tugas sebagai wasit pada kegiatan <strong>FORNAS VIII NTB</strong>.</p>

<p>Demikian surat mandate ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>`;
            document.querySelector('[name="ttd_nama"]').value = 'Aldina Olii';
            document.querySelector('[name="ttd_jabatan"]').value = 'Ketua Umum YPOK';
        }
        
        function fillUndanganRapat() {
            document.querySelector('[name="nomor"]').value = '015/YPOK-PP/III/2026';
            document.querySelector('[name="lampiran"]').value = '1 Berkas';
            document.querySelector('[name="hal"]').value = 'Undangan Rapat Koordinasi';
            document.querySelector('[name="tujuan"]').value = 'Ketua Dojo se-Jabodetabek';
            document.querySelector('[name="organisasi"]').value = '';
            document.querySelector('[name="tanggal"]').value = '';
            document.querySelector('[name="isi"]').value = `<p>Dengan hormat,</p>

<p>Sehubungan dengan akan dilaksanakannya Rapat Koordinasi Pengurus YPOK, maka kami mengundang Bapak/Ibu untuk hadir pada:</p>

<table style='margin-left: 40px; margin-top: 20px; margin-bottom: 20px;'>
    <tr>
        <td style='width: 150px; padding: 5px 0;'>Hari/Tanggal</td>
        <td style='width: 20px;'>:</td>
        <td>Sabtu, 15 Maret 2026</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Waktu</td>
        <td>:</td>
        <td>09:00 WIB - Selesai</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Tempat</td>
        <td>:</td>
        <td>Kantor Pusat YPOK, Jakarta</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Acara</td>
        <td>:</td>
        <td>Rapat Koordinasi Program Kerja 2026</td>
    </tr>
</table>

<p>Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.</p>`;
        }
        
        function fillPemberitahuan() {
            document.querySelector('[name="nomor"]').value = '020/YPOK-PP/III/2026';
            document.querySelector('[name="lampiran"]').value = '-';
            document.querySelector('[name="hal"]').value = 'Pemberitahuan Perubahan Jadwal Latihan';
            document.querySelector('[name="tujuan"]').value = 'Seluruh Anggota Dojo YPOK';
            document.querySelector('[name="organisasi"]').value = '';
            document.querySelector('[name="tanggal"]').value = '';
            document.querySelector('[name="isi"]').value = `<p>Dengan hormat,</p>

<p>Bersama ini kami sampaikan pemberitahuan mengenai perubahan jadwal latihan rutin sebagai berikut:</p>

<p><strong>Jadwal Lama:</strong></p>
<table style='margin-left: 40px; margin-bottom: 15px;'>
    <tr>
        <td style='width: 150px; padding: 5px 0;'>Hari</td>
        <td style='width: 20px;'>:</td>
        <td>Senin & Kamis</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Waktu</td>
        <td>:</td>
        <td>17:00 - 19:00 WIB</td>
    </tr>
</table>

<p><strong>Jadwal Baru (mulai 1 April 2026):</strong></p>
<table style='margin-left: 40px; margin-bottom: 15px;'>
    <tr>
        <td style='width: 150px; padding: 5px 0;'>Hari</td>
        <td style='width: 20px;'>:</td>
        <td>Selasa & Jumat</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Waktu</td>
        <td>:</td>
        <td>18:00 - 20:00 WIB</td>
    </tr>
</table>

<p>Demikian pemberitahuan ini kami sampaikan. Mohon untuk dapat diperhatikan dan dipatuhi.</p>`;
        }
        
        function fillUcapanSelamat() {
            document.querySelector('[name="nomor"]').value = '025/YPOK-PP/III/2026';
            document.querySelector('[name="lampiran"]').value = '-';
            document.querySelector('[name="hal"]').value = 'Ucapan Selamat Atas Prestasi';
            document.querySelector('[name="tujuan"]').value = 'Sdr. Ahmad Fadli';
            document.querySelector('[name="organisasi"]').value = 'Dojo Medan';
            document.querySelector('[name="tanggal"]').value = '';
            document.querySelector('[name="isi"]').value = `<p>Dengan penuh kebanggaan,</p>

<p>Kami Pengurus Pusat Yayasan Pendidikan Olahraga Karate (YPOK) mengucapkan selamat atas prestasi gemilang yang telah diraih oleh Saudara di ajang <strong>Kejuaraan Karate Nasional 2026</strong> dengan meraih:</p>

<table style='margin-left: 40px; margin-top: 20px; margin-bottom: 20px;'>
    <tr>
        <td style='width: 150px; padding: 5px 0;'>Medali Emas</td>
        <td style='width: 20px;'>:</td>
        <td>Kategori Kumite Putra</td>
    </tr>
    <tr>
        <td style='padding: 5px 0;'>Medali Perak</td>
        <td>:</td>
        <td>Kategori Kata Perseorangan</td>
    </tr>
</table>

<p>Prestasi ini merupakan kebanggaan bagi YPOK dan memotivasi atlet-atlet lain untuk terus berlatih dan berprestasi.</p>

<p>Semoga prestasi ini menjadi awal dari kesuksesan yang lebih besar di masa depan. Teruslah berlatih dan berprestasi!</p>`;
        }
    </script>
</body>
</html>
