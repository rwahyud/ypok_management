DROP TABLE IF EXISTS sertifikasi_kohai CASCADE;
DROP TABLE IF EXISTS prestasi_kohai CASCADE;
DROP TABLE IF EXISTS pendaftaran_kohai CASCADE;
DROP TABLE IF EXISTS kohai CASCADE;
DROP TABLE IF EXISTS sertifikasi_msh CASCADE;
DROP TABLE IF EXISTS prestasi_msh CASCADE;
DROP TABLE IF EXISTS pendaftaran_msh CASCADE;
DROP TABLE IF EXISTS majelis_sabuk_hitam CASCADE;
DROP TABLE IF EXISTS produk_variasi CASCADE;
DROP TABLE IF EXISTS transaksi_toko CASCADE;
DROP TABLE IF EXISTS produk_toko CASCADE;
DROP TABLE IF EXISTS kategori_produk CASCADE;
DROP TABLE IF EXISTS kegiatan CASCADE;
DROP TABLE IF EXISTS pembayaran CASCADE;
DROP TABLE IF EXISTS lokasi CASCADE;
DROP TABLE IF EXISTS provinsi CASCADE;
DROP TABLE IF EXISTS dojo CASCADE;
DROP TABLE IF EXISTS pengurus CASCADE;
DROP TABLE IF EXISTS legalitas CASCADE;
DROP TABLE IF EXISTS informasi_yayasan CASCADE;
DROP TABLE IF EXISTS transaksi CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Drop all indexes if they exist
DROP INDEX IF EXISTS idx_users_username;
DROP INDEX IF EXISTS idx_users_email;
DROP INDEX IF EXISTS idx_users_role;
DROP INDEX IF EXISTS idx_provinsi_nama;
DROP INDEX IF EXISTS idx_dojo_nama;
DROP INDEX IF EXISTS idx_dojo_provinsi;
DROP INDEX IF EXISTS idx_msh_nama;
DROP INDEX IF EXISTS idx_msh_kode;
DROP INDEX IF EXISTS idx_msh_tingkat;
DROP INDEX IF EXISTS idx_msh_status;
DROP INDEX IF EXISTS idx_prestasi_msh_id;
DROP INDEX IF EXISTS idx_sertifikasi_msh_id;
DROP INDEX IF EXISTS idx_sertifikasi_msh_status;
DROP INDEX IF EXISTS idx_kohai_nama;
DROP INDEX IF EXISTS idx_kohai_kode;
DROP INDEX IF EXISTS idx_kohai_tingkat;
DROP INDEX IF EXISTS idx_kohai_sabuk;
DROP INDEX IF EXISTS idx_kohai_status;
DROP INDEX IF EXISTS idx_prestasi_kohai_id;
DROP INDEX IF EXISTS idx_sertifikasi_kohai_id;
DROP INDEX IF EXISTS idx_sertifikasi_kohai_status;
DROP INDEX IF EXISTS idx_kegiatan_tanggal;
DROP INDEX IF EXISTS idx_pembayaran_kohai;
DROP INDEX IF EXISTS idx_pembayaran_status;
DROP INDEX IF EXISTS idx_produk_kode;
DROP INDEX IF EXISTS idx_produk_nama;
DROP INDEX IF EXISTS idx_produk_variasi_produk;
DROP INDEX IF EXISTS idx_transaksi_toko_id;
DROP INDEX IF EXISTS idx_transaksi_toko_tanggal;
DROP INDEX IF EXISTS idx_transaksi_tanggal;
DROP INDEX IF EXISTS idx_transaksi_jenis;


CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";


CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user', 'msh', 'kohai')),
    foto_profil VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    last_login TIMESTAMP,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

