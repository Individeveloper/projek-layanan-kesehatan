<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu', 'redirect' => 'login.php']);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pasien
$nik = trim($input['nik'] ?? '');
$full_name = trim($input['fullName'] ?? '');
$date_of_birth = trim($input['birthDate'] ?? '');
$gender_input = trim($input['gender'] ?? '');
$address = trim($input['address'] ?? '');
$phone_number = trim($input['phone'] ?? '');

// Ambil data kunjungan
$poli_name = trim($input['poli'] ?? '');
$visit_date = trim($input['visitDate'] ?? '');
$complaint = trim($input['complaint'] ?? '');
$doctor_name = trim($input['doctor'] ?? '');

// Validasi wajib
if (empty($nik) || empty($full_name) || empty($date_of_birth) || empty($gender_input) || 
    empty($address) || empty($phone_number) || empty($poli_name) || empty($visit_date)) {
    echo json_encode(['success' => false, 'message' => 'Lengkapi semua data yang wajib diisi']);
    exit;
}

// Konversi gender
$gender = ($gender_input === 'Laki-laki') ? 'L' : 'P';

// Mulai transaction
$db->begin_transaction();

try {
    // 1. Cek/insert pasien berdasarkan NIK
    $stmt = $db->prepare("SELECT id FROM patients WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Pasien sudah ada, ambil ID
        $patient = $result->fetch_assoc();
        $patient_id = $patient['id'];
        $stmt->close();

        // Update data pasien
        $stmt = $db->prepare("UPDATE patients SET full_name = ?, date_of_birth = ?, gender = ?, address = ?, phone_number = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $full_name, $date_of_birth, $gender, $address, $phone_number, $patient_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
        // Insert pasien baru
        $stmt = $db->prepare("INSERT INTO patients (user_id, nik, full_name, date_of_birth, gender, address, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $nik, $full_name, $date_of_birth, $gender, $address, $phone_number);
        $stmt->execute();
        $patient_id = $db->insert_id;
        $stmt->close();
    }

    // 2. Cari polyclinic dan schedule yang sesuai
    // Cari polyclinic berdasarkan nama
    $stmt = $db->prepare("SELECT id FROM polyclinics WHERE name = ?");
    $stmt->bind_param("s", $poli_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Poliklinik tidak ditemukan');
    }
    $poli = $result->fetch_assoc();
    $polyclinic_id = $poli['id'];
    $stmt->close();

    // Cari hari kunjungan
    $visit_timestamp = strtotime($visit_date);
    $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $day_of_week = $day_names[date('w', $visit_timestamp)];

    // Cari jadwal poli pada hari tersebut
    $stmt = $db->prepare("SELECT id, quota FROM polyclinic_schedules WHERE polyclinic_id = ? AND day_of_week = ?");
    $stmt->bind_param("is", $polyclinic_id, $day_of_week);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Poli tidak buka pada hari ' . $day_of_week);
    }
    $schedule = $result->fetch_assoc();
    $polyclinic_schedule_id = $schedule['id'];
    $quota = $schedule['quota'];
    $stmt->close();

    // 3. Hitung nomor antrian
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM reservations WHERE polyclinic_schedule_id = ? AND reservation_date = ? AND status != 'cancelled'");
    $stmt->bind_param("is", $polyclinic_schedule_id, $visit_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc();
    $current_queue = $count['total'];
    $stmt->close();

    // Cek kuota
    if ($current_queue >= $quota) {
        throw new Exception('Kuota antrian untuk tanggal tersebut sudah penuh (maks ' . $quota . ' pasien)');
    }

    $queue_number = $current_queue + 1;

    // 4. Insert reservasi
    $stmt = $db->prepare("INSERT INTO reservations (user_id, patient_id, polyclinic_schedule_id, reservation_date, queue_number, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiisi", $user_id, $patient_id, $polyclinic_schedule_id, $visit_date, $queue_number);
    $stmt->execute();
    $reservation_id = $db->insert_id;
    $stmt->close();

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil',
        'data' => [
            'reservation_id' => $reservation_id,
            'queue_number' => str_pad($queue_number, 3, '0', STR_PAD_LEFT),
            'patient_name' => $full_name,
            'poli' => $poli_name,
            'doctor' => $doctor_name,
            'visit_date' => $visit_date,
            'timestamp' => date('d/m/Y, H.i.s')
        ]
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$db->close();
?>
