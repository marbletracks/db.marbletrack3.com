CREATE TABLE IF NOT EXISTS `tokens` (
  `token_id` int NOT NULL AUTO_INCREMENT,
  `column_id` int NOT NULL,
  `token_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  `token_x_pos` int NOT NULL DEFAULT 0,
  `token_y_pos` int NOT NULL DEFAULT 0,
  `token_width` int NOT NULL DEFAULT 100,
  `token_height` int NOT NULL DEFAULT 50,
  `token_color` enum('Red','Blue','Black') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Black',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`) USING BTREE,
  KEY `column_id` (`column_id`),
  CONSTRAINT `tokens_column_id` FOREIGN KEY (`column_id`) REFERENCES `columns` (`column_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;