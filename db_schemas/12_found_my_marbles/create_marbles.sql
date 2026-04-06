-- Marbles that exist in the MT3 universe
-- quantity = how many Rob has available on set, not a world catalogue
-- Individual marble names within a group (e.g. each of the Three Tigers)
-- live in Super Spoony's moment perspectives, not in this table.

CREATE TABLE marbles (
    marble_id INT AUTO_INCREMENT PRIMARY KEY,
    marble_alias VARCHAR(20) COLLATE utf8mb4_bin NOT NULL UNIQUE,
    marble_name VARCHAR(100) NOT NULL,
    slug VARCHAR(200),
    team_name VARCHAR(100),
    size ENUM('small', 'medium', 'large') NOT NULL,
    color VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
