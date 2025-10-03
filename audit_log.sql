-- Create audit_log table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT,                              -- ID of the user (can be NULL for system events)
    username VARCHAR(100) NOT NULL,           -- Username or identifier of who performed the action
    action VARCHAR(100) NOT NULL,             -- Type of action performed (login, vote, etc.)
    details TEXT,                             -- JSON encoded details about the action
    ip_address VARCHAR(45),                   -- Store IPv4 or IPv6 address
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_timestamp (timestamp),            -- Index for faster sorting/filtering by time
    KEY idx_user (user_id),                  -- Index for filtering by user
    KEY idx_action (action)                  -- Index for filtering by action type
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;