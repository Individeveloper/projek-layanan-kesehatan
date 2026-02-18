// Multi-Step Reservation Form JavaScript

// Global variables to store form data
let formData = {
    patientStatus: 'new',
    medicalRecord: '',
    fullName: '',
    nik: '',
    birthDate: '',
    gender: '',
    address: '',
    phone: '',
    email: '',
    poli: '',
    poliId: '',
    complaint: '',
    visitDate: '',
    doctor: '',
    paymentMethod: 'umum',
    queueNumber: '',
    timestamp: ''
};

let availableSchedules = [];
let flatpickrInstance = null;
let quotaCache = {}; // Cache untuk menyimpan data kuota
let disabledDates = []; // Array untuk tanggal yang disabled
let availableDates = []; // Array untuk tanggal yang available

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    generateMedicalRecord();
    initializeDatePicker();
});

// Initialize all event listeners
function initializeEventListeners() {
    // Patient status toggle
    const statusCards = document.querySelectorAll('.status-card');
    statusCards.forEach(card => {
        card.addEventListener('click', function() {
            statusCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const status = this.dataset.status;
            formData.patientStatus = status;
            togglePatientStatus(status);
        });
    });

    // Payment method toggle
    const paymentCards = document.querySelectorAll('.payment-card');
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            paymentCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            formData.paymentMethod = this.dataset.payment;
        });
    });

    // Poli selection change
    const poliSelect = document.getElementById('poli');
    if (poliSelect) {
        poliSelect.addEventListener('change', function() {
            formData.poli = this.value;
            const selectedOption = this.options[this.selectedIndex];
            formData.poliId = selectedOption.getAttribute('data-id');
            updatePoliDisplay();
            loadSchedulesForPoli();
        });
    }

    // Visit date change
    const visitDateInput = document.getElementById('visitDate');
    if (visitDateInput) {
        visitDateInput.addEventListener('change', function() {
            formData.visitDate = this.value;
            if (formData.poliId) {
                loadDoctorsByDate();
            }
        });
    }
}

