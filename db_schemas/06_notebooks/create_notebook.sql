CREATE TABLE IF NOT EXISTS notebooks (
    notebook_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `pages` (
  `page_id` int NOT NULL AUTO_INCREMENT,
  `notebook_id` int NOT NULL,
  `number` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`) USING BTREE,
  KEY `notebook_id` (`notebook_id`),
  CONSTRAINT `notebook_id` FOREIGN KEY (`notebook_id`) REFERENCES `notebooks` (`notebook_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
