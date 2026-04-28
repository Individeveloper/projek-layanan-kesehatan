-- Tambahkan kolom rujukan (referral) ke tabel reservations
-- Migration untuk mendukung logika: jika ada rujukan, pilih poli; jika tidak, default poli umum

ALTER TABLE `reservations` ADD COLUMN `referral` TEXT NULL DEFAULT NULL COMMENT 'Nomor rujukan/surat rujukan dari dokter' AFTER `status`;

-- Optional: Tambahkan kolom referral_date jika ingin mengetahui kapan rujukan dibuat
ALTER TABLE `reservations` ADD COLUMN `referral_date` DATE NULL DEFAULT NULL COMMENT 'Tanggal rujukan' AFTER `referral`;

-- Optional: Tambahkan kolom referral_doctor untuk tracking dokter yang merujuk
ALTER TABLE `reservations` ADD COLUMN `referral_doctor` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Nama dokter yang merujuk' AFTER `referral_date`;
