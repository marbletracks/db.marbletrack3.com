ALTER TABLE `phrases`
ADD COLUMN `temp_not_3rd_normal_worker_id` INT NULL DEFAULT NULL AFTER `phrase_id`,
ADD INDEX `idx_temp_worker_id` (`temp_not_3rd_normal_worker_id`);

-- No foreign key constraint is added to keep this relationship flexible and temporary.
-- This allows phrases to exist without a worker, and avoids strict enforcement
-- that might complicate future cleanup.
