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
    complaint: '',
    visitDate: '',
    doctor: '',
    paymentMethod: 'umum',
    queueNumber: '',
    timestamp: ''
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    generateMedicalRecord();
    setMinDate();
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
            updatePoliDisplay();
        });
    }
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

// Set minimum date to today for visit date
function setMinDate() {
    const visitDateInput = document.getElementById('visitDate');
    if (visitDateInput) {
        const today = new Date().toISOString().split('T')[0];
        visitDateInput.setAttribute('min', today);
    }
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
            step.classList.add('active');
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

    fetch('../../config/process_reservation.php', {
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