// Initialize Flatpickr Date Picker
function initializeDatePicker() {
    const visitDateInput = document.getElementById('visitDate');
    if (!visitDateInput) return;

    flatpickrInstance = flatpickr(visitDateInput, {
        locale: 'id', // Bahasa Indonesia
        minDate: 'today', // Tidak boleh pilih tanggal lampau
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd F Y',
        
        // Disable dates berdasarkan data dari database
        disable: [
            function(date) {
                const dateStr = formatDateToYMD(date);
                
                // Jika ada dalam disabledDates array, disable
                if (disabledDates.includes(dateStr)) {
                    return true;
                }
                
                // Jika ada dalam availableDates, enable (return false)
                const isAvailable = availableDates.some(d => d.date === dateStr);
                if (isAvailable) {
                    return false;
                }
                
                // Default: disable jika tidak ada di available dates
                // Tapi izinkan jika data belum di-load (untuk smooth UX)
                if (availableDates.length === 0 && disabledDates.length === 0) {
                    // Data belum di-load, izinkan sementara (akan di-update setelah load)
                    const dayOfWeek = date.getDay();
                    // Minimal disable weekend
                    return dayOfWeek === 0 || dayOfWeek === 6;
                }
                
                // Jika data sudah di-load tapi tanggal ini tidak ada, disable
                return true;
            }
        ],

        // Event saat user memilih tanggal
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 0) return;
            
            formData.visitDate = dateStr;
            
            // Check quota dan load doctors
            if (formData.poliId) {
                checkQuotaAndLoadDoctors(dateStr);
            } else {
                showQuotaInfo('warning', 'Silakan pilih poli terlebih dahulu');
            }
        },

        onOpen: function(selectedDates, dateStr, instance) {
            // Load available dates saat calendar dibuka
            if (formData.poliId && availableDates.length === 0) {
                loadAvailableDates();
            }
        },

        onMonthChange: function(selectedDates, dateStr, instance) {
            // Reload available dates saat bulan berubah
            if (formData.poliId) {
                loadAvailableDates(instance.currentYear, instance.currentMonth);
            }
        },

        onReady: function(selectedDates, dateStr, instance) {
            // Add tooltips to disabled dates
            const calendarDays = instance.calendarContainer.querySelectorAll('.flatpickr-day');
            
            calendarDays.forEach(day => {
                day.addEventListener('mouseenter', function() {
                    if (this.classList.contains('flatpickr-disabled')) {
                        const dateAttr = this.getAttribute('aria-label');
                        const tooltip = document.createElement('div');
                        tooltip.className = 'flatpickr-tooltip';
                        
                        // Check if it's weekend or no schedule
                        const dayOfWeek = new Date(this.dateObj).getDay();
                        if (dayOfWeek === 0 || dayOfWeek === 6) {
                            tooltip.textContent = 'Tidak ada jadwal di hari Sabtu/Minggu';
                        } else {
                            tooltip.textContent = 'Tidak ada jadwal tersedia atau kuota penuh';
                        }
                        
                        this.appendChild(tooltip);
                        this._tooltip = tooltip;
                    }
                });

                day.addEventListener('mouseleave', function() {
                    if (this._tooltip) {
                        this._tooltip.remove();
                        this._tooltip = null;
                    }
                });
            });
        },

        onMonthChange: function(selectedDates, dateStr, instance) {
            // Reload tooltips after month change
            const calendarDays = instance.calendarContainer.querySelectorAll('.flatpickr-day');
            
            calendarDays.forEach(day => {
                day.addEventListener('mouseenter', function() {
                    if (this.classList.contains('flatpickr-disabled')) {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'flatpickr-tooltip';
                        
                        const dayOfWeek = new Date(this.dateObj).getDay();
                        if (dayOfWeek === 0 || dayOfWeek === 6) {
                            tooltip.textContent = 'Tidak ada jadwal di hari Sabtu/Minggu';
                        } else {
                            tooltip.textContent = 'Tidak ada jadwal tersedia atau kuota penuh';
                        }
                        
                        this.appendChild(tooltip);
                        this._tooltip = tooltip;
                    }
                });

                day.addEventListener('mouseleave', function() {
                    if (this._tooltip) {
                        this._tooltip.remove();
                        this._tooltip = null;
                    }
                });
            });

            // Reload available dates
            if (formData.poliId) {
                loadAvailableDates(instance.currentYear, instance.currentMonth);
            }
        }
    });
}

// Format date to YYYY-MM-DD
function formatDateToYMD(date) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().split('T')[0];
}

// Check quota and load doctors for selected date
async function checkQuotaAndLoadDoctors(dateStr) {
    if (!formData.poliId || !dateStr) {
        return;
    }

    try {
        // Fetch quota info
        const response = await fetch(`../../api/get_quota.php?polyclinic_id=${formData.poliId}&visit_date=${dateStr}`);
        const data = await response.json();

        if (data.success) {
            const available = data.available;
            quotaCache[dateStr] = available; // Update cache

            if (available > 0) {
                if (available <= 5) {
                    showQuotaInfo('limited', `Hampir penuh! Tersisa ${available} slot`);
                } else {
                    showQuotaInfo('available', `Tersedia ${available} slot`);
                }
                // Load doctors untuk tanggal ini
                loadDoctorsByDate();
            } else {
                showQuotaInfo('full', 'Maaf, kuota untuk tanggal ini sudah penuh');
                // Clear tanggal selection
                flatpickrInstance.clear();
                formData.visitDate = '';
                
                // Disable doctor dropdown
                const doctorSelect = document.getElementById('doctor');
                if (doctorSelect) {
                    doctorSelect.innerHTML = '<option value="">Pilih tanggal kunjungan terlebih dahulu</option>';
                    doctorSelect.disabled = true;
                }
            }
        } else {
            showQuotaInfo('full', data.message || 'Tanggal tidak tersedia');
            flatpickrInstance.clear();
            formData.visitDate = '';
        }
    } catch (error) {
        console.error('Error checking quota:', error);
        showQuotaInfo('warning', 'Gagal mengecek ketersediaan. Silakan coba lagi.');
    }
}

