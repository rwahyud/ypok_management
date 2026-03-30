<?php
require_once __DIR__ . '/config/database.php';

try {
    // Ambil data statistik
    $total_msh = $pdo->query("SELECT COUNT(*) FROM master_sabuk_hitam")->fetchColumn();
    $total_kohai = $pdo->query("SELECT COUNT(*) FROM kohai WHERE status='Aktif'")->fetchColumn();
    $total_lokasi = $pdo->query("SELECT COUNT(*) FROM lokasi WHERE status='aktif'")->fetchColumn();
    $total_kegiatan = $pdo->query("SELECT COUNT(*) FROM kegiatan")->fetchColumn();

    // Ambil kegiatan terbaru untuk section events
    $kegiatan_data = $pdo->query("SELECT nama_kegiatan, tanggal_kegiatan, foto, keterangan FROM kegiatan WHERE tampil_di_berita = true ORDER BY tanggal_kegiatan DESC LIMIT 3")->fetchAll();
    
} catch(PDOException $e) {
    $total_msh = 0;
    $total_kohai = 0;
    $total_lokasi = 0;
    $total_kegiatan = 0;
    $kegiatan_data = [];
    error_log("Guest Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yayasan Pendidikan Olahraga Karate - Membina karakter melalui seni bela diri karate">
    <title>YPOK - Yayasan Pendidikan Olahraga Karate</title>
    <link rel="icon" type="image/svg+xml" href="assets/icons/icon-192x192.svg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #00174b;
            --primary-dark: #000a25;
            --secondary-color: #f59e0b;
            --text-dark: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background: var(--white);
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Raleway', sans-serif;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* ========== TOPBAR ========== */
        .topbar {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 0;
            font-size: 14px;
        }

        .topbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .topbar-left, .topbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .topbar a {
            color: var(--white);
            opacity: 0.9;
            transition: opacity 0.3s;
        }

        .topbar a:hover {
            opacity: 1;
        }

        /* ========== HEADER ========== */
        header {
            background: var(--white);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-color);
            margin: 0;
        }

        .logo p {
            font-size: 11px;
            color: var(--text-light);
            margin: 0;
            letter-spacing: 1px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        nav a {
            color: var(--text-dark);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
            position: relative;
        }

        nav a:hover,
        nav a.active {
            color: var(--primary-color);
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            transition: width 0.3s;
        }

        nav a:hover::after {
            width: 100%;
        }

        .btn-login {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* ========== HERO SECTION ========== */
        .hero {
            background: linear-gradient(135deg, rgba(0, 23, 75, 0.95), rgba(0, 10, 37, 0.9)),
                        url('assets/images/karate-bg.jpg') center center/cover no-repeat;
            min-height: 90vh;
            display: flex;
            align-items: center;
            color: var(--white);
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, var(--white), transparent);
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        .hero-content {
            max-width: 700px;
        }

        .hero h2 {
            font-size: 52px;
            color: var(--white);
            margin-bottom: 25px;
            line-height: 1.2;
            font-weight: 800;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 35px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #d97706;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        .btn-outline {
            border: 2px solid var(--white);
            color: var(--white);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--white);
            color: var(--primary-color);
        }

        /* ========== CONTAINER ========== */
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 12px;
            }
        }

        /* ========== SECTIONS ========== */
        section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 38px;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--secondary-color);
        }

        .section-title p {
            color: var(--text-light);
            font-size: 16px;
            margin-top: 20px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ========== ABOUT SECTION ========== */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-text h3 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .about-text p {
            margin-bottom: 20px;
            color: var(--text-light);
            text-align: justify;
            line-height: 1.8;
        }

        .about-text ul {
            list-style: none;
            margin: 25px 0;
        }

        .about-text li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: var(--text-light);
        }

        .about-text li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--secondary-color);
            font-weight: 700;
            font-size: 18px;
        }

        .about-image {
            position: relative;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        /* ========== COUNTS SECTION ========== */
        .counts {
            background: var(--primary-color);
            color: var(--white);
        }

        .counts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .count-item i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .count-number {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--white);
            font-family: 'Raleway', sans-serif;
        }

        .count-label {
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        /* ========== FEATURES SECTION ========== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-box {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s;
            border: 1px solid var(--border-color);
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }

        .feature-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
        }

        .feature-box h4 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .feature-box p {
            color: var(--text-light);
            line-height: 1.7;
        }

        /* ========== TEAM/MSH SECTION ========== */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .team-member {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .team-photo {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), #1e3a8a);
            color: var(--white);
            font-size: 80px;
            font-weight: 700;
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .team-info {
            padding: 25px;
        }

        .team-info h4 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .team-badge {
            display: inline-block;
            background: var(--primary-color);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 5px 5px 10px 0;
        }

        .team-dojo {
            color: var(--text-light);
            font-size: 14px;
            margin-top: 10px;
        }

        /* MSH Search Box */
        .msh-search-container {
            max-width: 600px;
            margin: 30px auto;
        }

        .msh-search-box {
            position: relative;
            width: 100%;
        }

        .msh-search-input {
            width: 100%;
            padding: 15px 60px 15px 20px;
            background: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            color: var(--text-dark);
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .msh-search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 23, 75, 0.1);
        }

        .msh-search-input::placeholder {
            color: var(--text-light);
        }

        .msh-search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .msh-search-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-50%) scale(1.05);
        }

        .msh-search-btn svg {
            width: 20px;
            height: 20px;
            fill: var(--white);
        }

        .msh-stats {
            text-align: center;
            margin: 15px 0;
            color: var(--text-light);
            font-size: 14px;
            font-weight: 500;
        }

        .msh-loading {
            text-align: center;
            padding: 60px 20px;
            color: var(--primary-color);
            font-size: 18px;
            font-weight: 500;
        }

        .msh-search-prompt {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .msh-search-prompt h3 {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .msh-search-prompt p {
            font-size: 16px;
            line-height: 1.7;
            max-width: 500px;
            margin: 0 auto;
        }

        .msh-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .msh-empty .icon {
            font-size: 72px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .msh-load-more {
            text-align: center;
            margin: 40px 0 20px;
        }

        .btn-load-more {
            background: var(--primary-color);
            color: var(--white);
            padding: 12px 40px;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-load-more:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 23, 75, 0.2);
        }

        .btn-load-more:disabled {
            background: var(--border-color);
            cursor: not-allowed;
            transform: none;
        }

        /* ========== EVENTS SECTION ========== */
        .events {
            background: var(--light-bg);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }

        .event-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .event-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, var(--primary-color), #1e3a8a);
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-content {
            padding: 30px;
        }

        .event-date {
            background: var(--secondary-color);
            color: var(--white);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .event-content h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .event-content p {
            color: var(--text-light);
            line-height: 1.7;
        }

        /* ========== FOOTER ========== */
        footer {
            background: var(--primary-dark);
            color: var(--white);
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: var(--white);
            margin-bottom: 20px;
            font-size: 20px;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.8;
            display: block;
            margin-bottom: 10px;
        }

        .footer-section a:hover {
            color: var(--secondary-color);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        /* ========== MOBILE RESPONSIVE ========== */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            color: var(--primary-color);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .topbar .container {
                flex-direction: column;
                gap: 10px;
            }

            header .container {
                flex-wrap: wrap;
            }

            .mobile-menu-toggle {
                display: block;
            }

            nav {
                display: none;
                width: 100%;
                margin-top: 20px;
            }

            nav.active {
                display: block;
            }

            nav ul {
                flex-direction: column;
                gap: 15px;
            }

            /* Disable hover transforms for better mobile performance */
            .feature-box:hover,
            .team-member:hover,
            .event-card:hover {
                transform: none;
            }

            .btn:hover {
                transform: none;
            }

            .hero h2 {
                font-size: 32px;
                line-height: 1.3;
            }

            .hero p {
                font-size: 15px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                padding: 12px 28px;
                font-size: 14px;
            }

            .about-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .about-text h3 {
                font-size: 26px;
            }

            .section-title h2 {
                font-size: 26px;
            }

            .count-number {
                font-size: 38px;
            }

            .counts-grid {
                gap: 30px;
            }

            .features-grid {
                gap: 20px;
            }

            .feature-box {
                padding: 30px 20px;
                margin-bottom: 18px;
            }

            .team-grid {
                gap: 20px;
            }

            .team-member,
            .event-card {
                margin-bottom: 18px;
            }

            .event-card {
                margin-bottom: 18px;
            }

            .events-grid {
                gap: 20px;
                grid-template-columns: 1fr;
            }

            .team-photo {
                height: 280px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        /* Small Mobile (≤480px) */
        @media (max-width: 480px) {
            section {
                padding: 50px 0;
            }

            .hero h2 {
                font-size: 28px;
            }

            .hero p {
                font-size: 14px;
            }

            .section-title h2 {
                font-size: 24px;
            }

            .section-title p {
                font-size: 14px;
            }

            .counts-grid {
                gap: 20px;
            }

            .count-number {
                font-size: 32px;
            }

            .count-label {
                font-size: 13px;
            }

            .features-grid {
                gap: 15px;
            }

            .feature-icon {
                font-size: 52px;
            }

            .feature-box {
                padding: 25px 18px;
            }

            .feature-box h4 {
                font-size: 18px;
            }

            .team-grid {
                gap: 15px;
            }

            .team-photo {
                height: 250px;
                font-size: 60px;
            }

            .events-grid {
                gap: 15px;
            }

            .btn {
                padding: 10px 24px;
                font-size: 13px;
            }
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            /* Animation disabled for better performance */
            /* animation: fadeInUp 0.6s ease-out; */
            opacity: 1;
            transform: translateY(0);
        }

        /* ========== SCROLL TO TOP BUTTON ========== */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--secondary-color);
            color: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            z-index: 998;
        }

        .scroll-top:hover {
            background: var(--primary-color);
            transform: translateY(-5px);
        }

        .scroll-top.active {
            display: flex;
        }
    </style>
    <?php include __DIR__ . '/components/analytics.php'; ?>
</head>
<body>
    <!-- ========== TOPBAR ========== -->
    <div class="topbar">
        <div class="container">
            <div class="topbar-left">
                <span>📧 info@ypok.org</span>
                <span>📞 +62 21 1234567</span>
            </div>
            <div class="topbar-right">
                <span>🕐 Senin - Jumat: 09:00 - 17:00</span>
            </div>
        </div>
    </div>

    <!-- ========== HEADER ========== -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo">
                <div>
                    <h1>YPOK</h1>
                    <p>Yayasan Pendidikan Olahraga Karate</p>
                </div>
            </div>
            
            <button class="mobile-menu-toggle" onclick="toggleMenu()">☰</button>
            
            <nav id="mainNav">
                <ul>
                    <li><a href="#home" class="active">Beranda</a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#features">Pertandingan</a></li>
                    <li><a href="#team">MSH</a></li>
                    <li><a href="#events">Kegiatan</a></li>
                    <li><a href="#contact">Kontak</a></li>
                    <li><a href="index.php" class="btn-login">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ========== HERO SECTION ========== -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content fade-in-up">
                <h2>Membina Karakter Melalui<br>Seni Bela Diri Karate</h2>
                <p>Yayasan Pendidikan Olahraga Karate (YPOK) berkomitmen untuk mengembangkan potensi individu melalui pelatihan karate yang berkualitas, membentuk karakter kuat, disiplin, dan sportivitas.</p>
                <div class="hero-buttons">
                    <a href="#about" class="btn btn-primary">Pelajari Lebih Lanjut</a>
                    <a href="index.php" class="btn btn-outline">Akses Sistem</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT SECTION ========== -->
    <section id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h3>Tentang YPOK</h3>
                    <p>Yayasan Pendidikan Olahraga Karate (YPOK) adalah organisasi yang berdedikasi untuk mengembangkan dan mempromosikan seni bela diri karate di Indonesia. Kami percaya bahwa karate bukan hanya tentang teknik bertarung, tetapi juga tentang pengembangan karakter, disiplin, dan nilai-nilai kehidupan.</p>
                    <p>Dengan instruktur berpengalaman dan program pelatihan terstruktur, kami telah membina ribuan siswa dari berbagai usia untuk menjadi individu yang lebih baik, percaya diri, dan bertanggung jawab.</p>
                    <ul>
                        <li>Instruktur bersertifikat internasional</li>
                        <li>Program pelatihan terstruktur untuk semua tingkatan</li>
                        <li>Fasilitas latihan yang lengkap dan modern</li>
                        <li>Komunitas yang solid dan suportif</li>
                        <li>Kompetisi dan ujian kenaikan tingkat berkala</li>
                    </ul>
                </div>
                <div class="about-image">
                    <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="Logo YPOK" style="padding: 50px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 10px;">
                </div>
            </div>
        </div>
    </section>

    <!-- ========== COUNTS SECTION ========== -->
    <section class="counts">
        <div class="container">
            <div class="counts-grid">
                <div class="count-item">
                    <div class="count-number"><?php echo number_format($total_msh); ?></div>
                    <div class="count-label">Majelis Sabuk Hitam</div>
                </div>
                <div class="count-item">
                    <div class="count-number"><?php echo number_format($total_kohai); ?></div>
                    <div class="count-label"> Kohai</div>
                </div>
                <div class="count-item">
                    <div class="count-number"><?php echo number_format($total_lokasi); ?></div>
                    <div class="count-label">Jumlah Provinsi</div>
                </div>
              
            </div>
        </div>
    </section>

    <!-- ========== KELAS PERTANDINGAN SECTION ========== -->
    <section id="features">
        <div class="container">
            <div class="section-title">
                <h2>Kelas Pertandingan</h2>
                <p>Berbagai kategori pertandingan karate untuk semua tingkat usia dari dini hingga veteran</p>
            </div>
            <div class="features-grid">
                <div class="feature-box">
                    <span class="feature-icon">🥋</span>
                    <h4>Kata Perorangan</h4>
                    <p>Pertandingan kata perorangan untuk kategori putra dan putri dari usia dini hingga veteran.</p>
                </div>
                <div class="feature-box">
                    <span class="feature-icon">⚔️</span>
                    <h4>Kata Perorangan Alat</h4>
                    <p>Pertandingan kata perorangan dengan menggunakan alat/senjata untuk putra dan putri (usia dini - veteran).</p>
                </div>
                <div class="feature-box">
                    <span class="feature-icon">👥</span>
                    <h4>Kata Beregu</h4>
                    <p>Pertandingan kata beregu untuk kategori putra dan putri dari usia dini hingga veteran.</p>
                </div>
                <div class="feature-box">
                    <span class="feature-icon">🗡️</span>
                    <h4>Kata Beregu Alat</h4>
                    <p>Pertandingan kata beregu dengan menggunakan alat/senjata untuk putra dan putri (usia dini - veteran).</p>
                </div>
                <div class="feature-box">
                    <span class="feature-icon">🥊</span>
                    <h4>Kumite Perorangan</h4>
                    <p>Pertandingan kumite (pertarungan) perorangan untuk putra dan putri dari usia dini hingga veteran.</p>
                </div>
                <div class="feature-box">
                    <span class="feature-icon">🏆</span>
                    <h4>Kumite Beregu</h4>
                    <p>Pertandingan kumite (pertarungan) beregu untuk putra dan putri dari usia dini hingga veteran.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TEAM/MSH SECTION ========== -->
    <section id="team" style="background: var(--light-bg);">
        <div class="container">
            <div class="section-title">
                <h2>Majelis Sabuk Hitam</h2>
                <p>Para instruktur bersertifikat dan berpengalaman yang membimbing perjalanan karate Anda</p>
            </div>

            <!-- Search Box -->
            <div class="msh-search-container fade-in">
                <div class="msh-search-box">
                    <input type="text" 
                           id="mshSearchInput" 
                           class="msh-search-input" 
                           placeholder="Cari nama, nomor MSH, dojo, atau tingkat dan...">
                    <button type="button" class="msh-search-btn" id="mshSearchBtn">
                        <svg viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Stats Info -->
            <div id="mshStats" class="msh-stats" style="display: none;"></div>

            <!-- MSH Gallery Container -->
            <div id="mshGalleryContainer" class="team-grid">
                <div class="msh-search-prompt" style="grid-column: 1/-1; text-align: center; padding: 80px 20px;">
                    <div style="font-size: 72px; margin-bottom: 24px; opacity: 0.6;">🔍</div>
                    <h3 style="font-size: 24px; font-weight: 600; color: var(--primary-color); margin-bottom: 12px;">Cari Majelis Sabuk Hitam</h3>
                    <p style="font-size: 16px; line-height: 1.7; max-width: 500px; margin: 0 auto; color: var(--text-light);">
                        Gunakan form pencarian di atas untuk menemukan profil Majelis Sabuk Hitam YPOK.<br>
                        Anda dapat mencari berdasarkan nama, nomor MSH, dojo, atau tingkat dan.
                    </p>
                </div>
            </div>

            <!-- Load More Button -->
            <div id="mshLoadMore" class="msh-load-more" style="display: none;">
                <button class="btn-load-more" id="btnLoadMore">Muat Lebih Banyak</button>
            </div>
        </div>
    </section>

    <!-- ========== EVENTS SECTION ========== -->
    <?php if(count($kegiatan_data) > 0): ?>
    <section id="events" class="events">
        <div class="container">
            <div class="section-title">
                <h2>Kegiatan & Event</h2>
                <p>Berbagai kegiatan dan event yang kami selenggarakan untuk pengembangan anggota</p>
            </div>
            <div class="events-grid">
                <?php foreach($kegiatan_data as $kegiatan): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <?php $eventFotoPath = 'uploads/kegiatan/' . ($kegiatan['foto'] ?? ''); ?>
                            <?php if(!empty($kegiatan['foto']) && ypok_file_exists_compat($eventFotoPath)): ?>
                                <img src="<?php echo htmlspecialchars(ypok_public_asset_url($eventFotoPath)); ?>" alt="<?php echo htmlspecialchars($kegiatan['nama_kegiatan']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="event-content">
                            <span class="event-date">
                                <?php 
                                $date = new DateTime($kegiatan['tanggal_kegiatan']);
                                echo $date->format('d F Y');
                                ?>
                            </span>
                            <h3><?php echo htmlspecialchars($kegiatan['nama_kegiatan']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($kegiatan['keterangan'] ?? 'Kegiatan YPOK', 0, 150)); ?><?php echo strlen($kegiatan['keterangan'] ?? '') > 150 ? '...' : ''; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== CONTACT/FOOTER ========== -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>YPOK</h3>
                    <p>Yayasan Pendidikan Olahraga Karate berkomitmen untuk mengembangkan seni bela diri karate dan membentuk karakter generasi muda Indonesia.</p>
                </div>
                <div class="footer-section">
                    <h3>Link Penting</h3>
                    <a href="#home">Beranda</a>
                    <a href="#about">Tentang Kami</a>
                    <a href="#team">MSH</a>
                    <a href="#events">Kegiatan</a>
                    <a href="index.php">Login Admin</a>
                </div>
                <div class="footer-section">
                    <h3>Kontak Kami</h3>
                    <p>📍 Jakarta, Indonesia</p>
                    <p>📧 info@ypok.org</p>
                    <p>📞 +62 21 1234567</p>
                </div>
                <div class="footer-section">
                    <h3>Jam Operasional</h3>
                    <p>Senin - Jumat: 09:00 - 17:00</p>
                    <p>Sabtu: 09:00 - 15:00</p>
                    <p>Minggu: Libur</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> YPOK - Yayasan Pendidikan Olahraga Karate. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ========== SCROLL TO TOP BUTTON ========== -->
    <div class="scroll-top" id="scrollTop" onclick="scrollToTop()">↑</div>

    <script>
        // Mobile Menu Toggle
        function toggleMenu() {
            const nav = document.getElementById('mainNav');
            nav.classList.toggle('active');
        }

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    document.getElementById('mainNav').classList.remove('active');
                }
            });
        });

        // Scroll Top Button
        window.addEventListener('scroll', function() {
            const scrollTop = document.getElementById('scrollTop');
            if (window.pageYOffset > 300) {
                scrollTop.classList.add('active');
            } else {
                scrollTop.classList.remove('active');
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Active Navigation
        window.addEventListener('scroll', function() {
            let current = '';
            const sections = document.querySelectorAll('section');
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 60) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });

        // ========== MSH SEARCH FUNCTIONALITY ==========
        let mshCurrentOffset = 0;
        let mshCurrentSearch = '';
        let mshTotalData = 0;
        const mshLimit = 12;
        let isLoadingMsh = false;

        // Load MSH data
        async function loadMshData(search = '', offset = 0, append = false) {
            if (isLoadingMsh) return;
            
            isLoadingMsh = true;
            const container = document.getElementById('mshGalleryContainer');
            const statsDiv = document.getElementById('mshStats');
            const loadMoreDiv = document.getElementById('mshLoadMore');
            const loadMoreBtn = document.getElementById('btnLoadMore');

            if (!append) {
                container.innerHTML = '<div class="msh-loading" style="grid-column: 1/-1;">🥋 Memuat data...</div>';
            } else {
                loadMoreBtn.disabled = true;
                loadMoreBtn.textContent = 'Memuat...';
            }

            try {
                const response = await fetch(`actions/get_msh_public.php?search=${encodeURIComponent(search)}&limit=${mshLimit}&offset=${offset}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error ${response.status}`);
                }
                
                const result = await response.json();

                if (result.success) {
                    mshTotalData = result.total;
                    
                    // Update stats
                    if (search) {
                        statsDiv.textContent = `Menampilkan ${result.data.length} dari ${result.total} hasil untuk "${search}"`;
                    } else {
                        statsDiv.textContent = `Menampilkan ${Math.min(offset + result.data.length, result.total)} dari ${result.total} Majelis Sabuk Hitam`;
                    }
                    statsDiv.style.display = 'block';

                    // Clear container if not appending
                    if (!append) {
                        container.innerHTML = '';
                    } else {
                        const loadingEl = container.querySelector('.msh-loading');
                        if (loadingEl) loadingEl.remove();
                    }

                    // Display data
                    if (result.data.length === 0 && !append) {
                        container.innerHTML = `
                            <div class="msh-empty" style="grid-column: 1/-1;">
                                <div class="icon">🔍</div>
                                <p>Tidak ada data Majelis Sabuk Hitam yang ditemukan${search ? ' untuk "' + search + '"' : ''}</p>
                            </div>
                        `;
                        loadMoreDiv.style.display = 'none';
                    } else {
                        result.data.forEach(msh => {
                            const card = createMshCard(msh);
                            container.appendChild(card);
                        });

                        // Show/hide load more button
                        if (offset + result.data.length < result.total) {
                            loadMoreDiv.style.display = 'block';
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.textContent = 'Muat Lebih Banyak';
                        } else {
                            loadMoreDiv.style.display = 'none';
                        }
                    }

                    mshCurrentOffset = offset;
                } else {
                    throw new Error(result.message || 'Gagal memuat data');
                }
            } catch (error) {
                console.error('Error loading MSH data:', error);
                container.innerHTML = `
                    <div class="msh-empty" style="grid-column: 1/-1;">
                        <div class="icon">⚠️</div>
                        <p>Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
                        <small style="color: #888; font-size: 12px; margin-top: 10px; display: block;">${error.message || 'Unknown error'}</small>
                    </div>
                `;
                loadMoreDiv.style.display = 'none';
            }

            isLoadingMsh = false;
        }

        // Create MSH card element
        function createMshCard(msh) {
            const card = document.createElement('div');
            card.className = 'team-member fade-in';
            
            const photoHtml = msh.foto 
                ? `<img src="${msh.foto}" alt="${msh.nama}" class="team-photo" onerror="this.parentElement.innerHTML='<div class=\\'team-photo\\'>${msh.nama.charAt(0).toUpperCase()}</div>'">`
                : `<div class="team-photo">${msh.nama.charAt(0).toUpperCase()}</div>`;

            card.innerHTML = `
                <div class="team-photo">
                    ${msh.foto ? `<img src="${msh.foto}" alt="${msh.nama}" onerror="this.outerHTML='${msh.nama.charAt(0).toUpperCase()}'">` : msh.nama.charAt(0).toUpperCase()}
                </div>
                <div class="team-info">
                    <h4>${msh.nama}</h4>
                    ${msh.kode_msh ? `<div style="font-size: 12px; color: var(--text-light); font-weight: 600; margin-bottom: 8px;">📋 No. MSH: ${msh.kode_msh}</div>` : ''}
                    
                    <div style="margin: 10px 0;">
                        ${msh.tingkat_dan ? `<span class="team-badge">${msh.tingkat_dan}</span>` : ''}
                    </div>
                    
                    ${msh.dojo_cabang ? `<div class="team-dojo">📍 ${msh.dojo_cabang}</div>` : ''}
                </div>
            `;

            return card;
        }

        // Search handler
        function handleMshSearch() {
            const searchInput = document.getElementById('mshSearchInput');
            const searchValue = searchInput.value.trim();
            const container = document.getElementById('mshGalleryContainer');
            const loadMoreDiv = document.getElementById('mshLoadMore');
            const statsDiv = document.getElementById('mshStats');

            // If empty search, show prompt again
            if (!searchValue) {
                container.innerHTML = `
                    <div class="msh-search-prompt" style="grid-column: 1/-1; text-align: center; padding: 80px 20px;">
                        <div style="font-size: 72px; margin-bottom: 24px; opacity: 0.6;">🔍</div>
                        <h3 style="font-size: 24px; font-weight: 600; color: var(--primary-color); margin-bottom: 12px;">Cari Majelis Sabuk Hitam</h3>
                        <p style="font-size: 16px; line-height: 1.7; max-width: 500px; margin: 0 auto; color: var(--text-light);">
                            Gunakan form pencarian di atas untuk menemukan profil Majelis Sabuk Hitam YPOK.<br>
                            Anda dapat mencari berdasarkan nama, nomor MSH, dojo, atau tingkat dan.
                        </p>
                    </div>
                `;
                loadMoreDiv.style.display = 'none';
                statsDiv.style.display = 'none';
                mshCurrentSearch = '';
                return;
            }

            mshCurrentSearch = searchValue;
            loadMshData(mshCurrentSearch, 0, false);
        }

        // Event listeners
        document.getElementById('mshSearchBtn').addEventListener('click', handleMshSearch);
        
        document.getElementById('mshSearchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleMshSearch();
            }
        });

        // Real-time search with debounce
        let searchTimeout;
        document.getElementById('mshSearchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                handleMshSearch();
            }, 500);
        });

        // Load more button
        document.getElementById('btnLoadMore').addEventListener('click', () => {
            const newOffset = mshCurrentOffset + mshLimit;
            loadMshData(mshCurrentSearch, newOffset, true);
        });
    </script>
</body>
</html>
