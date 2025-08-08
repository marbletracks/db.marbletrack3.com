-- Add entity_type column to tracks table
-- This distinguishes between marble tracks, worker tracks, and mixed-use tracks

ALTER TABLE tracks
ADD COLUMN entity_type ENUM('marble', 'worker', 'mixed') NOT NULL DEFAULT 'marble'
COMMENT 'Type of entity that uses this track: marble (gravity-fed), worker (worker-navigated), or mixed (both)';

-- Update all existing tracks to be marble tracks (safe assumption for existing data)
UPDATE tracks SET entity_type = 'marble';

-- Add index for performance when filtering by entity type
CREATE INDEX idx_tracks_entity_type ON tracks (entity_type);
