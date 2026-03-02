# 🥋 YPOK Management System

Sistem Manajemen untuk **Yayasan Pengembangan Olahraga Karate (YPOK)** - Platform digital untuk mengelola master data MSH (Member/Sensei/Head), kohai (anggota), kegiatan, keuangan, dan administrasi organisasi karate.

![YPOK Logo](assets/images/logo%20ypok%20.jpg)

## ✨ Fitur Utama

### 👥 Master Data Management
- **Data MSH** - Manajemen member, sensei, dan head organisasi
- **Data Kohai** - Database anggota dengan import CSV batch
- **Data Lokasi** - Manajemen provinsi dan dojo per wilayah

### 📊 Dashboard & Analytics
- Dashboard interaktif dengan Chart.js
- Statistik real-time anggota aktif/non-aktif
- Laporan kegiatan dan keuangan
- Export laporan ke PDF dan Excel

### 💰 Keuangan & Pembayaran
- Sistem pembayaran dan invoice
- Toko internal untuk merchandise
- Laporan keuangan lengkap
- Export data keuangan

### 📝 Administrasi
- Pendaftaran anggota baru
- Manajemen legalitas organisasi
- Pengurus dan struktur organisasi
- Template surat otomatis
- Google Sheets integration

### 📱 Progressive Web App (PWA)
- Install sebagai aplikasi desktop/mobile
- Offline capability dengan Service Worker
- Responsive design untuk semua device

### 🔐 Authentication & Authorization
- Login/register dengan email
- Reset password functionality
- Session management
- Role-based access control

## 🚀 Tech Stack

- **Backend**: PHP 7.4+
- **Database**: Supabase (PostgreSQL)
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js
- **PWA**: Service Workers, Web Manifest
- **Deployment**: Vercel
- **Version Control**: Git & GitHub

## 📦 Installation

### Prerequisites
- PHP 7.4 atau lebih tinggi
- Composer (optional)
- Supabase account
- Git

### Local Development

1. **Clone repository**
```bash
git clone https://github.com/YOUR_USERNAME/ypok_management.git
cd ypok_management
```

2. **Setup environment variables**
```bash
cp .env.example .env
```

Edit `.env` dengan kredensial Supabase Anda:
```env
SUPABASE_URL=your_supabase_url
SUPABASE_ANON_KEY=your_supabase_anon_key
```

3. **Buat file konfigurasi Supabase**

Buat file `config/supabase.php`:
```php
<?php
session_start();

// Supabase Configuration
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'your_supabase_url');
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY') ?: 'your_anon_key');

// Database Connection (PDO)
$host = 'your_db_host';
$dbname = 'postgres';
$user = 'postgres';
$password = 'your_password';
$port = '5432';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

4. **Jalankan server lokal**
```bash
php -S localhost:8000
```

Buka browser: `http://localhost:8000`

## 🌐 Deploy ke Vercel

### 1. Install Vercel CLI
```bash
npm i -g vercel
```

### 2. Login ke Vercel
```bash
vercel login
```

### 3. Deploy
```bash
vercel
```

### 4. Set Environment Variables di Vercel Dashboard
- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`

## 📚 Dokumentasi

Dokumentasi lengkap tersedia di folder `docs/`:

- [Quickstart Supabase](docs/QUICKSTART_SUPABASE.md)
- [Cara Jalankan Localhost](docs/CARA_JALANKAN_LOCALHOST.md)
- [Migrasi Database](docs/MIGRASI_SUPABASE.md)
- [Responsive Design Guide](docs/RESPONSIVE_DESIGN_GUIDE.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)

## 📁 Struktur Project

```
ypok_management/
├── pages/                  # Halaman aplikasi (modular)
│   ├── msh/               # Data MSH
│   │   ├── index.php
│   │   ├── add.php  
│   │   ├── edit.php
│   │   ├── detail.php
│   │   └── import.php
│   ├── kohai/             # Data Kohai
│   ├── kegiatan/          # Kegiatan
│   ├── lokasi/            # Lokasi & Provinsi
│   ├── pembayaran/        # Pembayaran
│   ├── pendaftaran/       # Pendaftaran
│   ├── legalitas/         # Legalitas
│   ├── toko/              # Toko
│   ├── laporan/           # Laporan
│   ├── pengurus/          # Pengurus
│   └── surat/             # Surat
│
├── api/                    # API Endpoints
│   ├── msh.php
│   ├── kohai.php
│   ├── kegiatan.php
│   └── ...
│
├── actions/                # Action handlers
├── assets/                 # CSS, JS, images, icons
├── auth/                   # Authentication logic
├── components/             # Reusable components (navbar, etc)
├── config/                 # Configuration files
├── database/               # Database migrations & schemas
├── docs/                   # Documentation
├── export/                 # Exported files (gitignored)
├── googlesheet/            # Google Sheets integration
├── uploads/                # User uploads (gitignored)
│
├── index.php               # Login page
├── dashboard.php           # Main dashboard
├── guest_dashboard.php     # Public dashboard
├── manifest.json           # PWA manifest
├── sw.js                   # Service Worker
├── vercel.json             # Vercel deployment config
├── package.json            # Project metadata
├── .gitignore              # Git ignore rules
├── .env.example            # Environment template
└── README.md               # This file
```

## 🔧 Konfigurasi Database

Project ini menggunakan **Supabase** (PostgreSQL). Setup database:

1. Buat project di [Supabase](https://supabase.com)
2. Jalankan SQL migrations di SQL Editor:
   - Table: `users`, `msh`, `kohai`, `provinsi`, `dojo`, `kegiatan`, `pembayaran`, dll
3. Copy connection string dan credentials
4. Update `config/supabase.php`

## 🛡️ Security

- ✅ Environment variables untuk credentials
- ✅ Session-based authentication
- ✅ SQL prepared statements (PDO)
- ✅ Input sanitization
- ✅ HTTPS required in production
- ✅ CORS configuration

## 🤝 Contributing

Contributions are welcome! Silakan:

1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Developer

Developed with ❤️ for YPOK

## 📞 Support

Jika ada pertanyaan atau issue, silakan buka [GitHub Issues](https://github.com/YOUR_USERNAME/ypok_management/issues)

---

⭐ **Star this repo** if you find it helpful!
