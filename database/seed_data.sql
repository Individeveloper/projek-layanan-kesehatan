-- ============================================================
-- DATA AWAL (SEED) UNTUK db_kesehatan
-- Jalankan setelah import db_kesehatan.sql
-- ============================================================

USE db_kesehatan;

-- -----------------------------------------------------------
-- 1. Admin Default
-- Email: admin@heartlinkhospital.id | Password: admin123
-- WARNING: Password tidak di-hash untuk keperluan development!
-- -----------------------------------------------------------
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@heartlinkhospital.id', 'admin123', 'admin');

-- -----------------------------------------------------------
-- 2. Data Polyclinics
-- -----------------------------------------------------------
INSERT INTO polyclinics (name) VALUES
('Poli Umum'),
('Poli Gigi'),
('Poli Mata'),
('Poli Saraf'),
('Poli Jantung'),
('Poli Anak');

-- -----------------------------------------------------------
-- 3. Jadwal Polyclinic Schedules
-- -----------------------------------------------------------

-- Poli Umum (id=1): Senin-Jumat 08:00-14:00, Sabtu 08:00-12:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(1, 'Senin', '08:00:00', '14:00:00', 50),
(1, 'Selasa', '08:00:00', '14:00:00', 50),
(1, 'Rabu', '08:00:00', '14:00:00', 50),
(1, 'Kamis', '08:00:00', '14:00:00', 50),
(1, 'Jumat', '08:00:00', '14:00:00', 50),
(1, 'Sabtu', '08:00:00', '12:00:00', 30);

-- Poli Gigi (id=2): Senin-Jumat 09:00-15:00, Sabtu 09:00-12:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(2, 'Senin', '09:00:00', '15:00:00', 30),
(2, 'Selasa', '09:00:00', '15:00:00', 30),
(2, 'Rabu', '09:00:00', '15:00:00', 30),
(2, 'Kamis', '09:00:00', '15:00:00', 30),
(2, 'Jumat', '09:00:00', '15:00:00', 30),
(2, 'Sabtu', '09:00:00', '12:00:00', 20);

-- Poli Mata (id=3): Senin-Jumat 10:00-16:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(3, 'Senin', '10:00:00', '16:00:00', 25),
(3, 'Selasa', '10:00:00', '16:00:00', 25),
(3, 'Rabu', '10:00:00', '16:00:00', 25),
(3, 'Kamis', '10:00:00', '16:00:00', 25),
(3, 'Jumat', '10:00:00', '16:00:00', 25);

-- Poli Saraf (id=4): Senin-Jumat 08:00-12:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(4, 'Senin', '08:00:00', '12:00:00', 20),
(4, 'Selasa', '08:00:00', '12:00:00', 20),
(4, 'Rabu', '08:00:00', '12:00:00', 20),
(4, 'Kamis', '08:00:00', '12:00:00', 20),
(4, 'Jumat', '08:00:00', '12:00:00', 20);

-- Poli Jantung (id=5): Senin, Rabu, Jumat 09:00-14:00, Sabtu 09:00-12:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(5, 'Senin', '09:00:00', '14:00:00', 20),
(5, 'Rabu', '09:00:00', '14:00:00', 20),
(5, 'Jumat', '09:00:00', '14:00:00', 20),
(5, 'Sabtu', '09:00:00', '12:00:00', 15);

-- Poli Anak (id=6): Senin-Jumat 08:00-14:00, Sabtu 08:00-12:00
INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES
(6, 'Senin', '08:00:00', '14:00:00', 40),
(6, 'Selasa', '08:00:00', '14:00:00', 40),
(6, 'Rabu', '08:00:00', '14:00:00', 40),
(6, 'Kamis', '08:00:00', '14:00:00', 40),
(6, 'Jumat', '08:00:00', '14:00:00', 40),
(6, 'Sabtu', '08:00:00', '12:00:00', 25);
