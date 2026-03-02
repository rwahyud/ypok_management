-- Update Kohai Table untuk menambah field dari CSV
-- File: database/update_kohai_table.sql
-- Deskripsi: Menambah field tanggal_ujian, nomor_ijazah, dan keterangan

-- Tambah field tanggal_ujian
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS tanggal_ujian DATE;
COMMENT ON COLUMN kohai.tanggal_ujian IS 'Tanggal ujian kenaikan sabuk';

-- Tambah field nomor_ijazah  
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS nomor_ijazah TEXT;
COMMENT ON COLUMN kohai.nomor_ijazah IS 'Nomor registrasi ijazah (bisa multiple, dipisah spasi)';

-- Tambah field keterangan
ALTER TABLE kohai ADD COLUMN IF NOT EXISTS keterangan TEXT;
COMMENT ON COLUMN kohai.keterangan IS 'Keterangan tambahan';

-- Update timestamp
UPDATE kohai SET updated_at = CURRENT_TIMESTAMP WHERE id IS NOT NULL;
