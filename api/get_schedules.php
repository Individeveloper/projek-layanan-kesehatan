<?php
header('Content-Type: application/json');
require_once '../config/connection.php';

// Get parameters
$polyclinic_id = isset($_GET['polyclinic_id']) ? intval($_GET['polyclinic_id']) : 0;
$visit_date = isset($_GET['visit_date']) ? $_GET['visit_date'] : '';

// Validate parameters
if ($polyclinic_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Polyclinic ID is required'
    ]);
    exit;
}

// If date is provided, get day name in Indonesian
$day_of_week = null;
if ($visit_date) {
    $date = new DateTime($visit_date);
    $day_names = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    $day_of_week = $day_names[$date->format('l')];
}

// Build query
if ($day_of_week) {
    // Get schedule for specific day
    $stmt = $db->prepare("
        SELECT 
            ps.id,
            ps.day_of_week,
            ps.start_time,
            ps.end_time,
            ps.quota,
            ps.doctor_name,
            p.name as polyclinic_name
        FROM polyclinic_schedules ps
        JOIN polyclinics p ON ps.polyclinic_id = p.id
        WHERE ps.polyclinic_id = ? AND ps.day_of_week = ?
        ORDER BY ps.start_time
    ");
    $stmt->bind_param("is", $polyclinic_id, $day_of_week);
} else {
    // Get all schedules for the polyclinic
    $stmt = $db->prepare("
        SELECT 
            ps.id,
            ps.day_of_week,
            ps.start_time,
            ps.end_time,
            ps.quota,
            ps.doctor_name,
            p.name as polyclinic_name
        FROM polyclinic_schedules ps
        JOIN polyclinics p ON ps.polyclinic_id = p.id
        WHERE ps.polyclinic_id = ?
        ORDER BY FIELD(ps.day_of_week, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), ps.start_time
    ");
    $stmt->bind_param("i", $polyclinic_id);
}

$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = [
        'id' => $row['id'],
        'day_of_week' => $row['day_of_week'],
        'start_time' => substr($row['start_time'], 0, 5), // HH:MM format
        'end_time' => substr($row['end_time'], 0, 5),
        'quota' => $row['quota'],
        'doctor_name' => $row['doctor_name'],
        'polyclinic_name' => $row['polyclinic_name']
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'data' => $schedules
]);
?>
