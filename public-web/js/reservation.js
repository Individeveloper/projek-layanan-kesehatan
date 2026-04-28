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
    hasReferral: true, // Default: Ada Rujukan (true = Yes, false = No)
    referralNumber: '',
    referralDate: '',
    referralDoctor: '',
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

// Toggle referral input fields visibility
function toggleReferralFields(hasReferral) {
    const referralNumberSection = document.getElementById('referralNumberSection');
    const referralDateSection = document.getElementById('referralDateSection');
    const referralDoctorSection = document.getElementById('referralDoctorSection');
    
    const referralNumber = document.getElementById('referralNumber');
    const referralDate = document.getElementById('referralDate');
    const referralDoctor = document.getElementById('referralDoctor');

    if (hasReferral) {
        // Show referral fields if user has referral
        if (referralNumberSection) referralNumberSection.style.display = 'block';
        if (referralDateSection) referralDateSection.style.display = 'block';
        if (referralDoctorSection) referralDoctorSection.style.display = 'block';
        
        // Set required attributes
        if (referralNumber) referralNumber.setAttribute('required', 'required');
        if (referralDoctor) referralDoctor.setAttribute('required', 'required');
    } else {
        // Hide referral fields if user doesn't have referral
        if (referralNumberSection) referralNumberSection.style.display = 'none';
        if (referralDateSection) referralDateSection.style.display = 'none';
        if (referralDoctorSection) referralDoctorSection.style.display = 'none';
        
        // Remove required attributes and clear fields
        if (referralNumber) {
            referralNumber.removeAttribute('required');
            referralNumber.value = '';
            referralNumber.style.borderColor = '#E6EBFF';
        }
        if (referralDate) {
            referralDate.value = '';
            referralDate.style.borderColor = '#E6EBFF';
        }
        if (referralDoctor) {
            referralDoctor.removeAttribute('required');
            referralDoctor.value = '';
            referralDoctor.style.borderColor = '#E6EBFF';
        }
    }
}

