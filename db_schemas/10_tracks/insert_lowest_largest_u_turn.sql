-- Lowest Largest U-Turn track: large marbles only
-- Large marbles arrive here after lifting el Lifty Lever on the Lowest Largest Backtrack,
-- which will eventually release small marbles onto a future track.
INSERT INTO tracks (track_alias, track_name, track_description, is_transport, is_splitter, is_landing_zone, marble_sizes_accepted)
VALUES ('llu', 'Lowest Largest U-Turn',
  'After lifting el Lifty Lever and clearing the way for the little ones, large marbles make their final U-turn before cruising home on The First Track.',
  TRUE, FALSE, FALSE, 'large');

-- Link parts to the track
INSERT INTO track_parts (track_id, part_id, part_role, is_exclusive_to_track) VALUES
((SELECT track_id FROM tracks WHERE track_alias = 'llu'),
 (SELECT part_id FROM parts WHERE part_alias = 'llu'),
 'main', TRUE),
((SELECT track_id FROM tracks WHERE track_alias = 'llu'),
 (SELECT part_id FROM parts WHERE part_alias = 'llub'),
 'guide', TRUE);
