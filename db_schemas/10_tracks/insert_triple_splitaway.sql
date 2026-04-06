-- The Triple Splitaway: small marble catcher track between Triple Splitter and Little Wiggly Track
INSERT INTO tracks (track_alias, track_name, track_description, is_transport, is_splitter, is_landing_zone, marble_sizes_accepted)
VALUES ('ttssmc', 'The Triple Splitaway',
  'Slip out of the Triple Splitter before anyone notices! This secret exit catches small marbles and sends them toward the Little Wiggly Track.',
  TRUE, FALSE, FALSE, 'small');

-- Link the part to its track
INSERT INTO track_parts (track_id, part_id, part_role, is_exclusive_to_track)
VALUES (
  (SELECT track_id FROM tracks WHERE track_alias = 'ttssmc'),
  (SELECT part_id FROM parts WHERE part_alias = 'ttssmc'),
  'main', TRUE);