// Show quota info box
function showQuotaInfo(type, message) {
    const quotaInfoBox = document.getElementById('quota-info');
    if (!quotaInfoBox) return;

    quotaInfoBox.style.display = 'flex';
    quotaInfoBox.className = 'quota-info-box ' + type;

    let icon = '';
    if (type === 'available') {
        icon = '<i class="fas fa-check-circle"></i>';
    } else if (type === 'limited') {
        icon = '<i class="fas fa-exclamation-triangle"></i>';
    } else if (type === 'full') {
        icon = '<i class="fas fa-times-circle"></i>';
    } else {
        icon = '<i class="fas fa-info-circle"></i>';
    }

    quotaInfoBox.innerHTML = `
        ${icon}
        <div class="quota-text">
            <strong>${type === 'available' ? 'Tersedia' : type === 'limited' ? 'Terbatas' : type === 'full' ? 'Penuh' : 'Info'}</strong>
            <div>${message}</div>
        </div>
    `;
}

// Preload quota for current visible month
async function preloadQuotaForMonth() {
    // This function is now replaced by loadAvailableDates
    if (formData.poliId) {
        loadAvailableDates();
    }
}

// Load available dates from API
async function loadAvailableDates(year = null, month = null) {
    if (!formData.poliId) return;

    try {
        // Show loading state
        if (flatpickrInstance && flatpickrInstance.calendarContainer) {
            flatpickrInstance.calendarContainer.classList.add('loading');
        }

        // Calculate date range (current month + 2 months ahead)
        const today = new Date();
        const startDate = new Date(year || today.getFullYear(), month !== null ? month : today.getMonth(), 1);
        const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0); // 3 months ahead

        const startDateStr = formatDateToYMD(startDate);
        const endDateStr = formatDateToYMD(endDate);

        const response = await fetch(`../../api/get_available_dates.php?polyclinic_id=${formData.poliId}&start_date=${startDateStr}&end_date=${endDateStr}`);
        const data = await response.json();

        if (data.success) {
            availableDates = data.available_dates;
            disabledDates = data.disabled_dates;

            // Update quota cache
            availableDates.forEach(dateInfo => {
                quotaCache[dateInfo.date] = dateInfo.available;
            });

            disabledDates.forEach(dateStr => {
                quotaCache[dateStr] = 0;
            });

            // Redraw flatpickr calendar dengan data baru
            if (flatpickrInstance) {
                flatpickrInstance.redraw();
            }

            console.log('Available dates loaded:', availableDates.length);
            console.log('Disabled dates:', disabledDates.length);

            // Show message if no available dates
            if (availableDates.length === 0) {
                showQuotaInfo('warning', 'Tidak ada jadwal tersedia untuk poli ini dalam 3 bulan ke depan. Silakan hubungi rumah sakit untuk informasi lebih lanjut.');
            }
        } else {
            console.error('Failed to load available dates:', data.message);
            // Jika tidak ada jadwal sama sekali
            availableDates = [];
            disabledDates = [];
            showQuotaInfo('full', data.message || 'Tidak ada jadwal tersedia untuk poli ini');
        }
    } catch (error) {
        console.error('Error loading available dates:', error);
        showQuotaInfo('warning', 'Gagal memuat jadwal. Silakan coba lagi.');
    } finally {
        // Remove loading state
        if (flatpickrInstance && flatpickrInstance.calendarContainer) {
            flatpickrInstance.calendarContainer.classList.remove('loading');
        }
    }
}

// Set minimum date to today for visit date (Legacy - replaced by Flatpickr)
function setMinDate() {
    // This function is now handled by Flatpickr
}

