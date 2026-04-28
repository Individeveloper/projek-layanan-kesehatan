<?php
header('Content-Type: application/json');
require_once '../config/connection.php';

// Tangkap Nomor RM dari JavaScript
$rm = isset($_GET['rm']) ? trim($_GET['rm']) : '';

if (empty($rm)) {
    echo json_encode(['success' => false, 'message' => 'Nomor RM diperlukan']);
    exit;
}

// Cari data pasien di database berdasarkan Nomor RM
$stmt = $db->prepare("SELECT nik, full_name, date_of_birth, gender, address, phone_number FROM patients WHERE medical_record = ? LIMIT 1");
$stmt->bind_param("s", $rm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Jika ketemu, kirim datanya ke JavaScript
    $patient = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $patient]);
} else {
    // Jika tidak ketemu
    echo json_encode(['success' => false, 'message' => 'Data pasien tidak ditemukan']);
}

$stmt->close();
?>