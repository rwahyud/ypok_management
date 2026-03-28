# FashionStore E-Commerce Website

Website e-commerce modern dengan fitur lengkap menggunakan PHP, MySQL, dan Bootstrap 5.

## 🎯 Fitur Utama

### Untuk Customer (User)
- ✅ **Browse sebagai Guest** - Lihat produk tanpa login
- ✅ **Search & Filter** - Cari produk berdasarkan nama/kategori
- ✅ **Product Detail** - Halaman detail produk lengkap dengan review
- ✅ **Shopping Cart** - Keranjang belanja dengan update quantity
- ✅ **User Registration** - Daftar akun baru
- ✅ **User Login** - Login untuk checkout
- ✅ **Checkout Process** - Proses checkout (harus login)
- ✅ **Order Confirmation** - Halaman sukses order dengan detail
- ✅ **Product Reviews** - Beri rating & komentar produk

### Untuk Admin
- ✅ **Admin Dashboard** - Dashboard untuk manage toko
- ✅ **Product Management** - CRUD produk
- ✅ **Category Management** - CRUD kategori
- ✅ **Order Management** - Lihat & kelola pesanan
- ✅ **Separate Admin Login** - Login terpisah dari customer

### Fitur Tambahan
- ✅ **Auto Slider** - Hero section berganti otomatis setiap 10 detik
- ✅ **Responsive Design** - Mobile-friendly dengan Bootstrap 5
- ✅ **Clean UI** - Desain modern dengan FashionStore theme (maroon/pink)
- ✅ **COD Payment** - Pembayaran Cash on Delivery

## 📋 Requirements

- **XAMPP** (Apache + MySQL + PHP 8.x)
- **Web Browser** (Chrome, Firefox, Edge, dll)

## 🚀 Instalasi

### 1. Clone/Download Project
```bash
# Letakkan folder ecommerce di:
C:\xampp\htdocs\ecommerce
```

### 2. Import Database
Database sudah otomatis diimport! Jika perlu manual:
```bash
# Via phpMyAdmin:
1. Buka http://localhost/phpmyadmin
2. Import file database.sql

# Via Command Line:
cd c:\xampp
Get-Content "c:\xampp\htdocs\ecommerce\database.sql" | .\mysql\bin\mysql.exe -u root
```

### 3. Jalankan XAMPP
- Start Apache
- Start MySQL

### 4. Akses Website
```
Homepage: http://localhost/ecommerce
```

## 👤 Demo Accounts

### Customer Account
- **Email:** john@example.com
- **Password:** user123

### Admin Account
- **Username:** admin
- **Password:** admin123
- **URL:** http://localhost/ecommerce/admin/login.php

## 📁 Struktur Database

### Tables:
1. **admin** - Data administrator
2. **users** - Data customer/user
3. **categories** - Kategori produk (5 kategori)
4. **products** - Data produk (10 sample produk)
5. **comments** - Review & rating produk
6. **orders** - Data pesanan
7. **order_items** - Item dalam pesanan

## 🎨 Design Features

### Color Scheme
- **Primary:** #a02b6f (Maroon)
- **Secondary:** #862356 (Dark Maroon)
- **Pink Light:** #f4e4ed
- **Dark:** #2d2d2d

### Typography
- **Font:** Poppins (Google Fonts)
- **Weights:** 300, 400, 500, 600, 700, 800

### UI Components
- Rounded buttons (50px border-radius)
- Clean white cards with shadows
- Smooth hover effects
- Animated hero slider
- Floating badges

## 🔄 User Flow

### Guest User (Tanpa Login)
```
1. Browse Homepage
2. Search/Filter Products
3. View Product Detail
4. Add to Cart
5. View Cart
6. HARUS LOGIN untuk Checkout
```

### Registered User (Login)
```
1. Login/Register
2. Browse & Add to Cart
3. Checkout (auto-fill data)
4. Place Order
5. View Order Confirmation
6. Receive Email (simulation)
```

### Admin
```
1. Login via admin/login.php
2. Manage Products (Create, Read, Update, Delete)
3. Manage Categories
4. View & Update Orders
5. Logout
```

## 📂 File Structure

```
ecommerce/
├── index.php              # Homepage dengan slider
├── about.php              # Halaman About Us
├── contact.php            # Halaman Contact
├── product_detail.php     # Detail produk & reviews
├── cart.php               # Shopping cart
├── checkout.php           # Checkout (login required)
├── order_success.php      # Konfirmasi order
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
├── config.php             # Database config & functions
├── header.php             # Header component (search, cart, menu)
├── footer.php             # Footer component
├── style.css              # Custom CSS dengan theme
├── database.sql           # Database schema & sample data
├── admin/
│   ├── login.php          # Admin login (terpisah)
│   ├── index.php          # Admin dashboard
│   ├── products.php       # Product management
│   ├── categories.php     # Category management
│   └── orders.php         # Order management
└── uploads/               # Folder untuk upload gambar
```

