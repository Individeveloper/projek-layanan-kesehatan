<?php
session_start();
require_once 'includes/auth.php';
require_once '../config/connection.php';

header('Content-Type: application/json');

$polyclinic_id = $_GET['polyclinic_id'] ?? 0;

if (!$polyclinic_id) {
    echo json_encode(['success' => false, 'message' => 'Polyclinic ID required']);
    exit;
}

$stmt = $db->prepare("
    SELECT * FROM polyclinic_schedules 
    WHERE polyclinic_id = ? 
    ORDER BY FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
");
$stmt->bind_param("i", $polyclinic_id);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode([
    'success' => true,
    'schedules' => $schedules
]);

$stmt->close();
$db->close();
?>
