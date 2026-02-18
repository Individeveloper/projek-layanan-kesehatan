<?php
session_start();
require_once '../includes/auth.php';
require_once '../../config/connection.php';

$page_title = 'Dashboard';

// Get admin statistics
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

// Pending reservations (waiting for confirmation)
$result = $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
$stats['pending'] = $result->fetch_assoc()['count'];

// Confirmed reservations (ready to be called)
$result = $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'");
$stats['confirmed'] = $result->fetch_assoc()['count'];

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

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ADMIN DASHBOARD -->
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
            <i class="fas fa-list-ol"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['reservations']); ?></h3>
            <p>Total Antrian</p>
        </div>
    </div>
</div>

<!-- Queue Summary Stats -->
<div class="stats-row" style="margin-top: 20px;">
    <div class="stat-mini stat-warning">
        <i class="fas fa-clock"></i>
        <div>
            <h4><?php echo $stats['pending']; ?></h4>
            <p>Menunggu Konfirmasi</p>
        </div>
    </div>
    <div class="stat-mini stat-success">
        <i class="fas fa-check-circle"></i>
        <div>
            <h4><?php echo $stats['confirmed']; ?></h4>
            <p>Siap Dipanggil</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list-ol"></i> Antrian Terbaru</h2>
        <a href="reservations.php" class="btn btn-sm btn-primary">
            <i class="fas fa-eye"></i> Lihat Semua Antrian
        </a>
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
                                <td><strong><?php echo str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['polyclinic_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['reservation_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'pending' => 'Menunggu',
                                            'confirmed' => 'Dikonfirmasi',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_labels[$row['status']] ?? ucfirst($row['status']); 
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada antrian</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
