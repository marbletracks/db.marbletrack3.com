CREATE TABLE IF NOT EXISTS photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    code CHAR(16),      -- for CDN CloudFront etc
    url VARCHAR(255)          -- fallback or legacy URL
);

DROP TABLE IF EXISTS notebooks_2_photos;

CREATE TABLE IF NOT EXISTS notebooks_2_photos (
    notebook_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (notebook_id, photo_id)
);
