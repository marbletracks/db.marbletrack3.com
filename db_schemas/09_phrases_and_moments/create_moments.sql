CREATE TABLE moments (
    moment_id INT AUTO_INCREMENT PRIMARY KEY,
    frame_start INT, -- nullable because not all moments include frames
    frame_end INT, -- nullable because not all moments include frames
    phrase_id INT, -- Nullable: not all moments have a phrase
    take_id INT, -- Nullable: not all moments have a take
    notes VARCHAR(255),
    FOREIGN KEY (phrase_id) REFERENCES phrases(phrase_id),
    FOREIGN KEY (take_id) REFERENCES takes(take_id)
);
