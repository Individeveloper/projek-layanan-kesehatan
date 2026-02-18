<?php
session_start();

// Redirect admin to admin panel
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../../admin-panel/pages/index.php');
    exit;
}

require_once '../../config/connection.php';

// Ambil jadwal poli dari database
$query = "
    SELECT 
        p.id,
        p.name AS poli_name,
        ps.day_of_week,
        ps.start_time,
        ps.end_time
    FROM polyclinics p
    LEFT JOIN polyclinic_schedules ps ON p.id = ps.polyclinic_id
    ORDER BY p.id, FIELD(ps.day_of_week, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
";
$result = $db->query($query);

// Susun data per poli
$schedules = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $poliName = $row['poli_name'];
        if (!isset($schedules[$poliName])) {
            $schedules[$poliName] = [];
        }
        if ($row['day_of_week']) {
            $start = date('H.i', strtotime($row['start_time']));
            $end = date('H.i', strtotime($row['end_time']));
            $schedules[$poliName][$row['day_of_week']] = $start . ' - ' . $end;
        }
    }
}

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Poli - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/schedule.css">
</head>
<body>
    <!-- Header / Navbar -->
    <?php include 'component/navbar.php'; ?>

    <!-- Main Content -->
    <main class="schedule-container">
        <!-- Schedule Table Section -->
        <section class="schedule-section">
            <div class="schedule-header">
                <h2>INFORMASI JADWAL POLI</h2>
            </div>
            
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th class="poli-column">Poli</th>
                        <th>Senin</th>
                        <th>Selasa</th>
                        <th>Rabu</th>
                        <th>Kamis</th>
                        <th>Jumat</th>
                        <th>Sabtu</th>
                        <th>Minggu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $poliName => $poliSchedule): ?>
                            <tr>
                                <td class="poli-name"><?php echo htmlspecialchars($poliName); ?></td>
                                <?php foreach ($days as $day): ?>
                                    <?php if (isset($poliSchedule[$day])): ?>
                                        <td class="schedule-time"><?php echo htmlspecialchars($poliSchedule[$day]); ?></td>
                                    <?php else: ?>
                                        <td class="schedule-time closed">-</td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Belum ada jadwal poli yang tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

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
                        <li><a href="#care">Cari Dokter Spesialis</a></li>
                        <li><a href="schedule.php">Jadwal Praktik</a></li>
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
            <p>&copy; 2024 Heartlink Hospital. Hak Cipta Dilindungi.</p>
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
