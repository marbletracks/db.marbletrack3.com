-- Add 'marble' as a perspective entity type for moment translations
-- so marbles can have their own perspective on moments
ALTER TABLE moment_translations
    MODIFY perspective_entity_type ENUM('worker', 'part', 'marble') NOT NULL;
