// Mobile Menu Toggle
const mobileToggle = document.querySelector('.mobile-toggle');
const navMenu = document.querySelector('.nav-menu');

if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// Smooth Scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Doctor Slider
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');
const doctorsGrid = document.querySelector('.doctors-grid');

let scrollPosition = 0;

if (nextBtn) {
    nextBtn.addEventListener('click', () => {
        const cardWidth = document.querySelector('.doctor-card').offsetWidth + 30;
        scrollPosition += cardWidth;
        doctorsGrid.style.transform = `translateX(-${scrollPosition}px)`;
    });
}

if (prevBtn) {
    prevBtn.addEventListener('click', () => {
        const cardWidth = document.querySelector('.doctor-card').offsetWidth + 30;
        scrollPosition -= cardWidth;
        if (scrollPosition < 0) scrollPosition = 0;
        doctorsGrid.style.transform = `translateX(-${scrollPosition}px)`;
    });
}

// Scroll Animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.service-card, .doctor-card, .feature-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s, transform 0.6s';
    observer.observe(el);
});
