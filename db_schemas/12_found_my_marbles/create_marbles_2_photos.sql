-- Linking table for marble photos (same pattern as workers_2_photos, parts_2_photos)
CREATE TABLE IF NOT EXISTS marbles_2_photos (
    marble_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (marble_id, photo_id),
    FOREIGN KEY (marble_id) REFERENCES marbles(marble_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
);
