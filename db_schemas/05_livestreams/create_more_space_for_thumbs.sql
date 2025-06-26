ALTER TABLE `livestreams`
ADD `thumbnail_url` VARCHAR(200) NULL AFTER `description`,
ADD `duration` VARCHAR(15) NULL AFTER `thumbnail_url`;
