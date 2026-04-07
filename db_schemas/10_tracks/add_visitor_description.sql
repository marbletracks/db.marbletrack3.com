-- Rename track_description to technical_description
-- and add visitor_description for marble-friendly theme park voice
ALTER TABLE tracks
  CHANGE COLUMN track_description technical_description TEXT,
  ADD COLUMN visitor_description TEXT AFTER technical_description;
