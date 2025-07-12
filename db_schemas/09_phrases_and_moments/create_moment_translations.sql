-- Create the moment_translations table
-- This table stores different narrative versions of a moment from various perspectives.

CREATE TABLE `moment_translations` (
  `moment_id` int(11) NOT NULL,
  `perspective_entity_id` int(11) NOT NULL,
  `perspective_entity_type` enum('worker','part') NOT NULL,
  `translated_note` text NOT NULL,
  PRIMARY KEY (`moment_id`,`perspective_entity_id`,`perspective_entity_type`),
  KEY `moment_id` (`moment_id`),
  CONSTRAINT `moment_translations_ibfk_1` FOREIGN KEY (`moment_id`) REFERENCES `moments` (`moment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
