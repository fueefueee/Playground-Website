-- Playground Clothing Apparel Database
-- Run this SQL in your MySQL/MariaDB server

CREATE DATABASE IF NOT EXISTS playground_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE playground_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(255),
    stock INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    paypal_order_id VARCHAR(255),
    paypal_payer_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert default admin
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@playground.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Default admin password: password

-- Insert sample products
INSERT INTO products (name, description, price, category, image_url, stock) VALUES
('Classic White Tee', 'Premium 100% cotton crew neck tee. Timeless and versatile for any occasion.', 29.99, 'T-Shirts', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80', 50),
('Relaxed Hoodie', 'Oversized fleece hoodie with kangaroo pocket. Perfect for layering.', 69.99, 'Hoodies', 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=600&q=80', 35),
('Cargo Pants', 'Wide-leg cargo pants with multiple utility pockets. Street-ready style.', 89.99, 'Bottoms', 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80', 25),
('Graphic Print Tee', 'Bold graphic art print on heavyweight cotton. Limited edition drop.', 45.00, 'T-Shirts', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=600&q=80', 40),
('Jogger Set', 'Matching jogger and shorts set in brushed French terry.', 110.00, 'Sets', 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&q=80', 20),
('Bomber Jacket', 'Satin bomber jacket with embroidered back patch. Statement outerwear.', 149.99, 'Outerwear', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', 15),
('Boxy Crop Tee', 'Cropped boxy fit tee in garment-washed cotton. Effortlessly cool.', 35.00, 'T-Shirts', 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=600&q=80', 45),
('Wide Leg Jeans', 'High-rise wide leg denim in a light wash. A modern wardrobe essential.', 99.99, 'Bottoms', 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&q=80', 30),
('Zip-Up Fleece', 'Quarter-zip polar fleece pullover. Cozy meets minimal.', 79.99, 'Hoodies', 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=600&q=80', 22),
('Track Jacket', 'Retro-inspired track jacket in moisture-wicking fabric.', 95.00, 'Outerwear', 'https://images.unsplash.com/photo-1544441893-675973e31985?w=600&q=80', 18),
('Linen Shorts', 'Relaxed linen-blend shorts with elastic waistband. Summer essential.', 49.99, 'Bottoms', 'https://images.unsplash.com/photo-1591195853828-11db59a44f43?w=600&q=80', 40),
('Puffer Vest', 'Lightweight puffer vest with side pockets. Versatile layering piece.', 119.99, 'Outerwear', 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=600&q=80', 12);