INSERT INTO users (username, password, nama_lengkap, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator YPOK', 'admin@ypok.com', 'admin', 'active');


CREATE TABLE informasi_yayasan (
    id SERIAL PRIMARY KEY,
    nama_lengkap VARCHAR(255) NOT NULL,
    nama_singkat VARCHAR(100),
    tanggal_berdiri DATE,
    status_hukum VARCHAR(100),
    alamat TEXT,
    email VARCHAR(100),
    telepon VARCHAR(50),
    website VARCHAR(255),
    visi TEXT,
    misi TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE provinsi (
    id SERIAL PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL,
    kode_provinsi VARCHAR(10) UNIQUE,
    total_dojo INTEGER DEFAULT 0,
    total_msh INTEGER DEFAULT 0,
    total_kohai INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_provinsi_nama ON provinsi(nama_provinsi);


CREATE TABLE dojo (
    id SERIAL PRIMARY KEY,
    kode_dojo VARCHAR(50) UNIQUE NOT NULL,
    nama_dojo VARCHAR(255) NOT NULL,
    provinsi_id INTEGER REFERENCES provinsi(id) ON DELETE SET NULL,
    kota VARCHAR(100),
    alamat TEXT,
    koordinat VARCHAR(100),
    ketua_dojo VARCHAR(255),
    no_telp VARCHAR(20),
    email VARCHAR(100),
    tahun_berdiri INTEGER,
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'non-aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_dojo_nama ON dojo(nama_dojo);
CREATE INDEX idx_dojo_provinsi ON dojo(provinsi_id);

CREATE TABLE majelis_sabuk_hitam (
    id SERIAL PRIMARY KEY,
    kode_msh VARCHAR(50) UNIQUE,
    nomor_sertifikat VARCHAR(100),
    nama VARCHAR(255) NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(1) CHECK (jenis_kelamin IN ('L', 'P')),
    tingkat_sabuk VARCHAR(50), -- Dan 1-9
    tingkat_dan VARCHAR(20),
    tanggal_lulus DATE,
    nomor_ijazah VARCHAR(100),
    dojo_cabang VARCHAR(100),
    dojo_id INTEGER REFERENCES dojo(id) ON DELETE SET NULL,
    foto VARCHAR(255),
    alamat TEXT,
    no_telp VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'non-aktif', 'meninggal')),
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_msh_nama ON majelis_sabuk_hitam(nama);
CREATE INDEX idx_msh_kode ON majelis_sabuk_hitam(kode_msh);
CREATE INDEX idx_msh_tingkat ON majelis_sabuk_hitam(tingkat_sabuk);
CREATE INDEX idx_msh_status ON majelis_sabuk_hitam(status);

CREATE TABLE prestasi_msh (
    id SERIAL PRIMARY KEY,
    msh_id INTEGER NOT NULL REFERENCES majelis_sabuk_hitam(id) ON DELETE CASCADE,
    nama_prestasi VARCHAR(255) NOT NULL,
    tanggal_prestasi DATE,
    keterangan TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_prestasi_msh_id ON prestasi_msh(msh_id);


CREATE TABLE sertifikasi_msh (
    id SERIAL PRIMARY KEY,
    msh_id INTEGER NOT NULL REFERENCES majelis_sabuk_hitam(id) ON DELETE CASCADE,
    nama_sertifikasi VARCHAR(255),
    nomor_sertifikat VARCHAR(100),
    penerbit VARCHAR(100), -- YPOK, FORKI, dll
    level VARCHAR(50), -- Dan 1-9
    tanggal_terbit DATE,
    tanggal_kadaluarsa DATE,
    status VARCHAR(20) DEFAULT 'valid' CHECK (status IN ('valid', 'expired', 'permanent')),
    file_sertifikat VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sertifikasi_msh_id ON sertifikasi_msh(msh_id);
CREATE INDEX idx_sertifikasi_msh_status ON sertifikasi_msh(status);


CREATE TABLE pendaftaran_msh (
    id SERIAL PRIMARY KEY,
    no_msh VARCHAR(20) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    foto_msh VARCHAR(255),
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin VARCHAR(1) CHECK (jenis_kelamin IN ('L', 'P')),
    tingkat_dan VARCHAR(20) NOT NULL,
    dojo_cabang VARCHAR(50) NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    alamat TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Aktif', 'Tidak Aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE kohai (
    id SERIAL PRIMARY KEY,
    kode_kohai VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(1) CHECK (jenis_kelamin IN ('L', 'P')),
    tingkat_kyu VARCHAR(50), -- Kyu 1-10
    sabuk VARCHAR(50), -- Putih, Kuning, Orange, Hijau, Biru, Coklat
    tingkat_sabuk VARCHAR(50),
    dojo_cabang VARCHAR(100),
    dojo_id INTEGER REFERENCES dojo(id) ON DELETE SET NULL,
    msh_pembimbing INTEGER REFERENCES majelis_sabuk_hitam(id) ON DELETE SET NULL,
    tanggal_bergabung DATE,
    tanggal_ujian DATE, -- Tanggal ujian kenaikan sabuk
    nomor_ijazah TEXT, -- Nomor registrasi ijazah (bisa multiple)
    no_telp VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'Aktif' CHECK (status IN ('Aktif', 'Non-Aktif', 'Meninggal')),
    alamat TEXT,
    keterangan TEXT, -- Keterangan tambahan
    foto VARCHAR(255),
    nama_wali VARCHAR(255),
    no_telp_wali VARCHAR(20),
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_kohai_nama ON kohai(nama);
CREATE INDEX idx_kohai_kode ON kohai(kode_kohai);
CREATE INDEX idx_kohai_tingkat ON kohai(tingkat_kyu);
CREATE INDEX idx_kohai_sabuk ON kohai(sabuk);
CREATE INDEX idx_kohai_status ON kohai(status);


CREATE TABLE prestasi_kohai (
    id SERIAL PRIMARY KEY,
    kohai_id INTEGER NOT NULL REFERENCES kohai(id) ON DELETE CASCADE,
    nama_prestasi VARCHAR(255) NOT NULL,
    tanggal_prestasi DATE,
    keterangan TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_prestasi_kohai_id ON prestasi_kohai(kohai_id);


CREATE TABLE sertifikasi_kohai (
    id SERIAL PRIMARY KEY,
    kohai_id INTEGER NOT NULL REFERENCES kohai(id) ON DELETE CASCADE,
    nama_sertifikasi VARCHAR(255),
    nomor_sertifikat VARCHAR(100),
    penerbit VARCHAR(100),
    tanggal_terbit DATE,
    tanggal_kadaluarsa DATE,
    status VARCHAR(20) DEFAULT 'valid' CHECK (status IN ('valid', 'expired', 'permanent')),
    file_sertifikat VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sertifikasi_kohai_id ON sertifikasi_kohai(kohai_id);
CREATE INDEX idx_sertifikasi_kohai_status ON sertifikasi_kohai(status);


CREATE TABLE pendaftaran_kohai (
    id SERIAL PRIMARY KEY,
    no_kohai VARCHAR(20) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    foto_kohai VARCHAR(255),
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin VARCHAR(1) CHECK (jenis_kelamin IN ('L', 'P')),
    no_telp VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    alamat TEXT NOT NULL,
    nama_wali VARCHAR(100) NOT NULL,
    no_telp_wali VARCHAR(15) NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Aktif', 'Tidak Aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pengurus (
    id SERIAL PRIMARY KEY,
    nik VARCHAR(50),
    nama VARCHAR(255) NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    jabatan VARCHAR(100) NOT NULL,
    periode VARCHAR(50),
    tanggal_sk DATE,
    no_sk VARCHAR(100),
    email VARCHAR(100),
    telepon VARCHAR(50),
    alamat TEXT,
    pendidikan_terakhir VARCHAR(100),
    foto VARCHAR(255),
    foto_url TEXT,
    status VARCHAR(20) DEFAULT 'Aktif' CHECK (status IN ('Aktif', 'Tidak Aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE legalitas (
    id SERIAL PRIMARY KEY,
    jenis_dokumen VARCHAR(100),
    nomor_dokumen VARCHAR(100),
    tanggal_terbit DATE,
    tanggal_kadaluarsa DATE,
    instansi_penerbit VARCHAR(100),
    file_dokumen VARCHAR(255),
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'kadaluarsa', 'proses')),
    keterangan TEXT,
    is_permanent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lokasi (
    id SERIAL PRIMARY KEY,
    nama_lokasi VARCHAR(100) NOT NULL,
    alamat TEXT,
    koordinat VARCHAR(100),
    kota VARCHAR(50),
    provinsi VARCHAR(50),
    kapasitas INTEGER,
    fasilitas TEXT,
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'non-aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kegiatan (
    id SERIAL PRIMARY KEY,
    nama_kegiatan VARCHAR(100) NOT NULL,
    jenis_kegiatan VARCHAR(50),
    tanggal_kegiatan DATE,
    waktu_mulai TIME,
    waktu_selesai TIME,
    lokasi_id INTEGER REFERENCES lokasi(id) ON DELETE SET NULL,
    lokasi_nama VARCHAR(255),
    alamat TEXT,  -- Alamat lengkap lokasi kegiatan
    pic VARCHAR(100),  -- PIC/Penanggung jawab kegiatan
    peserta TEXT,
    jumlah_peserta INTEGER,
    biaya DECIMAL(15,2),
    dokumentasi TEXT,
    keterangan TEXT,
    foto VARCHAR(255),  -- Filename of uploaded photo for kegiatan news display
    tampil_di_berita BOOLEAN DEFAULT false,  -- Toggle untuk menampilkan kegiatan sebagai berita di halaman utama
    status VARCHAR(20) DEFAULT 'dijadwalkan' CHECK (status IN ('selesai', 'berlangsung', 'dijadwalkan', 'dibatalkan')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_kegiatan_tanggal ON kegiatan(tanggal_kegiatan);
CREATE INDEX idx_kegiatan_berita ON kegiatan(tampil_di_berita) WHERE tampil_di_berita = true;

CREATE TABLE pembayaran (
    id SERIAL PRIMARY KEY,
    kohai_id INTEGER REFERENCES kohai(id) ON DELETE SET NULL,
    msh_id INTEGER REFERENCES majelis_sabuk_hitam(id) ON DELETE SET NULL,
    jenis_pembayaran VARCHAR(50),
    jumlah DECIMAL(15,2) NOT NULL,
    tanggal_bayar DATE,
    bulan_periode VARCHAR(20),
    metode_pembayaran VARCHAR(50),
    bukti_pembayaran VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('lunas', 'pending', 'belum_bayar')),
    keterangan TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pembayaran_kohai ON pembayaran(kohai_id);
CREATE INDEX idx_pembayaran_status ON pembayaran(status);

CREATE TABLE kategori_produk (
    id SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(50),
    urutan INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'non-aktif')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk_toko (
    id SERIAL PRIMARY KEY,
    kode_produk VARCHAR(50) UNIQUE NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    kategori VARCHAR(100),
    kategori_id INTEGER REFERENCES kategori_produk(id) ON DELETE SET NULL,
    harga DECIMAL(15,2) NOT NULL,
    stok INTEGER DEFAULT 0,
    deskripsi TEXT,
    spesifikasi TEXT,
    gambar VARCHAR(255),
    foto VARCHAR(255),
    has_variasi BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'Tersedia' CHECK (status IN ('Tersedia', 'Pre Order', 'Habis', 'tersedia', 'habis')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_produk_kode ON produk_toko(kode_produk);
CREATE INDEX idx_produk_nama ON produk_toko(nama_produk);


CREATE TABLE produk_variasi (
    id SERIAL PRIMARY KEY,
    produk_id INTEGER NOT NULL REFERENCES produk_toko(id) ON DELETE CASCADE,
    atribut_1 VARCHAR(100),
    nilai_1 VARCHAR(100),
    atribut_2 VARCHAR(100),
    nilai_2 VARCHAR(100),
    stok INTEGER DEFAULT 0,
    harga_tambahan DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_produk_variasi_produk ON produk_variasi(produk_id);

CREATE TABLE transaksi_toko (
    id SERIAL PRIMARY KEY,
    id_transaksi VARCHAR(50) UNIQUE NOT NULL,
    produk_id INTEGER NOT NULL REFERENCES produk_toko(id) ON DELETE CASCADE,
    nama_produk VARCHAR(255),
    pembeli VARCHAR(255) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    alamat TEXT,
    jumlah INTEGER NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    metode_pembayaran VARCHAR(50),
    tanggal TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    tanggal_transaksi DATE,
    catatan TEXT,
    variasi_info TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('selesai', 'pending', 'batal')),
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_transaksi_toko_id ON transaksi_toko(id_transaksi);
CREATE INDEX idx_transaksi_toko_tanggal ON transaksi_toko(tanggal);

CREATE TABLE transaksi (
    id SERIAL PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis VARCHAR(20) NOT NULL CHECK (jenis IN ('pemasukan', 'pengeluaran')),
    kategori VARCHAR(100),
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0,
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal);
CREATE INDEX idx_transaksi_jenis ON transaksi(jenis);


CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_majelis_sabuk_hitam_updated_at BEFORE UPDATE ON majelis_sabuk_hitam
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_kohai_updated_at BEFORE UPDATE ON kohai
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_informasi_yayasan_updated_at BEFORE UPDATE ON informasi_yayasan
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_pengurus_updated_at BEFORE UPDATE ON pengurus
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_legalitas_updated_at BEFORE UPDATE ON legalitas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_kegiatan_updated_at BEFORE UPDATE ON kegiatan
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_pembayaran_updated_at BEFORE UPDATE ON pembayaran
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_produk_toko_updated_at BEFORE UPDATE ON produk_toko
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transaksi_updated_at BEFORE UPDATE ON transaksi
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_dojo_updated_at BEFORE UPDATE ON dojo
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_provinsi_updated_at BEFORE UPDATE ON provinsi
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_kategori_produk_updated_at BEFORE UPDATE ON kategori_produk
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();


INSERT INTO provinsi (nama_provinsi, kode_provinsi) VALUES
('DKI Jakarta', 'JKT'),
('Jawa Barat', 'JBR'),
('Jawa Tengah', 'JTG'),
('Jawa Timur', 'JTM'),
('Bali', 'BAL')
ON CONFLICT DO NOTHING;

INSERT INTO informasi_yayasan (nama_lengkap, nama_singkat, tanggal_berdiri, alamat, email, telepon, visi) VALUES
('Yayasan Pemberdayaan Orang Kerdil', 'YPOK', '2010-01-15', 'Jakarta, Indonesia', 'info@ypok.org', '021-1234567', 
'Menjadi yayasan terdepan dalam pemberdayaan masyarakat')
ON CONFLICT DO NOTHING;

INSERT INTO kategori_produk (nama_kategori, deskripsi) VALUES
('Seragam', 'Seragam karate dan perlengkapan'),
('Aksesoris', 'Aksesoris dan perlengkapan latihan'),
('Merchandise', 'Merchandise YPOK'),
('Perlengkapan', 'Perlengkapan camping dan outdoor')
ON CONFLICT DO NOTHING;


CREATE OR REPLACE VIEW view_msh_summary AS
SELECT 
    m.id,
    m.kode_msh,
    m.nama,
    m.tingkat_sabuk,
    m.dojo_cabang,
    m.status,
    COUNT(DISTINCT p.id) as jumlah_prestasi,
    COUNT(DISTINCT s.id) as jumlah_sertifikasi
FROM majelis_sabuk_hitam m
LEFT JOIN prestasi_msh p ON m.id = p.msh_id
LEFT JOIN sertifikasi_msh s ON m.id = s.msh_id
GROUP BY m.id;

CREATE OR REPLACE VIEW view_kohai_summary AS
SELECT 
    k.id,
    k.kode_kohai,
    k.nama,
    k.tingkat_kyu,
    k.sabuk,
    k.dojo_cabang,
    k.status,
    COUNT(DISTINCT p.id) as jumlah_prestasi,
    COUNT(DISTINCT s.id) as jumlah_sertifikasi
FROM kohai k
LEFT JOIN prestasi_kohai p ON k.id = p.kohai_id
LEFT JOIN sertifikasi_kohai s ON k.id = s.kohai_id
GROUP BY k.id;