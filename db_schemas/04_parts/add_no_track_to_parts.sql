-- Add no_track flag to parts table
-- Parts with no_track=TRUE will never be assigned to a track
-- (e.g. standalone circles, cut-off pieces, connectors)

ALTER TABLE parts
ADD COLUMN no_track BOOLEAN NOT NULL DEFAULT FALSE
COMMENT 'If TRUE, this part is not part of any track and should not appear in track assignment UI';
