CREATE TABLE IF NOT EXISTS `columns` (
  `column_id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `col_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `col_sort` int NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`column_id`) USING BTREE,
  KEY `page_id` (`page_id`),
  KEY `worker_id` (`worker_id`),
  CONSTRAINT `columns_page_id` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `columns_worker_id` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;