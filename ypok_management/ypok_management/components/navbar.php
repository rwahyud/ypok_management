<div class="sidebar" id="sidebar">
    <!-- Toggle Button -->
    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
        <span id="toggleIcon">◀</span>
        <img src="../assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK" id="toggleLogo" style="display: none;">
    </button>
    
    <div class="sidebar-brand">
        <img src="../assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo" class="logo-img">
        <div class="brand-text">
            <h2>YPOK</h2>
            <p class="brand-subtitle">Yayasan Pendidikan Olahraga Karate</p>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="index2.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index2.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        
        <div class="menu-section">MASTER DATA</div>
        
        <li>
            <a href="msh.php" <?php echo basename($_SERVER['PHP_SELF']) == 'msh.php' ? 'class="active"' : ''; ?>>
                <span class="icon">🥋</span>
                <span>Data MSH</span>
            </a>
        </li>
        
        <li>
            <a href="kohai.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kohai.php' ? 'class="active"' : ''; ?>>
                <span class="icon">👥</span>
                <span>Data Kohai</span>
            </a>
        </li>
        
        <li>
            <a href="lokasi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'lokasi.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📍</span>
                <span>Lokasi</span>
            </a>
        </li>
        
        <li>
            <a href="pembayaran.php" <?php echo basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'class="active"' : ''; ?>>
                <span class="icon">💳</span>
                <span>Pembayaran</span>
            </a>
        </li>
        
        <li>
            <a href="legalitas.php" <?php echo basename($_SERVER['PHP_SELF']) == 'legalitas.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📄</span>
                <span>Legalitas</span>
            </a>
        </li>
        
        <li>
            <a href="kegiatan_display.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kegiatan_display.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📺</span>
                <span>Kelola Tampilan Kegiatan</span>
            </a>
        </li>
        
        <div class="menu-section">LAPORAN</div>
        
        <li>
            <a href="laporan_kegiatan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'laporan_kegiatan.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📋</span>
                <span>Laporan Kegiatan</span>
            </a>
        </li>
        
        <li>
            <a href="laporan_keuangan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'laporan_keuangan.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📊</span>
                <span>Laporan Keuangan</span>
            </a>
        </li>
        
        <li class="logout-item">
            <button onclick="confirmLogout()" class="btn-logout" title="Logout">
                <span class="icon">🚪</span>
                <span>Logout</span>
            </button>
        </li>
    </ul>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">&#9776;</button>

<script>
function confirmLogout() {
    if(confirm('Apakah Anda yakin ingin logout?')) {
        window.location.href = '../actions/logout.php';
    }
}

(function () {
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar  = document.querySelector('.sidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const hamburger = document.getElementById('hamburgerBtn');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleLogo = document.getElementById('toggleLogo');
        const mainContent = document.querySelector('.main-content');
        
        if (!mainContent) {
            return;
        }

    // Check if mobile device
    function isMobile() {
        return window.innerWidth <= 768;
    }

    // Mobile: Hamburger toggle
    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        overlay.classList.add('active');
        if (hamburger) hamburger.innerHTML = '&#10005;';
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        if (hamburger) hamburger.innerHTML = '&#9776;';
    }

    // Desktop/Tablet: Sidebar collapse/expand toggle
    function toggleSidebarCollapse() {
        sidebar.classList.toggle('sidebar-collapsed');
        
        // Update main content
        if (mainContent) {
            mainContent.classList.toggle('sidebar-collapsed');
            console.log('Sidebar collapsed:', sidebar.classList.contains('sidebar-collapsed'));
            console.log('Main content classes:', mainContent.className);
        }
        
        // Update icon/logo
        if (sidebar.classList.contains('sidebar-collapsed')) {
            if (toggleIcon) toggleIcon.style.display = 'none';
            if (toggleLogo) toggleLogo.style.display = 'block';
        } else {
            if (toggleIcon) toggleIcon.style.display = 'block';
            if (toggleLogo) toggleLogo.style.display = 'none';
        }
        
        // Trigger resize for responsive elements (charts, etc.)
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 100);
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 350);
    }

    // Event listeners
    if (hamburger) {
        hamburger.addEventListener('click', function () {
            sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebarCollapse);
    }

    // Auto close sidebar on menu link click (mobile only)
    document.querySelectorAll('.sidebar-menu a').forEach(function (link) {
        link.addEventListener('click', function() {
            if (isMobile()) {
                closeSidebar();
            }
        });
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Reset collapsed state on mobile
            if (isMobile()) {
                sidebar.classList.remove('sidebar-collapsed');
                if (mainContent) {
                    mainContent.classList.remove('sidebar-collapsed');
                }
                if (toggleIcon) toggleIcon.style.display = 'block';
                if (toggleLogo) toggleLogo.style.display = 'none';
            }
        }, 250);
    });

    // Initialize on load
    if (isMobile()) {
        sidebar.classList.remove('sidebar-collapsed');
        if (mainContent) {
            mainContent.classList.remove('sidebar-collapsed');
        }
    }
    
    }); // End DOMContentLoaded
})();
</script>
