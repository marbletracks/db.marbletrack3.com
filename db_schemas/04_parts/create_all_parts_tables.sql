-- Core parts table
CREATE TABLE parts (
  part_id INT AUTO_INCREMENT PRIMARY KEY,
  part_alias VARCHAR(20) NOT NULL UNIQUE
) COMMENT='Each part is a distinct section of the marble track, identified by a URL-safe alias.';

-- Translations for part name/description
CREATE TABLE part_translations (
  part_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  part_name VARCHAR(255),
  part_description TEXT,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Ensure unique part translations per language 18 June 2025 ROB
-- Allows me to edit part translations
ALTER TABLE part_translations
ADD UNIQUE KEY part_language_unique (part_id, language_code);

-- General photos for parts
CREATE TABLE parts_photos (
  part_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  photo_code CHAR(16) NOT NULL,
  friendly_name VARCHAR(255),
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Gravity-fed connections between parts
CREATE TABLE part_connections (
  connection_id INT AUTO_INCREMENT PRIMARY KEY,
  from_part_id INT NOT NULL,
  to_part_id INT NOT NULL,
  marble_sizes SET('small', 'medium', 'large') NOT NULL,
  connection_description TEXT,
  FOREIGN KEY (from_part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
  FOREIGN KEY (to_part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Historical moments in a part's life
CREATE TABLE part_histories (
  part_history_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  event_date DATE,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Translations for part history events
CREATE TABLE part_history_translations (
  part_history_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  history_title VARCHAR(255),
  history_description TEXT,
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id) ON DELETE CASCADE
);

-- Images attached to part history events
CREATE TABLE part_history_photos (
  part_history_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  photo_sort TINYINT NOT NULL,
  history_photo VARCHAR(255),
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id) ON DELETE CASCADE
);
