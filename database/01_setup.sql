-- setup.sql - Insecure default settings for Hive Airport database

CREATE DATABASE IF NOT EXISTS hive_airport;

USE hive_airport;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    access_level INT NOT NULL DEFAULT 1, -- 1=basic user, 5=admin, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert default admin user with weak password (insecure)
INSERT INTO users (username, password, access_level) VALUES ('admin', 'admin123', 5);

-- Note: Passwords are stored in plaintext (insecure)
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

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- Insert sample customers data to satisfy foreign key constraints in bookings
INSERT INTO customers (username, password, email) VALUES
('johndoe', 'password1', 'johndoe@example.com'),
('janedoe', 'password2', 'janedoe@example.com'),
('alice', 'password3', 'alice@example.com'),
('bob', 'password4', 'bob@example.com'),
('charlie', 'password5', 'charlie@example.com'),
('david', 'password6', 'david@example.com'),
('eve', 'password7', 'eve@example.com'),
('frank', 'password8', 'frank@example.com'),
('grace', 'password9', 'grace@example.com'),
('heidi', 'password10', 'heidi@example.com');
