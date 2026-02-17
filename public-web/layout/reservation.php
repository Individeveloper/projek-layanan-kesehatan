<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Online - Heartlink Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/reservation.css">
</head>
<body>
    <?php 
    session_start();
    
    // Redirect admin and doctor to admin panel
    if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || strpos($_SESSION['role'], 'doctor-') === 0)) {
        header('Location: ../../admin-panel/index.php');
        exit;
    }
    
    // Cek apakah user sudah login, jika belum redirect ke login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    require_once '../../config/connection.php';
    include 'component/navbar.php';
    
    // Ambil data poli dari database
    $poliResult = $db->query("SELECT id, name FROM polyclinics ORDER BY name");
    $polyclinics = [];
    while ($row = $poliResult->fetch_assoc()) {
        $polyclinics[] = $row;
    }
    
    // Cek apakah user sudah login
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>

    <main class="reservation-container">
        <!-- Progress Stepper -->
        <div class="progress-stepper">
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <span class="step-label">Data Pasien</span>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <span class="step-label">Pilih Poli</span>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <span class="step-label">Data Kunjungan</span>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <span class="step-label">Selesai</span>
            </div>
        </div>

        <!-- Step 1: Data Pasien -->
        <div class="step-content active" id="step1">
            <div class="form-card">
                <h2 class="form-title">Data Diri Pasien</h2>
                
                <div class="patient-status-section">
                    <label class="section-label">Status Pasien</label>
                    <div class="status-cards">
                        <div class="status-card active" data-status="new">
                            <i class="fas fa-user"></i>
                            <h3>Pasien Baru</h3>
                            <p>Belum pernah berobat di sini</p>
                        </div>
                        <div class="status-card" data-status="existing">
                            <i class="fas fa-file-alt"></i>
                            <h3>Sudah Pernah Daftar</h3>
                            <p>pernah berobat di sini</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Record Display (for new patients - auto generated) -->
                <div class="rm-display-section" id="rmDisplaySection">
                    <label class="section-label">Nomor Rekam Medis (Otomatis)</label>
                    <div class="rm-display-card">
                        <i class="fas fa-id-card"></i>
                        <div class="rm-number" id="rmNumberDisplay">RM12345678</div>
                    </div>
                </div>

                <!-- Medical Record Input (for existing patients - manual input) -->
                <div class="form-group" id="rmInputSection" style="display: none;">
                    <label for="medicalRecord">No Rekam Medis<span class="required">*</span></label>
                    <input type="text" id="medicalRecord" name="medicalRecord" placeholder="Masukan nomor rekam medis" required>
                </div>

                <div class="form-group">
                    <label for="fullName">Nama Lengkap<span class="required">*</span></label>
                    <input type="text" id="fullName" name="fullName" placeholder="Masukan nama lengkap" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nik">NIK<span class="required">*</span></label>
                        <input type="text" id="nik" name="nik" placeholder="16 digit NIK" maxlength="16" required>
                    </div>
                    <div class="form-group">
                        <label for="birthDate">Tanggal Lahir<span class="required">*</span></label>
                        <input type="date" id="birthDate" name="birthDate" placeholder="dd/mm/yy" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin<span class="required">*</span></label>
                    <select id="gender" name="gender" required>
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Alamat<span class="required">*</span></label>
                    <textarea id="address" name="address" placeholder="Masukan alamat lengkap" rows="3" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">No HP<span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="08xxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email<span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="contoh@gmail.com" required>
                    </div>
                </div>

                <button class="btn-primary" onclick="nextStep(2)">Simpan & Lanjut</button>
            </div>
        </div>

        <!-- Step 2: Pilih Poli -->
        <div class="step-content" id="step2">
            <div class="form-card">
                <h2 class="form-title">Pilih Poli</h2>
                
                <div class="form-group">
                    <label for="poli">Pilih Poliklinik<span class="required">*</span></label>
                    <select id="poli" name="poli" required>
                        <option value="">--Pilih Poli--</option>
                        <?php foreach ($polyclinics as $poli): ?>
                            <option value="<?php echo htmlspecialchars($poli['name']); ?>" data-id="<?php echo $poli['id']; ?>">
                                <?php echo htmlspecialchars($poli['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button class="btn-secondary" onclick="prevStep(1)">Kembali</button>
                    <button class="btn-primary" onclick="nextStep(3)">Lanjut</button>
                </div>
            </div>
        </div>

        <!-- Step 3: Data Kunjungan -->
        <div class="step-content" id="step3">
            <div class="form-card">
                <h2 class="form-title">Data Kunjungan</h2>
                <p class="form-subtitle">Lengkapi informasi kunjungan Anda</p>

                <div class="selected-poli-display">
                    <label class="section-label">Poliklinik yang dipilih:</label>
                    <div class="poli-badge" id="selectedPoliDisplay">Poli Umum</div>
                </div>

                <div class="form-group">
                    <label for="complaint">Keluhan Utama<span class="required">*</span></label>
                    <textarea id="complaint" name="complaint" placeholder="Jelaskan keluhan kesehatan anda" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="visitDate">Tanggal Kunjungan<span class="required">*</span></label>
                    <input type="date" id="visitDate" name="visitDate" placeholder="dd/mm/yy" required>
                </div>

                <div class="form-group">
                    <label for="doctor">Pilih Dokter<span class="required">*</span></label>
                    <select id="doctor" name="doctor" required>
                        <option value="">---PILIH DOKTER---</option>
                        <option value="dr. Arief Pratama">dr. Arief Pratama</option>
                        <option value="dr. Aya Putri">dr. Aya Putri</option>
                        <option value="dr. Dimas Arjuna, Sp.A">dr. Dimas Arjuna, Sp.A</option>
                        <option value="dr. Clara Wijaya, Sp.JP">dr. Clara Wijaya, Sp.JP</option>
                    </select>
                </div>

                <div class="payment-section">
                    <label class="section-label">Metode Pembayaran<span class="required">*</span></label>
                    <div class="payment-cards">
                        <div class="payment-card active" data-payment="umum">
                            <i class="fas fa-money-bill-wave"></i>
                            <h3>Umum</h3>
                            <p>Pembayaran Pribadi</p>
                        </div>
                        <div class="payment-card" data-payment="bpjs">
                            <i class="fas fa-id-card"></i>
                            <h3>BPJS</h3>
                            <p>Jaminan Kesehatan</p>
                        </div>
                    </div>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-content">
                        <strong>Catatan Penting</strong>
                        <p>Mohon datang 15 menit sebelum jadwal kunjungan dan membawa kartu identitas serta kartu BPJS (jika menggunakan BPJS).</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-secondary" onclick="prevStep(2)">Kembali</button>
                    <button class="btn-primary" onclick="submitReservation()">Daftar Antrian</button>
                </div>
            </div>
        </div>

        <!-- Step 4: Selesai / Confirmation -->
        <div class="step-content" id="step4">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="success-title">Pendaftaran berhasil!</h2>
                <p class="success-subtitle">Nomor antrian Anda telah dibuat</p>

                <div class="queue-card">
                    <div class="queue-header">
                        <p>Nomor antrian anda</p>
                    </div>
                    <div class="queue-number" id="queueNumber">078</div>
                </div>

                <div class="details-card">
                    <div class="detail-row">
                        <span class="detail-label">Nama pasien</span>
                        <span class="detail-value" id="confirmName">Jessica Mila</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nomor rekam medis</span>
                        <span class="detail-value" id="confirmRM">RM12345678</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Poli tujuan</span>
                        <span class="detail-value" id="confirmPoli">Poli umum</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nama dokter</span>
                        <span class="detail-value" id="confirmDoctor">Dr. Alya Putri</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tanggal kunjungan</span>
                        <span class="detail-value" id="confirmDate">Minggu, 15 Februari 2026</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran</span>
                        <span class="detail-value" id="confirmPayment">Umum</span>
                    </div>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-content">
                        <strong>Instruksi Penting</strong>
                        <p>Silakan tunjukkan nomor antrian ini ke bagian administrasi rumah sakit. Mohon datang 15 menit sebelum jadwal kunjungan Anda.</p>
                    </div>
                </div>

                <p class="timestamp">Dibuat pada: <span id="timestamp">7/2/2026, 17.48.22</span></p>

                <button class="btn-download" onclick="downloadReceipt()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </main>

    <script>
        const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const USER_ID = <?php echo $isLoggedIn ? $_SESSION['user_id'] : '0'; ?>;
    </script>
    <script src="../js/reservation.js"></script>
</body>
</html>
