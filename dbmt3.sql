-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: eich.robnugen.com    Database: dbmt3
-- ------------------------------------------------------
-- Server version	8.4.11-0ubuntu0.26.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `api_credits`
--

DROP TABLE IF EXISTS `api_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_credits` (
  `credit_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `credits_remaining` int unsigned NOT NULL DEFAULT '0',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`credit_id`),
  UNIQUE KEY `uniq_user_credits` (`user_id`),
  CONSTRAINT `api_credits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_credits`
--

/*!40000 ALTER TABLE `api_credits` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_credits` ENABLE KEYS */;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `key_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `api_key_hash` char(64) NOT NULL COMMENT 'SHA-256 hex hash of the raw key',
  `label` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `can_write` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this key can use write endpoints (PATCH, POST, DELETE)',
  PRIMARY KEY (`key_id`),
  UNIQUE KEY `uniq_api_key_hash` (`api_key_hash`),
  KEY `idx_user_keys` (`user_id`),
  CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;

--
-- Table structure for table `api_usage`
--

DROP TABLE IF EXISTS `api_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_usage` (
  `usage_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `key_id` bigint unsigned NOT NULL,
  `endpoint` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  KEY `idx_user_usage` (`user_id`,`created_at`),
  KEY `idx_key_usage` (`key_id`,`created_at`),
  CONSTRAINT `api_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `api_usage_ibfk_2` FOREIGN KEY (`key_id`) REFERENCES `api_keys` (`key_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_usage`
--

/*!40000 ALTER TABLE `api_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_usage` ENABLE KEYS */;

--
-- Table structure for table `applied_DB_versions`
--

DROP TABLE IF EXISTS `applied_DB_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applied_DB_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `applied_version` varchar(128) NOT NULL,
  `direction` enum('up','down') NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applied_DB_versions`
--

/*!40000 ALTER TABLE `applied_DB_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `applied_DB_versions` ENABLE KEYS */;

--
-- Table structure for table `columns`
--

DROP TABLE IF EXISTS `columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `columns` (
  `column_id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `col_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `col_sort` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`column_id`) USING BTREE,
  KEY `page_id` (`page_id`),
  KEY `worker_id` (`worker_id`),
  CONSTRAINT `columns_page_id` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `columns_worker_id` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `columns`
--

/*!40000 ALTER TABLE `columns` DISABLE KEYS */;
/*!40000 ALTER TABLE `columns` ENABLE KEYS */;

--
-- Table structure for table `cookies`
--

DROP TABLE IF EXISTS `cookies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cookies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cookie` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_access` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent_md5` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cookie` (`cookie`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_cookies_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookies`
--

/*!40000 ALTER TABLE `cookies` DISABLE KEYS */;
/*!40000 ALTER TABLE `cookies` ENABLE KEYS */;

--
-- Table structure for table `episodes`
--

DROP TABLE IF EXISTS `episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `episodes` (
  `episode_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `episode_english_description` text COLLATE utf8mb4_general_ci,
  `episode_frames` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `livestream_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`episode_id`),
  KEY `livestream_id` (`livestream_id`),
  CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestreams` (`livestream_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `episodes`
--

/*!40000 ALTER TABLE `episodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `episodes` ENABLE KEYS */;

--
-- Table structure for table `episodes_2_photos`
--

DROP TABLE IF EXISTS `episodes_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `episodes_2_photos` (
  `episode_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`episode_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `episodes_2_photos_ibfk_1` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`episode_id`) ON DELETE CASCADE,
  CONSTRAINT `episodes_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `episodes_2_photos`
--

/*!40000 ALTER TABLE `episodes_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `episodes_2_photos` ENABLE KEYS */;

--
-- Table structure for table `livestream_transcripts`
--

DROP TABLE IF EXISTS `livestream_transcripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestream_transcripts` (
  `lt_id` int NOT NULL AUTO_INCREMENT,
  `livestream_id` int NOT NULL,
  `transcript` longtext COLLATE utf8mb4_general_ci,
  `source` enum('auto','manual') COLLATE utf8mb4_general_ci DEFAULT 'auto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lt_id`),
  KEY `livestream_id` (`livestream_id`),
  CONSTRAINT `livestream_transcripts_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestreams` (`livestream_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestream_transcripts`
--

/*!40000 ALTER TABLE `livestream_transcripts` DISABLE KEYS */;
/*!40000 ALTER TABLE `livestream_transcripts` ENABLE KEYS */;

--
-- Table structure for table `livestreams`
--

DROP TABLE IF EXISTS `livestreams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestreams` (
  `livestream_id` int NOT NULL AUTO_INCREMENT,
  `external_id` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `platform` enum('youtube','twitch') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'youtube',
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thumbnail_url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `status` enum('not','has','wont') COLLATE utf8mb4_general_ci DEFAULT 'not',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`livestream_id`),
  UNIQUE KEY `youtube_video_id` (`external_id`)
) ENGINE=InnoDB AUTO_INCREMENT=448 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestreams`
--

/*!40000 ALTER TABLE `livestreams` DISABLE KEYS */;
/*!40000 ALTER TABLE `livestreams` ENABLE KEYS */;

--
-- Table structure for table `marbles`
--

DROP TABLE IF EXISTS `marbles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marbles` (
  `marble_id` int NOT NULL AUTO_INCREMENT,
  `marble_alias` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `marble_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `team_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` enum('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`marble_id`),
  UNIQUE KEY `marble_alias` (`marble_alias`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marbles`
--

/*!40000 ALTER TABLE `marbles` DISABLE KEYS */;
/*!40000 ALTER TABLE `marbles` ENABLE KEYS */;

--
-- Table structure for table `marbles_2_photos`
--

DROP TABLE IF EXISTS `marbles_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marbles_2_photos` (
  `marble_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`marble_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `marbles_2_photos_ibfk_1` FOREIGN KEY (`marble_id`) REFERENCES `marbles` (`marble_id`) ON DELETE CASCADE,
  CONSTRAINT `marbles_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marbles_2_photos`
--

/*!40000 ALTER TABLE `marbles_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `marbles_2_photos` ENABLE KEYS */;

--
-- Table structure for table `moment_translations`
--

DROP TABLE IF EXISTS `moment_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moment_translations` (
  `moment_id` int NOT NULL,
  `perspective_entity_id` int NOT NULL,
  `perspective_entity_type` enum('worker','part','marble') NOT NULL,
  `translated_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_significant` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flags if this moment is a significant event for this perspective.',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'Defines the display order of moments for a given perspective.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`moment_id`,`perspective_entity_id`,`perspective_entity_type`),
  KEY `moment_id` (`moment_id`),
  KEY `is_significant_idx` (`is_significant`),
  KEY `sort_order_idx` (`sort_order`),
  CONSTRAINT `moment_translations_ibfk_1` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moment_translations`
--

/*!40000 ALTER TABLE `moment_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `moment_translations` ENABLE KEYS */;

--
-- Table structure for table `moment_translations_bakk`
--

DROP TABLE IF EXISTS `moment_translations_bakk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moment_translations_bakk` (
  `moment_id` int NOT NULL,
  `perspective_entity_id` int NOT NULL,
  `perspective_entity_type` enum('worker','part') NOT NULL,
  `translated_note` text NOT NULL,
  PRIMARY KEY (`moment_id`,`perspective_entity_id`,`perspective_entity_type`),
  KEY `moment_id` (`moment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moment_translations_bakk`
--

/*!40000 ALTER TABLE `moment_translations_bakk` DISABLE KEYS */;
/*!40000 ALTER TABLE `moment_translations_bakk` ENABLE KEYS */;

--
-- Table structure for table `moments`
--

DROP TABLE IF EXISTS `moments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moments` (
  `moment_id` int NOT NULL AUTO_INCREMENT,
  `frame_start` int DEFAULT NULL,
  `frame_end` int DEFAULT NULL,
  `take_id` int DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moment_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`moment_id`),
  KEY `take_id` (`take_id`),
  CONSTRAINT `moments_ibfk_2` FOREIGN KEY (`take_id`) REFERENCES `takes` (`take_id`)
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moments`
--

/*!40000 ALTER TABLE `moments` DISABLE KEYS */;
/*!40000 ALTER TABLE `moments` ENABLE KEYS */;

--
-- Table structure for table `moments_2_photos`
--

DROP TABLE IF EXISTS `moments_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moments_2_photos` (
  `moment_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`moment_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `moments_2_photos_ibfk_1` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE,
  CONSTRAINT `moments_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moments_2_photos`
--

/*!40000 ALTER TABLE `moments_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `moments_2_photos` ENABLE KEYS */;

--
-- Table structure for table `notebooks`
--

DROP TABLE IF EXISTS `notebooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notebooks` (
  `notebook_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notebook_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notebooks`
--

/*!40000 ALTER TABLE `notebooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `notebooks` ENABLE KEYS */;

--
-- Table structure for table `notebooks_2_photos`
--

DROP TABLE IF EXISTS `notebooks_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notebooks_2_photos` (
  `notebook_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notebook_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `notebooks_2_photos_ibfk_1` FOREIGN KEY (`notebook_id`) REFERENCES `notebooks` (`notebook_id`) ON DELETE CASCADE,
  CONSTRAINT `notebooks_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notebooks_2_photos`
--

/*!40000 ALTER TABLE `notebooks_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `notebooks_2_photos` ENABLE KEYS */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `page_id` int NOT NULL AUTO_INCREMENT,
  `notebook_id` int NOT NULL,
  `number` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`) USING BTREE,
  KEY `notebook_id` (`notebook_id`),
  CONSTRAINT `notebook_id` FOREIGN KEY (`notebook_id`) REFERENCES `notebooks` (`notebook_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;

--
-- Table structure for table `pages_2_photos`
--

DROP TABLE IF EXISTS `pages_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_2_photos` (
  `page_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `pages_2_photos_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE,
  CONSTRAINT `pages_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_2_photos`
--

/*!40000 ALTER TABLE `pages_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pages_2_photos` ENABLE KEYS */;

--
-- Table structure for table `part_connections`
--

DROP TABLE IF EXISTS `part_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_connections` (
  `connection_id` int NOT NULL AUTO_INCREMENT,
  `from_part_id` int NOT NULL,
  `to_part_id` int NOT NULL,
  `marble_sizes` set('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `connection_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`connection_id`),
  KEY `from_part_id` (`from_part_id`),
  KEY `to_part_id` (`to_part_id`),
  CONSTRAINT `part_connections_ibfk_1` FOREIGN KEY (`from_part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE,
  CONSTRAINT `part_connections_ibfk_2` FOREIGN KEY (`to_part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_connections`
--

/*!40000 ALTER TABLE `part_connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_connections` ENABLE KEYS */;

--
-- Table structure for table `part_histories`
--

DROP TABLE IF EXISTS `part_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_histories` (
  `part_history_id` int NOT NULL AUTO_INCREMENT,
  `part_id` int NOT NULL,
  `event_date` date DEFAULT NULL,
  PRIMARY KEY (`part_history_id`),
  KEY `part_id` (`part_id`),
  CONSTRAINT `part_histories_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_histories`
--

/*!40000 ALTER TABLE `part_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_histories` ENABLE KEYS */;

--
-- Table structure for table `part_history_photos`
--

DROP TABLE IF EXISTS `part_history_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_history_photos` (
  `part_history_photo_id` int NOT NULL AUTO_INCREMENT,
  `part_history_id` int NOT NULL,
  `photo_sort` tinyint NOT NULL,
  `history_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`part_history_photo_id`),
  KEY `part_history_id` (`part_history_id`),
  CONSTRAINT `part_history_photos_ibfk_1` FOREIGN KEY (`part_history_id`) REFERENCES `part_histories` (`part_history_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_history_photos`
--

/*!40000 ALTER TABLE `part_history_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_history_photos` ENABLE KEYS */;

--
-- Table structure for table `part_history_translations`
--

DROP TABLE IF EXISTS `part_history_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_history_translations` (
  `part_history_translation_id` int NOT NULL AUTO_INCREMENT,
  `part_history_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `history_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `history_description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`part_history_translation_id`),
  KEY `part_history_id` (`part_history_id`),
  CONSTRAINT `part_history_translations_ibfk_1` FOREIGN KEY (`part_history_id`) REFERENCES `part_histories` (`part_history_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_history_translations`
--

/*!40000 ALTER TABLE `part_history_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_history_translations` ENABLE KEYS */;

--
-- Table structure for table `part_translations`
--

DROP TABLE IF EXISTS `part_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_translations` (
  `part_translation_id` int NOT NULL AUTO_INCREMENT,
  `part_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `part_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `part_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`part_translation_id`),
  UNIQUE KEY `part_language_unique` (`part_id`,`language_code`),
  CONSTRAINT `part_translations_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=430 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_translations`
--

/*!40000 ALTER TABLE `part_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `part_translations` ENABLE KEYS */;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts` (
  `part_id` int NOT NULL AUTO_INCREMENT,
  `part_alias` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_rail` tinyint(1) DEFAULT '0',
  `is_support` tinyint(1) DEFAULT '0',
  `is_track` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `no_track` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If TRUE, this part is not part of any track and should not appear in track assignment UI',
  PRIMARY KEY (`part_id`),
  UNIQUE KEY `part_alias` (`part_alias`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Each part is a distinct section of the marble track, identified by a URL-safe alias.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts`
--

/*!40000 ALTER TABLE `parts` DISABLE KEYS */;
/*!40000 ALTER TABLE `parts` ENABLE KEYS */;

--
-- Table structure for table `parts_2_photos`
--

DROP TABLE IF EXISTS `parts_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts_2_photos` (
  `part_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`part_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `parts_2_photos_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE,
  CONSTRAINT `parts_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts_2_photos`
--

/*!40000 ALTER TABLE `parts_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `parts_2_photos` ENABLE KEYS */;

--
-- Table structure for table `parts_oss_status`
--

DROP TABLE IF EXISTS `parts_oss_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts_oss_status` (
  `parts_oss_status_id` int NOT NULL AUTO_INCREMENT,
  `part_id` int NOT NULL,
  `ssop_label` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `ssop_mm` decimal(6,1) NOT NULL,
  `height_orig` decimal(5,2) NOT NULL,
  `height_best` decimal(5,2) NOT NULL,
  `height_now` decimal(5,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parts_oss_status_id`),
  KEY `part_id` (`part_id`),
  CONSTRAINT `parts_oss_status_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts_oss_status`
--

/*!40000 ALTER TABLE `parts_oss_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `parts_oss_status` ENABLE KEYS */;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photos` (
  `photo_id` int NOT NULL AUTO_INCREMENT,
  `code` char(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `friendly_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`photo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=345 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;

--
-- Table structure for table `phrases`
--

DROP TABLE IF EXISTS `phrases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phrases` (
  `phrase_id` int NOT NULL AUTO_INCREMENT,
  `phrase` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token_json` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moment_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`phrase_id`),
  KEY `idx_moment_id` (`moment_id`),
  CONSTRAINT `fk_phrases_moment_id` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phrases`
--

/*!40000 ALTER TABLE `phrases` DISABLE KEYS */;
/*!40000 ALTER TABLE `phrases` ENABLE KEYS */;

--
-- Table structure for table `ride_tracks`
--

DROP TABLE IF EXISTS `ride_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ride_tracks` (
  `ride_track_id` int NOT NULL AUTO_INCREMENT,
  `ride_id` int NOT NULL,
  `track_id` int NOT NULL,
  `sequence_order` int NOT NULL COMMENT 'Order in which this track is visited (1, 2, 3...)',
  `experience_note` text COLLATE utf8mb4_general_ci COMMENT 'What the marble experiences here, e.g. "full length", "hairpin turn"',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ride_track_id`),
  UNIQUE KEY `unique_ride_track_order` (`ride_id`,`sequence_order`),
  KEY `track_id` (`track_id`),
  CONSTRAINT `ride_tracks_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`ride_id`) ON DELETE CASCADE,
  CONSTRAINT `ride_tracks_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps which tracks a ride visits and in what order.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ride_tracks`
--

/*!40000 ALTER TABLE `ride_tracks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ride_tracks` ENABLE KEYS */;

--
-- Table structure for table `rides`
--

DROP TABLE IF EXISTS `rides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rides` (
  `ride_id` int NOT NULL AUTO_INCREMENT,
  `ride_alias` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ride_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ride_description` text COLLATE utf8mb4_general_ci COMMENT 'Visitor-facing description of the experience',
  `ride_tagline` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Short teaser, e.g. "The longest ride on the track"',
  `marble_size` enum('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `is_complete` tinyint(1) DEFAULT '0' COMMENT 'All tracks in this ride are built and functional',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ride_id`),
  UNIQUE KEY `ride_alias` (`ride_alias`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Named marble journeys. Each ride visits a sequence of tracks.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rides`
--

/*!40000 ALTER TABLE `rides` DISABLE KEYS */;
/*!40000 ALTER TABLE `rides` ENABLE KEYS */;

--
-- Table structure for table `rides_2_photos`
--

DROP TABLE IF EXISTS `rides_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rides_2_photos` (
  `ride_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ride_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `rides_2_photos_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`ride_id`) ON DELETE CASCADE,
  CONSTRAINT `rides_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Photos associated with rides.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rides_2_photos`
--

/*!40000 ALTER TABLE `rides_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `rides_2_photos` ENABLE KEYS */;

--
-- Table structure for table `shortcodes`
--

DROP TABLE IF EXISTS `shortcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shortcodes` (
  `shortcode_id` int NOT NULL AUTO_INCREMENT,
  `keyword` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `language` char(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en',
  `replacement` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `context` set('parts','workers') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shortcode_id`),
  UNIQUE KEY `keyword` (`keyword`,`language`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Replaces VSCode snippets.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shortcodes`
--

/*!40000 ALTER TABLE `shortcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `shortcodes` ENABLE KEYS */;

--
-- Table structure for table `takes`
--

DROP TABLE IF EXISTS `takes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `takes` (
  `take_id` int NOT NULL AUTO_INCREMENT,
  `take_name` varchar(55) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`take_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `takes`
--

/*!40000 ALTER TABLE `takes` DISABLE KEYS */;
/*!40000 ALTER TABLE `takes` ENABLE KEYS */;

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tokens` (
  `token_id` int NOT NULL AUTO_INCREMENT,
  `column_id` int NOT NULL,
  `token_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  `token_x_pos` int NOT NULL DEFAULT '0',
  `token_y_pos` int NOT NULL DEFAULT '0',
  `token_width` int NOT NULL DEFAULT '100',
  `token_height` int NOT NULL DEFAULT '50',
  `token_color` enum('Red','Blue','Black') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Black',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 for permanent, 0 for not',
  PRIMARY KEY (`token_id`) USING BTREE,
  KEY `column_id` (`column_id`),
  CONSTRAINT `tokens_column_id` FOREIGN KEY (`column_id`) REFERENCES `columns` (`column_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens`
--

/*!40000 ALTER TABLE `tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `tokens` ENABLE KEYS */;

--
-- Table structure for table `track_connections`
--

DROP TABLE IF EXISTS `track_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `track_connections` (
  `track_connection_id` int NOT NULL AUTO_INCREMENT,
  `from_track_id` int NOT NULL,
  `to_track_id` int NOT NULL,
  `marble_sizes` set('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `connection_type` enum('direct','split','merge') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'direct',
  `connection_description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`track_connection_id`),
  UNIQUE KEY `unique_track_connection` (`from_track_id`,`to_track_id`,`connection_type`),
  KEY `to_track_id` (`to_track_id`),
  CONSTRAINT `track_connections_ibfk_1` FOREIGN KEY (`from_track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE,
  CONSTRAINT `track_connections_ibfk_2` FOREIGN KEY (`to_track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Marble flow connections between tracks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `track_connections`
--

/*!40000 ALTER TABLE `track_connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `track_connections` ENABLE KEYS */;

--
-- Table structure for table `track_parts`
--

DROP TABLE IF EXISTS `track_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `track_parts` (
  `track_part_id` int NOT NULL AUTO_INCREMENT,
  `track_id` int NOT NULL,
  `part_id` int NOT NULL,
  `part_role` enum('main','rail','support','connector','guide') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_exclusive_to_track` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If TRUE, this part cannot be used in other tracks',
  PRIMARY KEY (`track_part_id`),
  UNIQUE KEY `unique_track_part` (`track_id`,`part_id`),
  KEY `part_id` (`part_id`),
  KEY `idx_track_parts_exclusive` (`is_exclusive_to_track`,`part_id`),
  CONSTRAINT `track_parts_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE,
  CONSTRAINT `track_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps which parts belong to which tracks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `track_parts`
--

/*!40000 ALTER TABLE `track_parts` DISABLE KEYS */;
/*!40000 ALTER TABLE `track_parts` ENABLE KEYS */;

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS `tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tracks` (
  `track_id` int NOT NULL AUTO_INCREMENT,
  `track_alias` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `track_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `technical_description` text COLLATE utf8mb4_general_ci,
  `visitor_description` text COLLATE utf8mb4_general_ci,
  `is_transport` tinyint(1) DEFAULT '0',
  `is_splitter` tinyint(1) DEFAULT '0',
  `is_landing_zone` tinyint(1) DEFAULT '0',
  `marble_sizes_accepted` set('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `entity_type` enum('marble','worker','mixed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'marble' COMMENT 'Type of entity that uses this track: marble (gravity-fed), worker (worker-navigated), or mixed (both)',
  PRIMARY KEY (`track_id`),
  UNIQUE KEY `track_alias` (`track_alias`),
  KEY `idx_tracks_entity_type` (`entity_type`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logical tracks made up of multiple parts. Tracks transport marbles via gravity.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracks_2_photos`
--

DROP TABLE IF EXISTS `tracks_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tracks_2_photos` (
  `track_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`track_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `tracks_2_photos_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE,
  CONSTRAINT `tracks_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracks`
--

/*!40000 ALTER TABLE `tracks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracks` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Table structure for table `worker_names`
--

DROP TABLE IF EXISTS `worker_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `worker_names` (
  `worker_name_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `worker_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `worker_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_name_id`),
  KEY `worker_id` (`worker_id`),
  CONSTRAINT `worker_names_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worker_names`
--

/*!40000 ALTER TABLE `worker_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `worker_names` ENABLE KEYS */;

--
-- Table structure for table `workers`
--

DROP TABLE IF EXISTS `workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workers` (
  `worker_id` int NOT NULL AUTO_INCREMENT,
  `worker_alias` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `slug` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `busy_sort` tinyint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`),
  UNIQUE KEY `worker_alias` (`worker_alias`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Each worker is a character in Marble Track 3. Alias is an ASCII-safe unique code.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workers`
--

/*!40000 ALTER TABLE `workers` DISABLE KEYS */;
/*!40000 ALTER TABLE `workers` ENABLE KEYS */;

--
-- Table structure for table `workers_2_photos`
--

DROP TABLE IF EXISTS `workers_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workers_2_photos` (
  `worker_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `workers_2_photos_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  CONSTRAINT `workers_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workers_2_photos`
--

/*!40000 ALTER TABLE `workers_2_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `workers_2_photos` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13 19:54:49
