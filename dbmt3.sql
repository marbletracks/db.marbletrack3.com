-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: eich.robnugen.com
-- Generation Time: Jul 29, 2025 at 08:01 AM
-- Server version: 8.0.28-0ubuntu0.20.04.3
-- PHP Version: 8.1.2-1ubuntu2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbmt3`
--

-- --------------------------------------------------------

--
-- Table structure for table `applied_DB_versions`
--

DROP TABLE IF EXISTS `applied_DB_versions`;
CREATE TABLE `applied_DB_versions` (
  `id` int UNSIGNED NOT NULL,
  `applied_version` varchar(128) NOT NULL,
  `direction` enum('up','down') NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `columns`
--

DROP TABLE IF EXISTS `columns`;
CREATE TABLE `columns` (
  `column_id` int NOT NULL,
  `page_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `col_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `col_sort` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cookies`
--

DROP TABLE IF EXISTS `cookies`;
CREATE TABLE `cookies` (
  `id` int UNSIGNED NOT NULL,
  `cookie` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_access` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent_md5` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

DROP TABLE IF EXISTS `episodes`;
CREATE TABLE `episodes` (
  `episode_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `episode_english_description` text COLLATE utf8mb4_general_ci,
  `episode_frames` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `livestream_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `episodes_2_photos`
--

DROP TABLE IF EXISTS `episodes_2_photos`;
CREATE TABLE `episodes_2_photos` (
  `episode_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `livestreams`
--

DROP TABLE IF EXISTS `livestreams`;
CREATE TABLE `livestreams` (
  `livestream_id` int NOT NULL,
  `external_id` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `platform` enum('youtube','twitch') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'youtube',
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thumbnail_url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `status` enum('not','has','wont') COLLATE utf8mb4_general_ci DEFAULT 'not',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `livestream_transcripts`
--

DROP TABLE IF EXISTS `livestream_transcripts`;
CREATE TABLE `livestream_transcripts` (
  `lt_id` int NOT NULL,
  `livestream_id` int NOT NULL,
  `transcript` longtext COLLATE utf8mb4_general_ci,
  `source` enum('auto','manual') COLLATE utf8mb4_general_ci DEFAULT 'auto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moments`
--

DROP TABLE IF EXISTS `moments`;
CREATE TABLE `moments` (
  `moment_id` int NOT NULL,
  `frame_start` int DEFAULT NULL,
  `frame_end` int DEFAULT NULL,
  `take_id` int DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moment_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moments_2_photos`
--

DROP TABLE IF EXISTS `moments_2_photos`;
CREATE TABLE `moments_2_photos` (
  `moment_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moment_translations`
--

DROP TABLE IF EXISTS `moment_translations`;
CREATE TABLE `moment_translations` (
  `moment_id` int NOT NULL,
  `perspective_entity_id` int NOT NULL,
  `perspective_entity_type` enum('worker','part') NOT NULL,
  `translated_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'NULL for a simple moment with no specific perspective for this worker',
  `is_significant` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flags if this moment is a significant event for this perspective.',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'Defines the display order of moments for a given perspective.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moment_translations_bakk`
--

DROP TABLE IF EXISTS `moment_translations_bakk`;
CREATE TABLE `moment_translations_bakk` (
  `moment_id` int NOT NULL,
  `perspective_entity_id` int NOT NULL,
  `perspective_entity_type` enum('worker','part') NOT NULL,
  `translated_note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notebooks`
--

DROP TABLE IF EXISTS `notebooks`;
CREATE TABLE `notebooks` (
  `notebook_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notebooks_2_photos`
--

DROP TABLE IF EXISTS `notebooks_2_photos`;
CREATE TABLE `notebooks_2_photos` (
  `notebook_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `page_id` int NOT NULL,
  `notebook_id` int NOT NULL,
  `number` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages_2_photos`
--

DROP TABLE IF EXISTS `pages_2_photos`;
CREATE TABLE `pages_2_photos` (
  `page_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `part_id` int NOT NULL,
  `part_alias` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_rail` tinyint(1) DEFAULT '0',
  `is_support` tinyint(1) DEFAULT '0',
  `is_track` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Each part is a distinct section of the marble track, identified by a URL-safe alias.';

-- --------------------------------------------------------

--
-- Table structure for table `parts_2_photos`
--

DROP TABLE IF EXISTS `parts_2_photos`;
CREATE TABLE `parts_2_photos` (
  `part_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parts_oss_status`
--

DROP TABLE IF EXISTS `parts_oss_status`;
CREATE TABLE `parts_oss_status` (
  `parts_oss_status_id` int NOT NULL,
  `part_id` int NOT NULL,
  `ssop_label` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `ssop_mm` decimal(6,1) NOT NULL,
  `height_orig` decimal(5,2) NOT NULL,
  `height_best` decimal(5,2) NOT NULL,
  `height_now` decimal(5,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `part_connections`
--

DROP TABLE IF EXISTS `part_connections`;
CREATE TABLE `part_connections` (
  `connection_id` int NOT NULL,
  `from_part_id` int NOT NULL,
  `to_part_id` int NOT NULL,
  `marble_sizes` set('small','medium','large') COLLATE utf8mb4_general_ci NOT NULL,
  `connection_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `part_histories`
--

DROP TABLE IF EXISTS `part_histories`;
CREATE TABLE `part_histories` (
  `part_history_id` int NOT NULL,
  `part_id` int NOT NULL,
  `event_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `part_history_photos`
--

DROP TABLE IF EXISTS `part_history_photos`;
CREATE TABLE `part_history_photos` (
  `part_history_photo_id` int NOT NULL,
  `part_history_id` int NOT NULL,
  `photo_sort` tinyint NOT NULL,
  `history_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `part_history_translations`
--

DROP TABLE IF EXISTS `part_history_translations`;
CREATE TABLE `part_history_translations` (
  `part_history_translation_id` int NOT NULL,
  `part_history_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `history_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `history_description` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `part_translations`
--

DROP TABLE IF EXISTS `part_translations`;
CREATE TABLE `part_translations` (
  `part_translation_id` int NOT NULL,
  `part_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `part_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `part_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE `photos` (
  `photo_id` int NOT NULL,
  `code` char(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `friendly_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phrases`
--

DROP TABLE IF EXISTS `phrases`;
CREATE TABLE `phrases` (
  `phrase_id` int NOT NULL,
  `phrase` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token_json` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moment_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shortcodes`
--

DROP TABLE IF EXISTS `shortcodes`;
CREATE TABLE `shortcodes` (
  `shortcode_id` int NOT NULL,
  `keyword` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `language` char(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en',
  `replacement` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `context` set('parts','workers') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Replaces VSCode snippets.';

-- --------------------------------------------------------

--
-- Table structure for table `takes`
--

DROP TABLE IF EXISTS `takes`;
CREATE TABLE `takes` (
  `take_id` int NOT NULL,
  `take_name` varchar(55) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `token_id` int NOT NULL,
  `column_id` int NOT NULL,
  `token_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  `token_x_pos` int NOT NULL DEFAULT '0',
  `token_y_pos` int NOT NULL DEFAULT '0',
  `token_width` int NOT NULL DEFAULT '100',
  `token_height` int NOT NULL DEFAULT '50',
  `token_color` enum('Red','Blue','Black') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Black',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 for permanent, 0 for not'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

DROP TABLE IF EXISTS `workers`;
CREATE TABLE `workers` (
  `worker_id` int NOT NULL,
  `worker_alias` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `busy_sort` tinyint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Each worker is a character in Marble Track 3. Alias is an ASCII-safe unique code.';

-- --------------------------------------------------------

--
-- Table structure for table `workers_2_photos`
--

DROP TABLE IF EXISTS `workers_2_photos`;
CREATE TABLE `workers_2_photos` (
  `worker_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worker_names`
--

DROP TABLE IF EXISTS `worker_names`;
CREATE TABLE `worker_names` (
  `worker_name_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `language_code` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `worker_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `worker_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applied_DB_versions`
--
ALTER TABLE `applied_DB_versions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`column_id`) USING BTREE,
  ADD KEY `page_id` (`page_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `cookies`
--
ALTER TABLE `cookies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cookie` (`cookie`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`episode_id`),
  ADD KEY `livestream_id` (`livestream_id`);

--
-- Indexes for table `episodes_2_photos`
--
ALTER TABLE `episodes_2_photos`
  ADD PRIMARY KEY (`episode_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `livestreams`
--
ALTER TABLE `livestreams`
  ADD PRIMARY KEY (`livestream_id`),
  ADD UNIQUE KEY `youtube_video_id` (`external_id`);

--
-- Indexes for table `livestream_transcripts`
--
ALTER TABLE `livestream_transcripts`
  ADD PRIMARY KEY (`lt_id`),
  ADD KEY `livestream_id` (`livestream_id`);

--
-- Indexes for table `moments`
--
ALTER TABLE `moments`
  ADD PRIMARY KEY (`moment_id`),
  ADD KEY `take_id` (`take_id`);

--
-- Indexes for table `moments_2_photos`
--
ALTER TABLE `moments_2_photos`
  ADD PRIMARY KEY (`moment_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `moment_translations`
--
ALTER TABLE `moment_translations`
  ADD PRIMARY KEY (`moment_id`,`perspective_entity_id`,`perspective_entity_type`),
  ADD KEY `moment_id` (`moment_id`),
  ADD KEY `is_significant_idx` (`is_significant`),
  ADD KEY `sort_order_idx` (`sort_order`);

--
-- Indexes for table `moment_translations_bakk`
--
ALTER TABLE `moment_translations_bakk`
  ADD PRIMARY KEY (`moment_id`,`perspective_entity_id`,`perspective_entity_type`),
  ADD KEY `moment_id` (`moment_id`);

--
-- Indexes for table `notebooks`
--
ALTER TABLE `notebooks`
  ADD PRIMARY KEY (`notebook_id`);

--
-- Indexes for table `notebooks_2_photos`
--
ALTER TABLE `notebooks_2_photos`
  ADD PRIMARY KEY (`notebook_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`) USING BTREE,
  ADD KEY `notebook_id` (`notebook_id`);

--
-- Indexes for table `pages_2_photos`
--
ALTER TABLE `pages_2_photos`
  ADD PRIMARY KEY (`page_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`part_id`),
  ADD UNIQUE KEY `part_alias` (`part_alias`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `parts_2_photos`
--
ALTER TABLE `parts_2_photos`
  ADD PRIMARY KEY (`part_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `parts_oss_status`
--
ALTER TABLE `parts_oss_status`
  ADD PRIMARY KEY (`parts_oss_status_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indexes for table `part_connections`
--
ALTER TABLE `part_connections`
  ADD PRIMARY KEY (`connection_id`),
  ADD KEY `from_part_id` (`from_part_id`),
  ADD KEY `to_part_id` (`to_part_id`);

--
-- Indexes for table `part_histories`
--
ALTER TABLE `part_histories`
  ADD PRIMARY KEY (`part_history_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indexes for table `part_history_photos`
--
ALTER TABLE `part_history_photos`
  ADD PRIMARY KEY (`part_history_photo_id`),
  ADD KEY `part_history_id` (`part_history_id`);

--
-- Indexes for table `part_history_translations`
--
ALTER TABLE `part_history_translations`
  ADD PRIMARY KEY (`part_history_translation_id`),
  ADD KEY `part_history_id` (`part_history_id`);

--
-- Indexes for table `part_translations`
--
ALTER TABLE `part_translations`
  ADD PRIMARY KEY (`part_translation_id`),
  ADD UNIQUE KEY `part_language_unique` (`part_id`,`language_code`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`photo_id`);

--
-- Indexes for table `phrases`
--
ALTER TABLE `phrases`
  ADD PRIMARY KEY (`phrase_id`),
  ADD KEY `idx_moment_id` (`moment_id`);

--
-- Indexes for table `shortcodes`
--
ALTER TABLE `shortcodes`
  ADD PRIMARY KEY (`shortcode_id`),
  ADD UNIQUE KEY `keyword` (`keyword`,`language`);

--
-- Indexes for table `takes`
--
ALTER TABLE `takes`
  ADD PRIMARY KEY (`take_id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`token_id`) USING BTREE,
  ADD KEY `column_id` (`column_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`worker_id`),
  ADD UNIQUE KEY `worker_alias` (`worker_alias`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `workers_2_photos`
--
ALTER TABLE `workers_2_photos`
  ADD PRIMARY KEY (`worker_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Indexes for table `worker_names`
--
ALTER TABLE `worker_names`
  ADD PRIMARY KEY (`worker_name_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applied_DB_versions`
--
ALTER TABLE `applied_DB_versions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `columns`
--
ALTER TABLE `columns`
  MODIFY `column_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cookies`
--
ALTER TABLE `cookies`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `episodes`
--
ALTER TABLE `episodes`
  MODIFY `episode_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `livestreams`
--
ALTER TABLE `livestreams`
  MODIFY `livestream_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `livestream_transcripts`
--
ALTER TABLE `livestream_transcripts`
  MODIFY `lt_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `moments`
--
ALTER TABLE `moments`
  MODIFY `moment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notebooks`
--
ALTER TABLE `notebooks`
  MODIFY `notebook_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `part_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parts_oss_status`
--
ALTER TABLE `parts_oss_status`
  MODIFY `parts_oss_status_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `part_connections`
--
ALTER TABLE `part_connections`
  MODIFY `connection_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `part_histories`
--
ALTER TABLE `part_histories`
  MODIFY `part_history_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `part_history_photos`
--
ALTER TABLE `part_history_photos`
  MODIFY `part_history_photo_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `part_history_translations`
--
ALTER TABLE `part_history_translations`
  MODIFY `part_history_translation_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `part_translations`
--
ALTER TABLE `part_translations`
  MODIFY `part_translation_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `photo_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phrases`
--
ALTER TABLE `phrases`
  MODIFY `phrase_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shortcodes`
--
ALTER TABLE `shortcodes`
  MODIFY `shortcode_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `takes`
--
ALTER TABLE `takes`
  MODIFY `take_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `token_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `worker_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worker_names`
--
ALTER TABLE `worker_names`
  MODIFY `worker_name_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `columns`
--
ALTER TABLE `columns`
  ADD CONSTRAINT `columns_page_id` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `columns_worker_id` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `cookies`
--
ALTER TABLE `cookies`
  ADD CONSTRAINT `fk_cookies_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestreams` (`livestream_id`) ON DELETE SET NULL;

--
-- Constraints for table `episodes_2_photos`
--
ALTER TABLE `episodes_2_photos`
  ADD CONSTRAINT `episodes_2_photos_ibfk_1` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`episode_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `episodes_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `livestream_transcripts`
--
ALTER TABLE `livestream_transcripts`
  ADD CONSTRAINT `livestream_transcripts_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestreams` (`livestream_id`) ON DELETE CASCADE;

--
-- Constraints for table `moments`
--
ALTER TABLE `moments`
  ADD CONSTRAINT `moments_ibfk_2` FOREIGN KEY (`take_id`) REFERENCES `takes` (`take_id`);

--
-- Constraints for table `moments_2_photos`
--
ALTER TABLE `moments_2_photos`
  ADD CONSTRAINT `moments_2_photos_ibfk_1` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `moments_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `moment_translations`
--
ALTER TABLE `moment_translations`
  ADD CONSTRAINT `moment_translations_ibfk_1` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE;

--
-- Constraints for table `notebooks_2_photos`
--
ALTER TABLE `notebooks_2_photos`
  ADD CONSTRAINT `notebooks_2_photos_ibfk_1` FOREIGN KEY (`notebook_id`) REFERENCES `notebooks` (`notebook_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notebooks_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `notebook_id` FOREIGN KEY (`notebook_id`) REFERENCES `notebooks` (`notebook_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `pages_2_photos`
--
ALTER TABLE `pages_2_photos`
  ADD CONSTRAINT `pages_2_photos_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pages_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `parts_2_photos`
--
ALTER TABLE `parts_2_photos`
  ADD CONSTRAINT `parts_2_photos_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parts_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `parts_oss_status`
--
ALTER TABLE `parts_oss_status`
  ADD CONSTRAINT `parts_oss_status_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE;

--
-- Constraints for table `part_connections`
--
ALTER TABLE `part_connections`
  ADD CONSTRAINT `part_connections_ibfk_1` FOREIGN KEY (`from_part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `part_connections_ibfk_2` FOREIGN KEY (`to_part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE;

--
-- Constraints for table `part_histories`
--
ALTER TABLE `part_histories`
  ADD CONSTRAINT `part_histories_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE;

--
-- Constraints for table `part_history_photos`
--
ALTER TABLE `part_history_photos`
  ADD CONSTRAINT `part_history_photos_ibfk_1` FOREIGN KEY (`part_history_id`) REFERENCES `part_histories` (`part_history_id`) ON DELETE CASCADE;

--
-- Constraints for table `part_history_translations`
--
ALTER TABLE `part_history_translations`
  ADD CONSTRAINT `part_history_translations_ibfk_1` FOREIGN KEY (`part_history_id`) REFERENCES `part_histories` (`part_history_id`) ON DELETE CASCADE;

--
-- Constraints for table `part_translations`
--
ALTER TABLE `part_translations`
  ADD CONSTRAINT `part_translations_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON DELETE CASCADE;

--
-- Constraints for table `phrases`
--
ALTER TABLE `phrases`
  ADD CONSTRAINT `fk_phrases_moment_id` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE;

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_column_id` FOREIGN KEY (`column_id`) REFERENCES `columns` (`column_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `workers_2_photos`
--
ALTER TABLE `workers_2_photos`
  ADD CONSTRAINT `workers_2_photos_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workers_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_names`
--
ALTER TABLE `worker_names`
  ADD CONSTRAINT `worker_names_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
