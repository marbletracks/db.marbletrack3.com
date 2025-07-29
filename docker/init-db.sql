-- Initialize test database for Marble Track 3
CREATE DATABASE IF NOT EXISTS dbmt3_test;
USE dbmt3_test;

-- Basic test data to verify connection
CREATE TABLE IF NOT EXISTS test_connection (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO test_connection (message) VALUES ('Docker MySQL is working!');