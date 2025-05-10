-- setup.sql - Insecure default settings for Hive Airport database

CREATE DATABASE IF NOT EXISTS hive_airport;

USE hive_airport;

-- Create employees table with no password encryption and default admin user with weak password
CREATE TABLE employees (
    id INT PRIMARY KEY,
    uuid VARCHAR(36),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    position VARCHAR(50),
    department VARCHAR(50),
    salary INT,
    access_level INT,
    hire_date DATE
);

-- Insert default admin user with weak password (insecure)
INSERT INTO users (username, password, access_level) VALUES ('admin', 'admin123', 5);

-- Note: Passwords are stored in plaintext (insecure)
