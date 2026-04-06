-- Initial rides based on Rob's planned marble routes (2026-04-07)

INSERT INTO rides (ride_alias, ride_name, ride_description, ride_tagline, marble_size, is_complete) VALUES
('grand_spiral', 'The Grand Spiral',
  'The longest ride on the track. Spiral down from the top, split three ways, whip around the hairpin, and cruise home on The First Track.',
  'Only for large marbles brave enough to take on the full spiral.',
  'large', FALSE),
('medium_descent', 'The Medium Descent',
  'A smooth ride from the Outer Spiral, splitting halfway down the Triple Splitter to your landing zone.',
  'A quick but scenic roll.',
  'medium', FALSE),
('small_thrill', 'The Small Thrill',
  'Feed in from the top, split off early, catch the wiggly track, and land safe on the right side.',
  'Perfect for the little ones.',
  'small', FALSE);

-- The Grand Spiral: Large marbles
INSERT INTO ride_tracks (ride_id, track_id, sequence_order, experience_note) VALUES
((SELECT ride_id FROM rides WHERE ride_alias = 'grand_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'outer_spiral'),
 1, 'Wind your way down the towering spiral, supported by 22 hand-placed supports.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'grand_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 2, 'The big one. Three lanes for three sizes — you take the widest lane all the way to the bottom.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'grand_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'snake_plate_u_turn'),
 3, 'Hairpin turn! The berm keeps you on track as you reverse direction at speed.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'grand_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'lowest_largest_backtrack'),
 4, 'Cruise back along a chopstick-and-popsicle-stick rail.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'grand_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'tft'),
 6, 'The original. Where it all began. Roll to a gentle stop.');

-- Note: Lowest Largest U-Turn track does not exist yet.
-- Once created, insert it as sequence_order 5 for grand_spiral.

-- The Medium Descent: Medium marbles
INSERT INTO ride_tracks (ride_id, track_id, sequence_order, experience_note) VALUES
((SELECT ride_id FROM rides WHERE ride_alias = 'medium_descent'),
 (SELECT track_id FROM tracks WHERE track_alias = 'outer_spiral'),
 1, 'Start at the top of the spiral and roll down.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'medium_descent'),
 (SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 2, 'Split off halfway down — medium marbles exit here.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'medium_descent'),
 (SELECT track_id FROM tracks WHERE track_alias = 'brmlz'),
 3, 'Arrive at the Back Right Medium Landing Zone. Journey complete.');

-- The Small Thrill: Small marbles
INSERT INTO ride_tracks (ride_id, track_id, sequence_order, experience_note) VALUES
((SELECT ride_id FROM rides WHERE ride_alias = 'small_thrill'),
 (SELECT track_id FROM tracks WHERE track_alias = 'ttssf'),
 1, 'Feed in from the top on the Triple Splitter Small Feeder.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'small_thrill'),
 (SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 2, 'Split off early — small marbles take the quick exit.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'small_thrill'),
 (SELECT track_id FROM tracks WHERE track_alias = 'wiggly'),
 4, 'The wiggly track! A fun little wobble on your way down.'),
((SELECT ride_id FROM rides WHERE ride_alias = 'small_thrill'),
 (SELECT track_id FROM tracks WHERE track_alias = 'rsslz'),
 5, 'Land safely on the Right Side Small Landing Zone.');

-- Note: Small Marble Catcher track does not exist yet.
-- Once created, insert it as sequence_order 3 for small_thrill
-- (between Triple Splitter and Little Wiggly Track).
