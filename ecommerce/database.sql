-- Database E-commerce Fashion Store
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel Komentar
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    comment TEXT NOT NULL,
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Users/Customers
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert data admin default (password: admin123)
INSERT INTO admin (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fashionstore.com');

-- Insert sample users (password: user123)
INSERT INTO users (name, email, password, phone, address) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08123456789', 'Jl. Sudirman No. 123, Jakarta'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08198765432', 'Jl. Thamrin No. 456, Jakarta');

-- Insert kategori
INSERT INTO categories (name, description) VALUES 
('Men Fashion', 'Pakaian dan aksesoris pria'),
('Women Fashion', 'Pakaian dan aksesoris wanita'),
('Accessories', 'Aksesoris fashion'),
('Shoes', 'Sepatu dan sandal'),
('Bags', 'Tas dan dompet');

-- Insert produk sample
INSERT INTO products (category_id, name, description, price, stock, image) VALUES 
(1, 'Classic White Shirt', 'Premium cotton white shirt for men, perfect for formal occasions', 350000.00, 25, 'white-shirt.jpg'),
(1, 'Denim Jacket', 'Stylish denim jacket with modern cut', 550000.00, 15, 'denim-jacket.jpg'),
(2, 'Elegant Evening Dress', 'Beautiful evening dress for special occasions', 750000.00, 10, 'evening-dress.jpg'),
(2, 'Casual Summer Dress', 'Light and comfortable summer dress', 450000.00, 20, 'summer-dress.jpg'),
(3, 'Leather Watch', 'Premium leather strap watch', 850000.00, 30, 'watch.jpg'),
(3, 'Designer Sunglasses', 'UV protection designer sunglasses', 350000.00, 40, 'sunglasses.jpg'),
(4, 'Sneakers Sport', 'Comfortable sport sneakers', 650000.00, 18, 'sneakers.jpg'),
(4, 'Formal Leather Shoes', 'Classic formal leather shoes', 750000.00, 12, 'formal-shoes.jpg'),
(5, 'Designer Handbag', 'Luxury designer handbag', 1250000.00, 8, 'handbag.jpg'),
(5, 'Travel Backpack', 'Durable and stylish travel backpack', 450000.00, 22, 'backpack.jpg');
