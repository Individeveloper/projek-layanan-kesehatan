<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'patient'; // Default role

    // Validasi input
    if (empty($email) || empty($no_hp) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email, nomor HP, dan password harus diisi'
        ]);
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid'
        ]);
        exit;
    }

    // Validasi nomor HP
    if (strlen($no_hp) < 10 || !preg_match('/^[0-9+\-\s]+$/', $no_hp)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format nomor HP tidak valid'
        ]);
        exit;
    }

    // Validasi panjang password
    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 6 karakter'
        ]);
        exit;
    }

    // Cek apakah email sudah terdaftar
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email sudah terdaftar'
        ]);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    $stmt = $db->prepare("INSERT INTO users (email, no_hp, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $no_hp, $hashed_password, $role);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registrasi gagal: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $db->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak valid'
    ]);
}
?>
