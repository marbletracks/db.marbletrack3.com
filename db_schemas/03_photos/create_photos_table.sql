CREATE TABLE IF NOT EXISTS photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    code CHAR(16),      -- for CDN CloudFront etc
    url VARCHAR(255),          -- fallback or legacy URL
    friendly_name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, -- for human readable names
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notebooks_2_photos (
    notebook_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (notebook_id, photo_id),
    FOREIGN KEY (notebook_id) REFERENCES notebooks(notebook_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workers_2_photos (
    worker_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (worker_id, photo_id),
    FOREIGN KEY (worker_id) REFERENCES workers(worker_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS parts_2_photos (
    part_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (part_id, photo_id),
    FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
);

-- do this one after the pages table is created
CREATE TABLE IF NOT EXISTS pages_2_photos (
    page_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    PRIMARY KEY (page_id, photo_id),
    FOREIGN KEY (page_id) REFERENCES pages(page_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
);
