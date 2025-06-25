ALTER TABLE `livestreams`
  CHANGE COLUMN `youtube_video_id` `external_id` VARCHAR(64) NOT NULL;

ALTER TABLE `livestreams`
  ADD COLUMN `platform` ENUM('youtube','twitch') NOT NULL DEFAULT 'youtube' AFTER `external_id`;