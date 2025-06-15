CREATE TABLE livestreams (
    livestream_id INT AUTO_INCREMENT PRIMARY KEY,
    youtube_video_id VARCHAR(32) NOT NULL UNIQUE,
    title VARCHAR(255),
    description VARCHAR(255),  -- these are cropped by YT API
    published_at DATETIME,
    status ENUM('not', 'has', 'wont') DEFAULT 'not',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE livestream_transcripts (
    lt_id INT AUTO_INCREMENT PRIMARY KEY,
    livestream_id INT NOT NULL,
    transcript LONGTEXT,
    source ENUM('auto', 'manual') DEFAULT 'auto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestream_id) REFERENCES livestreams(livestream_id) ON DELETE CASCADE
);

CREATE TABLE episodes (
    episode_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    episode_english_description TEXT, -- wild name to easily move to translation table laterer
    livestream_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestream_id) REFERENCES livestreams(livestream_id) ON DELETE SET NULL
);
