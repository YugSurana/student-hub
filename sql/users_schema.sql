-- ========================================================
-- Users Table Schema (MySQLi User Registration)
-- StudentHub - CHARUSAT
-- ========================================================

USE studenthub;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student',
    last_login DATETIME DEFAULT NULL,
    remember_token_hash VARCHAR(64) DEFAULT NULL,
    remember_token_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

