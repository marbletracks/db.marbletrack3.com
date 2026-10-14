-- Photos of a Track (mg #709). badmint files them on b.robnugen.com and PATCHes the
-- _1000 URL to /api/v1/tracks/<id>; same shape as moments_2_photos.
CREATE TABLE IF NOT EXISTS tracks_2_photos (
    track_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (track_id, photo_id),
    KEY photo_id (photo_id),
    FOREIGN KEY (track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
