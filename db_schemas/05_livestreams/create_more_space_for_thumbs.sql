ALTER TABLE `livestreams`
ADD `thumbnail` VARCHAR(200) NULL AFTER `description`,
ADD `duration` VARCHAR(15) NULL AFTER `thumbnail`;
