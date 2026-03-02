# 🚀 Deployment Guide - YPOK Management System

Panduan lengkap untuk deploy YPOK Management System ke Vercel dengan database Supabase.

## 📋 Prerequisites

Sebelum deploy, pastikan Anda sudah punya:

- ✅ Akun [GitHub](https://github.com)
- ✅ Akun [Vercel](https://vercel.com)  
- ✅ Akun [Supabase](https://supabase.com)
- ✅ Git terinstall di komputer
- ✅ Project sudah rapih dan siap di-push

---

## 1️⃣ Setup Supabase Database

### A. Buat Project Supabase

1. **Login ke Supabase**
   - Buka https://supabase.com/dashboard
   - Klik **"New Project"**

2. **Isi detail project:**
   - Name: `ypok-management`
   - Database Password: Buat password kuat (SIMPAN!)
   - Region: Pilih terdekat (misal: `Southeast Asia (Singapore)`)
   - Klik **"Create new project"**

3. **Tunggu setup selesai** (~2 menit)

### B. Setup Database Tables

1. **Buka SQL Editor**
   - Dashboard > SQL Editor
   - Klik **"New query"**

2. **Jalankan SQL berikut** (satu per satu):

```sql
-- Table: users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: provinsi
CREATE TABLE provinsi (
    id SERIAL PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL,
    ibu_kota VARCHAR(100),
    logo_provinsi TEXT,
    total_dojo INTEGER DEFAULT 0,
    total_anggota INTEGER DEFAULT 0,
    anggota_aktif INTEGER DEFAULT 0,
    anggota_non_aktif INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Table: dojo
CREATE TABLE dojo (
    id SERIAL PRIMARY KEY,
    provinsi_id INTEGER REFERENCES provinsi(id) ON DELETE CASCADE,
    nama_dojo VARCHAR(100) NOT NULL,
    alamat TEXT,
    kota VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: msh
CREATE TABLE msh (
    id SERIAL PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(20),
    provinsi_id INTEGER REFERENCES provinsi(id),
    jabatan VARCHAR(50),
    status VARCHAR(20) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: kohai
CREATE TABLE kohai (
    id SERIAL PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(20),
    tanggal_lahir DATE,
    alamat TEXT,
    provinsi_id INTEGER REFERENCES provinsi(id),
    dojo_id INTEGER REFERENCES dojo(id),
    sabuk VARCHAR(20),
    status VARCHAR(20) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: kegiatan
CREATE TABLE kegiatan (
    id SERIAL PRIMARY KEY,
    nama_kegiatan VARCHAR(200) NOT NULL,
    tanggal DATE,
    lokasi VARCHAR(200),
    deskripsi TEXT,
    kategori VARCHAR(50),
    status VARCHAR(20) DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: pembayaran
CREATE TABLE pembayaran (
    id SERIAL PRIMARY KEY,
    kohai_id INTEGER REFERENCES kohai(id),
    jumlah DECIMAL(10,2),
    tanggal_bayar DATE,
    jenis_pembayaran VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending',
    bukti_transfer TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Table: legalitas
CREATE TABLE legalitas (
    id SERIAL PRIMARY KEY,
    nama_dokumen VARCHAR(200) NOT NULL,
    nomor_dokumen VARCHAR(100),
    tanggal_terbit DATE,
    tanggal_kadaluarsa DATE,
    file_path TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

3. **Enable Row Level Security (RLS)** - Optional untuk keamanan extra

### C. Dapatkan Credentials

1. **Database Credentials**
   - Dashboard > Settings > Database
   - Di **Connection String**, klik **"URI"**
   - Copy `Host`, `Database`, `User`, `Port`, `Password`

2. **API Credentials**
   - Dashboard > Settings > API
   - Copy **"Project URL"**
   - Copy **"anon public"** key

3. **Simpan semua credentials** ini!

---

## 2️⃣ Push ke GitHub

### A. Initialize Git

```bash
cd c:\xampp\htdocs\ypok_management\ypok_management
git init
git add .
git commit -m "Initial commit: YPOK Management System"
```

### B. Create GitHub Repository

1. Buka https://github.com/new
2. Repository name: `ypok-management`
3. Description: `YPOK Management System - Platform digital untuk organisasi karate`
4. **Private** atau **Public** (pilih sesuai kebutuhan)
5. **JANGAN** centang "Add README" (sudah ada)
6. Klik **"Create repository"**

### C. Push ke GitHub

```bash
git remote add origin https://github.com/YOUR_USERNAME/ypok-management.git
git branch -M main
git push -u origin main
```

Ganti `YOUR_USERNAME` dengan username GitHub Anda.

---

## 3️⃣ Deploy ke Vercel

### A. Import Project

1. **Login ke Vercel**
   - Buka https://vercel.com
   - Klik **"Add New"** > **"Project"**

2. **Import Git Repository**
   - Klik **"Import"** di samping repository `ypok-management`
   - Jika tidak muncul, klik **"Adjust GitHub App Permissions"**

3. **Configure Project**
   - Framework Preset: **Other**
   - Root Directory: `./` (default)
   - Build Command: (kosongkan)
   - Output Directory: (kosongkan)

### B. Environment Variables

Klik **"Environment Variables"** dan tambahkan:

| Name | Value |
|------|-------|
| `SUPABASE_URL` | `https://xxx.supabase.co` (dari Supabase) |
| `SUPABASE_ANON_KEY` | `eyJxxx...` (anon key dari Supabase) |
| `SUPABASE_HOST` | `db.xxx.supabase.co` |
| `SUPABASE_PORT` | `5432` |
| `SUPABASE_DB` | `postgres` |
| `SUPABASE_USER` | `postgres` |
| `SUPABASE_PASSWORD` | (password database Anda) |

### C. Deploy

1. Klik **"Deploy"**
2. Tunggu proses deployment (~2-3 menit)
3. Jika sukses, akan ada link: `https://ypok-management.vercel.app`

---

## 4️⃣ Post-Deployment Setup

### A. Test Website

1. Buka URL Vercel Anda
2. Test login/register
3. Test semua fitur utama

### B. Update PWA URLs

Jika domain berubah, update di:

**manifest.json:**
```json
{
  "start_url": "https://ypok-management.vercel.app/",
  "scope": "https://ypok-management.vercel.app/"
}
```

**sw.js:**
```javascript
const urlsToCache = [
  'https://ypok-management.vercel.app/',
  // ... urls lainnya
];
```

### C. Custom Domain (Optional)

1. Vercel Dashboard > Settings > Domains
2. Add domain Anda (misal: `ypok.com`)
3. Update DNS records sesuai instruksi
4. Tunggu propagasi (~24 jam)

---

## 🔧 Troubleshooting

### Error 500 - Database Connection

**Solusi:**
- Cek environment variables di Vercel
- Pastikan Supabase database online
- Verifikasi credentials benar

### PWA Tidak Muncul

**Solusi:**
- Vercel harus menggunakan HTTPS (default)
- Clear browser cache
- Update manifest.json dengan URL production

### File Upload Tidak Berfungsi

**Solusi:**
- Vercel serverless functions memiliki limit 4.5MB
- Gunakan Supabase Storage untuk file upload
- Update logic upload ke Supabase Storage API

---

## 📊 Monitoring

### Vercel Analytics

1. Dashboard > Analytics
2. Monitor traffic, performance, errors

### Supabase Logs

1. Dashboard > Logs
2. Monitor database queries
3. Check error logs

---

## 🔄 Update & Redeploy

Setiap kali ada perubahan code:

```bash
git add .
git commit -m "Describe your changes"
git push origin main
```

Vercel akan **auto-deploy** setiap push ke branch `main`.

---

## ✅ Checklist Deployment

- [ ] Supabase project dibuat
- [ ] Database tables dibuat
- [ ] Credentials disimpan
- [ ] GitHub repository dibuat
- [ ] Code di-push ke GitHub
- [ ] Vercel project dibuat
- [ ] Environment variables di-set
- [ ] Deploy berhasil
- [ ] Test login berfungsi
- [ ] Test fitur utama
- [ ] PWA bisa di-install

---

## 🎉 Selesai!

Website Anda sekarang live di: `https://ypok-management.vercel.app`

**Happy deploying! 🚀**

---

## 📞 Butuh Bantuan?

Jika ada masalah:
- Cek [Vercel Docs](https://vercel.com/docs)
- Cek [Supabase Docs](https://supabase.com/docs)
- Buka GitHub Issues
