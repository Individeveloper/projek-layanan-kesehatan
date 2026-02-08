<?php
session_start();
require_once 'includes/auth.php';
require_once '../config/connection.php';

$page_title = 'Kelola Jadwal';
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
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
    
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $polyclinic_id = $_POST['polyclinic_id'] ?? 0;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $quota = $_POST['quota'] ?? 0;
        
        if ($id && $polyclinic_id && $day_of_week && $start_time && $end_time && $quota) {
            $stmt = $db->prepare("UPDATE polyclinic_schedules SET polyclinic_id = ?, day_of_week = ?, start_time = ?, end_time = ?, quota = ? WHERE id = ?");
            $stmt->bind_param("isssii", $polyclinic_id, $day_of_week, $start_time, $end_time, $quota, $id);
            
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
    
    elseif ($action === 'delete') {
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

// Get all schedules
$schedules = $db->query("
    SELECT ps.*, p.name as polyclinic_name 
    FROM polyclinic_schedules ps
    JOIN polyclinics p ON ps.polyclinic_id = p.id
    ORDER BY p.name, 
    FIELD(ps.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
");

// Get polyclinics for dropdown
$polyclinics = $db->query("SELECT * FROM polyclinics ORDER BY name");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-alt"></i> Daftar Jadwal Poliklinik</h2>
        <button class="btn btn-primary" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poliklinik</th>
                        <th>Hari</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Kuota</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($schedule = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $schedule['id']; ?></td>
                            <td><?php echo htmlspecialchars($schedule['polyclinic_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($schedule['day_of_week']); ?></strong></td>
                            <td><?php echo date('H:i', strtotime($schedule['start_time'])); ?></td>
                            <td><?php echo date('H:i', strtotime($schedule['end_time'])); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $schedule['quota']; ?> pasien
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick='editSchedule(<?php echo json_encode($schedule); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
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
            <h3>Tambah Jadwal Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Poliklinik</label>
                    <select name="polyclinic_id" class="form-control" required>
                        <option value="">Pilih Poliklinik</option>
                        <?php 
                        $polyclinics->data_seek(0);
                        while ($poly = $polyclinics->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $poly['id']; ?>"><?php echo htmlspecialchars($poly['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hari</label>
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
                <div class="form-row">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kuota Pasien</label>
                    <input type="number" name="quota" class="form-control" min="1" required>
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
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Jadwal</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Poliklinik</label>
                    <select name="polyclinic_id" id="edit_polyclinic_id" class="form-control" required>
                        <?php 
                        $polyclinics->data_seek(0);
                        while ($poly = $polyclinics->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $poly['id']; ?>"><?php echo htmlspecialchars($poly['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hari</label>
                    <select name="day_of_week" id="edit_day_of_week" class="form-control" required>
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
                        <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kuota Pasien</label>
                    <input type="number" name="quota" id="edit_quota" class="form-control" min="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editSchedule(schedule) {
    document.getElementById('edit_id').value = schedule.id;
    document.getElementById('edit_polyclinic_id').value = schedule.polyclinic_id;
    document.getElementById('edit_day_of_week').value = schedule.day_of_week;
    document.getElementById('edit_start_time').value = schedule.start_time;
    document.getElementById('edit_end_time').value = schedule.end_time;
    document.getElementById('edit_quota').value = schedule.quota;
    openModal('editModal');
}

function deleteSchedule(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
