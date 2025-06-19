-- KaziSellers Consolidated Database Schema
-- Complete and organized database structure

-- Create database
CREATE DATABASE IF NOT EXISTS Kazi_sellers;
USE Kazi_sellers;

-- Disable foreign key checks for clean setup
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist (for fresh setup)
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_ratings;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- CORE TABLES
-- ========================================

-- Users table (unified buyer/seller system with role support)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100),
    bio TEXT,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    role TINYINT DEFAULT 0 COMMENT '0 = regular user, 1 = admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products/Listings table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(200),
    price DECIMAL(10,2) NOT NULL,
    condition_type ENUM('new', 'like_new', 'good', 'fair', 'poor') DEFAULT 'good',
    availability ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    location VARCHAR(100),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Product images table
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    upload_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ========================================
-- COMMUNICATION TABLES
-- ========================================

-- Messages/Chat table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- RATING SYSTEM TABLES
-- ========================================

-- User ratings table (for rating sellers/users)
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rater_id INT NOT NULL,
    rated_user_id INT NOT NULL,
    product_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rater_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_rating (rater_id, rated_user_id, product_id)
);

-- Product ratings table (for rating individual products)
CREATE TABLE product_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_rating (product_id, user_id)
);

-- ========================================
-- SHOPPING SYSTEM TABLES
-- ========================================

-- Favorites/Wishlist table
CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id)
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- ========================================
-- ADMIN SYSTEM
-- ========================================

-- Admin table (kept for backward compatibility, but users table with role=1 is preferred)
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- ========================================
-- INDEXES FOR PERFORMANCE
-- ========================================

-- Products indexes
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_availability ON products(availability);
CREATE INDEX idx_products_created ON products(created_at);

-- Messages indexes
CREATE INDEX idx_messages_product ON messages(product_id);
CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_messages_created ON messages(created_at);

-- Ratings indexes
CREATE INDEX idx_ratings_rater ON ratings(rater_id);
CREATE INDEX idx_ratings_rated_user ON ratings(rated_user_id);
CREATE INDEX idx_product_ratings_product ON product_ratings(product_id);
CREATE INDEX idx_product_ratings_user ON product_ratings(user_id);

-- Shopping system indexes
CREATE INDEX idx_favorites_user ON favorites(user_id);
CREATE INDEX idx_favorites_product ON favorites(product_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_product ON cart(product_id);

-- Product images indexes
CREATE INDEX idx_product_images_product ON product_images(product_id);
CREATE INDEX idx_product_images_primary ON product_images(is_primary);

-- ========================================
-- DEFAULT DATA
-- ========================================

-- Insert default categories
INSERT INTO categories (name, description, icon) VALUES
('Electronics', 'Phones, laptops, gadgets, and tech accessories', 'fa-laptop'),
('Clothing', 'Fashion, shoes, accessories, and apparel', 'fa-tshirt'),
('Books', 'Books, magazines, and reading materials', 'fa-book'),
('Furniture', 'Home and office furniture, decor items', 'fa-couch'),
('Sports', 'Sporting goods, fitness equipment, and gear', 'fa-futbol'),
('Other', 'Everything else not covered in other categories', 'fa-box');

-- Create default admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, phone, location, bio, rating, total_ratings, role) VALUES
('admin', 'admin@kazisellers.com', '$2y$10$nTrxftP5snmJA47NDyq7k.BxGarl80Gd5Of6iaVM2/X4u8utx/mOW', 'Admin', 'User', '0701234567', 'System', 'System administrator for KaziSellers platform.', 5.0, 0, 1);

-- Insert into admin table for backward compatibility (same password: admin123)
INSERT INTO admin (username, email, password) VALUES
('admin', 'admin@kazisellers.com', '$2y$10$nTrxftP5snmJA47NDyq7k.BxGarl80Gd5Of6iaVM2/X4u8utx/mOW');

-- ========================================
-- SUMMARY
-- ========================================
-- This consolidated database includes:
-- 1. Core user and product management
-- 2. Dual rating system (users and products)
-- 3. Shopping cart functionality  
-- 4. Wishlist/favorites system
-- 5. Messaging system
-- 6. Admin functionality
-- 7. Proper indexes for performance
-- 8. Foreign key relationships for data integrity
-- 9. Default categories and admin user
-- 10. Backward compatibility with existing code
