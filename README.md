# Sistem Antrian Digital - Heartlink Hospital

![Heartlink Hospital](https://img.shields.io/badge/Version-1.0-blue) ![PHP](https://img.shields.io/badge/PHP-8.2-purple) ![MySQL](https://img.shields.io/badge/MySQL-MariaDB-orange)

Sistem antrian digital berbasis web untuk meningkatkan efisiensi pelayanan rumah sakit dengan konsep:
- ✅ Pendaftaran online via website
- 📅 Pemilihan jadwal kunjungan
- 🎫 Nomor antrian otomatis
- ⚡ Konfirmasi cepat oleh administrasi
- 📊 Monitoring antrian real-time

---

## 🎯 Alur Sistem Antrian

```
┌─────────────────┐
│  1. PENDAFTARAN │ → User mendaftar via aplikasi web
└────────┬────────┘
         ↓
┌─────────────────┐
│  2. PILIH JADWAL│ → User memilih poli & tanggal kunjungan
└────────┬────────┘
         ↓
┌─────────────────┐
│  3. DAPAT NOMOR │ → Sistem memberikan nomor antrian otomatis
└────────┬────────┘
         ↓
┌─────────────────┐
│ 4. DATANG SESUAI│ → User datang ke RS sesuai jadwal
│    JADWAL       │
└────────┬────────┘
         ↓
┌─────────────────┐
│ 5. MELAPOR KE   │ → User melapor ke bagian administrasi
│    ADMINISTRASI │
└────────┬────────┘
         ↓
┌─────────────────┐
│ 6. ADMIN        │ → Admin konfirmasi & panggil pasien
│    KONFIRMASI   │
└────────┬────────┘
         ↓
┌─────────────────┐
│ 7. PASIEN       │ → Pasien dipanggil sesuai nomor antrian
│    DIPANGGIL    │
└─────────────────┘
```

---

## 🚀 Teknologi

- **Backend**: PHP 8.2 (Native)
- **Database**: MySQL/MariaDB 10.4
- **Frontend**: HTML5, CSS3, JavaScript, Tailwind CSS
- **Icons**: Font Awesome 6.4
- **Fonts**: Google Fonts (Poppins)
- **Server**: XAMPP (Apache + MySQL)

---

## 📦 Instalasi

### 1. Clone/Download Repository
```bash
cd C:\xampp\htdocs
git clone [repository-url] projek-layanan-kesehatan
```

### 2. Setup Database

#### Via phpMyAdmin (Recommended):
1. Buka `http://localhost/phpmyadmin`
2. Buat database baru: `db_kesehatan`
3. Import file `database/db_kesehatan.sql`
4. Import file `database/seed_data.sql`

#### Via MySQL Command Line:
```bash
# 1. Buat database
mysql -u root -e "CREATE DATABASE db_kesehatan"

# 2. Import schema
mysql -u root db_kesehatan < database/db_kesehatan.sql

# 3. Import data awal
mysql -u root db_kesehatan < database/seed_data.sql
```

### 3. Hapus Role Doctor (Jika Upgrade dari Versi Lama)
Jika sebelumnya sistem memiliki fitur rekam medis dokter:
```bash
mysql -u root db_kesehatan < database/remove_doctor_role.sql
```

### 4. Akses Aplikasi
- **Website User**: `http://localhost/projek-layanan-kesehatan/public-web/layout/main.php`
- **Admin Panel**: `http://localhost/projek-layanan-kesehatan/admin-panel/`

---

## 🔑 Kredensial Default

**Admin:**
- Email: `admin@heartlinkhospital.id`
- Password: `admin123`

**User Testing** (jika ada di seed_data.sql):
- Buat akun sendiri via halaman register

---

## 📁 Struktur Proyek

```
projek-layanan-kesehatan/
├── admin-panel/              # Panel Administrasi
│   ├── pages/                # Halaman-halaman admin
│   │   ├── index.php         # Dashboard
│   │   ├── users.php         # Kelola pengguna
│   │   ├── polyclinics.php   # Kelola poliklinik & jadwal
│   │   ├── reservations.php  # Kelola antrian (⭐ Fitur Utama)
│   │   └── medical_records.php # Riwayat medis
│   ├── includes/             # Auth, header, sidebar, footer
│   ├── css/                  # Stylesheet admin panel
│   ├── js/                   # JavaScript modal & interaksi
│   ├── login.php             # Login admin (redirect)
│   └── logout.php            # Logout admin
│
├── api/                      # API Endpoints
│   ├── get_data.php          # API get data umum
│   └── get_schedules.php     # API jadwal poli
│
├── handlers/                 # Request Handlers
│   ├── auth/                 # Authentication handlers
│   │   ├── login.php         # Process login
│   │   ├── register.php      # Process register
│   │   └── logout.php        # Process logout
│   └── process_reservation.php  # Process pembuatan antrian
│
├── config/                   # Konfigurasi
│   └── connection.php        # Koneksi database
│
├── database/                 # Database SQL Files
│   ├── db_kesehatan.sql      # Schema database
│   ├── seed_data.sql         # Data testing
│   ├── add_doctor_to_schedules.sql # Migration script
│   ├── README_UPDATE.md      # Catatan update
│   └── UPDATE_GUIDE.md       # Panduan migrasi
│
├── public-web/               # Website User/Pasien
│   ├── assets/               # Media files
│   │   ├── images/           # Gambar (logo, backgrounds, dll)
│   │   └── doctor-picture/   # Foto dokter
│   ├── js/                   # JavaScript frontend
│   ├── style/                # CSS files
│   └── layout/               # Halaman-halaman web
│       ├── component/        # Komponen reusable (navbar)
│       ├── main.php          # Landing page
│       ├── schedule.php      # Jadwal poliklinik
│       ├── reservation.php   # Form reservasi
│       ├── about.php         # Tentang kami
│       ├── account.php       # Akun user dengan riwayat reservasi
│       ├── login.php         # Login user
│       └── register.php      # Register user
│
└── README.md                 # Dokumentasi (file ini)
```

---

## 🎨 Fitur Lengkap

### 👥 Untuk User/Pasien:

#### 1. Landing Page
- Hero section dengan tagline "Setiap Detak Sangat Berarti"
- Informasi layanan unggulan (Poli Umum, Saraf, Mata, Gigi)
- Profil dokter-dokter rumah sakit
- Call-to-action untuk reservasi

#### 2. Lihat Jadwal Poliklinik
- Jadwal operasional semua poliklinik
- Informasi hari dan jam praktek
- Kuota pasien per hari

#### 3. Pendaftaran Online
- Form pendaftaran lengkap:
  - Data pribadi (NIK, nama, tanggal lahir, gender)
  - Data kontak (alamat, telepon)
  - Pilih poliklinik
  - Pilih tanggal kunjungan
  - Keluhan pasien
- **Nomor antrian otomatis** setelah selesai daftar
- Status antrian: `pending` (menunggu kedatangan)

#### 4. Manajemen Akun
- Lihat profil pribadi
- Update data diri
- Riwayat reservasi

### 🔐 Untuk Admin:

#### 1. Dashboard
- **Statistik real-time:**
  - Total Pengguna
  - Total Poliklinik
  - Jadwal Aktif
  - Total Antrian
- **Ringkasan Antrian:**
  - Menunggu Konfirmasi (pending)
  - Siap Dipanggil (confirmed)
- **Tabel Antrian Terbaru** (10 data terakhir)

#### 2. Kelola Pengguna
- CRUD pengguna lengkap
- Filter berdasarkan role (user/admin)
- Validasi tidak bisa hapus akun sendiri
- Password hashing otomatis

#### 3. Kelola Poliklinik
- CRUD poliklinik (Poli Umum, Gigi, Mata, Saraf, Jantung, Anak)
- Lihat jumlah jadwal per poliklinik
- Validasi sebelum delete (cek relasi)

#### 4. Kelola Jadwal
- CRUD jadwal operasional
- Pilihan:
  - Poliklinik
  - Hari (Senin - Minggu)
  - Jam buka & tutup
  - Kuota pasien harian
- Filter & search

#### 5. Kelola Antrian ⭐ (FITUR UTAMA)

**Workflow Admin:**

##### Saat Pasien Mendaftar Online:
- Sistem otomatis buat antrian dengan status `pending`
- Pasien dapat nomor antrian otomatis
- Pasien tahu jadwal kunjungan

##### Saat Pasien Datang ke RS:
1. Pasien melapor ke bagian administrasi
2. Admin buka menu "Kelola Antrian"
3. Admin cari pasien (nama/nomor antrian)
4. Admin ubah status: `pending` → `confirmed`
5. Pasien menunggu dipanggil

##### Saat Memanggil Pasien:
- Admin panggil sesuai nomor antrian
- Pasien masuk ke ruang pemeriksaan

##### Saat Pasien Selesai:
1. Admin ubah status: `confirmed` → `completed`
2. Antrian selesai
3. Statistik terupdate otomatis

**Fitur Kelola Antrian:**
- ✅ Filter berdasarkan status (pending, confirmed, completed, cancelled)
- ✅ Filter berdasarkan tanggal kunjungan
- ✅ Update status bulk
- ✅ Lihat detail lengkap pasien
- ✅ Statistik per status
- ✅ Export data (future enhancement)

**Status Antrian:**

| Status | Keterangan | Action |
|--------|------------|--------|
| `pending` | Pasien sudah daftar online, belum datang | Menunggu kedatangan |
| `confirmed` | Pasien sudah melapor, siap dipanggil | Panggil pasien |
| `completed` | Pasien sudah selesai dilayani | Antrian selesai |
| `cancelled` | Pasien batal / tidak datang | Dibatalkan |

**Status Flow:**
```
pending → confirmed → completed
   ↓
cancelled
```

---

## 📊 Database Schema

### Tabel: `users`
Data pengguna sistem
```sql
id (PK, INT, AUTO_INCREMENT)
name (VARCHAR 100)
email (VARCHAR 100, UNIQUE)
password (VARCHAR 255, hashed)
role (ENUM: 'user', 'admin')
created_at (TIMESTAMP)
```

### Tabel: `patients`
Data lengkap pasien
```sql
id (PK, INT, AUTO_INCREMENT)
user_id (FK → users.id)
nik (VARCHAR 16, UNIQUE)
full_name (VARCHAR 100)
place_of_birth (VARCHAR 50)
date_of_birth (DATE)
gender (ENUM: 'L', 'P')
address (TEXT)
phone_number (VARCHAR 15)
created_at (TIMESTAMP)
```

### Tabel: `polyclinics`
Master poliklinik
```sql
id (PK, INT, AUTO_INCREMENT)
name (VARCHAR 100)
```
**Contoh data:** Poli Umum, Poli Gigi, Poli Mata, Poli Saraf, Poli Jantung, Poli Anak

### Tabel: `polyclinic_schedules`
Jadwal operasional per poliklinik
```sql
id (PK, INT, AUTO_INCREMENT)
polyclinic_id (FK → polyclinics.id)
day_of_week (ENUM: 'Senin', 'Selasa', ..., 'Minggu')
start_time (TIME)
end_time (TIME)
quota (INT, default 50)
```

### Tabel: `reservations`
Data antrian pasien
```sql
id (PK, INT, AUTO_INCREMENT)
user_id (FK → users.id)
patient_id (FK → patients.id)
polyclinic_schedule_id (FK → polyclinic_schedules.id)
reservation_date (DATE)
queue_number (INT, auto-generated)
status (ENUM: 'pending', 'confirmed', 'completed', 'cancelled')
created_at (TIMESTAMP)
```

### Tabel: `medical_records`
Rekam medis pasien (untuk pengembangan future)
```sql
id (PK, INT, AUTO_INCREMENT)
reservation_id (FK → reservations.id, UNIQUE)
patient_id (FK → patients.id)
doctor_name (VARCHAR 100)
symptoms (TEXT)
diagnosis (TEXT)
treatment (TEXT)
prescription (TEXT)
created_at (TIMESTAMP)
```

**📝 Note:** Tabel `medical_records` sudah ada di schema tapi belum digunakan. Dipersiapkan untuk fitur rekam medis di masa depan.

---

## 🔒 Keamanan

### Implementasi Keamanan:
- ✅ **Session-based authentication** - Setiap halaman terproteksi
- ✅ **Password hashing** - Menggunakan `password_hash()` PHP
- ✅ **SQL injection prevention** - Prepared statements untuk semua query
- ✅ **Input sanitization** - `htmlspecialchars()` untuk output
- ✅ **Role-based access control** - Pemisahan akses Admin & User
- ✅ **Email validation** - Filter email valid
- ✅ **Protection logic** - Admin tidak bisa hapus akun sendiri

### Rekomendasi untuk Production:
- ⚠️ Tambahkan CSRF token untuk form
- ⚠️ Implementasi rate limiting untuk login
- ⚠️ Environment variables untuk credentials (gunakan .env)
- ⚠️ HTTPS untuk production
- ⚠️ Password reset functionality
- ⚠️ Two-factor authentication (2FA)

---

## 🛠️ Maintenance & Tips

### Backup Database
```bash
# Backup dengan timestamp
mysqldump -u root db_kesehatan > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore dari backup
mysql -u root db_kesehatan < backup_20260217_100000.sql
```

### Check Database Status
```sql
-- Cek jumlah users per role
SELECT role, COUNT(*) as total FROM users GROUP BY role;

-- Cek jumlah antrian per status
SELECT status, COUNT(*) as total FROM reservations GROUP BY status;

-- Cek poliklinik dengan jadwal
SELECT p.name, COUNT(ps.id) as total_schedules 
FROM polyclinics p 
LEFT JOIN polyclinic_schedules ps ON p.id = ps.polyclinic_id 
GROUP BY p.id;

-- Antrian hari ini
SELECT * FROM reservations WHERE DATE(reservation_date) = CURDATE();
```

### Reset Data Testing (⚠️ Hati-hati!)
```sql
-- Hapus semua data transaksi
DELETE FROM medical_records;
DELETE FROM reservations;
DELETE FROM patients WHERE user_id NOT IN (SELECT id FROM users WHERE role = 'admin');
DELETE FROM users WHERE role = 'user';

-- Reset auto increment
ALTER TABLE medical_records AUTO_INCREMENT = 1;
ALTER TABLE reservations AUTO_INCREMENT = 1;
ALTER TABLE patients AUTO_INCREMENT = 1;
```

### Tips Efisiensi Antrian

**Pagi Hari (07:00 - 10:00):**
- Konfirmasi antrian pasien yang sudah datang
- Filter by date: hari ini
- Ubah status pending → confirmed

**Siang Hari (10:00 - 14:00):**
- Pantau antrian yang masih pending
- Hubungi pasien yang belum datang (jika ada sistem notifikasi)
- Update completed untuk pasien yang selesai

**Sore Hari (14:00 - 17:00):**
- Tandai no-show sebagai cancelled
- Recap statistik harian
- Persiapan untuk besok

### Best Practice

1. **Konfirmasi Cepat**: Segera ubah status ke confirmed saat pasien melapor
2. **Update Real-time**: Selalu update status ke completed setelah selesai
3. **Gunakan Filter**: Filter tanggal untuk focus pada antrian hari ini
4. **Monitor Kuota**: Atur kuota jadwal berdasarkan statistik antrian
5. **Backup Rutin**: Backup database minimal 1x seminggu

---

## 📝 Migration History

| Tanggal | File | Deskripsi |
|---------|------|-----------|
| Feb 2026 | `db_kesehatan.sql` | Schema database awal |
| Feb 2026 | `seed_data.sql` | Data testing awal |
| Feb 2026 | `update_add_doctor_role.sql` | ❌ DEPRECATED - Tambah role doctor |
| Feb 2026 | `remove_doctor_role.sql` | ✅ Hapus role doctor, fokus antrian digital |

---

## 🎯 Roadmap & Future Enhancement

### Short-term (Next 3 months):
- [ ] Notifikasi email otomatis saat pendaftaran
- [ ] SMS reminder untuk kunjungan besok
- [ ] Queue display system (layar antrian)
- [ ] Print nomor antrian (receipt printer)
- [ ] Mobile responsive improvement

### Mid-term (Next 6 months):
- [ ] Mobile app (Android/iOS)
- [ ] WhatsApp integration untuk notifikasi
- [ ] Online payment integration
- [ ] QR code untuk check-in otomatis
- [ ] Dashboard analytics yang lebih advanced

### Long-term (Next 12 months):
- [ ] Telemedicine integration
- [ ] Rekam medis elektronik (EMR)
- [ ] Appointment scheduling dengan dokter spesifik
- [ ] Pharmacy integration
- [ ] Multi-branch support
- [ ] API untuk integrasi dengan sistem lain

---

## ❓ FAQ

**Q: Bagaimana cara mengubah password admin?**
A: Login ke admin panel → Kelola Pengguna → Edit admin → Update password

**Q: Nomor antrian reset setiap hari?**
A: Ya, nomor antrian di-generate berdasarkan tanggal dan poli. Bisa dikustomisasi di `handlers/process_reservation.php`

**Q: Bisa hapus data pasien?**
A: Admin bisa hapus user, tapi harus hati-hati karena akan mempengaruhi relasi dengan tabel patients dan reservations

**Q: Sistem support multi cabang?**
A: Saat ini belum, tapi ada di roadmap untuk pengembangan future

**Q: Bagaimana cara backup data?**
A: Gunakan mysqldump (lihat section Maintenance) atau via phpMyAdmin

---

## 📞 Support & Contact

Untuk pertanyaan, bug report, atau feature request:
- 📧 Email: admin@heartlinkhospital.id
- 💬 Create issue di repository (jika ada)
- 📱 Contact administrator sistem

---

## 📄 License

Proyek ini dibuat untuk keperluan edukasi dan implementasi sistem antrian digital rumah sakit.

---

## 👏 Credits

Developed with ❤️ for **Heartlink Hospital**

**Heartlink Hospital** - Linking You to Better Health 💙

---

*Last Updated: February 17, 2026*
