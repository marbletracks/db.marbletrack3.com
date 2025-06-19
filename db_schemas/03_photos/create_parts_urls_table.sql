CREATE TABLE part_image_urls (
  image_url_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  image_url VARCHAR(200) NOT NULL,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
) COMMENT 'URLs for b.robnugen.com';
