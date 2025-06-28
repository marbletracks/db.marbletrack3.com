ALTER TABLE `episodes`
ADD `episode_frames` TEXT
CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
AFTER `episode_english_description`;

