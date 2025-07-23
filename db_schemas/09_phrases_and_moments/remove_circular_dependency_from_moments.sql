-- First, drop the foreign key constraint that points from moments to phrases
ALTER TABLE `moments` DROP FOREIGN KEY `moments_ibfk_1`;

-- Second, drop the now-unused phrase_id column from the moments table
ALTER TABLE `moments` DROP COLUMN `phrase_id`;
