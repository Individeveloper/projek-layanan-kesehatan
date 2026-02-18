<?php
header('Content-Type: application/json');
require_once '../config/connection.php';

// Get parameters
$polyclinic_id = isset($_GET['polyclinic_id']) ? intval($_GET['polyclinic_id']) : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+2 months'));

// Validate parameters
if ($polyclinic_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Polyclinic ID is required'
    ]);
    exit;
}

try {
    // Get all schedules for this polyclinic (day_of_week with quota)
    $stmt = $db->prepare("
        SELECT day_of_week, quota 
        FROM polyclinic_schedules 
        WHERE polyclinic_id = ?
    ");
    $stmt->bind_param("i", $polyclinic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[$row['day_of_week']] = intval($row['quota']);
    }
    $stmt->close();

    if (empty($schedules)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada jadwal untuk poli ini',
            'available_dates' => [],
            'disabled_dates' => []
        ]);
        exit;
    }

    // Indonesian day names mapping
    $day_names = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $available_dates = [];
    $disabled_dates = [];

    // Loop through date range
    $current = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    while ($current <= $end) {
        $dateStr = $current->format('Y-m-d');
        $dayName = $day_names[$current->format('l')];
        
        // Check if this day has a schedule
        if (isset($schedules[$dayName])) {
            $total_quota = $schedules[$dayName];
            
            // Count existing reservations for this date
            $stmt2 = $db->prepare("
                SELECT COUNT(*) as reserved_count 
                FROM reservations r
                JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
                WHERE ps.polyclinic_id = ? 
                AND DATE(r.reservation_date) = ?
                AND r.status != 'cancelled'
            ");
            $stmt2->bind_param("is", $polyclinic_id, $dateStr);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $reservation_data = $result2->fetch_assoc();
            $reserved = intval($reservation_data['reserved_count']);
            $stmt2->close();
            
            $available = $total_quota - $reserved;
            
            if ($available > 0) {
                $available_dates[] = [
                    'date' => $dateStr,
                    'quota' => $total_quota,
                    'reserved' => $reserved,
                    'available' => $available
                ];
            } else {
                // Quota penuh, disable tanggal ini
                $disabled_dates[] = $dateStr;
            }
        } else {
            // Tidak ada jadwal untuk hari ini, disable
            $disabled_dates[] = $dateStr;
        }
        
        $current->modify('+1 day');
    }

    echo json_encode([
        'success' => true,
        'polyclinic_id' => $polyclinic_id,
        'available_dates' => $available_dates,
        'disabled_dates' => $disabled_dates,
        'schedules' => array_keys($schedules)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$db->close();
?>
