<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? '';
$user_role = $_SESSION['role'] ?? 'patient';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B4C94',
                        'primary-dark': '#2D3A70',
                        secondary: '#5A81FA',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../style/account.tailwind.css">
</head>
<body class="bg-gray-50">
    <!-- Header / Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-5">
            <div class="flex items-center justify-between py-4">
                <a href="main.php" class="flex items-center">
                    <img src="../assets/logo.png" alt="Heartlink Hospital" class="h-12">
                </a>
                <ul class="hidden lg:flex items-center gap-8 m-0 list-none">
                    <li><a href="main.php" class="text-gray-700 font-medium hover:text-primary transition-colors">Beranda</a></li>
                    <li><a href="main.php#layanan" class="text-gray-700 font-medium hover:text-primary transition-colors">Layanan</a></li>
                    <li><a href="main.php#jadwal" class="text-gray-700 font-medium hover:text-primary transition-colors">Jadwal Dokter</a></li>
                </ul>
                <div class="flex items-center gap-4">
                    <a href="account.php" class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-blue-50 text-primary text-xl hover:bg-blue-100 transition-colors" title="Akun">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <a href="../backend/logout.php" class="text-red-500 hover:text-red-600 font-medium transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-5 py-10">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Akun Saya</h1>
                <p class="text-gray-600">Kelola informasi akun dan preferensi Anda</p>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
                <div class="flex items-center gap-6 mb-8">
                    <div class="w-24 h-24 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800"><?php echo htmlspecialchars($user_email); ?></h2>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-primary text-sm font-medium rounded-full mt-2 capitalize">
                            <?php echo htmlspecialchars($user_role); ?>
                        </span>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Akun</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Email</label>
                            <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($user_email); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Role</label>
                            <p class="text-gray-800 font-medium capitalize"><?php echo htmlspecialchars($user_role); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">User ID</label>
                            <p class="text-gray-800 font-medium">#<?php echo htmlspecialchars($user_id); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Status</label>
                            <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                <i class="fas fa-check-circle text-sm"></i> Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="#" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow card-hover">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-calendar-check text-xl text-primary"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Janji Temu Saya</h4>
                    <p class="text-sm text-gray-500">Lihat riwayat janji temu</p>
                </a>
                <a href="#" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow card-hover">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-file-medical text-xl text-green-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Rekam Medis</h4>
                    <p class="text-sm text-gray-500">Akses rekam medis Anda</p>
                </a>
                <a href="#" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow card-hover">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-cog text-xl text-purple-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Pengaturan</h4>
                    <p class="text-sm text-gray-500">Ubah password & preferensi</p>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="container mx-auto px-5 text-center">
            <p class="text-gray-400 text-sm">&copy; 2026 Heartlink Hospital. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
