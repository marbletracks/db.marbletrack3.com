CREATE TABLE photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    code CHAR(16),      -- for CDN CloudFront etc
    url VARCHAR(255)          -- fallback or legacy URL
);