## 🛒 Shopping Flow Details

### 1. Add to Cart
- Klik "View Details" pada produk
- Pilih quantity
- Klik "Add to Cart"
- Item masuk session cart

### 2. Cart Management
- Update quantity
- Remove item
- Clear all items
- View total price
- Proceed to checkout

### 3. Checkout Process
- **Guest:** Redirect ke login dengan pesan
- **Logged In:** Auto-fill customer data
- Konfirmasi alamat & detail
- Place order
- Stock otomatis berkurang
- Redirect ke success page

### 4. Order Success
- Tampil Order ID
- Detail customer
- List produk yang dibeli
- Total pembayaran
- Info COD
- Print receipt button

## 🔐 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection protection (mysqli_real_escape_string)
- ✅ XSS protection (htmlspecialchars)
- ✅ Session management
- ✅ Login validation
- ✅ Stock validation

## 🎯 Sample Data

### Categories (5):
1. Men Fashion
2. Women Fashion
3. Accessories
4. Shoes
5. Bags

### Products (10):
- Classic White Shirt (Rp 350.000)
- Denim Jacket (Rp 550.000)
- Elegant Evening Dress (Rp 750.000)
- Casual Summer Dress (Rp 450.000)
- Leather Watch (Rp 850.000)
- Designer Sunglasses (Rp 350.000)
- Sneakers Sport (Rp 650.000)
- Formal Leather Shoes (Rp 750.000)
- Designer Handbag (Rp 1.250.000)
- Travel Backpack (Rp 450.000)

### Users (2 demo):
1. John Doe (john@example.com)
2. Jane Smith (jane@example.com)

## 🌐 Pages & URLs

### Public Pages
- **Homepage:** `/index.php`
- **About:** `/about.php`
- **Contact:** `/contact.php`
- **Product Detail:** `/product_detail.php?id=X`
- **Cart:** `/cart.php`
- **Checkout:** `/checkout.php` (login required)
- **Order Success:** `/order_success.php`

### Authentication
- **User Login:** `/login.php`
- **User Register:** `/register.php`
- **Logout:** `/logout.php`

### Admin Pages
- **Admin Login:** `/admin/login.php`
- **Dashboard:** `/admin/index.php`
- **Products:** `/admin/products.php`
- **Categories:** `/admin/categories.php`
- **Orders:** `/admin/orders.php`

## 📱 Responsive Breakpoints

- **Mobile:** < 768px
- **Tablet:** 768px - 1024px
- **Desktop:** > 1024px

## 🎨 Hero Slider (Auto 10 detik)

1. **Slide 1:** Season Sale 50% Off
2. **Slide 2:** Spring Collection 2026
3. **Slide 3:** Free Shipping Promo
4. **Slide 4:** Featured Product (Leather Jacket)

### Slider Controls:
- Auto-play setiap 10 detik
- Navigation dots (indicators)
- Previous/Next arrows
- Pause on hover

## 💡 Tips Development

### Upload Product Images
```php
// Folder: uploads/
// Format: JPG, PNG, GIF
// Max size: Sesuaikan di admin panel
```

### Custom Functions (config.php)
```php
- clean_input($data)        // Sanitize input
- format_rupiah($angka)     // Format to Rupiah
- is_admin_logged_in()      // Check admin session
- is_user_logged_in()       // Check user session
- get_current_user()        // Get user data
- redirect($url)            // Redirect helper
```

## 🐛 Troubleshooting

### Database Connection Error
```php
// Check config.php:
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');
```

### Session Not Working
```php
// Pastikan session_start() ada di config.php
// Clear browser cookies & cache
```

### Images Not Showing
```php
// Check folder uploads/ exists
// Check file permissions
// Check image path in database
```

## 📞 Support

Jika ada pertanyaan atau issue:
1. Check database connection
2. Check Apache & MySQL running
3. Check error log di XAMPP
4. Clear browser cache

## 📝 License

Educational Project - Free to use and modify

## 🎉 Ready to Use!

Database sudah diimport dengan:
- ✅ 7 Tables
- ✅ 10 Sample Products  
- ✅ 5 Categories
- ✅ 2 Demo Users
- ✅ 1 Admin Account

**Akses sekarang:** http://localhost/ecommerce

---

**Developed with ❤️ using PHP, MySQL & Bootstrap 5**
