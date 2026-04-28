<?php
// Pastikan path koneksi database ini benar (sesuaikan dengan struktur foldermu)
require_once '../config/connection.php';

header('Content-Type: application/json');

// Tangkap ID poli yang dikirim dari JavaScript
$polyclinic_id = isset($_GET['polyclinic_id']) ? intval($_GET['polyclinic_id']) : 0;

if ($polyclinic_id > 0) {
    // Ambil dokter yang sesuai dengan poli tersebut
    $query = "SELECT id, name, specialist FROM doctors WHERE polyclinic_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $polyclinic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    // Kembalikan data dalam format JSON
    echo json_encode([
        'status' => 'success',
        'data' => $doctors
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Poli tidak valid'
    ]);
}
?>