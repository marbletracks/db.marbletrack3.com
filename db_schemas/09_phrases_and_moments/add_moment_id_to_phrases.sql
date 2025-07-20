ALTER TABLE `phrases`
ADD COLUMN `moment_id` INT NULL DEFAULT NULL AFTER `token_json`,
ADD INDEX `idx_moment_id` (`moment_id`);
