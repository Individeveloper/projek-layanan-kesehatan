<?php
session_start();
require_once 'includes/auth.php';

// Only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/connection.php';

$page_title = 'Kelola Reservasi';
$message = '';
$message_type = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($id && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            $message = 'Status reservasi berhasil diupdate';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate status reservasi';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Get filter
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Build query
$query = "
    SELECT r.*, 
           p.full_name as patient_name, 
           p.nik, 
           p.phone_number,
           po.name as polyclinic_name,
           u.name as user_name,
           u.email as user_email
    FROM reservations r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
    LEFT JOIN polyclinics po ON ps.polyclinic_id = po.id
    LEFT JOIN users u ON r.user_id = u.id
    WHERE 1=1
";

if ($filter_status) {
    $query .= " AND r.status = '" . $db->real_escape_string($filter_status) . "'";
}
if ($filter_date) {
    $query .= " AND DATE(r.visit_date) = '" . $db->real_escape_string($filter_date) . "'";
}

$query .= " ORDER BY r.created_at DESC";

$reservations = $db->query($query);

// Get statistics
$stats = [
    'pending' => $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'],
    'confirmed' => $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'")->fetch_assoc()['count'],
    'completed' => $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'completed'")->fetch_assoc()['count'],
    'cancelled' => $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled'")->fetch_assoc()['count'],
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-mini stat-warning">
        <i class="fas fa-clock"></i>
        <div>
            <h4><?php echo $stats['pending']; ?></h4>
            <p>Pending</p>
        </div>
    </div>
    <div class="stat-mini stat-info">
        <i class="fas fa-check"></i>
        <div>
            <h4><?php echo $stats['confirmed']; ?></h4>
            <p>Confirmed</p>
        </div>
    </div>
    <div class="stat-mini stat-success">
        <i class="fas fa-check-double"></i>
        <div>
            <h4><?php echo $stats['completed']; ?></h4>
            <p>Completed</p>
        </div>
    </div>
    <div class="stat-mini stat-danger">
        <i class="fas fa-times"></i>
        <div>
            <h4><?php echo $stats['cancelled']; ?></h4>
            <p>Cancelled</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-file-medical"></i> Daftar Reservasi</h2>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Kunjungan</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>" onchange="this.form.submit()">
                </div>
                <?php if ($filter_status || $filter_date): ?>
                    <a href="reservations.php" class="btn btn-secondary">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Nama Pasien</th>
                        <th>NIK</th>
                        <th>Telepon</th>
                        <th>Poliklinik</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservations->num_rows > 0): ?>
                        <?php while ($reservation = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($reservation['queue_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($reservation['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['nik']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['polyclinic_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reservation['visit_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='viewReservation(<?php echo json_encode($reservation); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="updateStatus(<?php echo $reservation['id']; ?>, '<?php echo $reservation['status']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data reservasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Detail Reservasi</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" id="reservationDetails">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Status Reservasi</h3>
            <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="status_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status_value" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('statusModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewReservation(reservation) {
    const details = `
        <div class="detail-grid">
            <div class="detail-item">
                <label>No. Antrian</label>
                <p><strong>${reservation.queue_number}</strong></p>
            </div>
            <div class="detail-item">
                <label>Nama Pasien</label>
                <p>${reservation.patient_name}</p>
            </div>
            <div class="detail-item">
                <label>NIK</label>
                <p>${reservation.nik}</p>
            </div>
            <div class="detail-item">
                <label>Telepon</label>
                <p>${reservation.phone_number}</p>
            </div>
            <div class="detail-item">
                <label>Poliklinik</label>
                <p>${reservation.polyclinic_name}</p>
            </div>
            <div class="detail-item">
                <label>Tanggal Kunjungan</label>
                <p>${new Date(reservation.visit_date).toLocaleDateString('id-ID')}</p>
            </div>
            <div class="detail-item">
                <label>Keluhan</label>
                <p>${reservation.complaint || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Nama Dokter</label>
                <p>${reservation.doctor_name || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Status</label>
                <p><span class="badge badge-${reservation.status}">${reservation.status.toUpperCase()}</span></p>
            </div>
            <div class="detail-item">
                <label>User Email</label>
                <p>${reservation.user_email}</p>
            </div>
            <div class="detail-item">
                <label>Dibuat Pada</label>
                <p>${new Date(reservation.created_at).toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;
    
    document.getElementById('reservationDetails').innerHTML = details;
    openModal('viewModal');
}

function updateStatus(id, currentStatus) {
    document.getElementById('status_id').value = id;
    document.getElementById('status_value').value = currentStatus;
    openModal('statusModal');
}
</script>

<?php include 'includes/footer.php'; ?>
