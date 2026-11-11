CREATE DATABASE IF NOT EXISTS barangay_information_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_information_system;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(120) NOT NULL, username VARCHAR(60) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS residents (
 id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(100), middle_name VARCHAR(100), last_name VARCHAR(100),
 birth_date DATE, sex VARCHAR(20), civil_status VARCHAR(30), occupation VARCHAR(120), contact_number VARCHAR(40),
 email VARCHAR(150), purok VARCHAR(80), address TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS households (
 id INT AUTO_INCREMENT PRIMARY KEY, household_no VARCHAR(60), head_name VARCHAR(150), purok VARCHAR(80),
 address TEXT, members_count INT, contact_number VARCHAR(40), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS certificates (
 id INT AUTO_INCREMENT PRIMARY KEY, resident_name VARCHAR(180), certificate_type VARCHAR(100), purpose TEXT,
 date_requested DATE, status VARCHAR(50), amount DECIMAL(12,2), released_at DATE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS finance (
 id INT AUTO_INCREMENT PRIMARY KEY, transaction_date DATE, transaction_type VARCHAR(30), category VARCHAR(100),
 description TEXT, amount DECIMAL(12,2), recorded_by VARCHAR(120), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS blotter (
 id INT AUTO_INCREMENT PRIMARY KEY, incident_date DATE, complainant VARCHAR(150), respondent VARCHAR(150),
 incident_type VARCHAR(100), location VARCHAR(180), narrative TEXT, status VARCHAR(60), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS ordinances (
 id INT AUTO_INCREMENT PRIMARY KEY, reference_no VARCHAR(80), document_type VARCHAR(40), title VARCHAR(220),
 date_approved DATE, status VARCHAR(50), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS inventory (
 id INT AUTO_INCREMENT PRIMARY KEY, asset_name VARCHAR(180), category VARCHAR(100), quantity INT,
 condition_status VARCHAR(50), location VARCHAR(150), acquired_date DATE, estimated_value DECIMAL(12,2),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
