CREATE TABLE workers_photos (
  photo_id INT AUTO_INCREMENT PRIMARY KEY,
  worker_id INT NOT NULL,
  photo_code CHAR(16) NOT NULL,
  friendly_name VARCHAR(255) DEFAULT NULL,
  caption TEXT,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id) ON DELETE CASCADE
) COMMENT='Each photo has a FS code and optional friendly name. One photo may be primary.';

INSERT INTO workers_photos (worker_id, photo_code, friendly_name, is_primary)
VALUES
  ((SELECT worker_id FROM workers WHERE worker_alias = 'cm'),
   'jpgLjn2025061322',
   'Candy Mama',
   TRUE),

  ((SELECT worker_id FROM workers WHERE worker_alias = 'gar'),
   'jpgj3v2025061322',
   'Garinoppi',
   TRUE),

  ((SELECT worker_id FROM workers WHERE worker_alias = 'rab'),
   'jpgkda2025061322',
   'Rabby',
   TRUE),

  ((SELECT worker_id FROM workers WHERE worker_alias = 'francois'),
   'jpgVXZ2025061322',
   'François',
   TRUE);
