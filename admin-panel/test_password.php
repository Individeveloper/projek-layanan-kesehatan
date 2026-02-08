<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Password Hash</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #3B4C94;
            margin-top: 0;
        }
        .success {
            color: #10B981;
            font-weight: bold;
        }
        .error {
            color: #EF4444;
            font-weight: bold;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .hash {
            word-break: break-all;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>🔐 Test Password Hash untuk Admin</h1>
    
    <?php
    // Password yang ingin di-test
    $plainPassword = 'admin123';
    
    // Hash yang ada di database (dari seed_data.sql)
    $hashInDatabase = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHekrf92qN7tPF7u3q.o7t7bQY0P4A4xPC';
    
    // Generate hash baru
    $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    ?>
    
    <div class="box">
        <h2>📝 Password Info</h2>
        <p><strong>Plain Password:</strong> <code><?php echo $plainPassword; ?></code></p>
        <p><strong>Hash di Database:</strong></p>
        <div class="hash"><?php echo $hashInDatabase; ?></div>
    </div>
    
    <div class="box">
        <h2>✅ Verifikasi Password</h2>
        <?php
        if (password_verify($plainPassword, $hashInDatabase)) {
            echo '<p class="success">✓ Password BENAR! Hash di database valid untuk password "' . $plainPassword . '"</p>';
            echo '<p>Anda bisa login dengan:</p>';
            echo '<ul>';
            echo '<li>Email: <code>admin@heartlinkhospital.id</code></li>';
            echo '<li>Password: <code>' . $plainPassword . '</code></li>';
            echo '</ul>';
        } else {
            echo '<p class="error">✗ Password SALAH! Hash di database tidak cocok dengan "' . $plainPassword . '"</p>';
            echo '<p>Hash baru yang valid:</p>';
            echo '<div class="hash">' . $newHash . '</div>';
            echo '<p><strong>Solusi:</strong> Jalankan query SQL ini di phpMyAdmin:</p>';
            echo '<div class="hash">UPDATE users SET password = \'' . $newHash . '\' WHERE email = \'admin@heartlinkhospital.id\';</div>';
        }
        ?>
    </div>
    
    <div class="box">
        <h2>🔄 Generate Hash Baru</h2>
        <p><strong>Hash Baru (Random):</strong></p>
        <div class="hash"><?php echo $newHash; ?></div>
        <p><small>Setiap kali di-refresh, hash akan berbeda karena salt random, tapi semua valid untuk password yang sama.</small></p>
    </div>
    
    <div class="box">
        <h2>📋 SQL Query untuk Update</h2>
        <p>Copy dan jalankan di phpMyAdmin:</p>
        <div class="hash">
UPDATE users SET password = '<?php echo $newHash; ?>' WHERE email = 'admin@heartlinkhospital.id';
        </div>
    </div>
    
    <div class="box">
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="../admin-panel/login.php" target="_blank">→ Login Admin Panel</a><br>
            <a href="http://localhost/phpmyadmin" target="_blank">→ phpMyAdmin</a>
        </p>
    </div>
</body>
</html>
