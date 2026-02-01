-- Veterinary Clinic, Dairy & Farm Management System
-- Database Schema

CREATE DATABASE IF NOT EXISTS vet_management_system;
USE vet_management_system;

-- Users Table (Authentication & Authorization)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'veterinarian', 'staff', 'farm_owner') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Activity Logs Table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(50),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Customers Table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Animals Table
CREATE TABLE animals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_code VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    name VARCHAR(100),
    species ENUM('cattle', 'buffalo', 'goat', 'sheep', 'pig', 'poultry', 'other') NOT NULL,
    breed VARCHAR(50),
    gender ENUM('male', 'female') NOT NULL,
    date_of_birth DATE,
    age_years INT,
    age_months INT,
    color VARCHAR(50),
    identification_marks TEXT,
    weight DECIMAL(10, 2),
    status ENUM('active', 'sold', 'deceased') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Diseases Table
CREATE TABLE diseases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    disease_name VARCHAR(100) NOT NULL,
    symptoms TEXT,
    diagnosed_by INT,
    diagnosis_date DATE NOT NULL,
    severity ENUM('mild', 'moderate', 'severe', 'critical') DEFAULT 'moderate',
    status ENUM('active', 'recovered', 'chronic') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (diagnosed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Disease Images Table
CREATE TABLE disease_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    disease_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disease_id) REFERENCES diseases(id) ON DELETE CASCADE
);

-- Treatments Table
CREATE TABLE treatments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    disease_id INT,
    treatment_date DATE NOT NULL,
    prescribed_by INT,
    treatment_type VARCHAR(100),
    medicine_name VARCHAR(100),
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(50),
    route VARCHAR(50),
    instructions TEXT,
    start_date DATE,
    end_date DATE,
    cost DECIMAL(10, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (disease_id) REFERENCES diseases(id) ON DELETE SET NULL,
    FOREIGN KEY (prescribed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Vaccinations Table
CREATE TABLE vaccinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    vaccine_type VARCHAR(100),
    batch_number VARCHAR(50),
    administered_by INT,
    administered_date DATE NOT NULL,
    next_due_date DATE,
    status ENUM('scheduled', 'completed', 'overdue', 'skipped') DEFAULT 'completed',
    cost DECIMAL(10, 2),
    notes TEXT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Artificial Insemination (AI) Records Table
CREATE TABLE ai_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    ai_date DATE NOT NULL,
    bull_id VARCHAR(50),
    bull_breed VARCHAR(50),
    technician_name VARCHAR(100),
    performed_by INT,
    method ENUM('natural', 'artificial') DEFAULT 'artificial',
    first_checkup_date DATE,
    first_checkup_result ENUM('pending', 'positive', 'negative'),
    second_checkup_date DATE,
    second_checkup_result ENUM('pending', 'positive', 'negative'),
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    pregnancy_status ENUM('not_confirmed', 'confirmed', 'failed', 'delivered') DEFAULT 'not_confirmed',
    cost DECIMAL(10, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Milk Dairy Records Table
CREATE TABLE dairy_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    animal_id INT NOT NULL,
    record_date DATE NOT NULL,
    morning_milk DECIMAL(10, 2) DEFAULT 0,
    afternoon_milk DECIMAL(10, 2) DEFAULT 0,
    evening_milk DECIMAL(10, 2) DEFAULT 0,
    total_milk DECIMAL(10, 2) GENERATED ALWAYS AS (morning_milk + afternoon_milk + evening_milk) STORED,
    fat_percentage DECIMAL(5, 2),
    snf_percentage DECIMAL(5, 2),
    quality_grade VARCHAR(10),
    recorded_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_animal_date (animal_id, record_date)
);

-- Loans Table
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    loan_number VARCHAR(50) UNIQUE NOT NULL,
    loan_type VARCHAR(100),
    loan_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(5, 2),
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('active', 'paid', 'overdue', 'defaulted') DEFAULT 'active',
    paid_amount DECIMAL(15, 2) DEFAULT 0,
    remaining_amount DECIMAL(15, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Loan Payments Table
CREATE TABLE loan_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    payment_date DATE NOT NULL,
    payment_amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50),
    received_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insurance Policies Table
CREATE TABLE insurance_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    animal_id INT,
    policy_number VARCHAR(50) UNIQUE NOT NULL,
    insurance_company VARCHAR(100) NOT NULL,
    policy_type VARCHAR(100),
    coverage_amount DECIMAL(15, 2),
    premium_amount DECIMAL(15, 2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'claimed') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE
);

-- Insurance Claims Table
CREATE TABLE insurance_claims (
    id INT PRIMARY KEY AUTO_INCREMENT,
    policy_id INT NOT NULL,
    claim_number VARCHAR(50) UNIQUE NOT NULL,
    claim_date DATE NOT NULL,
    incident_date DATE NOT NULL,
    claim_amount DECIMAL(15, 2) NOT NULL,
    approved_amount DECIMAL(15, 2),
    claim_reason TEXT,
    status ENUM('submitted', 'under_review', 'approved', 'rejected', 'paid') DEFAULT 'submitted',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (policy_id) REFERENCES insurance_policies(id) ON DELETE CASCADE
);

-- Insurance Claim Documents Table
CREATE TABLE claim_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    claim_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES insurance_claims(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_customer_phone ON customers(phone);
CREATE INDEX idx_customer_code ON customers(customer_code);
CREATE INDEX idx_animal_code ON animals(animal_code);
CREATE INDEX idx_animal_customer ON animals(customer_id);
CREATE INDEX idx_disease_animal ON diseases(animal_id);
CREATE INDEX idx_treatment_animal ON treatments(animal_id);
CREATE INDEX idx_vaccination_animal ON vaccinations(animal_id);
CREATE INDEX idx_vaccination_due_date ON vaccinations(next_due_date);
CREATE INDEX idx_ai_animal ON ai_records(animal_id);
CREATE INDEX idx_dairy_animal_date ON dairy_records(animal_id, record_date);
CREATE INDEX idx_loan_customer ON loans(customer_id);
CREATE INDEX idx_insurance_customer ON insurance_policies(customer_id);
CREATE INDEX idx_claim_policy ON insurance_claims(policy_id);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role, status)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@vetclinic.com', 'admin', 'active');
