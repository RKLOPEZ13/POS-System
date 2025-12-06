-- Drop existing database if it exists
DROP DATABASE IF EXISTS pos_system;

-- Create new database
CREATE DATABASE pos_system;
USE pos_system;

-- --------------------------------------------------
-- USERS TABLE with password
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL,
    password VARCHAR(255) NOT NULL,
    hired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample users with passwords
INSERT INTO users (first_name, last_name, role, password, hired_at) VALUES
('Reinher', 'Lopez', 'admin', 'admin123', '2025-01-10 08:30:00'),
('Bea', 'Bello', 'cashier', 'bea123', '2025-03-15 09:00:00');

-- --------------------------------------------------
-- PRODUCTS TABLE
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    beginning_stock INT NOT NULL,
    total_stock INT NOT NULL,
    end_stock INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample products
INSERT INTO products (name, description, beginning_stock, total_stock, end_stock, price, image, created_at) VALUES
('Coffee', 'Freshly brewed hot coffee', 50, 100, 90, 50.00, 'coffee.jpg', '2025-11-08 08:00:00'),
('Sandwich', 'Ham and cheese sandwich', 30, 80, 70, 80.00, 'sandwich.jpg', '2025-11-08 08:10:00'),
('Tea', 'Hot milk tea', 40, 90, 85, 40.00, 'tea.jpg', '2025-11-08 08:15:00'),
('Cake', 'Slice of chocolate cake', 20, 50, 47, 120.00, 'cake.jpg', '2025-11-08 08:30:00'),
('Juice', 'Fresh fruit juice', 60, 120, 110, 60.00, 'juice.jpg', '2025-11-08 08:45:00');

-- --------------------------------------------------
-- TRANSACTIONS TABLE (merged sales)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Sample transactions
INSERT INTO transactions (user_id, product_id, quantity, total_price, sale_date) VALUES
(2, 1, 1, 50.00, '2025-11-08 09:00:00'),
(2, 2, 1, 80.00, '2025-11-08 09:05:00'),
(1, 3, 2, 80.00, '2025-11-08 10:15:00'),
(1, 4, 1, 120.00, '2025-11-08 11:30:00'),
(1, 5, 2, 120.00, '2025-11-08 12:10:00');

-- --------------------------------------------------
-- LOGS TABLE
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Sample logs
INSERT INTO logs (user_id, action, log_date) VALUES
(1, 'Added new product: Coffee', '2025-11-08 08:00:00'),
(1, 'Updated stock for Sandwich', '2025-11-08 08:10:00'),
(2, 'Processed sale for Coffee', '2025-11-08 09:00:00'),
(2, 'Processed sale for Tea', '2025-11-08 10:15:00'),
(1, 'Reviewed daily sales report', '2025-11-08 18:00:00');