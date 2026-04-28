<?php
session_start();
require_once '../config/connection.php';

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
$complaint = trim($input['complaint'] ?? ''); // Disimpan ke medical_records nantinya jika ada
$doctor_name = trim($input['doctor'] ?? '');
$medical_record = trim($input['medicalRecord'] ?? '');

// Ambil data rujukan (Hanya diambil dari form, tapi TIDAK disimpan ke database jika tabel tidak punya kolomnya)
$has_referral = isset($input['hasReferral']) ? (bool)$input['hasReferral'] : false;
// Note: Kita abaikan $referral_number dkk saat proses INSERT ke tabel reservations jika tabelnya belum punya kolom tersebut.

// Validasi wajib
if (empty($nik) || empty($full_name) || empty($date_of_birth) || empty($gender_input) || 
    empty($address) || empty($phone_number) || empty($poli_name) || empty($visit_date) || empty($doctor_name)) {
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
        $patient = $result->fetch_assoc();
        $patient_id = $patient['id'];
        $stmt->close();

        // PERBAIKAN: Tambahkan medical_record di UPDATE
        $stmt = $db->prepare("UPDATE patients SET medical_record = ?, full_name = ?, date_of_birth = ?, gender = ?, address = ?, phone_number = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $medical_record, $full_name, $date_of_birth, $gender, $address, $phone_number, $patient_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
        // PERBAIKAN: Tambahkan medical_record di INSERT
        $stmt = $db->prepare("INSERT INTO patients (user_id, medical_record, nik, full_name, date_of_birth, gender, address, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $medical_record, $nik, $full_name, $date_of_birth, $gender, $address, $phone_number);
        $stmt->execute();
        $patient_id = $db->insert_id;
        $stmt->close();
    }

    // 2. Cari polyclinic dan schedule yang sesuai
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

    $visit_timestamp = strtotime($visit_date);
    $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $day_of_week = $day_names[date('w', $visit_timestamp)];

    // PERBAIKAN PENTING: Cari jadwal poli yang spesifik berdasarkan nama dokter yang dipilih
    $stmt = $db->prepare("
        SELECT ps.id, ps.quota 
        FROM polyclinic_schedules ps
        JOIN doctors d ON ps.doctor_id = d.id
        WHERE ps.polyclinic_id = ? AND ps.day_of_week = ? AND d.name = ?
        LIMIT 1
    ");
    $stmt->bind_param("iss", $polyclinic_id, $day_of_week, $doctor_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Jadwal $doctor_name tidak ditemukan pada hari $day_of_week");
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

    if ($current_queue >= $quota) {
        throw new Exception('Kuota antrian untuk tanggal tersebut sudah penuh (maks ' . $quota . ' pasien)');
    }

    $queue_number = $current_queue + 1;

    // 4. Insert reservasi (PERBAIKAN PENTING: Disesuaikan dengan struktur tabelmu)
    // Berdasarkan screenshot struktur tabelmu, tidak ada kolom referral, referral_date, dll.
    $stmt = $db->prepare("INSERT INTO reservations (user_id, patient_id, polyclinic_schedule_id, reservation_date, queue_number, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiisi", $user_id, $patient_id, $polyclinic_schedule_id, $visit_date, $queue_number);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan reservasi: ' . $stmt->error);
    }
    $reservation_id = $db->insert_id;
    $stmt->close();

    // Opsional: Insert keluhan ke medical_records jika diperlukan
    // $stmt = $db->prepare("INSERT INTO medical_records (reservation_id, patient_id, symptoms) VALUES (?, ?, ?)");
    // $stmt->bind_param("iis", $reservation_id, $patient_id, $complaint);
    // $stmt->execute();

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