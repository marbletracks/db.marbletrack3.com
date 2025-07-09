CREATE TABLE phrases (
    phrase_id INT AUTO_INCREMENT PRIMARY KEY,
    phrase VARCHAR(255) NOT NULL,
    language_code CHAR(2) DEFAULT 'en',
    token_json VARCHAR(100), -- Optional: token breakdown
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