// Handle referral change logic
function handleReferralChange(hasReferral) {
    const poliSelect = document.getElementById('poli');
    const referralStatusInfo = document.getElementById('referralStatusInfo');
    const referralStatusText = document.getElementById('referralStatusText');
    const poliHint = document.getElementById('poliHint');

    if (!hasReferral) {
        // No referral: auto-select Poli Umum and disable dropdown
        const options = poliSelect.options;
        let generalPoliOption = null;

        // Cari Poli Umum dengan berbagai cara
        for (let i = 0; i < options.length; i++) {
            const optionText = options[i].text.toLowerCase().trim();
            const optionValue = options[i].value.toLowerCase().trim();
            
            if (optionText === 'poli umum' || optionValue === 'poli umum') {
                generalPoliOption = options[i];
                break;
            }
        }

        if (generalPoliOption) {
            poliSelect.value = generalPoliOption.value;
            formData.poli = generalPoliOption.value;
            formData.poliId = generalPoliOption.getAttribute('data-id');
            
            console.log('Poli Umum auto-selected:', formData.poli, 'ID:', formData.poliId);
        } else {
            console.warn('Poli Umum tidak ditemukan di dropdown');
        }

        // Disable dropdown and show info
        poliSelect.disabled = true;
        if (poliHint) {
            poliHint.textContent = 'Karena Anda tidak memiliki rujukan, otomatis dialihkan ke Poli Umum.';
            poliHint.style.display = 'block';
            poliHint.style.color = '#28a745';
        }

        // Show referral status info
        if (referralStatusInfo) {
            referralStatusText.textContent = 'Anda tidak memiliki rujukan. Anda akan berkonsultasi ke Poli Umum.';
            referralStatusInfo.style.display = 'flex';
        }

        // Update poli display
        updatePoliDisplay();
        loadSchedulesForPoli();
    } else {
        // Has referral: enable dropdown and let user choose
        poliSelect.disabled = false;
        poliSelect.value = ''; // Clear selection
        formData.poli = '';
        formData.poliId = '';

        if (poliHint) {
            poliHint.textContent = 'Pilih poliklinik sesuai dengan surat rujukan Anda.';
            poliHint.style.display = 'block';
            poliHint.style.color = '#5B7FDB';
        }

        // Show referral status info
        if (referralStatusInfo) {
            referralStatusText.textContent = 'Anda memiliki rujukan. Silakan pilih poliklinik sesuai surat rujukan Anda.';
            referralStatusInfo.style.display = 'flex';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    generateMedicalRecord();
    initializeDatePicker();
    
    // Set initial hasReferral value based on active referral card
    const activeReferralCard = document.querySelector('.referral-card.active');
    if (activeReferralCard) {
        const hasReferral = activeReferralCard.dataset.referral === 'yes';
        formData.hasReferral = hasReferral;
        toggleReferralFields(hasReferral);
        console.log('Initial hasReferral:', hasReferral);
    }
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

    // Referral toggle (Ada Rujukan / Tidak Ada Rujukan)
    const referralCards = document.querySelectorAll('.referral-card');
    referralCards.forEach(card => {
        card.addEventListener('click', function() {
            referralCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const hasReferral = this.dataset.referral === 'yes';
            formData.hasReferral = hasReferral;
            
            // Show/hide referral input fields
            toggleReferralFields(hasReferral);
            
            // Clear poli selection and update available options
            handleReferralChange(hasReferral);
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

    // --- TAMBAHAN FITUR AUTO-FILL PASIEN LAMA ---
    const rmInput = document.getElementById('medicalRecord');
    if (rmInput) {
        rmInput.addEventListener('blur', function() {
            const rmValue = this.value.trim();
            if (rmValue) {
                rmInput.style.borderColor = '#007bff';

                fetch(`../../api/get_patient.php?rm=${rmValue}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('fullName').value = data.data.full_name;
                            document.getElementById('nik').value = data.data.nik;
                            document.getElementById('birthDate').value = data.data.date_of_birth;
                            
                            const genderSelect = document.getElementById('gender');
                            if (genderSelect) {
                                genderSelect.value = data.data.gender === 'L' ? 'Laki-laki' : 'Perempuan';
                            }

                            document.getElementById('address').value = data.data.address;
                            document.getElementById('phone').value = data.data.phone_number;

                            alert('Data pasien ditemukan! Form telah diisi otomatis.');
                            rmInput.style.borderColor = '#28a745'; 
                        } else {
                            alert('Nomor RM tidak ditemukan. Silakan cek kembali atau daftar sebagai Pasien Baru.');
                            rmInput.style.borderColor = '#DC3545'; 
                            
                            ['fullName', 'nik', 'birthDate', 'address', 'phone'].forEach(id => {
                                const el = document.getElementById(id);
                                if(el) el.value = '';
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching patient:', error));
            }
        });
    }
}

// Initialize Flatpickr Date Picker
function initializeDatePicker() {
    const visitDateInput = document.getElementById('visitDate');
    if (!visitDateInput) return;

    flatpickrInstance = flatpickr(visitDateInput, {
        locale: 'id', 
        minDate: 'today', 
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd F Y',
        
        disable: [
            function(date) {
                const dateStr = formatDateToYMD(date);
                if (disabledDates.includes(dateStr)) return true;
                const isAvailable = availableDates.some(d => d.date === dateStr);
                if (isAvailable) return false;
                
                if (availableDates.length === 0 && disabledDates.length === 0) {
                    const dayOfWeek = date.getDay();
                    return dayOfWeek === 0 || dayOfWeek === 6;
                }
                return true;
            }
        ],

        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 0) return;
            formData.visitDate = dateStr;
            if (formData.poliId) {
                checkQuotaAndLoadDoctors(dateStr);
            } else {
                showQuotaInfo('warning', 'Silakan pilih poli terlebih dahulu');
            }
        },

        onOpen: function(selectedDates, dateStr, instance) {
            if (formData.poliId && availableDates.length === 0) {
                loadAvailableDates();
            }
        },

        onMonthChange: function(selectedDates, dateStr, instance) {
            if (formData.poliId) {
                loadAvailableDates(instance.currentYear, instance.currentMonth);
            }
        },

        onReady: function(selectedDates, dateStr, instance) {
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
        }
    });
}

function formatDateToYMD(date) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().split('T')[0];
}

async function checkQuotaAndLoadDoctors(dateStr) {
    if (!formData.poliId || !dateStr) return;

    try {
        const response = await fetch(`../../api/get_quota.php?polyclinic_id=${formData.poliId}&visit_date=${dateStr}`);
        const data = await response.json();

        if (data.success) {
            const available = data.available;
            quotaCache[dateStr] = available; 

            if (available > 0) {
                if (available <= 5) {
                    showQuotaInfo('limited', `Hampir penuh! Tersisa ${available} slot`);
                } else {
                    showQuotaInfo('available', `Tersedia ${available} slot`);
                }
                loadDoctorsByDate();
            } else {
                showQuotaInfo('full', 'Maaf, kuota untuk tanggal ini sudah penuh');
                flatpickrInstance.clear();
                formData.visitDate = '';
                
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

async function preloadQuotaForMonth() {
    if (formData.poliId) {
        loadAvailableDates();
    }
}

async function loadAvailableDates(year = null, month = null) {
    if (!formData.poliId) return;

    try {
        if (flatpickrInstance && flatpickrInstance.calendarContainer) {
            flatpickrInstance.calendarContainer.classList.add('loading');
        }

        const today = new Date();
        const startDate = new Date(year || today.getFullYear(), month !== null ? month : today.getMonth(), 1);
        const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0); 

        const startDateStr = formatDateToYMD(startDate);
        const endDateStr = formatDateToYMD(endDate);

        const response = await fetch(`../../api/get_available_dates.php?polyclinic_id=${formData.poliId}&start_date=${startDateStr}&end_date=${endDateStr}`);
        const data = await response.json();

        if (data.success) {
            availableDates = data.available_dates;
            disabledDates = data.disabled_dates;

            availableDates.forEach(dateInfo => {
                quotaCache[dateInfo.date] = dateInfo.available;
            });

            disabledDates.forEach(dateStr => {
                quotaCache[dateStr] = 0;
            });

            if (flatpickrInstance) {
                flatpickrInstance.redraw();
            }

            if (availableDates.length === 0) {
                showQuotaInfo('warning', 'Tidak ada jadwal tersedia untuk poli ini dalam 3 bulan ke depan.');
            }
        } else {
            availableDates = [];
            disabledDates = [];
            showQuotaInfo('full', data.message || 'Tidak ada jadwal tersedia untuk poli ini');
        }
    } catch (error) {
        console.error('Error loading available dates:', error);
        showQuotaInfo('warning', 'Gagal memuat jadwal. Silakan coba lagi.');
    } finally {
        if (flatpickrInstance && flatpickrInstance.calendarContainer) {
            flatpickrInstance.calendarContainer.classList.remove('loading');
        }
    }
}

// Toggle between new and existing patient
function togglePatientStatus(status) {
    const rmInputSection = document.getElementById('rmInputSection');
    const rmDisplaySection = document.getElementById('rmDisplaySection');
    const rmInput = document.getElementById('medicalRecord');
    const rmSearchStatus = document.getElementById('rmSearchStatus');

    if (status === 'new') {
        // New patient: show auto-generated RM, hide manual input
        rmDisplaySection.style.display = 'block';
        rmInputSection.style.display = 'none';
        
        // Reset search status
        if (rmSearchStatus) {
            rmSearchStatus.style.display = 'none';
        }
        
        // Kosongkan form jika pindah ke Pasien Baru
        ['medicalRecord', 'fullName', 'nik', 'birthDate', 'gender', 'address', 'phone', 'email'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.value = '';
                el.style.borderColor = '#E6EBFF';
            }
        });

        generateMedicalRecord();
    } else {
        // Existing patient: show manual RM input, hide auto display
        rmDisplaySection.style.display = 'none';
        rmInputSection.style.display = 'block';
        
        // Reset search status
        if (rmSearchStatus) {
            rmSearchStatus.style.display = 'none';
        }
        
        // Reset form but keep RM input
        formData.medicalRecord = '';
        
        ['fullName', 'nik', 'birthDate', 'gender', 'address', 'phone', 'email'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.value = '';
                el.style.borderColor = '#E6EBFF';
            }
        });
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

// Search patient by medical record
async function searchPatientByRM() {
    const medicalRecordInput = document.getElementById('medicalRecord');
    const rmSearchStatus = document.getElementById('rmSearchStatus');
    const medicalRecord = medicalRecordInput.value.trim();

    if (!medicalRecord) {
        showRMSearchStatus('error', '<i class="fas fa-exclamation-circle"></i> Masukan nomor rekam medis');
        return;
    }

    // Show loading
    showRMSearchStatus('loading', '<i class="fas fa-spinner"></i> Mencari data...');
    const searchBtn = document.querySelector('.btn-search-rm');
    searchBtn.disabled = true;

    try {
        const response = await fetch(`../../api/get_patient_by_medical_record.php?medical_record=${encodeURIComponent(medicalRecord)}`);
        const data = await response.json();

        if (data.success) {
            // Fill form dengan data yang ditemukan
            fillPatientData(data.data);
            showRMSearchStatus('success', '<i class="fas fa-check-circle"></i> Data pasien ditemukan dan terisi otomatis');
            formData.medicalRecord = medicalRecord;
            medicalRecordInput.style.borderColor = '#28a745';
        } else {
            showRMSearchStatus('error', `<i class="fas fa-times-circle"></i> ${data.message}`);
            medicalRecordInput.style.borderColor = '#DC3545';
        }
    } catch (error) {
        console.error('Error searching patient:', error);
        showRMSearchStatus('error', '<i class="fas fa-times-circle"></i> Gagal mencari data. Silakan coba lagi.');
        medicalRecordInput.style.borderColor = '#DC3545';
    } finally {
        searchBtn.disabled = false;
    }
}

// Show search status message
function showRMSearchStatus(type, message) {
    const rmSearchStatus = document.getElementById('rmSearchStatus');
    rmSearchStatus.className = type;
    rmSearchStatus.innerHTML = message;
    rmSearchStatus.style.display = 'flex';
}

// Fill patient form dengan data dari database
function fillPatientData(data) {
    document.getElementById('fullName').value = data.fullName;
    document.getElementById('nik').value = data.nik;
    document.getElementById('birthDate').value = data.birthDate;
    document.getElementById('gender').value = data.gender;
    document.getElementById('address').value = data.address;
    document.getElementById('phone').value = data.phone;

    // Update form data object
    formData.fullName = data.fullName;
    formData.nik = data.nik;
    formData.birthDate = data.birthDate;
    formData.gender = data.gender;
    formData.address = data.address;
    formData.phone = data.phone;
}

function loadSchedulesForPoli() {
    if (!formData.poliId) return;

    const doctorSelect = document.getElementById('doctor');
    if (doctorSelect) {
        doctorSelect.innerHTML = '<option value="">Pilih tanggal kunjungan terlebih dahulu</option>';
        doctorSelect.disabled = true;
    }

    const visitDateInput = document.getElementById('visitDate');
    if (visitDateInput) {
        if (flatpickrInstance) {
            flatpickrInstance.clear();
        } else {
            visitDateInput.value = '';
        }
        formData.visitDate = '';
    }

    const quotaInfoBox = document.getElementById('quota-info');
    if (quotaInfoBox) {
        quotaInfoBox.style.display = 'none';
    }

    quotaCache = {};
    availableDates = [];
    disabledDates = [];

    fetch(`../../api/get_schedules.php?polyclinic_id=${formData.poliId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableSchedules = data.data;
                loadAvailableDates();
            } else {
                availableSchedules = [];
            }
        })
        .catch(error => {
            console.error('Error loading schedules:', error);
            availableSchedules = [];
        });
}

function loadDoctorsByDate() {
    if (!formData.visitDate || !formData.poliId) return;

    const selectedDate = new Date(formData.visitDate);
    const dayOfWeek = selectedDate.getDay();
    
    if (dayOfWeek === 0 || dayOfWeek === 6) return;

    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const indonesianDay = dayNames[dayOfWeek];

    fetch(`../../api/get_schedules.php?polyclinic_id=${formData.poliId}&visit_date=${formData.visitDate}`)
        .then(response => response.json())
        .then(data => {
            const doctorSelect = document.getElementById('doctor');
            
            if (data.success && data.data.length > 0) {
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
                doctorSelect.innerHTML = '<option value="">Tidak ada jadwal dokter pada tanggal ini</option>';
                doctorSelect.disabled = true;
                alert(`Maaf, tidak ada jadwal ${formData.poli} pada hari ${indonesianDay}. Silakan pilih tanggal lain.`);
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
            const doctorSelect = document.getElementById('doctor');
            if (doctorSelect) {
                doctorSelect.innerHTML = '<option value="">Gagal memuat data dokter</option>';
                doctorSelect.disabled = true;
            }
        });
}

function updatePoliDisplay() {
    const poliDisplay = document.getElementById('selectedPoliDisplay');
    if (poliDisplay && formData.poli) {
        poliDisplay.textContent = formData.poli;
    }
}

function nextStep(stepNumber) {
    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        window.location.href = 'login.php';
        return;
    }

    const currentStep = stepNumber - 1;
    if (!validateStep(currentStep)) return;

    saveStepData(currentStep);

    const steps = document.querySelectorAll('.step-content');
    steps.forEach(step => step.classList.remove('active'));

    const targetStep = document.getElementById('step' + stepNumber);
    if (targetStep) targetStep.classList.add('active');

    updateStepper(stepNumber);

    if (stepNumber === 2) {
        handleReferralChange(formData.hasReferral);
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(stepNumber) {
    const steps = document.querySelectorAll('.step-content');
    steps.forEach(step => step.classList.remove('active'));

    const targetStep = document.getElementById('step' + stepNumber);
    if (targetStep) targetStep.classList.add('active');

    updateStepper(stepNumber);

    if (stepNumber === 2) {
        handleReferralChange(formData.hasReferral);
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepper(activeStep) {
    const steps = document.querySelectorAll('.progress-stepper .step');
    
    steps.forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNum < activeStep) {
            step.classList.add('completed');
        } else if (stepNum === activeStep) {
            if (activeStep === 4) {
                step.classList.add('completed');
            } else {
                step.classList.add('active');
            }
        }
    });
}

function validateStep(stepNumber) {
    let isValid = true;

    if (stepNumber === 1) {
        const requiredFields = ['fullName', 'nik', 'birthDate', 'gender', 'address', 'phone'];
        
        if (formData.patientStatus === 'existing') {
            requiredFields.push('medicalRecord');
        }

        if (formData.hasReferral) {
            requiredFields.push('referralNumber', 'referralDoctor');
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

        const nikInput = document.getElementById('nik');
        if (nikInput && nikInput.value.length !== 16) {
            isValid = false;
            nikInput.style.borderColor = '#DC3545';
        }

        const phoneInput = document.getElementById('phone');
        if (phoneInput && !phoneInput.value.startsWith('08')) {
            isValid = false;
            phoneInput.style.borderColor = '#DC3545';
        }

    } else if (stepNumber === 2) {
        const poliInput = document.getElementById('poli');
        if (!poliInput.value) {
            isValid = false;
            poliInput.style.borderColor = '#DC3545';
        } else {
            poliInput.style.borderColor = '#E6EBFF';
        }

    } else if (stepNumber === 3) {
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

function saveStepData(stepNumber) {
    if (stepNumber === 1) {
        if (formData.patientStatus === 'existing') {
            formData.medicalRecord = document.getElementById('medicalRecord').value;
        }
        formData.fullName = document.getElementById('fullName').value;
        formData.nik = document.getElementById('nik').value;
        formData.birthDate = document.getElementById('birthDate').value;
        formData.gender = document.getElementById('gender').value;
        formData.address = document.getElementById('address').value;
        formData.phone = document.getElementById('phone').value;
        
        const emailInput = document.getElementById('email');
        if(emailInput) formData.email = emailInput.value;

        formData.referralNumber = document.getElementById('referralNumber')?.value || '';
        formData.referralDate = document.getElementById('referralDate')?.value || '';
        formData.referralDoctor = document.getElementById('referralDoctor')?.value || '';

    } else if (stepNumber === 2) {
        formData.poli = document.getElementById('poli').value;
        const poliSelect = document.getElementById('poli');
        if(poliSelect.selectedIndex >= 0) {
            const selectedOption = poliSelect.options[poliSelect.selectedIndex];
            formData.poliId = selectedOption.getAttribute('data-id');
        }
        updatePoliDisplay();

    } else if (stepNumber === 3) {
        formData.complaint = document.getElementById('complaint').value;
        formData.visitDate = document.getElementById('visitDate').value;
        formData.doctor = document.getElementById('doctor').value;
    }

    localStorage.setItem('reservationData', JSON.stringify(formData));
}

function submitReservation() {
    if (!validateStep(3)) return;

    saveStepData(3);

    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        alert('Silakan login terlebih dahulu untuk melakukan reservasi.');
        window.location.href = 'login.php';
        return;
    }

    const now = new Date();
    formData.timestamp = now.toLocaleString('id-ID');

    submitToBackend();
}

function displayConfirmation() {
    const queueDisplay = document.getElementById('queueNumber');
    if (queueDisplay) queueDisplay.textContent = formData.queueNumber;

    const nameDisplay = document.getElementById('confirmName');
    if(nameDisplay) nameDisplay.textContent = formData.fullName;
    
    const rmDisplay = document.getElementById('confirmRM');
    if(rmDisplay) rmDisplay.textContent = formData.medicalRecord;
    
    const poliDisplay = document.getElementById('confirmPoli');
    if(poliDisplay) poliDisplay.textContent = formData.poli;
    
    const docDisplay = document.getElementById('confirmDoctor');
    if(docDisplay) docDisplay.textContent = formData.doctor;
    
    const visitDate = new Date(formData.visitDate);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = visitDate.toLocaleDateString('id-ID', options);
    
    const dateDisplay = document.getElementById('confirmDate');
    if(dateDisplay) dateDisplay.textContent = formattedDate;
    
    const payDisplay = document.getElementById('confirmPayment');
    if(payDisplay && formData.paymentMethod) payDisplay.textContent = formData.paymentMethod.charAt(0).toUpperCase() + formData.paymentMethod.slice(1);
    
    const timeDisplay = document.getElementById('timestamp');
    if(timeDisplay) timeDisplay.textContent = formData.timestamp;
}

function downloadReceipt() {
    window.print();
}

function submitToBackend() {
    const submitBtn = document.querySelector('#step3 .btn-primary');
    if(!submitBtn) return;
    
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
            formData.queueNumber = data.data.queue_number;
            formData.timestamp = data.data.timestamp;

            displayConfirmation();

            const steps = document.querySelectorAll('.step-content');
            steps.forEach(step => step.classList.remove('active'));
            const step4 = document.getElementById('step4');
            if (step4) step4.classList.add('active');
            updateStepper(4);
            window.scrollTo({ top: 0, behavior: 'smooth' });

            localStorage.removeItem('reservationData');
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses reservasi');
            }
        }
    })
    .catch(error => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        console.error('Error:', error);
        alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
    });
}