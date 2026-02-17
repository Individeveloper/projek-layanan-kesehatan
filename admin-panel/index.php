<?php
session_start();
require_once 'includes/auth.php';
require_once '../config/connection.php';

$page_title = 'Dashboard';

// Get statistics based on role
$stats = [];

if ($_SESSION['role'] === 'admin') {
    // Admin statistics
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
} elseif (strpos($_SESSION['role'], 'doctor-') === 0) {
    // Doctor statistics - filtered by polyclinic
    $poli_type = str_replace('doctor-', '', $_SESSION['role']);
    $poli_mapping = [
        'umum' => 'Poli Umum',
        'gigi' => 'Poli Gigi',
        'mata' => 'Poli Mata',
        'saraf' => 'Poli Saraf',
        'jantung' => 'Poli Jantung',
        'anak' => 'Poli Anak'
    ];
    $doctor_polyclinic = $poli_mapping[$poli_type] ?? null;
    
    if ($doctor_polyclinic) {
        // Total medical records for this polyclinic
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM medical_records mr
            JOIN reservations r ON mr.reservation_id = r.id
            JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
            JOIN polyclinics po ON ps.polyclinic_id = po.id
            WHERE po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
        ");
        $stats['total_records'] = $result->fetch_assoc()['count'];
        
        // Today's records
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM medical_records mr
            JOIN reservations r ON mr.reservation_id = r.id
            JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
            JOIN polyclinics po ON ps.polyclinic_id = po.id
            WHERE DATE(mr.created_at) = CURDATE()
            AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
        ");
        $stats['today_records'] = $result->fetch_assoc()['count'];
        
        // Pending checkup
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM reservations r
            JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
            JOIN polyclinics po ON ps.polyclinic_id = po.id
            WHERE r.status = 'confirmed' 
            AND r.id NOT IN (SELECT reservation_id FROM medical_records)
            AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
        ");
        $stats['pending_checkup'] = $result->fetch_assoc()['count'];
        
        // This week's records
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM medical_records mr
            JOIN reservations r ON mr.reservation_id = r.id
            JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
            JOIN polyclinics po ON ps.polyclinic_id = po.id
            WHERE YEARWEEK(mr.created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
        ");
        $stats['week_records'] = $result->fetch_assoc()['count'];
        
        // Recent medical records for this polyclinic
        $recent_records = $db->query("
            SELECT mr.*, p.full_name as patient_name, po.name as polyclinic_name, r.reservation_date
            FROM medical_records mr
            LEFT JOIN patients p ON mr.patient_id = p.id
            LEFT JOIN reservations r ON mr.reservation_id = r.id
            LEFT JOIN polyclinic_schedules ps ON r.polyclinic_schedule_id = ps.id
            LEFT JOIN polyclinics po ON ps.polyclinic_id = po.id
            WHERE po.name = '" . $db->real_escape_string($doctor_polyclinic) . "'
            ORDER BY mr.created_at DESC 
            LIMIT 10
        ");
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($_SESSION['role'] === 'admin'): ?>
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
                                    <td><strong><?php echo str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['polyclinic_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['reservation_date'])); ?></td>
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

<?php else: ?>
    <!-- DOCTOR DASHBOARD -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon stat-primary">
                <i class="fas fa-file-medical"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_records']); ?></h3>
                <p>Total Rekam Medis</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-success">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['today_records']); ?></h3>
                <p>Rekam Medis Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-warning">
                <i class="fas fa-user-injured"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['pending_checkup']); ?></h3>
                <p>Menunggu Pemeriksaan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-info">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['week_records']); ?></h3>
                <p>Rekam Medis Minggu Ini</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-notes-medical"></i> Rekam Medis Terbaru</h2>
            <a href="medical_records.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Rekam Medis
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal Periksa</th>
                            <th>Nama Pasien</th>
                            <th>Poliklinik</th>
                            <th>Dokter</th>
                            <th>Diagnosis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_records->num_rows > 0): ?>
                            <?php while ($row = $recent_records->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['polyclinic_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['diagnosis'], 0, 50)) . (strlen($row['diagnosis']) > 50 ? '...' : ''); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada rekam medis</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
