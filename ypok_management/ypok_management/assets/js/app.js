function getAppBasePath() {
    const pathname = window.location.pathname;
    if (/\/pages\/[^/]+$/.test(pathname)) {
        return pathname.replace(/\/pages\/[^/]+$/, '');
    }
    return pathname.replace(/\/[^/]+$/, '') || '';
}

function isStandalonePwa() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function setupLaunchSplash() {
    if (!isStandalonePwa()) {
        return;
    }

    const styleId = 'ypok-pwa-splash-style';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .ypok-pwa-splash {
                position: fixed;
                inset: 0;
                background: linear-gradient(180deg, #ffffff 0%, #f3f7ff 100%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                opacity: 1;
                transition: opacity .28s ease;
            }
            .ypok-pwa-splash.hide {
                opacity: 0;
                pointer-events: none;
            }
            .ypok-pwa-splash .logo-wrap {
                width: 112px;
                height: 112px;
                border-radius: 24px;
                background: #ffffff;
                box-shadow: 0 10px 32px rgba(30, 58, 138, 0.18);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
            }
            .ypok-pwa-splash img {
                width: 86px;
                height: 86px;
                object-fit: contain;
            }
            .ypok-pwa-splash .text {
                font-weight: 700;
                color: #1e3a8a;
                letter-spacing: .2px;
            }
        `;
        document.head.appendChild(style);
    }

    if (document.querySelector('.ypok-pwa-splash')) {
        return;
    }

    const basePath = getAppBasePath();
    const splash = document.createElement('div');
    splash.className = 'ypok-pwa-splash';
    splash.innerHTML = `
        <div class="logo-wrap">
            <img src="${basePath}/assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo">
        </div>
        <div class="text">YPOK Management</div>
    `;
    document.body.appendChild(splash);

    const startAt = Date.now();
    const hideSplash = () => {
        const elapsed = Date.now() - startAt;
        const delay = Math.max(0, 650 - elapsed);
        setTimeout(() => {
            splash.classList.add('hide');
            setTimeout(() => splash.remove(), 320);
        }, delay);
    };

    window.addEventListener('load', hideSplash, { once: true });
}

// Register Service Worker with base path awareness.
if ('serviceWorker' in navigator) {
    const basePath = getAppBasePath();
    navigator.serviceWorker.register((basePath || '') + '/sw.js')
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', setupLaunchSplash);

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
