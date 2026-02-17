# Update Database - Menambahkan Role Doctor dengan Poli Spesifik

## Langkah-Langkah Update Database

### 1. Jalankan Script SQL
Buka phpMyAdmin atau MySQL client Anda, lalu jalankan file:
```
database/update_add_doctor_role.sql
```

### 2. Akun Default yang Tersedia

Setelah menjalankan script, Anda akan memiliki akun berikut:

#### **Admin**
- Email: `admin@heartlinkhospital.id`
- Password: `admin123`
- Akses: Dashboard, Kelola Pengguna, Kelola Poliklinik & Jadwal, Kelola Reservasi

#### **Doctor Poli Umum**
- Email: `doctor.umum@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Ahmad Hidayat
- Role: `doctor-umum`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Umum)

#### **Doctor Poli Gigi** 
- Email: `doctor.gigi@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Siti Nurhaliza, Sp.KG
- Role: `doctor-gigi`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Gigi)

#### **Doctor Poli Mata**
- Email: `doctor.mata@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Budi Santoso, Sp.M
- Role: `doctor-mata`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Mata)

#### **Doctor Poli Saraf**
- Email: `doctor.saraf@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Rina Wijaya, Sp.S
- Role: `doctor-saraf`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Saraf)

#### **Doctor Poli Jantung**
- Email: `doctor.jantung@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Dedi Kurniawan, Sp.JP
- Role: `doctor-jantung`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Jantung)

#### **Doctor Poli Anak**
- Email: `doctor.anak@heartlinkhospital.id`
- Password: `doctor123`
- Nama: Dr. Maya Kusuma, Sp.A
- Role: `doctor-anak`
- Akses: Dashboard, Rekam Medis Pasien (Hanya Poli Anak)

## Sistem Hak Akses

### 🔐 Admin
- ✅ Dashboard (Statistik Admin)
- ✅ Kelola Pengguna
- ✅ Kelola Poliklinik & Jadwal
- ✅ Kelola Reservasi
- ❌ **TIDAK bisa akses Rekam Medis**
- ❌ **TIDAK bisa akses halaman publik**

### 🩺 Doctor (Berdasarkan Poli Spesifik)
- ✅ Dashboard (Statistik Doctor - Hanya data poli mereka)
- ✅ **Rekam Medis Pasien (Eksklusif - Hanya pasien di poli mereka)**
- ⚠️ **Doctor hanya bisa melihat dan mengelola rekam medis dari poli mereka sendiri**
  - Doctor Poli Umum → Hanya rekam medis pasien Poli Umum
  - Doctor Poli Gigi → Hanya rekam medis pasien Poli Gigi
  - Doctor Poli Mata → Hanya rekam medis pasien Poli Mata
  - Doctor Poli Saraf → Hanya rekam medis pasien Poli Saraf
  - Doctor Poli Jantung → Hanya rekam medis pasien Poli Jantung
  - Doctor Poli Anak → Hanya rekam medis pasien Poli Anak
- ❌ Tidak bisa akses Kelola Pengguna
- ❌ Tidak bisa akses Kelola Poliklinik & Jadwal
- ❌ Tidak bisa akses Kelola Reservasi
- ❌ **Tidak bisa akses semua halaman publik (main.php, schedule.php, reservation.php, about.php, account.php)**

### 👤 User (Pasien)
- ✅ Halaman Publik (Main, Schedule, Reservation, About)
- ✅ Account Page
- ❌ Tidak bisa akses Admin Panel

## Fitur Sistem

1. **Pemisahan Dashboard**: Admin dan Doctor melihat dashboard dengan statistik yang berbeda
2. **Sidebar Dinamis**: Menu yang ditampilkan berbeda berdasarkan role
3. **Proteksi Halaman**: Setiap halaman memiliki validasi akses berdasarkan role
4. **Redirect Otomatis**: Admin/Doctor yang mengakses halaman publik akan diarahkan ke dashboard mereka
5. **🔒 Filter Poli**: Doctor hanya melihat data rekam medis dari poli mereka sendiri
6. **Role Format**: `doctor-[nama_poli]` (contoh: `doctor-umum`, `doctor-jantung`)

## Format Role Doctor

Role doctor menggunakan format: `doctor-[nama_poli_lowercase]`

Mapping role ke polyclinic:
- `doctor-umum` → Poli Umum
- `doctor-gigi` → Poli Gigi
- `doctor-mata` → Poli Mata
- `doctor-saraf` → Poli Saraf
- `doctor-jantung` → Poli Jantung
- `doctor-anak` → Poli Anak

## Testing

### Test Login Admin:
1. Buka `admin-panel/login.php`
2. Login dengan email: `admin@heartlinkhospital.id` dan password: `admin123`
3. Verifikasi bisa akses semua menu admin
4. Coba akses `medical_records.php` - seharusnya redirect ke dashboard
5. Coba akses `main.php` atau halaman publik - seharusnya redirect ke dashboard admin

### Test Login Doctor Poli Umum:
1. Buka `admin-panel/login.php`
2. Login dengan email: `doctor.umum@heartlinkhospital.id` dan password: `doctor123`
3. Verifikasi hanya bisa akses Dashboard dan Rekam Medis Pasien
4. Verifikasi hanya melihat data pasien dari Poli Umum
5. Coba akses `users.php`, `polyclinics.php`, atau `reservations.php` - seharusnya redirect ke dashboard
6. Coba akses `main.php` atau halaman publik - seharusnya redirect ke dashboard doctor

### Test Login Doctor Poli Lain:
Ulangi langkah di atas dengan akun doctor lain untuk memastikan setiap doctor hanya melihat data poli mereka:
- `doctor.gigi@heartlinkhospital.id` → Hanya data Poli Gigi
- `doctor.mata@heartlinkhospital.id` → Hanya data Poli Mata
- `doctor.saraf@heartlinkhospital.id` → Hanya data Poli Saraf
- `doctor.jantung@heartlinkhospital.id` → Hanya data Poli Jantung
- `doctor.anak@heartlinkhospital.id` → Hanya data Poli Anak

## Catatan Penting

⚠️ **Keamanan**: Password dalam bentuk plain text hanya untuk development. Untuk production, gunakan `password_hash()` dan `password_verify()`.

⚠️ **Database**: Pastikan Anda sudah menjalankan `db_kesehatan.sql` dan `seed_data.sql` sebelum menjalankan script update ini.

⚠️ **Isolasi Data**: Setiap doctor hanya dapat melihat dan mengelola data dari poli mereka sendiri. Tidak ada akses silang antar poli.
