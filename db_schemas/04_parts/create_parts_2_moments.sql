CREATE TABLE parts_2_moments (
    part_id INT NOT NULL,
    moment_id INT NOT NULL,
    PRIMARY KEY (part_id, moment_id),
    FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
    FOREIGN KEY (moment_id) REFERENCES moments(moment_id) ON DELETE CASCADE
);