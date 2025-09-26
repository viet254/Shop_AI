-- TechShop Blue DB schema
CREATE DATABASE IF NOT EXISTS techshop_blue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techshop_blue;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  phone VARCHAR(40),
  address VARCHAR(255),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(120) UNIQUE
);

INSERT INTO categories (id, name, slug) VALUES
(1,'Laptop','laptop'),
(2,'Điện thoại','dien-thoai'),
(3,'Linh kiện','linh-kien'),
(4,'Phụ kiện','phu-kien')
ON DUPLICATE KEY UPDATE name = VALUES(name), slug=VALUES(slug);

-- Products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255),
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  short_desc VARCHAR(500),
  description TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Product images
CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Category-specific detail tables
CREATE TABLE IF NOT EXISTS laptops (
  product_id INT PRIMARY KEY,
  cpu VARCHAR(120),
  ram VARCHAR(80),
  storage VARCHAR(120),
  gpu VARCHAR(120),
  screen VARCHAR(120),
  weight VARCHAR(40),
  os VARCHAR(80),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS phones (
  product_id INT PRIMARY KEY,
  chipset VARCHAR(120),
  ram VARCHAR(80),
  storage VARCHAR(120),
  camera VARCHAR(200),
  battery VARCHAR(120),
  screen VARCHAR(120),
  sim VARCHAR(40),
  os VARCHAR(80),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS components (
  product_id INT PRIMARY KEY,
  type VARCHAR(120),
  brand VARCHAR(120),
  model VARCHAR(120),
  specs TEXT,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS accessories (
  product_id INT PRIMARY KEY,
  type VARCHAR(120),
  brand VARCHAR(120),
  compatibility VARCHAR(200),
  specs TEXT,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_code VARCHAR(32) NOT NULL UNIQUE,
  status ENUM('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_address TEXT,
  payment_method VARCHAR(40),
  note VARCHAR(255),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Reviews
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  content TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist (tuỳ chọn)
CREATE TABLE IF NOT EXISTS wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_wish (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Seed admin user (mật khẩu: admin123)
INSERT INTO users (name,email,password_hash,role) VALUES
('Admin','admin@techshop.local','$2y$10$LjqQ4/4Wz5ZZl2gK7jFCeuH9QM3NUp9H3Zc5r8rOopGyvV2CkI9BS','admin')
ON DUPLICATE KEY UPDATE role='admin';

-- Seed sample products
INSERT INTO products(id, category_id, name, price, discount, stock, status, short_desc, description)
VALUES
(1,1,'Laptop Pro 14 2025',25000000,0,5,'active','Laptop mỏng nhẹ, CPU i7, RAM 16GB','Màn 14" 2.5K 120Hz, SSD 512GB, pin lâu.'),
(2,2,'Điện thoại XPhone 12',15000000,0,10,'active','Chipset mạnh, camera đẹp','Màn OLED 6.1", pin 4500mAh, sạc nhanh.'),
(3,3,'RAM DDR4 16GB 3200',900000,0,20,'active','RAM hiệu năng cao','Tương thích đa số mainboard.'),
(4,4,'Chuột không dây Pro',350000,0,30,'active','Chuột 2.4G mượt mà','Thiết kế ergonomic.')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO product_images(product_id, image_url, is_primary) VALUES
(1,'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800',1),
(2,'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800',1),
(3,'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=800',1),
(4,'https://images.unsplash.com/photo-1587825140400-9f0f0a4b3b2f?w=800',1)
ON DUPLICATE KEY UPDATE image_url=VALUES(image_url);

INSERT INTO laptops(product_id,cpu,ram,storage,gpu,screen,weight,os) VALUES
(1,'Intel Core i7-13700H','16GB','512GB SSD','RTX 4050','14" 2.5K 120Hz','1.4kg','Windows 11')
ON DUPLICATE KEY UPDATE cpu=VALUES(cpu);

INSERT INTO phones(product_id,chipset,ram,storage,camera,battery,screen,sim,os) VALUES
(2,'Snapdragon 8 Gen 3','8GB','256GB','50MP OIS','4500mAh','6.1" OLED','2 nano','Android 14')
ON DUPLICATE KEY UPDATE chipset=VALUES(chipset);

INSERT INTO components(product_id,type,brand,model,specs) VALUES
(3,'RAM','Kingston','Fury Beast','DDR4 3200MHz CL16')
ON DUPLICATE KEY UPDATE brand=VALUES(brand);

INSERT INTO accessories(product_id,type,brand,compatibility,specs) VALUES
(4,'Chuột','Logi','Windows/macOS','2.4G + BT, 800-1600 DPI')
ON DUPLICATE KEY UPDATE brand=VALUES(brand);
