<?php
require_once 'config/supabase.php';

try {
    // Ambil data statistik
    $total_msh = $pdo->query("SELECT COUNT(*) FROM majelis_sabuk_hitam WHERE status='aktif'")->fetchColumn();
    $total_kohai = $pdo->query("SELECT COUNT(*) FROM kohai WHERE status='aktif'")->fetchColumn();
    $total_lokasi = $pdo->query("SELECT COUNT(*) FROM lokasi WHERE status='aktif'")->fetchColumn();
    $total_kegiatan = $pdo->query("SELECT COUNT(*) FROM kegiatan")->fetchColumn();

    // Ambil data MSH untuk galeri - menggunakan tingkat_dan bukan tingkat_sabuk
    try {
        $msh_data = $pdo->query("SELECT nama, foto, tingkat_dan, EXTRACT(YEAR FROM created_at) as tahun_bergabung FROM majelis_sabuk_hitam WHERE status='aktif' ORDER BY created_at DESC LIMIT 8")->fetchAll();
    } catch(PDOException $e) {
        // Fallback jika ada kolom yang tidak ditemukan
        $msh_data = $pdo->query("SELECT nama, foto, id, EXTRACT(YEAR FROM created_at) as tahun_bergabung FROM majelis_sabuk_hitam WHERE status='aktif' ORDER BY created_at DESC LIMIT 8")->fetchAll();
    }

    // Ambil kegiatan terbaru
    $kegiatan_data = $pdo->query("SELECT nama_kegiatan, tanggal_kegiatan, foto FROM kegiatan ORDER BY tanggal_kegiatan DESC LIMIT 4")->fetchAll();
    
    // Ambil berita kegiatan yang ditandai untuk ditampilkan di halaman utama
    // Query simple terlebih dahulu untuk memastikan data bisa dimuat
    try {
        $berita_kegiatan = $pdo->query("
            SELECT nama_kegiatan, tanggal_kegiatan, foto, keterangan, jenis_kegiatan 
            FROM kegiatan 
            WHERE tampil_di_berita = true 
            ORDER BY tanggal_kegiatan DESC 
            LIMIT 6
        ")->fetchAll();
    } catch(PDOException $e) {
        // Fallback query jika ada masalah dengan boolean
        error_log("Berita query error: " . $e->getMessage());
        $berita_kegiatan = $pdo->query("
            SELECT nama_kegiatan, tanggal_kegiatan, foto, keterangan, jenis_kegiatan 
            FROM kegiatan 
            WHERE tampil_di_berita::text = 'true' OR tampil_di_berita::text = 't' OR tampil_di_berita::text = '1'
            ORDER BY tanggal_kegiatan DESC 
            LIMIT 6
        ")->fetchAll();
    }
} catch(PDOException $e) {
    // Set default values jika ada error
    $total_msh = 0;
    $total_kohai = 0;
    $total_lokasi = 0;
    $total_kegiatan = 0;
    $msh_data = [];
    $kegiatan_data = [];
    $berita_kegiatan = [];
    
    // Log error untuk monitoring
    error_log("Guest Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#00174b">
    <title>YPOK - Yayasan Pendidikan Olahraga Karate</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/jpeg" href="assets/icons/icon-192x192.jpg">
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ypok-blue: #00174b;
            --ypok-blue-light: #002d7a;
            --ypok-blue-dark: #000b28;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --red-accent: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--white);
            color: var(--gray-700);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 {
            font-family: 'Roboto', sans-serif;
            color: var(--ypok-blue);
            font-weight: 700;
        }

        /* Loading Screen */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .loader.hidden {
            opacity: 0;
            visibility: hidden; 
        }

        .loader-text {
            font-size: 20px;
            color: var(--ypok-blue);
            font-family: 'Roboto', sans-serif;
            font-weight: 600;
        }

        /* Header */
        .header {
            background: var(--white);
            color: var(--ypok-blue);
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid var(--ypok-blue);
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header.scrolled {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            font-size: 36px;
        }

        .brand-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--ypok-blue);
            letter-spacing: 0.5px;
        }

        .brand-text p {
            font-size: 10px;
            color: var(--gray-600);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-links a:hover {
            color: var(--ypok-blue);
        }

        .btn-login {
            background: var(--ypok-blue);
            color: var(--white);
            padding: 10px 25px;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-login:hover {
            background: var(--ypok-blue-light);
        }

        /* Hero Section */
        .hero {
            min-height: 85vh;
            background: linear-gradient(135deg, var(--ypok-blue) 0%, var(--ypok-blue-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            margin-top: 70px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 60px 30px;
        }

        .hero-subtitle {
            font-size: 14px;
            color: var(--white);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            font-weight: 600;
            opacity: 0.9;
        }

        .hero h2 {
            font-size: 48px;
            margin-bottom: 25px;
            font-weight: 900;
            line-height: 1.2;
            color: var(--white);
        }

        .hero-description {
            font-size: 18px;
            color: var(--white);
            margin-bottom: 40px;
            line-height: 1.7;
            opacity: 0.95;
        }

        .hero-cta {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 15px 35px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--white);
            color: var(--ypok-blue);
        }

        .btn-primary:hover {
            background: var(--gray-100);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .btn-secondary:hover {
            background: var(--white);
            color: var(--ypok-blue);
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
        }

        .scroll-indicator span {
            display: block;
            width: 24px;
            height: 40px;
            border: 2px solid var(--white);
            border-radius: 20px;
            position: relative;
        }

        .scroll-indicator span::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            width: 4px;
            height: 8px;
            background: var(--white);
            border-radius: 2px;
            transform: translateX(-50%);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }

        /* Section */
        .section {
            padding: 80px 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-subtitle {
            font-size: 13px;
            color: var(--ypok-blue);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--ypok-blue);
        }

        .section-description {
            font-size: 16px;
            color: var(--gray-600);
            max-width: 700px;
            margin: 20px auto 0;
            line-height: 1.7;
        }

        /* Filosofi Section */
        .filosofi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .filosofi-content h3 {
            font-size: 28px;
            margin-bottom: 25px;
            color: var(--ypok-blue);
        }

        .filosofi-content p {
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: justify;
            color: var(--gray-700);
        }

        .filosofi-image {
            background: var(--gray-50);
            padding: 50px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid var(--gray-200);
        }

        .karate-icon {
            font-size: 150px;
        }

        /* Prinsip Cards */
        .prinsip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .prinsip-card {
            background: var(--white);
            padding: 35px 25px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid var(--gray-200);
        }

        .prinsip-card:hover {
            transform: translateY(-5px);
            border-color: var(--ypok-blue);
            box-shadow: 0 8px 20px rgba(0, 23, 75, 0.1);
        }

        .prinsip-icon {
            font-size: 50px;
            margin-bottom: 20px;
            display: block;
        }

        .prinsip-card h4 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--ypok-blue);
        }

        .prinsip-card p {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 14px;
        }

        /* Stats Section */
        .stats-section {
            background: var(--gray-50);
            padding: 60px 0;
            border-top: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item {
            position: relative;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 900;
            color: var(--ypok-blue);
            margin-bottom: 8px;
            font-family: 'Roboto', sans-serif;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* MSH Gallery */
        .msh-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .msh-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
            border: 2px solid var(--gray-200);
            display: flex;
            flex-direction: column;
        }

        .msh-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 23, 75, 0.1);
            border-color: var(--ypok-blue);
        }

        .msh-photo {
            width: 100%;
            height: 280px;
            object-fit: cover;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: var(--ypok-blue);
            flex-shrink: 0;
        }

        .msh-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .msh-info h4 {
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--ypok-blue);
            font-weight: 700;
        }

        .msh-badge {
            display: inline-block;
            background: var(--ypok-blue);
            color: var(--white);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            margin: 2px 4px 2px 0;
        }

        .msh-year {
            font-size: 12px;
            color: var(--gray-600);
            padding: 8px 12px;
            background: var(--gray-100);
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
            margin-top: auto;
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
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            color: var(--gray-900);
            font-size: 15px;
            transition: all 0.2s;
            font-family: 'Open Sans', sans-serif;
        }

        .msh-search-input:focus {
            outline: none;
            border-color: var(--ypok-blue);
        }

        .msh-search-input::placeholder {
            color: var(--gray-600);
        }

        .msh-search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--ypok-blue);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .msh-search-btn:hover {
            background: var(--ypok-blue-light);
        }

        .msh-search-btn svg {
            width: 18px;
            height: 18px;
            fill: var(--white);
        }

        .msh-stats {
            text-align: center;
            margin: 15px 0;
            color: var(--gray-600);
            font-size: 13px;
        }

        .msh-loading {
            text-align: center;
            padding: 50px 20px;
            color: var(--ypok-blue);
            font-size: 16px;
        }

        .msh-search-prompt {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-600);
        }

        .msh-search-prompt h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--ypok-blue);
            margin-bottom: 10px;
        }

        .msh-search-prompt p {
            font-size: 15px;
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
        }

        .msh-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray-600);
        }

        .msh-empty .icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .msh-load-more {
            text-align: center;
            margin-top: 30px;
        }

        .btn-load-more {
            background: var(--ypok-blue);
            color: var(--white);
            padding: 12px 35px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Open Sans', sans-serif;
        }

        .btn-load-more:hover {
            background: var(--ypok-blue-light);
        }

        .btn-load-more:disabled {
            background: var(--gray-300);
            color: var(--gray-600);
            cursor: not-allowed;
        }

        /* Timeline Section */
        .timeline {
            position: relative;
            max-width: 900px;
            margin: 60px auto 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--ypok-blue);
            transform: translateX(-50%);
        }

        .timeline-item {
            margin-bottom: 50px;
            position: relative;
            width: 50%;
            padding-right: 40px;
        }

        .timeline-item:nth-child(even) {
            margin-left: 50%;
            padding-right: 0;
            padding-left: 40px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            top: 20px;
            right: -7px;
            width: 14px;
            height: 14px;
            background: var(--ypok-blue);
            border-radius: 50%;
            border: 3px solid var(--white);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .timeline-item:nth-child(even)::before {
            left: -7px;
            right: auto;
        }

        .timeline-content {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            border: 2px solid var(--gray-200);
        }

        .timeline-year {
            font-size: 20px;
            color: var(--ypok-blue);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .timeline-content p {
            color: var(--gray-700);
            line-height: 1.7;
        }

        /* Program Section */
        .program-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .program-card {
            background: var(--white);
            padding: 30px;
            border-radius: 8px;
            border: 2px solid var(--gray-200);
            border-left: 4px solid var(--ypok-blue);
            transition: all 0.3s;
        }

        .program-card:hover {
            transform: translateX(5px);
            border-color: var(--ypok-blue);
            box-shadow: 0 8px 20px rgba(0, 23, 75, 0.1);
        }

        .program-card h4 {
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--ypok-blue);
        }

        .program-card h4 span {
            font-size: 26px;
        }

        .program-card p {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 14px;
        }

        /* News/Berita Section */
        .news-section {
            background: var(--white);
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .news-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .news-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 23, 75, 0.15);
        }
        
        .news-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: var(--gray-100);
        }
        
        .news-content {
            padding: 25px;
        }
        
        .news-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            font-size: 13px;
            color: var(--gray-600);
        }
        
        .news-category {
            background: var(--ypok-blue);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .news-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .news-card h4 {
            font-size: 18px;
            margin-bottom: 12px;
            color: var(--ypok-blue);
            line-height: 1.4;
        }
        
        .news-excerpt {
            color: var(--gray-600);
            font-size: 14px;
            line-height: 1.7;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .news-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-600);
        }
        
        .news-empty p {
            font-size: 16px;
            margin-top: 15px;
        }

        /* Contact Section */
        .contact-section {
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .contact-card {
            background: var(--white);
            padding: 35px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid var(--gray-200);
            transition: all 0.3s;
        }

        .contact-card:hover {
            border-color: var(--ypok-blue);
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 23, 75, 0.1);
        }

        .contact-icon {
            font-size: 44px;
            margin-bottom: 18px;
            display: block;
        }

        .contact-card h4 {
            font-size: 18px;
            margin-bottom: 12px;
            color: var(--ypok-blue);
        }

        .contact-card p {
            color: var(--gray-600);
            line-height: 1.7;
        }

        .contact-card a {
            color: var(--ypok-blue);
            text-decoration: none;
            transition: color 0.2s;
        }

        /* Footer */
        .footer {
            background: var(--ypok-blue);
            color: var(--white);
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 50px;
            margin-bottom: 40px;
        }

        .footer-about h3 {
            font-size: 22px;
            margin-bottom: 18px;
            color: var(--white);
        }

        .footer-about p {
            line-height: 1.7;
            margin-bottom: 12px;
            opacity: 0.95;
        }

        .footer-links h4 {
            font-size: 18px;
            margin-bottom: 18px;
            color: var(--white);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
            opacity: 0.9;
        }

        .footer-links a:hover {
            opacity: 1;
            transform: translateX(3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white);
            font-size: 13px;
            opacity: 0.9;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero h2 { font-size: 40px; }
            .section-title { font-size: 32px; }
            .filosofi-grid, .footer-content { grid-template-columns: 1fr; }
            .timeline::before { left: 30px; }
            .timeline-item { width: 100%; padding-right: 0; padding-left: 60px; }
            .timeline-item:nth-child(even) { margin-left: 0; padding-left: 60px; }
            .timeline-item::before { left: 23px; right: auto; }
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h2 { font-size: 36px; }
            .hero-description { font-size: 16px; }
            .container, .nav-container { padding: 0 20px; }
            .section { padding: 60px 0; }
            .section-header { margin-bottom: 40px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            
            /* MSH Gallery Responsive */
            .msh-gallery {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .msh-photo {
                height: 250px;
            }
            
            .msh-info {
                padding: 15px;
            }
            
            .msh-info h4 {
                font-size: 16px;
            }
        }

        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: var(--ypok-blue);
            color: var(--white);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--ypok-blue-light);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader">
        <div class="loader-text"><img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK" style="width: 40px; height: 40px; object-fit: contain; vertical-align: middle; margin-right: 8px;"> YPOK</div>
    </div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="nav-container">
            <div class="logo-section">
                <img src="assets/images/LOGO YPOK NO BACKGROUND.png" alt="YPOK Logo" class="logo" style="width: 60px; height: 60px; object-fit: contain;">
                <div class="brand-text">
                    <h1>YPOK</h1>
                    <p>Yayasan Pendidikan Olahraga Karate</p>
                </div>
            </div>
            <nav class="nav-links">
                <a href="#home">Beranda</a>
                <a href="#tentang">Tentang</a>
                <a href="#master">Majelis Sabuk Hitam</a>
                <a href="#program">Program</a>
                <a href="#berita">Berita</a>
                <a href="#kontak">Kontak</a>
                <a href="index.php" class="btn-login">Login Admin</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-subtitle">Yayasan Pendidikan Olahraga Karate</div>
            <h2>YPOK</h2>
            <p class="hero-description">
                Program Pendidikan Olahraga Karate untuk Sekolah-Sekolah di Indonesia. Membentuk Karateka Profesional Melalui Pendidikan dan Pelatihan yang Berkualitas.
            </p>
            <div class="hero-cta">
                <a href="#tentang" class="btn-primary">Tentang YPOK</a>
                <a href="#kontak" class="btn-secondary">Hubungi Kami</a>
            </div>
        </div>
        <div class="scroll-indicator">
            <span></span>
        </div>
    </section>

    <!-- Tentang YPOK Section -->
    <section class="section" id="tentang">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Tentang Kami</div>
                <h2 class="section-title">Yayasan Pendidikan Olahraga Karate</h2>
                <p class="section-description">
                    YPOK (sebelumnya PPOK) adalah organisasi pendidikan olahraga karate yang fokus pada 
                    pengembangan karateka profesional di lingkungan pendidikan formal Indonesia.
                </p>
            </div>

            <div class="filosofi-grid fade-in">
                <div class="filosofi-content">
                    <h3>Sejarah Pendirian</h3>
                    <p>
                        Yayasan Pendidikan Olahraga Karate (YPOK) berawal pada tanggal 1 Juli 2005, didirikan oleh 
                        <strong>Idris Buhang Olii DAN III</strong> (Nomor Sabuk Hitam Internasional JKA: ID 2-0064, 
                        MSH Nasional: 1407), seorang atlet karateka era 80-an, bersama istrinya 
                        <strong>Nur Maryam, AMd.Keb</strong>.
                    </p>
                    <p>
                        Ide pembentukan Program Pendidikan Olahraga Karate (PPOK) di sekolah-sekolah diajukan kepada 
                        Dinas Pendidikan DKI Jakarta. Ide ini mendapat respons positif dengan diterbitkannya 
                        <strong>Surat No. 54/1.851.5 Tahun 2007</strong> oleh Kepala Dinas Pendidikan Dasar Provinsi DKI Jakarta, 
                        Prof. Dr. Hj. Sylviana Murni, S.H., M.Si.
                    </p>
                    <p>
                        Mengingat jumlah murid yang terus bertambah, Pengurus INKAI Pusat menerbitkan 
                        <strong>SK No.17/SK/INPUS/VII/2006</strong> yang ditandatangani oleh Ketua Umum INKAI Pusat, 
                        Jenderal TNI (Purn.) Ryamizard Ryacudu, dan Sekjen INKAI Pusat, Prof. DR. Hermawan Sulistyo.
                    </p>
                    <p>
                        Pada tahun <strong>2013</strong>, organisasi berganti nama dari PPOK menjadi YPOK untuk 
                        memiliki legalitas berbadan hukum, agar dapat berkoordinasi dan bekerja sama dengan 
                        instansi terkait, pemerintah, serta mendapatkan dukungan sponsorship yang lebih baik.
                    </p>
                </div>
                <div class="filosofi-image">
                    <div class="karate-icon">🥋</div>
                </div>
            </div>

            <!-- Prinsip Cards -->
            <div class="prinsip-grid">
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">🎯</span>
                    <h4>Pendidikan Berkualitas</h4>
                    <p>Mengintegrasikan karate dalam pendidikan formal dengan standar kurikulum yang terstruktur dan berkualitas sesuai ketentuan JKA dan INKAI.</p>
                </div>
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">💪</span>
                    <h4>Karateka Profesional</h4>
                    <p>Membentuk generasi karateka yang profesional, disiplin, dan berkarakter melalui program pelatihan yang sistematis dan berkelanjutan.</p>
                </div>
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">🏫</span>
                    <h4>Afiliasi Resmi</h4>
                    <p>Beroperasi sebagai afiliasi resmi INKAI Pusat dengan dukungan penuh dari Dinas Pendidikan DKI Jakarta dan instansi terkait.</p>
                </div>
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">📚</span>
                    <h4>Kurikulum Terstandar</h4>
                    <p>Menggunakan kurikulum yang sesuai dengan standar JKA (Japan Karate Association) dan disesuaikan dengan tingkat pendidikan di Indonesia.</p>
                </div>
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">🤝</span>
                    <h4>Kolaborasi Pendidikan</h4>
                    <p>Bekerja sama dengan sekolah-sekolah dari tingkat TK hingga SMA se-DKI Jakarta untuk pengembangan program pendidikan karate.</p>
                </div>
                <div class="prinsip-card fade-in">
                    <span class="prinsip-icon">🌟</span>
                    <h4>Legalitas Berbadan Hukum</h4>
                    <p>Memiliki legalitas berbadan hukum sejak 2013 untuk memudahkan koordinasi dengan pemerintah dan instansi terkait.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_msh; ?></div>
                    <div class="stat-label">Majelis Sabuk Hitam</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_kohai; ?></div>
                    <div class="stat-label">Anggota Aktif</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_lokasi; ?></div>
                    <div class="stat-label">Lokasi Dojo</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_kegiatan; ?></div>
                    <div class="stat-label">Kegiatan Tahun Ini</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Majelis Sabuk Hitam Section -->
    <section class="section" id="master">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Guru dan Pembimbing</div>
                <h2 class="section-title">Majelis Sabuk Hitam</h2>
                <p class="section-description">
                    Para instruktur bersertifikat yang membimbing program pendidikan karate 
                    di sekolah-sekolah dengan dedikasi dan profesionalisme tinggi.
                </p>
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
            <div id="mshStats" class="msh-stats"></div>

            <!-- MSH Gallery Container -->
            <div id="mshGalleryContainer" class="msh-gallery">
                <div class="msh-search-prompt" style="grid-column: 1/-1; text-align: center; padding: 80px 20px; color: var(--text-light);">
                    <div style="font-size: 72px; margin-bottom: 24px; opacity: 0.6;">🔍</div>
                    <h3 style="font-size: 24px; font-weight: 600; color: var(--text); margin-bottom: 12px;">Cari Majelis Sabuk Hitam</h3>
                    <p style="font-size: 16px; line-height: 1.6; max-width: 500px; margin: 0 auto;">
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

    <!-- Sejarah Timeline -->
    <section class="section">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Perjalanan Kami</div>
                <h2 class="section-title">Sejarah YPOK</h2>
                <p class="section-description">
                    Dari program pendidikan karate di sekolah-sekolah hingga menjadi yayasan 
                    berbadan hukum, perjalanan YPOK dimulai sejak 1 Juli 2005.
                </p>
            </div>

            <div class="timeline">
                <div class="timeline-item fade-in">
                    <div class="timeline-content">
                        <div class="timeline-year">1 Juli 2005</div>
                        <p>PPOK (Program Pendidikan Olahraga Karate) didirikan oleh Idris Buhang Olii DAN III (No. MSH Nasional 1407, No. Sabuk Hitam Internasional JKA: ID 2-0064) dan istrinya Nur Maryam, AMd.Keb. Ide pembentukan program pendidikan karate di sekolah-sekolah diajukan kepada Dinas Pendidikan DKI Jakarta.</p>
                    </div>
                </div>
                <div class="timeline-item fade-in">
                    <div class="timeline-content">
                        <div class="timeline-year">2006</div>
                        <p>Pengurus INKAI Pusat menerbitkan SK Kepengurusan PPOK No.17/SK/INPUS/VII/2006, ditandatangani oleh Ketua Umum INKAI Pusat Jenderal TNI (Purn.) Ryamizard Ryacudu dan Sekjen INKAI Pusat Prof. DR. Hermawan Sulistyo. Program dimulai di beberapa sekolah dasar di Jakarta Pusat.</p>
                    </div>
                </div>
                <div class="timeline-item fade-in">
                    <div class="timeline-content">
                        <div class="timeline-year">2007</div>
                        <p>Dinas Pendidikan Dasar Provinsi DKI Jakarta merespons positif dengan menerbitkan Surat No. 54/1.851.5 Tahun 2007, ditandatangani oleh Kepala Dinas Pendidikan Dasar Provinsi DKI Jakarta Prof. Dr. Hj. Sylviana Murni, S.H., M.Si. PPOK resmi menjadi program afiliasi pusat INKAI.</p>
                    </div>
                </div>
                <div class="timeline-item fade-in">
                    <div class="timeline-content">
                        <div class="timeline-year">2009</div>
                        <p>Kepala Dinas Pendidikan Provinsi DKI Jakarta menerbitkan Surat No. 4502/1.857.21 tentang Program Pendidikan Olahraga Karate. Program PPOK berkembang ke berbagai sekolah se-DKI Jakarta dengan semangat memajukan karateka profesional.</p>
                    </div>
                </div>
                <div class="timeline-item fade-in">
                    <div class="timeline-content">
                        <div class="timeline-year">2013</div>
                        <p>Pergantian nama dari PPOK menjadi YPOK (Yayasan Pendidikan Olahraga Karate) diprakarsai oleh Idris Olii. Perubahan ini dilakukan agar organisasi memiliki legalitas berbadan hukum untuk dapat berkoordinasi dan bekerja sama dengan instansi terkait, pemerintah, dan sponsorship.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Section -->
    <section class="section" id="program">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Pelatihan & Kegiatan</div>
                <h2 class="section-title">Program Kami</h2>
                <p class="section-description">
                    Program pendidikan karate terintegrasi untuk berbagai jenjang pendidikan 
                    dari TK hingga SMA, dengan kurikulum terstandar JKA dan INKAI.
                </p>
            </div>

            <div class="program-grid">
                <div class="program-card fade-in">
                    <h4><span>🏫</span> Karate di TK</h4>
                    <p>Program Pendidikan Olahraga Karate untuk Taman Kanak-Kanak dengan metode pembelajaran yang sesuai dengan usia dini, membangun fondasi karakter dan motorik anak.</p>
                </div>
                <div class="program-card fade-in">
                    <h4><span>📚</span> Karate di SD</h4>
                    <p>Program pendidikan karate untuk Sekolah Dasar yang mengintegrasikan pembelajaran karate dengan kurikulum pendidikan, fokus pada disiplin dan prestasi akademis.</p>
                </div>
                <div class="program-card fade-in">
                    <h4><span>🎓</span> Karate di SMP</h4>
                    <p>Program karate untuk Sekolah Menengah Pertama dengan penekanan pada pengembangan karakter, kepemimpinan, dan teknik karate tingkat menengah.</p>
                </div>
                <div class="program-card fade-in">
                    <h4><span>🏆</span> Karate di SMA</h4>
                    <p>Program pendidikan karate untuk Sekolah Menengah Atas yang mempersiapkan siswa menjadi karateka profesional dan instruktur masa depan.</p>
                </div>
                <div class="program-card fade-in">
                    <h4><span>👨‍🏫</span> Pelatihan Instruktur</h4>
                    <p>Program sertifikasi dan pembinaan instruktur karate sesuai standar JKA dan INKAI untuk menghasilkan pengajar karate yang berkualitas dan profesional.</p>
                </div>
                <div class="program-card fade-in">
                    <h4><span>🥋</span> Afiliasi INKAI</h4>
                    <p>YPOK beroperasi sebagai afiliasi resmi INKAI Pusat, mengikuti standar dan kurikulum nasional dalam pengembangan karate di Indonesia.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Berita/News Section -->
    <section class="section news-section" id="berita">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Kegiatan Terkini</div>
                <h2 class="section-title">Berita & Kegiatan</h2>
                <p class="section-description">
                    Informasi terbaru tentang kegiatan, pelatihan, dan pencapaian YPOK
                </p>
            </div>

            <?php if(count($berita_kegiatan) > 0): ?>
                <div class="news-grid">
                    <?php foreach($berita_kegiatan as $berita): ?>
                        <div class="news-card fade-in">
                            <?php if(!empty($berita['foto'])): ?>
                                <img src="uploads/kegiatan/<?php echo htmlspecialchars($berita['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($berita['nama_kegiatan']); ?>" 
                                     class="news-image">
                            <?php else: ?>
                                <div class="news-image" style="background: linear-gradient(135deg, var(--ypok-blue), var(--ypok-blue-light)); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                    🥋
                                </div>
                            <?php endif; ?>
                            
                            <div class="news-content">
                                <div class="news-meta">
                                    <span class="news-category"><?php echo htmlspecialchars($berita['jenis_kegiatan']); ?></span>
                                    <span class="news-date">📅 <?php echo date('d M Y', strtotime($berita['tanggal_kegiatan'])); ?></span>
                                </div>
                                
                                <h4><?php echo htmlspecialchars($berita['nama_kegiatan']); ?></h4>
                                
                                <?php if(!empty($berita['keterangan'])): ?>
                                    <p class="news-excerpt"><?php echo htmlspecialchars($berita['keterangan']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="news-empty fade-in">
                    <div style="font-size: 64px;">📰</div>
                    <p>Belum ada berita kegiatan yang dipublikasikan</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact-section" id="kontak">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Hubungi Kami</div>
                <h2 class="section-title">Informasi Kontak</h2>
                <p class="section-description">
                    Tertarik untuk bergabung atau ingin informasi lebih lanjut? 
                    Jangan ragu untuk menghubungi kami.
                </p>
            </div>

            <div class="contact-grid">
                <div class="contact-card fade-in">
                    <span class="contact-icon">📍</span>
                    <h4>Alamat Kantor</h4>
                    <p>
                        Jl. Karate Raya No. 123<br>
                        Jakarta Selatan 12345<br>
                        Indonesia
                    </p>
                </div>
                <div class="contact-card fade-in">
                    <span class="contact-icon">📞</span>
                    <h4>Telepon</h4>
                    <p>
                        <a href="tel:+6281234567890">+62 812-3456-7890</a><br>
                        <a href="tel:+6287654321098">+62 876-5432-1098</a>
                    </p>
                </div>
                <div class="contact-card fade-in">
                    <span class="contact-icon">✉️</span>
                    <h4>Email</h4>
                    <p>
                        <a href="mailto:info@ypok.or.id">info@ypok.or.id</a><br>
                        <a href="mailto:sekretariat@ypok.or.id">sekretariat@ypok.or.id</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <h3>YPOK</h3>
                    <p>
                        Yayasan Pendidikan Olahraga Karate (sebelumnya PPOK) adalah organisasi pendidikan 
                        karate yang fokus pada pengembangan karateka profesional di sekolah-sekolah. 
                        Didirikan sejak 1 Juli 2005 sebagai afiliasi INKAI Pusat dengan dukungan 
                        Dinas Pendidikan DKI Jakarta.
                    </p>
                    <p style="margin-top: 20px;">
                        🥋 Membentuk Karateka Profesional
                    </p>
                </div>
                <div class="footer-links">
                    <h4>Menu Cepat</h4>
                    <ul>
                        <li><a href="#home">Beranda</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                        <li><a href="#master">Majelis Sabuk Hitam</a></li>
                        <li><a href="#program">Program</a></li>
                        <li><a href="#berita">Berita</a></li>
                        <li><a href="#kontak">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Admin</h4>
                    <ul>
                        <li><a href="index.php">Login Admin</a></li>
                        <li><a href="register.php">Daftar Admin</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 YPOK - Yayasan Pendidikan Olahraga Karate. Didirikan 1 Juli 2005.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <div class="back-to-top" id="backToTop">↑</div>

    <!-- Scripts -->
    <script>
        // Loader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.querySelector('.loader').classList.add('hidden');
            }, 1000);
        });

        // Header scroll effect
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            if(window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Fade in animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Back to top button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if(window.scrollY > 500) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Counter animation for stats
        const animateCounter = (element) => {
            const target = parseInt(element.textContent);
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += increment;
                if(current < target) {
                    element.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target;
                }
            };

            updateCounter();
        };

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const numbers = entry.target.querySelectorAll('.stat-number');
                    numbers.forEach(num => animateCounter(num));
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if(statsSection) {
            statsObserver.observe(statsSection);
        }

        // MSH Search Functionality
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
                
                // Check if response is ok
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    throw new Error(`HTTP Error ${response.status}: ${errorText.substring(0, 100)}`);
                }
                
                const result = await response.json();
                console.log('API Response:', result); // Debug log

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
                        // Remove loading indicator
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

                    // Update offset
                    mshCurrentOffset = offset;
                } else {
                    console.error('API Error:', result);
                    throw new Error(result.message || result.error || 'Gagal memuat data');
                }
            } catch (error) {
                console.error('Error loading MSH data:', error);
                container.innerHTML = `
                    <div class="msh-empty" style="grid-column: 1/-1;">
                        <div class="icon">⚠️</div>
                        <p>Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
                        <p style="font-size: 12px; color: var(--text-light); margin-top: 10px;">Error: ${error.message}</p>
                    </div>
                `;
                loadMoreDiv.style.display = 'none';
            }

            isLoadingMsh = false;
        }

        // Create MSH card element
        function createMshCard(msh) {
            const card = document.createElement('div');
            card.className = 'msh-card fade-in visible';
            
            // API already returns full path (uploads/msh/filename.jpg), use directly
            const photoHtml = msh.foto 
                ? `<img src="${msh.foto}" alt="${msh.nama}" class="msh-photo" onerror="this.parentElement.innerHTML='<div class=\\'msh-photo\\'>🥋</div>'">`
                : '<div class="msh-photo">🥋</div>';

            // Format tanggal lahir
            let tempatTglLahir = '';
            if (msh.tempat_lahir || msh.tanggal_lahir) {
                const tempat = msh.tempat_lahir || '-';
                let tanggal = '-';
                if (msh.tanggal_lahir) {
                    const d = new Date(msh.tanggal_lahir);
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    tanggal = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                }
                tempatTglLahir = `${tempat}, ${tanggal}`;
            }

            // Jenis kelamin
            const jenisKelamin = msh.jenis_kelamin === 'L' ? 'Laki-laki' : 
                                msh.jenis_kelamin === 'P' ? 'Perempuan' : '-';

            card.innerHTML = `
                ${photoHtml}
                <div class="msh-info">
                    <h4>${msh.nama}</h4>
                    ${msh.kode_msh ? `<div style="font-size: 12px; color: var(--gray-600); font-weight: 600; margin-bottom: 8px;">📋 No. MSH: ${msh.kode_msh}</div>` : ''}
                    
                    <div style="margin: 10px 0;">
                        <span class="msh-badge">${msh.tingkat_dan || 'Dan 1'}</span>
                        <span class="msh-badge" style="background: ${msh.jenis_kelamin === 'P' ? '#ec4899' : '#3b82f6'};">
                            ${jenisKelamin}
                        </span>
                    </div>
                    
                    ${tempatTglLahir ? `<div style="font-size: 13px; color: var(--gray-700); margin-top: 10px; line-height: 1.6;">
                        <strong>📍 Tempat, Tgl Lahir:</strong><br>
                        ${tempatTglLahir}
                    </div>` : ''}
                    
                    ${msh.dojo_cabang ? `<div style="font-size: 13px; color: var(--gray-700); margin-top: 8px;">
                        <strong>🥋 Dojo:</strong> ${msh.dojo_cabang}
                    </div>` : ''}
                    
                    ${msh.no_telp ? `<div style="font-size: 13px; color: var(--gray-700); margin-top: 8px;">
                        <strong>📞 Telepon:</strong> ${msh.no_telp}
                    </div>` : ''}
                    
                    ${msh.email ? `<div style="font-size: 12px; color: var(--gray-600); margin-top: 8px; word-break: break-all;">
                        <strong>✉️ Email:</strong> ${msh.email}
                    </div>` : ''}
                    
                    ${msh.alamat ? `<div style="font-size: 12px; color: var(--gray-600); margin-top: 8px; line-height: 1.5;">
                        <strong>🏠 Alamat:</strong> ${msh.alamat.substring(0, 80)}${msh.alamat.length > 80 ? '...' : ''}
                    </div>` : ''}
                    
                    ${msh.tahun_bergabung ? `<div class="msh-year" style="margin-top: 10px;">Bergabung: ${msh.tahun_bergabung}</div>` : ''}
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
                    <div class="msh-search-prompt" style="grid-column: 1/-1; text-align: center; padding: 80px 20px; color: var(--text-light);">
                        <div style="font-size: 72px; margin-bottom: 24px; opacity: 0.6;">🔍</div>
                        <h3 style="font-size: 24px; font-weight: 600; color: var(--text); margin-bottom: 12px;">Cari Majelis Sabuk Hitam</h3>
                        <p style="font-size: 16px; line-height: 1.6; max-width: 500px; margin: 0 auto;">
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

            // Load search results
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

        // Don't auto-load data - only load when user searches
        // Data will be loaded by handleMshSearch() when user clicks search or presses Enter
    </script>
</body>
</html>

