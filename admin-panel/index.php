<?php
session_start();
require_once 'includes/auth.php';
require_once '../config/connection.php';

$page_title = 'Dashboard';

// Get statistics
$stats = [];

// Total users
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['users'] = $result->fetch_assoc()['count'];

// Total polyclinics
$result = $db->query("SELECT COUNT(*) as count FROM polyclinics");
$stats['polyclinics'] = $result->fetch_assoc()['count'];

// Total schedules
$result = $db->query("SELECT COUNT(*) as count FROM polyclinic_schedules");
$stats['schedules'] = $result->fetch_assoc()['count'];

// Total reservations
$result = $db->query("SELECT COUNT(*) as count FROM reservations");
$stats['reservations'] = $result->fetch_assoc()['count'];

// Recent reservations
$recent_reservations = $db->query("
    SELECT r.*, p.full_name as patient_name, po.name as polyclinic_name 
    FROM reservations r
    LEFT JOIN patients p ON r.patient_id = p.id
    LEFT JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
    LEFT JOIN polyclinics po ON ps.polyclinic_id = po.id
    ORDER BY r.created_at DESC 
    LIMIT 10
");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon stat-primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['users']); ?></h3>
            <p>Total Pengguna</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-success">
            <i class="fas fa-clinic-medical"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['polyclinics']); ?></h3>
            <p>Poliklinik</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-warning">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['schedules']); ?></h3>
            <p>Jadwal Aktif</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-info">
            <i class="fas fa-file-medical"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['reservations']); ?></h3>
            <p>Total Reservasi</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Reservasi Terbaru</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Nama Pasien</th>
                        <th>Poliklinik</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Status</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_reservations->num_rows > 0): ?>
                        <?php while ($row = $recent_reservations->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['queue_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['polyclinic_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['visit_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada reservasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
