<?php
require_once '../config/connection.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'polyclinics':
        // Ambil semua poli
        $stmt = $db->prepare("SELECT id, name FROM polyclinics ORDER BY name");
        $stmt->execute();
        $result = $stmt->get_result();
        $polyclinics = [];
        while ($row = $result->fetch_assoc()) {
            $polyclinics[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $polyclinics]);
        $stmt->close();
        break;

    case 'schedules':
        // Ambil jadwal poli (untuk halaman schedule.php)
        $query = "
            SELECT 
                p.id AS polyclinic_id,
                p.name AS poli_name,
                ps.day_of_week,
                ps.start_time,
                ps.end_time,
                ps.quota
            FROM polyclinics p
            LEFT JOIN polyclinic_schedules ps ON p.id = ps.polyclinic_id
            ORDER BY p.id, FIELD(ps.day_of_week, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
        ";
        $result = $db->query($query);
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $schedules]);
        break;

    case 'check_schedule':
        // Cek apakah poli buka pada hari tertentu
        $polyclinic_id = intval($_GET['polyclinic_id'] ?? 0);
        $date = $_GET['date'] ?? '';

        if (empty($polyclinic_id) || empty($date)) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
            break;
        }

        $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $day_of_week = $day_names[date('w', strtotime($date))];

        $stmt = $db->prepare("
            SELECT ps.id, ps.start_time, ps.end_time, ps.quota,
                   (SELECT COUNT(*) FROM reservations r 
                    WHERE r.polyclinic_schedule_id = ps.id 
                    AND r.reservation_date = ? 
                    AND r.status != 'cancelled') as current_queue
            FROM polyclinic_schedules ps
            WHERE ps.polyclinic_id = ? AND ps.day_of_week = ?
        ");
        $stmt->bind_param("sis", $date, $polyclinic_id, $day_of_week);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $schedule = $result->fetch_assoc();
            $available = $schedule['current_queue'] < $schedule['quota'];
            echo json_encode([
                'success' => true,
                'data' => [
                    'is_open' => true,
                    'available' => $available,
                    'remaining_quota' => $schedule['quota'] - $schedule['current_queue'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => ['is_open' => false, 'available' => false]
            ]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
        break;
}

$db->close();
?>
