<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
            <a href="../../config/logout.php" class="logout-btn">
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
    </main>

    <!-- Footer -->
    <footer class="account-footer">
        <div class="footer-content">
            <p>&copy; 2026 Heartlink Hospital. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
