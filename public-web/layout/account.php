<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect admin to admin panel
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../../admin-panel/pages/index.php');
    exit;
}

// Include database connection
require_once '../../config/connection.php';

// Get user data from database
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found, destroy session and redirect
    session_destroy();
    header('Location: login.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Update session data
$_SESSION['user_name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

$user_email = $user['email'];
$user_name = $user['name'];
$user_role = $user['role'];
$user_created = date('d F Y', strtotime($user['created_at']));

// Get user's reservations
$reservations_query = "
    SELECT 
        r.id,
        r.reservation_date,
        r.queue_number,
        r.status,
        r.created_at,
        p.full_name as patient_name,
        po.name as polyclinic_name,
        ps.day_of_week,
        ps.start_time,
        ps.end_time
    FROM reservations r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
    LEFT JOIN polyclinics po ON ps.polyclinic_id = po.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
";

$stmt = $db->prepare($reservations_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/account.css">
</head>
<body>
    <!-- Header / Navbar -->
    <header class="account-header">
        <nav class="account-nav">
            <a href="main.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
            <a href="../../handlers/auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="account-main">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Akun Saya</h1>
            <p>Kelola informasi akun dan preferensi Anda</p>
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user_name ?: $user_email); ?></h2>
                    <p><?php echo htmlspecialchars($user_email); ?></p>
                </div>
            </div>

            <!-- Account Info -->
            <div class="account-details">
                <h3>Informasi Akun</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <p><?php echo htmlspecialchars($user_name); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Bergabung</label>
                        <p><?php echo htmlspecialchars($user_created); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <p class="status-active">
                            <i class="fas fa-check-circle"></i> Aktif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="reservation.php" class="action-card">
                <div class="action-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h4>Buat Reservasi</h4>
                <p>Buat jadwal reservasi baru</p>
            </a>
            <a href="schedule.php" class="action-card">
                <div class="action-icon green">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4>Jadwal Poli</h4>
                <p>Lihat jadwal poliklinik</p>
            </a>
            <a href="about.php" class="action-card">
                <div class="action-icon purple">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h4>Tentang Kami</h4>
                <p>Informasi tentang rumah sakit</p>
            </a>
        </div>

        <!-- Reservation History -->
        <div class="reservation-history">
            <div class="section-header">
                <h3><i class="fas fa-history"></i> Riwayat Reservasi</h3>
                <p>10 reservasi terakhir Anda</p>
            </div>

            <?php if ($reservations_result->num_rows > 0): ?>
                <div class="reservations-list">
                    <?php while ($reservation = $reservations_result->fetch_assoc()): ?>
                        <?php
                        $status = $reservation['status'];
                        $status_labels = [
                            'pending' => 'Menunggu',
                            'confirmed' => 'Dikonfirmasi',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan'
                        ];
                        $status_label = $status_labels[$status] ?? ucfirst($status);
                        
                        $status_icons = [
                            'pending' => 'fa-clock',
                            'confirmed' => 'fa-check-circle',
                            'completed' => 'fa-check-double',
                            'cancelled' => 'fa-times-circle'
                        ];
                        $status_icon = $status_icons[$status] ?? 'fa-info-circle';
                        ?>
                        
                        <div class="reservation-item">
                            <div class="reservation-header">
                                <div class="queue-badge">
                                    <i class="fas fa-hashtag"></i>
                                    <span><?php echo str_pad($reservation['queue_number'], 3, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <span class="reservation-status status-<?php echo $status; ?>">
                                    <i class="fas <?php echo $status_icon; ?>"></i>
                                    <?php echo $status_label; ?>
                                </span>
                            </div>
                            
                            <div class="reservation-body">
                                <div class="reservation-info">
                                    <div class="info-row">
                                        <i class="fas fa-user"></i>
                                        <div>
                                            <small>Nama Pasien</small>
                                            <strong><?php echo htmlspecialchars($reservation['patient_name']); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <i class="fas fa-clinic-medical"></i>
                                        <div>
                                            <small>Poliklinik</small>
                                            <strong><?php echo htmlspecialchars($reservation['polyclinic_name']); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <small>Tanggal Kunjungan</small>
                                            <strong>
                                                <?php 
                                                $date = date_create($reservation['reservation_date']);
                                                echo date_format($date, 'd F Y'); 
                                                ?>
                                            </strong>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <small>Jam Praktek</small>
                                            <strong>
                                                <?php 
                                                echo date('H:i', strtotime($reservation['start_time'])) . ' - ' . 
                                                     date('H:i', strtotime($reservation['end_time'])); 
                                                ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="reservation-meta">
                                    <small>
                                        <i class="fas fa-calendar-plus"></i>
                                        Dibuat: <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Belum Ada Reservasi</h4>
                    <p>Anda belum memiliki riwayat reservasi.</p>
                    <a href="reservation.php" class="btn-primary">
                         Buat Reservasi Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="account-footer">
        <div class="footer-content">
            <p>&copy; 2026 Heartlink Hospital. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
