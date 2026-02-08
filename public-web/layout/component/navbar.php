<header class="navbar">
        <nav class="nav-container">
            <div class="nav-logo">
                <a href="main.php"><img src="../assets/logo.png" alt="Heartlink Hospital"></a>
            </div>
            <ul class="nav-menu">
                <li><a href="main.php">Beranda</a></li>
                <li><a href="about.php">Tentang Kami</a></li>
                <li><a href="schedule.php">Jadwal Poli</a></li>
                <li><a href="<?php echo isset($_SESSION['user_id']) ? 'reservation.php' : 'login.php'; ?>">Reservasi Online</a></li>
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
