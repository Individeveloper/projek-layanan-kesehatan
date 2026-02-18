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
                <img src="../assets/images/logo.png" alt="Heartlink Hospital Logo">
            </div>

            <div class="welcome-text">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk mengakses layanan kami</p>
            </div>

            <div id="message" class="message" style="display: none;"></div>

            <form id="registerForm" method="POST">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Buat password Anda" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi password Anda" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="register-btn">Daftar</button>
                
                <!-- <button type="button" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                    Daftar dengan Google
                </button> -->

                <p class="login-link">Sudah punya akun? <a href="login.php">Masuk sekarang</a></p>
            </form>
        </div>

        <!-- Right Side - Background Image -->
        <div class="register-image-section"></div>
    </div>

    <script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const messageDiv = document.getElementById('message');
        
        // Validate password match
        if (password !== confirmPassword) {
            messageDiv.style.display = 'block';
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Password dan Konfirmasi Password tidak cocok!';
            return;
        }

        // Validate password length
        if (password.length < 6) {
            messageDiv.style.display = 'block';
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Password minimal 6 karakter!';
            return;
        }
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('../../handlers/auth/register.php', {
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
