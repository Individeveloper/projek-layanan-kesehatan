-- ============================================================
-- UPDATE DATABASE: Menambahkan role doctor dengan poli spesifik
-- Jalankan script ini untuk menambahkan role doctor
-- ============================================================

USE db_kesehatan;

-- 1. Ubah enum role untuk menambahkan doctor dengan poli spesifik
ALTER TABLE `users` MODIFY `role` enum('user','admin','doctor-umum','doctor-gigi','doctor-mata','doctor-saraf','doctor-jantung','doctor-anak') DEFAULT 'user';

-- 2. Tambahkan user doctor default untuk setiap poli
-- Format: doctor-[nama_poli_lowercase]

-- Doctor Poli Umum
INSERT INTO users (name, email, password, role) VALUES
('Dr. Ahmad Hidayat', 'doctor.umum@heartlinkhospital.id', 'doctor123', 'doctor-umum');

-- Doctor Poli Gigi
INSERT INTO users (name, email, password, role) VALUES
('Dr. Siti Nurhaliza, Sp.KG', 'doctor.gigi@heartlinkhospital.id', 'doctor123', 'doctor-gigi');

-- Doctor Poli Mata
INSERT INTO users (name, email, password, role) VALUES
('Dr. Budi Santoso, Sp.M', 'doctor.mata@heartlinkhospital.id', 'doctor123', 'doctor-mata');

-- Doctor Poli Saraf
INSERT INTO users (name, email, password, role) VALUES
('Dr. Rina Wijaya, Sp.S', 'doctor.saraf@heartlinkhospital.id', 'doctor123', 'doctor-saraf');

-- Doctor Poli Jantung
INSERT INTO users (name, email, password, role) VALUES
('Dr. Dedi Kurniawan, Sp.JP', 'doctor.jantung@heartlinkhospital.id', 'doctor123', 'doctor-jantung');

-- Doctor Poli Anak
INSERT INTO users (name, email, password, role) VALUES
('Dr. Maya Kusuma, Sp.A', 'doctor.anak@heartlinkhospital.id', 'doctor123', 'doctor-anak');

SELECT 'Database berhasil diupdate! Role doctor dengan poli spesifik telah ditambahkan.' as status;
