# Admin Panel - Heartlink Hospital

Panel administrasi untuk mengelola sistem reservasi rumah sakit Heartlink Hospital.

## Fitur

### 1. Dashboard
- Statistik total pengguna, poliklinik, jadwal, dan reservasi
- Daftar reservasi terbaru
- Visualisasi data secara real-time

### 2. Kelola Pengguna
- Melihat daftar semua pengguna
- Menambah pengguna baru (user/admin)
- Mengedit data pengguna
- Menghapus pengguna
- Filter berdasarkan role

### 3. Kelola Poliklinik
- Melihat daftar poliklinik
- Menambah poliklinik baru
- Mengedit nama poliklinik
- Menghapus poliklinik
- Melihat jumlah jadwal per poliklinik

### 4. Kelola Jadwal
- Melihat semua jadwal poliklinik
- Menambah jadwal baru dengan:
  - Pilihan poliklinik
  - Hari operasional
  - Jam buka dan tutup
  - Kuota pasien
- Mengedit jadwal yang ada
- Menghapus jadwal

### 5. Kelola Reservasi
- Melihat semua reservasi
- Filter berdasarkan:
  - Status (pending, confirmed, completed, cancelled)
  - Tanggal kunjungan
- Melihat detail lengkap reservasi
- Mengubah status reservasi
- Statistik per status

## Cara Menggunakan

### Login Admin
1. Akses `http://localhost/projek-layanan-kesehatan/admin-panel/`
2. Gunakan kredensial admin:
   - **Email**: admin@heartlinkhospital.id
   - **Password**: admin123

### Mengelola Data
1. Pilih menu yang ingin dikelola dari sidebar
2. Klik tombol "Tambah" untuk menambah data baru
3. Klik icon edit (pensil) untuk mengubah data
4. Klik icon hapus (tempat sampah) untuk menghapus data

### Filter dan Pencarian
- Gunakan filter yang tersedia di setiap halaman
- Reset filter dengan klik tombol "Reset Filter"

## Struktur File

```
admin-panel/
├── css/
│   └── admin.css          # Stylesheet admin panel
├── js/
│   └── admin.js           # JavaScript untuk modal dan interaksi
├── includes/
│   ├── auth.php           # Middleware authentication
│   ├── header.php         # Header HTML
│   ├── sidebar.php        # Sidebar navigasi
│   └── footer.php         # Footer HTML
├── index.php              # Dashboard
├── login.php              # Halaman login admin
├── logout.php             # Logout handler
├── users.php              # Kelola pengguna
├── polyclinics.php        # Kelola poliklinik
├── schedules.php          # Kelola jadwal
├── reservations.php       # Kelola reservasi
└── README.md              # Dokumentasi
```

## Keamanan

- Semua halaman dilindungi dengan session authentication
- Password di-hash menggunakan `password_hash()` PHP
- Prepared statements untuk mencegah SQL injection
- Input sanitization menggunakan `htmlspecialchars()`
- Admin tidak bisa menghapus akun sendiri

## Database

Admin panel menggunakan database `db_kesehatan` dengan tabel:
- `users` - Data pengguna dan admin
- `polyclinics` - Data poliklinik
- `polyclinic_schedules` - Jadwal poliklinik
- `reservations` - Data reservasi
- `patients` - Data pasien

## Teknologi

- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Poppins)

## Responsive Design

Admin panel mendukung berbagai ukuran layar:
- Desktop (> 768px): Full sidebar layout
- Tablet & Mobile (≤ 768px): Collapsible sidebar

## Support

Untuk bantuan atau pertanyaan, hubungi administrator sistem.

---
© 2026 Heartlink Hospital. All rights reserved.
