-- Add missing no_fly_list table
CREATE TABLE IF NOT EXISTS no_fly_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add missing messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS passengers (
    passenger_id VARCHAR(36) PRIMARY KEY,
    full_name VARCHAR(100),
    passport_number VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    nationality VARCHAR(50),
    flight_code VARCHAR(20),
    seat_number VARCHAR(10),
    check_in_status VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS customers (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
email VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS baggage (
    baggage_id VARCHAR(36) PRIMARY KEY,
    passenger_id VARCHAR(36),
    weight DECIMAL(5,2),
    destination VARCHAR(100),
    barcode VARCHAR(50),
    status VARCHAR(50),
    scanned_by VARCHAR(100),
    entry_point VARCHAR(50),
    exit_point VARCHAR(50),
    handling_agent VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS clearance_logs (
    id VARCHAR(36) PRIMARY KEY,
    passenger_id VARCHAR(36),
    security_officer VARCHAR(100),
    entry_time DATETIME,
    exit_time DATETIME,
    terminal VARCHAR(10),
    flagged VARCHAR(10),
    reason TEXT,
    resolution VARCHAR(50),
    notes TEXT
);

CREATE TABLE IF NOT EXISTS wifi_logs (
    device_id VARCHAR(36) PRIMARY KEY,
    passenger_email VARCHAR(100),
    mac_address VARCHAR(17),
    session_start DATETIME,
    session_end DATETIME,
    bandwidth_used VARCHAR(20),
    ip_address VARCHAR(45),
    device_type VARCHAR(50),
    login_status VARCHAR(20),
    terms_agreed VARCHAR(10)
);
