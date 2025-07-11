CREATE TABLE workers_2_moments (
    worker_id INT NOT NULL,
    moment_id INT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (worker_id, moment_id),
    FOREIGN KEY (worker_id) REFERENCES workers(worker_id) ON DELETE CASCADE,
    FOREIGN KEY (moment_id) REFERENCES moments(moment_id) ON DELETE CASCADE
);
