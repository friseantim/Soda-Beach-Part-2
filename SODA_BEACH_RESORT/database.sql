CREATE DATABASE IF NOT EXISTS soda_beach_resort CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE soda_beach_resort;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact VARCHAR(20) NOT NULL DEFAULT '',
  email VARCHAR(250) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','staff') NOT NULL DEFAULT 'customer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cottages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  pax_label VARCHAR(30) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 1
);

CREATE TABLE settings (
  k VARCHAR(50) PRIMARY KEY,
  v VARCHAR(100) NOT NULL
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  visit_date DATE NOT NULL,
  visitors INT NOT NULL,
  cottage_id INT NULL,
  cottage_qty INT NOT NULL DEFAULT 0,
  ticket_total DECIMAL(10,2) NOT NULL,
  cottage_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  ticket_code CHAR(12) NOT NULL UNIQUE,
  paid_at DATETIME NULL,
  checked_in_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (cottage_id) REFERENCES cottages(id)
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL,
  transaction_reference_no VARCHAR(100) NOT NULL,
  proof_image VARCHAR(255) NOT NULL,
  payment_status VARCHAR(20) NOT NULL DEFAULT 'Paid',
  payment_date_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

CREATE TABLE check_in_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  staff_id INT NOT NULL,
  scan_date_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  validation_status VARCHAR(50) NOT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(id),
  FOREIGN KEY (staff_id) REFERENCES users(id)
);

INSERT INTO settings (k, v) VALUES ('daily_capacity','200'), ('day_pass_price','150');

INSERT INTO cottages (name, pax_label, price, quantity) VALUES
 ('Small Cottage','4-6 pax',300.00,10),
 ('Medium Cottage','8-10 pax',500.00,6),
 ('Large Cottage','12-14 pax',1200.00,4);

-- Default Admin Account: Password is "123"
INSERT INTO users (name, email, password, role) VALUES ('Admin','admin@sodabeach.local','$2b$12$ZIy.QIdvtB4eo44M9j0hDuYE4gH9KZgd61G.hxmtPMV9M8BgbRjsm','staff');