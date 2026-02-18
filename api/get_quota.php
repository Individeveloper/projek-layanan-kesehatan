<?php
header('Content-Type: application/json');
require_once '../config/connection.php';

// Get parameters
$polyclinic_id = isset($_GET['polyclinic_id']) ? intval($_GET['polyclinic_id']) : 0;
$visit_date = isset($_GET['visit_date']) ? $_GET['visit_date'] : '';

// Validate parameters
if ($polyclinic_id <= 0 || empty($visit_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Polyclinic ID and visit date are required'
    ]);
    exit;
}

try {
    // Get day name in Indonesian
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

    // Check if weekend
    if ($day_of_week === 'Sabtu' || $day_of_week === 'Minggu') {
        echo json_encode([
            'success' => false,
            'message' => 'Hari Sabtu dan Minggu tidak tersedia',
            'quota' => 0,
            'reserved' => 0,
            'available' => 0
        ]);
        exit;
    }

    // Get schedule quota for the day
    $stmt = $db->prepare("
        SELECT quota 
        FROM polyclinic_schedules 
        WHERE polyclinic_id = ? AND day_of_week = ?
        LIMIT 1
    ");
    $stmt->bind_param("is", $polyclinic_id, $day_of_week);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada jadwal untuk poli ini di hari ' . $day_of_week,
            'quota' => 0,
            'reserved' => 0,
            'available' => 0
        ]);
        exit;
    }

    $schedule = $result->fetch_assoc();
    $total_quota = intval($schedule['quota']);

    // Count existing reservations for this date
    $stmt2 = $db->prepare("
        SELECT COUNT(*) as reserved_count 
        FROM reservations r
        JOIN patients p ON r.patient_id = p.id
        JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
        WHERE ps.polyclinic_id = ? 
        AND DATE(r.reservation_date) = ?
        AND r.status != 'cancelled'
    ");
    $stmt2->bind_param("is", $polyclinic_id, $visit_date);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $reservation_data = $result2->fetch_assoc();
    $reserved = intval($reservation_data['reserved_count']);

    // Calculate available slots
    $available = $total_quota - $reserved;
    $available = max(0, $available); // Ensure it's not negative

    echo json_encode([
        'success' => true,
        'quota' => $total_quota,
        'reserved' => $reserved,
        'available' => $available,
        'date' => $visit_date,
        'day_of_week' => $day_of_week
    ]);

    $stmt->close();
    $stmt2->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$db->close();
?>
