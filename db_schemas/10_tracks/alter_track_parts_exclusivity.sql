-- Add exclusivity flag to track_parts table
-- This allows parts to be marked as exclusive to one track
-- while still maintaining the many-to-many relationship structure

ALTER TABLE track_parts
ADD COLUMN is_exclusive_to_track BOOLEAN NOT NULL DEFAULT FALSE
COMMENT 'If TRUE, this part cannot be used in other tracks';

-- Add index for performance when filtering exclusive parts
CREATE INDEX idx_track_parts_exclusive ON track_parts (is_exclusive_to_track, part_id);
