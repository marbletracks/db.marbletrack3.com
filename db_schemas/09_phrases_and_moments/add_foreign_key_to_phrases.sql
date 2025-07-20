ALTER TABLE `phrases`
ADD CONSTRAINT `fk_phrases_moment_id`
FOREIGN KEY (`moment_id`) REFERENCES `moments`(`moment_id`)
ON DELETE CASCADE;
