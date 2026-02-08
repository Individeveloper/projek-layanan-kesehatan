        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-hospital"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Kelola Pengguna</span>
                </a>
                <a href="polyclinics.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'polyclinics.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clinic-medical"></i>
                    <span>Kelola Poliklinik & Jadwal</span>
                </a>
                <a href="reservations.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-medical"></i>
                    <span>Kelola Reservasi</span>
                </a>
                <a href="logout.php" class="nav-item nav-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                        <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                    </div>
                </div>
            </div>
        </aside>
        
        <div class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <div class="topbar-actions">
                    <a href="../public-web/layout/main.php" class="btn btn-sm btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Lihat Website
                    </a>
                </div>
            </header>
            
            <main class="content">
