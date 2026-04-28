<?php
/**
 * API untuk fetch data pasien berdasarkan medical record
 * GET /api/get_patient_by_medical_record.php?medical_record=RMxxx
 */

session_start();
require_once '../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$medical_record = trim($_GET['medical_record'] ?? '');

if (empty($medical_record)) {
    echo json_encode(['success' => false, 'message' => 'Medical record tidak boleh kosong']);
    exit;
}

try {
    // Fetch patient data berdasarkan medical record
    $stmt = $db->prepare("SELECT id, nik, full_name, date_of_birth, gender, address, phone_number FROM patients WHERE medical_record = ?");
    $stmt->bind_param("s", $medical_record);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Data pasien tidak ditemukan']);
        exit;
    }

    $patient = $result->fetch_assoc();
    $stmt->close();

    // Convert gender untuk display
    $gender_display = ($patient['gender'] === 'L') ? 'Laki-laki' : 'Perempuan';

    echo json_encode([
        'success' => true,
        'data' => [
            'nik' => $patient['nik'],
            'fullName' => $patient['full_name'],
            'birthDate' => $patient['date_of_birth'],
            'gender' => $gender_display,
            'address' => $patient['address'],
            'phone' => $patient['phone_number']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$db->close();
?>
