<?php
session_start();
require_once 'includes/auth.php';

// Only doctors can access this page
if (strpos($_SESSION['role'], 'doctor-') !== 0) {
    header('Location: index.php');
    exit;
}

require_once '../config/connection.php';

// Get doctor's polyclinic from role (e.g., 'doctor-umum' -> 'Poli Umum')
$doctor_role = $_SESSION['role'];
$poli_type = str_replace('doctor-', '', $doctor_role);
$poli_mapping = [
    'umum' => 'Poli Umum',
    'gigi' => 'Poli Gigi',
    'mata' => 'Poli Mata',
    'saraf' => 'Poli Saraf',
    'jantung' => 'Poli Jantung',
    'anak' => 'Poli Anak'
];
$doctor_polyclinic = $poli_mapping[$poli_type] ?? null;

if (!$doctor_polyclinic) {
    die('Invalid doctor role');
}

$page_title = 'Rekam Medis Pasien';
$message = '';
$message_type = '';

// Handle add/update medical record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_record') {
        $reservation_id = $_POST['reservation_id'] ?? 0;
        $patient_id = $_POST['patient_id'] ?? 0;
        $doctor_name = $_POST['doctor_name'] ?? '';
        $symptoms = $_POST['symptoms'] ?? '';
        $diagnosis = $_POST['diagnosis'] ?? '';
        $treatment = $_POST['treatment'] ?? '';
        $prescription = $_POST['prescription'] ?? '';
        
        // Check if record exists
        $check = $db->prepare("SELECT id FROM medical_records WHERE reservation_id = ?");
        $check->bind_param("i", $reservation_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
        
        if ($exists) {
            // Update existing record
            $stmt = $db->prepare("UPDATE medical_records SET doctor_name = ?, symptoms = ?, diagnosis = ?, treatment = ?, prescription = ? WHERE reservation_id = ?");
            $stmt->bind_param("sssssi", $doctor_name, $symptoms, $diagnosis, $treatment, $prescription, $reservation_id);
        } else {
            // Insert new record
            $stmt = $db->prepare("INSERT INTO medical_records (reservation_id, patient_id, doctor_name, symptoms, diagnosis, treatment, prescription) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $reservation_id, $patient_id, $doctor_name, $symptoms, $diagnosis, $treatment, $prescription);
        }
        
        if ($stmt->execute()) {
            // Update reservation status to completed
            $update_status = $db->prepare("UPDATE reservations SET status = 'completed' WHERE id = ?");
            $update_status->bind_param("i", $reservation_id);
            $update_status->execute();
            $update_status->close();
            
            $message = $exists ? 'Rekam medis berhasil diupdate' : 'Rekam medis berhasil ditambahkan';
            $message_type = 'success';
        } else {
            $message = 'Gagal menyimpan rekam medis';
            $message_type = 'error';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete_record') {
        $id = $_POST['id'] ?? 0;
        
        if ($id) {
            $stmt = $db->prepare("DELETE FROM medical_records WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Rekam medis berhasil dihapus';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus rekam medis';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get filter
$filter_date = $_GET['date'] ?? '';
$filter_patient = $_GET['patient'] ?? '';

// Get reservations for medical records (confirmed/completed only)
// Filter by doctor's polyclinic
$query = "
    SELECT r.*, 
           p.full_name as patient_name, 
           p.nik, 
           p.phone_number,
           p.date_of_birth,
           p.gender,
           p.address,
           po.name as polyclinic_name,
           ps.day_of_week,
           ps.start_time,
           ps.end_time,
           mr.id as record_id,
           mr.doctor_name,
           mr.symptoms,
           mr.diagnosis,
           mr.treatment,
           mr.prescription,
           mr.created_at as record_date
    FROM reservations r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
    LEFT JOIN polyclinics po ON ps.polyclinic_id = po.id
    LEFT JOIN medical_records mr ON r.id = mr.reservation_id
    WHERE r.status IN ('confirmed', 'completed')
    AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
";

if ($filter_date) {
    $query .= " AND DATE(r.reservation_date) = '" . $db->real_escape_string($filter_date) . "'";
}
if ($filter_patient) {
    $query .= " AND (p.full_name LIKE '%" . $db->real_escape_string($filter_patient) . "%' OR p.nik LIKE '%" . $db->real_escape_string($filter_patient) . "%')";
}

$query .= " ORDER BY r.reservation_date DESC, r.queue_number ASC";

$reservations = $db->query($query);

// Get statistics - filtered by doctor's polyclinic
$stats = [
    'total_records' => $db->query("
        SELECT COUNT(*) as count 
        FROM medical_records mr
        JOIN reservations r ON mr.reservation_id = r.id
        JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
        JOIN polyclinics po ON ps.polyclinic_id = po.id
        WHERE po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
    ")->fetch_assoc()['count'],
    'today_records' => $db->query("
        SELECT COUNT(*) as count 
        FROM medical_records mr
        JOIN reservations r ON mr.reservation_id = r.id
        JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
        JOIN polyclinics po ON ps.polyclinic_id = po.id
        WHERE DATE(mr.created_at) = CURDATE()
        AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
    ")->fetch_assoc()['count'],
    'pending_checkup' => $db->query("
        SELECT COUNT(*) as count 
        FROM reservations r
        JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
        JOIN polyclinics po ON ps.polyclinic_id = po.id
        WHERE r.status = 'confirmed' 
        AND r.id NOT IN (SELECT reservation_id FROM medical_records)
        AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
    ")->fetch_assoc()['count'],
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Doctor Polyclinic Info -->
<div class="alert alert-info" style="margin-bottom: 20px;">
    <i class="fas fa-clinic-medical"></i>
    <strong>Anda sedang mengelola: <?php echo htmlspecialchars($doctor_polyclinic); ?></strong>
    <p style="margin: 5px 0 0 0; font-size: 0.9em;">Anda hanya dapat melihat dan mengelola rekam medis pasien di <?php echo htmlspecialchars($doctor_polyclinic); ?></p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-mini stat-primary">
        <i class="fas fa-file-medical"></i>
        <div>
            <h4><?php echo $stats['total_records']; ?></h4>
            <p>Total Rekam Medis</p>
        </div>
    </div>
    <div class="stat-mini stat-success">
        <i class="fas fa-calendar-day"></i>
        <div>
            <h4><?php echo $stats['today_records']; ?></h4>
            <p>Rekam Medis Hari Ini</p>
        </div>
    </div>
    <div class="stat-mini stat-warning">
        <i class="fas fa-user-injured"></i>
        <div>
            <h4><?php echo $stats['pending_checkup']; ?></h4>
            <p>Menunggu Pemeriksaan</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-notes-medical"></i> Daftar Pasien & Rekam Medis</h3>
        <form method="GET" class="filter-form">
            <input type="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" placeholder="Filter tanggal">
            <input type="text" name="patient" value="<?php echo htmlspecialchars($filter_patient); ?>" placeholder="Cari nama/NIK pasien">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
            <?php if ($filter_date || $filter_patient): ?>
                <a href="medical_records.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Antrian</th>
                        <th>Pasien</th>
                        <th>Poliklinik</th>
                        <th>Status Rekam Medis</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservations && $reservations->num_rows > 0): ?>
                        <?php while ($row = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['reservation_date'])); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['patient_name']); ?></strong><br>
                                    <small class="text-muted">NIK: <?php echo htmlspecialchars($row['nik']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['polyclinic_name']); ?></td>
                                <td>
                                    <?php if ($row['record_id']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Sudah Diperiksa
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($row['record_date'])); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Belum Diperiksa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" onclick="openRecordModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-<?php echo $row['record_id'] ? 'edit' : 'plus'; ?>"></i>
                                        <?php echo $row['record_id'] ? 'Edit' : 'Tambah'; ?> Rekam Medis
                                    </button>
                                    <?php if ($row['record_id']): ?>
                                        <button class="btn btn-sm btn-info" onclick="viewRecordModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data reservasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Medical Record -->
<div id="recordModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-notes-medical"></i> <span id="modalTitle">Tambah Rekam Medis</span></h3>
            <button class="close-modal" onclick="closeRecordModal()">&times;</button>
        </div>
        <form method="POST" id="recordForm">
            <input type="hidden" name="action" value="save_record">
            <input type="hidden" name="reservation_id" id="reservation_id">
            <input type="hidden" name="patient_id" id="patient_id">
            
            <div class="modal-body">
                <!-- Patient Info -->
                <div class="info-section">
                    <h4><i class="fas fa-user"></i> Informasi Pasien</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" id="view_patient_name" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>NIK</label>
                            <input type="text" id="view_nik" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="text" id="view_dob" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <input type="text" id="view_gender" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" id="view_phone" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea id="view_address" class="form-control" rows="2" readonly></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Poliklinik</label>
                            <input type="text" id="view_polyclinic" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Kunjungan</label>
                            <input type="text" id="view_visit_date" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Medical Record Form -->
                <div class="info-section">
                    <h4><i class="fas fa-stethoscope"></i> Catatan Rekam Medis</h4>
                    <div class="form-group">
                        <label for="doctor_name">Nama Dokter <span class="text-danger">*</span></label>
                        <input type="text" name="doctor_name" id="doctor_name" class="form-control" required placeholder="Masukkan nama dokter pemeriksa">
                    </div>
                    
                    <div class="form-group">
                        <label for="symptoms">Keluhan / Gejala</label>
                        <textarea name="symptoms" id="symptoms" class="form-control" rows="3" placeholder="Keluhan pasien saat datang..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis <span class="text-danger">*</span></label>
                        <textarea name="diagnosis" id="diagnosis" class="form-control" rows="3" required placeholder="Diagnosis medis..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="treatment">Tindakan / Terapi</label>
                        <textarea name="treatment" id="treatment" class="form-control" rows="3" placeholder="Tindakan medis yang dilakukan..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="prescription">Resep Obat</label>
                        <textarea name="prescription" id="prescription" class="form-control" rows="4" placeholder="Daftar obat yang diresepkan..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRecordModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Rekam Medis
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for View Medical Record -->
<div id="viewModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-file-medical-alt"></i> Detail Rekam Medis Pasien</h3>
            <button class="close-modal" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Patient Info -->
            <div class="info-section">
                <h4><i class="fas fa-user"></i> Informasi Pasien</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <p id="detail_patient_name"></p>
                    </div>
                    <div class="info-item">
                        <label>NIK</label>
                        <p id="detail_nik"></p>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Lahir</label>
                        <p id="detail_dob"></p>
                    </div>
                    <div class="info-item">
                        <label>Jenis Kelamin</label>
                        <p id="detail_gender"></p>
                    </div>
                    <div class="info-item">
                        <label>Telepon</label>
                        <p id="detail_phone"></p>
                    </div>
                    <div class="info-item">
                        <label>Poliklinik</label>
                        <p id="detail_polyclinic"></p>
                    </div>
                </div>
                <div class="info-item">
                    <label>Alamat</label>
                    <p id="detail_address"></p>
                </div>
            </div>
            
            <hr>
            
            <!-- Medical Record -->
            <div class="info-section">
                <h4><i class="fas fa-stethoscope"></i> Catatan Rekam Medis</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Tanggal Pemeriksaan</label>
                        <p id="detail_record_date"></p>
                    </div>
                    <div class="info-item">
                        <label>Dokter Pemeriksa</label>
                        <p id="detail_doctor_name"></p>
                    </div>
                </div>
                <div class="info-item">
                    <label>Keluhan / Gejala</label>
                    <p id="detail_symptoms"></p>
                </div>
                <div class="info-item">
                    <label>Diagnosis</label>
                    <p id="detail_diagnosis" class="highlight-text"></p>
                </div>
                <div class="info-item">
                    <label>Tindakan / Terapi</label>
                    <p id="detail_treatment"></p>
                </div>
                <div class="info-item">
                    <label>Resep Obat</label>
                    <p id="detail_prescription"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Tutup</button>
            <button type="button" class="btn btn-primary" onclick="printRecord()">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 2% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-lg {
    max-width: 900px;
}

.modal-header {
    padding: 20px 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.close-modal:hover {
    background-color: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 25px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.info-section {
    margin-bottom: 20px;
}

.info-section h4 {
    margin-bottom: 15px;
    color: #667eea;
    font-size: 1.1rem;
    padding-bottom: 8px;
    border-bottom: 2px solid #e0e0e0;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.info-item p {
    margin: 0;
    padding: 8px 12px;
    background-color: #f8f9fa;
    border-radius: 4px;
    min-height: 38px;
    white-space: pre-wrap;
}

.highlight-text {
    background-color: #fff3cd;
    border-left: 3px solid #ffc107;
}

.filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-form input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

hr {
    border: none;
    border-top: 1px solid #e0e0e0;
    margin: 20px 0;
}
</style>

<script>
function openRecordModal(data) {
    document.getElementById('modalTitle').textContent = data.record_id ? 'Edit Rekam Medis' : 'Tambah Rekam Medis';
    document.getElementById('reservation_id').value = data.id;
    document.getElementById('patient_id').value = data.patient_id;
    
    // Patient info
    document.getElementById('view_patient_name').value = data.patient_name;
    document.getElementById('view_nik').value = data.nik;
    document.getElementById('view_dob').value = formatDate(data.date_of_birth);
    document.getElementById('view_gender').value = data.gender === 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('view_phone').value = data.phone_number || '-';
    document.getElementById('view_address').value = data.address || '-';
    document.getElementById('view_polyclinic').value = data.polyclinic_name;
    document.getElementById('view_visit_date').value = formatDate(data.reservation_date);
    
    // Medical record data (if editing)
    document.getElementById('doctor_name').value = data.doctor_name || '';
    document.getElementById('symptoms').value = data.symptoms || '';
    document.getElementById('diagnosis').value = data.diagnosis || '';
    document.getElementById('treatment').value = data.treatment || '';
    document.getElementById('prescription').value = data.prescription || '';
    
    document.getElementById('recordModal').style.display = 'block';
}

function closeRecordModal() {
    document.getElementById('recordModal').style.display = 'none';
    document.getElementById('recordForm').reset();
}

function viewRecordModal(data) {
    // Patient info
    document.getElementById('detail_patient_name').textContent = data.patient_name;
    document.getElementById('detail_nik').textContent = data.nik;
    document.getElementById('detail_dob').textContent = formatDate(data.date_of_birth);
    document.getElementById('detail_gender').textContent = data.gender === 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('detail_phone').textContent = data.phone_number || '-';
    document.getElementById('detail_address').textContent = data.address || '-';
    document.getElementById('detail_polyclinic').textContent = data.polyclinic_name;
    
    // Medical record
    document.getElementById('detail_record_date').textContent = formatDateTime(data.record_date);
    document.getElementById('detail_doctor_name').textContent = data.doctor_name || '-';
    document.getElementById('detail_symptoms').textContent = data.symptoms || '-';
    document.getElementById('detail_diagnosis').textContent = data.diagnosis || '-';
    document.getElementById('detail_treatment').textContent = data.treatment || '-';
    document.getElementById('detail_prescription').textContent = data.prescription || '-';
    
    document.getElementById('viewModal').style.display = 'block';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function printRecord() {
    window.print();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const recordModal = document.getElementById('recordModal');
    const viewModal = document.getElementById('viewModal');
    if (event.target == recordModal) {
        closeRecordModal();
    }
    if (event.target == viewModal) {
        closeViewModal();
    }
}
</script>

<?php
include 'includes/footer.php';
?>
