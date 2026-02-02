<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heartlink Hospital - Linking You to Better Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/main.css">
</head>
<body>
    <!-- Header / Navbar -->
    <header class="navbar">
        <nav class="nav-container">
            <div class="nav-logo">
                <img src="../assets/logo.png" alt="Heartlink Hospital">
            </div>
            <ul class="nav-menu">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#tentang">Tentang Kami</a></li>
                <li><a href="#layanan">Layanan & Fasilitas</a></li>
                <li><a href="#jadwal">Jadwal Dokter</a></li>
                <li><a href="#janji">Janji Temu</a></li>
            </ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="account.php" class="nav-account-btn" title="Akun">
                    <i class="fas fa-user-circle"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-btn">Masuk/Daftar</a>
            <?php endif; ?>
            <button class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="beranda">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Setiap Detak Sangat Berarti</h1>
            <p>Ditangani oleh Ahli, Dirawat dengan Hati</p>
            <a href="#layanan" class="hero-btn">Lihat Layanan</a>
            <div class="hero-tags">
                <span class="tag">Patient Care Center</span>
                <span class="tag">Konsultasi Layanan</span>
                <span class="tag">Paket MCU</span>
            </div>
        </div>
    </section>

    <!-- Layanan Unggulan -->
    <section class="services" id="layanan">
        <h2 class="section-title">Layanan Unggulan</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-baby"></i>
                </div>
                <h3>Poliklinik Anak</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-ambulance"></i>
                </div>
                <h3>Instalasi Gawat Darurat</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3>Laboratorium & Radiologi</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <h3>Fasilitas Rawat Inap</h3>
            </div>
        </div>
    </section>

    <!-- Percayakan Kesehatan -->
    <section class="trust-section" id="tentang">
        <div class="trust-container">
            <div class="trust-image">
                <img src="../assets/section-pic-1.jpg" alt="Dokter dengan Pasien">
            </div>
            <div class="trust-content">
                <h2>Percayakan Kesehatan Anda pada Heartlink Hospital</h2>
                <div class="trust-features">
                    <div class="trust-feature">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Dokter Berpengalaman</h4>
                            <p>Tim medis profesional di bidangnya</p>
                        </div>
                    </div>
                    <div class="trust-feature">
                        <div class="feature-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Peralatan Modern</h4>
                            <p>Teknologi terkini untuk perawatan</p>
                        </div>
                    </div>
                    <div class="trust-feature">
                        <div class="feature-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Pelayanan Ramah</h4>
                            <p>Kami hadir dengan sepenuh hati</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dokter Spesialis -->
    <section class="doctors-section" id="jadwal">
        <h2 class="section-title">Dokter Spesialis Kami</h2>
        <div class="doctors-grid">
            <div class="doctor-card">
                <div class="doctor-image">
                    <img src="../assets/doctor1.jpg" alt="dr. Alya Putri">
                    <span class="doctor-badge">Umum</span>
                </div>
                <div class="doctor-info">
                    <h3>dr. Alya Putri</h3>
                    <p class="schedule">Senin - Jumat | 14:00 - 20:00</p>
                    <p class="location">Heartlink Hospital Kemang-Menteng</p>
                    <button class="appointment-btn">Buat Janji</button>
                </div>
            </div>
            <div class="doctor-card">
                <div class="doctor-image">
                    <img src="../assets/doctor2.jpg" alt="dr. Keisha Larasati, Sp.A">
                    <span class="doctor-badge">Anak</span>
                </div>
                <div class="doctor-info">
                    <h3>dr. Keisha Larasati, Sp.A</h3>
                    <p class="schedule">Kamis & Sabtu | 15:00 - 17:00</p>
                    <p class="location">Heartlink Hospital Kemang-Menteng</p>
                    <button class="appointment-btn">Buat Janji</button>
                </div>
            </div>
            <div class="doctor-card">
                <div class="doctor-image">
                    <img src="../assets/doctor3.jpg" alt="dr. Rizky Mahendra, Sp.JP">
                    <span class="doctor-badge">Jantung</span>
                </div>
                <div class="doctor-info">
                    <h3>dr. Rizky Mahendra, Sp.JP</h3>
                    <p class="schedule">Senin & Kamis | 03:00 - 18:00</p>
                    <p class="location">Heartlink Hospital Kemang-Menteng</p>
                    <button class="appointment-btn">Buat Janji</button>
                </div>
            </div>
            <div class="doctor-card">
                <div class="doctor-image">
                    <img src="../assets/doctor4.jpg" alt="dr. Fajar Nugroho, Sp.Rad">
                    <span class="doctor-badge">Radiologi</span>
                </div>
                <div class="doctor-info">
                    <h3>dr. Fajar Nugroho, Sp.Rad</h3>
                    <p class="schedule">Senin - Jumat | 08:00 - 16:00</p>
                    <p class="location">Heartlink Hospital Kemang-Menteng</p>
                    <button class="appointment-btn">Buat Janji</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Pendaftaran Online -->
    <section class="registration-section" id="janji">
        <div class="registration-overlay"></div>
        <div class="registration-content">
            <h2>Pendaftaran Online Heartlink Hospital</h2>
            <p>Sekarang Anda dapat melakukan pendaftaran secara online. Klik tombol dibawah ini untuk melakukan pendaftaran</p>
            <a href="register.php" class="registration-btn">Daftar Sekarang</a>
        </div>
    </section>

    <!-- WhatsApp Customer Care -->
    <section class="whatsapp-section">
        <div class="whatsapp-container">
            <p>Kini <strong>Whatsapp Customer Care Heartlink Hospital</strong> hadir untuk menjawab kebutuhan informasi Heartmates!</p>
            <a href="https://wa.me/081122334455" class="whatsapp-btn" target="_blank">
                <i class="fab fa-whatsapp"></i> 081122334455
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-logo">
                    <img src="../assets/logo.png" alt="Heartlink Hospital">
                </div>
                <div class="footer-links">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="#care">Cari Spesialis</a></li>
                        <li><a href="#jadwal">Jadwal Praktik</a></li>
                        <li><a href="#pendaftaran">Pendaftaran Online</a></li>
                        <li><a href="#informasi">Informasi Kamar</a></li>
                        <li><a href="#tarif">Tarif & Pembayaran</a></li>
                        <li><a href="#artikel">Artikel Kesehatan</a></li>
                        <li><a href="#karir">Karir di Heartlink</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Kontak & Lokasi</h4>
                    <ul>
                        <li><strong>Alamat:</strong></li>
                        <li>Jl. Kesehatan No. 123,</li>
                        <li>Jakarta Pusat 10110</li>
                        <li><strong>Telepon:</strong> (021) 1234-5678</li>
                        <li><strong>Whatsapp:</strong> 0811-2233-4455</li>
                        <li><strong>Email:</strong> cs@heartlinkhospital.id</li>
                        <li><strong>IG/TT/FB:</strong> @heartlinkhospital</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
                <p>&copy; 2026 Heartlink Hospital. Hak Cipta Dilindungi.</p>
            </div>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>
