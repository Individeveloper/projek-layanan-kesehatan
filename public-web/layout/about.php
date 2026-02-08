<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/about.css">
</head>
<body>
    <!-- Header / Navbar -->
    <?php include 'component/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-foreground-img">
            <img src="../assets/about-asset2.png" alt="Heartlink Hospital Building">
        </div>
        <div class="about-hero-overlay"></div>
        <div class="about-hero-content">
            <h1>Heartlink Hospital</h1>
            <p>Linking You To better Health</p>
        </div>
    </section>

    <!-- Description Section -->
    <section class="about-description">
        <p>Heartlink Hospital merupakan rumah sakit umum terpadu yang berkomitmen menyediakan layanan kesehatan berkualitas tinggi dengan standar klinis yang unggul. Berdiri sejak tahun 2010, Heartlink Hospital terus berkembang menjadi institusi kesehatan terpercaya yang mengedepankan pelayanan komprehensif serta penerapan teknologi medis modern untuk mendukung proses diagnosis dan perawatan secara optimal.</p>
    </section>

    <!-- Visi & Misi Section -->
    <section class="visi-misi-section">
        <div class="visi-misi-container">
            <div class="visi-misi-image">
                <img src="../assets/about-asset3.png" alt="Dokter dengan pasien">
            </div>
            <div class="visi-misi-content">
                <h3>Visi</h3>
                <p>Menjadi rumah sakit pilihan utama yang unggul dalam pelayanan dan keselamatan pasien.</p>

                <h3>Misi</h3>
                <ul>
                    <li>Memberikan pelayanan kesehatan bermutu tinggi.</li>
                    <li>Menyediakan tenaga medis yang profesional dan kompeten.</li>
                    <li>Mengembangkan sarana dan prasarana medis sesuai perkembangan zaman.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- HEART Values Section -->
    <section class="heart-values-section">
        <div class="heart-values-grid">
            <div class="heart-card">
                <div class="heart-circle">
                    <span class="heart-letter">H</span>
                </div>
                <span class="heart-label">Humanity</span>
            </div>
            <div class="heart-card">
                <div class="heart-circle">
                    <span class="heart-letter">E</span>
                </div>
                <span class="heart-label">Excellence</span>
            </div>
            <div class="heart-card">
                <div class="heart-circle">
                    <span class="heart-letter">A</span>
                </div>
                <span class="heart-label">Accountability</span>
            </div>
            <div class="heart-card">
                <div class="heart-circle">
                    <span class="heart-letter">R</span>
                </div>
                <span class="heart-label">Respect</span>
            </div>
            <div class="heart-card">
                <div class="heart-circle">
                    <span class="heart-letter">T</span>
                </div>
                <span class="heart-label">Teamwork</span>
            </div>
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
                        <li><a href="#care">Cari Dokter Spesialis</a></li>
                        <li><a href="main.php#jadwal">Jadwal Praktik</a></li>
                        <li><a href="register.php">Pendaftaran Online</a></li>
                        <li><a href="#informasi">Informasi Kamar</a></li>
                        <li><a href="#tarif">Tarif & Pembiayaan</a></li>
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
                        <li><strong>WhatsApp:</strong> 0811-2233-4455</li>
                        <li><strong>Email:</strong> info@heartlinkhospital.id</li>
                        <li><strong>IG/FB/TT:</strong> @heartlinkhospital</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Heartlink Hospital. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script>
    // Mobile Menu Toggle
    const mobileToggle = document.getElementById('mobile-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
    </script>
</body>
</html>