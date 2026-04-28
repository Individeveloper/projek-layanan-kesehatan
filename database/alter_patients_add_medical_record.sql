-- Tambahkan kolom medical_record ke tabel patients
ALTER TABLE `patients` ADD COLUMN `medical_record` VARCHAR(50) UNIQUE DEFAULT NULL AFTER `user_id`;

-- Update existing records dengan medical record jika ada
-- Opsional: buat medical record untuk data yang sudah ada
-- UPDATE `patients` SET `medical_record` = CONCAT('RM', `id`, DATE_FORMAT(`created_at`, '%Y%m%d')) WHERE `medical_record` IS NULL;
