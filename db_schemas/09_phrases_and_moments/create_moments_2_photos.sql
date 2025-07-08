CREATE TABLE IF NOT EXISTS moments_2_photos (
    moment_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (moment_id, photo_id),
    FOREIGN KEY (moment_id) REFERENCES moments(moment_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
