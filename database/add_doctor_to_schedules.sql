-- =====================================================
-- Migration: Add Doctor to Polyclinic Schedules
-- Menambahkan informasi dokter ke jadwal poliklinik
-- =====================================================

USE db_kesehatan;

-- Tambah kolom doctor_name ke tabel polyclinic_schedules
ALTER TABLE polyclinic_schedules 
ADD COLUMN doctor_name VARCHAR(100) DEFAULT NULL AFTER quota;

-- Update jadwal dengan nama dokter
-- Poli Umum: dr. Arief Pratama (Senin-Rabu), dr. Aya Putri (Kamis-Jumat)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Arief Pratama, Sp.PD' 
WHERE polyclinic_id = 1 AND day_of_week IN ('Senin', 'Selasa', 'Rabu');

UPDATE polyclinic_schedules SET doctor_name = 'dr. Aya Putri' 
WHERE polyclinic_id = 1 AND day_of_week IN ('Kamis', 'Jumat');

-- Hapus jadwal Sabtu untuk Poli Umum (hanya Senin-Jumat)
DELETE FROM polyclinic_schedules 
WHERE polyclinic_id = 1 AND day_of_week = 'Sabtu';

-- Poli Gigi: dr. Siti Nurhaliza, Sp.KG (Senin, Rabu, Jumat), dr. Budi Santoso, Sp.KG (Selasa, Kamis)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Siti Nurhaliza, Sp.KG' 
WHERE polyclinic_id = 2 AND day_of_week IN ('Senin', 'Rabu', 'Jumat');

UPDATE polyclinic_schedules SET doctor_name = 'dr. Budi Santoso, Sp.KG' 
WHERE polyclinic_id = 2 AND day_of_week IN ('Selasa', 'Kamis');

-- Hapus jadwal Sabtu untuk Poli Gigi
DELETE FROM polyclinic_schedules 
WHERE polyclinic_id = 2 AND day_of_week = 'Sabtu';

-- Poli Mata: dr. Clara Wijaya, Sp.M (Senin-Jumat)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Clara Wijaya, Sp.M' 
WHERE polyclinic_id = 3;

-- Poli Saraf: dr. Rina Wijaya, Sp.S (Senin-Jumat)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Rina Wijaya, Sp.S' 
WHERE polyclinic_id = 4;

-- Poli Jantung: dr. Dedi Kurniawan, Sp.JP (Senin, Rabu, Jumat)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Dedi Kurniawan, Sp.JP' 
WHERE polyclinic_id = 5;

-- Hapus jadwal Sabtu untuk Poli Jantung (hanya buka Senin, Rabu, Jumat)  
DELETE FROM polyclinic_schedules 
WHERE polyclinic_id = 5 AND day_of_week = 'Sabtu';

-- Poli Anak: dr. Maya Kusuma, Sp.A (Senin-Rabu), dr. Dimas Arjuna, Sp.A (Kamis-Jumat)
UPDATE polyclinic_schedules SET doctor_name = 'dr. Maya Kusuma, Sp.A' 
WHERE polyclinic_id = 6 AND day_of_week IN ('Senin', 'Selasa', 'Rabu');

UPDATE polyclinic_schedules SET doctor_name = 'dr. Dimas Arjuna, Sp.A' 
WHERE polyclinic_id = 6 AND day_of_week IN ('Kamis', 'Jumat');

-- Hapus jadwal Sabtu untuk Poli Anak
DELETE FROM polyclinic_schedules 
WHERE polyclinic_id = 6 AND day_of_week = 'Sabtu';

-- Verifikasi hasil
SELECT 
    p.name as polyclinic,
    ps.day_of_week,
    ps.start_time,
    ps.end_time,
    ps.doctor_name,
    ps.quota
FROM polyclinic_schedules ps
JOIN polyclinics p ON ps.polyclinic_id = p.id
ORDER BY p.id, FIELD(ps.day_of_week, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');

-- Script selesai
SELECT 'Migration completed: Doctor names added to schedules!' as Status;
