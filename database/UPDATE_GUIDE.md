# Panduan Update Sistem Jadwal Reservasi

## 📋 Perubahan Yang Dilakukan

### 1. Database Changes
- ✅ Menambah kolom `doctor_name` ke tabel `polyclinic_schedules`
- ✅ Mengisi data dokter untuk setiap jadwal
- ✅ Menghapus jadwal hari Sabtu (sistem hanya Senin-Jumat)

### 2. Backend Changes
- ✅ API baru: `api/get_schedules.php` untuk mendapatkan jadwal berdasarkan poli dan tanggal
- ✅ API mengembalikan dokter yang tersedia per jadwal

### 3. Frontend Changes
- ✅ Form reservasi: dropdown dokter sekarang dinamis berdasarkan tanggal
- ✅ Validasi tanggal: hanya bisa pilih Senin-Jumat
- ✅ Disabled weekend selection (Sabtu & Minggu)

---

## 🚀 Cara Menjalankan Update

### Step 1: Backup Database (PENTING!)
```bash
# Via MySQL Command Line
mysqldump -u root db_kesehatan > backup_before_update.sql

# Atau via phpMyAdmin: Export database
```

### Step 2: Jalankan Migration SQL

**Via phpMyAdmin (Recommended):**
1. Buka `http://localhost/phpmyadmin`
2. Pilih database `db_kesehatan`
3. Klik tab **SQL**
4. Copy seluruh isi file `database/add_doctor_to_schedules.sql`
5. Paste ke SQL editor
6. Klik tombol **Go**
7. Pastikan muncul pesan success

**Via MySQL Command Line:**
```bash
mysql -u root db_kesehatan < database/add_doctor_to_schedules.sql
```

### Step 3: Verifikasi Hasil

Jalankan query berikut untuk memastikan data sudah benar:

```sql
-- Cek struktur tabel (harus ada kolom doctor_name)
DESCRIBE polyclinic_schedules;

-- Lihat semua jadwal dengan dokter
SELECT 
    p.name as poliklinik,
    ps.day_of_week as hari,
    ps.doctor_name as dokter,
    ps.start_time as mulai,
    ps.end_time as selesai
FROM polyclinic_schedules ps
JOIN polyclinics p ON ps.polyclinic_id = p.id
ORDER BY p.id, FIELD(ps.day_of_week, 'Senin','Selasa','Rabu','Kamis','Jumat');
```

**Expected Output:**
- Semua jadwal hanya Senin - Jumat (tidak ada Sabtu/Minggu)
- Setiap jadwal memiliki `doctor_name` yang terisi
- Total jadwal: sekitar 30-35 record

---

## 🎯 Data Dokter Yang Ditambahkan

### Poli Umum
- **dr. Arief Pratama, Sp.PD** - Senin, Selasa, Rabu
- **dr. Aya Putri** - Kamis, Jumat

### Poli Gigi
- **dr. Siti Nurhaliza, Sp.KG** - Senin, Rabu, Jumat
- **dr. Budi Santoso, Sp.KG** - Selasa, Kamis

### Poli Mata
- **dr. Clara Wijaya, Sp.M** - Senin - Jumat

### Poli Saraf
- **dr. Rina Wijaya, Sp.S** - Senin - Jumat

### Poli Jantung
- **dr. Dedi Kurniawan, Sp.JP** - Senin, Rabu, Jumat

### Poli Anak
- **dr. Maya Kusuma, Sp.A** - Senin, Selasa, Rabu
- **dr. Dimas Arjuna, Sp.A** - Kamis, Jumat

---

## ✅ Testing

### Test 1: Pilih Poli
1. Buka halaman reservasi (`/public-web/layout/reservation.php`)
2. Login sebagai user
3. Isi data pasien (Step 1)
4. Pilih poli di Step 2
5. Pindah ke Step 3

**Expected:** Dropdown dokter masih disabled dengan text "Pilih tanggal kunjungan terlebih dahulu"

### Test 2: Pilih Tanggal Weekend
1. Di Step 3, pilih tanggal kunjungan
2. Coba pilih hari Sabtu atau Minggu

**Expected:** Alert muncul: "Reservasi hanya tersedia untuk hari Senin sampai Jumat"

### Test 3: Pilih Tanggal Weekday
1. Pilih tanggal hari Senin - Jumat
2. Perhatikan dropdown dokter

**Expected:** 
- Dropdown dokter enabled
- Menampilkan dokter yang praktek pada hari tersebut
- Format: "dr. Nama Dokter (08:00 - 14:00)"

### Test 4: Ganti Tanggal
1. Pilih tanggal Senin (misal: Poli Umum)
2. Lihat dokter yang muncul (Dr. Arief Pratama)
3. Ganti ke tanggal Jumat
4. Lihat dokter yang muncul (Dr. Aya Putri)

**Expected:** Dokter berubah sesuai jadwal hari yang dipilih

### Test 5: Poli Jantung Selasa
1. Pilih Poli Jantung
2. Pilih tanggal hari Selasa

**Expected:** 
- Dropdown dokter menampilkan: "Tidak ada jadwal dokter pada tanggal ini"
- Alert: "Maaf, tidak ada jadwal Poli Jantung pada hari Selasa"

(Karena Poli Jantung hanya buka Senin, Rabu, Jumat)

---

## 🐛 Troubleshooting

### Error: Column 'doctor_name' doesn't exist
**Solution:** Migration belum dijalankan. Jalankan Step 2 di atas.

### Dropdown dokter masih kosong
**Possible causes:**
1. Check browser console untuk error
2. Pastikan file `api/get_schedules.php` ada
3. Test API langsung: `http://localhost/projek-layanan-kesehatan/api/get_schedules.php?polyclinic_id=1`

### Masih bisa pilih hari Sabtu/Minggu
**Solution:** Clear browser cache dan reload halaman.

### Dokter tidak muncul untuk tanggal tertentu
**Check:** 
1. Apakah poli tersebut buka di hari itu? (misal: Poli Jantung hanya Senin, Rabu, Jumat)
2. Cek database apakah ada jadwal untuk hari tersebut

---

## 📝 Rollback (Jika Ada Masalah)

Jika terjadi error dan ingin kembali ke kondisi sebelumnya:

```bash
# Restore dari backup
mysql -u root db_kesehatan < backup_before_update.sql
```

**Catatan:** Setelah rollback, sistem akan kembali ke kondisi lama dimana:
- Dokter masih hardcoded
- Bisa memilih hari Sabtu/Minggu
- Database tidak ada kolom doctor_name

---

## 📊 Summary

**Before Update:**
- ❌ Dokter hardcoded di form
- ❌ Bisa pilih hari Sabtu/Minggu
- ❌ Tidak ada validasi jadwal dokter

**After Update:**
- ✅ Dokter dinamis berdasarkan jadwal
- ✅ Hanya bisa pilih Senin-Jumat
- ✅ Dokter muncul sesuai hari praktek mereka
- ✅ Database terstruktur dengan baik

---

**Last Updated:** February 17, 2026
