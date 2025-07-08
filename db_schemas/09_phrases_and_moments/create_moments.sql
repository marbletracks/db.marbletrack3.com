CREATE TABLE moments (
    moment_id INT AUTO_INCREMENT PRIMARY KEY,
    frame_start INT, -- nullable because not all moments include frames
    frame_end INT, -- nullable because not all moments include frames
    phrase_id INT, -- Nullable: not all moments have a phrase
    take_id INT, -- Nullable: not all moments have a take
    notes VARCHAR(255),
    FOREIGN KEY (phrase_id) REFERENCES phrases(phrase_id),
    FOREIGN KEY (take_id) REFERENCES takes(take_id)
);

CREATE TABLE moment_translations (
    moment_translation_id INT PRIMARY KEY AUTO_INCREMENT,
    moment_id INT NOT NULL,
    language_code CHAR(2) DEFAULT 'en',  -- e.g., 'en', 'ja', etc.
    translation_text TEXT NOT NULL  COMMENT "Can include shortcodes",     -- Final sentence form (can include shortcodes)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (moment_id) REFERENCES moments(moment_id)
);