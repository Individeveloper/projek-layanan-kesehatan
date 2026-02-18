<?php
session_start();

// Redirect admin to admin panel
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../../admin-panel/pages/index.php');
    exit;
}
$doctors = [
        [
            'name' => 'dr. Alya Putri',
            'image' => '../assets/doctor-picture/doktor0.png',
            'badge' => 'Umum',
            'appointment' => false,
        ],
        [
            'name' => 'dr. Keisha Larasati, Sp.A',
            'image' => '../assets/doctor-picture/doktor1.png',
            'badge' => 'Anak',
            'appointment' => true,
        ],
        [
            'name' => 'dr. Rizky Mahendra, Sp.JP',
            'image' => '../assets/doctor-picture/doktor2.png',
            'badge' => 'Jantung',
            'appointment' => true,
        ],
        [
            'name' => 'dr. Fajar Nugroho, Sp.Rad',
            'image' => '../assets/doctor-picture/doktor3.png',
            'badge' => 'Radiologi',
            'appointment' => true,
        ],
    ];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heartlink Hospital - Linking You to Better Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/main.css">
</head>

<body>
    <!-- Header / Navbar -->
    <?php include 'component/navbar.php'; ?>

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
                    <i class="fas fa-stethoscope"></i>
                </div>
                <h3>Poli Umum</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>Poli Saraf</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Poli Mata</h3>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-tooth"></i>
                </div>
                <h3>Poli Gigi</h3>
            </div>
        </div>
    </section>

    <!-- Percayakan Kesehatan -->
    <section class="trust-section" id="tentang">
        <div class="trust-container">
            <div class="trust-image">
                <img src="../assets/images/section-pic-1.jpg" alt="Dokter dengan Pasien">
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
    <section class="doctors-section">
        <h2 class="section-title">Dokter Spesialis Kami</h2>
        <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card">
                    <div class="doctor-image">
                        <img src="<?= htmlspecialchars($doctor['image']) ?>" alt="<?= htmlspecialchars($doctor['name']) ?>">
                        <span class="doctor-badge"><?= htmlspecialchars($doctor['badge']) ?></span>
                    </div>
                    <div class="doctor-info">
                        <h3><?= htmlspecialchars($doctor['name']) ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Pendaftaran Online -->
    <section class="registration-section" id="janji">
        <div class="registration-overlay"></div>
        <div class="registration-content">
            <h2>Pendaftaran Online Heartlink Hospital</h2>
            <p>Sekarang Anda dapat melakukan pendaftaran secara online. Klik tombol dibawah ini untuk melakukan
                pendaftaran</p>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'reservation.php' : 'login.php'; ?>" class="registration-btn">Daftar Sekarang</a>
        </div>
    </section>

    <!-- WhatsApp Customer Care -->
    <section class="whatsapp-section">
        <div class="whatsapp-container">
            <p>Kini <strong>Whatsapp Customer Care Heartlink Hospital</strong> hadir untuk menjawab kebutuhan informasi
                Heartmates!</p>
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
                    <img src="../assets/images/logo.png" alt="Heartlink Hospital">
                </div>
                <div class="footer-links">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="main.php">Beranda</a></li>
                        <li><a href="about.php">Tentang Kami</a></li>
                        <li><a href="schedule.php">Jadwal Poli</a></li>
                        <li><a href="<?php echo isset($_SESSION['user_id']) ? 'reservation.php' : 'login.php'; ?>">Reservasi Online</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Kontak & Lokasi</h4>
                    <ul>
                        <li><strong>Alamat:</strong></li>
                        <li>Jl. Gatot Subroto No. 123,</li>
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