// Disable weekends (Saturday and Sunday) (Legacy - replaced by Flatpickr)
function disableWeekends() {
    // This function is now handled by Flatpickr
}

// Toggle between new and existing patient
function togglePatientStatus(status) {
    const rmInputSection = document.getElementById('rmInputSection');
    const rmDisplaySection = document.getElementById('rmDisplaySection');

    if (status === 'new') {
        // New patient: show auto-generated RM, hide manual input
        rmDisplaySection.style.display = 'block';
        rmInputSection.style.display = 'none';
        generateMedicalRecord();
    } else {
        // Existing patient: show manual RM input, hide auto display
        rmDisplaySection.style.display = 'none';
        rmInputSection.style.display = 'block';
    }
}

// Generate medical record number
function generateMedicalRecord() {
    const randomNum = Math.floor(10000000 + Math.random() * 90000000);
    const rmNumber = 'RM' + randomNum;
    formData.medicalRecord = rmNumber;
    
    const rmDisplay = document.getElementById('rmNumberDisplay');
    if (rmDisplay) {
        rmDisplay.textContent = rmNumber;
    }
}

// Load schedules for selected polyclinic
function loadSchedulesForPoli() {
    if (!formData.poliId) {
        return;
    }

    // Reset doctor dropdown when poli changes
    const doctorSelect = document.getElementById('doctor');
    if (doctorSelect) {
        doctorSelect.innerHTML = '<option value="">Pilih tanggal kunjungan terlebih dahulu</option>';
        doctorSelect.disabled = true;
    }

    // Reset visit date
    const visitDateInput = document.getElementById('visitDate');
    if (visitDateInput) {
        if (flatpickrInstance) {
            flatpickrInstance.clear();
        } else {
            visitDateInput.value = '';
        }
        formData.visitDate = '';
    }

    // Hide quota info
    const quotaInfoBox = document.getElementById('quota-info');
    if (quotaInfoBox) {
        quotaInfoBox.style.display = 'none';
    }

    // Clear quota cache for new poli
    quotaCache = {};
    availableDates = [];
    disabledDates = [];

    // Fetch schedules for the polyclinic
    fetch(`../../api/get_schedules.php?polyclinic_id=${formData.poliId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableSchedules = data.data;
                console.log('Loaded schedules:', availableSchedules);
                
                // Load available dates untuk update calendar
                loadAvailableDates();
            } else {
                console.error('Failed to load schedules');
                availableSchedules = [];
            }
        })
        .catch(error => {
            console.error('Error loading schedules:', error);
            availableSchedules = [];
        });
}

// Load doctors based on selected date
function loadDoctorsByDate() {
    if (!formData.visitDate || !formData.poliId) {
        return;
    }

    // Get day name in Indonesian
    const selectedDate = new Date(formData.visitDate);
    const dayOfWeek = selectedDate.getDay();
    
    // Check if weekend (should not happen due to validation)
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        return;
    }

    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const indonesianDay = dayNames[dayOfWeek];

    // Fetch schedule for specific date
    fetch(`../../api/get_schedules.php?polyclinic_id=${formData.poliId}&visit_date=${formData.visitDate}`)
        .then(response => response.json())
        .then(data => {
            const doctorSelect = document.getElementById('doctor');
            
            if (data.success && data.data.length > 0) {
                // Clear and populate doctor dropdown
                doctorSelect.innerHTML = '<option value="">-- Pilih Dokter --</option>';
                
                data.data.forEach(schedule => {
                    const option = document.createElement('option');
                    option.value = schedule.doctor_name;
                    option.textContent = `${schedule.doctor_name} (${schedule.start_time} - ${schedule.end_time})`;
                    option.setAttribute('data-schedule-id', schedule.id);
                    doctorSelect.appendChild(option);
                });
                
                doctorSelect.disabled = false;
            } else {
                // No schedule available for this date
                doctorSelect.innerHTML = '<option value="">Tidak ada jadwal dokter pada tanggal ini</option>';
                doctorSelect.disabled = true;
                alert(`Maaf, tidak ada jadwal ${formData.poli} pada hari ${indonesianDay}. Silakan pilih tanggal lain.`);
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
            const doctorSelect = document.getElementById('doctor');
            doctorSelect.innerHTML = '<option value="">Gagal memuat data dokter</option>';
            doctorSelect.disabled = true;
        });
}

// Update poli display in step 3
function updatePoliDisplay() {
    const poliDisplay = document.getElementById('selectedPoliDisplay');
    if (poliDisplay && formData.poli) {
        poliDisplay.textContent = formData.poli;
    }
}

// Navigate to next step
function nextStep(stepNumber) {
    // Check if user is logged in before proceeding
    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        console.log('User not logged in. Redirecting to login page.');
        window.location.href = 'login.php';
        return;
    }

    // Validate current step before moving
    const currentStep = stepNumber - 1;
    if (!validateStep(currentStep)) {
        return;
    }

    // Save current step data
    saveStepData(currentStep);

    // Hide all steps
    const steps = document.querySelectorAll('.step-content');
    steps.forEach(step => step.classList.remove('active'));

    // Show target step
    const targetStep = document.getElementById('step' + stepNumber);
    if (targetStep) {
        targetStep.classList.add('active');
    }

    // Update stepper
    updateStepper(stepNumber);

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Navigate to previous step
function prevStep(stepNumber) {
    // Hide all steps
    const steps = document.querySelectorAll('.step-content');
    steps.forEach(step => step.classList.remove('active'));

    // Show target step
    const targetStep = document.getElementById('step' + stepNumber);
    if (targetStep) {
        targetStep.classList.add('active');
    }

    // Update stepper
    updateStepper(stepNumber);

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Update progress stepper
function updateStepper(activeStep) {
    const steps = document.querySelectorAll('.progress-stepper .step');
    
    steps.forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNum < activeStep) {
            step.classList.add('completed');
        } else if (stepNum === activeStep) {
            // Step 4 adalah step terakhir (selesai), maka beri class completed juga
            if (activeStep === 4) {
                step.classList.add('completed');
            } else {
                step.classList.add('active');
            }
        }
    });
}

// Validate step before proceeding
function validateStep(stepNumber) {
    let isValid = true;

    if (stepNumber === 1) {
        // Validate Step 1: Patient Data
        const requiredFields = ['fullName', 'nik', 'birthDate', 'gender', 'address', 'phone', 'email'];
        
        if (formData.patientStatus === 'existing') {
            requiredFields.push('medicalRecord');
        }

        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (input && !input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#DC3545';
            } else if (input) {
                input.style.borderColor = '#E6EBFF';
            }
        });

        // Validate NIK (must be 16 digits)
        const nikInput = document.getElementById('nik');
        if (nikInput && nikInput.value.length !== 16) {
            isValid = false;
            nikInput.style.borderColor = '#DC3545';
        }

        // Validate phone (must start with 08)
        const phoneInput = document.getElementById('phone');
        if (phoneInput && !phoneInput.value.startsWith('08')) {
            isValid = false;
            phoneInput.style.borderColor = '#DC3545';
        }

    } else if (stepNumber === 2) {
        // Validate Step 2: Poli Selection
        const poliInput = document.getElementById('poli');
        if (!poliInput.value) {
            isValid = false;
            poliInput.style.borderColor = '#DC3545';
        } else {
            poliInput.style.borderColor = '#E6EBFF';
        }

    } else if (stepNumber === 3) {
        // Validate Step 3: Visit Data
        const requiredFields = ['complaint', 'visitDate', 'doctor'];
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (input && !input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#DC3545';
            } else if (input) {
                input.style.borderColor = '#E6EBFF';
            }
        });
    }

    if (!isValid) {
        console.log('Mohon lengkapi semua field yang wajib diisi');
    }

    return isValid;
}

// Save step data
function saveStepData(stepNumber) {
    if (stepNumber === 1) {
        // Save patient data
        if (formData.patientStatus === 'existing') {
            formData.medicalRecord = document.getElementById('medicalRecord').value;
        }
        formData.fullName = document.getElementById('fullName').value;
        formData.nik = document.getElementById('nik').value;
        formData.birthDate = document.getElementById('birthDate').value;
        formData.gender = document.getElementById('gender').value;
        formData.address = document.getElementById('address').value;
        formData.phone = document.getElementById('phone').value;
        formData.email = document.getElementById('email').value;

    } else if (stepNumber === 2) {
        // Save poli selection
        formData.poli = document.getElementById('poli').value;
        const poliSelect = document.getElementById('poli');
        const selectedOption = poliSelect.options[poliSelect.selectedIndex];
        formData.poliId = selectedOption.getAttribute('data-id');
        updatePoliDisplay();

    } else if (stepNumber === 3) {
        // Save visit data
        formData.complaint = document.getElementById('complaint').value;
        formData.visitDate = document.getElementById('visitDate').value;
        formData.doctor = document.getElementById('doctor').value;
    }

    // Save to localStorage
    localStorage.setItem('reservationData', JSON.stringify(formData));
}

// Submit reservation
function submitReservation() {
    // Validate step 3
    if (!validateStep(3)) {
        return;
    }

    // Save step 3 data
    saveStepData(3);

    // Cek login
    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        alert('Silakan login terlebih dahulu untuk melakukan reservasi.');
        window.location.href = 'login.php';
        return;
    }

    // Generate timestamp
    const now = new Date();
    formData.timestamp = now.toLocaleString('id-ID');

    // Submit ke backend
    submitToBackend();
}

// Generate queue number
function generateQueueNumber() {
    const randomQueue = Math.floor(Math.random() * 999) + 1;
    const queueNumber = randomQueue.toString().padStart(3, '0');
    formData.queueNumber = queueNumber;
}

// Display confirmation on step 4
function displayConfirmation() {
    // Update queue number
    const queueDisplay = document.getElementById('queueNumber');
    if (queueDisplay) {
        queueDisplay.textContent = formData.queueNumber;
    }

    // Update confirmation details
    document.getElementById('confirmName').textContent = formData.fullName;
    document.getElementById('confirmRM').textContent = formData.medicalRecord;
    document.getElementById('confirmPoli').textContent = formData.poli;
    document.getElementById('confirmDoctor').textContent = formData.doctor;
    
    // Format date
    const visitDate = new Date(formData.visitDate);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = visitDate.toLocaleDateString('id-ID', options);
    document.getElementById('confirmDate').textContent = formattedDate;
    
    document.getElementById('confirmPayment').textContent = formData.paymentMethod.charAt(0).toUpperCase() + formData.paymentMethod.slice(1);
    document.getElementById('timestamp').textContent = formData.timestamp;
}

// Download receipt (print for now)
function downloadReceipt() {
    window.print();
}

// Submit to backend
function submitToBackend() {
    // Tampilkan loading
    const submitBtn = document.querySelector('#step3 .btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Memproses...';
    submitBtn.disabled = true;

    fetch('../../handlers/process_reservation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;

        if (data.success) {
            // Gunakan data dari server
            formData.queueNumber = data.data.queue_number;
            formData.timestamp = data.data.timestamp;

            // Display confirmation
            displayConfirmation();

            // Move to step 4
            const steps = document.querySelectorAll('.step-content');
            steps.forEach(step => step.classList.remove('active'));
            const step4 = document.getElementById('step4');
            if (step4) step4.classList.add('active');
            updateStepper(4);
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Hapus localStorage
            localStorage.removeItem('reservationData');
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                console.log(data.message || 'Terjadi kesalahan saat memproses reservasi');
            }
        }
    })
    .catch(error => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        console.error('Error:', error);
        console.log('Terjadi kesalahan koneksi. Silakan coba lagi.');
    });
}
