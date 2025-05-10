-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create maintenance_mode table
CREATE TABLE IF NOT EXISTS maintenance_mode (
    id INT PRIMARY KEY DEFAULT 1,
    is_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default maintenance mode row if not exists
INSERT INTO maintenance_mode (id, is_enabled) VALUES (1, FALSE)
ON DUPLICATE KEY UPDATE id=id;
