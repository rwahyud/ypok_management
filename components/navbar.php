<div class="sidebar">
    <div class="sidebar-brand">
        <img src="<?php echo BASE_PATH; ?>/assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo" class="logo" style="width: 40px; height: 40px; object-fit: contain; border-radius: 50%;">
        <h2>YPOK</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        
        <div class="menu-section">MASTER DATA</div>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/msh/" <?php echo strpos($_SERVER['PHP_SELF'], '/msh/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">🥋</span>
                <span>Data MSH</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/kohai/" <?php echo strpos($_SERVER['PHP_SELF'], '/kohai/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">👥</span>
                <span>Data Kohai</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/lokasi/" <?php echo strpos($_SERVER['PHP_SELF'], '/lokasi/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">📍</span>
                <span>Lokasi</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/pembayaran/" <?php echo strpos($_SERVER['PHP_SELF'], '/pembayaran/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">💳</span>
                <span>Pembayaran</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/legalitas/" <?php echo strpos($_SERVER['PHP_SELF'], '/legalitas/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">📄</span>
                <span>Legalitas</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/pendaftaran/" <?php echo strpos($_SERVER['PHP_SELF'], '/pendaftaran/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">📝</span>
                <span>Pendaftaran</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/toko/" <?php echo strpos($_SERVER['PHP_SELF'], '/toko/') !== false ? 'class="active"' : ''; ?>>
                <span class="icon">🛒</span>
                <span>Toko</span>
            </a>
        </li>
        
        <div class="menu-section">KELOLA KONTEN</div>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/laporan/kegiatan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kegiatan.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📰</span>
                <span>Kelola Berita</span>
            </a>
        </li>
        
        <div class="menu-section">LAPORAN</div>
        
        <li>
            <a href="<?php echo BASE_PATH; ?>/pages/laporan/keuangan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'keuangan.php' ? 'class="active"' : ''; ?>>
                <span class="icon">📊</span>
                <span>Laporan Keuangan</span>
            </a>
        </li>
        
        <li>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="logout-menu">
                <span class="icon">🚪</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<script>
function confirmLogout() {
    if(confirm('Apakah Anda yakin ingin logout?')) {
        // Tampilkan loading state
        const logoutBtn = document.querySelector('.btn-logout');
        if(logoutBtn) {
            logoutBtn.disabled = true;
            logoutBtn.style.opacity = '0.6';
        }
        
        // Redirect ke logout
        window.location.href = '<?php echo BASE_PATH; ?>/actions/logout.php';
        
        // Fallback jika redirect gagal
        setTimeout(function() {
            if(logoutBtn) {
                logoutBtn.disabled = false;
                logoutBtn.style.opacity = '1';
            }
        }, 3000);
    }
}

(function () {
    const sidebar  = document.querySelector('.sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const hamburger = document.getElementById('hamburgerBtn');

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        overlay.classList.add('active');
        hamburger.classList.add('active');
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        hamburger.classList.remove('active');
    }

    hamburger.addEventListener('click', function () {
        sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    // Tutup sidebar otomatis saat pindah halaman (link diklik)
    document.querySelectorAll('.sidebar-menu a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
})();
</script>
