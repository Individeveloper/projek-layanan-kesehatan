/**
 * Tailwind CSS Configuration
 * Heartlink Hospital
 * 
 * File ini berisi konfigurasi Tailwind yang digunakan di seluruh project.
 * Include file ini setelah Tailwind CDN di setiap halaman PHP.
 */

// Konfigurasi Tailwind untuk Heartlink Hospital
if (typeof tailwind !== 'undefined') {
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    // Primary Colors
                    primary: '#3B4C94',
                    'primary-dark': '#2D3A70',
                    'primary-light': '#4A5BA8',
                    
                    // Secondary Colors  
                    secondary: '#5A81FA',
                    'secondary-dark': '#4A6FE0',
                    'secondary-light': '#7A9BFF',
                    
                    // Accent Colors
                    accent: '#6B7FD7',
                    
                    // Custom Gray
                    'gray-custom': '#2D3748',
                },
                fontFamily: {
                    'poppins': ['Poppins', 'sans-serif'],
                },
                boxShadow: {
                    'card': '0 4px 15px rgba(0, 0, 0, 0.08)',
                    'card-hover': '0 15px 35px rgba(0, 0, 0, 0.15)',
                    'button': '0 8px 20px rgba(0, 0, 0, 0.2)',
                },
                borderRadius: {
                    'xl': '0.75rem',
                    '2xl': '1rem',
                    '3xl': '1.5rem',
                },
            }
        }
    }
}
