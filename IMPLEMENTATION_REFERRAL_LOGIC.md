# Dokumentasi: Logika Reservasi Pilihan Poli Berdasarkan Rujukan

## Deskripsi
Fitur ini mengimplementasikan logika reservasi sesuai dengan flowchart yang telah ditentukan:
- **Jika pasien memiliki rujukan**: Dapat memilih poli sesuai rujukan
- **Jika pasien tidak memiliki rujukan**: Otomatis diarahkan ke Poli Umum

## Perubahan Database

### File: `database/add_referral_field.sql`
Menambahkan kolom rujukan ke tabel `reservations`:
```sql
ALTER TABLE `reservations` ADD COLUMN `referral` TEXT NULL DEFAULT NULL;
ALTER TABLE `reservations` ADD COLUMN `referral_date` DATE NULL DEFAULT NULL;
ALTER TABLE `reservations` ADD COLUMN `referral_doctor` VARCHAR(100) NULL DEFAULT NULL;
```

**Eksekusi SQL**: Jalankan file ini di phpMyAdmin atau MySQL command line

## Perubahan Frontend

### File: `public-web/layout/reservation.php`

#### Step 1 - Tambahan Bagian Rujukan:
- **Referral Toggle Cards**: Dua pilihan "Ada Rujukan" / "Tidak Ada Rujukan"
- **Referral Number Input**: Nomor surat rujukan (opsional)
- **Referral Date Input**: Tanggal rujukan (opsional)
- **Referral Doctor Input**: Nama dokter yang merujuk (opsional)

#### Step 2 - Logika Pilih Poli:
- **Info Box**: Menampilkan status rujukan pasien
- **Poli Dropdown**: Behavior berubah berdasarkan status rujukan:
  - Jika **tidak ada rujukan**: Disabled dan otomatis pilih "Poli Umum"
  - Jika **ada rujukan**: Enabled dan user dapat memilih

### File: `public-web/style/reservation.css`
- Menambahkan styling untuk `.referral-section` dan `.referral-cards`
- Styling konsisten dengan design system yang sudah ada
- Responsive design untuk mobile devices

### File: `public-web/js/reservation.js`

#### Perubahan FormData:
```javascript
let formData = {
    // ... field lainnya
    hasReferral: true,           // Default: Ada Rujukan
    referralNumber: '',
    referralDate: '',
    referralDoctor: '',
    // ... field lainnya
}
```

#### Fungsi Baru:

1. **toggleReferralFields(hasReferral)**
   - Menampilkan/menyembunyikan input field rujukan
   - Clear field jika user memilih "Tidak Ada Rujukan"

2. **handleReferralChange(hasReferral)**
   - Logika utama untuk mengatur pilihan poli:
     - Jika `hasReferral = false`: Auto-select "Poli Umum", disable dropdown
     - Jika `hasReferral = true`: Enable dropdown, biarkan user memilih
   - Tampilkan pesan info status rujukan

#### Event Listeners Baru:
- Referral cards click handler
- Trigger logika perubahan rujukan

#### Update saveStepData():
- Menyimpan data rujukan (referralNumber, referralDate, referralDoctor)

## Perubahan Backend

### File: `handlers/process_reservation.php`

#### Input Data Tambahan:
```php
$has_referral = isset($input['hasReferral']) ? (bool)$input['hasReferral'] : false;
$referral_number = isset($input['referralNumber']) ? trim($input['referralNumber']) : '';
$referral_date = isset($input['referralDate']) ? trim($input['referralDate']) : null;
$referral_doctor = isset($input['referralDoctor']) ? trim($input['referralDoctor']) : '';
```

#### Update INSERT Statement:
```php
$stmt = $db->prepare("INSERT INTO reservations (
    user_id, patient_id, polyclinic_schedule_id, 
    reservation_date, queue_number, status, 
    referral, referral_date, referral_doctor
) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)");

$stmt->bind_param("iiisiiss", 
    $user_id, $patient_id, $polyclinic_schedule_id, 
    $visit_date, $queue_number, 
    $referral_number, $referral_date, $referral_doctor);
```

## Alur Kerja

### Step 1 - Data Pasien:
1. User memilih status pasien (Baru/Sudah Pernah Daftar)
2. User mengisi data diri lengkap
3. **NEW**: User memilih status rujukan (Ada/Tidak Ada)
4. **NEW**: Jika ada rujukan, user bisa input nomor rujukan, tanggal, dan dokter
5. Klik "Simpan & Lanjut"

### Step 2 - Pilih Poli (Logika Baru):
```
Jika TIDAK ADA RUJUKAN:
  - Dropdown DISABLED
  - Poli UMUM otomatis terpilih
  - Info: "Karena Anda tidak memiliki rujukan, otomatis dialihkan ke Poli Umum"

Jika ADA RUJUKAN:
  - Dropdown ENABLED
  - User dapat memilih poli sesuai rujukan
  - Info: "Silakan pilih poliklinik sesuai dengan surat rujukan Anda"
```

### Step 3 - Data Kunjungan:
- User memilih tanggal dan dokter (normal, seperti sebelumnya)
- Sistem sudah tahu poli apa yang dipilih

### Step 4 - Konfirmasi:
- Menampilkan semua data termasuk status rujukan

## Testing

### Test Case 1: Pasien Tanpa Rujukan
1. Pilih "Tidak Ada Rujukan" di Step 1
2. Isi data pasien lainnya
3. Ke Step 2 → Verifikasi:
   - Dropdown poli DISABLED
   - Poli Umum sudah terseleksi otomatis
   - Pesan info ditampilkan

### Test Case 2: Pasien Dengan Rujukan
1. Pilih "Ada Rujukan" di Step 1
2. Isi nomor rujukan, tanggal, nama dokter
3. Isi data pasien lainnya
4. Ke Step 2 → Verifikasi:
   - Dropdown poli ENABLED
   - User dapat memilih poli
   - Pesan info ditampilkan

### Test Case 3: Data Database
- Verifikasi data rujukan tersimpan di database
- Check `reservations` table → kolom `referral`, `referral_date`, `referral_doctor`

## Catatan Penting

1. **Database Migration**: Pastikan menjalankan SQL migration sebelum menggunakan fitur
2. **Default Value**: Default adalah "Ada Rujukan" (hasReferral = true) untuk memberikan fleksibilitas
3. **Opsional Fields**: Nomor rujukan, tanggal, dan dokter bersifat opsional
4. **Poli Umum**: Pastikan ada poli dengan nama "Umum" atau "umum" di database
5. **Responsive**: Design sudah dioptimalkan untuk mobile

## Debugging

### Jika dropdown tidak ter-disable:
1. Check console browser untuk error
2. Verify `handleReferralChange()` function dipanggil
3. Check apakah HTML element `#poli` ada

### Jika data rujukan tidak tersimpan:
1. Check browser console → Network tab
2. Verify `referralNumber`, `referralDate`, `referralDoctor` ada di request body
3. Check `process_reservation.php` untuk error

### Jika Poli Umum tidak ter-select otomatis:
1. Verify nama poli di database exact match dengan kondisi di JavaScript
2. Check console.log di `handleReferralChange()` function
