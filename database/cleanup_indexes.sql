-- CLEANUP SCRIPT: Hapus semua index yang mungkin conflict
-- Jalankan ini SEBELUM menjalankan supabase_schema_complete.sql

DROP INDEX IF EXISTS idx_users_username CASCADE;
DROP INDEX IF EXISTS idx_users_email CASCADE;
DROP INDEX IF EXISTS idx_users_role CASCADE;
DROP INDEX IF EXISTS idx_provinsi_nama CASCADE;
DROP INDEX IF EXISTS idx_dojo_nama CASCADE;
DROP INDEX IF EXISTS idx_dojo_provinsi CASCADE;
DROP INDEX IF EXISTS idx_msh_nama CASCADE;
DROP INDEX IF EXISTS idx_msh_kode CASCADE;
DROP INDEX IF EXISTS idx_msh_tingkat CASCADE;
DROP INDEX IF EXISTS idx_msh_status CASCADE;
DROP INDEX IF EXISTS idx_prestasi_msh_id CASCADE;
DROP INDEX IF EXISTS idx_sertifikasi_msh_id CASCADE;
DROP INDEX IF EXISTS idx_sertifikasi_msh_status CASCADE;
DROP INDEX IF EXISTS idx_kohai_nama CASCADE;
DROP INDEX IF EXISTS idx_kohai_kode CASCADE;
DROP INDEX IF EXISTS idx_kohai_tingkat CASCADE;
DROP INDEX IF EXISTS idx_kohai_sabuk CASCADE;
DROP INDEX IF EXISTS idx_kohai_status CASCADE;
DROP INDEX IF EXISTS idx_prestasi_kohai_id CASCADE;
DROP INDEX IF EXISTS idx_sertifikasi_kohai_id CASCADE;
DROP INDEX IF EXISTS idx_sertifikasi_kohai_status CASCADE;
DROP INDEX IF EXISTS idx_kegiatan_tanggal CASCADE;
DROP INDEX IF EXISTS idx_pembayaran_kohai CASCADE;
DROP INDEX IF EXISTS idx_pembayaran_status CASCADE;
DROP INDEX IF EXISTS idx_produk_kode CASCADE;
DROP INDEX IF EXISTS idx_produk_nama CASCADE;
DROP INDEX IF EXISTS idx_produk_variasi_produk CASCADE;
DROP INDEX IF EXISTS idx_transaksi_id CASCADE;
DROP INDEX IF EXISTS idx_transaksi_tanggal CASCADE;
DROP INDEX IF EXISTS idx_transaksi_toko_id CASCADE;
DROP INDEX IF EXISTS idx_transaksi_toko_tanggal CASCADE;
DROP INDEX IF EXISTS idx_transaksi_jenis CASCADE;

-- Verifikasi semua index sudah terhapus
SELECT 
    schemaname,
    tablename,
    indexname
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;
