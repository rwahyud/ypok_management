// Register Service Worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/PROJECT/ypok_management/sw.js')
        .then(reg => console.log('Service Worker registered'))
        .catch(err => console.log('Service Worker registration failed'));
}

function toggleNav() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi');
            }
        });
    });
});

// Toast notification functions
function closeToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.classList.add('hide');
        setTimeout(() => {
            toast.remove();
            // Remove query parameters from URL
            if (window.history.replaceState) {
                const url = window.location.pathname + window.location.search.split('&').filter(param => 
                    !param.includes('success=') && 
                    !param.includes('error=') && 
                    !param.includes('updated=') && 
                    !param.includes('deleted=') &&
                    !param.includes('msg=')
                ).join('&').replace('?&', '?').replace(/\?$/, '');
                window.history.replaceState({}, document.title, url || window.location.pathname);
            }
        }, 400);
    }
}

// Auto hide toast after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('toast');
    if (toast) {
        // Auto close after 5 seconds
        setTimeout(function() {
            closeToast();
        }, 5000);
        
        // Close on click anywhere on toast (except close button)
        toast.addEventListener('click', function(e) {
            if (!e.target.classList.contains('toast-close')) {
                closeToast();
            }
        });
    }
});
