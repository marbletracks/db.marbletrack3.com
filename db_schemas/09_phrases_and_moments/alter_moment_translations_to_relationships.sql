-- Alter moment_translations to become the single source of truth for moment relationships.

-- Add a column to flag significant moments for a given perspective.
ALTER TABLE `moment_translations`
ADD COLUMN `is_significant` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flags if this moment is a significant event for this perspective.';

-- Add a column to define the display order.
ALTER TABLE `moment_translations`
ADD COLUMN `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Defines the display order of moments for a given perspective.';

-- Make the translated_note nullable to allow for simple relationships without a specific translation.
ALTER TABLE `moment_translations`
MODIFY COLUMN `translated_note` text NULL;

-- Add a basic index on the new columns for potential performance improvements.
ALTER TABLE `moment_translations`
ADD INDEX `is_significant_idx` (`is_significant`),
ADD INDEX `sort_order_idx` (`sort_order`);
