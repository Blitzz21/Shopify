-- Custom Print Shop Database Schema
-- Version: 2.0

CREATE DATABASE IF NOT EXISTS Shopify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Shopify;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shopify_product_id VARCHAR(255) NOT NULL UNIQUE,
    shopify_variant_id VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) DEFAULT 0.00,
    print_area_width INT NOT NULL COMMENT 'Width in inches',
    print_area_height INT NOT NULL COMMENT 'Height in inches',
    min_dpi INT DEFAULT 150,
    max_file_size INT DEFAULT 10485760 COMMENT 'Max file size in bytes (10MB default)',
    allowed_formats JSON DEFAULT ('["jpg", "jpeg", "png"]'),
    product_type VARCHAR(50) DEFAULT 'apparel',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shopify_product (shopify_product_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255),
    shopify_customer_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Designs table
CREATE TABLE IF NOT EXISTS designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    preview_path VARCHAR(500),
    file_size INT NOT NULL,
    mime_type VARCHAR(50),
    width INT,
    height INT,
    dpi INT,
    design_config JSON COMMENT 'Position, scale, rotation, etc.',
    status ENUM('uploaded', 'processing', 'processed', 'error', 'deleted') DEFAULT 'uploaded',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shopify_order_id VARCHAR(255) NOT NULL UNIQUE,
    shopify_order_number VARCHAR(50),
    session_id VARCHAR(100),
    customer_email VARCHAR(255),
    total_amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'USD',
    order_status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    fulfillment_status ENUM('unfulfilled', 'partial', 'fulfilled') DEFAULT 'unfulfilled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shopify_order (shopify_order_id),
    INDEX idx_session (session_id),
    INDEX idx_status (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    design_id INT NOT NULL,
    product_id INT NOT NULL,
    shopify_line_item_id VARCHAR(255),
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (design_id) REFERENCES designs(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id),
    INDEX idx_design (design_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Print jobs table
CREATE TABLE IF NOT EXISTS print_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    design_id INT NOT NULL,
    print_file_path VARCHAR(500),
    status ENUM('queued', 'preparing', 'printing', 'printed', 'shipped', 'failed') DEFAULT 'queued',
    priority INT DEFAULT 0,
    assigned_to VARCHAR(100),
    notes TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (design_id) REFERENCES designs(id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Webhook logs table
CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic VARCHAR(100) NOT NULL,
    shopify_order_id VARCHAR(255),
    payload JSON,
    status ENUM('received', 'processed', 'failed') DEFAULT 'received',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_topic (topic),
    INDEX idx_order (shopify_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample products
INSERT INTO products (shopify_product_id, shopify_variant_id, name, description, base_price, print_area_width, print_area_height, min_dpi, product_type) VALUES
('gid://shopify/Product/100001', 'gid://shopify/ProductVariant/200001', 'Custom T-Shirt', 'High quality cotton t-shirt with custom print', 24.99, 12, 16, 150, 'apparel'),
('gid://shopify/Product/100002', 'gid://shopify/ProductVariant/200002', 'Custom Mug', 'Ceramic mug with wrap-around print', 14.99, 8, 3, 200, 'drinkware'),
('gid://shopify/Product/100003', 'gid://shopify/ProductVariant/200003', 'Custom Hoodie', 'Premium hoodie with front print', 44.99, 12, 16, 150, 'apparel'),
('gid://shopify/Product/100004', 'gid://shopify/ProductVariant/200004', 'Custom Phone Case', 'Durable phone case with custom design', 19.99, 2, 4, 300, 'accessories');