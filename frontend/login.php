<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: main.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/login.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Form -->
        <div class="login-form-section">
            <div class="logo-container">
                <img src="../assets/logo.png" alt="Heartlink Hospital Logo">
            </div>

            <div class="welcome-text">
                <h2>Selamat Datang</h2>
                <p>Silakan masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <div id="message" class="message" style="display: none;"></div>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="text" id="email" name="email" placeholder="Masukkan email Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
                    </div>
                    <a href="#" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="login-btn">Masuk</button>
                
                <button type="button" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                    Masuk dengan Google
                </button>

                <p class="register-link">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
            </form>
        </div>

        <!-- Right Side - Background Image -->
        <div class="login-image-section"></div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageDiv = document.getElementById('message');
        
        try {
            const response = await fetch('../backend/login.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            messageDiv.style.display = 'block';
            if (data.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = data.message;
                
                setTimeout(() => {
                    window.location.href = 'main.php';
                }, 1000);
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = data.message;
            }
        } catch (error) {
            messageDiv.style.display = 'block';
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    });
    </script>
</body>
</html>
