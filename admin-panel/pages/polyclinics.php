<?php
session_start();
require_once '../includes/auth.php';

// Only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../../config/connection.php';

$page_title = 'Kelola Poliklinik & Jadwal';
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        
        if (!empty($name)) {
            $stmt = $db->prepare("INSERT INTO polyclinics (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            
            if ($stmt->execute()) {
                $message = 'Poliklinik berhasil ditambahkan';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan poliklinik';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        
        if ($id && !empty($name)) {
            $stmt = $db->prepare("UPDATE polyclinics SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            
            if ($stmt->execute()) {
                $message = 'Poliklinik berhasil diupdate';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupdate poliklinik';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        
        if ($id) {
            $stmt = $db->prepare("DELETE FROM polyclinics WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Poliklinik berhasil dihapus';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus poliklinik';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    // Schedule actions
    elseif ($action === 'add_schedule') {
        $polyclinic_id = $_POST['polyclinic_id'] ?? 0;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $quota = $_POST['quota'] ?? 0;
        
        if ($polyclinic_id && $day_of_week && $start_time && $end_time && $quota) {
            $stmt = $db->prepare("INSERT INTO polyclinic_schedules (polyclinic_id, day_of_week, start_time, end_time, quota) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $polyclinic_id, $day_of_week, $start_time, $end_time, $quota);
            
            if ($stmt->execute()) {
                $message = 'Jadwal berhasil ditambahkan';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan jadwal';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'edit_schedule') {
        $id = $_POST['id'] ?? 0;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $quota = $_POST['quota'] ?? 0;
        
        if ($id && $day_of_week && $start_time && $end_time && $quota) {
            $stmt = $db->prepare("UPDATE polyclinic_schedules SET day_of_week = ?, start_time = ?, end_time = ?, quota = ? WHERE id = ?");
            $stmt->bind_param("sssii", $day_of_week, $start_time, $end_time, $quota, $id);
            
            if ($stmt->execute()) {
                $message = 'Jadwal berhasil diupdate';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupdate jadwal';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'delete_schedule') {
        $id = $_POST['id'] ?? 0;
        
        if ($id) {
            $stmt = $db->prepare("DELETE FROM polyclinic_schedules WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Jadwal berhasil dihapus';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus jadwal';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get all polyclinics with schedule count
$polyclinics = $db->query("
    SELECT p.*, COUNT(ps.id) as schedule_count 
    FROM polyclinics p
    LEFT JOIN polyclinic_schedules ps ON p.id = ps.polyclinic_id
    GROUP BY p.id
    ORDER BY p.name
");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clinic-medical"></i> Daftar Poliklinik</h2>
        <button class="btn btn-primary" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Tambah Poliklinik
        </button>
    </div>
    <div class="card-body">
        <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
            <i class="fas fa-info-circle"></i> Klik tombol <strong>Edit</strong> pada poliklinik untuk mengelola jadwal
        </p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Poliklinik</th>
                        <th>Jumlah Jadwal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($poly = $polyclinics->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $poly['id']; ?></td>
                            <td><?php echo htmlspecialchars($poly['name']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $poly['schedule_count']; ?> Jadwal
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick='editPolyclinic(<?php echo json_encode($poly); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deletePolyclinic(<?php echo $poly['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Poliklinik Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Poliklinik</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Poli Umum" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3>Edit Poliklinik & Jadwal</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Poliklinik</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Tutup</button>
                <button type="submit" class="btn btn-primary">Update Nama</button>
            </div>
        </form>
        
        <div style="padding: 20px; border-top: 2px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0;"><i class="fas fa-calendar-alt"></i> Jadwal Poliklinik</h4>
                <button class="btn btn-sm btn-success" onclick="showAddScheduleForm()">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </button>
            </div>
            
            <!-- Add Schedule Form (hidden by default) -->
            <div id="addScheduleForm" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <form method="POST">
                    <input type="hidden" name="action" value="add_schedule">
                    <input type="hidden" name="polyclinic_id" id="schedule_polyclinic_id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 12px;">Hari</label>
                            <select name="day_of_week" class="form-control" required>
                                <option value="">Pilih Hari</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 12px;">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 12px;">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 12px;">Kuota</label>
                            <input type="number" name="quota" class="form-control" min="1" value="50" required>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="hideAddScheduleForm()">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Schedule List -->
            <div id="scheduleList">
                <p style="text-align: center; color: #999; padding: 20px;">Loading jadwal...</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Jadwal</h3>
            <button class="close-modal" onclick="closeModal('editScheduleModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_schedule">
            <input type="hidden" name="id" id="edit_schedule_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Hari</label>
                    <select name="day_of_week" id="edit_schedule_day" class="form-control" required>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" name="start_time" id="edit_schedule_start" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" name="end_time" id="edit_schedule_end" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kuota Pasien</label>
                    <input type="number" name="quota" id="edit_schedule_quota" class="form-control" min="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editScheduleModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Schedule Form -->
<form id="deleteScheduleForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_schedule">
    <input type="hidden" name="id" id="delete_schedule_id">
</form>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editPolyclinic(poly) {
    document.getElementById('edit_id').value = poly.id;
    document.getElementById('edit_name').value = poly.name;
    document.getElementById('schedule_polyclinic_id').value = poly.id;
    
    // Load schedules for this polyclinic
    loadSchedules(poly.id);
    
    openModal('editModal');
}

function loadSchedules(polyclinicId) {
    const scheduleList = document.getElementById('scheduleList');
    scheduleList.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Loading jadwal...</p>';
    
    fetch(`../api/get_schedules.php?polyclinic_id=${polyclinicId}`)
        .then(response => response.json())
        .then(data => {
            if (data.schedules && data.schedules.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Hari</th><th>Jam</th><th>Kuota</th><th style="width: 100px;">Aksi</th></tr></thead><tbody>';
                
                data.schedules.forEach(schedule => {
                    html += `<tr>
                        <td><strong>${schedule.day_of_week}</strong></td>
                        <td>${schedule.start_time.substring(0,5)} - ${schedule.end_time.substring(0,5)}</td>
                        <td><span class="badge badge-info">${schedule.quota} pasien</span></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick='editSchedule(${JSON.stringify(schedule)})' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSchedule(${schedule.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                scheduleList.innerHTML = html;
            } else {
                scheduleList.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Belum ada jadwal. Klik "Tambah Jadwal" untuk menambahkan.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            scheduleList.innerHTML = '<p style="text-align: center; color: #d9534f; padding: 20px;">Gagal memuat jadwal.</p>';
        });
}

function showAddScheduleForm() {
    document.getElementById('addScheduleForm').style.display = 'block';
}

function hideAddScheduleForm() {
    document.getElementById('addScheduleForm').style.display = 'none';
}

function editSchedule(schedule) {
    document.getElementById('edit_schedule_id').value = schedule.id;
    document.getElementById('edit_schedule_day').value = schedule.day_of_week;
    document.getElementById('edit_schedule_start').value = schedule.start_time;
    document.getElementById('edit_schedule_end').value = schedule.end_time;
    document.getElementById('edit_schedule_quota').value = schedule.quota;
    openModal('editScheduleModal');
}

function deleteSchedule(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        document.getElementById('delete_schedule_id').value = id;
        document.getElementById('deleteScheduleForm').submit();
    }
}

function deletePolyclinic(id) {
    if (confirm('Apakah Anda yakin ingin menghapus poliklinik ini? Jadwal terkait juga akan terhapus.')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
