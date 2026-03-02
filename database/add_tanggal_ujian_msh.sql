-- Menambah field tanggal_ujian ke tabel majelis_sabuk_hitam
-- Untuk menyimpan tanggal ujian kenaikan sabuk/tingkat Dan

ALTER TABLE majelis_sabuk_hitam ADD COLUMN IF NOT EXISTS tanggal_ujian DATE;
COMMENT ON COLUMN majelis_sabuk_hitam.tanggal_ujian IS 'Tanggal ujian kenaikan tingkat Dan';

-- Update existing data (opsional - set dari tanggal_lulus jika ada)
-- UPDATE majelis_sabuk_hitam SET tanggal_ujian = tanggal_lulus WHERE tanggal_ujian IS NULL AND tanggal_lulus IS NOT NULL;
