<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/register.css">
</head>
<body>
    <div class="register-container">
        <!-- Left Side - Form -->
        <div class="register-form-section">
            <div class="logo-container">
                <img src="../assets/logo.png" alt="Heartlink Hospital Logo">
            </div>

            <div class="welcome-text">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk mengakses layanan kami</p>
            </div>

            <div id="message" class="message" style="display: none;"></div>

            <form id="registerForm" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="no_hp">No Hp</label>
                    <div class="input-wrapper">
                        <input type="tel" id="no_hp" name="no_hp" placeholder="Masukkan nomor HP Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Buat password Anda" required>
                    </div>
                </div>

                <button type="submit" class="register-btn">Daftar</button>
                
                <button type="button" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                    Daftar dengan Google
                </button>

                <p class="login-link">Sudah punya akun? <a href="login.php">Masuk sekarang</a></p>
            </form>
        </div>

        <!-- Right Side - Background Image -->
        <div class="register-image-section"></div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageDiv = document.getElementById('message');
        
        try {
            const response = await fetch('../backend/register.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            messageDiv.style.display = 'block';
            if (data.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = data.message;
                
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
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
