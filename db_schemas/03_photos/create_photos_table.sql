CREATE TABLE photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    code CHAR(16) UNIQUE,      -- for CDN CloudFront etc
    url VARCHAR(255)          -- fallback or legacy URL
);
