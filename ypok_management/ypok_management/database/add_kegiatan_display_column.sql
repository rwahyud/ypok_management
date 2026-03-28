-- Add column untuk menampilkan kegiatan di guest dashboard
-- Run ini jika belum ada column 'tampil_di_berita' di table kegiatan

ALTER TABLE `kegiatan` 
ADD COLUMN `tampil_di_berita` BOOLEAN DEFAULT FALSE AFTER `keterangan`,
ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `tampil_di_berita`;

-- Update existing kegiatan (contoh: tampilkan data terbaru)
-- UPDATE kegiatan SET tampil_di_berita = TRUE ORDER BY tanggal_kegiatan DESC LIMIT 3;
